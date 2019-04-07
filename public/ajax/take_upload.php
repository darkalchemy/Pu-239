<?php

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_password.php';
require_once INCL_DIR . 'function_bitbucket.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

$lang = array_merge(load_language('global'), load_language('bitbucket'));
$image_proxy = new Pu239\ImageProxy();

header('content-type: application/json');

$SaLt = $site_config['salt']['one'];
$SaLty = $site_config['salt']['two'];
$skey = $site_config['salt']['three'];
$maxsize = $site_config['bucket']['maxsize'];
$folders = date('Y/m');
$formats = $site_config['images']['formats'];
$str = implode('|', $formats);
$str = str_replace('.', '', $str);

$bucketdir = BITBUCKET_DIR . $folders . '/';
$bucketlink = $folders . '/';
$PICSALT = $SaLt . $CURUSER['username'];
$USERSALT = substr(md5($SaLty . $CURUSER['id']), 0, 6);
make_year(BITBUCKET_DIR);
make_month(BITBUCKET_DIR);

for ($i = 0; $i < $_POST['nbr_files']; ++$i) {
    $file = preg_replace('`[^a-z0-9\-\_\.]`i', '', $_FILES['file_' . $i]['name']);
    $it1 = exif_imagetype($_FILES['file_' . $i]['tmp_name']);
    if (!in_array($it1, $site_config['images']['exif'])) {
        echo json_encode(['msg' => $lang['bitbucket_invalid']]);
        die();
    }

    $file = strtolower($file);
    $randb = make_password();
    $path = $bucketdir . $USERSALT . '_' . $randb . $file;
    $pathlink = $bucketlink . $USERSALT . '_' . $randb . $file;
    if (!move_uploaded_file($_FILES['file_' . $i]['tmp_name'], $path)) {
        echo json_encode(['msg' => $bucketdir . '<br>' . $USERSALT . '<br>' . $randb . '<br>' . $path . '<br>file move ' . $lang['bitbucket_upfail']]);
        die();
    }

    if (!file_exists($path)) {
        echo json_encode(['msg' => 'path not exists ' . $lang['bitbucket_upfail']]);
        die();
    }
    $image_proxy->optimize_image($path, null, null, false);
    $images[] = "{$site_config['paths']['baseurl']}/img.php?{$pathlink}";
}

if (!empty($images)) {
    $output = [
        'msg' => $lang['bitbucket_success'],
        'urls' => $images,
    ];
    file_put_contents('/var/log/nginx/images.log', json_encode($output) . PHP_EOL, FILE_APPEND);
    echo json_encode($output);
    die();
} else {
    echo json_encode(['msg' => 'Failure']);
    die();
}
