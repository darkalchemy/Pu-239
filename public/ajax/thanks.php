<?php

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
global $site_config, $cache, $session, $mysqli;

if (empty($_POST)) {
    $session->set('is-danger', 'Access Not Allowed');
    header("Location: {$site_config['paths']['baseurl']}/index.php");
    die();
}

if (!isset($CURUSER)) {
    $session->set('is-warning', "You can't add a thank you on your own torrent");
    header("Location: {$site_config['paths']['baseurl']}/index.php");
    die();
}

if (empty($_POST['csrf']) || !$session->validateToken($_POST['csrf'])) {
    echo 'CSRF Verification failed.';
    die();
}

$uid = (int) $CURUSER['id'];
$tid = isset($_POST['tid']) ? (int) $_POST['tid'] : (isset($_GET['tid']) ? (int) $_GET['tid'] : 0);
$do = isset($_POST['action']) ? htmlsafechars($_POST['action']) : (isset($_GET['action']) ? htmlsafechars($_GET['action']) : 'list');
$ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1 ? true : false;

/**
 * @return string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function print_list()
{
    global $uid, $tid, $ajax, $site_config;

    $target = $ajax ? '_self' : '_parent';
    $qt = sql_query('SELECT th.userid, u.username, u.seedbonus FROM thanks AS th INNER JOIN users AS u ON u.id=th.userid WHERE th.torrentid=' . sqlesc($tid) . ' ORDER BY u.class DESC') or sqlerr(__FILE__, __LINE__);
    $list = [];
    $hadTh = false;
    if (mysqli_num_rows($qt) > 0) {
        while ($a = mysqli_fetch_assoc($qt)) {
            $list[] = format_username($a['userid']);
            $ids[] = (int) $a['userid'];
        }
        $hadTh = in_array($uid, $ids) ? true : false;
    }
    if ($ajax) {
        return json_encode([
            'list' => (count($list) > 0 ? implode(', ', $list) : ''),
            'hadTh' => $hadTh,
            'status' => true,
        ]);
    } else {
        $form = !$hadTh ? "<span class='left10'><form action='{$site_config['paths']['baseurl']}/ajax/thanks.php' method='post'><input type='submit' class='button is-small' name='submit' value='Say thanks'><input type='hidden' name='torrentid' value='{$tid}'><input type='hidden' name='action' value='add'></form></span accept-charset='utf-8'>" : '';
        $out = (count($list) > 0 ? implode(', ', $list) : '');

        return <<<IFRAME
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
<style>
body { margin:0;padding:0; 
       font-size:12px;
       font-family:arial,sans-serif;
       color: #fff;
}
a, a:link, a:visited {
  text-decoration: none;
  color: #fff;
  font-size:12px;
}
a:hover {
  color: #fff
  text-decoration:underline;
  
}
.btn {
background-color:#890537;
border:1px solid #000000;
color:#fff;
font-family:arial,sans-serif;
font-size:12px;
padding:1px 3px;
}
</style>
<title>::</title>
</head>
<body>
{$out}{$form}
</body>
</html>
IFRAME;
    }
}

switch ($do) {
    case 'list':
        print print_list();
        break;

    case 'add':
        if ($uid > 0 && $tid > 0) {
            $c = 'SELECT count(id) FROM thanks WHERE userid=' . sqlesc($uid) . ' AND torrentid=' . sqlesc($tid);
            $result = sql_query($c);
            $arr = $result->fetch_row();
            if ($arr[0] == 0) {
                if (sql_query('INSERT INTO thanks(userid,torrentid) VALUES(' . sqlesc($uid) . ',' . sqlesc($tid) . ')')) {
                    echo print_list();
                } else {
                    $msg = 'There was an error with the query,contact the staff. Mysql error ' . ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
                    echo $ajax ? json_encode([
                        'status' => false,
                        'err' => $msg,
                    ]) : $msg;
                }
            }
        }
        if ($site_config['bonus']['on']) {
            sql_query('UPDATE users SET seedbonus = seedbonus + ' . sqlesc($site_config['bonus']['per_thanks']) . ' WHERE id =' . sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
            $sql = sql_query('SELECT seedbonus FROM users WHERE id=' . sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
            $User = mysqli_fetch_assoc($sql);
            $update['seedbonus'] = ($User['seedbonus'] + $site_config['bonus']['per_thanks']);
            $cache->update_row('user_' . $uid, [
                'seedbonus' => $update['seedbonus'],
            ], $site_config['expires']['user_cache']);
        }
        break;
}
