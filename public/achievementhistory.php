<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$HTMLOUT = '';
$id = (int) $_GET['id'];
if (!is_valid_id($id)) {
    stderr(_('Error'), _('It appears that you have entered an invalid id.'));
}
$res = sql_query('SELECT u.id, u.username, a.achpoints, a.spentpoints FROM users AS u LEFT JOIN usersachiev AS a ON u.id = a.userid WHERE u.id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (!$arr) {
    stderr(_('Error'), _('It appears that you have entered an invalid id.'));
}
$achpoints = (int) $arr['achpoints'];
$spentpoints = (int) $arr['spentpoints'];
$res = sql_query('SELECT COUNT(id) FROM achievements WHERE userid =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = (int) $row[0];
$perpage = 15;
$pager = pager($perpage, $count, "?id=$id&amp;");
global $site_config;

if ($id === $user['id']) {
    $HTMLOUT .= "
    <div class='w-100'>
        <ul class='level-center padding20 bg-06'>
            <li>
                <a href='{$site_config['paths']['baseurl']}/achievementlist.php'>" . _('Achievements List') . "</a>
            </li>
            <li>
                <a href='{$site_config['paths']['baseurl']}/postcounter.php'>" . _('Update Forum Post Counter') . "</a>
            </li>
            <li>
                <a href='{$site_config['paths']['baseurl']}/topiccounter.php'>" . _('Update Forum Topic Counter') . "</a>
            </li>
            <li>
                <a href='{$site_config['paths']['baseurl']}/invitecounter.php'>" . _('Update Invite Counter') . '</a>
            </li>
        </ul>
    </div>';
}

$HTMLOUT .= "
    <div class='has-text-centered'>
        <h1 class='level-item'>" . _('Achievements for') . ':&nbsp;' . format_username((int) $arr['id']) . '</h1>
        <h2>' . _pfe('Currently {0, number} achievment.', 'Currently {0, number} achievments.', (int) $row['0']);
if ($id === $user['id']) {
    $HTMLOUT .= " <a class='is-link' href='{$site_config['paths']['baseurl']}/achievementbonus.php'> " . _pfe('{0} Point Available', '{0} Points Available', $achpoints) . ' // ' . _pfe('{0} Point spent', '{0} Points spent', $spentpoints) . '</a>';
}
$HTMLOUT .= '</h2>
    </div>';
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if ($count === 0) {
    $HTMLOUT .= stdmsg(_('No Achievements'), _fe('It appears that {0} currently has no achievements.', format_username((int) $arr['id'])));
} else {
    $heading = '
                    <tr>
                        <th>' . _('Award') . '</th>
                        <th>' . _('Description') . '</th>
                        <th>' . _('Date Earned') . '</th>
                    </tr>';
    $res = sql_query('SELECT * FROM achievements WHERE userid=' . sqlesc($id) . " ORDER BY date DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $body .= "
                    <tr>
                        <td class='has-text-centered'>
                            <img src='{$site_config['paths']['images_baseurl']}achievements/" . htmlsafechars($arr['icon']) . "' alt='" . htmlsafechars($arr['achievement']) . "' class='tooltipper icon' title='" . htmlsafechars($arr['achievement']) . "'>
                        </td>
                        <td>" . htmlsafechars($arr['description']) . '</td>
                        <td>' . get_date((int) $arr['date'], '') . '</td>
                    </tr>';
    }
    $HTMLOUT .= main_table($body, $heading) . '
        </div>';
}
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$title = _('Achievement History');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
