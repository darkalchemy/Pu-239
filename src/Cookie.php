<?php

namespace Pu239;

use Blocktrail\CryptoJSAES\CryptoJSAES;

/**
 * Class Cookie.
 */
class Cookie
{
    protected $site_config;
    protected $cache;
    protected $key;
    protected $fluent;

    /**
     * Cookie constructor.
     *
     * @param $key
     */
    public function __construct($key)
    {
        global $site_config, $cache, $fluent;

        $this->site_config = $site_config;
        $this->cache = $cache;
        $this->key = $key;
        $this->fluent = $fluent;
    }

    /**
     * @param $value
     * @param $expires
     *
     * @return bool
     */
    public function set(string $value, int $expires)
    {
        if (empty($this->key) || empty($value)) {
            return false;
        }
        $params = session_get_cookie_params();
        $encrypted = CryptoJSAES::encrypt($value, $this->site_config['site']['salt']);
        setcookie($this->site_config['cookie_prefix'] . $this->key, base64_encode($encrypted), $expires, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    /**
     * @return bool|string
     */
    public function get()
    {
        if (empty($this->key) || empty($_COOKIE[$this->site_config['cookie_prefix'] . $this->key])) {
            return false;
        }
        $decrypted = CryptoJSAES::decrypt(base64_decode($_COOKIE[$this->site_config['cookie_prefix'] . $this->key]), $this->site_config['site']['salt']);

        return $decrypted;
    }

    /**
     * @return array
     */
    public function getToken()
    {
        $cookies = $this->get();
        if ($cookies) {
            return explode(':', $cookies);
        }
    }

    /**
     * @throws \Exception
     */
    public function reset_expire()
    {
        $cookie = $this->getToken();
        if (!empty($cookie[0]) && !empty($cookie[1]) && !empty($cookie[2])) {
            $selector = $cookie[0];
            $validator = $cookie[1];
            $expires = (int) $cookie[2];

            $this->set("$selector:$validator:$expires", TIME_NOW + $expires);

            $set = [
                'expires' => date('Y-m-d H:i:s', TIME_NOW + $expires),
            ];
            $this->fluent->update('auth_tokens')
                         ->set($set)
                         ->where('selector = ?', $selector)
                         ->execute();
        }
    }
}
