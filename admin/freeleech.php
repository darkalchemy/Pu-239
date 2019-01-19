<?php

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $fluent;

$lang = array_merge($lang, load_language('ad_freeleech'));
$checked1 = $checked2 = $checked3 = $checked4 = $HTMLOUT = '';
$free = json_decode(file_get_contents(CACHE_DIR . 'free_cache.php'), true);
if (isset($_GET['remove'])) {
    if (!empty($free[$_GET['remove']])) {
        unset($free[$_GET['remove']]);
    }
    $free = array_values($free);
    file_put_contents(CACHE_DIR . 'free_cache.php', json_encode($free) . PHP_EOL);
    clearstatcache();
    sleep(3);
    header("Location: {$site_config['baseurl']}/staffpanel.php?tool=freeleech");
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fl['modifier'] = (isset($_POST['modifier']) ? (int) $_POST['modifier'] : false);
    if (isset($_POST['expires']) && $_POST['expires'] == 255) {
        $fl['expires'] = 1;
    } else {
        $fl['expires'] = (isset($_POST['expires']) ? ($_POST['expires'] * 86400 + TIME_NOW) : false);
    }
    $fl['setby'] = (isset($_POST['setby']) ? htmlsafechars($_POST['setby']) : false);
    $fl['title'] = (isset($_POST['title']) ? htmlsafechars($_POST['title']) : false);
    $fl['message'] = (isset($_POST['message']) ? htmlsafechars($_POST['message']) : false);
    if ($fl['modifier'] === false || $fl['expires'] === false || $fl['setby'] === false || $fl['title'] === false || $fl['message'] === false) {
        echo '' . $lang['freeleech_error_form'] . '';
        die();
    }
    $i = 0;
    foreach ($free as $temp) {
        if ($temp['modifier'] == $fl['modifier']) {
            unset($free[$i]);
        }
        ++$i;
    }
    $free = array_values($free);
    $free[] = [
        'modifier' => $fl['modifier'],
        'expires' => $fl['expires'],
        'setby' => $fl['setby'],
        'title' => $fl['title'],
        'message' => $fl['message'],
    ];
    file_put_contents(CACHE_DIR . 'free_cache.php', json_encode($free) . PHP_EOL);
    clearstatcache();
    sleep(3);
    header("Location: {$site_config['baseurl']}/staffpanel.php?tool=freeleech");
    die();
}
$HTMLOUT .= '<h1 class="has-text-centered">' . $lang['freeleech_current'] . '</h1>';
if (isset($free) && (count($free) < 1)) {
    $HTMLOUT .= stdmsg($lang['freeleech_nofound'], 'has-text-centered bottom20');
} else {
    $heading .= "
        <tr>
            <th>{$lang['freeleech_free_all']}</th>
            <th>{$lang['freeleech_expires']}</th>
            <th>{$lang['freeleech_setby']}</th>
            <th>{$lang['freeleech_title']}</th>
            <th>{$lang['freeleech_message']}</th>
            <th>{$lang['freeleech_remove']}</th>
        </tr>";
    $i = 0;
    $body = '';
    foreach ($free as $fl) {
        switch ($fl['modifier']) {
            case 1:
                $checked1 = 'checked=\'checked\'';
                $mode = $lang['freeleech_torr_free'];
                break;

            case 2:
                $mode = $lang['freeleech_double_up'];
                $checked2 = 'checked=\'checked\'';
                break;

            case 3:
                $mode = $lang['freeleech_free_double'];
                $checked3 = 'checked=\'checked\'';
                break;

            case 4:
                $mode = $lang['freeleech_torr_silver'];
                $checked4 = 'checked=\'checked\'';
                break;

            default:
                $mode = $lang['freeleech_not_enable'];
        }
        $body .= "
            <tr>
                <td>$mode</td>
                <td>" . ($fl['expires'] != 'Inf.' && $fl['expires'] != 1 ? "{$lang['freeleech_until']}" . get_date($fl['expires'], 'DATE') . ' (' . mkprettytime($fl['expires'] - TIME_NOW) . "{$lang['freeleech_togo']})" : '' . $lang['freeleech_unlimited'] . '') . " </td>
                <td>{$fl['setby']}</td>
                <td>{$fl['title']}</td>
                <td>{$fl['message']}</td>
                <td><a href='{$site_config['baseurl']}/staffpanel.php?tool=freeleech&amp;action=freeleech&amp;remove={$i}' class='button is-small'>{$lang['freeleech_remove']}</a></td>
            </tr>";
        ++$i;
    }
    $HTMLOUT .= main_table($body, $heading);
}
$checked = ' checked';

$HTMLOUT .= "
    <h2 class='has-text-centered'>{$lang['freeleech_set_free']}</h2>
    <form method='post' action='{$site_config['baseurl']}/staffpanel.php?tool=freeleech&amp;action=freeleech'>
    <table class='table table-bordered table-striped'>
    <tr><td class='rowhead'>{$lang['freeleech_mode']}</td>
    <td> <table width='100%'>
 <tr>
 <td>{$lang['freeleech_torr_free']}</td>
 <td><input name='modifier' type='radio' $checked1 value='1'></td>
 </tr>
 <tr>
 <td>{$lang['freeleech_double_up']}</td>
 <td><input name='modifier' type='radio' $checked2 value='2'></td>
 </tr>
 <tr>
 <td >{$lang['freeleech_free_double']}</td>
 <td><input name='modifier' type='radio' $checked3 value='3'></td></tr>
 <tr>
 <td >{$lang['freeleech_torr_silver']}</td>
 <td><input name='modifier' type='radio' $checked4 value='4'></td></tr>
 </table>
    </td></tr>
    <tr><td class='rowhead'>{$lang['freeleech_expire']}
    </td><td>
    <select name='expires'>
    <option value='1'>{$lang['freeleech_1day']}</option>
    <option value='2'>{$lang['freeleech_2days']}</option>
    <option value='5'>{$lang['freeleech_5days']}</option>
    <option value='7'>{$lang['freeleech_7days']}</option>
    <option value='255'>{$lang['freeleech_unlimited']}</option>
    </select></td></tr>
    <tr><td class='rowhead'>{$lang['freeleech_title']}</td>
    <td><input type='text' class= 'w-100' name='title' placeholder='{$lang['freeleech_title']}'>
    </td></tr>
        <tr><td class='rowhead'>{$lang['freeleech_message']}</td>
    <td><input type='text' class='w-100' name='message' placeholder='{$lang['freeleech_message']}'>
    </td></tr>
            <tr><td class='rowhead'>{$lang['freeleech_setby']}</td>
    <td><input type='text' class='w-100' value ='" . $CURUSER['username'] . "' name='setby'>
    </td></tr>
    <tr><td colspan='2' class='has-text-centered'>
    <input type='submit' name='okay' value='{$lang['freeleech_doit']}' class='button is-small'>
    <input type='hidden' name='cacheit' value='{$lang['freeleech_cache']}'>
    </td></tr>
    </table></form>";

echo stdhead($lang['freeleech_stdhead']) . wrapper($HTMLOUT) . stdfoot();
die();
