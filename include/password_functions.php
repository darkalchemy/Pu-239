<?php
function make_passhash($pass)
{
    $options = [
        'cost' => 12,
    ];
    return password_hash($pass, PASSWORD_BCRYPT, $options);
}

function make_password($bytes = 12)
{
    return bin2hex(random_bytes($bytes));
}
