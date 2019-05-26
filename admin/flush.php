<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_flush'));
global $site_config, $CURUSER;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!is_valid_id($id)) {
    stderr($lang['flush_stderror'], $lang['flush_invalid']);
}
if ($CURUSER['class'] >= UC_STAFF) {
    $dt = TIME_NOW;
    $res = sql_query('SELECT username FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    $username = htmlsafechars($arr['username']);
    sql_query('DELETE FROM peers WHERE userid = ' . sqlesc($id));
    $effected = mysqli_affected_rows($mysqli);
    write_log($lang['flush_log1'] . $username . $lang['flush_log2'] . get_date((int) $dt, 'LONG', 0, 1) . $lang['flush_log3'] . (int) $effected . $lang['flush_log4']);
    header('Refresh: 3; url=index.php');
    stderr($lang['flush_success'], "$effected {$lang['flush_success2']}" . ($effected ? 's' : '') . $lang['flush_success3']);
} else {
    stderr($lang['flush_fail'], $lang['flush_fail2']);
}
