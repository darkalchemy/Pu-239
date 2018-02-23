<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER;

$lang = load_language('global');
/** got code help from system =] **/
$where = '';
if ($CURUSER['notifs']) {
    $parts = preg_split('`[\[\]]`', $CURUSER['notifs'], -1, PREG_SPLIT_NO_EMPTY);
    $cats  = [
        998,
        999,
    ]; // junk data
    foreach ($parts as $x) {
        if ('cat' === substr($x, 0, 3)) {
            $cats[] = substr($x, 3);
        }
    }
    $where = (2 === count($cats)) ? '' : 'WHERE category IN(' . join(',', $cats) . ') AND visible=\'yes\'';
}
/* end **/
// possible to shuffle torrents within specific category, overides previous $where
if (isset($_GET['cat'])) {
    $cat   = (int) $_GET['cat'];
    $where = 'WHERE category IN (' . $cat . ') AND visible="yes"';
}
$cat_id = (isset($cat) ? '&cat=' . $cat : '');
$res    = sql_query('SELECT id FROM torrents ' . $where . ' ORDER BY RAND() LIMIT 1'); //dunno if adding LIMIT here would help any since dies after 1st row
while (list($id) = mysqli_fetch_array($res)) {
    if (null != $id) {
        header('Location: details.php?id=' . $id . $cat_id . '&random'); //add &random to indicate on details.php random browsing
        die();
    }
}
