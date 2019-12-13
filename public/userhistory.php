<?php

declare(strict_types = 1);

use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
$curuser = check_user_status();
global $container, $site_config, $curuser;

$userid = !empty($_GET['id']) ? (int) $_GET['id'] : $curuser['id'];
if ($userid != $curuser['id']) {
    $users_class = $container->get(User::class);
    $user = $users_class->getUserFromId($userid);
}
if (!is_valid_id($userid)) {
    stderr(_('Error'), _('Invalid User ID'));
}
if ($curuser['class'] == UC_MIN || ($curuser['id'] != $userid && $curuser['class'] < UC_STAFF)) {
    stderr(_('Error'), _('Permission denied'));
}
$page = isset($_GET['page']) ? $_GET['page'] : '';
$action = isset($_GET['action']) ? htmlsafechars($_GET['action']) : 'viewposts';
$perpage = 25;
$HTMLOUT = '';
if ($action === 'viewposts') {
    $select_is = 'COUNT(DISTINCT p.id)';
    $from_is = 'posts AS p LEFT JOIN topics as t ON p.topic_id=t.id LEFT JOIN forums AS f ON t.forum_id=f.id';
    $where_is = 'p.user_id=' . sqlesc($userid) . ' AND f.min_class_read <= ' . sqlesc($curuser['class']);
    $order_is = 'p.id DESC';
    $query = "SELECT $select_is FROM $from_is WHERE $where_is";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_row($res) or stderr(_('Error'), _('No posts found'));
    $postcount = (int) $arr[0];
    $pager = pager($perpage, $postcount, "userhistory.php?action=viewposts&amp;id=$userid&amp;");
    if (!empty($user)) {
        $subject = format_username((int) $user['id']);
    } else {
        $subject = _('unknown') . '[' . $userid . ']';
    }
    $from_is = 'posts AS p LEFT JOIN topics as t ON p.topic_id=t.id LEFT JOIN forums AS f ON t.forum_id=f.id LEFT JOIN read_posts as r ON p.topic_id=r.topic_id AND p.user_id=r.user_id';
    $select_is = 'f.id AS f_id, f.name, t.id AS t_id, t.topic_name, t.last_post, r.last_post_read, p.*';
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is {$pager['limit']}";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        stderr(_('Error'), _('No posts found'));
    }
    $HTMLOUT .= "<h1 class='has-text-centered'>" . _('Post history for') . " $subject</h1>\n";
    if ($postcount > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    while ($arr = mysqli_fetch_assoc($res)) {
        $postid = (int) $arr['id'];
        $posterid = (int) $arr['user_id'];
        $topicid = (int) $arr['t_id'];
        $topicname = format_comment($arr['topic_name']);
        $forumid = (int) $arr['f_id'];
        $forumname = format_comment($arr['name']);
        $editedby = (int) $arr['edited_by'];
        $dt = TIME_NOW - $site_config['forum_config']['readpost_expiry'];
        $newposts = 0;
        if ($arr['added'] > $dt) {
            $newposts = ($arr['last_post_read'] < $arr['last_post']) && $curuser['id'] == $userid;
        }
        $added = get_date((int) $arr['added'], '');
        $title = "
        $added -- <b>" . _('Forum') . ": </b>
        <a href='{$site_config['paths']['baseurl']}/forums.php?action=view_forum&amp;forum_id=$forumid'>$forumname</a>
        -- <b>" . _('Topic') . ": </b>
        <a href='{$site_config['paths']['baseurl']}/forums.php?action=view_topic&amp;topic_id=$topicid'>$topicname</a>
        -- <b>" . _('Post') . ": </b>
        <a href='{$site_config['paths']['baseurl']}/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=p$postid#$postid'>#{$postid}</a>" . ($newposts ? "
        <b>(<span class='has-text-danger'>" . _('NEW!') . '</span>)</b>' : '');
        $body = format_comment($arr['body']);

        if (is_valid_id($editedby)) {
            $body = wrapper($body, 'padding10 bottom20');
            $body .= "
                <p>
                    <div class='size_4'>
                        " . _('Last edited by') . ' ' . format_username($editedby) . ' ' . _('at') . ' ' . get_date((int) $arr['edit_date'], 'LONG', 0, 1) . '
                    </div>
                </p>';
        }

        $HTMLOUT .= "
        <div class='portlet'>
            <h3 class='has-text-centered'>
            $title
            </h3>" . main_div($body, '', 'padding20') . '
        </div>';
    }
    if ($postcount > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
    $title = _('Posts Histroy');
    $breadcrumbs = [
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
} elseif ($action === 'viewcomments') {
    $select_is = 'COUNT(t.id)';
    $from_is = 'comments AS c LEFT JOIN torrents as t
                  ON c.torrent = t.id';
    $where_is = 'c.user =' . sqlesc($userid) . '';
    $order_is = 'c.id DESC';
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_row($res) or stderr(_('Error'), _('No comments found'));
    $commentcount = (int) $arr[0];
    $pager = pager($perpage, $commentcount, "userhistory.php?action=viewcomments&amp;id=$userid&amp;");
    if (!empty($user)) {
        $subject = format_username((int) $user['id']);
    } else {
        $subject = _('unknown') . '[' . $userid . ']';
    }
    $select_is = 't.name, c.torrent AS t_id, c.id, c.added, c.text';
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is {$pager['limit']}";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        stderr(_('Error'), _('No comments found'));
    }
    $HTMLOUT .= "<h1 class='has-text-centered'>" . _('Comments history for') . " $subject</h1>\n";
    if ($commentcount > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }

    while ($arr = mysqli_fetch_assoc($res)) {
        $commentid = (int) $arr['id'];
        $torrent = !empty($arr['name']) ? format_comment($arr['name']) : '';
        if (strlen($torrent) > 55) {
            $torrent = substr($torrent, 0, 52) . '...';
        }
        $torrentid = (int) $arr['t_id'];
        $subres = sql_query("SELECT COUNT(id) FROM comments WHERE torrent = " . sqlesc($torrentid) . ' AND id < ' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
        $subrow = mysqli_fetch_row($subres);
        $count = $subrow[0];
        $comm_page = floor($count / 20);
        $page_url = $comm_page ? "&amp;page=$comm_page" : '';
        $added = get_date((int) $arr['added'], '') . ' (' . get_date((int) $arr['added'], '', 0, 1) . ')';
        $body = format_comment($arr['text']);
        $HTMLOUT .= "
        <div class='portlet'>
            <h3 class='has-text-centered'>
                $added --- <b>" . _('Torrent:') . ': </b>' . ($torrent ? ("<a href='{$site_config['paths']['baseurl']}/details.php?id=$torrentid&amp;tocomm=1'>$torrent</a>") : ' [' . _('Deleted') . '] ') . ' --- <b>' . _('Comment') . ": </b>#<a href='{$site_config['paths']['baseurl']}/details.php?id=$torrentid&amp;tocomm=1$page_url'>$commentid</a>
            </h3>" . main_div($body, '', 'padding20') . '
        </div>';
    }
    if ($commentcount > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
    $title = _('Comments History');
    $breadcrumbs = [
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
}
if (empty($action)) {
    stderr(_('History Error'), _('Unknown action.'));
}
stderr(_('History Error'), _('Invalid or no query.'));
