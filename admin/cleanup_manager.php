<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

$lang = array_merge($lang, load_language('ad_cleanup_manager'));
$params = array_merge($_GET, $_POST);
$params['mode'] = isset($params['mode']) ? $params['mode'] : '';
switch ($params['mode']) {
    case 'unlock':
        cleanup_take_unlock();
        break;

    case 'delete':
        cleanup_take_delete();
        break;

    case 'takenew':
        cleanup_take_new();
        break;

    case 'new':
        cleanup_show_new();
        break;

    case 'takeedit':
        cleanup_take_edit();
        break;

    case 'edit':
        cleanup_show_edit();
        break;

    case 'run':
        manualclean();
        break;

    default:
        cleanup_show_main();
        break;
}
function manualclean()
{
    // these clean_ids need to be run at specific interval, regardless of when they run
    $run_at_specified_times = [
        82,
        83,
    ];

    global $params, $lang;
    if (function_exists('docleanup')) {
        stderr($lang['cleanup_stderr'], $lang['cleanup_stderr1']);
    }
    $opts = [
        'options' => [
            'min_range' => 1,
        ],
    ];
    $params['cid'] = filter_var($params['cid'], FILTER_VALIDATE_INT, $opts);
    if (!is_numeric($params['cid'])) {
        stderr($lang['cleanup_stderr'], $lang['cleanup_stderr2']);
    }
    $params['cid'] = sqlesc($params['cid']);
    $sql = sql_query('SELECT * FROM cleanup WHERE clean_id = ' . sqlesc($params['cid'])) or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($sql);
    if ($row['clean_id']) {
        $next_clean = intval(TIME_NOW + ($row['clean_increment'] ? $row['clean_increment'] : 15 * 60));
        if (in_array($row['clean_id'], $run_at_specified_times)) {
            $next_clean = intval($row['clean_time'] + $row['clean_increment']);
        }
        sql_query('UPDATE cleanup SET clean_time = ' . sqlesc($next_clean) . ' WHERE clean_id = ' . sqlesc($row['clean_id'])) or sqlerr(__FILE__, __LINE__);
        if (file_exists(CLEAN_DIR . $row['clean_file'])) {
            require_once CLEAN_DIR . $row['clean_file'];
            if (function_exists($row['function_name'])) {
                register_shutdown_function($row['function_name'], $row);
            }
        }
    }

    cleanup_show_main(); //instead of header() so can see queries in footer (using sql_query())
    die();
}

