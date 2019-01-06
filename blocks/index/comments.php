<?php

require_once INCL_DIR . 'html_functions.php';
global $lang, $site_config, $fluent, $cache, $CURUSER;

$comments = $cache->get('latest_comments_');
if ($comments === false || is_null($comments)) {
    $torrents = $fluent->from('comments AS c')
        ->select(null)
        ->select('c.id AS comment_id')
        ->select('c.user')
        ->select('c.torrent AS id')
        ->select('c.added')
        ->select('c.text')
        ->select('c.anonymous')
        ->select('c.user_likes')
        ->select('t.id')
        ->select('t.added')
        ->select('t.seeders')
        ->select('t.leechers')
        ->select('t.name')
        ->select('t.size')
        ->select('t.poster')
        ->select('t.anonymous')
        ->select('t.owner')
        ->select('t.imdb_id')
        ->select('t.times_completed')
        ->select('t.rating')
        ->select('t.year')
        ->select('t.subs AS subtitles')
        ->select('u.username')
        ->select('u.class')
        ->select('p.name AS parent_name')
        ->select('s.name AS cat')
        ->select('s.image')
        ->innerJoin('torrents AS t ON t.id = c.torrent')
        ->leftJoin('users AS u ON u.id = c.user')
        ->leftJoin('categories AS s ON t.category = s.id')
        ->leftJoin('categories AS p ON s.parent_id = p.id')
        ->where('c.torrent > 0')
        ->orderBy('c.id DESC')
        ->limit(5);

    foreach ($torrents as $torrent) {
        if (!empty($torrent['parent_name'])) {
            $torrent['cat'] = $torrent['parent_name'] . '::' . $torrent['cat'];
        }
        $comments[] = $torrent;
    }

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
    $subtitles = $year = $rating = $owner = $anonymous = $name = $added = $class = $cat = $image = '';
    extract($comment);
    $torrname = htmlsafechars($name);
    $user = $anonymous === 'yes' ? 'Anonymous' : format_username($user);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";
    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        global $user_stuffs;

        $users_data = $user_stuffs->getUserFromId($owner);
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($users_data['username']) . '</span>';
    }

    $caticon = !empty($image) ? "<img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($image) . "' class='tooltipper' alt='" . htmlsafechars($cat) . "' title='" . htmlsafechars($cat) . "' height='20px' width='auto'>" : htmlsafechars($cat);

    $posted_comments .= "
                        <tr>
                            <td class='has-text-centered'>$caticon</td>
                            <td>";
    $block_id = "comment_id_{$comment_id}";
    $posted_comments .= torrent_tooltip(format_comment($text), $id, $block_id, $name, $poster,  $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles);
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
