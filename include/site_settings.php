<?php

global $site_config, $cache, $fluent;

$site_settings = $cache->get('site_settings_');
if ($site_settings === false || is_null($site_settings)) {
    $sql = $fluent->from('site_config')
        ->select(null)
        ->select('name')
        ->select('value');

    foreach ($sql as $res) {
        if (is_int($res['value'])) {
            $res['value'] = (int) $res['value'];
        }
        $site_settings[$res['name']] = $res['value'];
    }
    $cache->set('site_settings_', $site_settings, 86400);
}

$paypal_settings = $cache->get('paypal_settings_');
if ($paypal_settings === false || is_null($paypal_settings)) {
    $sql = $fluent->from('paypal_config');

    foreach ($sql as $res) {
        $paypal_settings['paypal_config'][$res['name']] = $res['value'];
    }

    $cache->set('paypal_settings_', $paypal_settings, 86400);
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
        ->where('class BETWEEN ? AND ?', UC_STAFF, UC_MAX)
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

$site_config = array_merge($site_settings, $site_config, $paypal_settings, $hnr_settings, $staff_settings);

$time_string = $site_config['12_hour'] ? 'g:i:s a' : 'H:i:s';
$time_string_without_seconds = $site_config['12_hour'] ? 'g:i a' : 'H:i';
$site_config['time_adjust'] = 0;
$site_config['time_offset'] = 0;
$site_config['time_use_relative'] = 1;
$site_config['time_use_relative_format'] = '{--}, ' . $time_string;
$site_config['time_use_relative_format_without_seconds'] = '{--}, ' . $time_string_without_seconds;
$site_config['time_joined'] = 'j-F y';
$site_config['time_short'] = 'jS F Y - ' . $time_string;
$site_config['time_long'] = 'M j Y, ' . $time_string;
$site_config['time_tiny'] = '';
$site_config['time_date'] = '';
$site_config['time_with_seconds'] = $time_string;
$site_config['time_without_seconds'] = $time_string_without_seconds;
