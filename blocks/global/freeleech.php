<?php

declare(strict_types = 1);
$user = check_user_status();

$free = $cache->get('site_events_');
if ($user) {
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
                " . _('FreeLeech ON') . "
            </span>
            <div class='tooltip_templates'>
                <div id='free_tooltip_{$free['modifier']}' class='margin20'>
                    <div class='size_6 has-text-centered has-text-info has-text-weight-bold bottom10'>
                        {$mode}
                    </div>
                    <div class='has-text-centered'>
                        " . _('%1$s set by %2$s<br>%3$s', $free['title'], $username) . ($free['expires'] != 1 ? _(' Until %1$s (%2$s to go).', get_date((int) $free['expires'], 'DATE'), mkprettytime($free['expires'] - TIME_NOW)) : '') . '
                    </div>
                </div>
            </div>
        </a>
    </li>' : '');
    }
}
