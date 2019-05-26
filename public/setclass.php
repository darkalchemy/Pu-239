<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('setclass'));
global $container, $site_config, $CURUSER;

$HTMLOUT = '';
if ($CURUSER['class'] < UC_STAFF || $CURUSER['override_class'] != 255) {
    stderr('Error', 'whats the story?');
}
if (isset($_GET['action']) && htmlsafechars($_GET['action']) === 'editclass') {
    $newclass = (int) $_GET['class'];
    $returnto = htmlsafechars($_GET['returnto']);
    $set = [
        'override_class' => $newclass,
    ];
    $user_stuffs = $container->get(User::class);
    $user_stuffs->update($set, $CURUSER['id']);
    $fluent = $container->get(Database::class);
    $fluent->deleteFrom('ajax_chat_online')
           ->where('userID = ?', $CURUSER['id'])
           ->execute();
    header("Location: {$site_config['paths']['baseurl']}/" . $returnto);
    die();
}

$HTMLOUT .= "
<h2 class='has-text-centered'>{$lang['set_class_allow']}</h2>
<form method='get' action='{$site_config['paths']['baseurl']}/setclass.php' accept-charset='utf-8'>
    <input type='hidden' name='action' value='editclass'>
    <input type='hidden' name='returnto' value='userdetails.php?id=" . (int) $CURUSER['id'] . "'>";

$text = "
    <div class='has-text-centered padding20'>
        <label for='name'>Class</label>
        <span class='margin20'>
            <select name='class'>";
$maxclass = $CURUSER['class'] - 1;
for ($i = 0; $i <= $maxclass; ++$i) {
    if (trim(get_user_class_name($i)) != '') {
        $text .= "
                <option value='$i'>" . get_user_class_name($i) . '</option>';
    }
}
$text .= "
            </select>
        </span>
        <div class='top20'>
            <input type='submit' class='button is-small' value='{$lang['set_class_ok']}'>
        </div>
    </div>";

$HTMLOUT .= main_div($text) . '
</form>';
echo stdhead("{$lang['set_class_temp']}") . $HTMLOUT . stdfoot();
