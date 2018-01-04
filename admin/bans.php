<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'html_functions.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $cache, $lang, $pdo, $fluent;

$lang = array_merge($lang, load_language('ad_bans'));
$remove = isset($_GET['remove']) ? (int)$_GET['remove'] : 0;
if ($remove > 0) {
    $res = $fluent->from('bans')
        ->select(null)
        ->select('INET6_NTOA(first) AS first')
        ->select('INET6_NTOA(last) AS last')
        ->where('id = ?', $remove)
        ->fetch();

    if (!$res) {
        stderr($lang['stderr_error'], $lang['stderr_error1']);
    }
    for ($i = $res['first']; $i <= $res['last']; ++$i) {
        $cache->delete('bans_' . $i);
    }
    if (is_valid_id($remove)) {
        $fluent->deleteFrom('bans')
            ->where('id = ?', $remove)
            ->execute();
        $removed = sprintf($lang['text_banremoved'], $remove);
        write_log("{$removed}" . $CURUSER['id'] . ' (' . $CURUSER['username'] . ')');
        setSessionVar('is-success', "IPS: {$res['first']} to {$res['last']} removed");
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
        $cache->delete('bans_' . $i);
    }

    $values = [
        'added'   => $added,
        'addedby' => $CURUSER['id'],
        'first'   => $first,
        'last'    => $last,
        'comment' => $comment,
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO bans
                        (added, addedby, first, last, comment)
                      VALUES
                        (:added, :addedby, INET6_ATON(:first), INET6_ATON(:last), :comment)'
    );
    $stmt->execute($values);
    setSessionVar('is-success', "IPs: $first to $last added to Bans");
    unset($_POST);
}

$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=bans&amp;');
$res = $fluent->from('bans')
    ->select('INET6_NTOA(first) AS first')
    ->select('INET6_NTOA(last) AS last')
    ->orderBy('added DESC');

foreach ($res as $arr) {
    $bans[] = $arr;
}
$count = count($bans);
$HTMLOUT = "
        <h1 class='has-text-centered'>Bans</h1>
        <div class='top20 bg-00 round10'>
            <div class='padding20'>
                <h2>{$lang['text_current']}</h2>
            </div>";
if ($count == 0) {
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
    foreach ($bans as $banned) {
        $body .= '
                <tr>
                    <td>' . get_date($banned['added'], '') . "</td>
                    <td>" . htmlsafechars($banned['first']) . "</td>
                    <td>" . htmlsafechars($banned['last']) . "</td>
                    <td><a href='" . $site_config['baseurl'] . "/userdetails.php?id=" . $banned['addedby'] . "'>" . format_username($banned['addedby']) . "</a></td>
                    <td>" . htmlsafechars($banned['comment'], ENT_QUOTES) . "</td>
                    <td><a href='" . $site_config['baseurl'] . "/staffpanel.php?tool=bans&amp;remove=" . $banned['id'] . "'>{$lang['text_remove']}</a></td>
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
