<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_stats_extra'));
$inbound = $_GET;
unset($inbound['page']);
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
    global $inbound, $month_names, $lang, $site_config, $fluent;

    $page_title = $lang['stats_ex_ptitle'];
    $page_detail = $lang['stats_ex_pdetail'];
    $from_time = strtotime("MIDNIGHT {$inbound['olddate']}");
    $to_time = strtotime("MIDNIGHT {$inbound['newdate']}") + 86400;
    $human_to_date = getdate($to_time - 86400);
    $human_from_date = getdate($from_time);
    $sort_by = $inbound['sortby'] === 'desc' ? 'DESC' : 'ASC';
    $count = $fluent->from('topics AS t')
        ->select(null)
        ->select('t.forum_id')
        ->where('t.added>= ?', $from_time)
        ->where('t.added <= ?', $to_time)
        ->groupBy('t.forum_id')
        ->fetchAll();

    $count = !empty($count) ? count($count) : 0;
    $parsed_url = http_build_query($inbound);
    $perpage = 15;
    $pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?{$parsed_url}&amp;");
    $pagertop = $count > $perpage ? $pager['pagertop'] : '';
    $pagerbottom = $count > $perpage ? $pager['pagerbottom'] : '';
    $query = $fluent->from('topics AS t')
        ->select(null)
        ->select('SUM(t.views) AS result_count')
        ->select('t.forum_id')
        ->select('f.name AS result_name')
        ->leftJoin('forums AS f ON t.forum_id=f.id')
        ->where('t.added>= ?', $from_time)
        ->where('t.added <= ?', $to_time)
        ->groupBy('t.forum_id')
        ->orderBy("result_count $sort_by, t.forum_id")
        ->limit($pager['pdo'])
        ->fetchAll();

    $running_total = 0;
    $max_result = 0;
    $results = [];
    $menu = make_side_menu();
    $heading = "{$lang['stats_ex_topicv']} ({$human_from_date['mday']} {$month_names[$human_from_date['mon']]} {$human_from_date['year']} {$lang['stats_ex_topict']} {$human_to_date['mday']} {$month_names[$human_to_date['mon']]} {$human_to_date['year']})";
    $htmlout = $menu . "
        <h1 class='has-text-centered'>{$lang['stats_ex_center']}</h1>
        <div class='has-text-centered padding20 bg-02 round10 bottom20 size_5'>
            $heading
        </div>";
    $table_heading = "
            <tr>
                <th>{$lang['stats_ex_forum_name']}</th>
                <th>{$lang['stats_ex_result']}</th>
                <th>{$lang['stats_ex_count']}</th>
            </tr>";

    if ($count > 0) {
        foreach ($query as $row) {
            if ($row['result_count'] > $max_result) {
                $max_result = $row['result_count'];
            }
            $running_total += $row['result_count'];
            $results[] = [
                'result_name' => $row['result_name'],
                'result_count' => $row['result_count'],
            ];
        }
        $body = '';
        foreach ($results as $data) {
            $img_width = intval(($data['result_count'] / $max_result) * 100 - 8);
            if ($img_width < 1) {
                $img_width = 1;
            }
            $img_width .= '%';
            $body .= "
            <tr>
                <td>{$data['result_name']}</td>
                <td>
                    <div class='tooltipper' title='{$data['result_count']} of $running_total'>
                        <img src='{$site_config['paths']['images_baseurl']}bar_left.gif' width='4' alt='' class='bar'>
                        <img src='{$site_config['paths']['images_baseurl']}bar.gif' width='$img_width' alt='' class='bar'>
                        <img src='{$site_config['paths']['images_baseurl']}bar_right.gif' width='4' alt='' class='bar'>
                    </div>
                </td>
                <td class='has-text-centered'>{$data['result_count']}</td>
            </tr>";
        }
        $body .= "
            <tr>
                <td></td>
                <td>
                    <div><b>{$lang['stats_ex_total']}</b></div>
                </td>
                <td class='has-text-centered'><b>{$running_total}</b></td>
            </tr>";
    } else {
        $body .= "
            <tr>
                <td colspan='3'>{$lang['stats_ex_noresult']}</td>
            </tr>";
    }
    $htmlout .= $pagertop . main_table($body, $table_heading) . $pagerbottom;

    echo stdhead($page_title) . wrapper($htmlout) . stdfoot();
}

