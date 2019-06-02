<?php

declare(strict_types = 1);

namespace Pu239;

use bt_options;
use Delight\Auth\AttemptCancelledException;
use Delight\Auth\Auth;
use Delight\Auth\AuthError;
use Delight\Auth\DuplicateUsernameException;
use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\InvalidSelectorTokenPairException;
use Delight\Auth\NotLoggedInException;
use Delight\Auth\ResetDisabledException;
use Delight\Auth\TokenExpiredException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UserAlreadyExistsException;
use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Exception;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use PDOStatement;
use Psr\Container\ContainerInterface;
use Spatie\Image\Exceptions\InvalidManipulation;
use function urlencode;

/**
 * Class User.
 */
class User
{
    protected $fluent;
    protected $cache;
    protected $site_config;
    protected $session;
    protected $auth;
    protected $flash;
    protected $achieve;
    protected $container;
    protected $settings;
    protected $userblock;

    /**
     * User constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param Auth               $auth
     * @param Session            $session
     * @param Settings           $settings
     * @param Usersachiev        $achieve
     * @param Userblock          $userblock
     * @param ContainerInterface $c
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, Auth $auth, Session $session, Settings $settings, Usersachiev $achieve, Userblock $userblock, ContainerInterface $c)
    {
        $this->settings = $settings;
        $this->site_config = $this->settings->get_settings();
        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->auth = $auth;
        $this->session = $session;
        $this->achieve = $achieve;
        $this->container = $c;
        $this->userblock = $userblock;
    }

    /**
     * @param string $username
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function getUserIdFromName(string $username)
    {
        $user = $this->cache->get('userid_from_' . urlencode($username));

        if ($user === false || is_null($user)) {
            $user = $this->fluent->from('users')
                                 ->select(null)
                                 ->select('id')
                                 ->where('LOWER(username) = ?', strtolower($username))
                                 ->fetch('id');

            $this->cache->set('userid_from_' . urldecode($username), $user, $this->site_config['expires']['user_cache']);
        }

        return $user;
    }

    /**
     * @param string $username
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function search_by_username(string $username)
    {
        $username = strtolower($username);
        $users = $this->cache->get('search_users_' . $username);
        if ($users === false || is_null($users)) {
            $users = $this->fluent->from('users AS u')
                                  ->select(null)
                                  ->select('u.id')
                                  ->select('u.username')
                                  ->select('u.class')
                                  ->select("LOWER(REPLACE(classname, ' ', '_')) AS classname")
                                  ->innerJoin('class_config AS c ON u.class = c.id')
                                  ->where("u.acceptpms != 'no'")
                                  ->where('u.username != ?', $this->site_config['chatbot']['name'])
                                  ->where('u.username LIKE ?', "$username%")
                                  ->where('c.classname != ""')
                                  ->orderBy('LOWER(u.username)')
                                  ->fetchAll();
            $this->cache->set('search_users_' . $username, $users, 86400);
        }

        return $users;
    }

    /**
     * @param array $users
     *
     * @throws Exception
     */
    public function delete_user_cache(array $users)
    {
        foreach ($users as $userid) {
            if (!empty($userid)) {
                $user = $this->getUserFromId($userid);
                $username = !empty($user) ? $user['username'] : '';
                $this->cache->deleteMulti([
                    'get_all_boxes_' . $userid,
                    'inbox_' . $userid,
                    'insertJumpTo' . $userid,
                    'is_staffs',
                    'peers_' . $userid,
                    'poll_votes_' . $userid,
                    'port_data_' . $userid,
                    'shitlist_' . $userid,
                    'user' . $userid,
                    'useravatar_' . $userid,
                    'userclasses_' . $username,
                    'user_friends_' . $userid,
                    'userhnrs_' . $userid,
                    'users_names_' . $username,
                    'user_rep_' . $userid,
                    'user_snatches_data_' . $userid,
                    'userstatus_' . $userid,
                ]);
            }
        }
    }

