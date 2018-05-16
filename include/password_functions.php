<?php
/**
 * @param $pass
 *
 * @return bool|string
 */
function make_passhash($pass)
{
    $options = [
            'memory_cost' => 2048,
            'time_cost' => 12,
            'threads' => 4,
    ];
    return password_hash($pass, PASSWORD_ARGON2I, $options);
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
