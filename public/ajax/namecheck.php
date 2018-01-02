<?php
if (empty($_GET['wantusername'])) {
    die('Silly Rabbit - Twix are for kids - You cant post nothing please enter a username !');
}
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
dbconn();
global $site_config;

$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('takesignup'));
/**
 * @param $username
 *
 * @return bool
 */

$is_valid = valid_username($_GET['wantusername'], true);

if ($is_valid !== true) {
    echo $is_valid;
    die();
}

$checkname = sqlesc($_GET['wantusername']);
$sql = "SELECT username FROM users WHERE username = $checkname";
$result = sql_query($sql);
$numbers = mysqli_num_rows($result);
if ($numbers > 0) {
    while ($namecheck = mysqli_fetch_assoc($result)) {
        $HTMLOUT .= "<font color='#cc0000'><img src='{$site_config['pic_base_url']}cross.png' alt='Cross' title='Username Not Available' /><b>Sorry... Username - " . htmlsafechars($namecheck['username']) . ' is already in use.</b></font>';
    }
} else {
    $HTMLOUT .= "<font color='#33cc33'><img src='{$site_config['pic_base_url']}tick.png' alt='Tick' title='Username Available' /><b>Username Available</b></font>";
}
echo $HTMLOUT;
die();
