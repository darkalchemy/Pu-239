<?php

declare(strict_types = 1);
require_once INCL_DIR . 'function_html.php';

use Pu239\Comment;
use Pu239\Image;
use Pu239\User;

$user = check_user_status();
global $container, $site_config;

$comment = $container->get(Comment::class);
$comments = $comment->get_comments();
$posted_comments .= "
        <a id='latest_comment-hash'></a>
        <div id='latest_comment' class='box'>
            <div class='table-wrapper has-text-centered'>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='w-1 has-text-centered w-10 has-no-border-right'>" . _('Type') . "</th>
                            <th class='w-50 min-150 has-no-border-right has-no-border-left'>" . _('Latest Comments') . "</th>
                            <th class='w-1 has-text-centered has-no-border-right has-no-border-left tooltipper' title='" . _('User') . "'><i class='icon-user icon has-text-info' aria-hidden='true'></i></th>
                            <th class='w-1 has-text-centered has-no-border-right has-no-border-left tooltipper' title='" . _('When') . "'><i class='icon-calendar icon' aria-hidden='true'></i></th>
                            <th class='w-1 has-text-centered has-no-border-left tooltipper' title='" . _('Likes') . "'><i class='icon-thumbs-up icon has-text-success' aria-hidden='true'></i></th>
                        </tr>
                    </thead>
                    <tbody>";

$images_class = $container->get(Image::class);
$users_class = $container->get(User::class);
foreach ($comments as $comment) {
    $torrname = format_comment($comment['name']);
    $formatted = $comment['anonymous'] === '1' ? 'Anonymous' : format_username((int) $comment['user']);
    if (empty($comment['poster']) && !empty($imdb_id)) {
        $comment['poster'] = $images_class->find_images($imdb_id);
    }
    $comment['poster'] = empty($comment['poster']) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' class='tooltip-poster' alt=''>" : "<img src='" . url_proxy($comment['poster'], true, 250) . "' alt='' class='tooltip-poster'>";
    if ($comment['anonymous'] === '1' && ($user['class'] < UC_STAFF || (int) $comment['owner'] === $user['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $users_data = $users_class->getUserFromId((int) $comment['owner']);
        $username = !empty($users_data['username']) ? format_comment($users_data['username']) : 'unknown';
        $uploader = "<span class='" . get_user_class_name((int) $comment['class'], true) . "'>" . $username . '</span>';
    }

    $caticon = !empty($comment['image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . $comment['image'] . "' class='tooltipper' alt='" . format_comment($comment['cat']) . "' title='" . format_comment($comment['cat']) . "' height='20px' width='auto'>" : format_comment($comment['cat']);

    $posted_comments .= "
                        <tr>
                            <td class='has-text-centered'>$caticon</td>
                            <td>";
    $block_id = "comment_id_{$comment['comment_id']}";
    $posted_comments .= torrent_tooltip(format_comment($comment['text']), $comment['id'], $block_id, $comment['name'], $comment['poster'], $uploader, $added, $comment['size'], $comment['seeders'], $comment['leechers'], $comment['imdb_id'], $comment['rating'], $comment['year'], $comment['subtitles'], $comment['audios'], $comment['genre'], false, $comment['comment_id']);
    $posted_comments .= "
                            <td class='has-text-centered'>$formatted</td>
                            <td class='has-text-centered'>" . get_date((int) $added, 'LONG') . "</td>
                            <td class='has-text-centered'>" . number_format($comment['user_likes']) . '</td>
                        </tr>';
}

if (count($comments) === 0) {
    $posted_comments .= "
                        <tr>
                            <td colspan='5'>" . _('There are no comments.') . '</td>
                        </tr>';
}

$posted_comments .= '
                    </tbody>
                </table>
            </div>
        </div>';
