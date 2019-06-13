<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_password.php';
require_once INCL_DIR . 'function_account_delete.php';
require_once INCL_DIR . 'function_html.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_delacct'));
global $container, $CURUSER, $site_config;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = (int) trim($_POST['userid']);
    $username = trim(htmlsafechars((string) $_POST['username']));
    if (empty($username) || empty($userid)) {
        stderr($lang['text_error'], $lang['text_please']);
    }
    $fluent = $container->get(Database::class);
    $id = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('username = ?', $username)
        ->where('id = ?', $userid)
        ->fetch('id');

    if (!$id) {
        stderr($lang['text_error'], $lang['text_invalid']);
    }

    if (account_delete($id)) {
        write_log("User: $username Was deleted by {$CURUSER['username']}");
        $session = $container->get(Session::class);
        $session->set('is-success', $lang['text_success']);
    } else {
        stderr($lang['text_error'], $lang['text_unable']);
    }
}

$HTMLOUT = "
<script>
    function deleteConfirm(){
        var result = confirm('Are you sure to delete this user?');
        if (result) {
            return true;
        } else {
            return false;
        }
    }
</script>
<div class='row'>
    <div class='col-md-12'>
        <h1 class='has-text-centered'>{$lang['text_delete']}</h1>
            <form method='post' action='{$_SERVER['PHP_SELF']}?tool=delacct&amp;action=delacct' onsubmit='return deleteConfirm();' accept-charset='utf-8'>
                <table class='table table-bordered'>
                    <tr>
                        <td class='rowhead'>{$lang['table_userid']}</td>
                        <td><input class='w-100' name='userid'></td>
                    </tr>
                    <tr>
                        <td class='rowhead'>{$lang['table_username']}</td>
                        <td><input class='w-100' name='username'></td>
                    </tr>
                    <tr>
                        <td colspan='2' class='has-text-centered'><input type='submit' class='button is-small' value='{$lang['btn_delete']}'></td>
                    </tr>
                </table>
            </form>
        </div>
</div>";
echo stdhead($lang['stdhead_delete']) . wrapper($HTMLOUT) . stdfoot();
