<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $site_config, $CURUSER;

$cache = $container->get(Cache::class);
$forumposts = $cache->get('forum_posts_' . $id);
if ($forumposts === false || is_null($forumposts)) {
    $fluent = $container->get(Database::class);
    $forumposts = $fluent->from('posts')
                         ->select(null)
                         ->select('COUNT(id) AS count')
                         ->where('user_id = ?', $user['id'])
                         ->fetch('count');

    $cache->set('forum_posts_' . $id, $forumposts, $site_config['expires']['forum_posts']);
}
if ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Forum Posts') . '</td>';
    if ($forumposts && (($user['class'] >= (UC_MIN + 1) && $user['id'] == $CURUSER['id']) || $CURUSER['class'] >= UC_STAFF)) {
        $HTMLOUT .= "<td><a href='userhistory.php?action=viewposts&amp;id=$id'>" . (int) $forumposts . "</a></td></tr>\n";
    } else {
        $HTMLOUT .= '<td>' . (int) $forumposts . "</td></tr>\n";
    }
}
