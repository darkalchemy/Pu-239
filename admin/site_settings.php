<?php
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_sitesettings'));
$site_settings = $current_site_settings = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $pconf = sql_query('SELECT * FROM site_config') or sqlerr(__FILE__, __LINE__);
    while ($ac = mysqli_fetch_assoc($pconf)) {
        $current_site_settings[$ac[name]] = [value => $ac[value], description => $ac[description]];
    }
    $update = [];
    foreach ($_POST as $key => $value) {
        if ($key != 'new' && ($value["description"] != $current_site_settings[$key]["description"] || $value["value"] != $current_site_settings[$key]["value"])) {
            $update[] = '(' . sqlesc($key) . ', ' . sqlesc(trim($value["value"])) . ', ' . sqlesc(trim($value["description"])) . ')';
        } elseif ($key === 'new' && isset($value["value"]) && $value["value"] != '') {
            extract($value);
            $update[] = '(' . sqlesc(strtolower(str_replace(' ', '_', trim($setting)))) . ', ' . sqlesc(trim($value)) . ', ' . sqlesc(trim($description)) . ')';
        }
    }
    if (!empty($update) && sql_query('INSERT INTO site_config(name, value, description) VALUES ' . join(', ', $update) . ' ON DUPLICATE KEY update value = VALUES(value), description = VALUES(description)')) {
        $mc1->delete_value('site_settings_');
        setSessionVar('success', 'Update Successful');
    } else {
        setSessionVar('error', $lang['sitesettings_stderr3']);
    }
}
unset($_POST);
$pconf = sql_query('SELECT * FROM site_config ORDER BY name') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($pconf)) {
    $site_settings[] = $ac;
}
$HTMLOUT .= "
        <div class='container-fluid portlet'>
            <h3 class='text-center top20'>{$lang['sitesettings_sitehead']}</h3>
            <form action='./staffpanel.php?tool=site_settings' method='post'>
                <table class='table table-bordered table-striped bottom20'>";
foreach ($site_settings as $site_setting) {
    extract($site_setting);
    if (is_numeric($value)) {
        $value = (double)$value;
    }
    $var = $name . '[value]';
    $input = "
                        <input type='text' name='{$var}' value='" . htmlsafechars($value) . "' class='w-100' />";
    if (is_numeric($value) && ($value == 0 || $value == 1)) {
        $input = "
                        <div class='flex flex-justify-center'>
                            <label for ='{$var}' class='right10'>{$lang['sitesettings_no']}
                                <input class='table' type='radio' name='{$var}' value='0' " . ((int)$value === 0 ? 'checked' : '') . " />
                            </label>
                            <label for ='{$var}' class='right10'>{$lang['sitesettings_yes']}
                                <input class='table' type='radio' name='{$var}' value='1' " . ((int)$value === 1 ? 'checked' : '') . " />
                            </label>
                        </div>";
    }

    $var = $name . '[description]';
    $HTMLOUT .= "
                    <tr>
                        <td>
                            " . htmlsafechars(ucwords(str_replace('_', ' ', $name))) . "
                        </td>
                        <td>
                            $input
                        </td>
                        <td class='w-50'>
                            <textarea name='{$var}' class='w-100'>" . htmlsafechars($description) . "</textarea>
                        </td>
                    </tr>";
}

$name = 'new[setting]';
$value = 'new[value]';
$descr = 'new[description]';
$HTMLOUT .= "
                    <tr>
                        <td>
                            <input type='text' name='{$name}' value='' class='w-100' placeholder='New Site Setting Name' />
                        </td>
                        <td>
                            <input type='text' name='{$value}' value='' class='w-100' placeholder='Use 0 for false, 1 for true, or anyother int/float as needed.' />
                        </td>
                        <td>
                            <textarea name='{$descr}' class='w-100' placeholder='Description'></textarea>
                        </td>
                    </tr>
                </table>
                <div class='text-center bottom20'>
                    <input type='submit' value='{$lang['sitesettings_apply']}' />
                </div>
            </form>
        </div>";
