<?php

namespace Pu239;

/**
 * Class Session.
 */
class Session
{
    private $site_config;
    private $cache;
    private $fluent;
    private $cookies;
    private $user_stuffs;

    public function __construct()
    {
        global $site_config, $cache, $fluent;

        $this->site_config = $site_config;
        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->user_stuffs = new User();
        $this->cookies = new Cookie('remember');
    }

    /**
     * @return bool
     *
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function start()
    {
        $expires = (int) $this->site_config['cookie_lifetime'] * 60;

        if (!session_id()) {
            // Set the session name:
            session_name($this->site_config['sessionName']);

            $secure_session = get_scheme() === 'https' ? true : false;
            $domain = $this->site_config['cookie_domain'] === $this->site_config['domain'] ? '' : $this->site_config['cookie_domain'];

            // Set session cookie parameters:
            session_set_cookie_params($expires, $this->site_config['cookie_path'], $domain, $secure_session, true);

            // enforce php settings before start session
            if (ini_get('memory_limit') != 0) {
                $current = $this->convert_to_bytes(ini_get('memory_limit'));
                if ($current < 1024 * 1024 * 512) {
                    ini_set('memory_limit', '512M');
                }
            }
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_trans_sid', 0);
            ini_set('default_charset', $this->site_config['char_set']);
            ini_set('session.lazy_write', 0);
            ini_set('max_execution_time', 300);
            if (ini_get('session.save_handler') != 'files') {
                ini_set('session.sid_length', 256);
            } else {
                ini_set('session.sid_length', 128);
            }

            // Start the session:
            if (!@session_start()) {
                $this->destroy();

                return false;
            }
        }

        if (!session_id()) {
            $this->destroy();

            return false;
        }

        $this->set('LoggedIn', true);

        if (!$this->get('canary')) {
            $this->set('canary', TIME_NOW);
        }

        if (!$this->get('auth')) {
            $this->set('auth', bin2hex(random_bytes(32)));
        }

        if (!$this->get($this->site_config['session_csrf'])) {
            $this->set($this->site_config['session_csrf'], bin2hex(random_bytes(32)));
        }

        if ($this->get('canary') <= TIME_NOW - 300) {
            $this->cookies->reset_expire();
            session_regenerate_id(true);
            $this->set('canary', TIME_NOW);
        }

        return true;
    }

    /**
     * @param string      $key
     * @param             $value
     * @param string|null $prefix
     */
    public function set(string $key, $value, string $prefix = null)
    {
        if ($prefix === null) {
            $prefix = $this->site_config['sessionKeyPrefix'];
        }
        if (in_array($key, $this->site_config['notifications'])) {
            $current = $this->get($key);
            if ($current) {
                if (!in_array($value, $current)) {
                    $_SESSION[$prefix . $key] = array_merge($current, [$value]);
                }
            } else {
                $_SESSION[$prefix . $key] = [$value];
            }
        } else {
            $this->unset($key);
            $_SESSION[$prefix . $key] = $value;
        }
    }

    /**
     * @param string      $key
     * @param string|null $prefix
     *
     * @return |null
     */
    public function get(string $key, string $prefix = null)
    {
        if (empty($key)) {
            return null;
        }

        if ($prefix === null) {
            $prefix = $this->site_config['sessionKeyPrefix'];
        }

        if (isset($_SESSION[$prefix . $key])) {
            return $_SESSION[$prefix . $key];
        } else {
            return null;
        }
    }

    /**
     * @param string      $key
     * @param string|null $prefix
     */
    public function unset(string $key, string $prefix = null)
    {
        if ($prefix === null) {
            $prefix = $this->site_config['sessionKeyPrefix'];
        }

        unset($_SESSION[$prefix . $key]);
    }

    /**
     * @param string      $token
     * @param string|null $key
     * @param bool        $regen
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function validateToken(string $token, string $key = null, bool $regen = false)
    {
        if ($key === null) {
            $key = $this->site_config['session_csrf'];
        }
        if (empty($token)) {
            return false;
        }

        if (hash_equals($this->get($key), $token)) {
            if ($regen) {
                $this->unset($key);
                $this->set($key, bin2hex(random_bytes(32)));
            }

            return true;
        }

        return false;
    }

    /**
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function destroy()
    {
        global $CURUSER;

        $userID = $CURUSER['id'];
        if ($userID) {
            $this->user_stuffs->delete_user_cache([
                $userID,
            ]);
            $this->user_stuffs->delete_remember($userID);
        }

        $this->start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie($this->site_config['cookie_prefix'] . 'remember', '', TIME_NOW - 86400, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            setcookie(session_name(), '', TIME_NOW - 86400, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_unset();
        session_destroy();

        $returnto = !empty($_SERVER['REQUEST_URI']) && !preg_match('/logout.php/', $_SERVER['REQUEST_URI']) ? '?returnto=' . urlencode($_SERVER['REQUEST_URI']) : '';
        header("Location: {$this->site_config['baseurl']}/login.php" . $returnto);
        die();
    }

    public function close()
    {
        session_write_close();
    }

    /**
     * @param string $value
     *
     * @return float|int
     */
    private function convert_to_bytes(string $value)
    {
        if (preg_match('/^(\d+)(.)$/', $value, $matches)) {
            switch ($matches[2]) {
                case 'K':
                    return $matches[1] * 1024;
                    break;

                case 'M':
                    return $matches[1] * 1024 * 1024;
                    break;

                case 'G':
                    return $matches[1] * 1024 * 1024 * 1024;
                    break;

                default:
                    return 0;
            }
        }
    }
}
