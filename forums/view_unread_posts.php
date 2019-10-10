<?php

declare(strict_types = 1);

use Pu239\Database;

$colour = $topicpoll = $topic_status_image = '';
$HTMLOUT .= $mini_menu . '<h1 class="has-text-centered">' . _('Unread posts since your last visit') . '</h1>';
$user = check_user_status();
global $container, $site_config;

$fluent = $container->get(Database::class);
$count = $fluent->from('read_posts')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->where('user_id = ?', $user['id'])
                ->fetch('count');
if ($count === 0) {
    require_once FORUM_DIR . 'mark_all_as_read.php';
    mark_as_unread($user);
}
$time = $site_config['forum_config']['readpost_expiry'];
$query = $fluent->from('topics AS t')
                ->select(null)
                ->select('t.id')
                ->select('t.first_post')
                ->select('t.last_post')
                ->select('IF (r.last_post_read IS NULL, t.first_post, r.last_post_read) AS last_post_read')
                ->leftJoin('posts AS p ON t.last_post = p.id')
                ->leftJoin('forums AS f ON t.forum_id = f.id')
                ->leftJoin('read_posts AS r ON t.id = r.topic_id');
if ($user['class'] < UC_STAFF) {
    $query = $query->where('p.status = ?', 'ok')
                   ->where('t.status = ?', 'ok');
} elseif ($user['class'] < $site_config['forum_config']['min_delete_view_class']) {
    $query = $query->where('p.status != ?', 'deleted')
                   ->where('t.status != ?', 'deleted');
}
$query = $query->where('f.min_class_read <= ?', $user['class'])
               ->where('p.added > ?', $time)
               ->where('(r.last_post_read IS NULL OR r.last_post_read < t.last_post)')
               ->where('r.user_id = ?', $user['id'])
               ->fetchAll();
