<?php
/**
 * @param $context
 *
 * @return string
 */
function validator($context)
{
    global $CURUSER;

    $timestamp = time();
    $hash = hash_hmac('sha1', $CURUSER['auth'], $context.$timestamp);

    return substr($hash, 0, 20).dechex($timestamp);
}

/**
 * @param $context
 *
 * @return string
 */
function validatorForm($context)
{
    return '<input type="hidden" name="validator" value="'.validator($context).'"/>';
}

/**
 * @param     $validator
 * @param     $context
 * @param int $seconds
 *
 * @return bool
 */
function validate($validator, $context, $seconds = 0)
{
    global $CURUSER;
    $timestamp = hexdec(substr($validator, 20));
    if ($seconds && time() > $timestamp + $seconds) {
        return false;
    }
    $hash = substr(hash_hmac('sha1', $CURUSER['auth'], $context.$timestamp), 0, 20);
    if (substr($validator, 0, 20) != $hash) {
        return false;
    }

    return true;
}
