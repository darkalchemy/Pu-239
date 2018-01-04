<?php
global $CURUSER, $site_config, $cache, $lang;

$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));
if (!is_valid_id($forum_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

if ($CURUSER['forum_post'] == 'no' || $CURUSER['suspended'] == 'yes') {
    stderr($lang['gl_error'], $lang['fe_your_no_post_right']);
}
$extension_error = $size_error = '';

$topic_name = strip_tags(isset($_POST['topic_name']) ? $_POST['topic_name'] : '');
$topic_desc = strip_tags(isset($_POST['topic_desc']) ? $_POST['topic_desc'] : '');

$post_title = strip_tags(isset($_POST['post_title']) ? $_POST['post_title'] : '');
$icon = htmlsafechars(isset($_POST['icon']) ? $_POST['icon'] : '');
$body = (isset($_POST['body']) ? $_POST['body'] : '');
$ip = getip();
$bb_code = (isset($_POST['bb_code']) && $_POST['bb_code'] == 'no' ? 'no' : 'yes');
$anonymous = (isset($_POST['anonymous']) && $_POST['anonymous'] != '' ? 'yes' : 'no');
//=== poll stuff
$poll_question = strip_tags(isset($_POST['poll_question']) ? trim($_POST['poll_question']) : '');
$poll_answers = strip_tags(isset($_POST['poll_answers']) ? trim($_POST['poll_answers']) : '');
$poll_ends = (isset($_POST['poll_ends']) ? (($_POST['poll_ends'] > 168) ? 1356048000 : (TIME_NOW + $_POST['poll_ends'] * 86400)) : '');
$poll_starts = (isset($_POST['poll_starts']) ? (($_POST['poll_starts'] === 0) ? TIME_NOW : (TIME_NOW + $_POST['poll_starts'] * 86400)) : '');
$poll_starts = ($poll_starts > ($poll_ends + 1) ? TIME_NOW : $poll_starts);
$change_vote = ((isset($_POST['change_vote']) && $_POST['change_vote'] === 'yes') ? 'yes' : 'no');
$subscribe = (isset($_POST['subscribe']) && $_POST['subscribe'] === 'yes' ? 'yes' : 'no');
if (isset($_POST['button']) && $_POST['button'] == 'Post') {
    //=== make sure they are posting something
    if ($body === '') {
        stderr($lang['gl_error'], $lang['fe_no_body_txt']);
    }
    if ($topic_name === '') {
        stderr($lang['gl_error'], $lang['fe_no_topic_name']);
    }
    //=== if no poll give a dummy id
    $poll_id = 0;
    //=== stuff for polls
    if ($poll_answers !== '') {
        //=== make it an array with a max of 20 options
        $break_down_poll_options = explode("\n", $poll_answers);
        //=== be sure there are no blank options
        for ($i = 0; $i < count($break_down_poll_options); ++$i) {
            if (strlen($break_down_poll_options[ $i ]) < 2) {
                stderr($lang['gl_error'], $lang['fe_no_blank_lines_in_poll']);
            }
        }
        if ($i > 20 || $i < 2) {
            stderr($lang['gl_error'], '' . $lang['fe_there_is_min_max_options'] . ' ' . $i . '.');
        }
        $multi_options = ((isset($_POST['multi_options']) && $_POST['multi_options'] <= $i) ? intval($_POST['multi_options']) : 1);
        //=== serialize it and slap it in the DB FFS!
        $poll_options = serialize($break_down_poll_options);
        sql_query('INSERT INTO `forum_poll` (`user_id` ,`question` ,`poll_answers` ,`number_of_options` ,`poll_starts` ,`poll_ends` ,`change_vote` ,`multi_options`)
                    VALUES (' . $CURUSER['id'] . ', ' . sqlesc($poll_question) . ', ' . sqlesc($poll_options) . ', ' . $i . ', ' . $poll_starts . ', ' . $poll_ends . ', \'' . $change_vote . '\', ' . $multi_options . ')');
        $poll_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
    }
    sql_query('INSERT INTO topics (`id`, `user_id`, `topic_name`, `last_post`, `forum_id`, `topic_desc`, `poll_id`, `anonymous`) VALUES (NULL, ' . $CURUSER['id'] . ', ' . sqlesc($topic_name) . ', ' . $CURUSER['id'] . ', ' . $forum_id . ', ' . sqlesc($topic_desc) . ', ' . $poll_id . ', ' . sqlesc($anonymous) . ')');
    $topic_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
    sql_query('INSERT INTO `posts` ( `topic_id` , `user_id` , `added` , `body` , `icon` , `post_title` , `bbcode` , `ip` , `anonymous`) VALUES
            (' . sqlesc($topic_id) . ', ' . $CURUSER['id'] . ', ' . TIME_NOW . ', ' . sqlesc($body) . ', ' . sqlesc($icon) . ',  ' . sqlesc($post_title) . ', ' . sqlesc($bb_code) . ', ' . ipToStorageFormat($ip) . ', ' . sqlesc($anonymous) . ')');
    $post_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
    sql_query('UPDATE usersachiev SET forumtopics = forumtopics+1 WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    clr_forums_cache($post_id);
    clr_forums_cache($forum_id);
    $cache->delete('forum_posts_' . $CURUSER['id']);
    sql_query('UPDATE `topics` SET first_post =  ' . sqlesc($post_id) . ', last_post = ' . sqlesc($post_id) . ', post_count = 1 WHERE id=' . sqlesc($topic_id));
    sql_query('UPDATE `forums` SET post_count = post_count +1, topic_count = topic_count + 1 WHERE id =' . sqlesc($forum_id));
    if ($site_config['autoshout_on'] == 1) {
        $message = $CURUSER['username'] . ' ' . $lang['nt_created_new_topic'] . " [url={$site_config['baseurl']}/forums.php?action=view_topic&topic_id=$topic_id&page=last]{$topic_name}[/url]";
        //////remember to edit the ids to your staffforum ids :)
        if (!in_array($forum_id, $site_config['staff_forums'])) {
            autoshout($message);
        }
    }
    if ($site_config['seedbonus_on'] == 1) {
        sql_query('UPDATE users SET seedbonus = seedbonus+' . sqlesc($site_config['bonus_per_topic']) . ' WHERE id =  ' . sqlesc($CURUSER['id'] . '')) or sqlerr(__FILE__, __LINE__);
        $update['seedbonus'] = ($CURUSER['seedbonus'] + $site_config['bonus_per_topic']);
        $cache->update_row('userstats_' . $CURUSER['id'], [
            'seedbonus' => $update['seedbonus'],
        ], $site_config['expires']['u_stats']);
        $cache->update_row('user_stats_' . $CURUSER['id'], [
            'seedbonus' => $update['seedbonus'],
        ], $site_config['expires']['user_stats']);
    }
    //=== stuff for file uploads
    if ($CURUSER['class'] >= $min_upload_class) {
        //=== make sure file is kosher
        while (list($key, $name) = each($_FILES['attachment']['name'])) {
            if (!empty($name)) {
                $size = intval($_FILES['attachment']['size'][ $key ]);
                $type = $_FILES['attachment']['type'][ $key ];
                //=== make sure file is kosher
                $extension_error = $size_error = 0;
                //=== get rid of spaces
                $name = str_replace(' ', '_', $name);
                //=== allowed file types (2 checks) but still can't really trust it
                $accepted_file_types = [
                    'application/zip',
                    'application/x-zip',
                    'application/rar',
                    'application/x-rar',
                ];
                $accepted_file_extension = strrpos($name, '.');
                $file_extension = strtolower(substr($name, $accepted_file_extension));
                //===  make sure the name is only alphanumeric or _ or -
                $name = preg_replace('#[^a-zA-Z0-9_-]#', '', $name); // hell, it could even be 0_0 if it wanted to!
                switch (true) {
                    case $size > $max_file_size:
                        $size_error = ($size_error + 1);
                        break;

                    case !in_array($file_extension, $accepted_file_extension) && $accepted_file_extension == false:
                        $extension_error = ($extension_error + 1);
                        break;

                    case $accepted_file_extension === 0:
                        $extension_error = ($extension_error + 1);
                        break;

                    case !in_array($type, $accepted_file_types):
                        $extension_error = ($extension_error + 1);
                        break;

                    default:
                        //=== woohoo passed all our silly tests but just to be sure, let's mess it up a bit ;)
                        //=== get rid of the file extension
                        $name = substr($name, 0, -strlen($file_extension));
                        $upload_to = $upload_folder . $name . '(id-' . $post_id . ')' . $file_extension;
                        //===plop it into the DB all safe and snuggly
                        sql_query('INSERT INTO `attachments` (`post_id`, `user_id`, `file`, `file_name`, `added`, `extension`, `size`) VALUES
( ' . $post_id . ', ' . $CURUSER['id'] . ', ' . sqlesc($name . '(id-' . $post_id . ')' . $file_extension) . ', ' . sqlesc($name) . ', ' . TIME_NOW . ', ' . ($file_extension === '.zip' ? '\'zip\'' : '\'rar\'') . ', ' . $size . ')');
                        copy($_FILES['attachment']['tmp_name'][ $key ], $upload_to);
                        chmod($upload_to, 0777);
                }
            }
        }
    } //=== end attachment stuff
    if ($subscribe == 'yes') {
        sql_query('INSERT INTO `subscriptions` (`user_id`, `topic_id`) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($topic_id) . ')');
    }
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id . ($extension_error !== 0 ? '&ee=' . $extension_error : '') . ($size_error !== 0 ? '&se=' . $size_error : ''));
    die();
}
$res = sql_query('SELECT name FROM forums WHERE id=' . sqlesc($forum_id));
$arr = mysqli_fetch_assoc($res);
$section_name = htmlsafechars($arr['name'], ENT_QUOTES);
$HTMLOUT .= '
    <h1 class="has-text-centered">' . $lang['nt_new_topic_in'] . ' "<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $forum_id . '">' . $section_name . '</a>"</h1>
    <form method="post" action="' . $site_config['baseurl'] . '/forums.php?action=new_topic&amp;forum_id=' . $forum_id . '" enctype="multipart/form-data">
    <table class="table table-bordered table-striped">
        <tr>
            <td class="forum_head_dark" colspan="2">' . $lang['fe_compose'] . '</td>
        </tr>
        <tr>
            <td>
                <span>' . $lang['fe_icon'] . '</span>
            </td>
            <td>
                <div class="flex flex-grid">
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/smile1.gif" alt="' . $lang['fe_smile'] . '" class="bottom10" title="' . $lang['fe_smile'] . '" />
                        <input type="radio" name="icon" value="smile1"' . ($icon === 'smile1' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/grin.gif" alt="' . $lang['fe_smilee_grin'] . '" class="bottom10" title="' . $lang['fe_smilee_grin'] . '" />
                        <input type="radio" name="icon" value="grin"' . ($icon === 'grin' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/tongue.gif" alt="' . $lang['fe_smilee_tongue'] . '" class="bottom10" title="' . $lang['fe_smilee_tongue'] . '" />
                        <input type="radio" name="icon" value="tongue"' . ($icon === 'tongue' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/cry.gif" alt="' . $lang['fe_smilee_cry'] . '" class="bottom10" title="' . $lang['fe_smilee_cry'] . '" />
                        <input type="radio" name="icon" value="cry"' . ($icon === 'cry' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/wink.gif" alt="' . $lang['fe_smilee_wink'] . '" class="bottom10" title="' . $lang['fe_smilee_wink'] . '" />
                        <input type="radio" name="icon" value="wink"' . ($icon === 'wink' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/rolleyes.gif" alt="' . $lang['fe_smilee_roll_eyes'] . '" class="bottom10" title="' . $lang['fe_smilee_roll_eyes'] . '" />
                        <input type="radio" name="icon" value="rolleyes"' . ($icon === 'rolleyes' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/blink.gif" alt="' . $lang['fe_smilee_blink'] . '" class="bottom10" title="' . $lang['fe_smilee_blink'] . '" />
                        <input type="radio" name="icon" value="blink"' . ($icon === 'blink' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/bow.gif" alt="' . $lang['fe_smilee_bow'] . '" class="bottom10" title="' . $lang['fe_smilee_bow'] . '" />
                        <input type="radio" name="icon" value="bow"' . ($icon === 'bow' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/clap2.gif" alt="' . $lang['fe_smilee_clap'] . '" class="bottom10" title="' . $lang['fe_smilee_clap'] . '" />
                        <input type="radio" name="icon" value="clap2"' . ($icon === 'clap2' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/hmmm.gif" alt="' . $lang['fe_smilee_hmm'] . '" class="bottom10" title="' . $lang['fe_smilee_hmm'] . '" />
                        <input type="radio" name="icon" value="hmmm"' . ($icon === 'hmmm' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/devil.gif" alt="' . $lang['fe_smilee_devil'] . '" class="bottom10" title="' . $lang['fe_smilee_devil'] . '" />
                        <input type="radio" name="icon" value="devil"' . ($icon === 'devil' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/angry.gif" alt="' . $lang['fe_smilee_angry'] . '" class="bottom10" title="' . $lang['fe_smilee_angry'] . '" />
                        <input type="radio" name="icon" value="angry"' . ($icon === 'angry' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/shit.gif" alt="' . $lang['fe_smilee_shit'] . '" class="bottom10" title="' . $lang['fe_smilee_shit'] . '" />
                        <input type="radio" name="icon" value="shit"' . ($icon === 'shit' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/sick.gif" alt="' . $lang['fe_smilee_sick'] . '" class="bottom10" title="' . $lang['fe_smilee_sick'] . '" />
                        <input type="radio" name="icon" value="sick"' . ($icon === 'sick' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/tease.gif" alt="' . $lang['fe_smilee_tease'] . '" class="bottom10" title="' . $lang['fe_smilee_tease'] . '" />
                        <input type="radio" name="icon" value="tease"' . ($icon === 'tease' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/love.gif" alt="' . $lang['fe_smilee_love'] . '" class="bottom10" title="' . $lang['fe_smilee_love'] . '" />
                        <input type="radio" name="icon" value="love"' . ($icon === 'love' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/ohmy.gif" alt="' . $lang['fe_smilee_oh_my'] . '" class="bottom10" title="' . $lang['fe_smilee_oh_my'] . '" />
                        <input type="radio" name="icon" value="ohmy"' . ($icon === 'ohmy' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/yikes.gif" alt="' . $lang['fe_smilee_yikes'] . '" class="bottom10" title="' . $lang['fe_smilee_yikes'] . '" />
                        <input type="radio" name="icon" value="yikes"' . ($icon === 'yikes' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/spider.gif" alt="' . $lang['fe_smilee_spider'] . '" class="bottom10" title="' . $lang['fe_smilee_spider'] . '" />
                        <input type="radio" name="icon" value="spider"' . ($icon === 'spider' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/wall.gif" alt="' . $lang['fe_smilee_wall'] . '" class="bottom10" title="' . $lang['fe_smilee_wall'] . '" />
                        <input type="radio" name="icon" value="wall"' . ($icon === 'wall' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/idea.gif" alt="' . $lang['fe_smilee_idea'] . '" class="bottom10" title="' . $lang['fe_smilee_idea'] . '" />
                        <input type="radio" name="icon" value="idea"' . ($icon === 'idea' ? ' checked' : '') . ' />
                    </span>
                    <span class="flex flex-column flex-center">
                        <img src="' . $site_config['pic_base_url'] . 'smilies/question.gif" alt="' . $lang['fe_smilee_question'] . '" class="bottom10" title="' . $lang['fe_smilee_question'] . '" />
                        <input type="radio" name="icon" value="question"' . ($icon === 'question' ? ' checked' : '') . ' />
                    </span>
                </div>
            </td>
        </tr>
    <tr><td><span>' . $lang['fe_name'] . '</span></td>
    <td><input type="text" size="80"  name="topic_name" value="' . trim(strip_tags($topic_name)) . '" class="text_default" /></td></tr>
    <tr><td><span>' . $lang['fe_desc'] . '</span></td>
    <td><input type="text" size="80" maxlength="120" name="topic_desc" value="' . trim(strip_tags($topic_desc)) . '" class="text_default" /> [ optional ]</td></tr>
    <tr><td><span>' . $lang['fe_title'] . '</span></td>
    <td><input type="text" size="80" maxlength="120" name="post_title" value="' . trim(strip_tags($post_title)) . '" class="text_default" /> [ optional ]</td></tr>
    <tr><td><span>' . $lang['fe_bbcode'] . '</span></td>
    <td>
    <input type="radio" name="bb_code" value="yes"' . ($bb_code === 'yes' ? ' checked' : '') . ' /> ' . $lang['fe_yes_enable'] . ' ' . $lang['fe_bbcode_in_post'] . '
    <input type="radio" name="bb_code" value="no"' . ($bb_code === 'no' ? ' checked' : '') . ' /> ' . $lang['fe_no_disable'] . ' ' . $lang['fe_bbcode_in_post'] . '
    </td></tr>
    <tr><td><span>' . $lang['fe_body'] . '</span></td>
    <td>' . BBcode($body) . $more_options . '</td></tr>
    <tr><td colspan="2"  >
   <!- Anonymous  ->
   ' . $lang['fe_anonymous_topic'] . ' : <input type="checkbox" name="anonymous" value="yes" /><br>
   <img src="' . $site_config['pic_base_url'] . 'forums/subscribe.gif" alt="+" title="+" /> ' . $lang['fe_subscrib_to_tread'] . '
    <input type="radio" name="subscribe" value="yes"' . ($subscribe === 'yes' ? ' checked' : '') . ' />yes
    <input type="radio" name="subscribe" value="no"' . ($subscribe === 'no' ? ' checked' : '') . ' />no <br>
    <input type="submit" name="button" class="button is-small" value="' . $lang['fe_post'] . '" in/>
   </td></tr>
    </table></form>';
