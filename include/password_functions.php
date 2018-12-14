<?php

function make_passhash($pass)
{
    $options = get_options();
    $algo = $options['algo'];
    $options = $options = $options['options'];

    return password_hash($pass, $algo, $options);
}

function make_password($bytes = 12)
{
    return bin2hex(random_bytes($bytes));
}

function rehash_password($hash, $password, $userid)
{
    global $user_stuffs, $site_config;

    $options = get_options();
    $algo = $options['algo'];
    $options = $options = $options['options'];

    if (password_needs_rehash($hash, $algo, $options)) {
        $set = [
            'passhash' => make_passhash($password),
        ];
        $user_stuffs->update($set, $userid);
    }
}

function get_options()
{
    global $site_config;

    $options = [
        'memory_cost' => !empty($site_config['password_memory_cost']) ? $site_config['password_memory_cost'] : 2048,
        'time_cost' => !empty($site_config['password_time_cost']) ? $site_config['password_time_cost'] : 12,
        'threads' => !empty($site_config['password_threads']) ? $site_config['password_threads'] : 4,
    ];

    if (PHP_VERSION_ID >= 70200 && @password_hash('secret_password', PASSWORD_ARGON2ID)) {
        $algo = PASSWORD_ARGON2ID;
    } elseif (PHP_VERSION_ID >= 70200 && @password_hash('secret_password', PASSWORD_ARGON2I)) {
        $algo = PASSWORD_ARGON2I;
    } else {
        $algo = PASSWORD_BCRYPT;
        $options = [
            'cost' => !empty($site_config['password_cost']) ? $site_config['password_cost'] : 12,
        ];
    }


    return [
        'algo' => $algo,
        'options' => $options,
    ];
}
