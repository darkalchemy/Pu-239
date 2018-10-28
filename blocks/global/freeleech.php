<?php

global $CURUSER, $lang, $free;

if ($CURUSER) {
    if (isset($free) && is_array($free) && (count($free) >= 1)) {
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
            $htmlout .= ($fl['modifier'] != 0 && $fl['expires'] > TIME_NOW ? "
    <li>
        <a href='#'>
            <span class='button tag is-success dt-tooltipper-small' data-tooltip-content='#free_tooltip_{$fl['modifier']}'>
                {$lang['gl_freeleech']}
            </span>
            <div class='tooltip_templates'>
                <div id='free_tooltip_{$fl['modifier']}' class='margin20'>
                    <div class='size_6 has-text-centered has-text-info has-text-weight-bold bottom10'>
                        {$fl['title']}
                    </div>
                    <div class='has-text-centered'>
                        {$mode}<br>
                        {$fl['message']} {$lang['gl_freeleech_sb']} {$fl['setby']}<br>" . ($fl['expires'] != 1 ? $lang['gl_freeleech_u'] . ' ' . get_date($fl['expires'], 'DATE') . ' (' . mkprettytime($fl['expires'] - TIME_NOW) . ' ' . $lang['gl_freeleech_tg'] . ')' : '') . '
                    </div>
                </div>
            </div>
        </a>
    </li>' : '');
        }
    }
}
