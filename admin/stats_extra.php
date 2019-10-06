<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$inbound = $_GET;
unset($inbound['page']);
if (!isset($inbound['mode'])) {
    $inbound['mode'] = '';
}
$form_code = '';
$month_names = [
    1 => _('January'),
    _('January'),
    _('February'),
    _('March'),
    _('April'),
    _('May'),
    _('June'),
    _('July'),
    _('September'),
    _('October'),
    _('November'),
    _('December'),
];
switch ($inbound['mode']) {
    case 'show_reg':
        result_screen('reg', $inbound, $month_names);
        break;

    case 'show_topic':
        result_screen('topic', $inbound, $month_names);
        break;

    case 'topic':
        main_screen('topic');
        break;

    case 'show_comms':
        result_screen('comms', $inbound, $month_names);
        break;

    case 'comms':
        main_screen('comms');
        break;

    case 'show_torrents':
        result_screen('torrents', $inbound, $month_names);
        break;

    case 'torrents':
        main_screen('torrents');
        break;

    case 'show_reps':
        result_screen('reps', $inbound, $month_names);
        break;

    case 'reps':
        main_screen('reps');
        break;

    case 'show_post':
        result_screen('post', $inbound, $month_names);
        break;

    case 'post':
        main_screen('post');
        break;

    case 'show_msg':
        result_screen('msg', $inbound, $month_names);
        break;

    case 'msg':
        main_screen('msg');
        break;

    case 'show_views':
        show_views($inbound, $month_names);
        break;

    case 'views':
        main_screen('views');
        break;

    default:
        main_screen('reg');
        break;
}

/**
 * @param array $inbound
 * @param array $month_names
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 * @throws Exception
 */
function show_views(array $inbound, array $month_names)
{
    global $container, $site_config;

    $page_title = _('Statistic Center Results');
    $from_time = strtotime("MIDNIGHT {$inbound['olddate']}");
    $to_time = strtotime("MIDNIGHT {$inbound['newdate']}") + 86400;
    $human_to_date = getdate($to_time - 86400);
    $human_from_date = getdate($from_time);
    $sort_by = $inbound['sortby'] === 'desc' ? 'DESC' : 'ASC';
    $fluent = $container->get(Database::class);
    $count = $fluent->from('topics AS t')
                    ->select(null)
                    ->select('t.forum_id')
                    ->where('t.registered >= ?', $from_time)
                    ->where('t.registered <= ?', $to_time)
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
                    ->where('t.registered >= ?', $from_time)
                    ->where('t.registered <= ?', $to_time)
                    ->groupBy('t.forum_id')
                    ->orderBy("result_count $sort_by, t.forum_id")
                    ->limit($pager['pdo']['limit'])
                    ->offset($pager['pdo']['offset'])
                    ->fetchAll();

    $running_total = 0;
    $max_result = 0;
    $results = [];
    $menu = make_side_menu();
    $heading = '' . _('Topic Views') . " ({$human_from_date['mday']} {$month_names[$human_from_date['mon']]} {$human_from_date['year']} " . _(' to ') . " {$human_to_date['mday']} {$month_names[$human_to_date['mon']]} {$human_to_date['year']})";
    $htmlout = $menu . "
        <h1 class='has-text-centered'>" . _('Statistics Center') . "</h1>
        <div class='has-text-centered padding20 bg-02 round10 bottom20 size_5'>
            $heading
        </div>";
    $table_heading = '
            <tr>
                <th>' . _('Forum Name') . '</th>
                <th>' . _('Result') . '</th>
                <th>' . _('Count') . '</th>
            </tr>';

    $body = '';
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
        foreach ($results as $data) {
            $img_width = ($data['result_count'] / $max_result) * 100 - 8;
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
        $body .= '
            <tr>
                <td></td>
                <td>
                    <div><b>' . _('Total') . "</b></div>
                </td>
                <td class='has-text-centered'><b>{$running_total}</b></td>
            </tr>";
    } else {
        $body .= "
            <tr>
                <td colspan='3'>" . _('No results found') . '</td>
            </tr>';
    }
    $htmlout .= $pagertop . main_table($body, $table_heading) . $pagerbottom;
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$page_title</a>",
    ];
    echo stdhead($page_title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout) . stdfoot();
}