    /**
     * @param int  $userid
     * @param bool $fresh
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function getUserFromId(int $userid, bool $fresh = false)
    {
        $user = $this->cache->get('user_' . $userid);
        if ($fresh || $user === false || is_null($user)) {
            $user = $this->fluent->from('users AS u')
                                 ->select('u.bjwins - u.bjlosses AS bj')
                                 ->select('c.win - c.lost AS casino')
                                 ->leftJoin('casino AS c ON u.id=c.userid')
                                 ->where('id = ?', $userid)
                                 ->fetch();

            if ($user) {
                unset($user['hintanswer'], $user['passhash']);

                if ($user['gender'] === 'Male') {
                    $user['it'] = 'he';
                } elseif ($user['gender'] === 'Female') {
                    $user['it'] = 'she';
                } else {
                    $user['it'] = 'it';
                }

                $this->cache->set('user_' . $userid, $user, $this->site_config['expires']['user_cache']);
            }
        }

        if (!empty($user) && $user['override_class'] < $user['class']) {
            $user['class'] = $user['override_class'];
        }

        return $user;
    }

    /**
     * @param string $item
     * @param int    $userid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_item(string $item, int $userid)
    {
        $user = $this->getUserFromId($userid);

        return $user[$item];
    }

    /**
     * @param int    $class
     * @param string $bot
     * @param string $torrent_pass
     * @param string $auth
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_bot_id(int $class, string $bot, string $torrent_pass, string $auth)
    {
        $userid = $this->fluent->from('users')
                               ->select(null)
                               ->select('id')
                               ->where('class >= ?', $class)
                               ->where('username = ?', $bot)
                               ->where('auth = ?', $auth)
                               ->where('torrent_pass = ?', $torrent_pass)
                               ->where('uploadpos = 1')
                               ->where('suspended = "no"')
                               ->fetch('id');

        return $userid;
    }

    /**
     * @param array $values
     * @param array $lang
     *
     * @throws Exception
     * @throws NotFoundException
     * @throws UnbegunTransaction
     * @throws DependencyException
     *
     * @return bool|int
     */
    public function add(array $values, array $lang)
    {
        $userId = false;
        try {
            if ($this->site_config['signup']['email_confirm'] === true) {
                $userId = $this->auth->registerWithUniqueUsername(strip_tags($values['email']), strip_tags($values['password']), strip_tags($values['username']), function ($selector, $token) use ($values, $lang) {
                    $url = $this->site_config['paths']['baseurl'] . '/verify_email.php?selector=' . urlencode($selector) . '&token=' . urlencode($token);
                    $body = str_replace([
                        '<#SITENAME#>',
                        '<#USEREMAIL#>',
                        '<#IP_ADDRESS#>',
                        '<#REG_LINK#>',
                    ], [
                        $this->site_config['site']['name'],
                        strip_tags($values['email']),
                        getip(),
                        $url,
                    ], $lang['takesignup_email_body']);
                    send_mail(strip_tags($values['email']), "{$this->site_config['site']['name']} {$lang['takesignup_confirm']}", $body, strip_tags($body));
                    $this->session->set('is-success', 'We will send a confirmation email to ' . strip_tags($values['email']));
                });
            } else {
                $userId = $this->auth->registerWithUniqueUsername(strip_tags($values['email']), strip_tags($values['password']), strip_tags($values['username']));
            }
        } catch (DuplicateUsernameException $e) {
            $this->session->set('is-warning', 'Username already exists');
        } catch (InvalidEmailException $e) {
            $this->session->set('is-warning', 'Invalid email address');
        } catch (InvalidPasswordException $e) {
            $this->session->set('is-warning', 'Invalid password');
        } catch (UserAlreadyExistsException $e) {
            $this->session->set('is-warning', 'Email already in use');
        } catch (TooManyRequestsException $e) {
            $this->session->set('is-warning', 'Too many requests');
        } catch (AuthError $e) {
            $this->session->set('is-warning', 'Unknown Error');
        }
        if ($userId !== false) {
            $dt = TIME_NOW;
            $set = [
                'free_switch' => $dt + 14 * 86400,
                'torrent_pass' => bin2hex(random_bytes(32)),
                'auth' => bin2hex(random_bytes(32)),
                'apikey' => bin2hex(random_bytes(32)),
                'stylesheet' => $this->site_config['site']['stylesheet'],
                'last_access' => $dt,
            ];
            if (!empty($values['invitedby'])) {
                $set['invitedby'] = (int) $values['invitedby'];
            }
            $this->update($set, $userId);
            $this->achieve->add(['userid' => $userId]);
            $this->userblock->add(['userid' => $userId]);

            $this->cache->deleteMulti([
                'birthdayusers',
                'chat_users_list',
                'is_staff_',
                'latestuser_',
            ]);
            require_once INCL_DIR . 'function_users.php';
            $this->cache->set('latestuser_', format_username($userId), $this->site_config['expires']['latestuser']);
            write_log('User account ' . $userId . ' (' . htmlsafechars($values['username']) . ') was created');
        }

        return $userId;
    }

