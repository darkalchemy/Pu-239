<?php

global $lang, $CURUSER, $site_config;

flood_limit('forums');
$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));
if (!is_valid_id($forum_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

if ($CURUSER['forum_post'] === 'no' || $CURUSER['suspended'] === 'yes') {
    stderr($lang['gl_error'], $lang['fe_your_no_post_right']);
}
$extension_error = $size_error = '';

$topic_name = strip_tags(isset($_POST['topic_name']) ? $_POST['topic_name'] : '');
$topic_desc = strip_tags(isset($_POST['topic_desc']) ? $_POST['topic_desc'] : '');

$post_title = strip_tags(isset($_POST['post_title']) ? $_POST['post_title'] : '');
$icon = htmlsafechars(isset($_POST['icon']) ? $_POST['icon'] : '');
$body = isset($_POST['body']) ? $_POST['body'] : '';
$ip = getip();
$bb_code = !isset($_POST['bb_code']) || $_POST['bb_code'] == 'yes' ? 'yes' : 'no';
$anonymous = isset($_POST['anonymous']) && $_POST['anonymous'] != '' ? 'yes' : 'no';

$poll_question = strip_tags(isset($_POST['poll_question']) ? trim($_POST['poll_question']) : '');
$poll_answers = strip_tags(isset($_POST['poll_answers']) ? trim($_POST['poll_answers']) : '');
$poll_ends = isset($_POST['poll_ends']) ? (($_POST['poll_ends'] > 168) ? 1356048000 : (TIME_NOW + $_POST['poll_ends'] * 86400)) : '';
$poll_starts = isset($_POST['poll_starts']) ? (($_POST['poll_starts'] === 0) ? TIME_NOW : (TIME_NOW + $_POST['poll_starts'] * 86400)) : '';
$poll_starts = $poll_starts > ((int) $poll_ends + 1) ? TIME_NOW : $poll_starts;
$change_vote = isset($_POST['change_vote']) && $_POST['change_vote'] === 'yes' ? 'yes' : 'no';
$subscribe = isset($_POST['subscribe']) && $_POST['subscribe'] === 'yes' ? 'yes' : 'no';
if (isset($_POST['button']) && $_POST['button'] === 'Post') {
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
        'ip' => inet_pton($ip),
        'anonymous' => $anonymous,
    ];

    $post_id = $fluent->insertInto('posts')
                      ->values($values)
                      ->execute();

    $set = [
        'forumtopics' => new Envms\FluentPDO\Literal('forumtopics + 1'),
    ];
    $fluent->update('usersachiev')
           ->set($set)
           ->where('userid = ?', $CURUSER['id'])
           ->execute();

    clr_forums_cache($post_id);
    clr_forums_cache($forum_id);
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
        'post_count' => new Envms\FluentPDO\Literal('post_count + 1'),
        'topic_count' => new Envms\FluentPDO\Literal('topic_count + 1'),
    ];
    $fluent->update('forums')
           ->set($set)
           ->where('id = ?', $forum_id)
           ->execute();

    if ($site_config['autoshout_on']) {
        $message = $CURUSER['username'] . ' ' . $lang['nt_created_new_topic'] . " [quote][url={$site_config['baseurl']}/forums.php?action=view_topic&topic_id=$topic_id&page=last]{$topic_name}[/url][/quote]";
        if (!in_array($forum_id, $site_config['staff_forums'])) {
            autoshout($message);
        }
    }
    if ($site_config['seedbonus_on']) {
        $set = [
            'seedbonus' => new Envms\FluentPDO\Literal('seedbonus + ' . $site_config['bonus_per_topic']),
        ];
        $fluent->update('users')
               ->set($set)
               ->where('id = ?', $CURUSER['id'])
               ->execute();
        $cache->update_row('user_' . $CURUSER['id'], [
            'seedbonus' => $CURUSER['seedbonus'] + $site_config['bonus_per_topic'],
        ]);
    }

    if ($CURUSER['class'] >= $min_upload_class) {
        foreach ($_FILES['attachment']['name'] as $key => $name) {
            if (!empty($name)) {
                $size = intval($_FILES['attachment']['size'][$key]);
                $type = $_FILES['attachment']['type'][$key];
                $extension_error = $size_error = 0;
                $name = str_replace(' ', '_', $name);
                $accepted_file_types = [
                    'application/zip',
                    'application/x-zip',
                    'application/rar',
                    'application/x-rar',
                ];
                $accepted_file_extension = strrpos($name, '.');
                $file_extension = strtolower(substr($name, $accepted_file_extension));
                $name = preg_replace('#[^a-zA-Z0-9_-]#', '', $name);
                switch (true) {
                    case $size > $max_file_size:
                        $size_error = ($size_error + 1);
                        break;

                    case !in_array($file_extension, $accepted_file_extension) && false == $accepted_file_extension:
                        $extension_error = ($extension_error + 1);
                        break;

                    case $accepted_file_extension === 0:
                        $extension_error = ($extension_error + 1);
                        break;

                    case !in_array($type, $accepted_file_types):
                        $extension_error = ($extension_error + 1);
                        break;

                    default:
                        $name = substr($name, 0, -strlen($file_extension));
                        $upload_to = $upload_folder . $name . '(id-' . $post_id . ')' . $file_extension;
                        $values = [
                            'post_id' => $post_id,
                            'user_id' => $CURUSER['id'],
                            'file' => $name . '(id-' . $post_id . ')' . $file_extension,
                            'file_name' => $name,
                            'added' => TIME_NOW,
                            'extension' => ltrim($file_extension, '.'),
                            'size' => $size,
                        ];
                        $fluent->insertInto('attachments')
                               ->values($values)
                               ->execute();
                        copy($_FILES['attachment']['tmp_name'][$key], $upload_to);
                        chmod($upload_to, 0777);
                }
            }
        }
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
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id . ($extension_error !== 0 ? '&ee=' . $extension_error : '') . ($size_error !== 0 ? '&se=' . $size_error : ''));
    die();
}

$forum_name = $fluent->from('forums')
                     ->select(null)
                     ->select('name')
                     ->where('id = ?', $forum_id)
                     ->fetch('name');

$section_name = htmlsafechars($forum_name, ENT_QUOTES);

$HTMLOUT .= '
    <h1 class="has-text-centered">' . $lang['nt_new_topic_in'] . ' "<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $forum_id . '">' . $section_name . '</a>"</h1>
    <form method="post" action="' . $site_config['baseurl'] . '/forums.php?action=new_topic&amp;forum_id=' . $forum_id . '" enctype="multipart/form-data" accept-charset="utf-8">';

require_once FORUM_DIR . 'editor.php';

$HTMLOUT .= '
        <div class="has-text-centered margin20">
            <input type="submit" name="button" class="button is-small" value="' . $lang['fe_post'] . '">
        </div>
    </form>';
