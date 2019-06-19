<?php

declare(strict_types = 1);

/*
Any changes to this file will require that the DI_CACHE_DIR be emptied
else changes won't be reflected
you can do this be deleting the dir or run:
php bin/clear_cache.php
*/
$upload_max_filesize = ini_get('upload_max_filesize') !== null ? return_bytes(ini_get('upload_max_filesize')) : 0;
$post_max_filesize = ini_get('post_max_filesize') !== null ? return_bytes(ini_get('post_max_filesize')) : 0;

return [
    'env' => [
        'mail' => [
            'smtp_enable' => true,
            'smtp_host' => 'smtp.gmail.com',
            'smtp_auth' => true,
            'smtp_username' => 'gmail username',
            'smtp_password' => 'gmail password',
            'smtp_secure' => 'tls',
            'smtp_port' => 587,
        ],
        'db' => [
            'type' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'socket' => '/var/run/mysqld/mysqld.sock',
            'database' => '#mysql_db',
            'username' => '#mysql_user',
            'password' => '#mysql_pass',
            'charset' => 'utf8mb4',
            'use_socket' => false,
            'query_limit' => 65536,
            'attributes' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ],
            'debug' => true,
        ],
        'cache' => [
            'driver' => 'memory',
            'prefix' => '#cookie_prefix',
        ],
        'redis' => [
            'host' => '127.0.0.1',
            'password' => null,
            'port' => 6379,
            'database' => 1,
            'socket' => '/dev/shm/redis.sock',
            'use_socket' => false,
        ],
        'files' => [
            'path' => '/dev/shm/#mysql_db',
        ],
        'memcached' => [
            'host' => '127.0.0.1',
            'port' => 11211,
            'socket' => '/dev/shm/memcached.sock',
            'use_socket' => false,
        ],
        'paths' => [
            'flood_file' => CACHE_DIR . 'floodlimits.txt',
            'nameblacklist' => CACHE_DIR . 'nameblacklist.txt',
            'happyhour' => CACHE_DIR . 'happyhour.cache',
            'sql_error_log' => SQLERROR_LOGS_DIR . 'sql_err_' . date('Y_m_d', TIME_NOW) . '.log',
            'baseurl' => get_scheme() . '://' . (!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '#baseurl'),
            'images_baseurl' => '.' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR,
            'chat_images_baseurl' => get_scheme() . '://' . (!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '#baseurl') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR,
            'log_viewer' => [
                '/var/log/apache2/',
                '/var/log/nginx/',
                '/var/log/mysql/',
            ],
        ],
        'bucket' => [
            'maxsize' => $upload_max_filesize >= $post_max_filesize ? $upload_max_filesize : $post_max_filesize,
            'allowed' => true,
        ],
        'language' => [
            'imdb' => 'en-US',
        ],
        'api' => [
            'sentry' => '',
        ],
        'webserver' => [
            'username' => 'www-data',
        ],
    ],
];

/**
 * @param $val
 *
 * @return int|string
 */
function return_bytes($val)
{
    if ($val == '') {
        return 0;
    }
    $val = strtolower(trim($val));
    $last = $val[strlen($val) - 1];
    $val = rtrim($val, $last);

    switch ($last) {
        case 'g':
            $val *= (1024 * 1024 * 1024);
            break;
        case 'm':
            $val *= (1024 * 1024);
            break;
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}

/**
 * @return mixed
 */
function get_scheme()
{
    global $site_config;

    if (isset($site_config['site']['https_only']) && $site_config['site']['https_only']) {
        return 'https';
    } elseif (isset($_SERVER['REQUEST_SCHEME'])) {
        return $_SERVER['REQUEST_SCHEME'];
    } elseif (isset($_SERVER['HTTPS'])) {
        return 'https';
    } elseif (isset($_SERVER['REQUEST_URI'])) {
        $url = parse_url($_SERVER['REQUEST_URI']);

        return $url[0];
    }

    return 'http';
}