$count = !empty($query) ? count($query) : 0;
if ($count === 0) {
    $heading = '
            <tr>
                <th>
                    ' . _('No unread posts') . '
                </th>
            </tr>';
    $body = '
            <tr>
                <td>
                    ' . _('You are up to date on all topics') . '.<br><br>
                </td>
            </tr>';
    $HTMLOUT .= main_table($body, $heading);
} else {
    $page = isset($_GET['page']) ? $_GET['page'] : 0;
    $perpage = isset($_GET['perpage']) ? $_GET['perpage'] : 20;
    $link = $site_config['paths']['baseurl'] . '/forums.php?action=view_unread_posts&amp;' . (isset($_GET['perpage']) ? "perpage={$perpage}&amp;" : '');
    $pager = pager($perpage, $count, $link);
    $menu_top = $count > $perpage ? $pager['pagertop'] : '';
    $menu_bottom = $count > $perpage ? $pager['pagerbottom'] : '';
    $unread = $fluent->from('topics AS t')
                     ->select('t.id AS topic_id')
                     ->select('t.topic_name AS topic_name')
                     ->select('t.anonymous AS tan')
                     ->select('f.*')
                     ->select('f.name AS forum_name')
                     ->select('f.description AS forum_desc')
                     ->select('p.*')
                     ->select('p.added AS post_added')
                     ->select('p.anonymous AS pan')
                     ->select('IF (r.last_post_read IS NULL, 1, r.last_post_read) AS last_post_read')
                     ->leftJoin('posts AS p ON t.last_post = p.id')
                     ->leftJoin('forums AS f ON f.id = t.forum_id')
                     ->leftJoin('read_posts AS r ON t.id = r.topic_id');
    if ($user['class'] < UC_STAFF) {
        $unread->where("p.status = 'ok'")
               ->where("t.status = 'ok'");
    } elseif ($user['class'] < $site_config['forum_config']['min_delete_view_class']) {
        $unread->where("p.status != 'deleted'")
               ->where("t.status != 'deleted'");
    }
    $unread = $unread->where('f.min_class_read <= ?', $user['class'])
                     ->where('p.added > ?', $time)
                     ->where('(r.last_post_read IS NULL OR r.last_post_read < t.last_post)')
                     ->where('r.user_id = ?', $user['id'])
                     ->orderBy('t.last_post DESC')
                     ->limit($pager['pdo']['limit'])
                     ->offset($pager['pdo']['offset'])
                     ->fetchAll();
    $HTMLOUT .= $menu_top;
    $heading = '
        <tr>
            <th><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic.gif" class="icon tooltipper" alt="' . _('Topic') . '" title="' . _('Topic') . '"></th>
            <th><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" class="icon tooltipper" alt=' . _('Thread Icon') . '" title=' . _('Thread Icon') . '"></th>
            <th>' . _('New Posts') . '!</th>
            <th>' . _('Replies') . '</th>
            <th>' . _('Views') . '</th>
            <th>' . _('Started By') . '</th>
        </tr>';
    $body = '';
    foreach ($unread as $arr_unread) {
        $topic_status = htmlsafechars((string) $arr_unread['status']);
        switch ($topic_status) {
            case 'ok':
                $topic_status_image = '';
                break;

            case 'recycled':
                $topic_status_image = '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/recycle_bin.gif" class="icon tooltipper" alt="' . _('Recycled') . '" title="' . _('This thread is currently') . ' ' . _('in the recycle-bin') . '">';
                break;

            case 'deleted':
                $topic_status_image = '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/delete_icon.gif" class="icon tooltipper" alt="' . _('Deleted') . '" title="' . _('This thread is currently') . ' ' . _('Deleted') . '">';
                break;
        }
        $locked = $arr_unread['locked'] === 'yes';
        $sticky = $arr_unread['sticky'] === 'yes';
        $topic_poll = $arr_unread['poll_id'] > 0;
        if ($arr_unread['tan'] === '1') {
            if ($user['class'] < UC_STAFF && $arr_unread['user_id'] != $user['id']) {
                $thread_starter = (!empty($arr_unread['user_id']) ? '<i>' . get_anonymous_name() . '</i>' : '' . _('Lost') . ' [' . $arr_unread['user_id'] . ']') . '<br>' . get_date($arr_unread['post_added'], '');
            } else {
                $thread_starter = (!empty($arr_unread['user_id']) ? '<i>' . get_anonymous_name() . '</i> [' . format_username($arr_unread['user_id']) . ']' : '' . _('Lost') . ' [' . $arr_unread['user_id'] . ']') . '<br>' . get_date($arr_unread['post_added'], '');
            }
        } else {
            $thread_starter = (!empty($arr_unread['user_id']) ? format_username($arr_unread['user_id']) : '' . _('Lost') . ' [' . $arr_unread['user_id'] . ']') . '<br>' . get_date($arr_unread['post_added'], '');
        }
        $topicpic = $arr_unread['post_count'] < 30 ? ($locked ? 'lockednew' : 'topicnew') : ($locked ? 'lockednew' : 'hot_topic_new');
        $rpic = $arr_unread['num_ratings'] != 0 ? ratingpic_forums(round($arr_unread['rating_sum'] / $arr_unread['num_ratings'], 1)) : '';
        $posted = $fluent->from('posts')
                         ->select(null)
                         ->select('COUNT(id) AS count')
                         ->where('user_id = ?', $user['id'])
                         ->where('topic_id = ?', $arr_unread['topic_id'])
                         ->fetch('count');

        $subscriptions = $fluent->from('subscriptions')
                                ->select(null)
                                ->select('COUNT(id) AS count')
                                ->where('user_id = ?', $user['id'])
                                ->where('topic_id = ?', $arr_unread['topic_id'])
                                ->fetch('count');

        $icon = empty($arr_unread['icon']) ? '
            <img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" class="icon tooltipper left5" alt="' . _('Topic') . '" title="' . _('Topic') . '">' : '
            <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . $arr_unread['icon'] . '.gif" class="icon tooltipper left5" alt="' . _('Unread') . '" title="' . _('Unread') . '">';
        $first_post_text = bubble("<i class='icon-search icon' aria-hidden='true'></i>", format_comment($arr_unread['body'], true, true, false), _('Last Post') . ' ' . _('Preview'));
        $topic_name = ($sticky ? '
            <img src="' . $site_config['paths']['images_baseurl'] . 'forums/pinned.gif" class="icon tooltipper left5" alt="' . _('Pinned') . '" title="' . _('Pinned') . '">' : '') . ($topicpoll ? '
            <img src="' . $site_config['paths']['images_baseurl'] . 'forums/poll.gif" class="icon tooltipper left5" alt="' . _('Poll') . '" title="' . _('Poll') . '">' : '') . '
            <a class="is-link tooltipper left5" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $arr_unread['topic_id'] . '" title="' . _('First post in thread') . '">' . format_comment($arr_unread['topic_name']) . '</a>
            <a class="is-link tooltipper left5" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $arr_unread['topic_id'] . '&amp;page=0#' . $arr_unread['last_post_read'] . '" title="' . _('First unread post in this thread') . '">
                <img src="' . $site_config['paths']['images_baseurl'] . 'forums/last_post.gif" class="icon" alt="' . _('Last Post') . '">
            </a>' . ($posted > 0 ? '
            <img src="' . $site_config['paths']['images_baseurl'] . 'forums/posted.gif" class="icon tooltipper left5" alt="Posted" title="Posted">' : '') . ($subscriptions > 0 ? '
            <img src="' . $site_config['paths']['images_baseurl'] . 'forums/subscriptions.gif" class="icon tooltipper left5" alt="' . _('Subscribed') . '" title="' . _('Subscribed') . '">' : '') . '
            <img src="' . $site_config['paths']['images_baseurl'] . 'forums/new.gif" class="icon tooltipper left5" alt="' . _('New post in topic') . '!" title="' . _('New post in topic') . '!">';
        $body .= '
            <tr>
                <td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/' . $topicpic . '.gif" class="icon tooltipper" alt="' . _('Topic') . '" title="' . _('Topic') . '"></td>
                <td>' . $icon . '</td>
                <td>
                    <table>
                        <tr>
                            <td>
                                <div class="level-left">' . $topic_name . $first_post_text . '<a class="is-link tooltipper left5" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=clear_unread_post&amp;topic_id=' . $arr_unread['topic_id'] . '&amp;last_post=' . $arr_unread['last_post'] . '" title="' . _('Remove') . ' ' . _('this topic from your unread list. To remove all, use the: Mark All As Read link above') . '.">' . "<i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i></a>" . $topic_status_image . '</div></td>
                            <td>' . $rpic . '</td>
                        </tr>
                    </table>
                    ' . (!empty($arr_unread['topic_desc']) ? '&#9658; <span style="font-size: x-small;">' . format_comment($arr_unread['topic_desc']) . '</span>' : '') . '
                    <hr>in: <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $arr_unread['forum_id'] . '">' . format_comment($arr_unread['forum_name']) . '</a>
                    ' . (!empty($arr_unread['topic_desc']) ? ' [ <span style="font-size: x-small;">' . format_comment($arr_unread['topic_desc']) . '</span> ]' : '') . '
                </td>
                <td>' . number_format($arr_unread['post_count']) . '</td>
                <td>' . number_format($arr_unread['views']) . '</td>
                <td>' . $thread_starter . '</td>
            </tr>';
    }
    $HTMLOUT .= main_table($body, $heading) . $menu_bottom;
}
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/forums.php'>" . _('Forums') . '</a>',
    "<a href='{$site_config['paths']['baseurl']}/forums.php?action=view_unread_posts'>" . _('Unread Posts') . '</a>',
];
