<?php
/**
 * @param $pass
 *
 * @return bool|string
 */
function make_passhash($pass)
{
    global $site_config;

    if (PHP_VERSION_ID >= 70200 && @password_hash('secret_password', PASSWORD_ARGON2I)) {
        $options = [
                'memory_cost' => !empty($site_config['password_memory_cost']) ? $site_config['password_memory_cost'] : 2048,
                'time_cost'   => !empty($site_config['password_time_cost']) ? $site_config['password_time_cost'] : 12,
                'threads'     => !empty($site_config['password_threads']) ? $site_config['password_threads'] : 4,
        ];

        return password_hash($pass, PASSWORD_ARGON2I, $options);
    }
    $options = [
        'cost' => !empty($site_config['password_cost']) ? $site_config['password_cost'] : 12,
    ];

    return password_hash($pass, PASSWORD_BCRYPT, $options);
}

/**
 * @param int $bytes
 *
 * @return string
 *
 * @throws Exception
 */
function make_password($bytes = 12)
{
    return bin2hex(random_bytes($bytes));
}
