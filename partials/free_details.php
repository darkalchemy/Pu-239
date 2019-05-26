<?php

declare(strict_types = 1);

global $site_config, $CURUSER;

$is = $fl = '';
$isfree['yep'] = $isfree['expires'] = 0;
$freeimg = '<img src="' . $site_config['paths']['images_baseurl'] . 'freedownload.gif" alt="Free download" class="tooltipper icon" title="Free download">';
$silverimg = '<img src="' . $site_config['paths']['images_baseurl'] . 'silverdownload.gif" alt="Silver Torrent" class="tooltipper icon" title="Silver Torrent">';
if (isset($free)) {
    foreach ($free as $fl) {
        switch ($fl['modifier']) {
            case 1:
                $mode = 'All Torrents Free';
                break;

            case 2:
                $mode = 'All Double Upload';
                break;

            case 3:
                $mode = 'All Torrents Free and Double Upload';
                break;

            case 4:
                $mode = 'All Torrents Silver';
                break;

            default:
                $mode = 0;
        }
        $isfree['yep'] = ($fl['modifier'] != 0) && ($fl['expires'] > TIME_NOW || $fl['expires'] == 1);
        $isfree['expires'] = $fl['expires'];
    }
}
$in_use = (($torrent['free'] != 0 || $torrent['silver'] != 0 || $CURUSER['free_switch'] != 0 || $isfree['yep']) ? '<span> Free Status ' . ($torrent['free'] != 0 ? $freeimg . '<b><span style="color: ' . $torrent['free_color'] . ';"> Torrent FREE </span></b> ' . ($torrent['free'] > 1 ? ' Expires: ' . get_date((int) $torrent['free'], 'DATE', 1, 0) . '
(' . mkprettytime($torrent['free'] - TIME_NOW) . ' to go)<br>' : 'Unlimited<br>') : '') : '') . ($torrent['silver'] != 0 ? $silverimg . ' <b><span color="' . $torrent['silver_color'] . '">Torrent SILVER</span></b> ' . ($torrent['silver'] > 1 ? 'Expires: ' . get_date((int) $torrent['silver'], 'DATE', 1, 0) . ' 
(' . mkprettytime($torrent['silver'] - TIME_NOW) . ' to go)<br>' : 'Unlimited<br>') : '') . ($CURUSER['free_switch'] != 0 ? $freeimg . ' <b><span color="' . $torrent['free_color'] . '">Personal FREE Status</span></b> ' . ($CURUSER['free_switch'] > 1 ? 'Expires: ' . get_date((int) $CURUSER['free_switch'], 'DATE', 1, 0) . ' 
(' . mkprettytime($CURUSER['free_switch'] - TIME_NOW) . ' to go)<br>' : 'Unlimited<br>') : '') . ($isfree['yep'] ? $freeimg . ' <b><span color="' . $torrent['free_color'] . '">' . $mode . '</span></b> ' . ($isfree['expires'] != 1 ? 'Expires: ' . get_date((int) $isfree['expires'], 'DATE', 1, 0) . ' 
(' . mkprettytime($isfree['expires'] - TIME_NOW) . ' to go)<br>' : 'Unlimited<br>') : '') . (($torrent['free'] != 0 || $torrent['silver'] != 0 || $CURUSER['free_switch'] != 0 || $isfree['yep']) ? '</span>' : '') . '';

if (!empty($in_use)) {
    $title .= "<div class='round10 padding20 bg-00 bottom20 level-center'>$in_use</div>";
}
