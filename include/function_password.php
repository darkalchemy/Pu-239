<?php

declare(strict_types = 1);

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
