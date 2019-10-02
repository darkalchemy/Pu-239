<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Delight\Auth\NotLoggedInException;
use Delight\Auth\TooManyRequestsException;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_returnto.php';

$user = check_user_status();
global $container, $site_config;

get_template();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session = $container->get(Session::class);
    $auth = $container->get(Auth::class);
    $url = get_return_to($_POST['page']);
    if (empty($url)) {
        $session->set('is-warning', _('Invalid Page Requested.'));
        header("Location: {$site_config['paths']['baseurl']}/index.php");
        die();
    }
    try {
        if ($auth->reconfirmPassword($_POST['password'])) {
            $session->set('is-success', _('Your identity has been confirmed.'));
            $session->set('auth_remembered', false, false);
            header("Location: {$url}");
            die();
        } else {
            $auth->logOutEverywhere();
            $session->set('is-danger', _('Password verification failed.'));
            header("Location: {$site_config['paths']['baseurl']}/login.php");
            die();
        }
    } catch (NotLoggedInException $e) {
        $session->set('is-danger', _('The user is not signed in.'));
        header("Location: {$site_config['paths']['baseurl']}/login.php");
        die();
    } catch (TooManyRequestsException $e) {
        $session->set('is-danger', _('Too many requests.'));
        header("Location: {$site_config['paths']['baseurl']}/index.php");
        die();
    }
}
$HTMLOUT = "
            <form id='site_login' class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/verify.php' enctype='multipart/form-data' accept-charset='utf-8'>";
$body = "
                <h1 class='has-text-centered'>" . _('Verify Your Identity') . "</h1>
                <div class='columns'>
                    <div class='column is-one-quarter'>" . _('Password') . "</div>
                    <div class='column'>
                        <input type='password' class='w-100' name='password' autocomplete='on' placeholder='" . _('Password') . "' required>
                        <input type='hidden' name='page' value='{$_GET['page']}'>
                    </div>
                </div>
                <div class='has-text-centered'>
                    <input id='login' type='submit' value='Verify' class='button is-small'>
                </div>";

$HTMLOUT .= main_div($body, '', 'padding20') . '
            </div>
        </form>';
$title = _('Verify Identity');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
