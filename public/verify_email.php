<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Delight\Auth\InvalidSelectorTokenPairException;
use Delight\Auth\TokenExpiredException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UserAlreadyExistsException;
use Pu239\Cache;
use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
global $container, $site_config;

get_template();
$session = $container->get(Session::class);
$auth = $container->get(Auth::class);
if ($auth->isLoggedIn()) {
    $auth->logOutEverywhere();
    $auth->destroySession();
}
if (empty($_GET['selector']) || empty($_GET['token'])) {
    stderr(_('Error'), _('Invalid verification link'));
}
try {
    $emails = $auth->confirmEmail($_GET['selector'], $_GET['token']);
} catch (InvalidSelectorTokenPairException $e) {
    stderr(_('Error'), _('Invalid token'));
} catch (TokenExpiredException $e) {
    stderr(_('Error'), _('Token expired'));
} catch (UserAlreadyExistsException $e) {
    stderr(_('Error'), _('Email address already exists'));
} catch (TooManyRequestsException $e) {
    stderr(_('Error'), _('Too many requests from your IP'));
}
if (empty($emails[0])) {
    $session->set('is-success', _('Your email has been confirmed'));
} else {
    $session->set('is-success', _('Your email has been changed to %s', $emails[1]));
}
$cache = $container->get(Cache::class);
$userid = $auth->getUserId();
$user_class = $container->get(User::class);
header("Location: {$site_config['paths']['baseurl']}/usercp.php?action=security");
