<?php

global $CURUSER, $site_config, $lang, $cache, $user;

$usercomments = $cache->get('user_comments_' . $user['id']);
if ($usercomments === false || is_null($usercomments)) {
    $res = sql_query('SELECT COUNT(id) FROM comments WHERE user = ' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
    list($usercomments) = mysqli_fetch_row($res);
    $cache->set('user_comments_' . $user['id'], $usercomments, $site_config['expires']['torrent_comments']);
}

if ($user['paranoia'] < 2 || $CURUSER['id'] == $user['id'] || $CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_comments']}</td>";
    if ($usercomments && (($user['class'] >= (UC_MIN + 1) && $user['id'] == $CURUSER['id']) || $CURUSER['class'] >= UC_STAFF)) {
        $HTMLOUT .= "<td><a href='{$site_config['paths']['baseurl']}/userhistory.php?action=viewcomments&amp;id={$user['id']}'>" . (int) $usercomments . "</a></td></tr>\n";
    } else {
        $HTMLOUT .= '<td>' . (int) $usercomments . "</td></tr>\n";
    }
}
