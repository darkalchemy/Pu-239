<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$lang = array_merge(load_language('global'), load_language('setclass'));
global $container, $site_config;

$HTMLOUT = '';
if ($user['class'] < UC_STAFF || $user['override_class'] != 255) {
    stderr('Error', 'whats the story?');
}
if (isset($_GET['action']) && htmlsafechars($_GET['action']) === 'editclass') {
    $newclass = (int) $_GET['class'];
    $returnto = htmlsafechars($_GET['returnto']);
    $set = [
        'override_class' => $newclass,
    ];
    $users_class = $container->get(User::class);
    $users_class->update($set, $user['id']);
    $fluent = $container->get(Database::class);
    $fluent->deleteFrom('ajax_chat_online')
           ->where('userID = ?', $user['id'])
           ->execute();
    header("Location: {$site_config['paths']['baseurl']}/" . $returnto);
    die();
}

$HTMLOUT .= "
<h2 class='has-text-centered'>{$lang['set_class_allow']}</h2>
<form method='get' action='{$site_config['paths']['baseurl']}/setclass.php' enctype='multipart/form-data' enctype='multipart/form-data' accept-charset='utf-8'>
    <input type='hidden' name='action' value='editclass'>
    <input type='hidden' name='returnto' value='userdetails.php?id=" . $user['id'] . "'>";

$text = "
    <div class='has-text-centered padding20'>
        <label for='name'>Class</label>
        <span class='margin20'>
            <select id='class' name='class'>";
$maxclass = $user['class'] - 1;
for ($i = 0; $i <= $maxclass; ++$i) {
    if (trim(get_user_class_name((int) $i)) != '') {
        $text .= "
                <option value='$i'>" . get_user_class_name((int) $i) . '</option>';
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
echo stdhead($lang['set_class_temp']) . $HTMLOUT . stdfoot();
