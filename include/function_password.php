<?php

declare(strict_types = 1);

/**
 * @param $pass
 *
 * @return bool|string
 */
function make_passhash($pass)
{
    // update usercp to reset password
    $options = get_options();
    $algo = $options['algo'];
    $options = $options['options'];

    return password_hash($pass, $algo, $options);
}

/**
 * @param int $bytes
 *
 * @throws Exception
 *
 * @return string
 */
function make_password($bytes = 12)
{
    return bin2hex(random_bytes($bytes));
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
