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
require_once (INCL_DIR . 'html_functions.php');
dbconn();
loggedinorreturn();
$lang = array_merge(load_language('global') , load_language('rules'));
$stdhead = array(
    /** include the css **/
    'css' => array(
        'rules'
    )
);
$HTMLOUT = '';
$HTMLOUT.= '<script type="text/javascript">
    /*<![CDATA[*/
    $(document).ready(function()
    {
	  //slides the element with class "menu_body" when paragraph with class "menu_head" is clicked 
	  $("#firstpanel p.menu_head").click(function()
    {
		$(this).css({backgroundImage:"url(pic/down2.png)"}).next("div.menu_body").slideToggle(300).siblings("div.menu_body").slideUp("slow");
    //$(this).siblings().css({backgroundImage:"url(pic/left.png)"});
	  });
	  //slides the element with class "menu_body" when mouse is over the paragraph
	  $("#secondpanel p.menu_head").mouseover(function()
    {
	  $(this).css({backgroundImage:"url(pic/down2.png)"}).next("div.menu_body").slideDown(500).siblings("div.menu_body").slideUp("slow");
    //$(this).siblings().css({backgroundImage:"url(pic/left.png)"});
	  });
    });
    /*]]>*/
    </script>';
$HTMLOUT.= begin_main_frame();
$HTMLOUT.= '<div class="global_icon_r"><img src="images/global.design/info.png" alt="" title="Guidelines" class="global_image" width="25"/></div>
    <div class="global_head_r">Guidelines</div><br />
    <div class="global_text_r"><br />';
$HTMLOUT.= "
    <div id='firstpanel' class='menu_list'><!-- accordian starts here secondpanel as id is mouseover -->
	  <p class='menu_head'>
    {$lang['rules_general_header']}<font color='#004E98'>{$lang['rules_general_header_sub']}</font>
    </p>
    <div class='menu_body'>
    <ul>
    <li>{$lang['rules_general_body']}</li>
    <li>{$lang['rules_general_body1']}</li>
    <li><a name='warning'></a>{$lang['rules_general_body2']}</li>
    </ul></div>";
$HTMLOUT.= "
    <p class='menu_head'>
    {$lang['rules_downloading_header']}<font color='#004E98'>{$lang['rules_downloading_header_sub']}</font></p>
    <div class='menu_body'>
    <ul>
    <li>{$lang['rules_downloading_body']}</li>
    <li>{$lang['rules_downloading_body1']}</li>
    </ul></div>";
$HTMLOUT.= "
    <p class='menu_head'>
    {$lang['rules_forum_header']}<font color='#004E98'>{$lang['rules_forum_header_sub']}</font></p>
    <div class='menu_body'>
    <ul>
    <li>{$lang['rules_forum_body']}</li>
    <li>{$lang['rules_forum_body1']}</li>
    <li>{$lang['rules_forum_body2']}</li>
    <li>{$lang['rules_forum_body3']}</li>
    <li>{$lang['rules_forum_body4']}</li>
    <li>{$lang['rules_forum_body5']}</li>
    <li>{$lang['rules_forum_body6']}</li>
    <li>{$lang['rules_forum_body7']}</li>
    <li>{$lang['rules_forum_body8']}</li>
    <li>{$lang['rules_forum_body9']}</li>
    <li>{$lang['rules_forum_body10']}</li>
    <li>{$lang['rules_forum_body11']}</li>
    </ul></div>";
$HTMLOUT.= "
    <p class='menu_head'>
    {$lang['rules_avatar_header']}<font color='#004E98'>{$lang['rules_avatar_header_sub']}</font></p>
    <div class='menu_body'>
    <ul>
    <li>{$lang['rules_avatar_body']}</li>
    <li>{$lang['rules_avatar_body1']}</li>
    <li>{$lang['rules_avatar_body2']}</li>
    </ul></div>";
