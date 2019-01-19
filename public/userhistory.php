<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $CURUSER, $site_config, $user_stuffs;

$lang = array_merge(load_language('global'), load_language('userhistory'));
$userid = (int) $_GET['id'];
if (!is_valid_id($userid)) {
    stderr($lang['stderr_errorhead'], $lang['stderr_invalidid']);
}
if ($CURUSER['class'] == UC_MIN || ($CURUSER['id'] != $userid && $CURUSER['class'] < UC_STAFF)) {
    stderr($lang['stderr_errorhead'], $lang['stderr_perms']);
}
$page = isset($_GET['page']) ? $_GET['page'] : '';
$action = isset($_GET['action']) ? htmlsafechars($_GET['action']) : '';
$perpage = 25;
$HTMLOUT = '';

if ($action === 'viewposts') {
    $select_is = 'COUNT(DISTINCT p.id)';
    $from_is = 'posts AS p LEFT JOIN topics as t ON p.topic_id = t.id LEFT JOIN forums AS f ON t.forum_id = f.id';
    $where_is = 'p.user_id = ' . sqlesc($userid) . ' AND f.min_class_read <= ' . sqlesc($CURUSER['class']);
    $order_is = 'p.id DESC';
    $query = "SELECT $select_is FROM $from_is WHERE $where_is";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_row($res) or stderr($lang['stderr_errorhead'], $lang['top_noposts']);
    $postcount = $arr[0];
    $pager = pager($perpage, $postcount, "userhistory.php?action=viewposts&amp;id=$userid&amp;");
    $user = $user_stuffs->getUserFromId($userid);
    if (!empty($user)) {
        $subject = format_username($user['id']);
    } else {
        $subject = $lang['posts_unknown'] . '[' . $userid . ']';
    }
    $from_is = 'posts AS p LEFT JOIN topics as t ON p.topic_id = t.id LEFT JOIN forums AS f ON t.forum_id = f.id LEFT JOIN read_posts as r ON p.topic_id = r.topic_id AND p.user_id = r.user_id';
    $select_is = 'f.id AS f_id, f.name, t.id AS t_id, t.topic_name, t.last_post, r.last_post_read, p.*';
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is {$pager['limit']}";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        stderr($lang['stderr_errorhead'], $lang['top_noposts']);
    }
    $HTMLOUT .= "<h1 class='has-text-centered'>{$lang['top_posthfor']} $subject</h1>\n";
    if ($postcount > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    while ($arr = mysqli_fetch_assoc($res)) {
        $postid = (int) $arr['id'];
        $posterid = (int) $arr['user_id'];
        $topicid = (int) $arr['t_id'];
        $topicname = htmlsafechars($arr['topic_name']);
        $forumid = (int) $arr['f_id'];
        $forumname = htmlsafechars($arr['name']);
        $dt = (TIME_NOW - $site_config['readpost_expiry']);
        $newposts = 0;
        if ($arr['added'] > $dt) {
            $newposts = ($arr['last_post_read'] < $arr['last_post']) && $CURUSER['id'] == $userid;
        }
        $added = get_date($arr['added'], '');
        $title = "
        $added -- <b>{$lang['posts_forum']}: </b>
        <a href='{$site_config['baseurl']}/forums.php?action=view_forum&amp;forum_id=$forumid'>$forumname</a>
        -- <b>{$lang['posts_topic']}: </b>
        <a href='{$site_config['baseurl']}/forums.php?action=view_topic&amp;topic_id=$topicid'>$topicname</a>
        -- <b>{$lang['posts_post']}: </b>
        <a href='{$site_config['baseurl']}/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=p$postid#$postid'>#{$postid}</a>" . ($newposts ? "
        <b>(<span class='has-text-danger'>{$lang['posts_new']}</span>)</b>" : '');
        $body = format_comment($arr['body']);

        if (is_valid_id($arr['edited_by'])) {
            $body = wrapper($body, 'padding10 bottom20');
            $body .= "
                <p>
                    <div class='size_4'>
                        {$lang['posts_lasteditedby']} " . format_username($arr['edited_by']) . " {$lang['posts_at']} " . get_date($arr['edit_date'], 'LONG', 0, 1) . '
                    </div>
                </p>';
        }

        $HTMLOUT .= "
        <div class='portlet'>
            <h2 class='has-text-centered'>
            $title
            </h2>" . main_div($body) . '
        </div>';
    }
    if ($postcount > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
    echo stdhead($lang['head_post']) . wrapper($HTMLOUT) . stdfoot();
    die();
} elseif ($action === 'viewcomments') {
    $select_is = 'COUNT(*)';
    $from_is = 'comments AS c LEFT JOIN torrents as t
                  ON c.torrent = t.id';
    $where_is = 'c.user =' . sqlesc($userid) . '';
    $order_is = 'c.id DESC';
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_row($res) or stderr($lang['stderr_errorhead'], $lang['top_nocomms']);
    $commentcount = $arr[0];
    $pager = pager($perpage, $commentcount, "userhistory.php?action=viewcomments&amp;id=$userid&amp;");
    $user = $user_stuffs->getUserFromId($userid);
    if (!empty($user)) {
        $subject = format_username($user['id']);
    } else {
        $subject = $lang['posts_unknown'] . '[' . $userid . ']';
    }
    $select_is = 't.name, c.torrent AS t_id, c.id, c.added, c.text';
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is {$pager['limit']}";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        stderr($lang['stderr_errorhead'], $lang['top_nocomms']);
    }
    $HTMLOUT .= "<h1 class='has-text-centered'>{$lang['top_commhfor']} $subject</h1>\n";
    if ($commentcount > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }

    while ($arr = mysqli_fetch_assoc($res)) {
        $commentid = (int) $arr['id'];
        $torrent = htmlsafechars($arr['name']);
        if (strlen($torrent) > 55) {
            $torrent = substr($torrent, 0, 52) . '...';
        }
        $torrentid = (int) $arr['t_id'];
        $subres = sql_query('SELECT COUNT(*) FROM comments WHERE torrent = ' . sqlesc($torrentid) . ' AND id < ' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
        $subrow = mysqli_fetch_row($subres);
        $count = $subrow[0];
        $comm_page = floor($count / 20);
        $page_url = $comm_page ? "&amp;page=$comm_page" : '';
        $added = get_date($arr['added'], '') . ' (' . get_date($arr['added'], '', 0, 1) . ')';
        $body = format_comment($arr['text']);
        $HTMLOUT .= "
        <div class='portlet'>
            <h2 class='has-text-centered'>
                $added --- <b>{$lang['posts_torrent']}: </b>" . ($torrent ? ("<a href='{$site_config['baseurl']}/details.php?id=$torrentid&amp;tocomm=1'>$torrent</a>") : " [{$lang['posts_del']}] ") . " --- <b>{$lang['posts_comment']}: </b>#<a href='{$site_config['baseurl']}/details.php?id=$torrentid&amp;tocomm=1$page_url'>$commentid</a>
            </h2>" . main_div($body) . '
        </div>';
    }
    if ($commentcount > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
    echo stdhead($lang['head_comm']) . wrapper($HTMLOUT) . stdfoot();
    die();
}
if (empty($action)) {
    stderr($lang['stderr_histerrhead'], $lang['stderr_unknownact']);
}
stderr($lang['stderr_histerrhead'], $lang['stderr_invalidq']);
