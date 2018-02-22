<?php

namespace DarkAlchemy\Pu239;

class User
{
    private $fluent,
        $session,
        $cookies,
        $cache,
        $config;

    /**
     * User constructor.
     *
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function __construct()
    {
        global $site_config;

        $this->fluent = new Database();
        $this->session = new Session();
        $this->cookies = new Cookie('remember');
        $this->cache = new Cache();
        $this->config = $site_config;
    }

    /**
     * @param int $user_id
     *
     * @return bool|mixed
     */
    public function getUserFromId(int $user_id)
    {
        $user = $this->cache->get('user' . $user_id);
        if ($user === false || is_null($user)) {
            $user = $this->fluent->from('users')
                ->select('INET6_NTOA(ip) AS ip')
                ->where('id = ?', $user_id)
                ->fetch();

            if ($user) {
                unset($user['hintanswer']);
                unset($user['passhash']);
                if ($user['gender'] === 'Male') {
                    $user['it'] = 'he';
                } elseif ($user['gender'] === 'Female') {
                    $user['it'] = 'she';
                } else {
                    $user['it'] = 'it';
                }

                $this->cache->set('user' . $user_id, $user, $this->config['expires']['user_cache']);
            }
        }

        return $user;
    }

    /**
     * @param $username
     *
     * @return bool|mixed
     */
    public function getUserIdFromName($username)
    {
        $user = $this->cache->get('userid_from_' . urlencode($username));

        if ($user === false || is_null($user)) {
            $user = $this->fluent->from('users')
                ->select(null)
                ->select('id')
                ->where('username = ?', $username)
                ->fetch('id');

            $this->cache->set('userid_from_' . urldecode($username), $user, $this->config['expires']['user_cache']);
        }

        return $user;

    }

    /**
     * @return bool|int
     * @throws Exception
     * @throws \Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function getUserId()
    {
        $id = $this->session->get('userID');

        if (!$id) {
            $cookie = $this->cookies->getToken();
            if ($cookie) {
                $stashed = $this->cache->get('remember_' . $cookie[0]);
                $validator = $cookie[1];
                if (empty($stashed)) {
                    $this->session->destroy();
                    return false;
                }
                if (hash_equals($stashed['hash'], hash('sha512', $validator))) {
                    $id = $stashed['uid'];
                    $this->session->set('userID', $id);
                    $this->session->set('remembered_by_cookie', true);
                    return (int)$id;
                } else {
                    $this->cache->delete('remember_' . $cookie[0]);
                    $this->session->destroy();
                    return false;
                }
            }
            $this->session->destroy();
            return false;
        }
        return (int)$id;
    }

    /**
     * @param int   $user_id
     * @param array $set
     *
     * @return \PDOStatement
     * @throws \Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
     */
    public function update(int $user_id, array $set)
    {
        $result = $this->fluent->update('users')
            ->set($set)
            ->where('id = ?', $user_id)
            ->execute();

        if ($result) {
            $this->cache->update_row('user' . $user_id,
                                     $set,
                                     $this->config['expires']['user_cache']);
        }

        return $result;
    }
}
