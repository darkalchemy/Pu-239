<?php

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $cache, $session;

$lang = array_merge($lang, load_language('ad_hit_and_run_settings'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($site_config['hnr_config'] as $c_name => $c_value) {
        if (isset($_POST[$c_name]) && $_POST[$c_name] != $c_value) {
            $update[] = '(' . sqlesc($c_name) . ',' . sqlesc(is_array($_POST[$c_name]) ? join('|', $_POST[$c_name]) : $_POST[$c_name]) . ')';
        }
    }
    if (sql_query('INSERT INTO hit_and_run_settings(name,value) VALUES ' . join(',', $update) . ' ON DUPLICATE KEY UPDATE value = VALUES(value)')) {
        $cache->delete('hnr_settings_');
        $session->set('is-success', 'Update Successful');
    } else {
        $session->set('is-warning', $lang['hnr_settings_err_query']);
    }
}

$pconf = sql_query('SELECT * FROM hit_and_run_settings') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($pconf)) {
    $site_config['hnr_config'][$ac['name']] = $ac['value'];
}

$HTMLOUT .= "<h3>{$lang['hnr_settings_title']}</h3>
<form action='staffpanel.php?tool=hit_and_run_settings' method='post'>
<table width='100%' >";
$HTMLOUT .= "

<tr><td width='50%' class='table'>{$lang['hnr_settings_online']}</td><td class='table'>{$lang['hnr_settings_yes']}<input class='table' type='radio' name='hnr_online' value='1' " . ($site_config['hnr_config']['hnr_online'] ? 'checked=\'checked\'' : '') . " />{$lang['hnr_settings_no']}<input class='table' type='radio' name='hnr_online' value='0' " . (!$site_config['hnr_config']['hnr_online'] ? 'checked=\'checked\'' : '') . " /></td></tr>
<!-- Set Class's Here With UC_ -->
<tr><td width='50%' class='table'>{$lang['hnr_settings_fclass']}</td><td class='table'><input type='text' name='firstclass' size='20' value='" . htmlsafechars($site_config['hnr_config']['firstclass']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_sclass']}</td><td class='table'><input type='text' name='secondclass' size='20' value='" . htmlsafechars($site_config['hnr_config']['secondclass']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_tclass']}</td><td class='table'><input type='text' name='thirdclass' size='20' value='" . htmlsafechars($site_config['hnr_config']['thirdclass']) . "' /></td></tr>

<tr><td width='50%' class='table'>{$lang['hnr_settings_tage1']}</td><td class='table'><input type='number' name='torrentage1' min='0' max='31' step='0.5'value='" . htmlsafechars($site_config['hnr_config']['torrentage1']) . "' />{$lang['hnr_settings_days']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_tage2']}</td><td class='table'><input type='number' name='torrentage2' min='0' max='31' step='0.5'value='" . htmlsafechars($site_config['hnr_config']['torrentage2']) . "' />{$lang['hnr_settings_days']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_tage3']}</td><td class='table'><input type='number' name='torrentage3' min='0' max='31' step='0.5'value='" . htmlsafechars($site_config['hnr_config']['torrentage3']) . "' />{$lang['hnr_settings_days']}</td></tr>

<!-- Set the day shits -->
<tr><td width='50%' class='table'>{$lang['hnr_settings_seed1_1']}</td><td class='table'><input type='number' name='_3day_first' min='0' max='120' step='0.5' value='" . htmlsafechars($site_config['hnr_config']['_3day_first']) . "' />{$lang['hnr_settings_hours']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_seed1_2']}</td><td class='table'><input type='number' name='_14day_first' min='0' max='120' step='0.5' value='" . htmlsafechars($site_config['hnr_config']['_14day_first']) . "' />{$lang['hnr_settings_hours']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_seed1_3']}</td><td class='table'><input type='number' name='_14day_over_first' min='0' max='120' step='0.5' value='" . htmlsafechars($site_config['hnr_config']['_14day_over_first']) . "' />Hours</td></tr>

<tr><td width='50%' class='table'>{$lang['hnr_settings_seed2_1']}</td><td class='table'><input type='number' name='_3day_second' min='0' max='120' step='0.5' value='" . htmlsafechars($site_config['hnr_config']['_3day_second']) . "' />{$lang['hnr_settings_hours']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_seed2_2']}</td><td class='table'><input type='number' name='_14day_second' min='0' max='120' step='0.5' value='" . htmlsafechars($site_config['hnr_config']['_14day_second']) . "' />{$lang['hnr_settings_hours']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_seed2_3']}</td><td class='table'><input type='number' name='_14day_over_second' min='0' max='120' step='0.5'  value='" . htmlsafechars($site_config['hnr_config']['_14day_over_second']) . "' />{$lang['hnr_settings_hours']}</td></tr>

<tr><td width='50%' class='table'>{$lang['hnr_settings_seedt3_1']}</td><td class='table'><input type='number' name='_3day_third' min='0' max='120' step='0.5' value='" . htmlsafechars($site_config['hnr_config']['_3day_third']) . "' />{$lang['hnr_settings_hours']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_seedt3_2']}</td><td class='table'><input type='number' name='_14day_third' min='0' max='120' step='0.5' value='" . htmlsafechars($site_config['hnr_config']['_14day_third']) . "' />{$lang['hnr_settings_hours']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_seedt3_3']}</td><td class='table'><input type='number' name='_14day_over_third' min='0' max='120' step='0.5' value='" . htmlsafechars($site_config['hnr_config']['_14day_over_third']) . "' />{$lang['hnr_settings_hours']}</td></tr>

<tr><td width='50%' class='table'>{$lang['hnr_settings_tallow']}</td><td class='table'><input type='number' name='caindays' min='0' max='20' step='0.5'value='" . htmlsafechars($site_config['hnr_config']['caindays']) . "' />{$lang['hnr_settings_days']}</td></tr>
<tr><td width='50%' class='table'>{$lang['hnr_settings_allow']}</td><td class='table'><input type='number' name='cainallowed' min='0' max='500' step='1'value='" . htmlsafechars($site_config['hnr_config']['cainallowed']) . "' /></td></tr>

<tr><td colspan='2' class='table'><input type='submit' value='{$lang['hnr_settings_apply']}' /></td></tr>
</table></form>";
echo stdhead($lang['hnr_settings_stdhead']) . $HTMLOUT . stdfoot();
