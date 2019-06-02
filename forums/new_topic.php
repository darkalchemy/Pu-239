<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;

flood_limit('forums');
$forum_id = isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0);
if (!is_valid_id($forum_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
global $container, $CURUSER, $site_config;

if ($CURUSER['forum_post'] === 'no' || $CURUSER['suspended'] === 'yes') {
    stderr($lang['gl_error'], $lang['fe_your_no_post_right']);
}
$topic_name = isset($_POST['topic_name']) ? htmlsafechars($_POST['topic_name']) : '';
$topic_desc = isset($_POST['topic_desc']) ? htmlsafechars($_POST['topic_desc']) : '';
$post_title = isset($_POST['post_title']) ? htmlsafechars($_POST['post_title']) : '';
$icon = isset($_POST['icon']) ? htmlsafechars($_POST['icon']) : '';
$body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
$bb_code = !isset($_POST['bb_code']) || $_POST['bb_code'] === 'yes' ? 'yes' : 'no';
$anonymous = isset($_POST['anonymous']) && !empty($_POST['anonymous']) ? 'yes' : 'no';
$poll_question = isset($_POST['poll_question']) ? htmlsafechars($_POST['poll_question']) : '';
$poll_answers = isset($_POST['poll_answers']) ? htmlsafechars($_POST['poll_answers']) : '';
$poll_ends = isset($_POST['poll_ends']) ? (($_POST['poll_ends'] > 168) ? 1356048000 : (TIME_NOW + $_POST['poll_ends'] * 86400)) : '';
$poll_starts = isset($_POST['poll_starts']) ? (($_POST['poll_starts'] === 0) ? TIME_NOW : (TIME_NOW + $_POST['poll_starts'] * 86400)) : '';
$poll_starts = $poll_starts > ((int) $poll_ends + 1) ? TIME_NOW : $poll_starts;
$change_vote = isset($_POST['change_vote']) && $_POST['change_vote'] === 'yes' ? 'yes' : 'no';
$subscribe = isset($_POST['subscribe']) && $_POST['subscribe'] === 'yes' ? 'yes' : 'no';
$fluent = $container->get(Database::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['button'] === 'Post') {
    if (empty($body)) {
        stderr($lang['gl_error'], $lang['fe_no_body_txt']);
    }
    if (empty($topic_name)) {
        stderr($lang['gl_error'], $lang['fe_no_topic_name']);
    }
    $poll_id = 0;
    if (!empty($poll_answers)) {
        $break_down_poll_options = explode("\n", $poll_answers);
        for ($i = 0; $i < count($break_down_poll_options); ++$i) {
            if (strlen($break_down_poll_options[$i]) < 2) {
                stderr($lang['gl_error'], $lang['fe_no_blank_lines_in_poll']);
            }
        }
        if ($i > 20 || $i < 2) {
            stderr($lang['gl_error'], '' . $lang['fe_there_is_min_max_options'] . ' ' . $i . '.');
        }
        $multi_options = isset($_POST['multi_options']) && $_POST['multi_options'] <= $i ? intval($_POST['multi_options']) : 1;

        $poll_options = serialize($break_down_poll_options);
        $values = [
            'user_id' => $CURUSER['id'],
            'question' => $poll_question,
            'poll_answers' => $poll_options,
            'number_of_options' => $i,
            'poll_starts' => $poll_starts,
            'poll_ends' => $poll_ends,
            'change_vote' => $change_vote,
            'multi_options' => $multi_options,
        ];
        $poll_id = $fluent->insertInto('forum_poll')
                          ->values($values)
                          ->execute();
    }
    $values = [
        'user_id' => $CURUSER['id'],
        'topic_name' => $topic_name,
        'last_post' => $CURUSER['id'],
        'forum_id' => $forum_id,
        'topic_desc' => $topic_desc,
        'poll_id' => $poll_id,
        'anonymous' => $anonymous,
        'added' => TIME_NOW,
    ];
    $topic_id = $fluent->insertInto('topics')
                       ->values($values)
                       ->execute();

    $values = [
        'topic_id' => $topic_id,
        'user_id' => $CURUSER['id'],
        'added' => TIME_NOW,
        'body' => $body,
        'icon' => $icon,
        'post_title' => $post_title,
        'bbcode' => $bb_code,
        'anonymous' => $anonymous,
    ];

    $post_id = $fluent->insertInto('posts')
                      ->values($values)
                      ->execute();
    $post_id = (int) $post_id;
    $set = [
        'forumtopics' => new Literal('forumtopics + 1'),
    ];
    $fluent->update('usersachiev')
           ->set($set)
           ->where('userid = ?', $CURUSER['id'])
           ->execute();

    clr_forums_cache($post_id);
    clr_forums_cache($forum_id);
    $cache = $container->get(Cache::class);
    $cache->delete('forum_posts_' . $CURUSER['id']);

    $set = [
        'first_post' => $post_id,
        'last_post' => $post_id,
        'post_count' => 1,
    ];
    $fluent->update('topics')
           ->set($set)
           ->where('id = ?', $topic_id)
           ->execute();

    $set = [
        'post_count' => new Literal('post_count + 1'),
        'topic_count' => new Literal('topic_count + 1'),
    ];
    $fluent->update('forums')
           ->set($set)
           ->where('id = ?', $forum_id)
           ->execute();

    if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
        $message = $CURUSER['username'] . ' ' . $lang['nt_created_new_topic'] . " [quote][url={$site_config['paths']['baseurl']}/forums.php?action=view_topic&topic_id=$topic_id&page=last]{$topic_name}[/url][/quote]";
        if (!in_array($forum_id, $site_config['staff_forums'])) {
            autoshout($message);
        }
    }
    if ($site_config['bonus']['on']) {
        $set = [
            'seedbonus' => $CURUSER['seedbonus'] + $site_config['bonus']['per_topic'],
        ];
        $fluent->update('users')
               ->set($set)
               ->where('id = ?', $CURUSER['id'])
               ->execute();
        $cache->update_row('user_' . $CURUSER['id'], [
            'seedbonus' => $CURUSER['seedbonus'] + $site_config['bonus']['per_topic'],
        ]);
    }

    if ($subscribe === 'yes') {
        $values = [
            'user_id' => $CURUSER['id'],
            'topic_id' => $topic_id,
        ];
        $fluent->insertInto('subscriptions')
               ->values($values)
               ->execute();
    }

    $extension_error = $size_error = 0;
    if (!empty($_FILES)) {
        require_once FORUM_DIR . 'attachment.php';
        $uploaded = upload_attachments($post_id);
        $extension_error = $uploaded[0];
        $size_error = $uploaded[1];
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id . ($extension_error !== 0 ? '&ee=' . $extension_error : '') . ($size_error !== 0 ? '&se=' . $size_error : ''));
    die();
}

$forum_name = $fluent->from('forums')
                     ->select(null)
                     ->select('name')
                     ->where('id = ?', $forum_id)
                     ->fetch('name');

$section_name = htmlsafechars($forum_name);

$HTMLOUT .= '
    <h1 class="has-text-centered">' . $lang['nt_new_topic_in'] . ' "<a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $forum_id . '">' . $section_name . '</a>"</h1>
    <form method="post" action="' . $site_config['paths']['baseurl'] . '/forums.php?action=new_topic&amp;forum_id=' . $forum_id . '" enctype="multipart/form-data" accept-charset="utf-8">';

require_once FORUM_DIR . 'editor.php';

$HTMLOUT .= '
        <div class="has-text-centered margin20">
            <input type="submit" name="button" class="button is-small" value="' . $lang['fe_post'] . '">
        </div>
    </form>';
