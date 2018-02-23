<?php

global $CURUSER, $site_config, $lang, $fluent, $cache;

if ($site_config['uploadapp_alert'] && $CURUSER['class'] >= UC_STAFF) {
    $newapp = $cache->get('new_uploadapp_');
    if (false === $newapp || is_null($newapp)) {
        $res = $fluent->from('uploadapp')
            ->select(null)
            ->select('COUNT(id) AS count')
            ->where('status = ?', 'pending')
            ->fetch();

        $newapp = $res['count'];
        $cache->set('new_uploadapp_', $newapp, $site_config['expires']['alerts']);
    }
    if ($newapp > 0) {
        $htmlout .= "
    <li>
        <a href='{$site_config['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'>
            <span class='button tag is-info dt-tooltipper-small' data-tooltip-content='#uploadapp_tooltip'>
                {$lang['gl_uploadapp_new']}
            </span>
            <div class='tooltip_templates'>
                <span id='uploadapp_tooltip'>
                    <div class='size_4 has-text-centered has-text-danger has-text-weight-bold bottom10'>
                        {$lang['gl_hey']} {$CURUSER['username']}!<br> $newapp {$lang['gl_uploadapp_ua']}" . ($newapp > 1 ? 's' : '') . " {$lang['gl_uploadapp_dealt']} 
                    </div>
                </span>
            </div>
        </a>
    </li>";
    }
}
