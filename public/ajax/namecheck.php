<?php

if (empty($_GET['wantusername'])) {
    die('<div class="margin10 has-text-info">You can\'t post nothing please enter a username!</div>');
}
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
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
        $HTMLOUT .= "<div class='has-text-danger tooltipper margin10' title='Username Not Available'><i class='icon-thumbs-down-alt icon' aria-hidden='true'></i><b>Sorry... Username - " . htmlsafechars($namecheck['username']) . ' is already in use.</b></div>';
    }
} else {
    $HTMLOUT .= "<div class='has-text-success tooltipper margin10' title='Username Available'><i class='icon-thumbs-up-alt icon' aria-hidden='true'></i><b>Username Available</b></div>";
}
echo $HTMLOUT;
die();
