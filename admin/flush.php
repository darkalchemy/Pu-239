<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $CURUSER;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!is_valid_id($id)) {
    stderr(_('Error'), _('Invalid ID'));
}
if ($CURUSER['class'] >= UC_STAFF) {
    $dt = TIME_NOW;
    $res = sql_query('SELECT username FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    $username = htmlsafechars($arr['username']);
    sql_query('DELETE FROM peers WHERE userid = ' . sqlesc($id));
    $effected = mysqli_affected_rows($mysqli);
    write_log(_fe("Staff flushed {0}'s ghost torrents at {1}. {2} torrents where sucessfully cleaned.", $username, get_date((int) $dt, 'LONG', 0, 1), $effected));
    header('Refresh: 3; url=index.php');
    stderr(_('Success'), _p('%s ghost torrent was sucessfully cleaned. You may now restart your torrents, the tracker has been updated.', '%s ghost torrents were sucessfully cleaned. You may now restart your torrents, the tracker has been updated.', $effected));
} else {
    stderr(_('Error'), _('You are not a member of the staff.'));
}