function cleanup_show_main()
{
    global $site_config, $lang;
    $count1 = get_row_count('cleanup');
    $perpage = 15;
    $pager = pager($perpage, $count1, $site_config['baseurl'] . '/staffpanel.php?tool=cleanup_manager&amp;');
    $htmlout = "
    <div class='container is-fluid portlet'>
        <h2 class='has-text-centered top20'>{$lang['cleanup_head']}</h2>
        <table class='table table-bordered table-striped bottom20'>
            <thead>
                <tr>
                    <th>{$lang['cleanup_title']}</th>
                    <th class='has-text-centered'>{$lang['cleanup_run']}</th>
                    <th class='has-text-centered'>{$lang['cleanup_next']}</th>
                    <th class='has-text-centered'>{$lang['cleanup_edit']}</th>
                    <th class='has-text-centered'>{$lang['cleanup_delete']}</th>
                    <th class='has-text-centered'>{$lang['cleanup_on']}</th>
                    <th class='has-text-centered'>{$lang['cleanup_run_now']}</th>
                </tr>
            </thead>
            <tbody>";
    $sql = sql_query("SELECT * FROM cleanup ORDER BY clean_on DESC, clean_time ASC, clean_increment DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
    if (!mysqli_num_rows($sql)) {
        stderr($lang['cleanup_stderr'], $lang['cleanup_panic']);
    }
    while ($row = mysqli_fetch_assoc($sql)) {
        $row['_clean_time'] = get_date($row['clean_time'], 'LONG');
        $row['clean_increment'] = (int)$row['clean_increment'];
        $row['_class'] = $row['clean_on'] != 1 ? " style='color:red'" : '';
        $row['_title'] = $row['clean_on'] != 1 ? " {$lang['cleanup_lock']}" : '';
        $row['_clean_time'] = $row['clean_on'] != 1 ? "<span style='color:red'>{$row['_clean_time']}</span>" : $row['_clean_time'];
        $htmlout .= "
        <tr>
            <td{$row['_class']}>{$row['clean_title']}{$row['_title']}<br><span class='size_3'>{$row['clean_desc']}</span></td>
            <td class='has-text-centered'>" . mkprettytime($row['clean_increment']) . "</td>
            <td class='has-text-centered'>{$row['_clean_time']}</td>
            <td class='has-text-centered'>
                <a href='{$site_config['baseurl']}/staffpanel.php?tool=cleanup_manager&amp;action=cleanup_manager&amp;mode=edit&amp;cid={$row['clean_id']}'>
                <img src='{$site_config['pic_baseurl']}aff_tick.gif' alt='{$lang['cleanup_edit2']}' class='tooltipper' title='{$lang['cleanup_edit']}' height='12' width='12' /></a>
            </td>
            <td class='has-text-centered'>
                <a href='{$site_config['baseurl']}/staffpanel.php?tool=cleanup_manager&amp;action=cleanup_manager&amp;mode=delete&amp;cid={$row['clean_id']}'>
                <img src='{$site_config['pic_baseurl']}aff_cross.gif' alt='{$lang['cleanup_delete2']}' class='tooltipper' title='{$lang['cleanup_delete1']}' height='12' width='12' /></a>
            </td>
            <td class='has-text-centered'>
                <a href='{$site_config['baseurl']}/staffpanel.php?tool=cleanup_manager&amp;action=cleanup_manager&amp;mode=unlock&amp;cid={$row['clean_id']}&amp;clean_on={$row['clean_on']}'>
                <img src='{$site_config['pic_baseurl']}warned.png' alt='{$lang['cleanup_off_on2']}' class='tooltipper' title='{$lang['cleanup_off_on']}' height='12' width='12' /></a>
            </td>
            <td class='has-text-centered'>
                <a href='{$site_config['baseurl']}/staffpanel.php?tool=cleanup_manager&amp;action=cleanup_manager&amp;mode=run&amp;cid={$row['clean_id']}'>{$lang['cleanup_run_now2']}</a>
            </td>
         </tr>";
    }
    $htmlout .= '</tbody></table></div>';
    if ($count1 > $perpage) {
        $htmlout = $pager['pagertop'] . $htmlout . $pager['pagerbottom'];
    }
    $htmlout .= "
                <div class='has-text-centered top20'>
                    <a href='{$site_config['baseurl']}/staffpanel.php?tool=cleanup_manager&amp;action=cleanup_manager&amp;mode=new' class='margin20 button is-small'>{$lang['cleanup_add_new']}</a>
                </div>";
    echo stdhead($lang['cleanup_stdhead']) . wrapper($htmlout) . stdfoot();
}

function cleanup_show_edit()
{
    global $params, $lang;
    if (!isset($params['cid']) || empty($params['cid']) || !is_valid_id($params['cid'])) {
        cleanup_show_main();
        exit;
    }
    $cid = intval($params['cid']);
    $sql = sql_query("SELECT * FROM cleanup WHERE clean_id = $cid");
    if (!mysqli_num_rows($sql)) {
        stderr($lang['cleanup_stderr'], $lang['cleanup_stderr3']);
    }
    $row = mysqli_fetch_assoc($sql);
    $row['clean_title'] = htmlsafechars($row['clean_title'], ENT_QUOTES);
    $row['clean_desc'] = htmlsafechars($row['clean_desc'], ENT_QUOTES);
    $row['clean_file'] = htmlsafechars($row['clean_file'], ENT_QUOTES);
    $row['clean_title'] = htmlsafechars($row['clean_title'], ENT_QUOTES);
    $row['function_name'] = htmlsafechars($row['function_name'], ENT_QUOTES);
    $logyes = $row['clean_log'] ? 'checked' : '';
    $logno = !$row['clean_log'] ? 'checked' : '';
    $cleanon = $row['clean_on'] ? 'checked' : '';
    $cleanoff = !$row['clean_on'] ? 'checked' : '';
    $htmlout = "<h2>{$lang['cleanup_show_head']} {$row['clean_title']}</h2>
    <div style='width: 800px; text-align: left; padding: 10px; margin: 0 auto;border-style: solid; border-color: #333333; border-width: 5px 2px;'>
    <form name='inputform' method='post' action='staffpanel.php?tool=cleanup_manager&amp;action=cleanup_manager'>
    <input type='hidden' name='mode' value='takeedit' />
    <input type='hidden' name='cid' value='{$row['clean_id']}' />

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_title']}</label>
    <input type='text' value='{$row['clean_title']}' name='clean_title' style='width:250px;' /></div>
    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_descr']}</label>
    <input type='text' value='{$row['clean_desc']}' name='clean_desc' style='width:380px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_fname']}</label>
    <input type='text' value='{$row['function_name']}' name='function_name' style='width:380px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_file']}</label>
    <input type='text' value='{$row['clean_file']}' name='clean_file' style='width:380px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_interval']}</label>
    <input type='text' value='{$row['clean_increment']}' name='clean_increment' style='width:380px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_log']}</label>{$lang['cleanup_show_yes']}<input name='clean_log' value='1' $logyes type='radio' />&#160;&#160;&#160;<input name='clean_log' value='0' $logno type='radio' />{$lang['cleanup_show_no']}</div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_on']}</label>
    {$lang['cleanup_show_yes']} <input name='clean_on' value='1' $cleanon type='radio' />&#160;&#160;&#160;<input name='clean_on' value='0' $cleanoff type='radio' /> {$lang['cleanup_show_no']}
    </div>

    <div style='text-align:center;'>
        <input type='submit' name='submit' value='{$lang['cleanup_show_edit']}' class='button is-small right1-' />
        <input type='button' class='button is-small' value='{$lang['cleanup_show_cancel']}' onclick='javascript: history.back()' />
    </div>
    </form>
    </div>";
    echo stdhead($lang['cleanup_show_stdhead']) . wrapper($htmlout) . stdfoot();
}

