<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_event.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_freeleech'));
global $site_config, $CURUSER;

$checked1 = $checked2 = $checked3 = $checked4 = $HTMLOUT = '';
$free = get_event(true);
$fl = $temp = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove'])) {
        update_event((int) $_POST['expires'], TIME_NOW);
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=freeleech");
        die();
    }
    $fl['modifier'] = isset($_POST['modifier']) ? (int) $_POST['modifier'] : false;
    if (isset($_POST['expires']) && (int) $_POST['expires'] === 255) {
        $fl['expires'] = 1;
    } else {
        $fl['expires'] = isset($_POST['expires']) ? $_POST['expires'] * 86400 + TIME_NOW : false;
    }
    $fl['setby'] = isset($_POST['setby']) ? htmlsafechars($_POST['setby']) : false;
    $fl['title'] = isset($_POST['title']) ? htmlsafechars($_POST['title']) : false;
    if ($fl['modifier'] === false || $fl['expires'] === false || $fl['setby'] === false || $fl['title'] === false) {
        echo '' . $lang['freeleech_error_form'] . '';
        die();
    }
    $i = 0;
    foreach ($free as $temp) {
        if ($temp['modifier'] === $fl['modifier']) {
            unset($free[$i]);
        }
        ++$i;
    }
    set_event($fl['modifier'], TIME_NOW, $fl['expires'], (int) $fl['setby'], $fl['title']);
    header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=freeleech");
    die();
}
$HTMLOUT .= '<h1 class="has-text-centered">' . $lang['freeleech_current'] . '</h1>';
if (isset($free) && (count($free) < 1)) {
    $HTMLOUT .= stdmsg($lang['freeleech_nofound'], '', 'has-text-centered bottom20');
} else {
    $heading = "
        <tr>
            <th>{$lang['freeleech_free_all']}</th>
            <th>{$lang['freeleech_begin']}</th>
            <th>{$lang['freeleech_expires']}</th>
            <th>{$lang['freeleech_setby']}</th>
            <th>{$lang['freeleech_title']}</th>
            <th>{$lang['freeleech_remove']}</th>
        </tr>";
    $i = 0;
    $body = '';
    foreach ($free as $fl) {
        $username = format_username((int) $fl['setby']);
        switch ($fl['modifier']) {
            case 1:
                $checked1 = 'checked';
                $mode = $lang['freeleech_torr_free'];
                break;

            case 2:
                $mode = $lang['freeleech_double_up'];
                $checked2 = 'checked';
                break;

            case 3:
                $mode = $lang['freeleech_free_double'];
                $checked3 = 'checked';
                break;

            case 4:
                $mode = $lang['freeleech_torr_silver'];
                $checked4 = 'checked';
                break;

            default:
                $mode = $lang['freeleech_not_enable'];
        }
        $body .= "
            <tr>
                <td>$mode</td>
                <td>" . get_date((int) $fl['begin'], 'LONG') . '</td>
                <td>' . ($fl['expires'] != 'Inf.' && $fl['expires'] != 1 ? $lang['freeleech_until'] . get_date((int) $fl['expires'], 'LONG') . ' (' . mkprettytime($fl['expires'] - TIME_NOW) . "{$lang['freeleech_togo']})" : '' . $lang['freeleech_unlimited'] . '') . " </td>
                <td>{$username}</td>
                <td>{$fl['title']}</td>
                <td class='has-text-centered'>
                    <form method='post' action='{$_SERVER['PHP_SELF']}?tool=freeleech&amp;action=remove' accept-charset='utf-8'>
                        <input type='hidden' class='w-100' value ='" . $fl['expires'] . "' name='expires'>
                        <input type='" . ($fl['expires'] > TIME_NOW ? 'submit' : 'hidden') . "' name='remove' value='{$lang['freeleech_remove']}' class='button is-small'>
                    </form>
                </td>
            </tr>";
        ++$i;
    }
    $HTMLOUT .= main_table($body, $heading);
}
$checked = 'checked';

$HTMLOUT .= "
    <h2 class='has-text-centered'>{$lang['freeleech_set_free']}</h2>
    <form method='post' action='{$_SERVER['PHP_SELF']}?tool=freeleech&amp;action=freeleech' accept-charset='utf-8'>
    <table class='table table-bordered table-striped'>
    <tr><td class='rowhead'>{$lang['freeleech_mode']}</td>
    <td> <table>
 <tr>
 <td>{$lang['freeleech_torr_free']}</td>
 <td><input name='modifier' type='radio' {$checked1} value='1'></td>
 </tr>
 <tr>
 <td>{$lang['freeleech_double_up']}</td>
 <td><input name='modifier' type='radio' {$checked2} value='2'></td>
 </tr>
 <tr>
 <td>{$lang['freeleech_free_double']}</td>
 <td><input name='modifier' type='radio' {$checked3} value='3'></td></tr>
 <tr>
 <td>{$lang['freeleech_torr_silver']}</td>
 <td><input name='modifier' type='radio' {$checked4} value='4'></td></tr>
 </table>
    </td></tr>
    <tr><td class='rowhead'>{$lang['freeleech_expire']}
    </td><td>
    <select name='expires'>
    <option value='1'>{$lang['freeleech_1day']}</option>
    <option value='2'>{$lang['freeleech_2days']}</option>
    <option value='3'>{$lang['freeleech_3days']}</option>
    <option value='5'>{$lang['freeleech_5days']}</option>
    <option value='7'>{$lang['freeleech_7days']}</option>
    <option value='255'>{$lang['freeleech_unlimited']}</option>
    </select></td></tr>
    <tr><td class='rowhead'>{$lang['freeleech_title']}</td>
    <td><input type='text' class= 'w-100' name='title' placeholder='{$lang['freeleech_title']}'>
    </td></tr>
    <tr><td class='rowhead'>{$lang['freeleech_setby']}</td>
    <td><span>" . format_username($CURUSER['id']) . "</span>
    </td></tr>
    <tr><td colspan='2' class='has-text-centered'>
    <input type='hidden' class='w-100' value ='" . $CURUSER['id'] . "' name='setby'>
    <input type='submit' name='okay' value='{$lang['freeleech_doit']}' class='button is-small'>
    </td></tr>
    </table></form>";

echo stdhead($lang['freeleech_stdhead']) . wrapper($HTMLOUT) . stdfoot();
die();
