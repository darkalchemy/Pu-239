<?php

namespace DarkAlchemy\Pu239;

use Blocktrail\CryptoJSAES\CryptoJSAES;

class Cookie
{
    private $config;
    private $cache;
    protected $key;

    /**
     * Cookie constructor.
     *
     * @param $key
     *
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function __construct($key)
    {
        global $site_config;
        $this->config = $site_config;
        $this->cache = new Cache();
        $this->key = $key;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    /**
     * @param $value
     * @param $expires
     *
     * @return bool
     */
    public function set($value, $expires)
    {
        if (empty($this->key) || empty($value)) {
            return false;
        }

        $params = session_get_cookie_params();
        $encrypted = CryptoJSAES::encrypt($value, $this->config['site']['salt']);
        setcookie(
            $this->config['cookie_prefix'] . $this->key,
            base64_encode($encrypted),
            $expires,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    /**
     * @return bool|string
     */
    public function get()
    {
        if (empty($this->key) || empty($_COOKIE[$this->config['cookie_prefix'] . $this->key])) {
            return false;
        }
        $decrypted = CryptoJSAES::decrypt(base64_decode($_COOKIE[$this->config['cookie_prefix'] . $this->key]), $this->config['site']['salt']);

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
}
