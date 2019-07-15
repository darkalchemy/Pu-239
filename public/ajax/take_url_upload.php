<?php

declare(strict_types = 1);

use Pu239\ImageProxy;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_password.php';
require_once INCL_DIR . 'function_bitbucket.php';
$user = check_user_status();
$lang = load_language('bitbucket');
global $container, $site_config;

header('content-type: application/json');
if (empty($user['id'])) {
    echo json_encode(['msg' => $lang['bitbucket_invalid_userid']]);
    die();
}

$url = $_POST['url'];
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['msg' => $lang['bitbucket_invalid_url']]);
    die();
}
$username = $user['username'];
$SaLt = $site_config['salt']['one'];
$SaLty = $site_config['salt']['two'];
$skey = $site_config['salt']['three'];
$maxsize = $site_config['bucket']['maxsize'];
$folders = date('Y/m');
$formats = $site_config['images']['formats'];
$str = implode('|', $formats);
$bucketdir = BITBUCKET_DIR . $folders . '/';
$bucketlink = $folders . '/';
$PICSALT = $SaLt . $username;
$USERSALT = substr(md5($SaLty . $user['id']), 0, 6);
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
if (!in_array($it1, $site_config['images']['exif'])) {
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
$image_proxy = $container->get(ImageProxy::class);
$image_proxy->optimize_image($path, null, null, false);
$image = "{$site_config['paths']['baseurl']}/img.php?{$pathlink}";

if (!empty($image)) {
    echo json_encode([
        'msg' => $lang['bitbucket_success'],
        'url' => $image,
    ]);
    die();
} else {
    echo json_encode(['msg' => $lang['bitbucket_unknown']]);
    die();
}