    /**
     * @param array $set
     * @param int   $userid
     * @param bool  $persist
     *
     * @throws Exception
     * @throws UnbegunTransaction
     *
     * @return bool|int|PDOStatement
     */
    public function update(array $set, int $userid, bool $persist = true)
    {
        $result = $this->fluent->update('users')
                               ->set($set)
                               ->where('id = ?', $userid)
                               ->execute();

        if ($result && $persist) {
            $this->cache->update_row('user_' . $userid, $set, $this->site_config['expires']['user_cache']);
        } else {
            $this->cache->delete('user_' . $userid);
        }

        return $result;
    }

    /**
     * @throws Exception
     *
     * @return array|PDOStatement
     */
    public function get_all_ids()
    {
        $ids = $this->fluent->from('users')
                            ->select(null)
                            ->select('id')
                            ->where('enabled = "yes"')
                            ->fetchAll();

        return $ids;
    }

    /**
     * @param $torrent_pass
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get_user_from_torrent_pass(string $torrent_pass)
    {
        if (strlen($torrent_pass) != 64) {
            return false;
        }
        $userid = $this->cache->get('torrent_pass_' . $torrent_pass);
        if ($userid === false || is_null($userid)) {
            $userid = $this->fluent->from('users')
                                   ->select(null)
                                   ->select('id')
                                   ->where('torrent_pass = ?', $torrent_pass)
                                   ->fetch('id');
            $this->cache->set('torrent_pass_' . $torrent_pass, $userid, 86400);
        }
        if (empty($userid)) {
            return false;
        }
        $user = $this->getUserFromId((int) $userid);
        if (!$user) {
            return false;
        }

        return $user;
    }

    /**
     * @param int $category
     *
     * @throws Exception
     *
     * @return array
     */
    public function get_users_for_notifications(int $category)
    {
        $users = $this->fluent->from('users')
                              ->select(null)
                              ->select('id')
                              ->select('notifs')
                              ->where('notifs IS NOT NULL');

        $notify = [];
        foreach ($users as $user) {
            if (strpos($user['notifs'], '[cat' . $category . ']') !== false) {
                $notify[] = $user;
            }
        }

        return $notify;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get_latest_user()
    {
        $this->cache->delete('latestuser_');
        $userid = $this->cache->get('latestuser_');
        if ($userid === false || is_null($userid)) {
            $userid = $this->fluent->from('users')
                                   ->select(null)
                                   ->select('id')
                                   ->where('status = 0')
                                   ->where('perms < ?', bt_options::PERMS_STEALTH)
                                   ->orderBy('id DESC')
                                   ->fetch('id');

            $this->cache->set('latestuser_', $userid, $this->site_config['expires']['latestuser']);
        }

        return $userid;
    }

    /**
     * @throws Exception
     * @throws NotLoggedInException
     * @throws AuthError
     */
    public function logout()
    {
        if (!empty($this->auth->getUserId())) {
            $this->fluent->deleteFrom('ajax_chat_online')
                         ->where('userID = ?', $this->auth->getUserId())
                         ->execute();
        }

        $this->auth->logOutEverywhere();
        $this->auth->destroySession();
    }

    /**
     * @param string $email
     * @param string $password
     * @param int    $remember
     * @param array  $lang
     *
     * @throws AttemptCancelledException
     * @throws AuthError
     *
     * @return bool
     */
    public function login(string $email, string $password, int $remember, array $lang)
    {
        $duration = null;
        if ($remember === 1) {
            $duration = (int) $this->site_config['expires']['remember_me'] * 60 * 60 * 24;
        }

        try {
            $this->auth->login($email, $password, $duration);

            return true;
        } catch (InvalidEmailException $e) {
            $this->session->set('is-warning', $lang['login_email_pass_incorrect']);

            return false;
        } catch (InvalidPasswordException $e) {
            $this->session->set('is-warning', $lang['login_email_pass_incorrect']);

            return false;
        } catch (EmailNotVerifiedException $e) {
            $this->session->set('is-warning', $lang['login_not_verified']);

            return false;
        } catch (TooManyRequestsException $e) {
            $this->session->set('is-warning', $lang['login_too_many']);

            return false;
        }
    }

    /**
     * @param $lang
     * @param $post
     *
     * @throws AuthError
     * @throws DependencyException
     * @throws Exception
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws NotLoggedInException
     */
    public function reset_password($lang, $post)
    {
        try {
            $this->auth->resetPassword($post['selector'], $post['token'], $post['password']);

            stderr($lang['stderr_successhead'], 'Password has been reset');
        } catch (InvalidSelectorTokenPairException $e) {
            stderr($lang['stderr_errorhead'], 'Invalid token');
        } catch (TokenExpiredException $e) {
            stderr($lang['stderr_errorhead'], 'Token expired');
        } catch (ResetDisabledException $e) {
            stderr($lang['stderr_errorhead'], 'Password reset is disabled');
        } catch (InvalidPasswordException $e) {
            stderr($lang['stderr_errorhead'], 'Invalid password');
        } catch (TooManyRequestsException $e) {
            stderr($lang['stderr_errorhead'], 'Too many requests');
        }
    }

    /**
     * @param $email
     * @param $lang
     *
     * @throws AuthError
     * @throws DependencyException
     * @throws Exception
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws NotLoggedInException
     */
    public function create_reset($email, $lang)
    {
        try {
            $this->auth->forgotPassword($email, function ($selector, $token) {
                global $lang, $email;

                $body = sprintf($lang['email_request'], $email, getip(), $this->site_config['paths']['baseurl'], urlencode($selector), urlencode($token), $this->site_config['site']['name']);
                send_mail($email, "{$this->site_config['site']['name']} {$lang['email_subjreset']}", $body, strip_tags($body));
            });
            stderr($lang['stderr_successhead'], $lang['stderr_confmailsent']);
        } catch (InvalidEmailException $e) {
            stderr($lang['stderr_errorhead'], $lang['stderr_invalidemail']);
        } catch (EmailNotVerifiedException $e) {
        } catch (ResetDisabledException $e) {
            stderr($lang['stderr_errorhead'], 'Password reset is disabled');
        } catch (TooManyRequestsException $e) {
            stderr($lang['stderr_errorhead'], 'Too many requests');
        }
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     * @throws UnbegunTransaction
     */
    public function update_last_access(int $userid)
    {
        $user = $this->getUserFromId($userid);
        if (!empty($user)) {
            $new_time = TIME_NOW - $user['last_access_numb'];
            $update_time = 0;
            if ($new_time < 300) {
                $update_time = $new_time;
            }
            $where = $this->container->get('where');
            $request = $_SERVER['REQUEST_URI'] === '/' ? '/index.php' : $_SERVER['REQUEST_URI'];
            if (preg_match('/\/(.*?)\.php/is', $request, $whereis_temp)) {
                if (isset($where[$whereis_temp[1]])) {
                    $whereis = sprintf($where[$whereis_temp[1]], $user['username'], htmlsafechars($request));
                } else {
                    $whereis = sprintf($where['unknown'], $user['username']);
                }
            } else {
                $whereis = sprintf($where['unknown'], $user['username']);
            }
            $this->session->set('last_access', TIME_NOW);
            if ($user['last_access'] < (TIME_NOW - 90)) {
                $set = [
                    'where_is' => $whereis,
                    'last_access' => TIME_NOW,
                    'onlinetime' => $user['onlinetime'] + $update_time,
                    'last_access_numb' => TIME_NOW,
                ];
                $this->update($set, $user['id']);
            }
        }
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws Exception
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('users', $values)
                     ->onDuplicateKeyUpdate($update)
                     ->execute();
    }
}
