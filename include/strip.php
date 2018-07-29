<?php

/**
 * @param $descr
 */
function trim_ml(&$descr)
{
    $lines = [];
    foreach (explode("\n", $descr) as $line) {
        $lines[] = trim($line, "\x00..\x1F.,-+=\t ~");
    }
    $descr = implode("\n", $lines);
}

/**
 * @param $pattern
 * @param $replacement
 * @param $subject
 *
 * @return mixed
 */
function trim_regex($pattern, $replacement, $subject)
{
    trim_ml($subject);

    return preg_replace($pattern, $replacement, $subject);
}

/**
 * @param $desc
 */
function strip(&$desc)
{
    $desc = preg_replace('`[\x00-\x08\x0b-\x0c\x0e-\x1f\x7f-\xff]`', '', $desc);

    return;
}
