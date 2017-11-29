<?php
global $site_config, $lang, $user;

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
    //$secs = $val - ($mins * 60);

    return "&#160;$days {$lang['userdetails_irc_days']}, $hours {$lang['userdetails_irc_hrs']}, $mins {$lang['userdetails_irc_min']}";
}

//==Irc
if ($user['onirc'] == 'yes') {
    $ircbonus = (!empty($user['irctotal']) ? number_format($user['irctotal'] / $site_config['autoclean_interval'], 1) : '0.0');
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_irc_bonus']}</td><td>{$ircbonus}</td></tr>";
    $irctotal = (!empty($user['irctotal']) ? calctime($user['irctotal']) : htmlsafechars($user['username']) . $lang['userdetails_irc_never']);
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_irc_idle']}</td><td>{$irctotal}</td></tr>";
}
