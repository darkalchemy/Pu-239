<?php

declare(strict_types = 1);
global $user, $site_config;

/**
 * @param $val
 *
 * @return string
 */
function calctime($val)
{
    $days = intval($val / 86400);
    $val -= $days * 86400;
    $hours = intval($val / 3600);
    $val -= $hours * 3600;
    $mins = intval($val / 60);
    //$secs = $val - ($mins * 60);

    return "&#160;$days " . _('days') . ", $hours " . _('hrs') . ", $mins " . _('minutes') . '';
}

if ($user['onirc'] === 'yes') {
    $ircbonus = (!empty($user['irctotal']) ? number_format($user['irctotal'] / $site_config['irc']['autoclean_interval'], 1) : '0.0');
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Irc Bonus') . "</td><td>{$ircbonus}</td></tr>";
    $irctotal = (!empty($user['irctotal']) ? calctime($user['irctotal']) : htmlsafechars($user['username']) . _(' has never been on IRC!'));
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Irc Idle Time') . "</td><td>{$irctotal}</td></tr>";
}
