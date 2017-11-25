<?php

global $cache, $site_config;
dbconn();
$site_setting = $paypal_settings = $hnr_settings = $staff_settings = [];

if (($site_settings = $cache->get('site_settings_')) === false) {
    $res = sql_query('SELECT name, value FROM site_config') or sqlerr(__FILE__, __LINE__);
    while ($site_setting = mysqli_fetch_assoc($res)) {
        $site_settings[ $site_setting['name'] ] = $site_setting['value'];
    }
    $cache->set('site_settings_', $site_settings, 86400);
}

if (($paypal_settings = $cache->get('paypal_settings_')) === false) {
    $res = sql_query('SELECT * FROM paypal_config') or sqlerr(__FILE__, __LINE__);
    while ($paypal_setting = mysqli_fetch_assoc($res)) {
        $paypal_settings['paypal_config'][ $paypal_setting['name'] ] = $paypal_setting['value'];
    }
    $cache->set('paypal_settings_', $paypal_settings, 86400);
}

if (($hnr_settings = $cache->get('hnr_settings_')) === false) {
    $res = sql_query('SELECT * FROM hit_and_run_settings') or sqlerr(__FILE__, __LINE__);
    while ($hnr_setting = mysqli_fetch_assoc($res)) {
        $hnr_settings['hnr_config'][ $hnr_setting['name'] ] = $hnr_setting['value'];
    }
    $cache->set('hnr_settings_', $hnr_settings, 86400);
}

if (($staff_settings = $cache->get('staff_settings_')) === false) {
    $res = sql_query('SELECT id, username, class FROM users WHERE class BETWEEN ' . UC_STAFF . ' AND ' . UC_MAX . ' ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
    while ($staff_setting = mysqli_fetch_assoc($res)) {
        $staff_settings['is_staff']['allowed'][] = (int)$staff_setting['id'];
    }
    if (!empty($staff_settings['is_staff']['allowed'])) {
        $cache->set('staff_settings_', $staff_settings, 86400);
    } else {
        $staff_settings['is_staff']['allowed'] = 0;
    }
}

$site_config = array_merge($site_settings, $site_config, $paypal_settings, $hnr_settings, $staff_settings);
