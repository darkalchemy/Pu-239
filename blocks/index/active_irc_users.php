<?php
global $site_config, $cache, $lang;

/**
 * @param $val
 *
 * @return string
 */
function calctime($val)
{
    global $lang;
    $days = intval($val / 86400);
    $val -= $days * 86400;
    $hours = intval($val / 3600);
    $val -= $hours * 3600;
    $mins = intval($val / 60);
    $secs = $val - ($mins * 60);

    return "<br>&#160;&#160;&#160;$days {$lang['gl_irc_days']}, $hours {$lang['gl_irc_hrs']}, $mins {$lang['gl_irc_min']}";
}

$keys['activeircusers'] = 'activeircusers';
$active_irc_users_cache = $cache->get($keys['activeircusers']);
if ($active_irc_users_cache === false || is_null($active_irc_users_cache)) {
    $dt = $_SERVER['REQUEST_TIME'] - 180;
    $activeircusers = '';
    $active_irc_users_cache = [];
    $res = sql_query('SELECT id, username, perms FROM users WHERE onirc = "yes" AND perms < ' . bt_options::PERMS_STEALTH . ' ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
    $actcount = mysqli_num_rows($res);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($activeircusers) {
            $activeircusers .= ", ";
        }
        $activeircusers .= format_username($arr['id']);
    }
    $active_irc_users_cache['activeircusers'] = $activeircusers;
    $active_irc_users_cache['actcount'] = $actcount;
    $cache->set($keys['activeircusers'], $active_irc_users_cache, $site_config['expires']['activeircusers']);
}
if (!$active_irc_users_cache['activeircusers']) {
    $active_irc_users_cache['activeircusers'] = $lang['index_irc_nousers'];
}
$HTMLOUT .= "
    <a id='irc-hash'></a>
    <fieldset id='irc' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_active_irc']} ({$active_irc_users_cache['actcount']})</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                {$active_irc_users_cache['activeircusers']}
            </div>
        </div>
    </fieldset>";
