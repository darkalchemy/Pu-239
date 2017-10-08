<?php
$HTMLOUT .= "
    <a id='latestforum-hash'></a>
    <fieldset id='latestforum' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['latestposts_title']}</legend>
        <div class='text-center'>";
$page = 1;
$num = 0;
if (($topics = $mc1->get_value('last_posts_' . $CURUSER['class'])) === false) {
    $topicres = sql_query('SELECT t.id, t.user_id, t.topic_name, t.locked, t.forum_id, t.last_post, t.sticky, t.views, t.anonymous AS tan, f.min_class_read, f.name ' . ', (SELECT COUNT(id) FROM posts WHERE topic_id=t.id) AS p_count ' . ', p.user_id AS puser_id, p.added, p.anonymous AS pan ' . ', u.id AS uid, u.username ' . ', u2.username AS u2_username ' . 'FROM topics AS t ' . 'LEFT JOIN forums AS f ON f.id = t.forum_id ' . 'LEFT JOIN posts AS p ON p.id=(SELECT MAX(id) FROM posts WHERE topic_id = t.id) ' . 'LEFT JOIN users AS u ON u.id=p.user_id ' . 'LEFT JOIN users AS u2 ON u2.id=t.user_id ' . 'WHERE f.min_class_read <= ' . $CURUSER['class'] . ' ' . "ORDER BY t.last_post DESC LIMIT {$site_config['latest_posts_limit']}") or sqlerr(__FILE__, __LINE__);
    while ($topic = mysqli_fetch_assoc($topicres)) {
        $topics[] = $topic;
    }
    $mc1->cache_value('last_posts_' . $CURUSER['class'], $topics, $site_config['expires']['latestposts']);
}
if (count($topics) > 0) {
    $HTMLOUT .= "
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th class='row-fluid span5'>{$lang['latestposts_topic_title']}</th>
                        <th class='row-fluid span1'>{$lang['latestposts_replies']}</th>
                        <th class='row-fluid span1'>{$lang['latestposts_views']}</th>
                        <th class='row-fluid span1'>{$lang['latestposts_last_post']}</th>
                    </tr>
                </thead>
                <tbody>";
    if ($topics) {
        foreach ($topics as $topicarr) {
            $topicid = (int)$topicarr['id'];
            $topic_userid = (int)$topicarr['user_id'];
            $perpage = (int)$CURUSER['postsperpage'];

            if (!$perpage) {
                $perpage = 24;
            }
            $posts = (int)$topicarr['p_count'];
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
                    $menu .= "<a href='/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=$i'>$i</a>\n";
                }
                if ($i < $pages) {
                    $menu .= "|\n";
                }
                if ($i == $pages && $i > 1) {
                    $menu .= ']';
                }
            }
            $added = get_date($topicarr['added'], '', 0, 1);
            if ($topicarr['pan'] == 'yes') {
                if ($CURUSER['class'] < UC_STAFF && $topicarr['user_id'] != $CURUSER['id']) {
                    $username = (!empty($topicarr['username']) ? "<i>{$lang['index_fposts_anonymous']}</i>" : "<i>{$lang['index_fposts_unknow']}</i>");
                } else {
                    $username = (!empty($topicarr['username']) ? "<i>{$lang['index_fposts_anonymous']}</i><br><a href='" . $site_config['baseurl'] . '/userdetails.php?id=' . (int)$topicarr['puser_id'] . "'><b>[" . htmlsafechars($topicarr['username']) . ']</b></a>' : "<i>{$lang['index_fposts_unknow']}[$topic_userid]</i>");
                }
            } else {
                $username = (!empty($topicarr['username']) ? "<a href='" . $site_config['baseurl'] . '/userdetails.php?id=' . (int)$topicarr['puser_id'] . "'><b>" . htmlsafechars($topicarr['username']) . '</b></a>' : "<i>{$lang['index_fposts_unknow']}[$topic_userid]</i>");
            }
            if ($topicarr['tan'] == 'yes') {
                if ($CURUSER['class'] < UC_STAFF && $topicarr['user_id'] != $CURUSER['id']) {
                    $author = (!empty($topicarr['u2_username']) ? "<i>{$lang['index_fposts_anonymous']}</i>" : ($topic_userid == '0' ? '<i>System</i>' : "<i>{$lang['index_fposts_unknow']}</i>"));
                } else {
                    $author = (!empty($topicarr['u2_username']) ? "<i>{$lang['index_fposts_anonymous']}</i><br><a href='" . $site_config['baseurl'] . '/userdetails.php?id=' . $topic_userid . "'><b>[" . htmlsafechars($topicarr['u2_username']) . ']</b></a>' : ($topic_userid == '0' ? '<i>System</i>' : "<i>{$lang['index_fposts_unknow']}[$topic_userid]</i>"));
                }
            } else {
                $author = (!empty($topicarr['u2_username']) ? "<a href='" . $site_config['baseurl'] . '/userdetails.php?id=' . $topic_userid . "'><b>" . htmlsafechars($topicarr['u2_username']) . '</b></a>' : ($topic_userid == '0' ? '<i>System</i>' : "<i>{$lang['index_fposts_unknow']}[$topic_userid]</i>"));
            }
            $staffimg = ($topicarr['min_class_read'] >= UC_STAFF ? "<img src='" . $site_config['pic_base_url'] . "staff.png' border='0' alt='Staff forum' title='Staff Forum' />" : '');
            $stickyimg = ($topicarr['sticky'] == 'yes' ? "<img src='" . $site_config['pic_base_url'] . "sticky.gif' border='0' alt='{$lang['index_fposts_sticky']}' title='{$lang['index_fposts_stickyt']}' />&#160;&#160;" : '');
            $lockedimg = ($topicarr['locked'] == 'yes' ? "<img src='" . $site_config['pic_base_url'] . "forumicons/locked.gif' border='0' alt='{$lang['index_fposts_locked']}' title='{$lang['index_fposts_lockedt']}' />&#160;" : '');
            $topic_name = $lockedimg . $stickyimg . "<a href='/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=last#" . (int)$topicarr['last_post'] . "'><b>" . htmlsafechars($topicarr['topic_name']) . "</b></a>&#160;&#160;$staffimg&#160;&#160;$menu<br><font class='small'>{$lang['index_fposts_in']}<a href='forums.php?action=view_forum&amp;forum_id=" . (int)$topicarr['forum_id'] . "'>" . htmlsafechars($topicarr['name']) . "</a>&#160;by&#160;$author&#160;&#160;($added)</font>";
            $HTMLOUT .= "
                    <tr>
                        <td>{$topic_name}</td>
                        <td>{$replies}</td>
                        <td>" . number_format($topicarr['views']) . "</td>
                        <td>{$username}</td>
                    </tr>";
        }
        $HTMLOUT .= "
                </tbody>
            </table>
        </div>
    </fieldset>";
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
