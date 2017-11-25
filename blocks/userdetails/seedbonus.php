<?php
global $site_config, $lang, $user_stats;

$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_bonus_points']}</td><td><a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>" . (int)$user_stats['seedbonus'] . '</a></td></tr>';
