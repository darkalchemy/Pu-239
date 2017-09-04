<?php
function stdhead($title = '', $msgalert = true, $stdhead = false)
{
    global $CURUSER, $INSTALLER09, $lang, $free, $query_stat, $querytime, $mc1, $BLOCKS, $CURBLOCK, $mood;
    if (!$INSTALLER09['site_online']) {
        die('Site is down for maintenance, please check back again later... thanks<br>');
    }
    if ($title == '') {
        $title = $INSTALLER09['site_name'] . (isset($_GET['tbv']) ? ' (' . TBVERSION . ')' : '');
    } else {
        $title = $INSTALLER09['site_name'] . (isset($_GET['tbv']) ? ' (' . TBVERSION . ')' : '') . ' :: ' . htmlsafechars($title);
    }
    $css_incl = '';
    if (!empty($stdhead['css'])) {
        foreach ($stdhead['css'] as $CSS) {
            if (strpos($CSS, 'http') === false) {
                $css_incl .= "
    <link rel='stylesheet' href='./css/" . get_stylesheet() . "/{$CSS}.css' />";
            } else {
                $css_incl .= "
    <link rel='stylesheet' href='{$CSS}' />";
            }
        }
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
        $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ' . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">';
    }
    $body_class = 'background-15 h-style-1 text-1 skin-2';

    $htmlout =
$doctype . "
<head>
    <meta http-equiv='Content-Language' content='en-us' />
    <title>{$title}</title>
    <link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='./rss.php?torrent_pass={$CURUSER['torrent_pass']}' />
    <!-- favicon
    =================================================== -->
    <link rel='shortcut icon' href='favicon.ico' />
    <!-- css
    =================================================== -->
    <link rel='stylesheet' href='./css/" . get_stylesheet() . "/f6612415ae84278cd9d18ea8bca45b07.min.css' />
    <link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' integrity='sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN' crossorigin='anonymous'>
    <link href='https://fonts.googleapis.com/css?family=Acme|Baloo+Bhaijaan|Encode+Sans+Condensed|Lobster|Nova+Square|Open+Sans|Oswald|PT+Sans+Narrow' rel='stylesheet' />
    {$css_incl}
    <style>#mlike{cursor:pointer;}</style>
</head>
<body class='{$body_class}'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
    </script>
    <div class='container'>";
    if ($CURUSER) {
        $active_users_cache = $last24_cache = 0;
        $keys['last24'] = 'last24';
        $last24_cache = $mc1->get_value($keys['last24']);
        $keys['activeusers'] = 'activeusers';
        $active_users_cache = $mc1->get_value($keys['activeusers']);
        $htmlout .= "
        <div id='navigation' class='container navigation'>
            <ul>
                <li><a href='#'>{$lang['gl_torrent']}</a>
                    <ul class='sub-menu'>
                        <li><a href='./browse.php'>{$lang['gl_torrents']}</a></li>
                        <li><a href='./requests.php'>{$lang['gl_requests']}</a></li>
                        <li><a href='./offers.php'>{$lang['gl_offers']}</a></li>
                        <li><a href='./needseed.php?needed=seeders'>{$lang['gl_nseeds']}</a></li>
                        " . ($CURUSER['class'] <= UC_VIP ? "<li><a href='./uploadapp.php'>{$lang['gl_uapp']}</a> </li>" : "<li><a href='./upload.php'>{$lang['gl_upload']}</a></li>") . "
                        <li><a href='./bookmarks.php'>{$lang['gl_bookmarks']}</a></li>
                    </ul>
                </li>
                <li><a href='#'>{$lang['gl_general']}</a>
                    <ul class='sub-menu'>";
            if ($INSTALLER09['bucket_allowed'] === 1) {
                $htmlout .= "
                        <li><a href='./bitbucket.php'>{$lang['gl_bitbucket']}</a></li>";
            }
            $htmlout .= "
                        <li><a href='./announcement.php'>{$lang['gl_announcements']}</a></li>
                        <li><a href='./topten.php'>{$lang['gl_stats']}</a></li>
                        <li><a href='./faq.php'>{$lang['gl_faq']}</a></li>
                        <li><a href='./chat.php'>{$lang['gl_irc']}</a></li>
                        <li><a href='./staff.php'>{$lang['gl_staff']}</a></li>
                        <li><a href='./wiki.php'>{$lang['gl_wiki']}</a></li>
                        <li><a href='#' onclick='radio();'>{$lang['gl_radio']}</a></li>
                        <li><a href='./rsstfreak.php'>{$lang['gl_tfreak']}</a></li>
                    </ul>
                </li>
                <li><a href='#'>{$lang['gl_games']}</a>
                    <ul class='sub-menu'>
                        <li><a href='./games.php'>{$lang['gl_games']}</a></li>
                        <li><a href='./arcade.php'>{$lang['gl_arcade']}</a></li>
                        <li><a href='./lottery.php'>{$lang['gl_lottery']}</a></li>
                    </ul>
                </li>
                <li><a href='./donate.php'>{$lang['gl_donate']}</a></li>
                <li><a href='#'>{$lang['gl_forums']}</a>
                    <ul class='sub-menu'>
                        <li><a href='http://tech-info'>{$lang['gl_tforums']}</a></li>
                        <li><a href='./forums.php'>{$lang['gl_forums']}</a></li>
                    </ul>
                </li>
                <li>" . ($CURUSER['class'] < UC_STAFF ? "<a class='brand' href='./bugs.php?action=add'>{$lang['gl_breport']}</a>" : "<a class='brand' href='./bugs.php?action=bugs'>{$lang['gl_brespond']}</a>") . '</li>
                <li>' . ($CURUSER['class'] < UC_STAFF ? "<a class='brand' href='./contactstaff.php'>{$lang['gl_cstaff']}</a>" : "<a class='brand' href='./staffbox.php'>{$lang['gl_smessages']}</a>") . '</li>
            </ul>
            <small>
                <strong>';
        if (!empty($last24_cache)) {
            if ($last24_cache['totalonline24'] != 1) {
                $last24_cache['ss24'] = $lang['gl_members'];
            } else {
                $last24_cache['ss24'] = $lang['gl_member'];
            }
        }
        $htmlout .= "
                    <div>
                        <span class='left10'>
                            {$last24_cache['totalonline24']}{$last24_cache['ss24']}{{$lang['gl_last24']}}
                        </span>
                    </div>";
        if (!empty($active_users_cache)) {
            $htmlout .= "
                    <div>
                        <span class='left10'>
                            {$lang['gl_ausers']} [{$active_users_cache['au']}]
                        </span>
                    </div>";
        }
        $htmlout .= "
                </strong>
            </small>
        </div>
        <div class='clear'>
        </div>";
    }
    $htmlout .= "
        <div class='cl'>
        </div>
        <div id='logo'>
            <h1>" . TBVERSION . " Code</h1>
            <p class='description'><i>Making progress, 1 day at a time...</i></p>
        </div>";
    if ($CURUSER) {
        $salty = salty($CURUSER['username']);
        $htmlout .= "
        <div id='platform-menu' class='container platform-menu'>
            <a href='./index.php' class='home'>{$lang['gl_home']}</a>
                <ul>
                    <li><a href='./pm_system.php'>{$lang['gl_pms']}</a></li>
                    <li><a href='./usercp.php?action=default'>{$lang['gl_usercp']}</a></li>
                    " . (isset($CURUSER) && $CURUSER['class'] >= UC_STAFF ? "<li><a href='./staffpanel.php'>{$lang['gl_admin']}</a></li>" : '') . "
                    <li><a href='#' onclick='themes();'>{$lang['gl_theme']}</a></li>
                    <li><a href='#' onclick='language_select();'>{$lang['gl_language_select']}</a></li>
                    <!--<li><a href='javascript:void(0)' onclick='status_showbox()'>{$lang['gl_status']}</a></li>-->
                    <li><a href='./friends.php'>{$lang['gl_friends']}</a></li>
                    <li><a href='./logout.php?hash_please={$salty}'>{$lang['gl_logout']}</a></li>
                </ul>
                <div class='container-fluid'>
                    <div class='statusbar-container'>";
        if ($CURUSER) {
            $htmlout .= StatusBar() . "
                    </div>
                </div>
                <div id='base_globelmessage'>
                    <div id='gm_taps'>
                        <ul class='gm_taps'>
                            <li><b>{$lang['gl_alerts']}</b></li>";

            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_REPORTS && $BLOCKS['global_staff_report_on']) {
                require_once BLOCK_DIR . 'global/report.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_UPLOADAPP && $BLOCKS['global_staff_uploadapp_on']) {
                require_once BLOCK_DIR . 'global/uploadapp.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR && $BLOCKS['global_happyhour_on'] && XBT_TRACKER == false) {
                require_once BLOCK_DIR . 'global/happyhour.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_STAFF_MESSAGE && $BLOCKS['global_staff_warn_on']) {
                require_once BLOCK_DIR . 'global/staffmessages.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_NEWPM && $BLOCKS['global_message_on']) {
                require_once BLOCK_DIR . 'global/message.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_DEMOTION && $BLOCKS['global_demotion_on']) {
                require_once BLOCK_DIR . 'global/demotion.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH && $BLOCKS['global_freeleech_on'] && XBT_TRACKER == false) {
                require_once BLOCK_DIR . 'global/freeleech.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR && $BLOCKS['global_crazyhour_on'] && XBT_TRACKER == false) {
                require_once BLOCK_DIR . 'global/crazyhour.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_BUG_MESSAGE && $BLOCKS['global_bug_message_on']) {
                require_once BLOCK_DIR . 'global/bugmessages.php';
            }
            if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION && $BLOCKS['global_freeleech_contribution_on']) {
                require_once BLOCK_DIR . 'global/freeleech_contribution.php';
            }

            $htmlout .= '
                        </ul>
                    </div>
                </div>';
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
        $htmlout .= "
        </div>";
    }
    if (getSessionVar('error')) {
        $htmlout .= "
        <div class='alert alert-error text-center'>" . getSessionVar('error') . "</div>";
        unsetSessionVar('error');
    }
    $htmlout .= "
        <div id='base_content' class='round5'>
            <div id='col1' class='content'>";

    return $htmlout;
} // stdhead
function stdfoot($stdfoot = false)
{
    global $CURUSER, $INSTALLER09, $start, $query_stat, $queries, $mc1, $querytime, $lang;
    $debug = (SQL_DEBUG && in_array($CURUSER['id'], $INSTALLER09['allowed_staff']['id']) ? 1 : 0);
    $cachetime = ($mc1->Time / 1000);
    $seconds = microtime(true) - $start;
    $r_seconds = round($seconds, 5);
    //$phptime = $seconds - $cachetime;
    $phptime = $seconds - $querytime - $cachetime;
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
    if (!empty($MemStats['Hits']) && !empty($MemStats['curr_items']) && !empty($phptime) && !empty($percentmc) && !empty($cachetime)) {
        $header = '<b>' . $lang['gl_stdfoot_querys_mstat'] . '</b> ' . mksize(memory_get_peak_usage()) . ' ' . $lang['gl_stdfoot_querys_mstat1'] . ' ' . round($phptime, 2) . 's | ' . round($percentmc, 2) . '' . $lang['gl_stdfoot_querys_mstat2'] . '' . number_format($cachetime, 4) . 's ' . $lang['gl_stdfoot_querys_mstat3'] . '' . $MemStats['Hits'] . '' . $lang['gl_stdfoot_querys_mstat4'] . '' . number_format((100 - $MemStats['Hits']), 3) . '' . $lang['gl_stdfoot_querys_mstat5'] . '' . number_format($MemStats['curr_items']);
    }
    $htmlfoot = '';
    //== query stats
    $querytime = 0;
    if ($CURUSER && $query_stat && $debug) {
        $htmlfoot .= "
                <div class='container-fluid portlet'>
                    <a id='queries-hash'></a>
                    <fieldset id='queries' class='header'>
                        <legend class='flipper'><i class='fa fa-angle-up' aria-hidden='true'></i>{$lang['gl_stdfoot_querys']}</legend>
                        <table class='table table-bordered'>
                            <tbody>
                                <tr>
                                    <td class='colhead'>{$lang['gl_stdfoot_id']}</td>
                                    <td class='colhead'>{$lang['gl_stdfoot_qt']}</td>
                                    <td class='colhead'>{$lang['gl_stdfoot_qs']}</td>
                                </tr>";
        foreach ($query_stat as $key => $value) {
            $querytime += $value['seconds']; // query execution time
            $htmlfoot .= '
                                <tr>
                                    <td>' . ($key + 1) . "</td>
                                    <td align='center'><b>" . ($value['seconds'] > 0.01 ? "<font color='red' title='{$lang['gl_stdfoot_ysoq']}'>" . $value['seconds'] . '</font>' : "<font color='green' title='{$lang['gl_stdfoot_qg']}'>" . $value['seconds'] . '</font>') . "</b></td>
                                    <td align='left'>" . htmlsafechars($value['query']) . '<br></td>
                                </tr>';
        }
        $htmlfoot .= '
                            </tbody>
                        </table>
                    </fieldset>
                </div>';
    }
    $htmlfoot .= "
            </div>
        </div>";
    if ($CURUSER) {
        /* just in case **/
        $htmlfoot .= "
        <div class='nav-collapse collapse'>
            <div class='container padding10' >
                <div class='pull-left'>
                " . $INSTALLER09['site_name'] . " {$lang['gl_stdfoot_querys_page']}" . $r_seconds . " {$lang['gl_stdfoot_querys_seconds']}<br>" . "
                {$lang['gl_stdfoot_querys_server']}" . $queries . " {$lang['gl_stdfoot_querys_time']} " . ($queries != 1 ? "{$lang['gl_stdfoot_querys_times']}" : '') . '
                ' . ($debug ? '<br><b>' . $header . "</b><br><b>{$lang['gl_stdfoot_uptime']}</b> " . $uptime . '' : ' ') . "
                </div>
                <div class='pull-right' align='right'>
                {$lang['gl_stdfoot_powered']}" . TBVERSION . "<br>
                {$lang['gl_stdfoot_using']}<b>{$lang['gl_stdfoot_using1']}</b><br>
                " . ($debug ? "<a title='{$lang['gl_stdfoot_logview']}' rel='external' href='/Log_Viewer/'>{$lang['gl_stdfoot_logview']}</a> | " . "<a title='{$lang['gl_stdfoot_sview']}' rel='external' href='/staffpanel.php?tool=system_view'>{$lang['gl_stdfoot_sview']}</a> | " . "<a rel='external' title='OPCache' href='/staffpanel.php?tool=op'>{$lang['gl_stdfoot_opc']}</a> | " . "<a rel='external' title='Memcache' href='/staffpanel.php?tool=memcache'>{$lang['gl_stdfoot_memcache']}</a>" : '') . '';
        $htmlfoot .= '
                </div>
            </div>
        </div>';
    }
    $htmlfoot .= "
        <div id='control_panel'>
            <a href='#' id='control_label'></a>
        </div>
    </div>
    <a href='#' class='back-to-top'>
        <i class='fa fa-arrow-circle-up' style='font-size:48px'></i>
    </a>
    <script>
        var cookie_prefix   = '{$INSTALLER09['cookie_prefix']}';
        var cookie_path     = '{$INSTALLER09['cookie_path']}';
        var cookie_lifetime = '{$INSTALLER09['cookie_lifetime']}';
        var cookie_domain   = '{$INSTALLER09['cookie_domain']}';
        var cookie_secure   = '{$INSTALLER09['sessionCookieSecure']}';
        var csrf_token      = '" . getSessionVar('csrf_token') . "';
        var x = document.getElementsByClassName('flipper');
        var i;
        for (i = 0; i < x.length; i++) {
            var id = x[i].parentNode.id;
            if (id && localStorage[id] === 'closed') {
                var nextSibling = x[i].nextSibling;
                while (nextSibling && nextSibling.nodeType != 1) {
                    nextSibling = nextSibling.nextSibling;
                }
                nextSibling.style.display = 'none';
                child = x[i].children[0];
                child.classList.add('fa-expand');
                child.classList.remove('fa-compress');
            }
        }
    </script>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
    <script src='./js/af0ade9cd34b15b543ce38fdf1be17bf.min.js'></script>";

    if (!empty($stdfoot['js'])) {
        foreach ($stdfoot['js'] as $JS) {
            if (strpos($JS, 'http') === false) {
                $htmlfoot .= "
    <script src='./js/{$JS}.js'></script>";
            } else {
                $htmlfoot .= "
    <script src='{$JS}'></script>";
            }
        }
    }

    $htmlfoot .= "
    <!--[if lt IE 9]>
        <script src='./templates/" . get_stylesheet() . "/js/modernizr.custom.js'></script>
        <script src='http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE8.js'></script>
        <script src='./templates/" . get_stylesheet() . "/js/ie.js'></script>
    <![endif]-->

</body>
</html>";

    return $htmlfoot;
}