function cleanup_take_edit()
{
    global $params, $lang;
    //ints
    foreach ([
                 'cid',
                 'clean_increment',
                 'clean_log',
                 'clean_on',
             ] as $x) {
        unset($opts);
        if ($x == 'cid' || $x == 'clean_increment') {
            $opts = [
                'options' => [
                    'min_range' => 1,
                ],
            ];
        } else {
            $opts = [
                'options' => [
                    'min_range' => 0,
                    'max_range' => 1,
                ],
            ];
        }
        $params[$x] = filter_var($params[$x], FILTER_VALIDATE_INT, $opts);
        if (!is_numeric($params[$x])) {
            stderr($lang['cleanup_take_error'], "{$lang['cleanup_take_error1']} $x");
        }
    }
    unset($opts);
    // strings
    foreach ([
                 'clean_title',
                 'clean_desc',
                 'clean_file',
                 'function_name',
             ] as $x) {
        $opts = [
            'flags' => FILTER_FLAG_STRIP_LOW,
            FILTER_FLAG_STRIP_HIGH,
        ];
        $params[$x] = filter_var($params[$x], FILTER_SANITIZE_STRING, $opts);
        if (empty($params[$x])) {
            stderr($lang['cleanup_take_error'], "{$lang['cleanup_take_error2']}");
        }
    }
    $params['clean_file'] = preg_replace('#\.{1,}#s', '.', $params['clean_file']);
    if (!file_exists(CLEAN_DIR . "{$params['clean_file']}")) {
        stderr($lang['cleanup_take_error'], "{$lang['cleanup_take_error3']}");
    }
    // new clean time =
    $params['clean_time'] = intval(TIME_NOW + $params['clean_increment']);
    //one more time around! LoL
    foreach ($params as $k => $v) {
        $params[$k] = sqlesc($v);
    }
    sql_query("UPDATE cleanup SET function_name = {$params['function_name']}, clean_title = {$params['clean_title']}, clean_desc = {$params['clean_desc']}, clean_file = {$params['clean_file']}, clean_time = {$params['clean_time']}, clean_increment = {$params['clean_increment']}, clean_log = {$params['clean_log']}, clean_on = {$params['clean_on']} WHERE clean_id = {$params['cid']}");
    cleanup_show_main();
    die();
}

function cleanup_show_new()
{
    global $lang;
    $htmlout = "<h2>{$lang['cleanup_new_head']}</h2>
    <div style='width: 800px; text-align: left; padding: 10px; margin: 0 auto;border-style: solid; border-color: #333333; border-width: 5px 2px;'>
    <form name='inputform' method='post' action='staffpanel.php?tool=cleanup_manager&amp;action=cleanup_manager'>
    <input type='hidden' name='mode' value='takenew' />

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_title']}</label>
    <input type='text' value='' name='clean_title' style='width:350px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_descr']}</label>
    <input type='text' value='' name='clean_desc' style='width:350px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_fname']}</label>
    <input type='text' value='' name='function_name' style='width:350px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_file']}</label>
    <input type='text' value='' name='clean_file' style='width:350px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_interval']}</label>
    <input type='text' value='' name='clean_increment' style='width:350px;' />
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_log']}</label>
    {$lang['cleanup_show_yes']} <input name='clean_log' value='1' type='radio' />&#160;&#160;&#160;<input name='clean_log' value='0' checked type='radio' /> {$lang['cleanup_show_no']}
    </div>

    <div style='margin-bottom:5px;'>
    <label style='float:left;width:200px;'>{$lang['cleanup_show_on']}</label>
    {$lang['cleanup_show_yes']} <input name='clean_on' value='1' type='radio' />&#160;&#160;&#160;<input name='clean_on' value='0' checked type='radio' /> {$lang['cleanup_show_no']}
    </div>

    <div style='text-align:center;'>
        <input type='submit' name='submit' value='{$lang['cleanup_new_add']}' class='button is-small right10' />
        <input type='button' class='button is-small' value='{$lang['cleanup_new_cancel']}' onclick='javascript: history.back()' />
    </div>
    </form>
    </div>";
    echo stdhead($lang['cleanup_new_stdhead']) . wrapper($htmlout) . stdfoot();
}

