<?php

global $CURUSER, $site_config, $lang, $cache, $user;

$torrentcomments = $cache->get('torrent_comments_' . $id);
if ($torrentcomments === false || is_null($torrentcomments)) {
    $res                   = sql_query('SELECT COUNT(id) FROM comments WHERE user = ' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
    list($torrentcomments) = mysqli_fetch_row($res);
    $cache->set('torrent_comments_' . $id, $torrentcomments, $site_config['expires']['torrent_comments']);
}
if ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_comments']}</td>";
    if ($torrentcomments && (($user['class'] >= UC_POWER_USER && $user['id'] == $CURUSER['id']) || $CURUSER['class'] >= UC_STAFF)) {
        $HTMLOUT .= "<td><a href='userhistory.php?action=viewcomments&amp;id=$id'>" . (int) $torrentcomments . "</a></td></tr>\n";
    } else {
        $HTMLOUT .= '<td>' . (int) $torrentcomments . "</td></tr>\n";
    }
}
