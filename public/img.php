<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';

function valid_path($root, $input)
{
    $fullpath = $root . $input;
    $fullpath = realpath($fullpath);
    $root = realpath($root);
    $rl = strlen($root);

    return ($root != substr($fullpath, 0, $rl)) ? null : $fullpath;
}

if (isset($_SERVER['REQUEST_URI'])) {
    $image = valid_path(BITBUCKET_DIR, substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']) + 1));
    if (!((($pi = pathinfo($image)) && preg_match('#^(jpg|jpeg|gif|png)$#i', $pi['extension'])) && $image && is_file($image))) {
        die('^_^');
    }
    $img['last_mod'] = filemtime($image);
    $img['date_fmt'] = 'D, d M Y H:i:s T';
    $img['lm_date'] = date($img['date_fmt'], $img['last_mod']);
    $img['ex_date'] = date($img['date_fmt'], time() + (86400 * 7));
    $img['stop'] = false;
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $img['since'] = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE'], 2);
        $img['since'] = strtotime($img['since'][0]);
        if ($img['since'] == $img['last_mod']) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
            $img['stop'] = true;
        }
    }
    header('Expires: ' . $img['ex_date']);
    header('Cache-Control: private, max-age=604800');
    if ($img['stop']) {
        exit();
    }
    header('Last-Modified: ' . $img['lm_date']);
    header('Content-type: image/' . $pi['extension']);
    readfile($image);
    exit();
}
