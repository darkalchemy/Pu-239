<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once INCL_DIR . 'dragndrop.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

$lang = array_merge(load_language('global'), load_language('bitbucket'));
$image_proxy = new DarkAlchemy\Pu239\ImageProxy();

$HTMLOUT = '';

$SaLt = $site_config['site']['salt'];
$SaLty = $site_config['site']['salty'];
$skey = $site_config['site']['skey'];
$maxsize = $site_config['bucket_maxsize'];
$folders = date('Y/m');
$formats = $site_config['allowed_formats'];
$str = implode('|', $formats);
$str = str_replace('.', '', $str);

$bucketdir = (isset($_POST['avy']) ? AVATAR_DIR : BITBUCKET_DIR . $folders . '/');
$bucketlink = ((isset($_POST['avy']) || (isset($_GET['images']) && $_GET['images'] == 2)) ? 'avatar/' : $folders . '/');
$PICSALT = $SaLt . $CURUSER['username'];
$USERSALT = substr(md5($SaLty . $CURUSER['id']), 0, 6);
make_year(BITBUCKET_DIR);
make_month(BITBUCKET_DIR);


for ($i = 0; $i < $_POST['nbr_files']; $i++) {
    $file = preg_replace('`[^a-z0-9\-\_\.]`i', '', $_FILES['file_' . $i]['name']);
    $it1 = exif_imagetype($_FILES['file_' . $i]['tmp_name']);
    if (!in_array($it1, $site_config['allowed_exif_types'])) {
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
    $image_proxy->optimize_image($path);
    $images[] = "{$site_config['baseurl']}/img.php?{$pathlink}";
}

if (!empty($images)) {
    echo json_encode([
        'msg'  => $lang['bitbucket_success'],
        'urls' => $images,
    ]);
    die();
} else {
    echo json_encode(['msg' => 'Failure']);
    die();
}
