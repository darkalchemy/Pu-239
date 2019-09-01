<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\User;

/**
 * @param $ip
 *
 * @return bool
 */
function validip($ip)
{
    if (!PRODUCTION) {
        return filter_var($ip, FILTER_VALIDATE_IP) ? true : false;
    }

    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ? true : false;
}

/**
 * @param int  $date
 * @param      $method
 * @param int  $norelative
 * @param int  $full_relative
 * @param bool $calc
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return false|mixed|string
 */
function get_date(int $date, $method, $norelative = 1, $full_relative = 0, bool $calc = false)
{
    global $container, $site_config;

    $user = [];
    if (function_exists('get_userid')) {
        $userid = get_userid();
        if (!empty($userid)) {
            $user_class = $container->get(User::class);
            $user = $user_class->getUserFromId($userid);
        }
    }
    $today_time = $yesterday_time = $tomorrow_time = 0;
    $use_12_hour = !empty($user['use_12_hour']) ? $user['use_12_hour'] : $site_config['site']['use_12_hour'];
    $time_string = $use_12_hour ? 'g:i:s a' : 'H:i:s';
    $time_string_without_seconds = $use_12_hour ? 'g:i a' : 'H:i';

    $time_options = [
        'JOINED' => $site_config['time']['joined'],
        'SHORT' => $site_config['time']['short'] . ' ' . $time_string,
        'LONG' => $site_config['time']['long'] . ' ' . $time_string,
        'TINY' => $site_config['time']['tiny'],
        'DATE' => $site_config['time']['date'],
        'FORM' => $site_config['time']['form'],
        'TIME' => $time_string,
        'MYSQL' => 'Y-m-d G:i:s',
        'WITH_SEC' => $time_string,
        'WITHOUT_SEC' => $time_string_without_seconds,
    ];
    if (!$date) {
        return '--';
    }
    if (empty($method)) {
        $method = 'LONG';
    }
    if (function_exists('get_time_offset')) {
        $user_offset = get_time_offset();
    } else {
        $user_offset = 0;
    }

    if ($site_config['time']['use_relative']) {
        $today_time = gmdate('d,m,Y', (TIME_NOW + $user_offset));
        $yesterday_time = gmdate('d,m,Y', ((TIME_NOW - 86400) + $user_offset));
        $tomorrow_time = gmdate('d,m,Y', ((TIME_NOW + 86400) + $user_offset));
    }
    if ($site_config['time']['use_relative'] === 3) {
        $full_relative = 1;
    }
    if ($full_relative && $norelative != false && !$calc) {
        $diff = TIME_NOW - $date;
        if ($diff < 3600) {
            if ($diff < 120) {
                return '< 1 minute ago';
            } else {
                return sprintf('%s minutes ago', (int) ($diff / 60));
            }
        } elseif ($diff < 7200) {
            return '< 1 hour ago';
        } elseif ($diff < 86400) {
            return sprintf('%s hours ago', (int) ($diff / 3600));
        } elseif ($diff < 172800) {
            return '< 1 day ago';
        } elseif ($diff < 604800) {
            return sprintf('%s days ago', (int) ($diff / 86400));
        } elseif ($diff < 1209600) {
            return '< 1 week ago';
        } elseif ($diff < 3024000) {
            return sprintf('%s weeks ago', (int) ($diff / 604900));
        } else {
            return gmdate($time_options[$method], ($date + $user_offset));
        }
    } elseif ($site_config['time']['use_relative'] && $norelative != 1 && !$calc) {
        $this_time = gmdate('d,m,Y', ($date + $user_offset));
        if ($site_config['time']['use_relative'] === 2) {
            $diff = TIME_NOW - $date;
            if ($diff < 3600) {
                if ($diff < 120) {
                    return '< 1 minute ago';
                } else {
                    return sprintf('%s minutes ago', (int) ($diff / 60));
                }
            }
        }
        if ($this_time == $today_time) {
            if ($method === 'WITHOUT_SEC') {
                return str_replace('{--}', 'Today', gmdate($site_config['time']['use_relative_format_without_seconds'] . $time_string_without_seconds, ($date + $user_offset)));
            }

            return str_replace('{--}', 'Today', gmdate($site_config['time']['use_relative_format'] . $time_string, ($date + $user_offset)));
        } elseif ($this_time == $yesterday_time) {
            if ($method === 'WITHOUT_SEC') {
                return str_replace('{--}', 'Yesterday', gmdate($site_config['time']['use_relative_format_without_seconds'] . $time_string_without_seconds, ($date + $user_offset)));
            }

            return str_replace('{--}', 'Yesterday', gmdate($site_config['time']['use_relative_format'] . $time_string, ($date + $user_offset)));
        } elseif ($this_time == $tomorrow_time) {
            if ($method === 'WITHOUT_SEC') {
                return str_replace('{--}', 'Tomorrow', gmdate($site_config['time']['use_relative_format_without_seconds'] . $time_string_without_seconds, ($date + $user_offset)));
            }

            return str_replace('{--}', 'Tomorrow', gmdate($site_config['time']['use_relative_format'] . $time_string_without_seconds, ($date + $user_offset)));
        } else {
            return gmdate($time_options[$method], ($date + $user_offset));
        }
    } elseif ($calc) {
        $years = (int) ($date / 31536000);
        $date -= $years * 31536000;
        $days = intval($date / 86400);
        $date -= $days * 86400;
        $hours = intval($date / 3600);
        $date -= $hours * 3600;
        $mins = intval($date / 60);
        $secs = $date - ($mins * 60);
        $text = [];
        if ($years > 0) {
            $text[] = number_format($years) . ' year' . plural($years);
        }
        if ($days > 0) {
            $text[] = number_format($days) . ' day' . plural($days);
        }
        if ($hours > 0) {
            $text[] = number_format($hours) . ' hour' . plural($hours);
        }
        if ($mins > 0) {
            $text[] = number_format($mins) . ' min' . plural($mins);
        }
        if ($secs > 0) {
            $text[] = number_format($secs) . ' sec' . plural($secs);
        }
        if (!empty($text)) {
            return implode(', ', $text);
        }
    }

    return gmdate($time_options[$method], ($date + $user_offset));
}
