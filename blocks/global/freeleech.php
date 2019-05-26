<?php

declare(strict_types = 1);
global $CURUSER;

$free = $cache->get('site_events_');
if ($CURUSER) {
    if (!empty($free) && $free['modifier'] != 0) {
        switch ($free['modifier']) {
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
        $username = format_username((int) $free['setby']);
        $htmlout .= ($free['modifier'] != 0 && $free['expires'] > TIME_NOW ? "
    <li>
        <a href='#'>
            <span class='button tag is-success dt-tooltipper-small' data-tooltip-content='#free_tooltip_{$free['modifier']}'>
                {$lang['gl_freeleech']}
            </span>
            <div class='tooltip_templates'>
                <div id='free_tooltip_{$free['modifier']}' class='margin20'>
                    <div class='size_6 has-text-centered has-text-info has-text-weight-bold bottom10'>
                        {$mode}
                    </div>
                    <div class='has-text-centered'>
                        {$free['title']} {$lang['gl_freeleech_sb']} {$username}<br>" . ($free['expires'] != 1 ? $lang['gl_freeleech_u'] . ' ' . get_date((int) $free['expires'], 'DATE') . ' (' . mkprettytime($free['expires'] - TIME_NOW) . ' ' . $lang['gl_freeleech_tg'] . ')' : '') . '
                    </div>
                </div>
            </div>
        </a>
    </li>' : '');
    }
}
