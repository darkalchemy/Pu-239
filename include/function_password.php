<?php

use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;

/**
 * @param $pass
 *
 * @return bool|string
 */
function make_passhash($pass)
{
    $options = get_options();
    $algo = $options['algo'];
    $options = $options['options'];

    return password_hash($pass, $algo, $options);
}

/**
 * @param int $bytes
 *
 * @return string
 * @throws Exception
 */
function make_password($bytes = 12)
{
    return bin2hex(random_bytes($bytes));
}

/**
 * @param $hash
 * @param $password
 * @param $userid
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function rehash_password($hash, $password, $userid)
{
    global $user_stuffs;

    $options = get_options();
    $algo = $options['algo'];
    $options = $options['options'];

    if (password_needs_rehash($hash, $algo, $options)) {
        $set = [
            'passhash' => make_passhash($password),
        ];
        $user_stuffs->update($set, $userid);
    }
}

/**
 * @return array
 */
function get_options()
{
    global $site_config;

    $options = [
        'memory_cost' => $site_config['password']['memory_cost'],
        'time_cost' => $site_config['password']['time_cost'],
        'threads' => $site_config['password']['threads'],
    ];

    if (PHP_VERSION_ID >= 70200 && @password_hash('secret_password', PASSWORD_ARGON2ID)) {
        $algo = PASSWORD_ARGON2ID;
    } elseif (PHP_VERSION_ID >= 70200 && @password_hash('secret_password', PASSWORD_ARGON2I)) {
        $algo = PASSWORD_ARGON2I;
    } else {
        $algo = PASSWORD_BCRYPT;
        $options = [
            'cost' => $site_config['password']['cost'],
        ];
    }

    return [
        'algo' => $algo,
        'options' => $options,
    ];
}
