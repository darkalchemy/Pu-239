<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UserAlreadyExistsException;
use Pu239\Cache;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
global $container, $site_config;

$auth = $container->get(Auth::class);
try {
    if ($emails = $auth->confirmEmail($_GET['selector'], $_GET['token'])) {
        $session = $container->get(Session::class);
        if (empty($emails[0])) {
            $session->set('is-success', 'Your email has been confirmed');
        } else {
            $session->set('is-success', "Your email has been changed to {$emails[1]}");
        }
        $cache = $container->get(Cache::class);
        $cache->delete('user_' . $auth->getUserId());
    }
} catch (UserAlreadyExistsException $e) {
    die('Email address already exists');
} catch (TooManyRequestsException $e) {
    die('Too many requests');
}

header("Refresh: 0; url={$site_config['paths']['baseurl']}/usercp.php?action=security");
