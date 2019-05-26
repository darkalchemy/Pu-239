<?php

declare(strict_types = 1);

use Blocktrail\CryptoJSAES\CryptoJSAES;

/**
 * @param $text
 * @param $salt
 *
 * @return string
 */
function encrypt($text, $salt)
{
    $encrypted = CryptoJSAES::encrypt($text, $salt);

    return base64_encode($encrypted);
}

/**
 * @param $text
 * @param $salt
 *
 * @return string
 */
function decrypt($text, $salt)
{
    $str = base64_decode($text);

    return CryptoJSAES::decrypt($str, $salt);
}

/**
 * @param $root
 * @param $input
 *
 * @return bool|string|null
 */
function valid_path($root, $input)
{
    $fullpath = $root . $input;
    $fullpath = realpath($fullpath);
    $root = realpath($root);
    $rl = strlen($root);

    return ($root != substr($fullpath, 0, $rl)) ? null : $fullpath;
}

/**
 * @param $path
 */
function make_year($path)
{
    $dir = $path . '/' . date('Y');
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

/**
 * @param $path
 */
function make_month($path)
{
    $dir = $path . '/' . date('Y/m');
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}
