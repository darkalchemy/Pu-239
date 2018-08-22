<?php

require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $fluent;

$lang = array_merge($lang, load_language('ad_freelech'));
$HTMLOUT = '';
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
        echo '' . $lang['freelech_error_form'] . '';
        die();
    }
    $i = 0;
    foreach ($free as $temp) {
        if ($temp['modifier'] == $fl['modifier']) {
            unset($free[$i]);
        }
        $i++;
    }
    $free = array_values($free);
    $free[] = [
        'modifier'=> $fl['modifier'],
        'expires'=> $fl['expires'],
        'setby'=> $fl['setby'],
        'title'=> $fl['title'],
        'message'=> $fl['message'],
    ];
    file_put_contents(CACHE_DIR . 'free_cache.php', json_encode($free) . PHP_EOL);
    clearstatcache();
    sleep(3);
    header("Location: {$site_config['baseurl']}/staffpanel.php?tool=freeleech");
    die();
}
$HTMLOUT .= '<h1 class="has-text-centered">' . $lang['freelech_current'] . '</h1>';
if (isset($free) && (count($free) < 1)) {
    $HTMLOUT .= main_div($lang['freelech_nofound'], 'has-text-centered bottom20');
} else {
    $HTMLOUT .= "<table class='table table-bordered table-striped'>
        <tr><td class='colhead'>{$lang['freelech_free_all']}</td>
        <td class='colhead'>{$lang['freelech_expires']}</td>
        <td class='colhead'>{$lang['freelech_setby']}</td>
        <td class='colhead'>{$lang['freelech_title']}</td>
        <td class='colhead'>{$lang['freelech_message']}</td>
        <td class='colhead'>{$lang['freelech_remove']}</td></tr>";
    $checked1 = $checked2 = $checked3 = $checked4 = '';
    $i = 0;
    foreach ($free as $fl) {
        switch ($fl['modifier']) {
            case 1:
                $checked1 = 'checked=\'checked\'';
                $mode = $lang['freelech_torr_free'];
                break;

            case 2:
                $mode = $lang['freelech_double_up'];
                $checked2 = 'checked=\'checked\'';
                break;

            case 3:
                $mode = $lang['freelech_free_double'];
                $checked3 = 'checked=\'checked\'';
                break;

            case 4:
                $mode = $lang['freelech_torr_silver'];
                $checked4 = 'checked=\'checked\'';
                break;

            default:
                $mode = $lang['freelech_not_enable'];
        }
        $HTMLOUT .= "<tr><td>$mode
             </td><td>" . ($fl['expires'] != 'Inf.' && $fl['expires'] != 1 ? "{$lang['freelech_until']}" . get_date($fl['expires'], 'DATE') . ' (' . mkprettytime($fl['expires'] - TIME_NOW) . "{$lang['freelech_togo']})" : '' . $lang['freelech_unlimited'] . '') . " </td>
             <td>{$fl['setby']}</td>
             <td>{$fl['title']}</td>
             <td>{$fl['message']}</td>
             <td><a href='staffpanel.php?tool=freeleech&amp;action=freeleech&amp;remove={$i}' class='button is-small'>{$lang['freelech_remove']}</a>
             </td></tr>";
        $i++;
    }
    $HTMLOUT .= '</table>';
}
$checked = 'checked=\'checked\'';

$HTMLOUT .= "<h2 class='has-text-centered'>{$lang['freelech_set_free']}</h2>
    <form method='post' action='staffpanel.php?tool=freeleech&amp;action=freeleech'>
    <table class='table table-bordered table-striped'>
    <tr><td class='rowhead'>{$lang['freelech_mode']}</td>
    <td> <table width='100%'>
 <tr>
 <td>{$lang['freelech_torr_free']}</td>
 <td><input name=\"modifier\" type=\"radio\" $checked1 value=\"1\" /></td>
 </tr>
 <tr>
 <td>{$lang['freelech_double_up']}</td>
 <td><input name=\"modifier\" type=\"radio\" $checked2 value=\"2\" /></td>
 </tr>
 <tr>
 <td >{$lang['freelech_free_double']}</td>
 <td><input name=\"modifier\" type=\"radio\" $checked3 value=\"3\" /></td></tr>
 <tr>
 <td >{$lang['freelech_torr_silver']}</td>
 <td><input name=\"modifier\" type=\"radio\" $checked4 value=\"4\" /></td></tr>
 </table>
    </td></tr>
    <tr><td class='rowhead'>{$lang['freelech_expire']}
    </td><td>
    <select name='expires'>
    <option value='1'>{$lang['freelech_1day']}</option>
    <option value='2'>{$lang['freelech_2days']}</option>
    <option value='5'>{$lang['freelech_5days']}</option>
    <option value='7'>{$lang['freelech_7days']}</option>
    <option value='255'>{$lang['freelech_unlimited']}</option>
    </select></td></tr>
    <tr><td class='rowhead'>{$lang['freelech_title']}</td>
    <td><input type='text' class= 'w-100'' name='title' placeholder='{$fl['title']}' />
    </td></tr>
        <tr><td class='rowhead'>{$lang['freelech_message']}</td>
    <td><input type='text' class='w-100' name='message' placeholder='{$fl['message']}' />
    </td></tr>
            <tr><td class='rowhead'>{$lang['freelech_setby']}</td>
    <td><input type='text' class='w-100' value ='" . $CURUSER['username'] . "' name='setby' />
    </td></tr>
    <tr><td colspan='2' class='has-text-centered'>
    <input type='submit' name='okay' value='{$lang['freelech_doit']}' class='button is-small' />
    <input type='hidden' name='cacheit' value='{$lang['freelech_cache']}' />
    </td></tr>
    </table></form>";

echo stdhead($lang['freelech_stdhead']) . wrapper($HTMLOUT) . stdfoot();
die();
