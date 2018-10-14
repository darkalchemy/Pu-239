<?php

namespace DarkAlchemy\Pu239;

class User
{
    protected $fluent;
    protected $session;
    protected $cookies;
    protected $cache;
    protected $config;
    protected $pdo;
    protected $limit;

    public function __construct()
    {
        global $site_config, $session, $cache, $fluent, $pdo;

        $this->fluent = $fluent;
        $this->session = $session;
        $this->cache = $cache;
        $this->cookies = new Cookie('remember');
        $this->config = $site_config;
        $this->pdo = $pdo;
        $this->limit = $this->config['query_limit'];
    }

    /**
     * @param int  $userid
     * @param bool $fresh
     *
     * @return bool|mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function getUserFromId(int $userid, bool $fresh = false)
    {
        $this->cache->delete('user' . $userid);
        $user = $this->cache->get('user' . $userid);
        if ($fresh || $user === false || is_null($user)) {
            $user = $this->fluent->from('users AS u')
                ->select('INET6_NTOA(u.ip) AS ip')
                ->select('u.bjwins - u.bjlosses AS bj')
                ->select('c.win - c.lost AS casino')
                ->leftJoin('casino AS c ON u.id = c.userid')
                ->where('id = ?', $userid)
                ->fetch();

            if ($user) {
                unset($user['hintanswer'], $user['passhash']);

                if ('Male' === $user['gender']) {
                    $user['it'] = 'he';
                } elseif ('Female' === $user['gender']) {
                    $user['it'] = 'she';
                } else {
                    $user['it'] = 'it';
                }

                $this->cache->set('user' . $userid, $user, $this->config['expires']['user_cache']);
            }
        }

        if (!empty($user) && $user['override_class'] < $user['class']) {
            $user['class'] = $user['override_class'];
        }

        return $user;
    }

    /**
     * @param string $username
     *
     * @return bool|mixed
     *
     * @throws \Envms\FluentPDO\Exception
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

            $this->cache->set('userid_from_' . urldecode($username), $user, $this->config['expires']['user_cache']);
        }

        return $user;
    }

    /**
     * @param string $ip
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function getUsersFromIP(string $ip)
    {
        $users = $this->fluent->from('users')
            ->select(null)
            ->select('id')
            ->select('last_access')
            ->select('added')
            ->select('email')
            ->select('downloaded')
            ->select('uploaded')
            ->select('INET6_NTOA(ip) AS ip')
            ->where('ip = ?', inet_pton($ip))
            ->orderBy('id')
            ->fetchAll();

        foreach ($users as $user) {
            unset($user['hintanswer'], $user['passhash']);

            if ('Male' === $user['gender']) {
                $user['it'] = 'he';
            } elseif ('Female' === $user['gender']) {
                $user['it'] = 'she';
            } else {
                $user['it'] = 'it';
            }
        }

        return $users;
    }

    /**
     * @return int
     *
     * @throws \Envms\FluentPDO\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function getUserId()
    {
        $id = $this->session->get('userID');

        if (!$id) {
            $cookie = $this->cookies->getToken();
            if (!empty($cookie[0]) && !empty($cookie[1]) && !empty($cookie[2])) {
                $selector = $cookie[0];
                $validator = $cookie[1];
                $expires = $cookie[2];
                $stashed = $this->get_remember($selector);
                if (!empty($stashed) && hash_equals($stashed['hashedValidator'], hash('sha256', $validator))) {
                    $id = $stashed['userid'];
                    $this->session->start();
                    $this->session->set('userID', $id);
                    $this->session->set('remembered_by_cookie', true);
                    $this->refresh_remember($selector, $id, $expires);

                    return (int) $id;
                }
            }

            $this->session->destroy();
        }

        return (int) $id;
    }

    /**
     * @param string $username
     *
     * @return bool|mixed
     *
     * @throws \Envms\FluentPDO\Exception
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
                ->where('u.username != ?', $this->config['chatBotName'])
                ->where('u.username LIKE ?', "$username%")
                ->where('c.classname != ""')
                ->orderBy('LOWER(u.username)')
                ->fetchAll();
            $this->cache->set('search_users_' . $username, $users, 86400);
        }

        return $users;
    }

    /**
     * @param array $set
     * @param int   $userid
     * @param bool  $persist
     *
     * @return bool|int|\PDOStatement
     *
     * @throws \Envms\FluentPDO\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
     */
    public function update(array $set, int $userid, bool $persist = true)
    {
        $result = $this->fluent->update('users')
            ->set($set)
            ->where('id = ?', $userid)
            ->execute();

        if ($result && $persist) {
            $this->cache->update_row('user' . $userid, $set, $this->config['expires']['user_cache']);
        }

        return $result;
    }