function result_screen($mode = 'reg')
{
    global $site_config, $inbound, $month_names, $lang, $fluent;

    $page_title = $lang['stats_ex_center_result'];
    $page_detail = '';
    $from_time = strtotime("MIDNIGHT {$inbound['olddate']}");
    $to_time = strtotime("MIDNIGHT {$inbound['newdate']}") + 86400;
    $human_to_date = getdate($to_time - 86400);
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
        $table = $lang['stats_ex_commsts'];
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
            $php_date = 'F jS, Y';
            break;

        case 'monthly':
            $sql_date = '%m %Y';
            $php_date = 'F Y';
            break;

        default:
            $sql_date = '%U %Y';
            $php_date = ' [F Y]';
            break;
    }
    $sort_by = $inbound['sortby'] === 'desc' ? 'DESC' : 'ASC';
    $count = $fluent->from($sql_table)
        ->select(null)
        ->select("DATE_FORMAT(FROM_UNIXTIME($sql_field), '$sql_date') AS result_time")
        ->where("$sql_field>= $from_time")
        ->where("$sql_field <= $to_time")
        ->groupBy('result_time')
        ->fetchAll();

    $count = !empty($count) ? count($count) : 0;
    $parsed_url = http_build_query($inbound);
    $perpage = 15;
    $pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?{$parsed_url}&amp;");
    $pagertop = $count > $perpage ? $pager['pagertop'] : '';
    $pagerbottom = $count > $perpage ? $pager['pagerbottom'] : '';

    $query = $fluent->from($sql_table)
        ->select(null)
        ->select('COUNT(*) AS result_count')
        ->select("MAX($sql_field) AS result_maxdate")
        ->select("DATE_FORMAT(FROM_UNIXTIME($sql_field), '$sql_date') AS result_time")
        ->where("$sql_field>= $from_time")
        ->where("$sql_field <= $to_time")
        ->groupBy('result_time')
        ->orderBy("result_maxdate $sort_by")
        ->limit($pager['pdo'])
        ->fetchAll();

    $running_total = 0;
    $max_result = 0;
    $results = [];
    $heading = ucfirst($inbound['timescale']) . " $table ({$human_from_date['mday']} {$month_names[$human_from_date['mon']]} {$human_from_date['year']} to {$human_to_date['mday']} {$month_names[$human_to_date['mon']]} {$human_to_date['year']})";
    $menu = make_side_menu();

    $htmlout = $menu . "
        <h1 class='has-text-centered'>{$lang['stats_ex_center']}</h1>
        <div class='has-text-centered padding20 bg-02 round10 bottom20 size_5'>
            {$heading}<br><br>
            {$page_detail}
        </div>";
    $table_heading = "
            <tr>
                <th>{$lang['stats_ex_date']}</th>
                <th>{$lang['stats_ex_result']}</th>
                <th>{$lang['stats_ex_count']}</th>
            </tr>";
    foreach ($query as $row) {
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
    if (!empty($results)) {
        $body = '';
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
            $body .= "
            <tr>
                <td>$date</td>
                <td>
                    <div class='tooltipper' title='{$data['result_count']} of $running_total'>
                        <img src='{$site_config['paths']['images_baseurl']}bar_left.gif' width='4' alt='' class='bar'>
                        <img src='{$site_config['paths']['images_baseurl']}bar.gif' width='$img_width' alt='' class='bar'>
                        <img src='{$site_config['paths']['images_baseurl']}bar_right.gif' width='4' alt='' class='bar'>
                    </div>
                </td>
                <td class='has-text-centered'>{$data['result_count']}</td>
            </tr>";
        }
        $body .= "
            <tr>
                <td></td>
                <td>
                    <div><b>{$lang['stats_ex_total']}</b></div>
                </td>
                <td class='has-text-centered'><b>{$running_total}</b></td>
            </tr>";
    } else {
        $body = "
            <tr>
                <td colspan='3'>{$lang['stats_ex_noresult']}</td>
            </tr>";
    }
    $htmlout .= $pagertop . main_table($body, $table_heading) . $pagerbottom;

    echo stdhead($page_title) . wrapper($htmlout) . stdfoot();
}

