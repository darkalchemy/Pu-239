<?php
global $CURUSER, $site_config, $cache, $lang, $fpdo;

if ($site_config['uploadapp_alert'] && $CURUSER['class'] >= UC_STAFF) {
    $newapp = $cache->get('new_uploadapp_');
    if ($newapp === false || is_null($newapp)) {
        $res = $fpdo->from('uploadapp')
            ->select(null)
            ->select('COUNT(id) AS count')
            ->where('status = ?', 'pending')
            ->fetch();

        $newapp= $res['count'];
        $cache->set('new_uploadapp_', $newapp, $site_config['expires']['alerts']);
    }
    if ($newapp > 0) {
        $htmlout .= "
   <li>
   <a class='tooltip' href='staffpanel.php?tool=uploadapps&amp;action=app'><b class='button btn-warning is-small'>{$lang['gl_uploadapp_new']}</b>
   <span class='custom info alert alert-warning'><em>{$lang['gl_uploadapp_new']}</em>
   {$lang['gl_hey']} {$CURUSER['username']}!<br> $newapp {$lang['gl_uploadapp_ua']}" . ($newapp > 1 ? 's' : '') . " {$lang['gl_uploadapp_dealt']} 
   {$lang['gl_uploadapp_click']}</span></a></li>";
    }
}
