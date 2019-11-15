<?php

declare(strict_types = 1);

/**
 * Class Bencode.
 */
class Bencode
{
    /**
     * @param $data
     */
    public static function encode($data): string
    {
        if (is_array($data)) {
            $return = '';
            $check = -1;
            $list = true;
            foreach ($data as $key => $value) {
                if ($key !== ++$check) {
                    $list = false;
                    break;
                }
            }
            if ($list) {
                $return .= 'l';
                foreach ($data as $value) {
                    $return .= self::encode($value);
                }
            } else {
                $return .= 'd';
                foreach ($data as $key => $value) {
                    $return .= self::encode(strval($key));
                    $return .= self::encode($value);
                }
            }
            $return .= 'e';
        } elseif (is_integer($data)) {
            $return = 'i' . $data . 'e';
        } else {
            $return = strlen($data) . ':' . $data;
        }

        return $return;
    }
}
