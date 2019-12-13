<?php

declare(strict_types = 1);

use Pu239\Achievement;
use Pu239\Post;
use Pu239\Topic;
use Pu239\User;
use Pu239\Usersachiev;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$id = isset($_GET['id']) ? (int) $_GET['id'] : $user['id'];
if (!is_valid_id($id)) {
    stderr(_('Error'), _('Invalid ID'));
}
$usersachiev = $container->get(Usersachiev::class);
$arr = $usersachiev->get_points($id);
if (!$arr) {
    stderr(_('Error'), _('Invalid ID'));
}
$post = $container->get(Post::class);
$posts = $post->get_user_count($id);
$topic = $container->get(Topic::class);
$topics = $topic->get_user_count($id);
$users_class = $container->get(User::class);
$invited = $users_class->get_count('invitedby', (string) $id);
$update = [
    'forumposts' => $posts,
    'forumtopics' => $topics,
    'invited' => $invited,
];
$usersachiev->update($update, $id);
$achievement = $container->get(Achievement::class);
$count = (int) $achievement->get_achievements_count($id);
$perpage = 15;
$pager = pager($perpage, $count, "?id=$id&amp;");
global $site_config;

$HTMLOUT = "
    <div class='w-100'>
        <ul class='level-center bg-06'>
            <li class='is-link margin10'>
                <a href='{$site_config['paths']['baseurl']}/achievementlist.php'>" . _('Achievements List') . '</a>
            </li>
        </ul>
    </div>';

$HTMLOUT .= "
    <div class='has-text-centered'>
        <h1 class='level-item'>" . _('Achievements for') . ':&nbsp;' . format_username($id) . '</h1>
        <ul class="level-center-center bottom20 size_5">
            <li class="right10">' . _pfe('{0} achievement earned.', '{0} achievements earned.', $count) . '</li>
            <li class="left10 right10">' . _pfe('{0} Point spent.', '{0} Points spent.', $arr['spentpoints']) . '</li>
            <li class="left10">' . _pfe('{0} Point Available.', '{0} Points Available.', $arr['achpoints']) . '</li>
        </ul>';
if ($id === $user['id'] && $arr['achpoints'] > 0) {
    $HTMLOUT .= "
        <div>
            <a href='{$site_config['paths']['baseurl']}/achievementbonus.php' class='button is-small bottom20 tooltipper' title='" . _('Trade your achievement points for random gifts.') . "'>" . _('Spend those Points') . '</a>
        </div>';
}
$HTMLOUT .= '
    </div>';
$HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';

if ($count === 0) {
    stderr(_('No Achievements'), _fe('It appears that {0} currently has no achievements.', format_username($id)));
} else {
    $heading = '
                    <tr>
                        <th>' . _('Award') . '</th>
                        <th>' . _('Description') . '</th>
                        <th>' . _('Date Earned') . '</th>
                    </tr>';
    $body = '';
    $res = $achievement->get_achievements($id, $pager['pdo']['limit'], $pager['pdo']['offset']);
    foreach ($res as $arr) {
        $body .= "
                    <tr>
                        <td class='has-text-centered'>
                            <img src='{$site_config['paths']['images_baseurl']}achievements/" . format_comment($arr['icon']) . "' alt='" . format_comment($arr['achievement']) . "' class='tooltipper icon' title='" . format_comment($arr['achievement']) . "'>
                        </td>
                        <td>" . format_comment($arr['description']) . '</td>
                        <td>' . get_date((int) $arr['date'], '') . '</td>
                    </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);
}
$HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
$title = _('Achievement History');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
