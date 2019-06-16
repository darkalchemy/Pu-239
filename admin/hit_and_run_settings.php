<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_hit_and_run_settings'));
global $container, $site_config;

//dd($site_config['hnr_config']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session = $container->get(Session::class);
    $fluent = $container->get(Database::class);
    $updated = false;
    foreach ($site_config['hnr_config'] as $c_name => $c_value) {
        if (isset($_POST[$c_name]) && $_POST[$c_name] != $c_value) {
            $fluent->update('hit_and_run_settings')
                   ->set(['value' => $_POST[$c_name]])
                   ->where('name = ?', $c_name)
                   ->execute();

            $updated = true;
        }
    }
    if (!$updated) {
        $session->set('is-warning', $lang['hnr_settings_err_query']);
    } else {
        $cache = $container->get(Cache::class);
        $cache->delete('hnr_settings_');
        $session->set('is-success', 'Update Successful');
    }
}

$HTMLOUT .= "
<h1 class='has-text-centered'>{$lang['hnr_settings_title']}</h1>
<form action='staffpanel.php?tool=hit_and_run_settings' method='post' accept-charset='utf-8'>";

$HTMLOUT .= main_table("
    <tr><td class='w-50'>{$lang['hnr_settings_online']}</td><td>{$lang['hnr_settings_yes']}<input type='radio' name='hnr_online' value='1' " . ($site_config['hnr_config']['hnr_online'] ? 'checked' : '') . ">{$lang['hnr_settings_no']}<input type='radio' name='hnr_online' value='0' " . (!$site_config['hnr_config']['hnr_online'] ? 'checked' : '') . "></td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_fclass']}</td><td><input type='text' name='firstclass' size='20' value='" . htmlsafechars($site_config['hnr_config']['firstclass']) . "'></td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_sclass']}</td><td><input type='text' name='secondclass' size='20' value='" . htmlsafechars($site_config['hnr_config']['secondclass']) . "'></td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_tclass']}</td><td><input type='text' name='thirdclass' size='20' value='" . htmlsafechars($site_config['hnr_config']['thirdclass']) . "'></td></tr>

    <tr><td class='w-50'>{$lang['hnr_settings_tage1']}</td><td><input type='number' name='torrentage1' min='0' max='31' step='1' value='" . $site_config['hnr_config']['torrentage1'] . "'>{$lang['hnr_settings_days']}</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_tage2']}</td><td><input type='number' name='torrentage2' min='0' max='31' step='1' value='" . $site_config['hnr_config']['torrentage2'] . "'>{$lang['hnr_settings_days']}</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_tage3']}</td><td><input type='number' name='torrentage3' min='0' max='31' step='1' value='" . $site_config['hnr_config']['torrentage3'] . "'>{$lang['hnr_settings_days']}</td></tr>
    <tr><td colspan='2'><div class='has-text-centered size_6'>{$lang['hnr_settings_group1']}</div></td></tr>

    <tr><td class='w-50'>{$lang['hnr_settings_seed1_1']}</td><td><input type='number' name='_3day_first' min='0' max='4320' step='1' value='" . $site_config['hnr_config']['_3day_first'] . "'>{$lang['hnr_settings_hours']}</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_seed2_1']}</td><td><input type='number' name='_3day_second' min='0' max='4320' step='1' value='" . $site_config['hnr_config']['_3day_second'] . "'>{$lang['hnr_settings_hours']}</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_seed3_1']}</td><td><input type='number' name='_3day_third' min='0' max='4320' step='1' value='" . $site_config['hnr_config']['_3day_third'] . "'>{$lang['hnr_settings_hours']}</td></tr>
    <tr><td colspan='2'><div class='has-text-centered size_6'>{$lang['hnr_settings_group2']}</div></td></tr>

    <tr><td class='w-50'>{$lang['hnr_settings_seed1_2']}</td><td><input type='number' name='_14day_first' min='0' max='4320' step='1' value='" . $site_config['hnr_config']['_14day_first'] . "'>{$lang['hnr_settings_hours']}</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_seed2_2']}</td><td><input type='number' name='_14day_second' min='0' max='4320' step='1' value='" . $site_config['hnr_config']['_14day_second'] . "'>{$lang['hnr_settings_hours']}</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_seed3_2']}</td><td><input type='number' name='_14day_third' min='0' max='4320' step='1' value='" . $site_config['hnr_config']['_14day_third'] . "'>{$lang['hnr_settings_hours']}</td></tr>
    <tr><td colspan='2'><div class='has-text-centered size_6'>{$lang['hnr_settings_group3']}</div></td></tr>

    <tr><td class='w-50'>{$lang['hnr_settings_seed1_3']}</td><td><input type='number' name='_14day_over_first' min='0' max='4320' step='1' value='" . $site_config['hnr_config']['_14day_over_first'] . "'>Hours</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_seed2_3']}</td><td><input type='number' name='_14day_over_second' min='0' max='4320' step='1'  value='" . $site_config['hnr_config']['_14day_over_second'] . "'>{$lang['hnr_settings_hours']}</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_seed3_3']}</td><td><input type='number' name='_14day_over_third' min='0' max='4320' step='1' value='" . $site_config['hnr_config']['_14day_over_third'] . "'>{$lang['hnr_settings_hours']}</td></tr>
    <tr><td colspan='2'></td></tr>

    <tr><td class='w-50'>{$lang['hnr_settings_tallow']}</td><td><input type='number' name='caindays' min='0' max='31' step='0.1' value='" . $site_config['hnr_config']['caindays'] . "'>{$lang['hnr_settings_days']}</td></tr>
    <tr><td class='w-50'>{$lang['hnr_settings_allow']}</td><td><input type='number' name='cainallowed' min='0' max='500' step='1' value='" . $site_config['hnr_config']['cainallowed'] . "'></td></tr>
    
    <tr><td colspan='2'></td></tr>
    <tr>
        <td class='w-50'>{$lang['hnr_settings_completed']}</td>
        <td>
            <input type='radio' name='all_torrents' value='1' " . ($site_config['hnr_config']['all_torrents'] ? 'checked' : '') . ">{$lang['hnr_settings_yes']}
            <input type='radio' name='all_torrents' value='0' " . (!$site_config['hnr_config']['all_torrents'] ? 'checked' : '') . ">{$lang['hnr_settings_no']}
        </td>
    </tr>

    <tr><td colspan='2' class='has-text-centered'><input type='submit' value='{$lang['hnr_settings_apply']}' class='button is-small'></td></tr>") . '</form>';

echo stdhead($lang['hnr_settings_stdhead']) . wrapper($HTMLOUT) . stdfoot();
