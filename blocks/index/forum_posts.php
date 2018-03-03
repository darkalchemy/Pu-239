<?php

global $CURUSER, $site_config, $lang, $cache;

$HTMLOUT .= "
    <a id='latestforum-hash'></a>
    <fieldset id='latestforum' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['latestposts_title']}</legend>
        <div class='table-wrapper has-text-centered'>";
$page   = 1;
$num    = 0;
$topics = $cache->get('last_posts_' . $CURUSER['class']);
if ($topics === false || is_null($topics)) {
    $topicres = sql_query('SELECT t.id, t.user_id AS tuser_id, t.topic_name, t.locked, t.forum_id, t.last_post, t.sticky, t.views, t.anonymous AS tan,
                            f.min_class_read, f.name,
                            (SELECT COUNT(id) FROM posts WHERE topic_id = t.id) AS p_count, p.user_id AS puser_id, p.added, p.anonymous AS pan
                            FROM topics AS t
                            LEFT JOIN forums AS f ON f.id = t.forum_id
                            LEFT JOIN posts AS p ON p.id = (SELECT MAX(id) FROM posts WHERE topic_id = t.id)
                            WHERE f.min_class_read <= ' . $CURUSER['class'] . "
                            ORDER BY t.last_post DESC
                            LIMIT {$site_config['latest_posts_limit']}") or sqlerr(__FILE__, __LINE__);
    while ($topic = mysqli_fetch_assoc($topicres)) {
        $topics[] = $topic;
    }
    $cache->set('last_posts_' . $CURUSER['class'], $topics, $site_config['expires']['latestposts']);
}
if (count($topics) > 0) {
    $HTMLOUT .= "
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th>{$lang['latestposts_topic_title']}</th>
                        <th>{$lang['latestposts_replies']}</th>
                        <th>{$lang['latestposts_views']}</th>
                        <th>{$lang['latestposts_last_post']}</th>
                    </tr>
                </thead>
                <tbody>";
    if ($topics) {
        foreach ($topics as $topicarr) {
            $topicid = (int) $topicarr['id'];
            $perpage = (int) $CURUSER['postsperpage'];

            if (!$perpage) {
                $perpage = 24;
            }
            $posts   = (int) $topicarr['p_count'];
            $replies = max(0, $posts - 1);
            $first   = ($page * $perpage)                                                                                                                                                                                                                                                                                                                                                                                                                                                                               - $perpage + 1;
            $last    = $first                                                                                                                                                                                                                                                                                                                                                                                                                                                                                + $perpage - 1;
            if ($last > $num) {
                $last = $num;
            }
            $pages = ceil($posts / $perpage);
            $menu  = '';
            for ($i = 1; $i <= $pages; ++$i) {
                if (1 == $i && $i != $pages) {
                    $menu .= '[ ';
                }
                if ($pages > 1) {
                    $menu .= "<a href='{$site_config['baseurl']}/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=$i'>$i</a>\n";
                }
                if ($i < $pages) {
                    $menu .= "|\n";
                }
                if ($i == $pages && $i > 1) {
                    $menu .= ']';
                }
            }
            $added = get_date($topicarr['added'], '', 0, 1);
            if ('yes' == $topicarr['pan']) {
                if ($CURUSER['class'] < UC_STAFF && $topicarr['tuser_id'] != $CURUSER['id']) {
                    $username = (!empty($topicarr['puser_id']) ? "<i>{$lang['index_fposts_anonymous']}</i>" : "<i>{$lang['index_fposts_unknow']}</i>");
                } else {
                    $username = (!empty($topicarr['puser_id']) ? "<i>{$lang['index_fposts_anonymous']}</i>[ " . format_username($topicarr['puser_id']) . ' ]' : "<i>{$lang['index_fposts_unknow']}[{$topicarr['tuser_id']}]</i>");
                }
            } else {
                $username = (!empty($topicarr['puser_id']) ? format_username($topicarr['puser_id']) : "<i>{$lang['index_fposts_unknow']}[{$topicarr['tuser_id']}]</i>");
            }
            if ('yes' == $topicarr['tan']) {
                if ($CURUSER['class'] < UC_STAFF && $topicarr['tuser_id'] != $CURUSER['id']) {
                    $author = (!empty($topicarr['tuser_id']) ? "<i>{$lang['index_fposts_anonymous']}</i>" : ('0' == $topicarr['tuser_id'] ? '<i>System</i>' : "<i>{$lang['index_fposts_unknow']}</i>"));
                } else {
                    $author = (!empty($topicarr['tuser_id']) ? "<i>{$lang['index_fposts_anonymous']}</i><br>[ " . format_username($topicarr['tuser_id']) . ' ]' : ('0' == $topicarr['tuser_id'] ? '<i>System</i>' : "<i>{$lang['index_fposts_unknow']}[{$topicarr['tuser_id']}]</i>"));
                }
            } else {
                $author = (!empty($topicarr['tuser_id']) ? format_username($topicarr['tuser_id']) : ('0' == $topicarr['tuser_id'] ? '<i>System</i>' : "<i>{$lang['index_fposts_unknow']}[{$topicarr['tuser_id']}]</i>"));
            }
            $staffimg   = ($topicarr['min_class_read'] >= UC_STAFF ? "<img src='" . $site_config['pic_baseurl'] . "staff.png' alt='Staff forum' title='Staff Forum' />" : '');
            $stickyimg  = ('yes' == $topicarr['sticky'] ? "<img src='" . $site_config['pic_baseurl'] . "sticky.gif' alt='{$lang['index_fposts_sticky']}' title='{$lang['index_fposts_stickyt']}' />&#160;&#160;" : '');
            $lockedimg  = ('yes' == $topicarr['locked'] ? "<img src='" . $site_config['pic_baseurl'] . "forumicons/locked.gif' alt='{$lang['index_fposts_locked']}' title='{$lang['index_fposts_lockedt']}' />&#160;" : '');
            $topic_name = $lockedimg . $stickyimg . "<a href='{$site_config['baseurl']}/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=last#" . (int) $topicarr['last_post'] . "'><b>" . htmlsafechars($topicarr['topic_name']) . "</b></a>&#160;&#160;$staffimg&#160;&#160;$menu<br><font class='small'>{$lang['index_fposts_in']}<a href='forums.php?action=view_forum&amp;forum_id=" . (int) $topicarr['forum_id'] . "'>" . htmlsafechars($topicarr['name']) . "</a>&#160;by&#160;$author&#160;&#160;($added)</font>";
            $HTMLOUT .= "
                    <tr>
                        <td>{$topic_name}</td>
                        <td>{$replies}</td>
                        <td>" . number_format($topicarr['views']) . "</td>
                        <td>{$username}</td>
                    </tr>";
        }
        $HTMLOUT .= '
                </tbody>
            </table>
        </div>
    </fieldset>';
    } else {
        //if there are no posts...
        if (empty($topics)) {
            $HTMLOUT .= "
        <tr><td colspan='4'>
         {$lang['latestposts_no_posts']}
        </td></tr></table>
        </div></fieldset>";
        }
    }
}
