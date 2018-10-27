<?php

global $lang;

$post_id = (isset($_GET['post_id']) ? intval($_GET['post_id']) : (isset($_POST['post_id']) ? intval($_POST['post_id']) : 0));
$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
$page = (isset($_GET['page']) ? intval($_GET['page']) : (isset($_POST['page']) ? intval($_POST['page']) : 0));
if (!is_valid_id($post_id) || !is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
//=== get the post info
$res_post = sql_query('SELECT p.added, p.user_id AS puser_id, p.body, p.icon, p.post_title, p.bbcode, p.post_history, p.edited_by, p.edit_date, p.edit_reason, p.staff_lock, a.file, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, t.topic_name, t.locked, t.user_id, t.topic_desc, f.min_class_read, f.min_class_write, f.id AS forum_id FROM posts AS p LEFT JOIN attachments AS a ON p.id = a.post_id LEFT JOIN users AS u ON p.user_id = u.id LEFT JOIN topics AS t ON t.id = p.topic_id LEFT JOIN forums AS f ON t.forum_id = f.id WHERE p.id=' . sqlesc($post_id)) or sqlerr(__FILE__, __LINE__);
$arr_post = mysqli_fetch_assoc($res_post);
//=== get any attachments
$colour = $attachments = $extension_error = $size_error = '';
//=== if there are attachments, let's get them!
if (!empty($arr_post['file'])) {
    $attachments = '<tr><td><span style="white-space:nowrap; font-weight: bold;">' . $lang['fe_attachments'] . ':</span></td>
	<td>
   <table>
	<tr>
	<td colspan="2"><span style="font-weight: bold">' . $lang['fe_delete'] . '</span></td>
	</tr>';
    $attachments_res = sql_query('SELECT id, file_name, extension, size FROM attachments WHERE post_id =' . sqlesc($post_id) . ' AND user_id = ' . sqlesc($arr_post['id'])) or sqlerr(__FILE__, __LINE__);
    while ($attachments_arr = mysqli_fetch_assoc($attachments_res)) {
        $attachments .= '
	<tr>
	<td>
	<input type="checkbox" name="attachment_to_delete[]" value="' . (int) $attachments_arr['id'] . '"></td><td>
	<span style="white-space:nowrap;">' . ('zip' === $attachments_arr['extension'] ? ' <img src="' . $site_config['pic_baseurl'] . 'forums/zip.gif" alt="' . $lang['fe_zip'] . '" title="' . $lang['fe_zip'] . '" class="emoticon tooltipper"> ' : '<img src="' . $site_config['pic_baseurl'] . 'forums/rar.gif" alt="' . $lang['fe_rar'] . '" title="' . $lang['fe_rar'] . '" class="emoticon tooltipper">') . '
	<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=download_attachment&amp;id=' . (int) $attachments_arr['id'] . '" title="' . $lang['fe_download_attachment'] . '" target="_blank">' . htmlsafechars($attachments_arr['file_name']) . '</a> <span style="font-weight: bold; font-size: xx-small;">[' . mksize($attachments_arr['size']) . ']</span></span></td>
	</tr>';
    }
    $attachments .= '</table></td></tr>';
}
$can_edit = ($arr_post['puser_id'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF);
if ($CURUSER['class'] < $arr_post['min_class_read'] || $CURUSER['class'] < $arr_post['min_class_write']) {
    stderr($lang['gl_error'], $lang['fe_topic_not_found']);
}
if ($CURUSER['forum_post'] === 'no' || $CURUSER['suspended'] === 'yes') {
    stderr($lang['gl_error'], $lang['fe_your_no_post_right']);
}
if (!$can_edit) {
    stderr($lang['gl_error'], '' . $lang['fe_this_is_not_your_post_to_edit'] . '');
}
if ($arr_post['locked'] === 'yes') {
    stderr($lang['gl_error'], $lang['fe_this_topic_is_locked']);
}
if ($arr_post['staff_lock'] === 1) {
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
$show_edited_by = ((isset($_POST['show_edited_by']) && $_POST['show_edited_by'] === 'no' && $CURUSER['class'] == UC_MAX && $CURUSER['id'] == $arr_post['id']) ? 'no' : 'yes');
if (isset($_POST['button']) && $_POST['button'] === 'Edit') {
    if (empty($body)) {
        stderr($lang['gl_error'], $lang['fe_body_text_can_not_be_empty']);
    }
    $changed = '<span style="color:red;">' . $lang['fe_changed'] . '</span>';
    $not_changed = '<span style="color:green;">' . $lang['fe_not_changed'] . '</span>';
    $post_history = '<table>
	<tr>
	<td>#' . $post_id . '  ' . format_username($arr_post['user_id']) . '</td>
	<td>' . (empty($arr_post['post_history']) ? '' . $lang['fe_first_post'] . '' : '' . $lang['fe_post_edited'] . '') . ' By: ' . format_username($CURUSER['id']) . ' On: ' . date('l jS \of F Y h:i:s A', TIME_NOW) . ' GMT ' . ('' !== $post_title ? '&nbsp;&nbsp;&nbsp;&nbsp; ' . $lang['fe_title'] . ': <span style="font-weight: bold;">' . $post_title . '</span>' : '') . ('' !== $icon ? ' <img src="' . $site_config['pic_baseurl'] . 'smilies/' . $icon . '.gif" alt="' . $icon . '" title="' . $icon . '" class="emoticon tooltipper">' : '') . '</td>
	<tr>
	<td>' . (empty($arr_post['post_history']) ? ($can_edit ? '<span style="white-space:nowrap;">Desc: ' . ('' !== $arr_post['topic_desc'] ? 'yes' : 'none') . '</span><br>' : '') . '<span style="white-space:nowrap;">' . $lang['fe_title'] . ': ' . ('' !== $arr_post['post_title'] ? 'yes' : 'none') . '</span><br><span style="white-space:nowrap;">' . $lang['fe_icon'] . ': ' . ('' !== $arr_post['icon'] ? 'yes' : 'none') . '</span><br><span style="white-space:nowrap;">' . $lang['ep_bb_code'] . ': ' . ('yes' !== $arr_post['bbcode'] ? 'off' : 'on') . '</span><br>' : ($can_edit ? '<span style="white-space:nowrap;">Topic Name: ' . ((isset($_POST['topic_name']) && $_POST['topic_name'] !== $arr_post['topic_name']) ? $changed : $not_changed) . '</span><br><span style="white-space:nowrap;">Desc: ' . ((isset($_POST['topic_desc']) && $_POST['topic_desc'] !== $arr_post['topic_desc']) ? $changed : $not_changed) . '</span><br>' : '') . '<span style="white-space:nowrap;">' . $lang['fe_title'] . ': ' . ((isset($_POST['post_title']) && $_POST['post_title'] !== $arr_post['post_title']) ? $changed : $not_changed) . '</span><br><span style="white-space:nowrap;">' . $lang['fe_icon'] . ': ' . ((isset($_POST['icon']) && $_POST['icon'] !== $arr_post['icon']) ? $changed : $not_changed) . '</span><br><span style="white-space:nowrap;">' . $lang['ep_bb_code'] . ': ' . ((isset($_POST['show_bbcode']) && $_POST['show_bbcode'] !== $arr_post['bbcode']) ? $changed : $not_changed) . '</span><br><span style="white-space:nowrap;">' . $lang['fe_body'] . ': ' . ((isset($_POST['body']) && $_POST['body'] !== $arr_post['body']) ? $changed : $not_changed) . '</span><br>') . '
	</td>
	<td>' . ($arr_post['bbcode'] === 'yes' ? format_comment($arr_post['body']) : format_comment_no_bbcode($arr_post['body'])) . '</td>
	</tr>
	</table><br>' . $arr_post['post_history'];
    //=== let the sysop have the power to not show they edited their own post if they wish...
    if ($show_edited_by === 'no' && $CURUSER['class'] == UC_MAX) {
        $edit_reason = htmlsafechars($arr_post['edit_reason']);
        $edited_by = htmlsafechars($arr_post['edited_by']);
        $edit_date = (int) $arr_post['edit_date'];
        $post_history = htmlsafechars($arr_post['post_history']);
    }
    sql_query('UPDATE posts SET body = ' . sqlesc($body) . ', icon = ' . sqlesc($icon) . ', post_title = ' . sqlesc($post_title) . ', bbcode = ' . sqlesc($show_bbcode) . ', edit_reason = ' . sqlesc($edit_reason) . ', edited_by = ' . sqlesc($edited_by) . ', edit_date = ' . sqlesc($edit_date) . ', post_history = ' . sqlesc($post_history) . ' WHERE id = ' . sqlesc($post_id)) or sqlerr(__FILE__, __LINE__);
    clr_forums_cache($post_id);
    $cache->delete('forum_posts_' . $CURUSER['id']);
    //=== update topic stuff
    if ($can_edit) {
        sql_query('UPDATE topics SET topic_name = ' . sqlesc($topic_name) . ', topic_desc = ' . sqlesc($topic_desc) . ' WHERE id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    }
    //=== stuff for file uploads
    if ($CURUSER['class'] >= $min_upload_class) {
        foreach ($_FILES['attachment']['name'] as $key => $name) {
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
                $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
				$name = basename($name, '.' . $file_extension);
                $name = preg_replace('#[^a-zA-Z0-9_-]#', '', $name);
                switch (true) {
                    case $size > $max_file_size:
                        $size_error = ($size_error + 1);
                        break;

                    case !in_array($file_extension, $accepted_file_extension) && $accepted_file_extension === false:
                        $extension_error = ($extension_error + 1);
                        break;

                    case $accepted_file_extension === 0:
                        $extension_error = ($extension_error + 1);
                        break;

                    case !in_array($type, $accepted_file_types):
                        $extension_error = ($extension_error + 1);
                        break;

                    default:
                        $upload_to = $upload_folder . $name . '(id-' . $post_id . ')' . $file_extension;
                        sql_query('INSERT INTO `attachments` (`post_id`, `user_id`, `file`, `file_name`, `added`, `extension`, `size`) VALUES ( ' . sqlesc($post_id) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($name . '(id-' . $post_id . ')' . $file_extension) . ', ' . sqlesc($name) . ', ' . TIME_NOW . ', ' . ('.zip' === $file_extension ? '\'zip\'' : '\'rar\'') . ', ' . $size . ')') or sqlerr(__FILE__, __LINE__);
                        copy($_FILES['attachment']['tmp_name'][$key], $upload_to);
                        chmod($upload_to, 0777);
                }
            }
        }
    }
    if (isset($_POST['attachment_to_delete'])) {
        $_POST['attachment_to_delete'] = (isset($_POST['attachment_to_delete']) ? $_POST['attachment_to_delete'] : '');
        $attachment_to_delete = [];
        foreach ($_POST['attachment_to_delete'] as $var) {
            $attachment_to_delete = intval($var);
            //=== get attachment info
            $attachments_res = sql_query('SELECT file FROM attachments WHERE id = ' . sqlesc($attachment_to_delete)) or sqlerr(__FILE__, __LINE__);
            $attachments_arr = mysqli_fetch_array($attachments_res);
            //=== delete the file
            unlink($upload_folder . $attachments_arr['file']);
            //=== delete them from the DB
            sql_query('DELETE FROM attachments WHERE id = ' . sqlesc($attachment_to_delete) . ' AND post_id = ' . sqlesc($post_id)) or sqlerr(__FILE__, __LINE__);
        }
    } //=== end attachment stuff
    //=== only write to staff actions if it's a staff editing and not their own post
    if ($CURUSER['class'] >= UC_STAFF && $CURUSER['id'] !== $arr_post['user_id']) {
        write_log('' . $CURUSER['username'] . ' ' . $lang['ep_edited_a_post_by'] . ' ' . htmlsafechars($arr_post['username']) . '. ' . $lang['ep_here_is_the'] . ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . $post_id . '&amp;forum_id=' . (int) $arr_post['forum_id'] . '&amp;topic_id=' . $topic_id . '">' . $lang['ep_link'] . '</a> ' . $lang['ep_to_the_post_history'] . '', $CURUSER['id']);
    }
    //header('Location: forums.php?action=view_topic&topic_id='.$topic_id.'&page='.$page.'#'.$post_id);
    header('Location: ' . $site_config['baseurl'] . '/forums.php?action=view_topic&topic_id=' . $topic_id . (0 !== $extension_error ? '&ee=' . $extension_error : '') . (0 !== $size_error ? '&se=' . $size_error : ''));
    die();
}
$HTMLOUT .= '
	<h1 class="has-text-centered">' . $lang['ep_edit_post_by'] . ': ' . format_username($arr_post['user_id']) . ' ' . $lang['ep_in_topic'] . ' 
	"<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($arr_post['topic_name'], ENT_QUOTES) . '</a>"</h1>
	<form method="post" action="' . $site_config['baseurl'] . '/forums.php?action=edit_post&amp;topic_id=' . $topic_id . '&amp;post_id=' . $post_id . '&amp;page=' . $page . '" enctype="multipart/form-data">';
    require_once FORUM_DIR . 'editor.php';

$HTMLOUT .= '
	<div class="has-text-centered">
	<input type="submit" name="button" class="button is-small margin20" value="Edit">
    </div>
    </form>';

require_once FORUM_DIR . 'last_ten.php';
