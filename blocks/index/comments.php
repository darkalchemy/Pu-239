<?php

declare(strict_types = 1);
require_once INCL_DIR . 'function_html.php';

use Pu239\Comment;
use Pu239\Image;
use Pu239\User;

global $container, $lang, $site_config, $CURUSER;
$comment = $container->get(Comment::class);
$comments = $comment->get_comments();
$posted_comments .= "
        <a id='latest_comment-hash'></a>
        <div id='latest_comment' class='box'>
            <div class='table-wrapper has-text-centered'>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10'>Type</th>
                            <th class='w-50 min-150'>Last 5 Comments</th>
                            <th class='has-text-centered'>User</th>
                            <th class='has-text-centered'>When</th>
                            <th class='has-text-centered'>Likes</th>
                        </tr>
                    </thead>
                    <tbody>";

$images_class = $container->get(Image::class);
$users_class = $container->get(User::class);
foreach ($comments as $comment) {
    $text = $owner = $user = $id = $comment_id = $cat = $image = $poster = $name = $toradd = $seeders = $leechers = $class = $username = $user_likes = $times_completed = $genre = '';
    $subtitles = $year = $rating = $owner = $anonymous = $name = $added = $class = $cat = $image = $imdb_id = '';
    extract($comment);
    $torrname = htmlsafechars($name);
    $user = $anonymous === 'yes' ? 'Anonymous' : format_username((int) $user);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = $images_class->find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' class='tooltip-poster' alt=''>" : "<img src='" . url_proxy($poster, true, 250) . "' alt='' class='tooltip-poster'>";
    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || (int) $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $users_data = $users_class->getUserFromId((int) $owner);
        $username = !empty($users_data['username']) ? htmlsafechars($users_data['username']) : 'unknown';
        $uploader = "<span class='" . get_user_class_name((int) $class, true) . "'>" . $username . '</span>';
    }

    $caticon = !empty($image) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($image) . "' class='tooltipper' alt='" . htmlsafechars($cat) . "' title='" . htmlsafechars($cat) . "' height='20px' width='auto'>" : htmlsafechars($cat);

    $posted_comments .= "
                        <tr>
                            <td class='has-text-centered'>$caticon</td>
                            <td>";
    $block_id = "comment_id_{$comment_id}";
    $posted_comments .= torrent_tooltip(format_comment($text), $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre, false, $comment_id);
    $posted_comments .= "
                            <td class='has-text-centered'>$user</td>
                            <td class='has-text-centered'>" . get_date((int) $added, 'LONG') . "</td>
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
