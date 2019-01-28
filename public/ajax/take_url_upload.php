<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_password.php';
require_once INCL_DIR . 'dragndrop.php';
global $site_config, $cache, $session, $user_stuffs;

header('content-type: application/json');
if (empty($_POST['csrf']) || !$session->validateToken($_POST['csrf'])) {
    echo json_encode(['msg' => $lang['bitbucket_csrf']]);
    die();
}

$userid = $session->get('userID');
if (empty($userid)) {
    echo json_encode(['msg' => $lang['bitbucket_invalid_userid']]);
    die();
}
$username = $user_stuffs->get_item('username', $userid);

$url = $_POST['url'];
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['msg' => $lang['bitbucket_invalid_url']]);
    die();
}

$lang = array_merge(load_language('global'), load_language('bitbucket'));
$image_proxy = new Pu239\ImageProxy();
$SaLt = $site_config['site']['salt'];
$SaLty = $site_config['site']['salty'];
$skey = $site_config['site']['skey'];
$maxsize = $site_config['bucket_maxsize'];
$folders = date('Y/m');
$formats = $site_config['allowed_formats'];
$str = implode('|', $formats);
$str = str_replace('.', '', $str);
$bucketdir = BITBUCKET_DIR . $folders . '/';
$bucketlink = $folders . '/';
$PICSALT = $SaLt . $username;
$USERSALT = substr(md5($SaLty . $userid), 0, 6);
$rand = make_password();
$temppath = CACHE_DIR . $rand;
make_year(BITBUCKET_DIR);
make_month(BITBUCKET_DIR);

$image = fetch($url);
if (!$image) {
    echo json_encode(['msg' => $lang['bitbucket_download_failed']]);
    die();
}
if (!file_put_contents($temppath, $image)) {
    echo json_encode(['msg' => $lang['bitbucket_store_failed']]);
    die();
}

$it1 = exif_imagetype($temppath);
if (!in_array($it1, $site_config['allowed_exif_types'])) {
    echo json_encode(['msg' => $lang['bitbucket_invalid']]);
    die();
}
switch ($it1) {
    case 1:
        $ext = '.gif';
        break;
    case 2:
        $ext = '.jpg';
        break;
    case 3:
        $ext = '.png';
        break;
    case 19:
        $ext = '.webp';
        break;
}

$path = $bucketdir . $USERSALT . '_' . $rand . $ext;
$pathlink = $bucketlink . $USERSALT . '_' . $rand . $ext;
if (!rename($temppath, $path)) {
    echo json_encode(['msg' => $lang['bitbucket_upfail_save']]);
    die();
}

if (!file_exists($path)) {
    echo json_encode(['msg' => $lang['bitbucket_upfail_save']]);
    die();
}
$image_proxy->optimize_image($path, null, null, false);
$image = "{$site_config['baseurl']}/img.php?{$pathlink}";

if (!empty($image)) {
    echo json_encode([
        'msg' => $lang['bitbucket_success'],
        'url' => $image,
    ]);
    die();
} else {
    echo json_encode(['msg' => 'Unknown failure occurred']);
    die();
}
