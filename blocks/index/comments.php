<?php

require_once INCL_DIR . 'html_functions.php';
global $lang, $site_config, $fluent, $cache;

$comments = $cache->get('latest_comments_');
if ($comments === false || is_null($comments)) {
    $comments = $fluent->from('comments')
        ->select(null)
        ->select('comments.id AS comment_id')
        ->select('comments.user')
        ->select('comments.torrent AS id')
        ->select('comments.added')
        ->select('comments.text')
        ->select('comments.anonymous')
        ->select('comments.user_likes')
        ->select('torrents.name')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.poster')
        ->select('torrents.added AS toradd')
        ->select('torrents.size')
        ->select('torrents.imdb_id')
        ->select('torrents.owner')
        ->select('torrents.times_completed')
        ->select('users.username')
        ->select('users.class')
        ->select('torrents:categories.name AS cat')
        ->select('torrents:categories.image')
        ->innerJoin('torrents ON torrents.id = comments.torrent')
        ->leftJoin('users ON users.id = comments.user')
        ->leftJoin('categories ON categories.id = torrents.category')
        ->where('torrent > 0')
        ->orderBy('comments.id DESC')
        ->limit(5)
        ->fetchAll();
    $cache->set('latest_comments_', $comments, $site_config['expires']['latestcomments']);
}

$posted_comments .= "
        <a id='latest_comment-hash'></a>
        <div id='latest_comment' class='box'>
            <div class='table-wrapper has-text-centered'>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10'>Type</th>
                            <th class='w-50 minw-150'>Last 5 Comments</th>
                            <th class='has-text-centered'>User</th>
                            <th class='has-text-centered'>When</th>
                            <th class='has-text-centered'>Likes</th>
                        </tr>
                    </thead>
                    <tbody>";

foreach ($comments as $comment) {
    $text = $owner = $user = $id = $comment_id = $cat = $image = $poster = $name = $toradd = $seeders = $leechers = $class = $username = $user_likes = $times_completed = '';
    extract($comment);
    $torrname = htmlsafechars($name);
    $user = $anonymous === 'yes' ? 'Anonymous' : format_username($user);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 150, null) . "' class='tooltip-poster'>";
    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        global $user_stuffs;

        $users_data = $user_stuffs->getUserFromId($owner);
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($users_data['username']) . '</span>';
    }

    $posted_comments .= "
                        <tr>
                            <td class='has-text-centered'>
                                <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/$image' class='tooltipper' alt='$cat' title='$cat'>
                            </td>
                            <td>";
    $block_id = "comment_id_{$comment_id}";
    $posted_comments .= torrent_tooltip(format_comment($text));
    $posted_comments .= "
                            <td class='has-text-centered'>$user</td>
                            <td class='has-text-centered'>" . get_date($added, 'LONG') . "</td>
                            <td class='has-text-centered'>" . number_format($user_likes) . '</td>
                        </tr>';
}

if (count($comments) === 0) {
    $posted_comments .= "
                        <tr>
                            <td colspan='5'>No Comments Found</td>
                        </tr>";
}

$posted_comments .= '
                    </tbody>
                </table>
            </div>
        </div>';
