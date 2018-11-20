<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config, $user_stuffs, $fluent;

$lang = array_merge(load_language('global'), load_language('setclass'));
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
    $user_stuffs->update($set, $CURUSER['id']);
    $fluent->deleteFrom('ajax_chat_online')
        ->where('userID = ?', $CURUSER['id'])
        ->execute();
    header("Location: {$site_config['baseurl']}/" . $returnto);
    die();
}

$HTMLOUT .= "
<h2 class='has-text-centered'>{$lang['set_class_allow']}</h2>
<form method='get' action='{$site_config['baseurl']}/setclass.php'>
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
                <option value='$i'>" . get_user_class_name($i) . "</option>";
    }
}
$text .= "
            </select>
        </span>
        <div class='top20'>
            <input type='submit' class='button is-small' value='{$lang['set_class_ok']}'>
        </div>
    </div>";

$HTMLOUT .= main_div($text) . "
</form>";
echo stdhead("{$lang['set_class_temp']}") . $HTMLOUT . stdfoot();
