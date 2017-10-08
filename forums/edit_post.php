<?php
if (!defined('BUNNY_FORUMS')) {
    setSessionVar('error', 'Access Not Allowed');
    header("Location: {$site_config['baseurl']}/index.php");
    exit();
}
global $lang;

$post_id = (isset($_GET['post_id']) ? intval($_GET['post_id']) : (isset($_POST['post_id']) ? intval($_POST['post_id']) : 0));
$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
$page = (isset($_GET['page']) ? intval($_GET['page']) : (isset($_POST['page']) ? intval($_POST['page']) : 0));
if (!is_valid_id($post_id) || !is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
//=== get the post info
$res_post = sql_query('SELECT p.added, p.user_id AS puser_id, p.body, p.icon, p.post_title, p.bbcode, p.post_history, p.edited_by, p.edit_date, p.edit_reason, p.staff_lock, a.file, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, t.topic_name, t.locked, t.user_id, t.topic_desc, f.min_class_read, f.min_class_write, f.id AS forum_id FROM posts AS p LEFT JOIN attachments as a ON p.id = a.post_id LEFT JOIN users AS u ON p.user_id = u.id LEFT JOIN topics AS t ON t.id = p.topic_id LEFT JOIN forums AS f ON t.forum_id = f.id WHERE p.id=' . sqlesc($post_id));
$arr_post = mysqli_fetch_assoc($res_post);
//=== get any attachments
$attachments = $extension_error = $size_error = '';
//=== if there are attachments, let's get them!
if (!empty($arr_post['file'])) {
    $attachments = '<tr><td><span>' . $lang['fe_attachments'] . ':</span></td>
    <td>
   <table class="table table-bordered table-striped">
    <tr>
    <td class="forum_head" colspan="2"><span>' . $lang['fe_delete'] . '</span></td>
    </tr>';
    $attachments_res = sql_query('SELECT id, file_name, extension, size FROM attachments WHERE post_id =' . sqlesc($post_id) . ' AND user_id = ' . sqlesc($arr_post['id']));
    while ($attachments_arr = mysqli_fetch_assoc($attachments_res)) {
        $attachments .= '
    <tr>
    <td>
    <input type="checkbox" name="attachment_to_delete[]" value="' . (int)$attachments_arr['id'] . '" /></td><td>
    <span>' . ($attachments_arr['extension'] === 'zip' ? ' <img src="' . $site_config['pic_base_url'] . 'forums/zip.gif" alt="' . $lang['fe_zip'] . '" title="' . $lang['fe_zip'] . '" width="18" /> ' : '<img src="' . $site_config['pic_base_url'] . 'forums/rar.gif" alt="' . $lang['fe_rar'] . '" title="' . $lang['fe_rar'] . '" width="18" />') . '
    <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=download_attachment&amp;id=' . (int)$attachments_arr['id'] . '" title="' . $lang['fe_download_attachment'] . '" target="_blank">' . htmlsafechars($attachments_arr['file_name']) . '</a> <span>[' . mksize($attachments_arr['size']) . ']</span></span></td>
    </tr>';
    }
    $attachments .= '</table></td></tr>';
}
//=== if staff or topic owner let them edit topic topic_name and topic_desc user_id
$can_edit = ($arr_post['puser_id'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF);
//=== stop them, they shouldn't be here lol
if ($CURUSER['class'] < $arr_post['min_class_read'] || $CURUSER['class'] < $arr_post['min_class_write']) {
    stderr($lang['gl_error'], $lang['fe_topic_not_found']);
}
if ($CURUSER['forum_post'] == 'no' || $CURUSER['suspended'] == 'yes') {
    stderr($lang['gl_error'], $lang['fe_your_no_post_right']);
}
if (!$can_edit) {
    stderr($lang['gl_error'], '' . $lang['fe_this_is_not_your_post_to_edit'] . '');
}
if ($arr_post['locked'] == 'yes') {
    stderr($lang['gl_error'], $lang['fe_this_topic_is_locked']);
}
if ($arr_post['staff_lock'] == 1) {
    stderr($lang['gl_error'], $lang['fe_this_post_is_staff_locked']);
}
$edited_by = $CURUSER['id'];
$edit_date = TIME_NOW;
$body = (isset($_POST['body']) ? $_POST['body'] : $arr_post['body']);
if ($can_edit) {
    $topic_name = strip_tags(isset($_POST['topic_name']) ? $_POST['topic_name'] : $arr_post['topic_name']);
    $topic_desc = strip_tags(isset($_POST['topic_desc']) ? $_POST['topic_desc'] : $arr_post['topic_desc']);
}
$post_title = strip_tags(isset($_POST['post_title']) ? $_POST['post_title'] : $arr_post['post_title']);
$icon = (isset($_POST['icon']) ? htmlsafechars($_POST['icon']) : htmlsafechars($arr_post['icon']));
$show_bbcode = (isset($_POST['show_bbcode']) ? $_POST['show_bbcode'] : $arr_post['bbcode']);
$edit_reason = strip_tags(isset($_POST['edit_reason']) ? ($_POST['edit_reason']) : '');
$show_edited_by = ((isset($_POST['show_edited_by']) && $_POST['show_edited_by'] == 'no' && $CURUSER['class'] == UC_MAX && $CURUSER['id'] == $arr_post['id']) ? 'no' : 'yes');
if (isset($_POST['button']) && $_POST['button'] == 'Edit') {
    if (empty($body)) {
        stderr($lang['gl_error'], $lang['fe_body_text_can_not_be_empty']);
    }
    $changed = '<span>' . $lang['fe_changed'] . '</span>';
    $not_changed = '<span>' . $lang['fe_not_changed'] . '</span>';
    $post_history = '<table class="table table-bordered table-striped">
    <tr>
    <td class="forum_head">#' . $post_id . '  ' . print_user_stuff($arr_post) . '</td>
    <td class="forum_head">' . (empty($arr_post['post_history']) ? '' . $lang['fe_first_post'] . '' : '' . $lang['fe_post_edited'] . '') . ' By: ' . print_user_stuff($CURUSER) . ' On: ' . date('l jS \of F Y h:i:s A', TIME_NOW) . ' UTC ' . ($post_title !== '' ? '&#160;&#160;&#160;&#160; ' . $lang['fe_title'] . ': <span>' . $post_title . '</span>' : '') . ($icon !== '' ? ' <img src="' . $site_config['pic_base_url'] . 'smilies/' . $icon . '.gif" alt="' . $icon . '" title="' . $icon . '" />' : '') . '</td>
    <tr>
    <td>' . (empty($arr_post['post_history']) ? ($can_edit ? '<span>Desc: ' . ($arr_post['topic_desc'] !== '' ? 'yes' : 'none') . '</span><br>' : '') . '<span>' . $lang['fe_title'] . ': ' . ($arr_post['post_title'] !== '' ? 'yes' : 'none') . '</span><br><span>' . $lang['fe_icon'] . ': ' . ($arr_post['icon'] !== '' ? 'yes' : 'none') . '</span><br><span>' . $lang['ep_bb_code'] . ': ' . ($arr_post['bbcode'] !== 'yes' ? 'off' : 'on') . '</span><br>' : ($can_edit ? '<span>Topic Name: ' . ((isset($_POST['topic_name']) && $_POST['topic_name'] !== $arr_post['topic_name']) ? $changed : $not_changed) . '</span><br><span>Desc: ' . ((isset($_POST['topic_desc']) && $_POST['topic_desc'] !== $arr_post['topic_desc']) ? $changed : $not_changed) . '</span><br>' : '') . '<span>' . $lang['fe_title'] . ': ' . ((isset($_POST['post_title']) && $_POST['post_title'] !== $arr_post['post_title']) ? $changed : $not_changed) . '</span><br><span>' . $lang['fe_icon'] . ': ' . ((isset($_POST['icon']) && $_POST['icon'] !== $arr_post['icon']) ? $changed : $not_changed) . '</span><br><span>' . $lang['ep_bb_code'] . ': ' . ((isset($_POST['show_bbcode']) && $_POST['show_bbcode'] !== $arr_post['bbcode']) ? $changed : $not_changed) . '</span><br><span>' . $lang['fe_body'] . ': ' . ((isset($_POST['body']) && $_POST['body'] !== $arr_post['body']) ? $changed : $not_changed) . '</span><br>') . '
    </td>
    <td>' . ($arr_post['bbcode'] == 'yes' ? format_comment($arr_post['body']) : format_comment_no_bbcode($arr_post['body'])) . '</td>
    </tr>
    </table><br>' . $arr_post['post_history'];
    //=== let the sysop have the power to not show they edited their own post if they wish...
    if ($show_edited_by == 'no' && $CURUSER['class'] == UC_MAX) {
        $edit_reason = htmlsafechars($arr_post['edit_reason']);
        $edited_by = htmlsafechars($arr_post['edited_by']);
        $edit_date = (int)$arr_post['edit_date'];
        $post_history = htmlsafechars($arr_post['post_history']);
    }
    sql_query('UPDATE posts SET body = ' . sqlesc($body) . ', icon = ' . sqlesc($icon) . ', post_title = ' . sqlesc($post_title) . ', bbcode = ' . sqlesc($show_bbcode) . ', edit_reason = ' . sqlesc($edit_reason) . ', edited_by = ' . sqlesc($edited_by) . ', edit_date = ' . sqlesc($edit_date) . ', post_history = ' . sqlesc($post_history) . ' WHERE id = ' . sqlesc($post_id));
    clr_forums_cache($post_id);
    $mc1->delete_value('forum_posts_' . $CURUSER['id']);
    //=== update topic stuff
    if ($can_edit) {
        sql_query('UPDATE topics SET topic_name = ' . sqlesc($topic_name) . ', topic_desc = ' . sqlesc($topic_desc) . ' WHERE id = ' . sqlesc($topic_id));
    }
    //=== stuff for file uploads
    if ($CURUSER['class'] >= $min_upload_class) {
        while (list($key, $name) = each($_FILES['attachment']['name'])) {
            if (!empty($name)) {
                $size = intval($_FILES['attachment']['size'][$key]);
                $type = $_FILES['attachment']['type'][$key];
                //=== make sure file is kosher
                $accepted_file_types = [
                    'application/zip',
                    'application/x-zip',
                    'application/rar',
                    'application/x-rar',
                ];
                $extension_error = $size_error = 0;
                //=== allowed file types (2 checks) but still can't really trust it
                $the_file_extension = strrpos($name, '.');
                $file_extension = strtolower(substr($name, $the_file_extension)); //===  make sure the name is only alphanumeric or _ or -
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
( ' . sqlesc($post_id) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($name . '(id-' . $post_id . ')' . $file_extension) . ', ' . sqlesc($name) . ', ' . TIME_NOW . ', ' . ($file_extension === '.zip' ? '\'zip\'' : '\'rar\'') . ', ' . $size . ')');
                        copy($_FILES['attachment']['tmp_name'][$key], $upload_to);
                        chmod($upload_to, 0777);
                }
            }
        }
    } //=== end attachment stuff
    //=== now to delete any atachments if selected:
    if (isset($_POST['attachment_to_delete'])) {
        $_POST['attachment_to_delete'] = (isset($_POST['attachment_to_delete']) ? $_POST['attachment_to_delete'] : '');
        $attachment_to_delete = [];
        foreach ($_POST['attachment_to_delete'] as $var) {
            $attachment_to_delete = intval($var);
            //=== get attachment info
            $attachments_res = sql_query('SELECT file FROM attachments WHERE id = ' . sqlesc($attachment_to_delete));
            $attachments_arr = mysqli_fetch_array($attachments_res);
            //=== delete the file
            unlink($upload_folder . $attachments_arr['file']);
            //=== delete them from the DB
            sql_query('DELETE FROM attachments WHERE id = ' . sqlesc($attachment_to_delete) . ' AND post_id = ' . sqlesc($post_id));
        }
    } //=== end attachment stuff
    //=== only write to staff actions if it's a staff editing and not their own post
    if ($CURUSER['class'] >= UC_STAFF && $CURUSER['id'] !== $arr_post['user_id']) {
        write_log('' . $CURUSER['username'] . ' ' . $lang['ep_edited_a_post_by'] . ' ' . htmlsafechars($arr_post['username']) . '. ' . $lang['ep_here_is_the'] . ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . $post_id . '&amp;forum_id=' . (int)$arr_post['forum_id'] . '&amp;topic_id=' . $topic_id . '">' . $lang['ep_link'] . '</a> ' . $lang['ep_to_the_post_history'] . '', $CURUSER['id']);
    }
    //header('Location: forums.php?action=view_topic&topic_id='.$topic_id.'&page='.$page.'#'.$post_id);
    header('Location: ' . $site_config['baseurl'] . '/forums.php?action=view_topic&topic_id=' . $topic_id . ($extension_error !== 0 ? '&ee=' . $extension_error : '') . ($size_error !== 0 ? '&se=' . $size_error : ''));
    exit();
}
$HTMLOUT .= '<table class="table table-bordered table-striped">
    <tr><td class="embedded">
    <h1>' . $lang['ep_edit_post_by'] . ':' . print_user_stuff($arr_post) . ' ' . $lang['ep_in_topic'] . '
    "<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($arr_post['topic_name'], ENT_QUOTES) . '</a>"</h1>
    <form method="post" action="' . $site_config['baseurl'] . '/forums.php?action=edit_post&amp;topic_id=' . $topic_id . '&amp;post_id=' . $post_id . '&amp;page=' . $page . '" enctype="multipart/form-data">

    <table class="table table-bordered table-striped">
    <tr><td class="forum_head_dark" colspan="2">' . $lang['fe_compose'] . '</td></tr>
    <tr><td><span>' . $lang['fe_icon'] . '</span></td>
    <td>
    <table class="table table-bordered table-striped">
    <tr>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/smile1.gif" alt="' . $lang['fe_smile'] . '" title="' . $lang['fe_smile'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/grin.gif" alt="' . $lang['fe_smilee_grin'] . '" title="' . $lang['fe_smilee_grin'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/tongue.gif" alt="' . $lang['fe_smilee_tongue'] . '" title="' . $lang['fe_smilee_tongue'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/cry.gif" alt="' . $lang['fe_smilee_cry'] . '" title="' . $lang['fe_smilee_cry'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/wink.gif" alt="' . $lang['fe_smilee_wink'] . '" title="' . $lang['fe_smilee_wink'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/rolleyes.gif" alt="' . $lang['fe_smilee_roll_eyes'] . '" title="' . $lang['fe_smilee_roll_eyes'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/blink.gif" alt="' . $lang['fe_smilee_blink'] . '" title="' . $lang['fe_smilee_blink'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/bow.gif" alt="' . $lang['fe_smilee_bow'] . '" title="' . $lang['fe_smilee_bow'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/clap2.gif" alt="' . $lang['fe_smilee_clap'] . '" title="' . $lang['fe_smilee_clap'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/hmmm.gif" alt="' . $lang['fe_smilee_hmm'] . '" title="' . $lang['fe_smilee_hmm'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/devil.gif" alt="' . $lang['fe_smilee_devil'] . '" title="' . $lang['fe_smilee_devil'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/angry.gif" alt="' . $lang['fe_smilee_angry'] . '" title="' . $lang['fe_smilee_angry'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/shit.gif" alt="' . $lang['fe_smilee_shit'] . '" title="' . $lang['fe_smilee_shit'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/sick.gif" alt="' . $lang['fe_smilee_sick'] . '" title="' . $lang['fe_smilee_sick'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/tease.gif" alt="' . $lang['fe_smilee_tease'] . '" title="' . $lang['fe_smilee_tease'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/love.gif" alt="' . $lang['fe_smilee_love'] . '" title="' . $lang['fe_smilee_love'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/ohmy.gif" alt="' . $lang['fe_smilee_oh_my'] . '" title="' . $lang['fe_smilee_oh_my'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/yikes.gif" alt="' . $lang['fe_smilee_yikes'] . '" title="' . $lang['fe_smilee_yikes'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/spider.gif" alt="' . $lang['fe_smilee_spider'] . '" title="' . $lang['fe_smilee_spider'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/wall.gif" alt="' . $lang['fe_smilee_wall'] . '" title="' . $lang['fe_smilee_wall'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/idea.gif" alt="' . $lang['fe_smilee_idea'] . '" title="' . $lang['fe_smilee_idea'] . '" /></td>
    <td><img src="' . $site_config['pic_base_url'] . 'smilies/question.gif" alt="' . $lang['fe_smilee_question'] . '" title="' . $lang['fe_smilee_question'] . '" /></td>
    </tr>
    <tr>
    <td><input type="radio" name="icon" value="smile1"' . ($icon == 'smile1' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="grin"' . ($icon == 'grin' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="tongue"' . ($icon == 'tongue' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="cry"' . ($icon == 'cry' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="wink"' . ($icon == 'wink' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="rolleyes"' . ($icon == 'rolleyes' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="blink"' . ($icon == 'blink' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="bow"' . ($icon == 'bow' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="clap2"' . ($icon == 'clap2' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="hmmm"' . ($icon == 'hmmm' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="devil"' . ($icon == 'devil' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="angry"' . ($icon == 'angry' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="shit"' . ($icon == 'shit' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="sick"' . ($icon == 'sick' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="tease"' . ($icon == 'tease' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="love"' . ($icon == 'love' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="ohmy"' . ($icon == 'ohmy' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="yikes"' . ($icon == 'yikes' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="spider"' . ($icon == 'spider' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="wall"' . ($icon == 'wall' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="idea"' . ($icon == 'idea' ? ' checked="checked"' : '') . ' /></td>
    <td><input type="radio" name="icon" value="question"' . ($icon == 'question' ? ' checked="checked"' : '') . ' /></td>
    </tr>
    </table>
    </td></tr>
    ' . ($can_edit ? '<tr><td><span>' . $lang['fe_name'] . '</span></td>
    <td><input type="text"  name="topic_name" value="' . trim(strip_tags($topic_name)) . '" class="text_default" /></td></tr>
    <tr><td><span>' . $lang['fe_desc'] . '</span></td>
    <td><input type="text" maxlength="120" name="topic_desc" value="' . trim(strip_tags($topic_desc)) . '" class="text_default" /> [ optional ]</td></tr>' : '') . '
    <tr><td><span>' . $lang['fe_title'] . '</span></td>
    <td><input type="text" maxlength="120" name="post_title" value="' . trim(strip_tags($post_title)) . '" class="text_default" /> [ optional ]</td></tr>
    <tr><td><span>' . $lang['fe_bbcode'] . '</span></td>
    <td>
    <input type="radio" name="show_bbcode" value="yes" ' . ($show_bbcode == 'yes' ? 'checked="checked"' : '') . ' /> ' . $lang['fe_yes_enable'] . ' ' . $lang['fe_bbcode_in_post'] . '
    <input type="radio" name="show_bbcode" value="no" ' . ($show_bbcode == 'no' ? 'checked="checked"' : '') . ' /> ' . $lang['fe_no_disable'] . ' ' . $lang['fe_bbcode_in_post'] . '
    </td></tr>
    <tr><td><span>' . $lang['fe_reason'] . '</span></td>
    <td><input type="text" maxlength="20" name="edit_reason" value="' . trim(strip_tags($edit_reason)) . '" class="text_default" /> [ optional ]
    &#160;&#160;&#160;&#160;
    </td></tr>
    ' . (($CURUSER['class'] == UC_MAX or $CURUSER['id'] == $arr_post['id']) ? '<tr><td><span>Edit By</span></td>
    <td>
    <input type="radio" name="show_edited_by" value="yes"' . ($show_edited_by == 'yes' ? ' checked="checked"' : '') . ' /> yes
    <input type="radio" name="show_edited_by" value="no"' . ($show_edited_by == 'no' ? ' checked="checked"' : '') . ' /> no
    </td></tr>' : '') . $attachments . '
    <tr><td><span>' . $lang['fe_body'] . '</span></td>
    <td>' . BBcode($body) . $more_options . '
    </td></tr>
    <tr><td colspan="2">
    <input type="submit" name="button" class="button_tiny" value="Save Changes" onmouseover="this.className=\'button_tiny_hover\'" onmouseout="this.className=\'button_tiny\'" />
    </td></tr>
    </table></form>';
//=== get last ten posts
$res_posts = sql_query('SELECT p.id AS post_id, p.user_id, p.added, p.body, p.icon, p.post_title, p.bbcode, p.anonymous, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.avatar, u.chatpost, u.leechwarn, u.pirate, u.king, u.offensive_avatar FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND' : '')) . '  topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1, 10');
$HTMLOUT .= '<br><span>' . $lang['fe_last_ten_posts_in_reverse_order'] . '</span>
    <table class="table table-bordered table-striped">';
//=== lets start the loop \o/
while ($arr = mysqli_fetch_assoc($res_posts)) {
    //=== change colors
    $HTMLOUT .= '<tr><td class="forum_head"><a name="' . (int)$arr['post_id'] . '"></a>
        <span>#' . (int)$arr['post_id'] . '
        <span>' . ($arr['anonymous'] == 'yes' ? '<i>' . $lang['fe_anonymous'] . '</i>' : htmlsafechars($arr['username'])) . '</span></span></td>
        <td class="forum_head"><span> ' . $lang['fe_posted_on'] . ': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span></td></tr>';
    $width = 100;
    if ($arr['anonymous'] == 'yes') {
        if ($CURUSER['class'] < UC_STAFF && $arr['user_id'] != $CURUSER['id']) {
            $HTMLOUT .= '<tr><td><img style="max-width:' . $width . 'px;" src="' . $site_config['pic_base_url'] . 'anonymous_1.jpg" alt="avatar" /><br><i>' . $lang['fe_anonymous'] . '</i></td>';
        } else {
            $HTMLOUT .= '<tr><td>' . avatar_stuff($arr) . '<br><i>Anonymous </i>[' . print_user_stuff($arr) . ']</td>';
        }
    } else {
        $HTMLOUT .= '<tr><td>' . avatar_stuff($arr) . '<br>' . print_user_stuff($arr) . '</td>';
    }
    $HTMLOUT .= '<td colspan="2">' . ($arr['bbcode'] == 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body'])) . '</td></tr>';
}
//=== end while loop
$HTMLOUT .= '</table><br></td></tr></table><br>';
