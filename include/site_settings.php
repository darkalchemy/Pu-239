<?php

global $site_config, $cache, $fluent, $CURUSER, $session;

$site_settings = $cache->get('site_settings_');
if ($site_settings === false || is_null($site_settings)) {
    $sql = $fluent->from('site_config')
        ->select(null)
        ->select('name')
        ->select('value');

    foreach ($sql as $res) {
        if (ctype_digit($res['value'])) {
            $res['value'] = (int) $res['value'];
        } elseif (is_float($res['value'])) {
            $res['value'] = (float) $res['value'];
        }
        $site_settings[$res['name']] = $res['value'];
    }
    $cache->set('site_settings_', $site_settings, 86400);
}

$hnr_settings = $cache->get('hnr_settings_');
if ($hnr_settings === false || is_null($hnr_settings)) {
    $sql = $fluent->from('hit_and_run_settings');

    foreach ($sql as $res) {
        $hnr_settings['hnr_config'][$res['name']] = $res['value'];
    }

    $cache->set('hnr_settings_', $hnr_settings, 86400);
}

$staff_settings = $cache->get('staff_settings_');
if ($staff_settings === false || is_null($staff_settings)) {
    $sql = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('class >= ?', UC_STAFF)
        ->where('class <= ?', UC_MAX)
        ->orderBy('id ASC');
    foreach ($sql as $res) {
        $staff_settings['is_staff']['allowed'][] = $res['id'];
    }

    if (!empty($staff_settings['is_staff']['allowed'])) {
        $cache->set('staff_settings_', $staff_settings, 86400);
    } else {
        $staff_settings['is_staff']['allowed'] = 0;
    }
}

$staff_forums = $cache->get('staff_forums_');
if ($staff_forums === false || is_null($staff_forums)) {
    $sql = $fluent->from('forums')
        ->select(null)
        ->select('id')
        ->where('min_class_read >= ?', UC_STAFF)
        ->orderBy('id')
        ->fetchAll();

    foreach ($sql as $res) {
        $staff_forums['staff_forums'][] = $res['id'];
    }

    $cache->set('staff_forums_', $staff_forums, 86400);
}
$site_config = array_merge($site_settings, $site_config, $hnr_settings, $staff_settings, $staff_forums);
$use_12_hour = !empty($session->get('use_12_hour')) ? $session->get('use_12_hour') === 'yes' ? 1 : 0 : $site_config['use_12_hour'];
$time_string = $use_12_hour ? 'g:i:s a' : 'H:i:s';
$time_string_without_seconds = $use_12_hour ? 'g:i a' : 'H:i';
$site_config['time_adjust'] = 0; // If you have not set date_default_timezone_set to UTC, you should adjust your time to UTC here
$site_config['time_offset'] = 0;
$site_config['time_use_relative'] = 1;
$site_config['time_use_relative_format'] = '{--}, ' . $time_string;
$site_config['time_use_relative_format_without_seconds'] = '{--}, ' . $time_string_without_seconds;
$site_config['time_joined'] = 'j-F y';
$site_config['time_short'] = 'jS F Y - ' . $time_string;
$site_config['time_long'] = 'M j Y, ' . $time_string;
$site_config['time_time'] = $time_string;
$site_config['time_tiny'] = '';
$site_config['time_date'] = '';
$site_config['time_form'] = '';
$site_config['time_with_seconds'] = $time_string;
$site_config['time_without_seconds'] = $time_string_without_seconds;
