<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */
// Class to provide multibyte enabled string methods

/**
 * Class AJAXChatString.
 */
class AJAXChatString
{
    /**
     * @param        $str
     * @param int    $start
     * @param null   $length
     * @param string $encoding
     *
     * @return bool|string
     */
    public static function subString($str, $start = 0, $length = null, $encoding = 'UTF-8')
    {
        if ($length === null) {
            $length = self::stringLength($str);
        }
        if (function_exists('mb_substr')) {
            return mb_substr($str, $start, $length, $encoding);
        } elseif (function_exists('iconv_substr')) {
            return iconv_substr($str, $start, $length, $encoding);
        } else {
            return substr($str, $start, $length);
        }
    }

    /**
     * @param        $str
     * @param string $encoding
     *
     * @return int
     */
    public static function stringLength($str, $encoding = 'UTF-8')
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, $encoding);
        } elseif (function_exists('iconv_strlen')) {
            return iconv_strlen($str, $encoding);
        } else {
            return strlen($str);
        }
    }
}