if (isset($CURUSER) AND $CURUSER['class'] >= UC_UPLOADER) {
    $HTMLOUT.= "
      <p class='menu_head'>
      {$lang['rules_uploading_header']}<font color='#004E98'>{$lang['rules_uploading_header_sub']}</font></p>
      <div class='menu_body'>
      <ul>
      <li>{$lang['rules_uploading_body']}</li>
      <li>{$lang['rules_uploading_body1']}</li>
      <li>{$lang['rules_uploading_body2']}</li>
      <li>{$lang['rules_uploading_body3']}</li>
      <li>{$lang['rules_uploading_body4']}</li>
      <li>{$lang['rules_uploading_body5']}</li>
      <li>{$lang['rules_uploading_body6']}</li>
      <li>{$lang['rules_uploading_body7']}</li>
      <li>{$lang['rules_uploading_body8']}</li>
      </ul>
      <br />
      <br />
      {$lang['rules_uploading_body9']}</div>";
}
if (isset($CURUSER) AND $CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT.= "
     <p class='menu_head'>
     {$lang['rules_moderating_header']}<font color='#004E98'>{$lang['rules_moderating_header_sub']}</font></p>
     <div class='menu_body'>
     <table border='0' cellspacing='3' cellpadding='0'>
      <tr>
        <td class='embedded' bgcolor='#ffffff' valign='top' width='80'>&nbsp; <b><font color='f9a200'>{$lang['rules_moderating_pu']}</font></b></td>
        <td class='embedded' width='5'>&nbsp;</td>
        <td class='embedded'>{$lang['rules_moderating_body']}</td>
      </tr>
      <tr>
        <td class='embedded' bgcolor='#ffffff' valign='top'>&nbsp; <b><img src='pic/star.gif' alt='Donor' title='Donor' /></b></td>
        <td class='embedded' width='5'>&nbsp;</td>
        <td class='embedded'>{$lang['rules_moderating_body1']}</td>
      </tr>
      <tr>
        <td class='embedded' bgcolor='#ffffff' valign='top'>&nbsp; <b><font color='009F00'>{$lang['rules_moderating_vip']}</font></b></td>
        <td class='embedded' width='5'>&nbsp;</td>
        <td class='embedded'>{$lang['rules_moderating_body2']}</td>
      </tr>
      <tr>
        <td class='embedded' bgcolor='#ffffff' valign='top'>&nbsp; <b>{$lang['rules_moderating_other']}</b></td>
        <td class='embedded' width='5'>&nbsp;</td>
        <td class='embedded'>{$lang['rules_moderating_body3']}</td>
      </tr>
      <tr>
        <td class='embedded' bgcolor='#ffffff' valign='top'>&nbsp; <b><font color='0000FF'>{$lang['rules_moderating_uploader']}</font></b></td>
        <td class='embedded' width='5'>&nbsp;</td>
        <td class='embedded'>{$lang['rules_moderating_body4']}</td>
      </tr>
      <tr>
        <td class='embedded' bgcolor='#ffffff' valign='top'>&nbsp; <b><font color='#FE2E2E'>{$lang['rules_moderating_mod']}</font></b></td>
        <td class='embedded' width='5'>&nbsp;</td>
        <td class='embedded'>{$lang['rules_moderating_body5']}</td>
      </tr>
      </table></div>";
    $HTMLOUT.= "
      <p class='menu_head'>
      {$lang['rules_mod_rules_header']}<font color='#004E98'>{$lang['rules_mod_rules_header_sub']}</font></p>
      <div class='menu_body'>
      <ul>
      <li>{$lang['rules_mod_rules_body']}</li>
      <li>{$lang['rules_mod_rules_body1']}</li>
      <li>{$lang['rules_mod_rules_body2']}</li>
      <li>{$lang['rules_mod_rules_body3']}</li>
      <li>{$lang['rules_mod_rules_body4']}</li>
      <li>{$lang['rules_mod_rules_body5']}</li>
      <li>{$lang['rules_mod_rules_body6']}</li>
      <li>{$lang['rules_mod_rules_body7']}</li>
      <li>{$lang['rules_mod_rules_body8']}</li>
      <li>{$lang['rules_mod_rules_body9']}</li>
      <li>{$lang['rules_mod_rules_body10']}</li>
      <li>{$lang['rules_mod_rules_body11']}</li>
      </ul></div>";
    $HTMLOUT.= "
      <p class='menu_head'>
      {$lang['rules_mod_options_header']}<font color='#004E98'>{$lang['rules_mod_options_header_sub']}</font></p>
      <div class='menu_body'>
      <ul>
      <li>{$lang['rules_mod_options_body']}</li>
      <li>{$lang['rules_mod_options_body1']}</li>
      <li>{$lang['rules_mod_options_body2']}</li>
      <li>{$lang['rules_mod_options_body3']}</li>
      <li>{$lang['rules_mod_options_body4']}</li>
      <li>{$lang['rules_mod_options_body5']}</li>
      <li>{$lang['rules_mod_options_body6']}</li>
      <li>{$lang['rules_mod_options_body7']}</li>
      <li>{$lang['rules_mod_options_body8']}</li>
      </ul></div></div>";
}
$HTMLOUT.= '</div>';
$HTMLOUT.= end_main_frame();
echo stdhead("Rules", true, $stdhead) . $HTMLOUT . stdfoot();
?>
