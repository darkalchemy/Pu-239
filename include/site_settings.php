<?php

global $site_config, $cache, $fluent, $CURUSER, $session;

$staff_settings = $cache->get('is_staff_');
if ($staff_settings === false || is_null($staff_settings)) {
    $sql = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('class>= ?', UC_STAFF)
        ->where('class <= ?', UC_MAX)
        ->orderBy('id ASC');
    foreach ($sql as $res) {
        $staff_settings['is_staff'][] = $res['id'];
    }

    if (!empty($staff_settings['is_staff'])) {
        $cache->set('is_staff_', $staff_settings, 86400);
    } else {
        $staff_settings['is_staff'] = 0;
    }
}

$staff_forums = $cache->get('staff_forums_');
if ($staff_forums === false || is_null($staff_forums)) {
    $sql = $fluent->from('forums')
        ->select(null)
        ->select('id')
        ->where('min_class_read>= ?', UC_STAFF)
        ->orderBy('id')
        ->fetchAll();

    foreach ($sql as $res) {
        $staff_forums['staff_forums'][] = $res['id'];
    }

    $cache->set('staff_forums_', $staff_forums, 86400);
}

$site_settings = $cache->get('site_settings_');
if ($site_settings === false || is_null($site_settings)) {
    $sql = $fluent->from('site_config')
        ->orderBy('parent')
        ->orderBy('name');

    foreach ($sql as $row) {
        switch ($row['type']) {
            case 'int':
                $value = (int) $row['value'];
                break;
            case 'float':
                $value = (float) $row['value'];
                break;
            case 'bool':
                $value = (bool) $row['value'];
                break;
            case 'array':
                if ($row['name'] === 'recaptcha') {
                    $value = [
                        'site' => '',
                        'secret' => '',
                    ];
                    if (!empty($row['value'])) {
                        $temp = explode('|', $row['value']);
                        $value = [
                            'site' => $temp[0],
                            'secret' => $temp[1],
                        ];
                    }
                } elseif (empty($row['value'])) {
                    $value = [];
                } else {
                    $value = explode('|', $row['value']);
                    foreach ($value as $key => $item) {
                        if (is_numeric($item)) {
                            $value[$key] = (int) $item;
                        }
                    }
                }
                break;
            default:
                $value = $row['value'];
        }
        if (!empty($row['parent'])) {
            $site_config_db[$row['parent']][$row['name']] = $value;
        } else {
            $site_config_db[$row['name']] = $value;
        }
    }
    $cache->set('site_settings_', $site_settings, 86400);
}

$badwords = $cache->get('badwords_');
if ($badwords === false || is_null($badwords)) {
    $query = $fluent->from('class_config')
        ->select('name')
        ->select('classname')
        ->where('template = 1')
        ->where('classname != ""');
    foreach ($query as $classname) {
        $temp[] = $classname['name'];
        $temp[] = $classname['classname'];
        $temp[] = str_replace('_', '', $classname['name']);
        $temp[] = str_replace(' ', '', $classname['classname']);
    }
    $badwords['badwords'] = array_unique($temp);
    $cache->set('badwords_', $badwords, 86400);
}

$hnr_config = $cache->get('hnr_config_');
if ($hnr_config === false || is_null($hnr_config)) {
    $query = $fluent->from('hit_and_run_settings')
        ->orderBy('name');
    foreach ($query as $row) {
        $hnr_config['hnr_config'][$row['name']] = $row['value'];
    }
    $cache->set('hnr_config_', $hnr_config, 86400);
}

$site_config = array_merge_recursive($site_config, $staff_settings, $staff_forums, $site_config_db, $badwords, $hnr_config);
$site_config['site']['badwords'] = strtolower(implode('|', array_merge($site_config['badwords'], $site_config['site']['bad_words'])));
ksort($site_config);
