<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $CURUSER, $site_config;

$lang = array_merge(load_language('global'), load_language('achievement_history'));
$HTMLOUT = '';
$id = (int) $_GET['id'];
if (!is_valid_id($id)) {
    stderr($lang['achievement_history_err'], $lang['achievement_history_err1']);
}
$res = sql_query('SELECT u.id, u.username, a.achpoints, a.spentpoints FROM users AS u LEFT JOIN usersachiev AS a ON u.id=a.userid WHERE u.id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (!$arr) {
    stderr($lang['achievement_history_err'], $lang['achievement_history_err1']);
}
$achpoints = (int) $arr['achpoints'];
$spentpoints = (int) $arr['spentpoints'];
$res = sql_query('SELECT COUNT(*) FROM achievements WHERE userid =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row[0];
$perpage = 15;
if (!$count) {
    stderr($lang['achievement_history_no'], "{$lang['achievement_history_err2']} " . format_username($arr['id']) . " {$lang['achievement_history_err3']}");
}
$pager = pager($perpage, $count, "?id=$id&amp;");
if ($id === $CURUSER['id']) {
    $HTMLOUT .= "
    <div class='w-100'>
        <ul class='level-center padding20 bg-06'>
            <li>
                <a href='{$site_config['paths']['baseurl']}/achievementlist.php'>{$lang['achievement_history_al']}</a>
            </li>
            <li>
                <a href='{$site_config['paths']['baseurl']}/postcounter.php'>{$lang['achievement_history_fpc']}</a>
            </li>
            <li>
                <a href='{$site_config['paths']['baseurl']}/topiccounter.php'>{$lang['achievement_history_ftc']}</a>
            </li>
            <li>
                <a href='{$site_config['paths']['baseurl']}/invitecounter.php'>{$lang['achievement_history_ic']}</a>
            </li>
        </ul>
    </div>";
}
$HTMLOUT .= "
    <div class='has-text-centered'>
        <h1 class='level-item'>{$lang['achievement_history_afu']}&nbsp;" . format_username($arr['id']) . "</h1>
        <h2>{$lang['achievement_history_c']}" . htmlsafechars($row['0']) . $lang['achievement_history_a'] . ($row[0] == 1 ? '' : 's') . '.';
if ($id === $CURUSER['id']) {
    $HTMLOUT .= " <a class='altlink' href='{$site_config['paths']['baseurl']}/achievementbonus.php'>" . htmlsafechars($achpoints) . "{$lang['achievement_history_pa']}" . htmlsafechars($spentpoints) . "{$lang['achievement_history_ps']}</a>";
}
$HTMLOUT .= '</h2>
    </div>';
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$heading = "
                    <tr>
                        <th>{$lang['achievement_history_award']}</th>
                        <th>{$lang['achievement_history_descr']}</th>
                        <th>{$lang['achievement_history_date']}</th>
                    </tr>";
$res = sql_query('SELECT * FROM achievements WHERE userid=' . sqlesc($id) . " ORDER BY date DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
$body = '';
while ($arr = mysqli_fetch_assoc($res)) {
    $body .= "
                    <tr>
                        <td class='has-text-centered'><img src='{$site_config['paths']['images_baseurl']}achievements/" . htmlsafechars($arr['icon']) . "' alt='" . htmlsafechars($arr['achievement']) . "' class='tooltipper icon' title='" . htmlsafechars($arr['achievement']) . "'></td>
                        <td>" . htmlsafechars($arr['description']) . '</td>
                        <td>' . get_date($arr['date'], '') . '</td>
                    </tr>';
}
$HTMLOUT .= main_table($body, $heading) . '
        </div>';
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['achievement_history_stdhead']) . wrapper($HTMLOUT) . stdfoot();
