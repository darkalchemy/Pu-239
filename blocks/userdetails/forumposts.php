<?php

global $CURUSER, $site_config, $lang, $user_stuffs, $id, $cache;

$user       = $user_stuffs->getUserFromId($CURUSER['id']);
$forumposts = $cache->get('forum_posts_' . $id);
if ($forumposts === false || is_null($forumposts)) {
    $res              = sql_query('SELECT COUNT(id) FROM posts WHERE user_id=' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
    list($forumposts) = mysqli_fetch_row($res);
    $cache->set('forum_posts_' . $id, $forumposts, $site_config['expires']['forum_posts']);
}
if ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_posts']}</td>";
    if ($forumposts && (($user['class'] >= UC_POWER_USER && $user['id'] == $CURUSER['id']) || $CURUSER['class'] >= UC_STAFF)) {
        $HTMLOUT .= "<td><a href='userhistory.php?action=viewposts&amp;id=$id'>" . htmlsafechars($forumposts) . "</a></td></tr>\n";
    } else {
        $HTMLOUT .= '<td>' . htmlsafechars($forumposts) . "</td></tr>\n";
    }
}