function cleanup_take_new()
{
    global $params, $lang;
    //ints
    foreach ([
                 'clean_increment',
                 'clean_log',
                 'clean_on',
             ] as $x) {
        unset($opts);
        if ($x == 'clean_increment') {
            $opts = [
                'options' => [
                    'min_range' => 1,
                ],
            ];
        } else {
            $opts = [
                'options' => [
                    'min_range' => 0,
                    'max_range' => 1,
                ],
            ];
        }
        $params[$x] = filter_var($params[$x], FILTER_VALIDATE_INT, $opts);
        if (!is_numeric($params[$x])) {
            stderr($lang['cleanup_take_error'], "{$lang['cleanup_take_error1']} $x");
        }
    }
    unset($opts);
    // strings
    foreach ([
                 'clean_title',
                 'clean_desc',
                 'clean_file',
                 'function_name',
             ] as $x) {
        $opts = [
            'flags' => FILTER_FLAG_STRIP_LOW,
            FILTER_FLAG_STRIP_HIGH,
        ];
        $params[$x] = filter_var($params[$x], FILTER_SANITIZE_STRING, $opts);
        if (empty($params[$x])) {
            stderr($lang['cleanup_take_error'], "{$lang['cleanup_take_error2']}");
        }
    }
    $params['clean_file'] = preg_replace('#\.{1,}#s', '.', trim($params['clean_file']));
    if (!file_exists(CLEAN_DIR . "{$params['clean_file']}")) {
        stderr($lang['cleanup_take_error'], "{$lang['cleanup_take_error3']}");
    }
    // new clean time =
    $params['clean_time'] = intval(time() + $params['clean_increment']);
    //one more time around! LoL
    foreach ($params as $k => $v) {
        $params[$k] = sqlesc($v);
    }
    sql_query("INSERT INTO cleanup (function_name, clean_title, clean_desc, clean_file, clean_time, clean_increment, clean_log, clean_on) VALUES ({$params['function_name']}, {$params['clean_title']}, {$params['clean_desc']}, {$params['clean_file']}, {$params['clean_time']}, {$params['clean_increment']}, {$params['clean_log']}, {$params['clean_on']})");
    if (((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res)) {
        stderr($lang['cleanup_new_info'], "{$lang['cleanup_new_success']}");
    } else {
        stderr($lang['cleanup_new_error'], "{$lang['cleanup_new_error1']}");
    }
    die();
}

function cleanup_take_delete()
{
    global $params, $lang;
    $opts = [
        'options' => [
            'min_range' => 1,
        ],
    ];
    $params['cid'] = filter_var($params['cid'], FILTER_VALIDATE_INT, $opts);
    if (!is_numeric($params['cid'])) {
        stderr($lang['cleanup_del_error'], "{$lang['cleanup_del_error1']}");
    }
    $params['cid'] = sqlesc($params['cid']);
    sql_query("DELETE FROM cleanup WHERE clean_id = {$params['cid']}");
    if (1 === mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr($lang['cleanup_del_info'], "{$lang['cleanup_del_success']}");
    } else {
        stderr($lang['cleanup_del_error'], "{$lang['cleanup_del_error2']}");
    }
    die();
}

function cleanup_take_unlock()
{
    global $params, $lang;
    foreach ([
                 'cid',
                 'clean_on',
             ] as $x) {
        unset($opts);
        if ($x == 'cid') {
            $opts = [
                'options' => [
                    'min_range' => 1,
                ],
            ];
        } else {
            $opts = [
                'options' => [
                    'min_range' => 0,
                    'max_range' => 1,
                ],
            ];
        }
        $params[$x] = filter_var($params[$x], FILTER_VALIDATE_INT, $opts);
        if (!is_numeric($params[$x])) {
            stderr($lang['cleanup_unlock_error'], "{$lang['cleanup_unlock_error1']} $x");
        }
    }
    unset($opts);
    $params['cid'] = sqlesc($params['cid']);
    $params['clean_on'] = ($params['clean_on'] === 1 ? sqlesc($params['clean_on'] - 1) : sqlesc($params['clean_on'] + 1));
    sql_query("UPDATE cleanup SET clean_on = {$params['clean_on']} WHERE clean_id = {$params['cid']}");
    if (1 === mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        cleanup_show_main(); // this go bye bye later
    } else {
        stderr($lang['cleanup_unlock_error'], "{$lang['cleanup_unlock_error']}");
    }
    die();
}
