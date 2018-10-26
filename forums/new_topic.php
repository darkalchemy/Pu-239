<?php

global $lang, $CURUSER, $site_config;

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
$bb_code = isset($_POST['bb_code']) && $_POST['bb_code'] == 'no' ? 'no' : 'yes';
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

    if ($site_config['autoshout_on'] === 1) {
        $message = $CURUSER['username'] . ' ' . $lang['nt_created_new_topic'] . " [url={$site_config['baseurl']}/forums.php?action=view_topic&topic_id=$topic_id&page=last]{$topic_name}[/url]";
        if (!in_array($forum_id, $site_config['staff_forums'])) {
            autoshout($message);
        }
    }
    if ($site_config['seedbonus_on'] === 1) {
        $set = [
            'seedbonus' => new Envms\FluentPDO\Literal('seedbonus + ' . $site_config['bonus_per_topic']),
        ];
        $fluent->update('users')
            ->set($set)
            ->where('id = ?', $CURUSER['id'])
            ->execute();
        $cache->update_row('user' . $CURUSER['id'], [
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
    <form method="post" action="' . $site_config['baseurl'] . '/forums.php?action=new_topic&amp;forum_id=' . $forum_id . '" enctype="multipart/form-data">';

$HTMLOUT .= main_table('
        <tr>
            <td class="w15">
                <span>' . $lang['fe_icon'] . '</span>
            </td>
            <td>
                <div class="level-center">
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/smile1.gif" alt="' . $lang['fe_smile'] . '" title="' . $lang['fe_smile'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="smile1"' . ('smile1' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/grin.gif" alt="' . $lang['fe_smilee_grin'] . '" title="' . $lang['fe_smilee_grin'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="grin"' . ('grin' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/tongue.gif" alt="' . $lang['fe_smilee_tongue'] . '" title="' . $lang['fe_smilee_tongue'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="tongue"' . ('tongue' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/cry.gif" alt="' . $lang['fe_smilee_cry'] . '" title="' . $lang['fe_smilee_cry'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="cry"' . ('cry' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/wink.gif" alt="' . $lang['fe_smilee_wink'] . '" title="' . $lang['fe_smilee_wink'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="wink"' . ('wink' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/rolleyes.gif" alt="' . $lang['fe_smilee_roll_eyes'] . '" title="' . $lang['fe_smilee_roll_eyes'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="rolleyes"' . ('rolleyes' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/blink.gif" alt="' . $lang['fe_smilee_blink'] . '" title="' . $lang['fe_smilee_blink'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="blink"' . ('blink' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/bow.gif" alt="' . $lang['fe_smilee_bow'] . '" title="' . $lang['fe_smilee_bow'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="bow"' . ('bow' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/clap2.gif" alt="' . $lang['fe_smilee_clap'] . '" title="' . $lang['fe_smilee_clap'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="clap2"' . ('clap2' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/hmmm.gif" alt="' . $lang['fe_smilee_hmm'] . '" title="' . $lang['fe_smilee_hmm'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="hmmm"' . ('hmmm' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/devil.gif" alt="' . $lang['fe_smilee_devil'] . '" title="' . $lang['fe_smilee_devil'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="devil"' . ('devil' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/angry.gif" alt="' . $lang['fe_smilee_angry'] . '" title="' . $lang['fe_smilee_angry'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="angry"' . ('angry' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="' . $lang['fe_smilee_shit'] . '" title="' . $lang['fe_smilee_shit'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="shit"' . ('shit' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/sick.gif" alt="' . $lang['fe_smilee_sick'] . '" title="' . $lang['fe_smilee_sick'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="sick"' . ('sick' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/tease.gif" alt="' . $lang['fe_smilee_tease'] . '" title="' . $lang['fe_smilee_tease'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="tease"' . ('tease' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/love.gif" alt="' . $lang['fe_smilee_love'] . '" title="' . $lang['fe_smilee_love'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="love"' . ('love' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/ohmy.gif" alt="' . $lang['fe_smilee_oh_my'] . '" title="' . $lang['fe_smilee_oh_my'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="ohmy"' . ('ohmy' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/yikes.gif" alt="' . $lang['fe_smilee_yikes'] . '" title="' . $lang['fe_smilee_yikes'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="yikes"' . ('yikes' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/spider.gif" alt="' . $lang['fe_smilee_spider'] . '" title="' . $lang['fe_smilee_spider'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="spider"' . ('spider' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/wall.gif" alt="' . $lang['fe_smilee_wall'] . '" title="' . $lang['fe_smilee_wall'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="wall"' . ('wall' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/idea.gif" alt="' . $lang['fe_smilee_idea'] . '" title="' . $lang['fe_smilee_idea'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="idea"' . ('idea' === $icon ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/question.gif" alt="' . $lang['fe_smilee_question'] . '" title="' . $lang['fe_smilee_question'] . '" class="tooltipper icon bottom10" />
                        <input type="radio" name="icon" value="question"' . ('question' === $icon ? ' checked' : '') . ' />
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <span>' . $lang['fe_name'] . '</span>
            </td>
            <td>
                <input type="text" name="topic_name" value="' . trim(strip_tags($topic_name)) . '" class="w-100" placeholder="required" />
            </td>
        </tr>
        <tr>
            <td>
                <span>' . $lang['fe_desc'] . '</span>
            </td>
            <td>
                <input type="text" maxlength="120" name="topic_desc" value="' . trim(strip_tags($topic_desc)) . '" class="w-100" placeholder="optional" />
            </td>
        </tr>
        <tr>
            <td>
                <span>' . $lang['fe_title'] . '</span>
            </td>
            <td>
                <input type="text" maxlength="120" name="post_title" value="' . trim(strip_tags($post_title)) . '" class="w-100" placeholder="optional" />
            </td>
        </tr>
        <tr>
            <td>       
                <span>' . $lang['fe_bbcode'] . '</span>
            </td>
            <td>
                <div>
                    <input type="radio" name="bb_code" value="yes"' . ($bb_code === 'yes' ? ' checked' : '') . ' /> Allow ' . $lang['fe_bbcode_in_post'] . '
                </div>
                <div>
                    <input type="radio" name="bb_code" value="no"' . ($bb_code === 'no' ? ' checked' : '') . ' /> No ' . $lang['fe_bbcode_in_post'] . '
                </div>
            </td>
        </tr>
        <tr>
            <td><span>' . $lang['fe_body'] . '</span></td>
            <td class="is-paddingless">' . BBcode($body) . $more_options . '</td>
        </tr>
        <tr>
            <td>
                Anonymous
            </td>
            <td>
                <div class="level-left">
                    <span class="level-center">
                        <input type="checkbox" name="anonymous" value="yes" class="right10" />
                        ' . $lang['fe_anonymous_topic'] . '
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                ' . $lang['fe_subscrib_to_tread'] . '
            </td>
            <td>
                <div class="level-left">
                    <span class="level-center flex-vertical right10">
                        yes
                        <input type="radio" name="subscribe" value="yes"' . ('yes' === $subscribe ? ' checked' : '') . ' />
                    </span>
                    <span class="level-center flex-vertical margin10">
                        no
                        <input type="radio" name="subscribe" value="no"' . ('no' === $subscribe ? ' checked' : '') . ' />
                    </span>
                </div>
            </td>
        </tr>');

$HTMLOUT .= '
        <div class="has-text-centered margin20">
            <input type="submit" name="button" class="button is-small" value="' . $lang['fe_post'] . '" />
        </div>
    </form>';
