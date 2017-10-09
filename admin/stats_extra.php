<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_stats_extra'));
$inbound = array_merge($_GET, $_POST);
if (!isset($inbound['mode'])) {
    $inbound['mode'] = '';
}
$form_code = '';
$month_names = [
    1 => $lang['stats_ex_jan'],
    $lang['stats_ex_jan'],
    $lang['stats_ex_feb'],
    $lang['stats_ex_mar'],
    $lang['stats_ex_apr'],
    $lang['stats_ex_may'],
    $lang['stats_ex_jun'],
    $lang['stats_ex_jul'],
    $lang['stats_ex_sep'],
    $lang['stats_ex_oct'],
    $lang['stats_ex_nov'],
    $lang['stats_ex_dec'],
];
switch ($inbound['mode']) {
    case 'show_reg':
        result_screen('reg');
        break;

    case 'show_topic':
        result_screen('topic');
        break;

    case 'topic':
        main_screen('topic');
        break;

    case 'show_comms':
        result_screen('comms');
        break;

    case 'comms':
        main_screen('comms');
        break;

    case 'show_torrents':
        result_screen('torrents');
        break;

    case 'torrents':
        main_screen('torrents');
        break;

    case 'show_reps':
        result_screen('reps');
        break;

    case 'reps':
        main_screen('reps');
        break;

    case 'show_post':
        result_screen('post');
        break;

    case 'post':
        main_screen('post');
        break;

    case 'show_msg':
        result_screen('msg');
        break;

    case 'msg':
        main_screen('msg');
        break;

    case 'show_views':
        show_views();
        break;

    case 'views':
        main_screen('views');
        break;

    default:
        main_screen('reg');
        break;
}
function show_views()
{
    global $inbound, $month_names, $lang;
    $page_title = $lang['stats_ex_ptitle'];
    $page_detail = $lang['stats_ex_pdetail'];
    /* This function not available in this version, you need tbdev2010 */
    stderr($lang['stats_ex_stderr'], $lang['stats_ex_stderr1']);
    if (!checkdate($inbound['to_month'], $inbound['to_day'], $inbound['to_year'])) {
        stderr($lang['stats_ex_ustderr'], $lang['stats_ex_ustderr1']);
    }
    if (!checkdate($inbound['from_month'], $inbound['from_day'], $inbound['from_year'])) {
        stderr($lang['stats_ex_ustderr'], $lang['stats_ex_dstderr']);
    }
    $to_time = mktime(12, 0, 0, $inbound['to_month'], $inbound['to_day'], $inbound['to_year']);
    $from_time = mktime(12, 0, 0, $inbound['from_month'], $inbound['from_day'], $inbound['from_year']);
    $human_to_date = getdate($to_time);
    $human_from_date = getdate($from_time);
    $sql = [
        'from_time' => $from_time,
        'to_time'   => $to_time,
        'sortby'    => $inbound['sortby'],
    ];
    $q = sql_query("SELECT SUM(t.views) as result_count, t.forumid, f.name as result_name
					FROM topics t
					LEFT JOIN forums f ON (f.id=t.forumid)
					WHERE t.start_date > '{$sql['from_time']}'
					AND t.start_date < '{$sql['to_time']}'
					GROUP BY t.forumid
					ORDER BY result_count {$sql['sortby']}") or sqlerr(__FILE__, __LINE__);
    $running_total = 0;
    $max_result = 0;
    $results = [];
    $menu = make_side_menu();
    $heading = "{$lang['stats_ex_topicv']} ({$human_from_date['mday']} {$month_names[$human_from_date['mon']]} {$human_from_date['year']} {$lang['stats_ex_topict']} {$human_to_date['mday']} {$month_names[$human_to_date['mon']]} {$human_to_date['year']})";
    $htmlout = "<div>
      <div style='background: grey; height: 25px;'>
      <span style='font-weight: bold; font-size: 12pt;'>{$lang['stats_ex_center']}</span>
      </div><br>
    {$menu}
		
		<div><table border='0' cellpadding='5' cellspacing='0' width='70%'>
		<tr>
    <td colspan='3'>{$heading}</td>
    </tr>
		<tr>
    <td width='20%'>{$lang['stats_ex_date']}</td>
    <td width='70%'>{$lang['stats_ex_result']}</td>
    <td width='10%'>{$lang['stats_ex_count']}</td>
    </tr>";
    if (mysqli_num_rows($q)) {
        while ($row = mysqli_fetch_assoc($q)) {
            if ($row['result_count'] > $max_result) {
                $max_result = $row['result_count'];
            }
            $running_total += $row['result_count'];
            $results[] = [
                'result_name'  => $row['result_name'],
                'result_count' => $row['result_count'],
            ];
        }
        foreach ($results as $data) {
            $img_width = intval(($data['result_count'] / $max_result) * 100 - 8);
            if ($img_width < 1) {
                $img_width = 1;
            }
            $img_width .= '%';
            $htmlout .= "<tr>
    			<td>$date</td>
    			<td><img src='{$site_config['pic_base_url']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt='' /><img src='{$site_config['pic_base_url']}/bar.gif' border='0' width='$img_width' height='11' align='middle' alt='' /><img src='{$site_config['pic_base_url']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt='' /></td>
					<td><center>{$data['result_count']}</center></td>
					</tr>";
        }
        $htmlout .= "<tr>
<td width='20%'>&#160;</td>
<td width='70%'><div><b>{$lang['stats_ex_total']}</b></div></td>
<td width='10%'><center><b>{$running_total}</b></center></td>
</tr>";
    } else {
        $htmlout .= "<tr><td colspan='3'>{$lang['stats_ex_noresult']}</td></tr>";
    }
    $htmlout .= '</table></div></div>';
    echo stdhead($page_title) . $htmlout . stdfoot();
}

function result_screen($mode = 'reg')
{
    global $site_config, $inbound, $month_names, $lang;
    $page_title = $lang['stats_ex_center_result'];
    $page_detail = '&#160;';
    if (!checkdate($inbound['to_month'], $inbound['to_day'], $inbound['to_year'])) {
        stderr($lang['stats_ex_ustderr'], $lang['stats_ex_ustderr1']);
    }
    if (!checkdate($inbound['from_month'], $inbound['from_day'], $inbound['from_year'])) {
        stderr($lang['stats_ex_ustderr'], $lang['stats_ex_dstderr']);
    }
    $to_time = mktime(0, 0, 0, $inbound['to_month'], $inbound['to_day'], $inbound['to_year']);
    $from_time = mktime(0, 0, 0, $inbound['from_month'], $inbound['from_day'], $inbound['from_year']);
    $human_to_date = getdate($to_time);
    $human_from_date = getdate($from_time);
    if ($mode == 'reg') {
        $table = $lang['stats_ex_registr'];
        $sql_table = 'users';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_rdetails'];
    } elseif ($mode == 'topic') {
        $table = $lang['stats_ex_newtopicst'];
        $sql_table = 'topics';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_topdetails'];
    } elseif ($mode == 'post') {
        $table = $lang['stats_ex_poststs'];
        $sql_table = 'posts';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_postdetails'];
    } elseif ($mode == 'msg') {
        $table = $lang['stats_ex_pmsts'];
        $sql_table = 'messages';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_pmdetails'];
    } elseif ($mode == 'comms') {
        $table = $lang['stats_ex_comsts'];
        $sql_table = 'comments';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_cdetails'];
    } elseif ($mode == 'torrents') {
        $table = $lang['stats_ex_torrsts'];
        $sql_table = 'torrents';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_tordetails'];
    } elseif ($mode == 'reps') {
        $table = $lang['stats_ex_repsts'];
        $sql_table = 'reputation';
        $sql_field = 'dateadd';
        $page_detail = $lang['stats_ex_repdetails'];
    }
    switch ($inbound['timescale']) {
        case 'daily':
            $sql_date = '%w %U %m %Y';
            $php_date = 'F jS - Y';
            break;

        case 'monthly':
            $sql_date = '%m %Y';
            $php_date = 'F Y';
            break;

        default:
            // weekly
            $sql_date = '%U %Y';
            $php_date = ' [F Y]';
            break;
    }
    $sort_by = ($inbound['sortby'] == 'DESC') ? 'DESC' : 'ASC';
    $sql = [
        'from_time' => $from_time,
        'to_time'   => $to_time,
        'sortby'    => $sort_by,
        'sql_field' => $sql_field,
        'sql_table' => $sql_table,
        'sql_date'  => $sql_date,
    ];
    $q1 = sql_query("SELECT MAX({$sql['sql_field']}) as result_maxdate,
				 COUNT(*) as result_count,
				 DATE_FORMAT(from_unixtime({$sql['sql_field']}),'{$sql['sql_date']}') AS result_time
				 FROM {$sql['sql_table']}
				 WHERE {$sql['sql_field']} > '{$sql['from_time']}'
				 AND {$sql['sql_field']} < '{$sql['to_time']}'
				 GROUP BY result_time
				 ORDER BY {$sql['sql_field']} {$sql['sortby']}");
    $running_total = 0;
    $max_result = 0;
    $results = [];
    $heading = ucfirst($inbound['timescale']) . " $table ({$human_from_date['mday']} {$month_names[$human_from_date['mon']]} {$human_from_date['year']} to {$human_to_date['mday']} {$month_names[$human_to_date['mon']]} {$human_to_date['year']})";
    $menu = make_side_menu();
    $htmlout = "<div>
      <div style='background: grey; height: 25px;'>
      <span style='font-weight: bold; font-size: 12pt;'>{$lang['stats_ex_center']}</span>
      </div><br>
    {$menu}
		
		<div><table border='0' cellpadding='5' cellspacing='0' width='70%'>
		<tr>
    <td colspan='3'>{$heading}<br>{$page_detail}</td>
    </tr>
		<tr>
    <td width='20%'>{$lang['stats_ex_date']}</td>
    <td width='70%'>{$lang['stats_ex_result']}</td>
    <td width='10%'>{$lang['stats_ex_count']}</td>
    </tr>";
    if (mysqli_num_rows($q1)) {
        while ($row = mysqli_fetch_assoc($q1)) {
            if ($row['result_count'] > $max_result) {
                $max_result = $row['result_count'];
            }
            $running_total += $row['result_count'];
            $results[] = [
                'result_maxdate' => $row['result_maxdate'],
                'result_count'   => $row['result_count'],
                'result_time'    => $row['result_time'],
            ];
        }
        foreach ($results as $data) {
            $img_width = intval(($data['result_count'] / $max_result) * 100 - 8);
            if ($img_width < 1) {
                $img_width = 1;
            }
            $img_width .= '%';
            if ($inbound['timescale'] == 'weekly') {
                $date = 'Week #' . strftime('%W', $data['result_maxdate']) . date($php_date, $data['result_maxdate']);
            } else {
                $date = date($php_date, $data['result_maxdate']);
            }
            $htmlout .= "<tr>
    			<td>$date</td>
    			<td><img src='{$site_config['pic_base_url']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt='' /><img src='{$site_config['pic_base_url']}/bar.gif' border='0' width='$img_width' height='11' align='middle' alt='' /><img src='{$site_config['pic_base_url']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt='' /></td>
					<td><center>{$data['result_count']}</center></td>
					</tr>";
        }
        $htmlout .= "<tr>
<td width='20%'>&#160;</td>
<td width='70%'><div><b>{$lang['stats_ex_total']}</b></div></td>
<td width='10%'><center><b>{$running_total}</b></center></td>
</tr>";
    } else {
        $htmlout .= "<tr><td colspan='3'>{$lang['stats_ex_noresult']}</td></tr>";
    }
    $htmlout .= '</table></div></div>';
    echo stdhead($page_title) . $htmlout . stdfoot();
}

function main_screen($mode = 'reg')
{
    global $site_config, $lang;
    $page_title = $lang['stats_ex_center'];
    $page_detail = "{$lang['stats_ex_details_main']}<br>{$lang['stats_ex_details_main1']}";
    if ($mode == 'reg') {
        $form_code = 'show_reg';
        $table = $lang['stats_ex_registr'];
    } elseif ($mode == 'topic') {
        $form_code = 'show_topic';
        $table = $lang['stats_ex_newtopicst'];
    } elseif ($mode == 'post') {
        $form_code = 'show_post';
        $table = $lang['stats_ex_poststs'];
    } elseif ($mode == 'msg') {
        $form_code = 'show_msg';
        $table = $lang['stats_ex_pmsts'];
    } elseif ($mode == 'views') {
        $form_code = 'show_views';
        $table = $lang['stats_ex_topicviewsts'];
    } elseif ($mode == 'comms') {
        $form_code = 'show_comms';
        $table = $lang['stats_ex_comsts'];
    } elseif ($mode == 'torrents') {
        $form_code = 'show_torrents';
        $table = $lang['stats_ex_torrsts'];
    } elseif ($mode == 'reps') {
        $form_code = 'show_reps';
        $table = $lang['stats_ex_repsts'];
    }
    $old_date = getdate(time() - (3600 * 24 * 90));
    $new_date = getdate(time() + (3600 * 24));
    $menu = make_side_menu();
    $htmlout = "<div>
      <div style='background: grey; height: 25px;'>
      <span style='font-weight: bold; font-size: 12pt;'>{$lang['stats_ex_center']}</span>
      </div><br>
    {$menu}
    <form action='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra' method='post' name='StatsForm'>
    <input name='mode' value='{$form_code}' type='hidden' />

	
    <div style='text-align: left; width: 50%; border: 1px solid blue; padding: 5px;'>
		<div style='background: grey; height: 25px; margin-bottom:20px;'>
      <span style='font-weight: bold; font-size: 12pt;'>{$table}</span>
    </div>
    <fieldset><legend><strong>{$lang['stats_ex_infor']}</strong></legend>
    {$page_detail}</fieldset>
		<fieldset><legend><strong>{$lang['stats_ex_datefrom']}</strong></legend>";
    $htmlout .= make_select('from_month', make_month(), $old_date['mon']) . '&#160;&#160;';
    $htmlout .= make_select('from_day', make_day(), $old_date['mday']) . '&#160;&#160;';
    $htmlout .= make_select('from_year', make_year(), $old_date['year']) . '</fieldset>';
    $htmlout .= "<fieldset><legend><strong>{$lang['stats_ex_dateto']}</strong></legend>";
    $htmlout .= make_select('to_month', make_month(), $new_date['mon']) . '&#160;&#160;';
    $htmlout .= make_select('to_day', make_day(), $new_date['mday']) . '&#160;&#160;';
    $htmlout .= make_select('to_year', make_year(), $new_date['year']) . '</fieldset>';
    if ($mode != 'views') {
        $htmlout .= "<fieldset><legend><strong>{$lang['stats_ex_timescale']}</strong></legend>";
        $htmlout .= make_select('timescale', [
                0 => [
                    'daily',
                    $lang['stats_ex_daily'],
                ],
                1 => [
                    'weekly',
                    $lang['stats_ex_weekly'],
                ],
                2 => [
                    'monthly',
                    $lang['stats_ex_monthly'],
                ],
            ]) . '</fieldset>';
    }
    $htmlout .= "<fieldset><legend><strong>{$lang['stats_ex_ressort']}</strong></legend>";
    $htmlout .= make_select('sortby', [
            0 => [
                'asc',
                $lang['stats_ex_asc'],
            ],
            1 => [
                'desc',
                $lang['stats_ex_desc'],
            ],
        ], 'desc') . '</fieldset>';
    $htmlout .= "<fieldset><legend><strong>{$lang['stats_ex_submit']}</strong></legend>
				<input value='{$lang['stats_ex_show']}' class='btn' accesskey='s' type='submit' />
			</fieldset>

		</div>
	
    </form></div>";
    echo stdhead($page_title) . $htmlout . stdfoot();
}

function make_year()
{
    $time_now = getdate();
    $return = [];
    $start_year = 2005;
    $latest_year = intval($time_now['year']);
    if ($latest_year == $start_year) {
        $start_year -= 1;
    }
    for ($y = $start_year; $y <= $latest_year; ++$y) {
        $return[] = [
            $y,
            $y,
        ];
    }

    return $return;
}

function make_month()
{
    global $month_names;
    $return = [];
    for ($m = 1; $m <= 12; ++$m) {
        $return[] = [
            $m,
            $month_names[$m],
        ];
    }

    return $return;
}

function make_day()
{
    $return = [];
    for ($d = 1; $d <= 31; ++$d) {
        $return[] = [
            $d,
            $d,
        ];
    }

    return $return;
}

function make_select($name, $in = [], $default = '')
{
    $html = "<select name='$name' class='dropdown'>\n";
    foreach ($in as $v) {
        $selected = '';
        if (($default != '') and ($v[0] == $default)) {
            $selected = " selected='selected'";
        }
        $html .= "<option value='{$v[0]}'{$selected}>{$v[1]}</option>\n";
    }
    $html .= "</select>\n\n";

    return $html;
}

function make_side_menu()
{
    global $site_config, $lang;
    $htmlout = "<div style='float:left;border: 1px solid black;padding:5px;'>
    <div><strong>{$lang['stats_ex_menu']}</strong></div>
    <div>&#160;&#160;<a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=reg' style='text-decoration: none;'>{$lang['stats_ex_menureg']}</a></div>
    <div>&#160;&#160;<a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=topic' style='text-decoration: none;'>{$lang['stats_ex_menutopnew']}</a></div>
    <div>&#160;&#160;<a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=post' style='text-decoration: none;'>{$lang['stats_ex_menuposts']}</a></div>
    <div>&#160;&#160;<a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=msg' style='text-decoration: none;'>{$lang['stats_ex_menupm']}</a></div>
    <div>&#160;&#160;<a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=views' style='text-decoration: none;'>{$lang['stats_ex_menutopic']}</a></div>
    <div>&#160;&#160;<a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=comms' style='text-decoration: none;'>{$lang['stats_ex_menucomm']}</a></div>
    <div>&#160;&#160;<a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=torrents' style='text-decoration: none;'>{$lang['stats_ex_menutorr']}</a></div>
    <div>&#160;&#160;<a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=reps' style='text-decoration: none;'>{$lang['stats_ex_menurep']}</a></div>
</div>";

    return $htmlout;
}
