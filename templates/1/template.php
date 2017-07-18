<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL							    |
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
//==Template system by Terranova
function stdhead($title = "", $msgalert = true, $stdhead = false)
{
    global $CURUSER, $INSTALLER09, $lang, $free, $_NO_COMPRESS, $query_stat, $querytime, $mc1, $BLOCKS, $CURBLOCK, $mood;
    if (!$INSTALLER09['site_online']) die("Site is down for maintenance, please check back again later... thanks<br />");
    if ($title == "") $title = $INSTALLER09['site_name'] . (isset($_GET['tbv']) ? " (" . TBVERSION . ")" : '');
    else $title = $INSTALLER09['site_name'] . (isset($_GET['tbv']) ? " (" . TBVERSION . ")" : '') . " :: " . htmlsafechars($title);
    if ($CURUSER) {
        $INSTALLER09['stylesheet'] = isset($CURUSER['stylesheet']) ? "{$CURUSER['stylesheet']}.css" : $INSTALLER09['stylesheet'];
        $INSTALLER09['categorie_icon'] = isset($CURUSER['categorie_icon']) ? "{$CURUSER['categorie_icon']}" : $INSTALLER09['categorie_icon'];
        $INSTALLER09['language'] = isset($CURUSER['language']) ? "{$CURUSER['language']}" : $INSTALLER09['language'];
    }
    /** ZZZZZZZZZZZZZZZZZZZZZZZZZZip it! */
    if (!isset($_NO_COMPRESS)) if (!ob_start('ob_gzhandler')) ob_start();
    //== Include js files needed only for the page being used by pdq
    $js_incl = '';
    $js_incl.= '<!-- javascript goes here or in footer -->';
    if (!empty($stdhead['js'])) {
        foreach ($stdhead['js'] as $JS) $js_incl.= "<script type='text/javascript' src='{$INSTALLER09['baseurl']}/scripts/" . $JS . ".js'></script>";
    }
    //== Include css files needed only for the page being used by pdq
    $css_incl = '';
    $css_incl.= '<!-- css goes here -->';
    if (!empty($stdhead['css'])) {
        foreach ($stdhead['css'] as $CSS) $css_incl.= "<link type='text/css' rel='stylesheet' href='{$INSTALLER09['baseurl']}/templates/{$CURUSER['stylesheet']}/css/" . $CSS . ".css' />";
    }
    if (isset($INSTALLER09['xhtml_strict'])) { //== Use strict mime type/doctype
        //== Only if browser/user agent supports xhtml strict mode
        if (isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') && ($INSTALLER09['xhtml_strict'] === 1 || ($INSTALLER09['xhtml_strict'] == $CURUSER['username'] && $CURUSER['username'] != ''))) {
            header('Content-type:application/xhtml+xml; charset=' . charset());
            $doctype = '<?xml version="1.0" encoding="' . charset() . '"?>' . '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
        }
    }
    if (!isset($doctype)) {
        header('Content-type:text/html; charset=' . charset());
        //$doctype = '<!DOCTYPE html>' . '<html xmlns="http://www.w3.org/1999/xhtml">';
        $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ' . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . '<html xmlns="http://www.w3.org/1999/xhtml">';
    }
    $body_class = isset($_COOKIE['theme']) ? htmlsafechars($_COOKIE['theme']) : 'background-15 h-style-1 text-1 skin-1';
    $htmlout = $doctype . "<head>
        <meta http-equiv='Content-Language' content='en-us' />
        <!-- ####################################################### -->
        <!-- #   This website is powered by U-232 V4	           # -->
        <!-- #   Download and support at: https://u-232.com        # -->
        <!-- #   Template Modded by U-232 Dev Team                 # -->
        <!-- ####################################################### -->
        <title>{$title}</title>
        <link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='./rss.php?torrent_pass={$CURUSER['torrent_pass']}' />
     	<!-- favicon 
      	=================================================== -->
        <link rel='shortcut icon' href='favicon.ico' />
      	<!-- css 
      	=================================================== -->
        <link rel='stylesheet' href='./templates/1/1.css' type='text/css' />
	<link rel='stylesheet' href='./templates/1/bootstrap.css' type='text/css' />
	<link rel='stylesheet' href='./templates/1/bootstrap-responsive.css' type='text/css' />
        <link rel='stylesheet' href='./templates/1/themeChanger/css/colorpicker.css' type='text/css' />
        <link rel='stylesheet' href='./templates/1/themeChanger/css/themeChanger.css' type='text/css' />
        <style type='text/css'>#mlike{cursor:pointer;}</style>
      	<!-- global javascript
      	================================================== -->
        <script type='text/javascript' src='./scripts/jquery-1.5.js'></script>
        <script type='text/javascript' src='./scripts/jquery.status.js'></script>
        <script type='text/javascript' src='./scripts/jquery.cookie.js'></script>
	<script type='text/javascript' src='./scripts/help.js'></script>
	<!-- template javascript
	================================================== -->
        <script type='text/javascript' src='./templates/1/themeChanger/js/colorpicker.js'></script>
        <script type='text/javascript' src='./templates/1/themeChanger/js/themeChanger.js'></script>
        <script type='text/javascript' src='./templates/1/js/jquery.smoothmenu.js'></script>
        <script type='text/javascript' src='./templates/1/js/core.js'></script>
        <script type='text/javascript'>
        /*<![CDATA[*/
		// Like Dislike function
		//================================================== -->
		$(function() {							// the like js
		$('span[id*=mlike]').like232({
		times : 5,            	// times checked 
		disabled : 5,         	// disabled from liking for how many seconds
		time  : 5,             	// period within check is performed
		url : '/ajax.like.php'
		});
		});
	// template changer function
	//================================================== -->
        function themes() {
          window.open('take_theme.php','My themes','height=150,width=200,resizable=no,scrollbars=no,toolbar=no,menubar=no');
        }
	// language changer function
	//================================================== -->
        function language_select() {
          window.open('take_lang.php','My language','height=150,width=200,resizable=no,scrollbars=no,toolbar=no,menubar=no');
        }
	// radio function
	//================================================== -->
        function radio() {
          window.open('radio_popup.php','My Radio','height=700,width=800,resizable=no,scrollbars=no,toolbar=no,menubar=no');
        }
         /*]]>*/
        </script>
        <script type='text/javascript' src='./ajax/helpers.js'></script>
        {$js_incl}{$css_incl}
        <!--[if lt IE 9]>
        <script type='text/javascript' src='./templates/1/js/modernizr.custom.js'></script>
	<script type='text/javascript' src='http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE8.js'></script>
	<script type='text/javascript' src='./templates/1/js/ie.js'></script>
        <![endif]-->
        </head>
        <body class='{$body_class}'>
        <!-- Main Outer Container
        =================================================== -->
	<div class='container'>
        <!--<header class='clearfix'>-->";
    if ($CURUSER) {
        $active_users_cache = $last24_cache = 0;
        $keys['last24'] = 'last24';
        $last24_cache = $mc1->get_value($keys['last24']);
        $keys['activeusers'] = 'activeusers';
        $active_users_cache = $mc1->get_value($keys['activeusers']);
        $htmlout.= "
		<!-- Main Navigation
		=================================================== -->
		<div id='navigation' class='navigation'>
     		<ul>
		<li><a href='#'>{$lang['gl_torrent']}</a>
		<ul class='sub-menu'>
	        <li><a href='" . $INSTALLER09['baseurl'] . "/browse.php'>{$lang['gl_torrents']}</a></li>
		<li><a href='" . $INSTALLER09['baseurl'] . "/requests.php'>{$lang['gl_requests']}</a></li>
	        <li><a href='" . $INSTALLER09['baseurl'] . "/offers.php'>{$lang['gl_offers']}</a></li>
	        <li><a href='" . $INSTALLER09['baseurl'] . "/needseed.php?needed=seeders'>{$lang['gl_nseeds']}</a></li>
		" . (isset($CURUSER) && $CURUSER['class'] <= UC_VIP ? "<li><a href='" . $INSTALLER09['baseurl'] . "/uploadapp.php'>{$lang['gl_uapp']}</a> </li>" : "<li><a href='" . $INSTALLER09['baseurl'] . "/upload.php'>{$lang['gl_upload']}</a></li>") . "
                <li><a href='" . $INSTALLER09['baseurl'] . "/bookmarks.php'>{$lang['gl_bookmarks']}</a></li>
		</ul><!--/ .sub-menu-->
		</li>
		<li><a href='#'>{$lang['gl_general']}</a>
		<ul class='sub-menu'>
                        <li><a href='" . $INSTALLER09['baseurl'] . "/announcement.php'>{$lang['gl_announcements']}</a></li>
                        <li><a href='" . $INSTALLER09['baseurl'] . "/topten.php'>{$lang['gl_stats']}</a></li>
                        <li><a href='" . $INSTALLER09['baseurl'] . "/faq.php'>{$lang['gl_faq']}</a></li>
        		<li><a href='" . $INSTALLER09['baseurl'] . "/chat.php'>{$lang['gl_irc']}</a></li>
                        <li><a href='" . $INSTALLER09['baseurl'] . "/staff.php'>{$lang['gl_staff']}</a></li>
                        <li><a href='" . $INSTALLER09['baseurl'] . "/wiki.php'>{$lang['gl_wiki']}</a></li>
			<li><a href='#' onclick='radio();'>{$lang['gl_radio']}</a></li>
			<li><a href='./rsstfreak.php'>{$lang['gl_tfreak']}</a></li>
			</ul><!--/ .sub-menu-->
		</li>
		<li><a href='#'>{$lang['gl_games']}</a>
		<ul class='sub-menu'>
                    " . (isset($CURUSER) && $CURUSER['class'] >= UC_POWER_USER ? "<li><a href='" . $INSTALLER09['baseurl'] . "/casino.php'>{$lang['gl_casino']}</a></li>" : "") . "
                    " . (isset($CURUSER) && $CURUSER['class'] >= UC_POWER_USER ? "<li><a href='" . $INSTALLER09['baseurl'] . "/blackjack.php'>{$lang['gl_bjack']}</a></li>" : "") . "
                    </ul><!--/ .sub-menu-->
		</li>
		    <li><a href='" . $INSTALLER09['baseurl'] . "/donate.php'>{$lang['gl_donate']}</a></li>
		    <li><a href='#'>{$lang['gl_forums']}</a>
		<ul class='sub-menu'>
                    <li><a href='" . $INSTALLER09['baseurl'] . "/forums.php'>{$lang['gl_tforums']}</a></li>
                    <li><a href='http://forum.u-232.com/index.php'>SMF Support</a></li>
		</ul>
		</li>
                <li> " . (isset($CURUSER) && $CURUSER['class'] < UC_STAFF ? "<a class='brand' href='" . $INSTALLER09['baseurl'] . "/bugs.php?action=add'>{$lang['gl_breport']}</a>" : "<a class='brand' href='" . $INSTALLER09['baseurl'] . "/bugs.php?action=bugs'>{$lang['gl_brespond']}</a>") . "</li>
                <li>" . (isset($CURUSER) && $CURUSER['class'] < UC_STAFF ? "<a class='brand' href='" . $INSTALLER09['baseurl'] . "/contactstaff.php'>{$lang['gl_cstaff']}</a>" : "<a class='brand' href='" . $INSTALLER09['baseurl'] . "/staffbox.php'>{$lang['gl_smessages']}</a>") . "</li>
		</ul>
		<small>
		<strong>";
                if (!empty($last24_cache)) 
                if ($last24_cache['totalonline24'] != 1) $last24_cache['ss24'] = $lang['gl_members'];
	        else $last24_cache['ss24'] = $lang['gl_member'];
                $htmlout .="
                &nbsp;&nbsp;" . $last24_cache['totalonline24'] . $last24_cache['ss24'] . " {$lang['gl_last24']}<br />";
		if (!empty($active_users_cache)) 
                $htmlout.= "&nbsp;&nbsp;{$lang['gl_ausers']}&nbsp;[" . $active_users_cache['au'] . "]";
		$htmlout.= "</strong></small></div><div class='clear'></div>";
                }
                $htmlout.= "
		<!-- END Main Navigation
		=================================================== -->
		<!-- Logo
		=================================================== -->
		<!-- U-232 Source - Print Logo (CSS Controled) -->
			<div class='cl'>&nbsp;</div>
			<!-- Logo -->
			<div id='logo'>
			<h1>" . TBVERSION . "<span>&nbsp;&nbsp;Code</span></h1>
			<p class='description'>&nbsp;&nbsp;&nbsp;<i>FTW</i></p>
			</div>
		<!-- End Logo
		=================================================== -->";
    if ($CURUSER) {
        $salty = md5("Th15T3xtis5add3dto66uddy6he@water..." . $CURUSER['username'] . "");
        $htmlout.= "
		<!-- Platform Navigation
		=================================================== -->
		<div id='platform-menu' class='platform-menu'>
			<a href='" . $INSTALLER09['baseurl'] . "/index.php' class='home'>{$lang['gl_home']}</a>
				<ul>
					<li><a href='" . $INSTALLER09['baseurl'] . "/pm_system.php'>{$lang['gl_pms']}</a></li>
					<li><a href='" . $INSTALLER09['baseurl'] . "/usercp.php?action=default'>{$lang['gl_usercp']}</a></li>
					" . (isset($CURUSER) && $CURUSER['class'] >= UC_STAFF ? "<li><a href='" . $INSTALLER09['baseurl'] . "/staffpanel.php'>{$lang['gl_admin']}</a></li>" : "") . "
					<li><a href='#' onclick='themes();'>{$lang['gl_theme']}</a></li>
					<li><a href='#' onclick='language_select();'>{$lang['gl_language_select']}</a></li>
					<!--<li><a href='javascript:void(0)' onclick='status_showbox()'>{$lang['gl_status']}</a></li>-->
					<li><a href='" . $INSTALLER09['baseurl'] . "/friends.php'>{$lang['gl_friends']}</a></li>
					<li><a href='" . $INSTALLER09['baseurl'] . "/logout.php?hash_please={$salty}'>{$lang['gl_logout']}</a></li>
				</ul>
			<div class='container-fluid'>
			<!--/ statusbar start-->
			<div class='statusbar-container'>";
		        if ($CURUSER) { $htmlout.= StatusBar() . "
			</div>
			<!--/ statusbar end-->
			<!-- U-232 Source - Print Global Messages Start -->
			</div>
			<div id='base_globelmessage'>
			<div id='gm_taps'>
			<ul class='gm_taps'>
		        <li><b>{$lang['gl_alerts']}</b></li>";

    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_REPORTS && $BLOCKS['global_staff_report_on']) {
        require_once (BLOCK_DIR . 'global/report.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_UPLOADAPP && $BLOCKS['global_staff_uploadapp_on']) {
        require_once (BLOCK_DIR . 'global/uploadapp.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR && $BLOCKS['global_happyhour_on'] && XBT_TRACKER == false) { 
        require_once (BLOCK_DIR . 'global/happyhour.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_STAFF_MESSAGE && $BLOCKS['global_staff_warn_on']) {
        require_once (BLOCK_DIR . 'global/staffmessages.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_NEWPM && $BLOCKS['global_message_on']) {
        require_once (BLOCK_DIR . 'global/message.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_DEMOTION && $BLOCKS['global_demotion_on']) {
        require_once (BLOCK_DIR . 'global/demotion.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH && $BLOCKS['global_freeleech_on'] && XBT_TRACKER == false) {
        require_once (BLOCK_DIR . 'global/freeleech.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR && $BLOCKS['global_crazyhour_on'] && XBT_TRACKER == false) {
        require_once (BLOCK_DIR . 'global/crazyhour.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_BUG_MESSAGE && $BLOCKS['global_bug_message_on']) {
        require_once (BLOCK_DIR . 'global/bugmessages.php');
    }
    if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION && $BLOCKS['global_freeleech_contribution_on']) { 
        require_once (BLOCK_DIR . 'global/freeleech_contribution.php');
     }
     $htmlout.= "</ul></div></div><!-- U-232 Source - Print Global Messages End -->";
     }
/*
 $INSTALLER09['expires']['staff_check'] = 3600; //== test value
 if ($CURUSER['class'] >= UC_STAFF)
 {
 if (($mysql_data = $mc1->get_value('is_staff_' . $CURUSER['class'])) === false) {
 $res = sql_query('SELECT * FROM staffpanel WHERE av_class <= ' . sqlesc($CURUSER['class']) . ' ORDER BY page_name ASC') or sqlerr(__FILE__, __LINE__);
  while ($arr = mysqli_fetch_assoc($res)) $mysql_data[] = $arr;
 $mc1->cache_value('is_staff_' . $CURUSER['class'], $mysql_data, $INSTALLER09['expires']['staff_check']);
  }
  if ($mysql_data) { 
   $htmlout .= '<div class="Staff_tools">Staff Tools:
     <div class="btn-group">
     <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
     User
     <span class="caret"></span>
     </a>
  <ul class="dropdown-menu">';
     
  foreach ($mysql_data as $key => $value){
  if ($value['av_class'] <= $CURUSER['class'] && $value['type'] == 'user') {
  $htmlout .= '<li><a href="'.htmlsafechars($value["file_name"]).'">'.htmlsafechars($value["page_name"]).'</a></li>';
  }
  }
  $htmlout .= '</ul></div>';

  $htmlout .= '
  <div class="btn-group">
  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
    Settings
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu">';
           
  foreach ($mysql_data as $key => $value){
  if ($value['av_class'] <= $CURUSER['class'] && $value['type'] == 'settings') {
  $htmlout .= '<li><a href="'.htmlsafechars($value["file_name"]).'">'.htmlsafechars($value["page_name"]).'</a></li>';
  }
  }
  $htmlout .= '    </ul></div>';

  $htmlout .= '
  <div class="btn-group">
  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
    Stats
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu">';
           
  foreach ($mysql_data as $key => $value){
  if ((int)$value['av_class'] <= $CURUSER['class'] && htmlsafechars($value['type']) == 'stats') {
  $htmlout .= '<li><a href="'.htmlsafechars($value["file_name"]).'">'.htmlsafechars($value["page_name"]).'</a></li>';
  }
  }
  $htmlout .= '</ul></div>';

  $htmlout .= '
  <div class="btn-group">
  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
    Other
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu">';
           
  foreach ($mysql_data as $key => $value){
  if ((int)$value['av_class'] <= $CURUSER['class'] && htmlsafechars($value['type']) == 'other') {
  $htmlout .= '<li><a href="'.htmlsafechars($value["file_name"]).'">'.htmlsafechars($value["page_name"]).'</a></li>';
  }
  }
  $htmlout .= '    </ul></div></div>';
  }
  }
*/
    $htmlout.= "
    </div>
    <div class='clearfix'></div>
    <!-- End Platform Navigation and Global Messages 
    ======================================================= -->";
    }
    $htmlout.= "<br />
    <div id='base_content'>
    <!--<table class='mainouter' cellspacing='0' cellpadding='10'>
    <tr>
    <td align='center' class='outer' style='padding-bottom: 10px'>-->";
    return $htmlout;
} // stdhead
function stdfoot($stdfoot = false)
{
    global $CURUSER, $INSTALLER09, $start, $query_stat, $mc1, $querytime, $lang;
    $debug = (SQL_DEBUG && in_array($CURUSER['id'], $INSTALLER09['allowed_staff']['id']) ? 1 : 0);
    $cachetime = ($mc1->Time / 1000);
    $seconds = microtime(true) - $start;
    $r_seconds = round($seconds, 5);
    //$phptime = $seconds - $cachetime;
    $phptime = $seconds - $querytime - $cachetime;
    $queries = count($query_stat); // sql query count by pdq
    $percentphp = number_format(($phptime / $seconds) * 100, 2);
    //$percentsql  = number_format(($querytime / $seconds) * 100, 2);
    $percentmc = number_format(($cachetime / $seconds) * 100, 2);
    if (($MemStats = $mc1->get_value('mc_hits')) === false) {
        $MemStats = $mc1->getStats();
        $MemStats['Hits'] = (($MemStats['get_hits'] / $MemStats['cmd_get'] < 0.7) ? '' : number_format(($MemStats['get_hits'] / $MemStats['cmd_get']) * 100, 3));
        $mc1->cache_value('mc_hits', $MemStats, 10);
    }
    // load averages - pdq
    if ($debug) {
        if (($uptime = $mc1->get_value('uptime')) === false) {
            $uptime = `uptime`;
            $mc1->cache_value('uptime', $uptime, 25);
        }
        preg_match('/load average: (.*)$/i', $uptime, $load);
    }
    $header = '';
    $header = '<b>' . $lang['gl_stdfoot_querys_mstat'] . '</b> ' . mksize(memory_get_peak_usage()) . ' ' . $lang['gl_stdfoot_querys_mstat1'] . ' ' . round($phptime, 2) . 's | ' . round($percentmc, 2) . '' . $lang['gl_stdfoot_querys_mstat2'] . '' . number_format($cachetime, 5) . 's ' . $lang['gl_stdfoot_querys_mstat3'] . '' . $MemStats['Hits'] . '' . $lang['gl_stdfoot_querys_mstat4'] . '' . (100 - $MemStats['Hits']) . '' . $lang['gl_stdfoot_querys_mstat5'] . '' . number_format($MemStats['curr_items']);
    $htmlfoot = '';
    //== query stats
    //== include js files needed only for the page being used by pdq
    $htmlfoot.= '<!-- javascript goes here -->';
    if (!empty($stdfoot['js'])) {
        foreach ($stdfoot['js'] as $JS) $htmlfoot.= '<script type="text/javascript" src="' . $INSTALLER09['baseurl'] . '/scripts/' . $JS . '.js"></script>';
    }
    $querytime = 0;
    if ($CURUSER && $query_stat && $debug) {
        $htmlfoot.= "
		<div class='row-fluid'>
			<fieldset><legend>{$lang['gl_stdfoot_querys']}</legend>
				<div class='box-content'>
					<table class='table  table-bordered'>
						<thead>
							<tr>
								<th align='center'>{$lang['gl_stdfoot_id']}</th>
								<th align='center'>{$lang['gl_stdfoot_qt']}</th>
								<th align='left'>{$lang['gl_stdfoot_qs']}</th>
							</tr>
						</thead>";
        foreach ($query_stat as $key => $value) {
            $querytime+= $value['seconds']; // query execution time
            $htmlfoot.= "
						<tbody>
							<tr>
								<td>" . ($key + 1) . "</td>
								<td align='center'><b>" . ($value['seconds'] > 0.01 ? "<font color='red' title='{$lang['gl_stdfoot_ysoq']}'>" . $value['seconds'] . "</font>" : "<font color='green' title='{$lang['gl_stdfoot_qg']}'>" . $value['seconds'] . "</font>") . "</b></td>
								<td align='left'>" . htmlsafechars($value['query']) . "<br /></td>
							</tr>
						</tbody>";
        }
        $htmlfoot.= '
					</table>
				</div>
			</fieldset>
		</div>';
    }
    $htmlfoot.= "    	<!-- external javascript 
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->

		<!-- accordion library (optional, not used in demo)
		<script type='text/javascript' src='templates/framework/js/bootstrap-collapse.js'></script> -->
	</div>
<!--</td></tr></table>-->";
    if ($CURUSER) {
        /** just in case **/
        $htmlfoot.= "
		<div class='nav-collapse collapse'>
			<div class='container' >
				<div class='pull-left'>
				" . $INSTALLER09['site_name'] . " {$lang['gl_stdfoot_querys_page']}" . $r_seconds . " {$lang['gl_stdfoot_querys_seconds']}<br />" . "
				{$lang['gl_stdfoot_querys_server']}" . $queries . " {$lang['gl_stdfoot_querys_time']} " . ($queries != 1 ? "{$lang['gl_stdfoot_querys_times']}" : "") . "
				" . ($debug ? "<br /><b>" . $header . "</b><br /><b>{$lang['gl_stdfoot_uptime']}</b> " . $uptime . "" : " ") . "
				</div>
				<div class='pull-right' align='right'>
				{$lang['gl_stdfoot_powered']}" . TBVERSION . "<br />
				{$lang['gl_stdfoot_using']}<b>{$lang['gl_stdfoot_using1']}</b><br />
				{$lang['gl_stdfoot_support']}<b><a href='https://forum.u-232.com/index.php'>{$lang['gl_stdfoot_here']}</a></b><br />
				" . ($debug ? "| <a title='{$lang['gl_stdfoot_sview']}' rel='external' href='/staffpanel.php?tool=system_view'>{$lang['gl_stdfoot_sview']}</a> | " . "<a rel='external' title='OPCache' href='/staffpanel.php?tool=op'>{$lang['gl_stdfoot_opc']}</a> | " . "<a rel='external' title='Memcache' href='/staffpanel.php?tool=memcache'>{$lang['gl_stdfoot_memcache']}</a>" : "") . "";
			$htmlfoot.= "				
				</div>
			</div>
		</div>";
    }
    $htmlfoot.= "
	</div>
	<!--  End main outer container
	======================================================= -->
	 <div id='control_panel'>
	 <a href='#' id='control_label'></a>
	 </div><!-- #control_panel -->
    <!-- Ends Footer -->
    <script type='text/javascript' src='templates/1/js/general.js'></script>
<script type='text/javascript' src='scripts/bootstrap.js'></script>

    </body></html>\n";
    return $htmlfoot;
}
function stdmsg($heading, $text)
{
    $htmlout = "<table class='main' width='750' border='0' cellpadding='0' cellspacing='0'>
		<tr><td class='embedded'>\n";
    if ($heading) $htmlout.= "<h2>$heading</h2>\n";
    $htmlout.= "<table width='100%' border='1' cellspacing='0' cellpadding='10'>
		<tr><td class='text'>\n";
    $htmlout.= "{$text}</td></tr></table>
    </td></tr></table>\n";
    return $htmlout;
}
function hey()
{
    global $CURUSER, $lang;
    $now = date("H", TIME_NOW);
    switch ($now) {
    case ($now >= 7 && $now < 11):
        return "{$lang['gl_stdhey']}";
    case ($now >= 11 && $now < 13):
        return "{$lang['gl_stdhey1']}";
    case ($now >= 13 && $now < 17):
        return "{$lang['gl_stdhey2']}";
    case ($now >= 17 && $now < 19):
        return "{$lang['gl_stdhey3']}";
    case ($now >= 19 && $now < 21):
        return "{$lang['gl_stdhey4']}";
    case ($now >= 23 && $now < 0):
        return "{$lang['gl_stdhey5']}";
    case ($now >= 0 && $now < 7):
        return "{$lang['gl_stdhey6']}";
    default:
        return "{$lang['gl_stdhey7']}";
    }
}
function StatusBar()
{
    global $CURUSER, $INSTALLER09, $lang, $rep_is_on, $mc1, $msgalert;
    if (!$CURUSER) return "";
    $upped = mksize($CURUSER['uploaded']);
    $downed = mksize($CURUSER['downloaded']);
    //==Memcache unread pms
    $PMCount = 0;
    if (($unread1 = $mc1->get_value('inbox_new_sb_' . $CURUSER['id'])) === false) {
        $res1 = sql_query("SELECT COUNT(id) FROM messages WHERE receiver=" . sqlesc($CURUSER['id']) . " AND unread = 'yes' AND location = '1'") or sqlerr(__LINE__, __FILE__);
        list($PMCount) = mysqli_fetch_row($res1);
        $PMCount = (int)$PMCount;
        $unread1 = $mc1->cache_value('inbox_new_sb_' . $CURUSER['id'], $PMCount, $INSTALLER09['expires']['unread']);
    }
    $inbox = ($unread1 == 1 ? "$unread1&nbsp;{$lang['gl_msg_singular']}" : "$unread1&nbsp;{$lang['gl_msg_plural']}");
    //==Memcache peers
    if (XBT_TRACKER == true) {
    if (($MyPeersXbtCache = $mc1->get_value('MyPeers_XBT_'.$CURUSER['id'])) === false) {
        $seed['yes'] = $seed['no'] = 0;
        $seed['conn'] = 3;
        $r = sql_query("SELECT COUNT(uid) AS `count`, `left`, `active`, `connectable` FROM `xbt_files_users` WHERE uid= " . sqlesc($CURUSER['id'])." GROUP BY `left`") or sqlerr(__LINE__, __FILE__);
        while ($a = mysqli_fetch_assoc($r)) {
            $key = $a['left'] == 0 ? 'yes' : 'no';
            $seed[$key] = number_format(0 + $a['count']);
            $seed['conn'] = $a['connectable'] == 0 ? 1 : 2;
        }
        $mc1->cache_value('MyPeers_XBT_'.$CURUSER['id'], $seed, $INSTALLER09['expires']['MyPeers_xbt_']);
        unset($r, $a);
    } else {
        $seed = $MyPeersXbtCache;
    }
     // for display connectable  1 / 2 / 3
    if (!empty($seed['conn'])) {
        switch ($seed['conn']) {
        case 1:
            $connectable = "<img src='{$INSTALLER09['pic_base_url']}notcon.png' alt='{$lang['gl_not_connectable']}' title='{$lang['gl_not_connectable']}' />";
            break;

        case 2:
            $connectable = "<img src='{$INSTALLER09['pic_base_url']}yescon.png' alt='{$lang['gl_connectable']}' title='{$lang['gl_connectable']}' />";
            break;

        default:
            $connectable = "{$lang['gl_na_connectable']}";
        }
    } else $connectable = $lang['gl_na_connectable'];
} else {
    if (($MyPeersCache = $mc1->get_value('MyPeers_' . $CURUSER['id'])) === false) {
        $seed['yes'] = $seed['no'] = 0;
        $seed['conn'] = 3;
        $r = sql_query("SELECT COUNT(id) AS count, seeder, connectable FROM peers WHERE userid=" . sqlesc($CURUSER['id']) . " GROUP BY seeder");
        while ($a = mysqli_fetch_assoc($r)) {
            $key = $a['seeder'] == 'yes' ? 'yes' : 'no';
            $seed[$key] = number_format(0 + $a['count']);
            $seed['conn'] = $a['connectable'] == 'no' ? 1 : 2;
        }
        $mc1->cache_value('MyPeers_' . $CURUSER['id'], $seed, $INSTALLER09['expires']['MyPeers_']);
        unset($r, $a);
    } else {
        $seed = $MyPeersCache;
    }
    // for display connectable  1 / 2 / 3
    if (!empty($seed['conn'])) {
        switch ($seed['conn']) {
        case 1:
            $connectable = "<img src='{$INSTALLER09['pic_base_url']}notcon.png' alt='{$lang['gl_not_connectable']}' title='{$lang['gl_not_connectable']}' />";
            break;

        case 2:
            $connectable = "<img src='{$INSTALLER09['pic_base_url']}yescon.png' alt='{$lang['gl_connectable']}' title='{$lang['gl_connectable']}' />";
            break;

        default:
            $connectable = "{$lang['gl_na_connectable']}";
        }
    } else $connectable = $lang['gl_na_connectable'];
    }
    if (($Achievement_Points = $mc1->get_value('user_achievement_points_' . $CURUSER['id'])) === false) {
        $Sql = sql_query("SELECT users.id, users.username, usersachiev.achpoints, usersachiev.spentpoints FROM users LEFT JOIN usersachiev ON users.id = usersachiev.id WHERE users.id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $Achievement_Points = mysqli_fetch_assoc($Sql);
        $Achievement_Points['id'] = (int)$Achievement_Points['id'];
        $Achievement_Points['achpoints'] = (int)$Achievement_Points['achpoints'];
        $Achievement_Points['spentpoints'] = (int)$Achievement_Points['spentpoints'];
        $mc1->cache_value('user_achievement_points_' . $CURUSER['id'], $Achievement_Points, 0);
    }
    $member_reputation = get_reputation($CURUSER);
    $usrclass = "";
    if ($CURUSER['override_class'] != 255) $usrclass = "&nbsp;<b>(" . get_user_class_name($CURUSER['class']) . ")</b>&nbsp;";
    else if ($CURUSER['class'] >= UC_STAFF) $usrclass = "&nbsp;<a href='./setclass.php'><b>(" . get_user_class_name($CURUSER['class']) . ")</b></a>&nbsp;";
    $StatusBar = $clock = '';
    $StatusBar.= "
       <!-- U-232 Source - Print Statusbar/User Menu -->
       <script type='text/javascript'>
       //<![CDATA[
       function showSlidingDiv(){
       $('#slidingDiv').animate({'height': 'toggle'}, { duration: 1000 });
       }
       //]]>
       </script>
       <div id='base_usermenu'>" . format_username($CURUSER) . " &nbsp;&nbsp;&nbsp;<span id='clock'>{$clock}</span>&nbsp;<span class='base_usermenu_arrow'><a href='#' onclick='showSlidingDiv(); return false;'><i class='icon-chevron-down'></i></a></span></div>
       <div id='slidingDiv'>
       <div class='slide_head'>{$lang['gl_pstats']}</div>
       ".(isset($CURUSER) && $CURUSER['class'] < UC_STAFF ? "<div class='slide_a'>{$lang['gl_uclass']}</div><div class='slide_b'><b>(".get_user_class_name($CURUSER['class']).")</b></div>" : "<div class='slide_a'>{$lang['gl_uclass']}</div><div class='slide_b'>{$usrclass}</div>")."
       <div class='slide_c'>{$lang['gl_rep']}</div><div class='slide_d'>$member_reputation</div>
       <div class='slide_a'>{$lang['gl_invites']}</div><div class='slide_b'><a href='./invite.php'>{$CURUSER['invites']}</a></div>
       <div class='slide_c'>{$lang['gl_karma']}</div><div class='slide_d'><a href='./mybonus.php'>{$CURUSER['seedbonus']}</a></div>
       <div class='slide_a'>{$lang['gl_achpoints']}</div><div class='slide_b'><a href='./achievementhistory.php?id={$CURUSER['id']}'>" . (int)$Achievement_Points['achpoints'] . "</a></div>
       <div class='slide_head'>{$lang['gl_tstats']}</div>
       <div class='slide_a'>{$lang['gl_shareratio']}</div><div class='slide_b'>" . member_ratio($CURUSER['uploaded'], $INSTALLER09['ratio_free'] ? "0" : $CURUSER['downloaded']) . "</div>";
    if ($INSTALLER09['ratio_free']) {
        $StatusBar.= "<div class='slide_c'>{$lang['gl_uploaded']}</div><div class='slide_d'>$upped</div>";
    } else {
        $StatusBar.= "<div class='slide_c'>{$lang['gl_uploaded']}</div><div class='slide_d'>$upped</div>
       <div class='slide_a'>{$lang['gl_downloaded']}</div><div class='slide_b'>$downed</div>";
    }
    $StatusBar.= "<div class='slide_c'>{$lang['gl_seed_torrents']}</div><div class='slide_d'>{$seed['yes']}</div>
       <div class='slide_a'>{$lang['gl_leech_torrents']}</div><div class='slide_b'>{$seed['no']}</div>
       <div class='slide_c'>{$lang['gl_connectable']}</div><div class='slide_d'>{$connectable}</div>
        " . (isset($CURUSER) && $CURUSER['got_blocks'] == 'yes' ? "<div class='slide_head'>{$lang['gl_userblocks']}</div><div class='slide_a'>{$lang['gl_myblocks']}</div><div class='slide_b'><a href='./user_blocks.php'>{$lang['gl_click']}</a></div>" : "") . "
         " . (isset($CURUSER) && $CURUSER['got_moods'] == 'yes' ? "<div class='slide_c'>{$lang['gl_myunlocks']}</div><div class='slide_d'><a href='./user_unlocks.php'>{$lang['gl_click']}</a></div>" : "") . "
       </div>";
    $StatusBar.= '<script type="text/javascript">
      //<![CDATA[
      function refrClock(){
      var d=new Date();
      var s=d.getSeconds();
      var m=d.getMinutes();
      var h=d.getHours();
      var day=d.getDay();
      var date=d.getDate();
      var month=d.getMonth();
      var year=d.getFullYear();
      var am_pm;
      if (s<10) {s="0" + s}
      if (m<10) {m="0" + m}
      if (h>12) {h-=12;am_pm = "Pm"}
      else {am_pm="Am"}
      if (h<10) {h="0" + h}
      document.getElementById("clock").innerHTML=h + ":" + m + ":" + s + " " + am_pm;
      setTimeout("refrClock()",1000);
      }
      refrClock();
      //]]>
      </script>';
    return $StatusBar;
}
?>
