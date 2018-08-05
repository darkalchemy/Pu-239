<?php

require_once INCL_DIR . 'html_functions.php';
global $lang, $site_config, $fluent, $cache;

$comments = $cache->get('latest_comments_');
if ($comments === false || is_null($comments)) {
    $comments = $fluent->from('comments')
        ->select(null)
        ->select('comments.id')
        ->select('comments.user')
        ->select('comments.torrent')
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

$header = "
                        <tr>
                            <th class='has-text-centered w-10'>Type</th>
                            <th class='w-50 minw-150'>Last 5 Comments</th>
                            <th class='has-text-centered'>User</th>
                            <th class='has-text-centered'>When</th>
                            <th class='has-text-centered'>Likes</th>
                        </tr>";
$body = '';
if (!$comments) {
    $body = '
                        <tr>
                            <td colspan="5">No Comments Found</td>
                        </tr>';
} else {
    foreach ($comments as $comment) {
        $user = $torrent = $id = $cat = $image = $poster = $name = $toradd = $seeders = $leechers = $class = $username = $user_likes = '';
        extract($comment);
        $user = $anonymous === 'yes' ? 'Anonymous' : format_username($user);
        $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster' />" : "<img src='" . url_proxy($poster, true, 150, null) . "' class='tooltip-poster' />";

        $body .= "
                        <tr>
                            <td class='has-text-centered'>
                                <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/$image' class='tooltipper' alt='$cat' title='$cat' />
                            </td>
                            <td>
                                <a href='{$site_config['baseurl']}/details.php?id=$torrent&amp;hit=1'>
                                    <div class='dt-tooltipper-large' data-tooltip-content='#comment_id_{$id}_tooltip'>
                                        " . format_comment($text) . "
                                        <div class='tooltip_templates'>
                                            <span id='comment_id_{$id}_tooltip'>
                                                <div class='is-flex tooltip-torrent'>
                                                    <span class='margin10'>
                                                        $poster
                                                    </span>
                                                    <span class='margin10'>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($name) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b><span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . "</span><br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($toradd, 'DATE', 0, 1) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($size)) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>" . (int) $seeders . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>" . (int) $leechers . "<br>
                                                    </span>
                                                </div>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td class='has-text-centered'>$user</td>
                            <td class='has-text-centered'>" . get_date($added, 'LONG') . "</td>
                            <td class='has-text-centered'>" . number_format($user_likes) . '</td>
                        </tr>';
    }
}

$text = main_table($body, $header);

$HTMLOUT .= "
    <a id='latest_comment-hash'></a>
    <fieldset id='latest_comment' class='header'>
        <legend class='flipper has-text-primary'><i class='icon-down-open size_3' aria-hidden='true'></i>{$lang['index_latest_comments']}</legend>
        <div class='table-wrapper has-text-centered'>
            $text
        </div>
    </fieldset>";
