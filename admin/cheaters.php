<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $cache;

$lang = array_merge($lang, load_language('cheaters'));
$stdfoot = [
    'js' => [
        get_file_name('cheaters_js'),
    ],
];

if (isset($_POST['nowarned']) && $_POST['nowarned'] === 'nowarned') {
    if (empty($_POST['desact']) && empty($_POST['remove'])) {
        stderr($lang['cheaters_err'], $lang['cheaters_seluser']);
    }
    if (!empty($_POST['remove'])) {
        sql_query('DELETE FROM cheaters WHERE id IN (' . implode(', ', array_map('sqlesc', $_POST['remove'])) . ')') or sqlerr(__FILE__, __LINE__);
    }
    if (!empty($_POST['desact'])) {
        sql_query("UPDATE users SET enabled = 'no' WHERE id IN (" . implode(', ', array_map('sqlesc', $_POST['desact'])) . ')') or sqlerr(__FILE__, __LINE__);
        $this->cache->deleteMulti($_POST['desact']);
    }
}
$res = sql_query('SELECT COUNT(*) FROM cheaters') or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_array($res);
$count = $row[0];
$perpage = 15;
$HTMLOUT = "<h1 class='has-text-centered'>Possible Cheaters</h1>";
if ($count > 0) {
    $pager = pager($perpage, $count, $site_config['baseurl'] . '/staffpanel.php?tool=cheaters&amp;action=cheaters&amp;');
    $HTMLOUT .= "
    <form action='{$site_config['baseurl']}/staffpanel.php?tool=cheaters&amp;action=cheaters' method='post' accept-charset='utf-8'>";
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $heading = "
        <tr>
            <th class='w-1 has-text-centered'>#</th>
            <th>{$lang['cheaters_uname']}</th>
            <th class='w-1 has-text-centered'>{$lang['cheaters_d']}</th>
            <th class='w-1 has-text-centered'>{$lang['cheaters_r']}</th>
        </tr>";
    $res = sql_query('SELECT c.id AS cid, c.added, c.userid, c.torrentid, c.client, c.rate, c.beforeup, c.upthis, c.timediff, c.userip, t.id AS tid, t.name AS tname FROM cheaters AS c LEFT JOIN torrents AS t ON t.id = c.torrentid ORDER BY added DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $id = $arr['cid'];
        $userid = $arr['userid'];
        $torrname = htmlsafechars(CutName($arr['tname'], 80));
        $cheater = format_username($userid) . " {$lang['cheaters_hbcc']}<br>
        {$lang['cheaters_torrent']} <a href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['tid'] . "' title='{$torrname}'>{$torrname}</a><br>
        {$lang['cheaters_upped']} " . mksize((int) $arr['upthis']) . "<br>
        {$lang['cheaters_speed']} " . mksize((int) $arr['rate']) . "/s<br>
        {$lang['cheaters_within']} " . (int) $arr['timediff'] . " {$lang['cheaters_sec']}<br>
        {$lang['cheaters_uc']} " . htmlsafechars($arr['client']) . "<br>
        {$lang['cheaters_ipa']} " . htmlsafechars($arr['userip']);

        $cheaters = "
        <span class='dt-tooltipper-large' data-tooltip-content='#cheater_{$id}_tooltip'>" . format_username($userid, true, false) . "
            <div class='tooltip_templates'>
                <div id='cheater_{$id}_tooltip'>$cheater</div>
            </div>
        </span>";

        $body .= "
        <tr>
            <td class='has-text-centered'>{$arr['cid']}</td>
            <td>$cheaters</td>
            <td class='has-text-centered'><input type='checkbox' name='desact[]' value='{$userid}'></td>
            <td class='has-text-centered'><input type='checkbox' name='remove[]' value='{$arr['cid']}'></td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading);
    $HTMLOUT .= "
        <div class='has-text-centered margin20'>
            <input type='button' value='{$lang['cheaters_cad']}' onclick=\"this.value=check1(this.form.elements['desact[]'])\" class='button is-small'>
            <input type='button' value='{$lang['cheaters_car']}' onclick=\"this.value=check2(this.form.elements['remove[]'])\" class='button is-small'>
            <input type='hidden' name='nowarned' value='nowarned'>
            <input type='submit' name='submit' value='{$lang['cheaters_ac']}' class='button is-small'>
        </div>
    </form>";
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
} else {
    $HTMLOUT .= stderr('', 'There are not any cheaters');
}
echo stdhead($lang['cheaters_stdhead']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
die();
