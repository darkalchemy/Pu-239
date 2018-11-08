<?php

require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
global $CURUSER, $site_config, $lang, $user_stuffs, $user;

if ($user['paranoia'] < 2 || $user['opt1'] & user_options::HIDECUR || $CURUSER['id'] == $user['id'] || $CURUSER['class'] >= UC_STAFF) {
    $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_uploaded_t']}</td>
            <td>
                <a id='torrents-hash'></a>
                <fieldset id='torrents' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i>View Torrents you uploaded</legend>
                    <div id='inner_torrents' data-uid='{$user['id']}' data-csrf='" . $session->get('csrf_token') . "'></div>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['userdetails_cur_seed']}</td>
            <td>
                <a id='seeding-hash'></a>
                <fieldset id='seeding' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i>View Torrents you are currently seeding</legend>
                    <div id='inner_seeding' data-uid='{$user['id']}' data-csrf='" . $session->get('csrf_token') . "'></div>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['userdetails_cur_leech']}</td>
            <td>
                <a id='leeching-hash'></a>
                <fieldset id='leeching' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i>View Torrents you are currently leeching</legend>
                    <div id='inner_leeching' data-uid='{$user['id']}' data-csrf='" . $session->get('csrf_token') . "'></div>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['userdetails_cur_snatched']}</td>
            <td>
                <a id='snatched-hash'></a>
                <fieldset id='snatched' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i>View Torrents you have snatched</legend>
                    <div id='inner_snatched' data-uid='{$user['id']}' data-csrf='" . $session->get('csrf_token') . "'></div>
                </fieldset>
            </td>
        </tr>";
}
