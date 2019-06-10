<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

$post_id = (isset($_GET['post_id']) ? intval($_GET['post_id']) : (isset($_POST['post_id']) ? intval($_POST['post_id']) : 0));
$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));
$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
if (!is_valid_id($post_id) || !is_valid_id($forum_id) || !is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
global $container, $site_config, $CURUSER;

$user_stuffs = $container->get(User::class);
$fluent = $container->get(Database::class);
$query = $fluent->from('posts AS p')
                ->select('t.topic_name AS topic_name')
                ->select('f.name AS forum_name')
                ->leftJoin('topics AS t ON p.topic_id = t.id')
                ->leftJoin('forums AS f ON t.forum_id = f.id')
                ->where('p.id = ?', $post_id);
if ($CURUSER['class'] < UC_STAFF) {
    $query = $query->where("p.status = 'ok'")
                   ->where("t.status = 'ok'");
} elseif ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class']) {
    $query = $query->where("p.status != 'deleted'")
                   ->where("t.status != 'deleted'");
}
$query = $query->fetch();
$arr_edited = $user_stuffs->getUserFromId($query['edited_by']);
$icon = htmlsafechars($query['icon']);
$post_title = htmlsafechars($query['post_title']);
$HTMLOUT .= " 
    <h1 class='has-text-centered'>" . ($query['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</>' : htmlsafechars($arr_edited['username'])) . '\'s ' . $lang['vph_final_edit_post'] . "</h1>
    <h2 class='has-text-centered'>{$lang['vph_last_edit_by']}: " . ($query['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : htmlsafechars($arr_edited['username'])) . '</h2>';
$body = "
    #{$post_id} " . ($query['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : format_username($arr_edited['id'])) . " {$lang['fe_posted_on']}: " . get_date($query['added'], 'LONG') . '
    <br>' . (!empty($post_title) ? "{$lang['fe_title']}: <span class='has-text-weight-bold'>{$post_title}</span>" : '') . (!empty($icon) ? ' <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . $icon . '.gif" alt="' . $icon . '" title="' . $icon . '" class="emoticon">' : '') . ($query['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : format_username($arr_edited['id'])) . ($query['bbcode'] === 'yes' ? format_comment($query['body']) : format_comment_no_bbcode($query['body']));
$HTMLOUT .= main_div($body, 'bottom20', 'padding20') . "
    <h2 class='has-text-centered'>{$lang['fe_post_history']}</h2>
    <div class='has-text-centered bottom20'>
        [ {$lang['vph_all_post_edits_date']} ]
    </div>";
$HTMLOUT .= $query['post_history'];
//dd($query['post_history']);
