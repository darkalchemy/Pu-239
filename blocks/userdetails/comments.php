<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $site_config, $CURUSER;

$cache = $container->get(Cache::class);
$usercomments = $cache->get('user_comments_' . $user['id']);
if ($usercomments === false || is_null($usercomments)) {
    $fluent = $container->get(Database::class);
    $usercomments = $fluent->from('comments')
                           ->select(null)
                           ->select('COUNT(id) AS count')
                           ->where('user = ?', $user['id'])
                           ->fetch('count');
    $cache->set('user_comments_' . $user['id'], $usercomments, $site_config['expires']['torrent_comments']);
}

if ($user['paranoia'] < 2 || $CURUSER['id'] == $user['id'] || has_access($CURUSER['class'], UC_STAFF, '')) {
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Torrent Comments') . '</td>';
    if ($usercomments && ((has_access($CURUSER['class'], UC_STAFF + 1, '') && $user['id'] == $CURUSER['id']) || has_access($CURUSER['class'], UC_STAFF, ''))) {
        $HTMLOUT .= "<td><a href='{$site_config['paths']['baseurl']}/userhistory.php?action=viewcomments&amp;id={$user['id']}'>" . (int) $usercomments . "</a></td></tr>\n";
    } else {
        $HTMLOUT .= '<td>' . (int) $usercomments . "</td></tr>\n";
    }
}
