<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'user_functions.php');
dbconn();
loggedinorreturn();
$lang = array_merge(load_language('global') , load_language('chat'));
$nick = ($CURUSER ? $CURUSER['username'] : ('Guest' . rand(1000, 9999)));
$irc_url = 'irc.p2p-network.net';
$irc_channel = '#09source weloveweed';
$HTMLOUT = '';
$HTMLOUT.= "<p>{$lang['chat_channel']}<a href='irc://{$irc_url}'>{$irc_channel}</a> {$lang['chat_network']}</p>
    <div class='borderwrap' align='center'>
    <div class='maintitle'>{$INSTALLER09['site_name']}</div>
    <div class='row1' align='center'>
    <applet code='IRCApplet.class' codebase='./javairc/' archive='irc.jar,pixx.jar' width='640' height='400'>
      <param name='CABINETS' value='irc.cab,securedirc.cab,pixx.cab' />
      <param name='name' value='{$nick}' />
      <param name='nick' value='{$nick}' />
      <param name='alternatenick' value='{$nick}???' />
      <param name='fullname' value='Java User' />
      <param name='host' value='{$irc_url}' />
      <param name='gui' value='pixx' />
      <param name='quitmessage' value='{$INSTALLER09['site_name']} forever!' />
      <param name='asl' value='true' />
      <param name='command1' value='/join {$irc_channel}' />
      <param name='style:bitmapsmileys' value='true' />
      <param name='style:floatingasl' value='true' />
      <param name='pixx:highlight' value='true' />
      <param name='pixx:highlightnick' value='true' />
      <param name='pixx:nickfield' value='true' />
      <param name='style:smiley1' value='~:) pic/smilies/sleep.gif' />
    </applet>
    </div>
    </div>";
///////////////////// HTML OUTPUT ////////////////////////////
echo stdhead("{$lang['chat_chat']}") . $HTMLOUT . stdfoot();
?>
