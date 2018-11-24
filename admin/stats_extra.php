<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

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
    stderr($lang['stats_ex_stderr'], $lang['stats_ex_stderr1']);
    $to_time = strtotime($inbound['olddate']);
    $from_time = strtotime($inbound['newdate']);
    $human_to_date = getdate($to_time);
    $human_from_date = getdate($from_time);
    $sql = [
        'from_time' => $from_time,
        'to_time' => $to_time,
        'sortby' => $inbound['sortby'],
    ];
    $q = sql_query("SELECT SUM(t.views) as result_count, t.forumid, f.name as result_name
                    FROM topics AS t
                    LEFT JOIN forums AS f ON (f.id=t.forumid)
                    WHERE t.start_date > '{$sql['from_time']}'
                    AND t.start_date < '{$sql['to_time']}'
                    GROUP BY t.forumid
                    ORDER BY result_count {$sql['sortby']}") or sqlerr(__FILE__, __LINE__);
    $running_total = 0;
    $max_result = 0;
    $results = [];
    $menu = make_side_menu();
    $heading = "{$lang['stats_ex_topicv']} ({$human_from_date['mday']} {$month_names[$human_from_date['mon']]} {$human_from_date['year']} {$lang['stats_ex_topict']} {$human_to_date['mday']} {$month_names[$human_to_date['mon']]} {$human_to_date['year']})";
    $htmlout = $menu . "
    <h1 class='has-text-centered'>{$lang['stats_ex_center']}</h1>
    <div>
    <table class='table table-bordered table-striped'>
        <tr>
    <td colspan='3'>{$heading}</td>
    </tr>
        <tr>
    <td>{$lang['stats_ex_date']}</td>
    <td>{$lang['stats_ex_result']}</td>
    <td>{$lang['stats_ex_count']}</td>
    </tr>";
    if (mysqli_num_rows($q)) {
        while ($row = mysqli_fetch_assoc($q)) {
            if ($row['result_count'] > $max_result) {
                $max_result = $row['result_count'];
            }
            $running_total += $row['result_count'];
            $results[] = [
                'result_name' => $row['result_name'],
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
                <td><img src='{$site_config['pic_baseurl']}bar_left.gif' width='4' height='11' align='middle' alt=''><img src='{$site_config['pic_baseurl']}bar.gif' width='$img_width' height='11' align='middle' alt=''><img src='{$site_config['pic_baseurl']}bar_right.gif' width='4' height='11' align='middle' alt=''></td>
                    <td><center>{$data['result_count']}</center></td>
                    </tr>";
        }
        $htmlout .= "<tr>
<td>&#160;</td>
<td><div><b>{$lang['stats_ex_total']}</b></div></td>
<td><center><b>{$running_total}</b></center></td>
</tr>";
    } else {
        $htmlout .= "<tr><td colspan='3'>{$lang['stats_ex_noresult']}</td></tr>";
    }
    $htmlout .= '</table></div></div>';

    echo stdhead($page_title) . wrapper($htmlout) . stdfoot();
}

/**
 * @param string $mode
 */
function result_screen($mode = 'reg')
{
    global $site_config, $inbound, $month_names, $lang;

    $page_title = $lang['stats_ex_center_result'];
    $page_detail = '&#160;';
    $to_time = strtotime($inbound['olddate']);
    $from_time = strtotime($inbound['newdate']);
    $human_to_date = getdate($to_time);
    $human_from_date = getdate($from_time);
    if ($mode === 'reg') {
        $table = $lang['stats_ex_registr'];
        $sql_table = 'users';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_rdetails'];
    } elseif ($mode === 'topic') {
        $table = $lang['stats_ex_newtopicst'];
        $sql_table = 'topics';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_topdetails'];
    } elseif ($mode === 'post') {
        $table = $lang['stats_ex_poststs'];
        $sql_table = 'posts';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_postdetails'];
    } elseif ($mode === 'msg') {
        $table = $lang['stats_ex_pmsts'];
        $sql_table = 'messages';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_pmdetails'];
    } elseif ($mode === 'comms') {
        $table = $lang['stats_ex_comsts'];
        $sql_table = 'comments';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_cdetails'];
    } elseif ($mode === 'torrents') {
        $table = $lang['stats_ex_torrsts'];
        $sql_table = 'torrents';
        $sql_field = 'added';
        $page_detail = $lang['stats_ex_tordetails'];
    } elseif ($mode === 'reps') {
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
    $sort_by = ($inbound['sortby'] === 'DESC') ? 'DESC' : 'ASC';
    $sql = [
        'from_time' => $from_time,
        'to_time' => $to_time,
        'sortby' => $sort_by,
        'sql_field' => $sql_field,
        'sql_table' => $sql_table,
        'sql_date' => $sql_date,
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
    $htmlout = $menu . "
    <h1 class='has-text-centered'>{$lang['stats_ex_center']}</h1>
    <table class='table table-bordered table-striped'>
        <tr>
    <td colspan='3'>{$heading}<br>{$page_detail}</td>
    </tr>
        <tr>
    <td>{$lang['stats_ex_date']}</td>
    <td>{$lang['stats_ex_result']}</td>
    <td>{$lang['stats_ex_count']}</td>
    </tr>";
    if (mysqli_num_rows($q1)) {
        while ($row = mysqli_fetch_assoc($q1)) {
            if ($row['result_count'] > $max_result) {
                $max_result = $row['result_count'];
            }
            $running_total += $row['result_count'];
            $results[] = [
                'result_maxdate' => $row['result_maxdate'],
                'result_count' => $row['result_count'],
                'result_time' => $row['result_time'],
            ];
        }
        foreach ($results as $data) {
            $img_width = intval(($data['result_count'] / $max_result) * 100 - 8);
            if ($img_width < 1) {
                $img_width = 1;
            }
            $img_width .= '%';
            if ($inbound['timescale'] === 'weekly') {
                $date = 'Week #' . strftime('%W', $data['result_maxdate']) . date($php_date, $data['result_maxdate']);
            } else {
                $date = date($php_date, $data['result_maxdate']);
            }
            $htmlout .= "<tr>
                <td>$date</td>
                <td><img src='{$site_config['pic_baseurl']}bar_left.gif' width='4' height='11' align='middle' alt=''><img src='{$site_config['pic_baseurl']}bar.gif' width='$img_width' height='11' align='middle' alt=''><img src='{$site_config['pic_baseurl']}bar_right.gif' width='4' height='11' align='middle' alt=''></td>
                    <td><center>{$data['result_count']}</center></td>
                    </tr>";
        }
        $htmlout .= "<tr>
<td>&#160;</td>
<td><div><b>{$lang['stats_ex_total']}</b></div></td>
<td><center><b>{$running_total}</b></center></td>
</tr>";
    } else {
        $htmlout .= "<tr><td colspan='3'>{$lang['stats_ex_noresult']}</td></tr>";
    }
    $htmlout .= '</table></div></div>';

    echo stdhead($page_title) . wrapper($htmlout) . stdfoot();
}

function main_screen($mode = 'reg')
{
    global $site_config, $lang;

    $page_title = $lang['stats_ex_center'];
    $page_detail = "{$lang['stats_ex_details_main']}<br>{$lang['stats_ex_details_main1']}";
    if ($mode === 'reg') {
        $form_code = 'show_reg';
        $table = $lang['stats_ex_registr'];
    } elseif ($mode === 'topic') {
        $form_code = 'show_topic';
        $table = $lang['stats_ex_newtopicst'];
    } elseif ($mode === 'post') {
        $form_code = 'show_post';
        $table = $lang['stats_ex_poststs'];
    } elseif ($mode === 'msg') {
        $form_code = 'show_msg';
        $table = $lang['stats_ex_pmsts'];
    } elseif ($mode === 'views') {
        $form_code = 'show_views';
        $table = $lang['stats_ex_topicviewsts'];
    } elseif ($mode === 'comms') {
        $form_code = 'show_comms';
        $table = $lang['stats_ex_comsts'];
    } elseif ($mode === 'torrents') {
        $form_code = 'show_torrents';
        $table = $lang['stats_ex_torrsts'];
    } elseif ($mode === 'reps') {
        $form_code = 'show_reps';
        $table = $lang['stats_ex_repsts'];
    }
    $old_date = get_date(TIME_NOW - (3600 * 24 * 90), 'FORM', 1, 0);
    $new_date = get_date(TIME_NOW + (3600 * 24), 'FORM', 1, 0);
    $menu = make_side_menu();
    $htmlout = $menu . "
        <h1 class='has-text-centered'>{$lang['stats_ex_center']}</h1>
        <form action='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra' method='post' name='StatsForm'>
            <div class='has-text-centered'>
                <input name='mode' value='{$form_code}' type='hidden'>
                <h1 class='has-text-centered'>{$table}</h1>";
    $htmlout .= main_div("<h2 class='has-text-centered'>{$lang['stats_ex_infor']}</h2>$page_detail");
    $dates = "
                <h2 class='has-text-centered'>{$lang['stats_ex_datefrom']}</h2>
                <input name='olddate' type='date' value='$old_date' class='bottom20'>
                <h2>{$lang['stats_ex_dateto']}</h2>
                <input name='newdate' type='date' value='$new_date' class='bottom20'>";

    $htmlout .= main_div($dates, 'top20');
    $timescale = '';
    if ($mode != 'views') {
        $timescale .= "
                <h2 class='has-text-centered'>{$lang['stats_ex_timescale']}</h2>";
        $timescale .= make_select('timescale', [
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
            ]);
    }
    $timescale .= "
                <h2 class='has-text-centered'>{$lang['stats_ex_ressort']}</h2>";
    $timescale .= make_select('sortby', [
            0 => [
                'asc',
                $lang['stats_ex_asc'],
            ],
            1 => [
                'desc',
                $lang['stats_ex_desc'],
            ],
        ], 'desc');
    $htmlout .= main_div($timescale, 'top20');
    $htmlout .= "
                <input value='{$lang['stats_ex_submit']}' class='button is-small margin20' accesskey='s' type='submit'>
            </div>
        </form>";
    echo stdhead($page_title) . wrapper($htmlout) . stdfoot();
}

/**
 * @param        $name
 * @param array  $in
 * @param string $default
 *
 * @return string
 */
function make_select($name, $in = [], $default = '')
{
    $html = "<select name='$name' class='dropdown bottom20'>\n";
    foreach ($in as $v) {
        $selected = '';
        if (($default != '') && ($v[0] == $default)) {
            $selected = ' selected';
        }
        $html .= "<option value='{$v[0]}'{$selected}>{$v[1]}</option>\n";
    }
    $html .= "</select>\n\n";

    return $html;
}

/**
 * @return string
 */
function make_side_menu()
{
    global $site_config, $lang;

    $htmlout = "
    <ul class='level-center bg-06'>
        <li class='margin10'><a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=reg'>{$lang['stats_ex_menureg']}</a></li>
        <li class='margin10'><a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=topic'>{$lang['stats_ex_menutopnew']}</a></li>
        <li class='margin10'><a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=post'>{$lang['stats_ex_menuposts']}</a></li>
        <li class='margin10'><a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=msg'>{$lang['stats_ex_menupm']}</a></li>
        <li class='margin10'><a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=views'>{$lang['stats_ex_menutopic']}</a></li>
        <li class='margin10'><a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=comms'>{$lang['stats_ex_menucomm']}</a></li>
        <li class='margin10'><a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=torrents'>{$lang['stats_ex_menutorr']}</a></li>
        <li class='margin10'><a href='{$site_config['baseurl']}/staffpanel.php?tool=stats_extra&amp;action=stats_extra&amp;mode=reps'>{$lang['stats_ex_menurep']}</a></li>
    </ul>";

    return $htmlout;
}
