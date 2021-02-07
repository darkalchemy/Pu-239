<?php

declare(strict_types = 1);

use Aura\Sql\ExtendedPdo;
use Delight\Auth\Auth;
use Delight\I18n\I18n;
use Imdb\Config;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Rakit\Validation\Validator;
use Scriptotek\GoogleBooks\GoogleBooks;

return [
    Auth::class => DI\factory(function (ContainerInterface $c) {
        $pdo = $c->get(PDO::class);
        return new Auth($pdo, null, null, PRODUCTION);
    }),
    PDO::class => DI\factory(function (ContainerInterface $c) {
        $env = $c->get('env');
        $dsn = !$env['db']['use_socket'] ? "{$env['db']['type']}:host={$env['db']['host']};port={$env['db']['port']};dbname={$env['db']['database']};charset={$env['db']['charset']}" : "{$env['db']['type']}:unix_socket={$env['db']['socket']};dbname={$env['db']['database']};charset={$env['db']['charset']}";
        $username = $env['db']['username'];
        $password = $env['db']['password'];
        $attributes = $env['db']['attributes'];
        return new ExtendedPdo($dsn, $username, $password, $attributes);
    }),
    mysqli::class => DI\factory(function (ContainerInterface $c) {
        $env = $c->get('env');
        if ($env['db']['use_socket']) {
            $mysqli = new mysqli($env['db']['host'], $env['db']['username'], $env['db']['password'], $env['db']['database'], 0, $env['db']['socket']);
        } else {
            $mysqli = new mysqli($env['db']['host'], $env['db']['username'], $env['db']['password'], $env['db']['database'], $env['db']['port']);
        }
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }

        return $mysqli;
    }),
    Redis::class => DI\factory(function (ContainerInterface $c) {
        $client = new Redis();
        $env = $c->get('env');
        if (!$env['redis']['use_socket']) {
            $client->connect($env['redis']['host'], $env['redis']['port']);
        } else {
            $client->connect($env['redis']['socket']);
        }
        $client->select($env['redis']['database']);

        return $client;
    }),
    Memcached::class => DI\factory(function (ContainerInterface $c) {
        $client = new Memcached();
        $env = $c->get('env');
        if (!count($client->getServerList())) {
            if (!$env['memcached']['use_socket']) {
                $client->addServer($env['memcached']['host'], $env['memcached']['port']);
            } else {
                $client->addServer($env['memcached']['socket'], 0);
            }
        }

        return $client;
    }),
    GoogleBooks::class => DI\factory(function (ContainerInterface $c) {
        $env = $c->get('env');
        if (!empty($env['api']['google'])) {
            $books = new GoogleBooks([
                'key' => $env['api']['google'],
                'country' => 'US',
            ]);
        } else {
            $books = new GoogleBooks(['country' => 'US']);
        }

        return $books;
    }),
    Config::class => DI\factory(function (ContainerInterface $c) {
        $env = $c->get('env');
        $config = new Config();
        $config->usecache = true;
        $config->usezip = true;
        $config->language = $env['language']['imdb'];
        $config->cachedir = IMDB_CACHE_DIR;
        $config->throwHttpExceptions = 0;
        $config->default_agent = get_random_useragent();

        return $config;
    }),
    PHPMailer::class => DI\factory(function (ContainerInterface $c) {
        $env = $c->get('env');
        if ($env['mail']['smtp_enable']) {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $env['mail']['smtp_host'];
            $mail->SMTPAuth = $env['mail']['smtp_auth'];
            $mail->Username = $env['mail']['smtp_username'];
            $mail->Password = $env['mail']['smtp_password'];
            $mail->SMTPSecure = $env['mail']['smtp_secure'];
            $mail->Port = $env['mail']['smtp_port'];

            return $mail;
        }

        return null;
    }),
    Validator::class => DI\factory(function () {
        return new Validator();
    }),
    I18n::class => DI\factory(function (ContainerInterface $c) {
        $env = $c->get('env');
        return new I18n($env['available_languages']);
    }),
];
