<?php

declare(strict_types = 1);

namespace Pu239;

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
use Envms\FluentPDO\Queries\Select;
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
     *
     * @param string $username
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function getUserIdFromName(string $username)
    {
        $user = $this->cache->get('userid_from_' . strtolower($username));
        if ($user === false || is_null($user)) {
            $user = $this->fluent->from('users')
                                 ->select(null)
                                 ->select('id')
                                 ->where('LOWER(username) = ?', strtolower($username))
                                 ->fetch('id');

            $this->cache->set('userid_from_' . strtolower($username), $user, $this->site_config['expires']['user_cache']);
        }

        return $user;
    }

    /**
     *
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
     *
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
     *
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
                                 ->select('a.achpoints')
                                 ->select('a.spentpoints')
                                 ->leftJoin('casino AS c ON u.id = c.userid')
                                 ->leftJoin('usersachiev AS a ON u.id = a.userid')
                                 ->where('u.id = ?', $userid)
                                 ->orderBy('u.last_access DESC')
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
                $user['seedbonus'] = (float) $user['seedbonus'];
                $user['blocks'] = $this->userblock->get($userid);

                $this->cache->set('user_' . $userid, $user, $this->site_config['expires']['user_cache']);
            }
        }

        if (!empty($user) && $user['override_class'] < $user['class']) {
            $user['class'] = $user['override_class'];
        }

        return $user;
    }

    /**
     *
     * @param array $items
     * @param array $where
     *
     * @throws Exception
     *
     * @return array|bool|Select
     */
    public function search(array $items, array $where)
    {
        $users = $this->fluent->from('users')
                              ->select(null);
        foreach ($items as $item) {
            $users = $users->select($item);
        }
        foreach ($where as $key => $value) {
            $users = $users->where($key . ' ?', $value);
        }
        $users = $users->fetchAll();

        return $users;
    }

    /**
     *
     * @param string $bot
     * @param string $torrent_pass
     * @param string $auth
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_bot_id(string $bot, string $torrent_pass, string $auth)
    {
        $userid = $this->fluent->from('users')
                               ->select(null)
                               ->select('id')
                               ->where('roles_mask & ?', Roles::UPLOADER)
                               ->where('username = ?', $bot)
                               ->where('auth = ?', $auth)
                               ->where('torrent_pass = ?', $torrent_pass)
                               ->where('uploadpos = 1')
                               ->fetch('id');

        return $userid;
    }

    /**
     *
     * @param array $values
     *
     * @throws AuthError
     * @throws DependencyException
     * @throws Exception
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws UnbegunTransaction
     * @throws \Exception
     *
     * @return bool|int
     */
    public function add(array $values)
    {
        $userid = false;
        try {
            if ($this->site_config['mail']['smtp_enable'] && $this->site_config['signup']['email_confirm'] && !isset($values['send_email'])) {
                $userid = $this->auth->registerWithUniqueUsername(strip_tags(trim($values['email'])), strip_tags(trim($values['password'])), strip_tags(trim($values['username'])), function ($selector, $token) use ($values) {
                    $body = doc_head(_fe('{0} Registration', $this->site_config['site']['name'], false));
                    $body .= _fe('
</head>
<body>
    <p>You have requested a new user account on {0} and you have specified this address ({1}) as user contact.</p>
    <p>If you did not do this, please ignore this email. The person who entered your email address had the IP address {2}. Please do not reply.</p>
    <p>To confirm your user registration, you have to follow this link:</p>
    <p>{3}</p>
    <p>After you do this, you will be able to use your new account. If you fail to do this, your account will be deleted within 24 hours. We urge you to read the {4}RULES{5} and {6}FAQ{5} before you start using {0}.</p>
</body>
</html>', $this->site_config['site']['name'], strip_tags($values['email']), getip(0), $this->site_config['paths']['baseurl'] . '/verify_email.php?selector=' . htmlsafechars($selector) . '&token=' . urlencode($token), "<a href='{$this->site_config['paths']['baseurl']}/rules.php'>", '</a>', "<a href='{$this->site_config['paths']['baseurl']}/faq.php'>");
                    send_mail(strip_tags($values['email']), "{$this->site_config['site']['name']} " . _('user registration confirmation'), $body, strip_tags($body));
                    $this->session->set('is-success', 'We will send a confirmation email to ' . strip_tags($values['email']));
                });
            } else {
                $userid = $this->auth->registerWithUniqueUsername(strip_tags($values['email']), strip_tags($values['password']), strip_tags($values['username']));
            }
        } catch (DuplicateUsernameException $e) {
            stderr(_('Error'), _('Username already exists'));
        } catch (InvalidEmailException $e) {
            stderr(_('Error'), _('Invalid email address'));
        } catch (InvalidPasswordException $e) {
            stderr(_('Error'), _('Invalid password'));
        } catch (UserAlreadyExistsException $e) {
            stderr(_('Error'), _('Email already in use'));
        } catch (TooManyRequestsException $e) {
            stderr(_('Error'), _('Too many requests from your IP'));
        } catch (AuthError $e) {
            stderr(_('Error'), _('Unknown Error'));
        }
        if ($userid !== false) {
            $dt = TIME_NOW;
            $set = [
                'personal_freeleech' => get_date($dt + 14 * 86400, 'MYSQL'),
                'personal_doubleseed' => get_date($dt + 14 * 86400, 'MYSQL'),
                'torrent_pass' => bin2hex(random_bytes(32)),
                'auth' => bin2hex(random_bytes(32)),
                'apikey' => bin2hex(random_bytes(32)),
                'stylesheet' => $this->site_config['site']['stylesheet'],
                'last_access' => $dt,
                'uploaded' => $this->site_config['signup']['upload_credit'],
            ];
            if (!empty($values['invitedby'])) {
                $set['invitedby'] = (int) $values['invitedby'];
            }
            $this->update($set, $userid);
            $this->achieve->add(['userid' => $userid]);
            $this->userblock->add(['userid' => $userid]);

            $this->cache->deleteMulti([
                'birthdayusers_',
                'chat_users_list_',
                'is_staff_',
                'all_users_',
            ]);
            if ($userid > 2 && ($this->site_config['site']['autoshout_chat'] || $this->site_config['site']['autoshout_irc'])) {
                require_once INCL_DIR . 'function_users.php';
                $classname = get_user_class_name(UC_MIN, true);
                $message = "Welcome New {$this->site_config['site']['name']} Member: [" . $classname . ']' . format_comment($values['username']) . '[/' . $classname . ']';
                autoshout($message);
            }

            if (!$this->site_config['signup']['email_confirm']) {
                $this->session->set('is-success', 'You have successfully registered. Please login');
            }
            $this->cache->set('latestuser_', $userid, $this->site_config['expires']['latestuser']);
            write_log('User account ' . $userid . ' (' . format_comment($values['username']) . ') was created');
        }

        return $userid;
    }

    /**
     *
     * @param array $set
     * @param int   $userid
     * @param bool  $persist
     *
     * @throws UnbegunTransaction
     * @throws Exception
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
                            ->where('status = 0')
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
     *
     * @param int $category
     *
     * @throws Exception
     *
     * @return array
     */
    public function get_notifications(int $category)
    {
        $cat = '[cat' . $category . ']';
        $users = $this->fluent->from('users')
                              ->select(null)
                              ->select('id')
                              ->select('email')
                              ->select('notifs')
                              ->where('MATCH (notifs) AGAINST (? IN NATURAL LANGUAGE MODE)', $cat)
                              ->where('MATCH (notifs) AGAINST (? IN NATURAL LANGUAGE MODE)', '[email] [pmail]')
                              ->fetchAll();

        return $users;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get_latest_user()
    {
        require_once CLASS_DIR . 'class_user_options.php';
        $userid = $this->cache->get('latestuser_');
        if ($userid === false || is_null($userid)) {
            $userid = $this->fluent->from('users')
                                   ->select(null)
                                   ->select('id')
                                   ->where('status = 0')
                                   ->where('perms < ?', PERMS_STEALTH)
                                   ->where('anonymous_until < ?', TIME_NOW)
                                   ->where('paranoia < ?', 2)
                                   ->orderBy('id DESC')
                                   ->fetch('id');

            $this->cache->set('latestuser_', $userid, $this->site_config['expires']['latestuser']);
        }

        return $userid;
    }

    /**
     * @param int  $userid
     * @param bool $redirect
     *
     * @throws AuthError
     * @throws Exception
     * @throws NotLoggedInException
     */
    public function logout(int $userid, bool $redirect)
    {
        if (empty($userid)) {
            $userid = $this->auth->getUserId();
        }
        if (!empty($userid)) {
            $this->cache->delete('user_' . $userid);
            $this->fluent->deleteFrom('ajax_chat_online')
                         ->where('userID = ?', $userid)
                         ->execute();
        }
        if ($this->auth->isLoggedIn()) {
            $this->auth->logOutEverywhere();
            $this->auth->destroySession();
        }
        if ($redirect) {
            header('Location: ' . $this->site_config['paths']['baseurl'] . '/login.php');
            die();
        }
    }

    /**
     *
     * @param string $email
     * @param string $password
     * @param int    $remember
     *
     * @throws AttemptCancelledException
     * @throws AuthError
     * @throws DependencyException
     * @throws Exception
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws UnbegunTransaction
     *
     * @return bool
     */
    public function login(string $email, string $password, int $remember)
    {
        $duration = null;
        if ($remember === 1) {
            $duration = (int) $this->site_config['expires']['remember_me'] * 60 * 60 * 24;
        }

        try {
            $this->auth->login($email, $password, $duration);
            $userid = $this->auth->getUserId();
            $this->cache->delete('user_' . $userid);

            return true;
        } catch (InvalidEmailException $e) {
            stderr(_('Error'), _('Your credentials could not be validated.'));
        } catch (InvalidPasswordException $e) {
            stderr(_('Error'), _('Your credentials could not be validated.'));
        } catch (EmailNotVerifiedException $e) {
            stderr(_('Error'), _('You have not verified you email address. Please check your email and click the link to verify it.'));
        } catch (TooManyRequestsException $e) {
            stderr(_('Error'), _('Too many requests from your IP'));
        }
    }

    /**
     *
     * @param array $post
     * @param bool  $return
     *
     * @throws AuthError
     * @throws DependencyException
     * @throws Exception
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws UnbegunTransaction
     *
     * @return bool
     */
    public function reset_password(array $post, bool $return)
    {
        try {
            $this->auth->resetPassword($post['selector'], $post['token'], $post['password']);
        } catch (InvalidSelectorTokenPairException $e) {
            stderr(_('Error'), _('Invalid token'));
        } catch (TokenExpiredException $e) {
            stderr(_('Error'), _('Token expired'));
        } catch (ResetDisabledException $e) {
            stderr(_('Error'), _('Password reset is disabled'));
        } catch (InvalidPasswordException $e) {
            stderr(_('Error'), _('Invalid password'));
        } catch (TooManyRequestsException $e) {
            stderr(_('Error'), _('Too many requests from your IP'));
        }
        if ($return) {
            return true;
        }
        $this->session->set('is-success', _('Password has been reset'));
        header('Location: ' . $this->site_config['paths']['baseurl']);
        die();
    }

    /**
     * @param string $email
     *
     * @throws AuthError
     * @throws DependencyException
     * @throws Exception
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws UnbegunTransaction
     */
    public function create_reset(string $email)
    {
        try {
            $this->auth->forgotPassword($email, function ($selector, $token) use ($email) {
                $body = doc_head(_fe('{0} Reset Password Request', $this->site_config['site']['name'], false));
                $body .= _fe('
</head>
<body>
<p>Someone, hopefully you, requested that the password for the account associated with this email address ({0}) be reset.</p>
<p>The request originated from {1}.</p>
<p>If you did not do this, you can ignore this email. Please do not reply.</p>
<p>Should you wish to confirm this request, please follow this link:</p>
<br>
<p><b>{2}</b></p>
<br>
<p>After you do this, you will be able to log with the new password.</p>
<p>--{3}</p>
</body>
</html>', $email, getip(0), "{$this->site_config['paths']['baseurl']}/recover.php?selector=" . urlencode($selector) . '&token=' . urlencode($token), $this->site_config['site']['name']);
                send_mail($email, "{$this->site_config['site']['name']} " . _('password reset confirmation'), $body, strip_tags($body));
            });
            stderr(_('Success'), _('If the email address exists, a confirmation email will be sent. Please allow a few minutes for the mail to arrive.'));
        } catch (InvalidEmailException $e) {
            stderr(_('Success'), _('If the email address exists, a confirmation email will be sent. Please allow a few minutes for the mail to arrive.'));
        } catch (EmailNotVerifiedException $e) {
            stderr(_('Error'), _('Email has not been verified.'));
        } catch (ResetDisabledException $e) {
            stderr(_('Error'), _('Password reset is disabled.'));
        } catch (TooManyRequestsException $e) {
            stderr(_('Error'), _('Too many requests from your IP'));
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

    /**
     *
     * @param int $registered
     * @param int $last_access
     * @param int $parked
     * @param int $class
     *
     * @throws Exception
     *
     * @return array
     */
    public function get_inactives(int $registered, int $last_access, int $parked, int $class)
    {
        $botid = $this->site_config['chatbot']['id'];

        $group1 = $this->fluent->from('users')
                               ->select(null)
                               ->select('id')
                               ->where('verified = 0')
                               ->where('class < ?', $class)
                               ->where('registered < ?', $registered)
                               ->where('id != ?', $botid)
                               ->fetchAll();
        $group1 = !empty($group1) ? $group1 : [];
        $group2 = $this->fluent->from('users')
                               ->select(null)
                               ->select('id')
                               ->where('immunity = "no"')
                               ->where('status = 0')
                               ->where('class < ?', $class)
                               ->where('last_access < ?', $last_access)
                               ->where('id != ?', $botid)
                               ->fetchAll();
        $group2 = !empty($group2) ? $group2 : [];
        $group3 = $this->fluent->from('users')
                               ->select(null)
                               ->select('id')
                               ->where('immunity = "no"')
                               ->where('status = 1')
                               ->where('class < ?', $class)
                               ->where('last_access < ?', $parked)
                               ->where('id != ?', $botid)
                               ->fetchAll();

        $group3 = !empty($group3) ? $group3 : [];

        return array_merge($group1, $group2, $group3);
    }

    /**
     * @param array $users
     *
     * @throws Exception
     */
    public function delete_users(array $users)
    {
        foreach ($users as $user) {
            $this->fluent->deleteFrom('users')
                         ->where('id', $user)
                         ->execute();

            $this->delete_user_cache($user);
        }
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
                    'user_' . $userid,
                    'useravatar_' . $userid,
                    'userclasses_' . $username,
                    'user_friends_' . $userid,
                    'userhnrs_' . $userid,
                    'users_names_' . $username,
                    'user_rep_' . $userid,
                    'user_snatches_data_' . $userid,
                    'userstatus_' . $userid,
                ]);
                unset($user);
            }
        }
    }

    /**
     * @param string $email
     *
     * @return mixed|string
     */
    public function get_count_by_email(string $email)
    {
        try {
            return $this->fluent->from('users')
                                ->select(null)
                                ->select('COUNT(id) AS count')
                                ->where('email = ?', $email)
                                ->fetch('count');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $username
     *
     * @return mixed|string
     */
    public function get_count_by_username(string $username)
    {
        try {
            return $this->fluent->from('users')
                                ->select(null)
                                ->select('COUNT(id) AS count')
                                ->where('username = ?', $username)
                                ->fetch('count');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $where
     * @param string $by
     *
     * @return mixed|string
     */
    public function get_count(string $where, string $by)
    {
        $allowed_columns = [
            'invitedby',
        ];
        if (!in_array($where, $allowed_columns)) {
            return false;
        }
        try {
            return $this->fluent->from('users')
                                ->select(null)
                                ->select('COUNT(id) AS count')
                                ->where('status = 0')
                                ->where($where . ' = ?', $by)
                                ->fetch('count');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
