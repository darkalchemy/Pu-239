<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_event.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
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
        stderr(_('Error'), _('Incomplete form.'));
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
$HTMLOUT .= '<h1 class="has-text-centered">' . _('Current Freeleech Status') . '</h1>';
if (isset($free) && (count($free) < 1)) {
    $HTMLOUT .= stdmsg(_('Nothing found'), '', 'has-text-centered bottom20');
} else {
    $heading = '
        <tr>
            <th>' . _('Free All Torrents') . '</th>
            <th>' . _('Started') . '</th>
            <th>' . _('Expires') . '</th>
            <th>' . _('Set By') . '</th>
            <th>' . _('Title') . '</th>
            <th>' . _('Remove') . '</th>
        </tr>';
    $i = 0;
    $body = '';
    foreach ($free as $fl) {
        $username = format_username((int) $fl['setby']);
        switch ($fl['modifier']) {
            case 1:
                $checked1 = 'checked';
                $mode = _('All Torrents Free');
                break;

            case 2:
                $mode = _('All Torrents Double Upload');
                $checked2 = 'checked';
                break;

            case 3:
                $mode = _('All Torrents Free and Double Upload');
                $checked3 = 'checked';
                break;

            case 4:
                $mode = _('All Torrents Silver');
                $checked4 = 'checked';
                break;

            default:
                $mode = _('Not Enabled');
        }
        $body .= "
            <tr>
                <td>$mode</td>
                <td>" . get_date((int) $fl['begin'], 'LONG') . '</td>
                <td>' . ($fl['expires'] != 'Inf.' && $fl['expires'] != 1 ? _('Until ') . get_date((int) $fl['expires'], 'LONG') . ' (' . mkprettytime($fl['expires'] - TIME_NOW) . _(' to go') . ')' : _('Unlimited') . '') . " </td>
                <td>{$username}</td>
                <td>{$fl['title']}</td>
                <td class='has-text-centered'>
                    <form method='post' action='{$_SERVER['PHP_SELF']}?tool=freeleech&amp;action=remove' enctype='multipart/form-data' accept-charset='utf-8'>
                        <input type='hidden' class='w-100' value ='" . $fl['expires'] . "' name='expires'>
                        <input type='" . ($fl['expires'] > TIME_NOW ? 'submit' : 'hidden') . "' name='remove' value='" . _('Remove') . "' class='button is-small'>
                    </form>
                </td>
            </tr>";
        ++$i;
    }
    $HTMLOUT .= main_table($body, $heading);
}
$checked = 'checked';

$HTMLOUT .= "
    <h2 class='has-text-centered'>" . _('Set Freeleech') . "</h2>
    <form method='post' action='{$_SERVER['PHP_SELF']}?tool=freeleech&amp;action=freeleech' enctype='multipart/form-data' accept-charset='utf-8'>
    <table class='table table-bordered table-striped'>
    <tr><td class='rowhead'>" . _('Mode') . '</td>
    <td> <table>
 <tr>
 <td>' . _('All Torrents Free') . "</td>
 <td><input name='modifier' type='radio' {$checked1} value='1'></td>
 </tr>
 <tr>
 <td>" . _('All Torrents Double Upload') . "</td>
 <td><input name='modifier' type='radio' {$checked2} value='2'></td>
 </tr>
 <tr>
 <td>" . _('All Torrents Free and Double Upload') . "</td>
 <td><input name='modifier' type='radio' {$checked3} value='3'></td></tr>
 <tr>
 <td>" . _('All Torrents Silver') . "</td>
 <td><input name='modifier' type='radio' {$checked4} value='4'></td></tr>
 </table>
    </td></tr>
    <tr><td class='rowhead'>" . _('Expires in ') . "
    </td><td>
    <select name='expires'>
    <option value='1'>" . _pf('%d day', '%d days', 1) . "</option>
    <option value='2'>" . _pf('%d day', '%d days', 2) . "</option>
    <option value='3'>" . _pf('%d day', '%d days', 3) . "</option>
    <option value='5'>" . _pf('%d day', '%d days', 5) . "</option>
    <option value='7'>" . _pf('%d day', '%d days', 6) . "</option>
    <option value='255'>" . _('Unlimited') . "</option>
    </select></td></tr>
    <tr><td class='rowhead'>" . _('Title') . "</td>
    <td><input type='text' class= 'w-100' name='title' placeholder='" . _('Title') . "'>
    </td></tr>
    <tr><td class='rowhead'>" . _('Set By') . '</td>
    <td><span>' . format_username($CURUSER['id']) . "</span>
    </td></tr>
    <tr><td colspan='2' class='has-text-centered'>
    <input type='hidden' class='w-100' value ='" . $CURUSER['id'] . "' name='setby'>
    <input type='submit' name='okay' value='" . _('Do it!') . "' class='button is-small'>
    </td></tr>
    </table></form>";

$title = _('Freeleech Status');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
