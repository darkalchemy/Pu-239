<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $CURUSER, $site_config;

$lang = array_merge(load_language('global'), load_language('chat'));
$nick = $CURUSER ? $CURUSER['username'] : ('Guest' . random_int(1000, 9999));
$irc_url = 'irc.p2p-network.net';
$irc_channel = '#pu-239';
$HTMLOUT = "
    <div class='padding20'>
    <p>{$lang['chat_channel']}<a href='irc://{$irc_url}'>{$irc_channel}</a> {$lang['chat_network']}</p>
    <div class='maintitle'>{$site_config['site_name']}</div>
    <div class='row1'>
    <applet code='IRCApplet.class' codebase='./javairc/' archive='irc.jar,pixx.jar' width='640' height='400'>
      <param name='CABINETS' value='irc.cab,securedirc.cab,pixx.cab'>
      <param name='name' value='{$nick}'>
      <param name='nick' value='{$nick}'>
      <param name='alternatenick' value='{$nick}???'>
      <param name='fullname' value='Java User'>
      <param name='host' value='{$irc_url}'>
      <param name='gui' value='pixx'>
      <param name='quitmessage' value='{$site_config['site_name']} forever!'>
      <param name='asl' value='true'>
      <param name='command1' value='/join {$irc_channel}'>
      <param name='style:bitmapsmileys' value='true'>
      <param name='style:floatingasl' value='true'>
      <param name='pixx:highlight' value='true'>
      <param name='pixx:highlightnick' value='true'>
      <param name='pixx:nickfield' value='true'>
      <param name='style:smiley1' value='~:) images/smilies/sleep.gif'>
    </applet>
    </div>
    </div>";

echo stdhead("{$lang['chat_chat']}") . wrapper(main_div($HTMLOUT)) . stdfoot();
