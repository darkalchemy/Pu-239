<?php

global $CURUSER, $site_config, $lang, $fluent, $cache;

if ($site_config['bug_alert'] && $CURUSER['class'] >= UC_STAFF) {
    $bugs = $cache->get('bug_mess_');
    if ($bugs === false || is_null($bugs)) {
        $bugs = $fluent->from('bugs')
            ->select(null)
            ->select('COUNT(id) AS count')
            ->where('status = ?', 'na')
            ->fetch('count');

        $cache->set('bug_mess_', $bugs, $site_config['expires']['alerts']);
    }
    if ($bugs > 0) {
        $htmlout .= "
    <li>
        <a href='{$site_config['baseurl']}/bugs.php?action=bugs'>
            <span class='button tag is-danger dt-tooltipper-small' data-tooltip-content='#bugmessage_tooltip'>
                {$lang['gl_bug_alert']}
            </span>
            <div class='tooltip_templates'>
                <div id='bugmessage_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-danger has-text-weight-bold bottom10'>
                        {$lang['gl_bug_alert1']}
                    </div>
                    <div class='has-text-centered'>{$lang['gl_bug_alert2']} {$CURUSER['username']}!<br> " . sprintf($lang['gl_bugs'], $bugs[0]) . ($bugs[0] > 1 ? "{$lang['gl_bugss']}" : '') . '!</div>
                 </div>
            </div>
        </a>
    </li>';
    }
}
