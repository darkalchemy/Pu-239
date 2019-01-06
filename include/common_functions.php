<?php

/**
 * @param $val
 *
 * @return int|string
 */
function return_bytes($val)
{
    if ($val == '') {
        return 0;
    }
    $val = strtolower(trim($val));
    $last = $val[strlen($val) - 1];
    $val = rtrim($val, $last);

    switch ($last) {
        case 'g':
            $val *= (1024 * 1024 * 1024);
            break;
        case 'm':
            $val *= (1024 * 1024);
            break;
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}

/**
 * @return mixed
 */
function get_scheme()
{
    if (isset($_SERVER['REQUEST_SCHEME'])) {
        return $_SERVER['REQUEST_SCHEME'];
    } elseif (isset($_SERVER['HTTPS'])) {
        return 'https';
    }

    return 'http';
}