/**
 * @param string $mode
 * @param array  $inbound
 * @param array  $month_names
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function result_screen(string $mode, array $inbound, array $month_names)
{
    global $container, $site_config;

    $page_title = _('Statistic Center Results');
    $table = $page_detail = $sql_table = $sql_field = '';
    $from_time = strtotime("MIDNIGHT {$inbound['olddate']}");
    $to_time = strtotime("MIDNIGHT {$inbound['newdate']}") + 86400;
    $human_to_date = getdate($to_time - 86400);
    $human_from_date = getdate($from_time);
    if ($mode === 'reg') {
        $table = _('Registration Statistics');
        $sql_table = 'users';
        $sql_field = 'registered';
        $page_detail = _('Showing the number of users registered.');
    } elseif ($mode === 'topic') {
        $table = _('New Topic Statistics');
        $sql_table = 'topics';
        $sql_field = 'added';
        $page_detail = _('Showing the number of topics started.');
    } elseif ($mode === 'post') {
        $table = _('Post Statistics');
        $sql_table = 'posts';
        $sql_field = 'added';
        $page_detail = _('Showing the number of posts.');
    } elseif ($mode === 'msg') {
        $table = _('PM Sent Statistics');
        $sql_table = 'messages';
        $sql_field = 'added';
        $page_detail = _('Showing the number of sent messages.');
    } elseif ($mode === 'comms') {
        $table = _('Comment Statistics');
        $sql_table = 'comments';
        $sql_field = 'added';
        $page_detail = _('Showing the number of sent comments.');
    } elseif ($mode === 'torrents') {
        $table = _('Torrents Statistics');
        $sql_table = 'torrents';
        $sql_field = 'added';
        $page_detail = _('Showing the number of Torrents.');
    } elseif ($mode === 'reps') {
        $table = _('Reputation Statistics');
        $sql_table = 'reputation';
        $sql_field = 'dateadd';
        $page_detail = _('Showing the number of Reputations.');
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
    $fluent = $container->get(Database::class);
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
                    ->select('COUNT(id) AS result_count')
                    ->select("MAX($sql_field) AS result_maxdate")
                    ->select("DATE_FORMAT(FROM_UNIXTIME($sql_field), '$sql_date') AS result_time")
                    ->where("$sql_field>= $from_time")
                    ->where("$sql_field <= $to_time")
                    ->groupBy('result_time')
                    ->orderBy("result_maxdate $sort_by")
                    ->limit($pager['pdo']['limit'])
                    ->offset($pager['pdo']['offset'])
                    ->fetchAll();

    $running_total = 0;
    $max_result = 0;
    $results = [];
    $heading = ucfirst($inbound['timescale']) . " $table ({$human_from_date['mday']} {$month_names[$human_from_date['mon']]} {$human_from_date['year']} to {$human_to_date['mday']} {$month_names[$human_to_date['mon']]} {$human_to_date['year']})";
    $menu = make_side_menu();

    $htmlout = $menu . "
        <h1 class='has-text-centered'>" . _('Statistics Center') . "</h1>
        <div class='has-text-centered padding20 bg-02 round10 bottom20 size_5'>
            {$heading}<br><br>
            {$page_detail}
        </div>";
    $table_heading = '
            <tr>
                <th>' . _('Date') . '</th>
                <th>' . _('Result') . '</th>
                <th>' . _('Count') . '</th>
            </tr>';
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
            $img_width = ($data['result_count'] / $max_result) * 100 - 8;
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
        $body .= '
            <tr>
                <td></td>
                <td>
                    <div><b>' . _('Total') . "</b></div>
                </td>
                <td class='has-text-centered'><b>{$running_total}</b></td>
            </tr>";
    } else {
        $body = "
            <tr>
                <td colspan='3'>" . _('No results found') . '</td>
            </tr>';
    }
    $htmlout .= $pagertop . main_table($body, $table_heading) . $pagerbottom;
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$page_title</a>",
    ];
    echo stdhead($page_title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout) . stdfoot();
}

/**
 * @param string $mode
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function main_screen($mode)
{
    global $container, $site_config;

    $form_code = $table = '';
    $page_title = _('Statistics Center');
    $page_detail = _('Please define the date ranges and other options below.') . '<br>' . _('Note: The statistics generated are based on the information currently held in the database');
    if ($mode === 'reg') {
        $form_code = 'show_reg';
        $table = _('Registration Statistics');
    } elseif ($mode === 'topic') {
        $form_code = 'show_topic';
        $table = _('New Topic Statistics');
    } elseif ($mode === 'post') {
        $form_code = 'show_post';
        $table = _('Post Statistics');
    } elseif ($mode === 'msg') {
        $form_code = 'show_msg';
        $table = _('PM Sent Statistics');
    } elseif ($mode === 'views') {
        $form_code = 'show_views';
        $table = _('Topic Views');
    } elseif ($mode === 'comms') {
        $form_code = 'show_comms';
        $table = _('Comment Statistics');
    } elseif ($mode === 'torrents') {
        $form_code = 'show_torrents';
        $table = _('Torrents Statistics');
    } elseif ($mode === 'reps') {
        $form_code = 'show_reps';
        $table = _('Reputation Statistics');
    }
    $cache = $container->get(Cache::class);
    $oldest = $cache->get('oldest_');
    if ($oldest === false || is_null($oldest)) {
        $fluent = $container->get(Database::class);
        $oldest = $fluent->from('users')
                         ->select(null)
                         ->select('registered')
                         ->orderBy('registered')
                         ->limit(1)
                         ->fetch('registered');
        $cache->set('oldest_', $oldest, 0);
    }
    $old_date = get_date((int) $oldest, 'FORM', 1, 0);
    $new_date = get_date((int) TIME_NOW, 'FORM', 1, 0);
    $menu = make_side_menu();
    $htmlout = $menu . "
        <h1 class='has-text-centered'>" . _('Statistics Center') . "</h1>
        <form action='{$site_config['paths']['baseurl']}/staffpanel.php' method='get' name='StatsForm' enctype='multipart/form-data' accept-charset='utf-8'>
            <div class='has-text-centered'>
                <input name='tool' value='stats_extra' type='hidden'>
                <input name='mode' value='{$form_code}' type='hidden'>
                <h2 class='has-text-centered'>{$table}</h2>";
    $div = "
                <h2 class='has-text-centered'>" . _('Info') . "</h2>$page_detail
                <div class='is-flex level-center padding20'>
                    <div class='padding20'>
                    <label for='olddate' class='right5'>" . _('Date From') . "</label>
                    <input id='olddate' name='olddate' type='date' value='$old_date' required>
                    </div>
                    <div class='padding20'>
                    <label for='newdate' class='left20 right5'>" . _('Date to') . "</label>
                    <input id='newdate' name='newdate' type='date' value='$new_date' required>
                    </div>";
    $timescale = '';
    if ($mode != 'views') {
        $timescale .= "
                <div class='padding20'>
                <label for='timescale' class='left20 right5'>" . _('Time scale') . '</label>';
        $timescale .= make_select('timescale', [
            0 => [
                'daily',
                _('Daily'),
            ],
            1 => [
                'weekly',
                _('Weekly'),
            ],
            2 => [
                'monthly',
                _('Monthly'),
            ],
        ]);
    }
    $timescale .= "
                <div class='padding20'>
                <label for='sortby' class='left20 right5'>" . _('Result Sorting') . '</label>';
    $timescale .= make_select('sortby', [
        0 => [
            'asc',
            _('Ascending - Oldest dates first'),
        ],
        1 => [
            'desc',
            _('Descending - Newest dates first'),
        ],
    ], 'desc');
    $div .= $timescale;
    $htmlout .= main_div($div . '</div>');
    $htmlout .= "
                <input value='" . _('Submit it!') . "' class='button is-small margin20' accesskey='s' type='submit'>
            </div>
        </form>";
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$page_title</a>",
    ];
    echo stdhead($page_title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout) . stdfoot();
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
    $html = "
            <select id='$name' name='$name' required>";
    foreach ($in as $v) {
        $selected = '';
        if (($default != '') && ($v[0] == $default)) {
            $selected = 'selected';
        }
        $html .= "
                <option value='{$v[0]}' {$selected}>{$v[1]}</option>";
    }
    $html .= '
            </select>
            </div>';

    return $html;
}

/**
 * @return string
 */
function make_side_menu()
{
    global $site_config;

    $htmlout = "
    <ul class='level-center bg-06'>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=reg'>" . _('Registration Stats') . "</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=topic'>" . _('New Topic Stats') . "</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=post'>" . _('Post Stats') . "</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=msg'>" . _('Personal Message') . "</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=views'>" . _('Topic Views') . "</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=comms'>" . _('Comment Stats') . "</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=torrents'>" . _('Torrents Stats') . "</a></li>
        <li class='margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=stats_extra&amp;mode=reps'>" . _('Reputation Stats') . '</a></li>
    </ul>';

    return $htmlout;
}
