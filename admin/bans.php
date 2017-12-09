<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'html_functions.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $cache, $lang;

$lang = array_merge($lang, load_language('ad_bans'));
$remove = isset($_GET['remove']) ? (int)$_GET['remove'] : 0;
if ($remove > 0) {
    $banned = sql_query('SELECT first, last FROM bans WHERE id = ' . sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
    if (!mysqli_num_rows($banned)) {
        stderr($lang['stderr_error'], $lang['stderr_error1']);
    }
    $ban = mysqli_fetch_assoc($banned);
    $first = ipFromStorageFormat($ban['first']);
    $last = ipFromStorageFormat($ban['last']);
    for ($i = $first; $i <= $last; ++$i) {
        $ip = long2ip($i);
        $cache->delete('bans:::' . $ip);
    }
    if (is_valid_id($remove)) {
        sql_query('DELETE FROM bans WHERE id=' . sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
        $removed = sprintf($lang['text_banremoved'], $remove);
        write_log("{$removed}" . $CURUSER['id'] . ' (' . $CURUSER['username'] . ')');
        setSessionVar('is-success', "IPS: $first to $last removed");
        unset($_GET);
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $CURUSER['class'] == UC_MAX) {
    $first = trim($_POST['first']);
    $last = trim($_POST['last']);
    $comment = htmlsafechars(trim($_POST['comment']));
    if (!$first || !$last || !$comment) {
        stderr("{$lang['stderr_error']}", "{$lang['text_missing']}");
    }
    $test_first = ip2long($first);
    $test_last = ip2long($last);
    if ($test_first == -1 || $test_first === false || $test_last == -1 || $test_last === false) {
        stderr("{$lang['stderr_error']}", "{$lang['text_badip']}");
    }
    $added = TIME_NOW;
    for ($i = $first; $i <= $last; ++$i) {
        $key = 'bans:::' . long2ip($i);
        $cache->delete($key);
    }
    sql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES($added, " . sqlesc($CURUSER['id']) . ', ' . ipToStorageFormat($first) . ', ' . ipToStorageFormat($last) . ', ' . sqlesc($comment) . ')') or sqlerr(__FILE__, __LINE__);
    setSessionVar('is-success', "IPs: $first to $last added to Bans");
    unset($_POST);
}
$bc = sql_query('SELECT COUNT(*) FROM bans') or sqlerr(__FILE__, __LINE__);
$bcount = mysqli_fetch_row($bc);
$count = $bcount[0];
$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=bans&amp;');
$res = sql_query("SELECT b.*, u.username FROM bans b LEFT JOIN users u on b.addedby = u.id ORDER BY added DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
$HTMLOUT = '';
$HTMLOUT .= "
        <div class='margin20'>
            <h1>Bans</h1>
        </div>
        <div class='top20 bg-00 round10'>
            <div class='padding20'>
                <h2>{$lang['text_current']}</h2>
            </div>";
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= main_div("<p><b>{$lang['text_nothing']}</b></p>");
} else {
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $header = "
                <tr>
                    <th>{$lang['header_added']}</th>
                    <th>{$lang['header_firstip']}</th>
                    <th>{$lang['header_lastip']}</th>
                    <th>{$lang['header_by']}</th>
                    <th>{$lang['header_comment']}</th>
                    <th>{$lang['header_remove']}</th>
                </tr>";
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $body .= '
                <tr>
                    <td>' . get_date($arr['added'], '') . "</td>
                    <td>" . htmlsafechars(ipFromStorageFormat($arr['first'])) . "</td>
                    <td>" . htmlsafechars(ipFromStorageFormat($arr['last'])) . "</td>
                    <td><a href='userdetails.php?id=" . (int)$arr['addedby'] . "'>" . htmlsafechars($arr['username']) . "</a></td>
                    <td>" . htmlsafechars($arr['comment'], ENT_QUOTES) . "</td>
                    <td><a href='staffpanel.php?tool=bans&amp;remove=" . (int)$arr['id'] . "'>{$lang['text_remove']}</a></td>
               </tr>";
    }
    $HTMLOUT .= main_table($body, $header);
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
}
$HTMLOUT .= "
        </div>";
if ($CURUSER['class'] == UC_MAX) {
    $HTMLOUT .= "
        <div class='top20 bg-00 round10'>
            <div class='padding20'>
                <h2>{$lang['text_addban']}</h2>
            </div>
            <form method='post' action='staffpanel.php?tool=bans'>";
    $HTMLOUT .= main_table("
                <tr>
                    <td class='rowhead'>{$lang['table_firstip']}</td>
                    <td><input type='text' name='first' class='w-100' /></td>
                </tr>
                <tr>
                    <td class='rowhead'>{$lang['table_lastip']}</td>
                    <td><input type='text' name='last' class='w-100' /></td>
                </tr>
                <tr>
                    <td class='rowhead'>{$lang['table_comment']}</td><td><input type='text' name='comment' class='w-100' /></td>
                </tr>");
    $HTMLOUT .= "
                <div class='has-text-centered padding20'>
                    <input type='submit' name='okay' value='{$lang['btn_add']}' class='button is-small' />
                </div>
            </form>
        </div>";
}
echo stdhead("{$lang['stdhead_adduser']}") . wrapper($HTMLOUT) . stdfoot();