    /**
     * @param string $selector
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_remember(string $selector)
    {
        $remember = $this->fluent->from('auth_tokens')
            ->where('selector = ?', $selector)
            ->where('expires >= ?', date('Y-m-d H:i:s', TIME_NOW))
            ->fetch();

        return $remember;
    }

    /**
     * @param int $userid
     * @param int $expires
     *
     * @throws \Exception
     */
    public function set_remember(int $userid, int $expires)
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hashedValidator = hash('sha256', $validator);

        $this->cookies->set("$selector:$validator:$expires", TIME_NOW + $expires);

        $this->fluent->deleteFrom('auth_tokens')
            ->where('expires <= ?', date('Y-m-d H:i:s', TIME_NOW))
            ->execute();

        $values = [
            'selector' => $selector,
            'hashedValidator' => $hashedValidator,
            'userid' => $userid,
            'expires' => date('Y-m-d H:i:s', TIME_NOW + $expires),
            'created_at' => date('Y-m-d H:i:s', TIME_NOW),
        ];
        $this->fluent->insertInto('auth_tokens')
            ->values($values)
            ->execute();
    }

    /**
     * @param string $selector
     * @param int    $userid
     * @param int    $expires
     *
     * @throws \Exception
     */
    public function refresh_remember(string $selector, int $userid, int $expires)
    {
        $this->fluent->deleteFrom('auth_tokens')
            ->where('selector = ?', $selector)
            ->execute();

        $this->set_remember($userid, $expires);
    }

    public function delete_remember(int $userid)
    {
        $this->fluent->deleteFrom('auth_tokens')
            ->where('userid = ?', $userid)
            ->execute();
    }

    /**
     * @param array $users
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
                    'userlist_' . $userid,
                    'users_names_' . $username,
                    'user_rep_' . $userid,
                    'user_snatches_data_' . $userid,
                    'userstatus_' . $userid,
                ]);
            }
        }
    }

    /**
     * @param string $item
     * @param int    $userid
     *
     * @return mixed
     */
    public function get_item(string $item, int $userid)
    {
        $user = $this->getUserFromId($userid);

        return $user[$item];
    }

    /**
     * @param string $username
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_login(string $username)
    {
        $row = $this->fluent->from('users')
            ->select(null)
            ->select('id')
            ->select('INET6_NTOA(ip) AS ip')
            ->select('passhash')
            ->select('perms')
            ->select('enabled')
            ->select('status')
            ->where('username = ?', $username)
            ->fetch();

        return $row;
    }

    /**
     * @param $class
     * @param $bot
     * @param $torrent_pass
     * @param $auth
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_bot_id($class, $bot, $torrent_pass, $auth)
    {
        $userid = $this->fluent->from('users')
            ->select(null)
            ->select('id')
            ->where('class >= ?', $class)
            ->where('username = ?', $bot)
            ->where('auth = ?', $auth)
            ->where('torrent_pass = ?', $torrent_pass)
            ->where('uploadpos = 1 AND suspended = "no"')
            ->fetch('id');

        return $userid;
    }

    /**
     * @param array $values
     *
     * @return int
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function add(array $values)
    {
        $id = $this->fluent->insertInto('users')
            ->values($values)
            ->execute();

        return $id;
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function insert(array $values, array $update)
    {
        $count = floor($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $this->fluent->insertInto('users', $t)
                ->onDuplicateKeyUpdate($update)
                ->execute();
        }
    }

    /**
     * @param $date
     *
     * @return array|\PDOStatement
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_birthday_users($date)
    {
        $results = $this->fluent->from('users')
            ->select(null)
            ->select('id')
            ->select('class')
            ->select('username')
            ->select('uploaded')
            ->select('email')
            ->select('INET6_NTOA(ip) AS ip')
            ->where('MONTH(birthday) = ?', $date['mon'])
            ->where('DAYOFMONTH(birthday) = ?', $date['mday'])
            ->fetchAll();

        return $results;
    }

    /**
     * @return array|\PDOStatement
     *
     * @throws \Envms\FluentPDO\Exception
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
     * @return bool|mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_user_from_torrent_pass($torrent_pass)
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
                ->where("enabled = 'yes'")
                ->fetch();
            $userid = $userid['id'];
            $this->cache->set('torrent_pass_' . $torrent_pass, $userid, 86400);
        }
        if (empty($userid)) {
            return false;
        }
        $user = $this->getUserFromId($userid);
        if (!$user) {
            return false;
        }

        return $user;
    }
}
