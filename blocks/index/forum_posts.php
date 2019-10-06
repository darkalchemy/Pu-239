<?php

declare(strict_types = 1);

$forum_posts .= "
    <a id='latestforum-hash'></a>
    <div id='latestforum' class='box'>
        <div class='grid-wrapper'>
        <div class='table-wrapper has-text-centered'>";
$page = 1;
$num = 0;

use Pu239\Cache;

global $container, $site_config, $CURUSER;

$cache = $container->get(Cache::class);
$topics = $cache->get('last_posts_' . $CURUSER['class']);
if ($topics === false || is_null($topics)) {
    $topicres = sql_query('SELECT t.id, t.user_id AS tuser_id, t.topic_name, t.locked, t.forum_id, t.last_post, t.sticky, t.views, t.anonymous AS tan,
                            f.min_class_read, f.name,
                            (SELECT COUNT(id) FROM posts WHERE topic_id = t.id) AS p_count, p.user_id AS puser_id, p.added, p.anonymous AS pan
                            FROM topics AS t
                            INNER JOIN forums AS f ON f.id = t.forum_id
                            INNER JOIN posts AS p ON p.id = (SELECT MAX(id) FROM posts WHERE topic_id = t.id)
                            WHERE f.min_class_read <= ' . $CURUSER['class'] . "
                            ORDER BY t.last_post DESC
                            LIMIT {$site_config['latest']['posts_limit']}") or sqlerr(__FILE__, __LINE__);
    while ($topic = mysqli_fetch_assoc($topicres)) {
        $topics[] = $topic;
    }
    if (!empty($topics)) {
        $cache->set('last_posts_' . $CURUSER['class'], $topics, $site_config['expires']['latestposts']);
    } else {
        $cache->set('last_posts_' . $CURUSER['class'], 'empty', $site_config['expires']['latestposts']);
    }
}
$forum_posts .= "
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th class='w-50 min-350'>" . _('Latest Posts') . "</th>
                        <th class='w-1 has-text-centered has-no-border-right has-no-border-left'>" . _('Replies') . "</th>
                        <th class='w-1 has-text-centered has-no-border-right has-no-border-left'>" . _('Views') . "</th>
                        <th class='w-1 has-text-centered has-no-border-left'>" . _('Last Post') . '</th>
                    </tr>
                </thead>
                <tbody>';
if (!empty($topics) && is_array($topics)) {
    foreach ($topics as $topicarr) {
        $topicid = (int) $topicarr['id'];
        $perpage = (int) $CURUSER['postsperpage'];

        if (!$perpage) {
            $perpage = 24;
        }
        $posts = (int) $topicarr['p_count'];
        $replies = max(0, $posts - 1);
        $first = ($page * $perpage) - $perpage + 1;
        $last = $first + $perpage - 1;
        if ($last > $num) {
            $last = $num;
        }
        $pages = ceil($posts / $perpage);
        $menu = '';
        for ($i = 1; $i <= $pages; ++$i) {
            if ($i == 1 && $i != $pages) {
                $menu .= '[ ';
            }
            if ($pages > 1) {
                $menu .= "<a href='{$site_config['paths']['baseurl']}/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=$i'>$i</a>\n";
            }
            if ($i < $pages) {
                $menu .= "|\n";
            }
            if ($i == $pages && $i > 1) {
                $menu .= ']';
            }
        }
        $added = get_date((int) $topicarr['added'], '', 0, 1);
        if ($topicarr['pan'] === '1') {
            if ($CURUSER['class'] < UC_STAFF && $topicarr['tuser_id'] != $CURUSER['id']) {
                $username = (!empty($topicarr['puser_id']) ? '<i>' . _('Anonymous') . '</i>' : '<i>' . _('Unknown') . '</i>');
            } else {
                $username = (!empty($topicarr['puser_id']) ? '<i>' . _('Anonymous') . '</i>[ ' . format_username((int) $topicarr['puser_id']) . ' ]' : '<i>' . _('Unknown') . " [{$topicarr['tuser_id']}]</i>");
            }
        } else {
            $username = (!empty($topicarr['puser_id']) ? format_username((int) $topicarr['puser_id']) : '<i>' . _('Unknown') . " [{$topicarr['tuser_id']}]</i>");
        }
        if ($topicarr['tan'] === '1') {
            if ($CURUSER['class'] < UC_STAFF && $topicarr['tuser_id'] != $CURUSER['id']) {
                $author = (!empty($topicarr['tuser_id']) ? '<i>' . _('Anonymous') . '</i>' : ($topicarr['tuser_id'] == '0' ? '<i>System</i>' : '<i>' . _('Unknown') . '</i>'));
            } else {
                $author = (!empty($topicarr['tuser_id']) ? '<i>' . _('Anonymous') . '</i><br>[ ' . format_username((int) $topicarr['tuser_id']) . ' ]' : ($topicarr['tuser_id'] == '0' ? '<i>System</i>' : '<i>' . _('Unknown') . " [{$topicarr['tuser_id']}]</i>"));
            }
        } else {
            $author = (!empty($topicarr['tuser_id']) ? format_username((int) $topicarr['tuser_id']) : ($topicarr['tuser_id'] == '0' ? '<i>System</i>' : '<i>' . _('Unknown') . " [{$topicarr['tuser_id']}]</i>"));
        }
        $staffimg = ($topicarr['min_class_read'] >= UC_STAFF ? "<img src='" . $site_config['paths']['images_baseurl'] . "staff.png' alt='Staff forum' class='tooltipper' title='Staff Forum'>" : '');
        $stickyimg = ($topicarr['sticky'] === 'yes' ? "<img src='" . $site_config['paths']['images_baseurl'] . "sticky.gif' alt='" . _('Sticky') . "' class='tooltipper right5 left5' title='" . _('Sticky Topic') . "'>" : '');
        $lockedimg = ($topicarr['locked'] === 'yes' ? "<img src='" . $site_config['paths']['images_baseurl'] . "forumicons/locked.gif' alt='" . _('Locked') . "' class='tooltipper right5' title='" . _('Locked Topic') . "'>" : '');
        $topic_name = "<div class='level-left'>{$lockedimg}{$stickyimg}<a href='{$site_config['paths']['baseurl']}/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=last#" . (int) $topicarr['last_post'] . "'><span class='torrent-name'>" . format_comment($topicarr['topic_name']) . "</span></a>{$staffimg}{$menu}</div><span class='size_3'>" . _fe('in {0} by {1} ({2})', "<a href='{$site_config['paths']['baseurl']}/forums.php?action=view_forum&amp;forum_id=" . (int) $topicarr['forum_id'] . "'>" . format_comment($topicarr['name']) . '</a>', $author, $added) . '</span>';
        $forum_posts .= "
                    <tr>
                        <td>{$topic_name}</td>
                        <td class='has-text-centered has-no-border-right has-no-border-left'>{$replies}</td>
                        <td class='has-text-centered has-no-border-right has-no-border-left'>" . number_format((int) $topicarr['views']) . "</td>
                        <td class='has-text-centered has-no-border-left'>{$username}</td>
                    </tr>";
    }
    $forum_posts .= '
                </tbody>
            </table>
        </div>
        </div>
    </div>';
} else {
    $forum_posts .= "
                    <tr>
                        <td colspan='4'>" . _('There are no forum posts.') . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </div>
    </div>';
}
