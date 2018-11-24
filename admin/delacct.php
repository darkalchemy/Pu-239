<?php

require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'password_functions.php';
require_once INCL_DIR . 'function_account_delete.php';
require_once INCL_DIR . 'html_functions.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $lang, $site_config, $cache, $session;

$lang = array_merge($lang, load_language('ad_delacct'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = (int) trim(htmlsafechars($_POST['userid']));
    $username = trim(htmlsafechars($_POST['username']));
    if (empty($username) || empty($userid)) {
        stderr($lang['text_error'], $lang['text_please']);
    }
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
            <form method='post' action='staffpanel.php?tool=delacct&amp;action=delacct' onsubmit='return deleteConfirm();'>
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
echo stdhead("{$lang['stdhead_delete']}") . wrapper($HTMLOUT) . stdfoot();
