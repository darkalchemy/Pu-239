<?php

declare(strict_types = 1);

/**
 * @param string $filename
 * @param array  $options
 */
function asyncInclude(string $filename, array $options)
{
    $options = implode(' ', $options);
    exec("php -f {$filename} {$options} > /dev/null 2>/dev/null &");
}
