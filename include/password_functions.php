<?php
/**
 * @param $pass
 *
 * @return bool|string
 */
function make_passhash($pass)
{
    if (PHP_VERSION_ID >= 70200) {
        $options = [
                'memory_cost' => 2048,
                'time_cost' => 12,
                'threads' => 4,
        ];
        return password_hash($pass, PASSWORD_ARGON2I, $options);
    }
    $options = [
        'cost' => 12,
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