function main_screen($mode = 'reg')
{
    global $site_config, $lang, $cache, $fluent, $inbound;

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
        $table = $lang['stats_ex_topicv'];
    } elseif ($mode === 'comms') {
        $form_code = 'show_comms';
        $table = $lang['stats_ex_commsts'];
    } elseif ($mode === 'torrents') {
        $form_code = 'show_torrents';
        $table = $lang['stats_ex_torrsts'];
    } elseif ($mode === 'reps') {
        $form_code = 'show_reps';
        $table = $lang['stats_ex_repsts'];
    }
    $oldest = $cache->get('oldest_');
    if ($oldest === false || is_null($oldest)) {
        $oldest = $fluent->from('users')
            ->select(null)
            ->select('added')
            ->orderBy('added')
            ->limit(1)
            ->fetch('added');
        $cache->set('oldest_', $oldest, 0);
    }
    $old_date = get_date($oldest, 'FORM', 1, 0);
    $new_date = get_date(TIME_NOW, 'FORM', 1, 0);
    $menu = make_side_menu();
    $htmlout = $menu . "
        <h1 class='has-text-centered'>{$lang['stats_ex_center']}</h1>
        <form action='{$site_config['paths']['baseurl']}/staffpanel.php' method='get' name='StatsForm' accept-charset='utf-8'>
            <div class='has-text-centered'>
                <input name='tool' value='stats_extra' type='hidden'>
                <input name='mode' value='{$form_code}' type='hidden'>
                <h2 class='has-text-centered'>{$table}</h2>";
    $div = "
                <h2 class='has-text-centered'>{$lang['stats_ex_infor']}</h2>$page_detail
                <div class='is-flex level-center padding20'>
                    <div class='padding20'>
                    <label for='olddate' class='right5'>{$lang['stats_ex_datefrom']}</label>
                    <input id='olddate' name='olddate' type='date' value='$old_date' required>
                    </div>
                    <div class='padding20'>
                    <label for='newdate' class='left20 right5'>{$lang['stats_ex_dateto']}</label>
                    <input id='newdate' name='newdate' type='date' value='$new_date' required>
                    </div>";
    $timescale = '';
    if ($mode != 'views') {
        $timescale .= "
                <div class='padding20'>
                <label for='timescale' class='left20 right5'>{$lang['stats_ex_timescale']}</label>";
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
                <div class='padding20'>
                <label for='sortby' class='left20 right5'>{$lang['stats_ex_ressort']}</label>";
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
    $div .= $timescale;
    $htmlout .= main_div($div . '</div>');
    $htmlout .= "
                <input value='{$lang['stats_ex_submit']}' class='button is-small margin20' accesskey='s' type='submit'>
            </div>
        </form>";
    echo stdhead($page_title) . wrapper($htmlout) . stdfoot();
}

function make_select($name, $in = [], $default = '')
{
    $html = "
            <select id='$name' name='$name' required>";
    foreach ($in as $v) {
        $selected = '';
        if (($default != '') && ($v[0] == $default)) {
            $selected = ' selected';
        }
        $html .= "
                <option value='{$v[0]}'{$selected}>{$v[1]}</option>";
    }
    $html .= '
            </select>
            </div>';

    return $html;
}

function make_side_menu()
{
    global $site_config, $lang;

    $htmlout = "
    <ul class='level-center bg-06'>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=reg'>{$lang['stats_ex_menureg']}</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=topic'>{$lang['stats_ex_menutopnew']}</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=post'>{$lang['stats_ex_menuposts']}</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=msg'>{$lang['stats_ex_menupm']}</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=views'>{$lang['stats_ex_menutopic']}</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=comms'>{$lang['stats_ex_menucomm']}</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=torrents'>{$lang['stats_ex_menutorr']}</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=reps'>{$lang['stats_ex_menurep']}</a></li>
    </ul>";

    return $htmlout;
}