/*
$HTMLOUT .= "<h3>{$lang['sitesettings_sitehead']}</h3>
<form action='staffpanel.php?tool=site_settings' method='post'>
<table class='table table-bordered table-striped'>";
if ($CURUSER['id'] === 1) {
    $HTMLOUT .= "<tr><td width='50%' class='table'>{$lang['sitesettings_online']}</td><td class='table'>{$lang['sitesettings_yes']}<input class='table' type='radio' name='site_online' value='1' " . ($site_settings['site_online'] ? 'checked=\'checked\'' : '') . " />{$lang['sitesettings_no']}<input class='table' type='radio' name='site_online' value='0' " . (!$site_settings['site_online'] ? 'checked=\'checked\'' : '') . ' /></td></tr>';
}
$HTMLOUT .= "<tr><td width='50%' class='table'>{$lang['sitesettings_autoshout']}</td><td class='table'>{$lang['sitesettings_yes']}
<input class='table' type='radio' name='autoshout_on' value='1' " . ($site_settings['autoshout_on'] ? 'checked=\'checked\'' : '') . " />{$lang['sitesettings_no']}<input class='table' type='radio' name='autoshout_on' value='0' " . (!$site_settings['autoshout_on'] ? 'checked=\'checked\'' : '') . " /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_seedbonus']}</td><td class='table'>{$lang['sitesettings_yes']}<input class='table' type='radio' name='seedbonus_on' value='1' " . ($site_settings['seedbonus_on'] ? 'checked=\'checked\'' : '') . " />{$lang['sitesettings_no']}<input class='table' type='radio' name='seedbonus_on' value='0' " . (!$site_settings['seedbonus_on'] ? 'checked=\'checked\'' : '') . " /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bpdur']}</td><td class='table'><input type='text' name='bonus_per_duration' size='3' value='" . htmlsafechars($site_settings['bonus_per_duration']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bpdload']}</td><td class='table'><input type='text' name='bonus_per_download' size='3' value='" . htmlsafechars($site_settings['bonus_per_download']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bpcomm']}</td><td class='table'><input type='text' name='bonus_per_comment' size='3' value='" . htmlsafechars($site_settings['bonus_per_comment']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bpupload']}</td><td class='table'><input type='text' name='bonus_per_upload' size='3' value='" . htmlsafechars($site_settings['bonus_per_upload']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bprate']}</td><td class='table'><input type='text' name='bonus_per_rating' size='3' value='" . htmlsafechars($site_settings['bonus_per_rating']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bptopic']}</td><td class='table'><input type='text' name='bonus_per_topic' size='3' value='" . htmlsafechars($site_settings['bonus_per_topic']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bppost']}</td><td class='table'><input type='text' name='bonus_per_post' size='3' value='" . htmlsafechars($site_settings['bonus_per_post']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bpdel']}</td><td class='table'><input type='text' name='bonus_per_delete' size='3' value='" . htmlsafechars($site_settings['bonus_per_delete']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_bptnk']}</td><td class='table'><input type='text' name='bonus_per_thanks' size='3' value='" . htmlsafechars($site_settings['bonus_per_thanks']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_forums']}</td><td class='table'>{$lang['sitesettings_yes']}<input class='table' type='radio' name='forums_online' value='1' " . ($site_settings['forums_online'] ? 'checked=\'checked\'' : '') . " />{$lang['sitesettings_no']}<input class='table' type='radio' name='forums_online' value='0' " . (!$site_settings['forums_online'] ? 'checked=\'checked\'' : '') . " /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_openreg']}</td><td class='table'><input type='text' name='openreg' size='2' value='" . htmlsafechars($site_settings['openreg']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_openinvite']}</td><td class='table'><input type='text' name='openreg_invites' size='2' value='" . htmlsafechars($site_settings['openreg_invites']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_auto_confirm']}</td><td class='table'><input type='text' name='auto_confirm' size='2' value='" . htmlsafechars($site_settings['auto_confirm']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_email_confirm']}</td><td class='table'><input type='text' name='email_confirm' size='2' value='" . htmlsafechars($site_settings['email_confirm']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_maxusers']}</td><td class='table'><input type='text' name='maxusers' size='2' value='" . htmlsafechars($site_settings['maxusers']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_maxinvite']}</td><td class='table'><input type='text' name='invites' size='2' value='" . htmlsafechars($site_settings['invites']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_maxlogins']}</td><td class='table'><input type='text' name='failedlogins' size='2' value='" . htmlsafechars($site_settings['failedlogins']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_ratio']}</td><td class='table'><input type='text' name='ratio_free' size='2' value='" . htmlsafechars($site_settings['ratio_free']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_captcha']}</td><td class='table'><input type='text' name='captcha_on' size='2' value='" . htmlsafechars($site_settings['captcha_on']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_dupe']}</td><td class='table'><input type='text' name='dupeip_check_on' size='2' value='" . htmlsafechars($site_settings['dupeip_check_on']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['sitesettings_donation']}</td><td class='table'><input type='text' name='totalneeded' size='2' value='" . htmlsafechars($site_settings['totalneeded']) . "' /></td></tr>
<tr><td colspan='2' class='table'><input type='submit' value='{$lang['sitesettings_apply']}' /></td></tr>
</table></form>";
*/
echo stdhead($lang['sitesettings_stdhead']) . $HTMLOUT . stdfoot();
