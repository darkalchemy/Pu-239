<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
check_user_status();
global $CURUSER;

$lang = array_merge(load_language('global'), load_language('videoformats'));
$HTMLOUT = "
<h1 class='has-text-centered'>{$lang['videoformats_body']}</h1>";
$HTMLOUT .= main_table("
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_cam']}</b><br>
                    <br>{$lang['videoformats_cam1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_ts']}</b><br>
                    <br>{$lang['videoformats_ts1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_tc']}</b><br>
                    <br>{$lang['videoformats_tc1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_scr']}</b><br>
                    <br>{$lang['videoformats_scr1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_dvdscr']}</b><br>
                    <br>{$lang['videoformats_dvdscr1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_dvdrip']}</b><br>
                    <br>{$lang['videoformats_dvdrip1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_vhsrip']}</b><br>
                    <br>{$lang['videoformats_vhsrip1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_tvrip']}</b><br>
                    <br>{$lang['videoformats_tvrip1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_workpoint']}</b><br>
                    <br>{$lang['videoformats_workpoint1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_divxre']}</b><br>
                    <br>{$lang['videoformats_divxre1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_watermarks']}</b><br>
                    <br>{$lang['videoformats_watermarks1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_pdvd']}</b><br>
                    <br>{$lang['videoformats_pdvd1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_scene']}</b><br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_proper']}</b><br>
                    <br>{$lang['videoformats_proper1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_limited']}</b><br>
                    <br>{$lang['videoformats_limited1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_internal']}</b><br>
                    <br>{$lang['videoformats_internal1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_stv']}</b><br>
                    <br>{$lang['videoformats_stv1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_aspect']}</b><br>
                    <br>{$lang['videoformats_ws']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_repack']}</b><br>
                    <br>{$lang['videoformats_repack1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_nuked']}</b><br>
                    <br>{$lang['videoformats_nuked1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_reason']}</b>{$lang['videoformats_reason1']}<br>
                    <b>{$lang['videoformats_badar']}</b>{$lang['videoformats_badar1']}<br>
                    <b>{$lang['videoformats_badivtc']}</b>{$lang['videoformats_badivtc1']}<br>
                    <b>{$lang['videoformats_interlaced']}</b>{$lang['videoformats_interlaced1']}<br>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class='padding20'>
                    <b>{$lang['videoformats_dupe']}</b><br>
                    <br>{$lang['videoformats_dupe1']}<br>
                </div>
            </td>
        </tr>");
echo stdhead($lang['videoformats_header']) . wrapper($HTMLOUT) . stdfoot();