function stdmsg($heading, $text)
{
    $htmlout = "<table class='main' width='750' border='0' cellpadding='0' cellspacing='0'>
        <tr><td class='embedded'>\n";
    if ($heading) {
        $htmlout .= "<h2>$heading</h2>\n";
    }
    $htmlout .= "<table width='100%' border='1' cellspacing='0' cellpadding='10'>
        <tr><td class='text'>\n";
    $htmlout .= "{$text}</td></tr></table>
    </td></tr></table>\n";

    return $htmlout;
}

function hey()
{
    global $CURUSER, $lang;
    $now = date('H', TIME_NOW);
    switch ($now) {
        case $now >= 7 && $now < 11:
            return "{$lang['gl_stdhey']}";
        case $now >= 11 && $now < 13:
            return "{$lang['gl_stdhey1']}";
        case $now >= 13 && $now < 17:
            return "{$lang['gl_stdhey2']}";
        case $now >= 17 && $now < 19:
            return "{$lang['gl_stdhey3']}";
        case $now >= 19 && $now < 21:
            return "{$lang['gl_stdhey4']}";
        case $now >= 23 && $now < 0:
            return "{$lang['gl_stdhey5']}";
        case $now >= 0 && $now < 7:
            return "{$lang['gl_stdhey6']}";
        default:
            return "{$lang['gl_stdhey7']}";
    }
}

function StatusBar()
{
    global $CURUSER, $INSTALLER09, $lang, $rep_is_on, $mc1, $msgalert;
    if (!$CURUSER) {
        return '';
    }
    $StatusBar = $clock = '';
    $StatusBar .= "
                        <div id='base_usermenu' class='tooltipper-ajax'>
                            <span id='clock' class='right20'>{$clock}</span>
                            " . format_username($CURUSER['id']) . "
                        </div>";

    return $StatusBar;
}
