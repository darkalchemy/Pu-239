<?php

declare(strict_types = 1);

$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : (isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0);
$topic_id = isset($_GET['topic_id']) ? (int) $_GET['topic_id'] : (isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0);
$page = isset($_GET['page']) ? (int) $_GET['page'] : (isset($_POST['page']) ? (int) $_POST['page'] : 0);
if (!is_valid_id($post_id) || !is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
$res_post = sql_query('SELECT p.added, p.user_id AS puser_id, p.body, p.icon, p.post_title, p.bbcode, p.post_history, p.edited_by, p.edit_date, p.edit_reason, p.staff_lock, a.file, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, t.topic_name, t.locked, t.user_id, t.topic_desc, f.min_class_read, f.min_class_write, f.id AS forum_id FROM posts AS p LEFT JOIN attachments AS a ON p.id=a.post_id LEFT JOIN users AS u ON p.user_id=u.id LEFT JOIN topics AS t ON t.id=p.topic_id LEFT JOIN forums AS f ON t.forum_id=f.id WHERE p.id=' . sqlesc($post_id)) or sqlerr(__FILE__, __LINE__);
$arr_post = mysqli_fetch_assoc($res_post);
$colour = $attachments = $extension_error = $size_error = '';
global $site_config, $CURUSER;

if (!empty($arr_post['file'])) {
    $attachments = '<tr><td><span style="white-space:nowrap; font-weight: bold;">' . $lang['fe_attachments'] . ':</span></td>
	<td>
   <table>
	<tr>
	<td colspan="2"><span style="font-weight: bold">' . $lang['fe_delete'] . '</span></td>
	</tr>';
    $attachments_res = sql_query('SELECT id, file_name, extension, size FROM attachments WHERE post_id =' . sqlesc($post_id) . ' AND user_id=' . sqlesc($arr_post['id'])) or sqlerr(__FILE__, __LINE__);
    while ($attachments_arr = mysqli_fetch_assoc($attachments_res)) {
        $attachments .= '
	<tr>
	<td>
	<input type="checkbox" name="attachment_to_delete[]" value="' . (int) $attachments_arr['id'] . '"></td><td>
	<span class="has-no-wrap">' . ($attachments_arr['extension'] === 'zip' ? ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/zip.gif" alt="' . $lang['fe_zip'] . '" title="' . $lang['fe_zip'] . '" class="emoticon tooltipper"> ' : '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/rar.gif" alt="' . $lang['fe_rar'] . '" title="' . $lang['fe_rar'] . '" class="emoticon tooltipper">') . '
	<a class="is-link tooltipper" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=download_attachment&amp;id=' . (int) $attachments_arr['id'] . '" title="' . $lang['fe_download_attachment'] . '" target="_blank">' . htmlsafechars($attachments_arr['file_name']) . '</a> <span style="font-weight: bold; font-size: xx-small;">[' . mksize($attachments_arr['size']) . ']</span></span></td>
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
$body = isset($_POST['body']) ? $_POST['body'] : $arr_post['body'];
if ($can_edit) {
    $topic_name = isset($_POST['topic_name']) ? htmlsafechars($_POST['topic_name']) : htmlsafechars($arr_post['topic_name']);
    $topic_desc = isset($_POST['topic_desc']) ? htmlsafechars($_POST['topic_desc']) : htmlsafechars($arr_post['topic_desc']);
}
$post_title = isset($_POST['post_title']) ? htmlsafechars($_POST['post_title']) : htmlsafechars($arr_post['post_title']);
$icon = isset($_POST['icon']) ? htmlsafechars($_POST['icon']) : htmlsafechars($arr_post['icon']);
$show_bbcode = isset($_POST['bb_code']) ? $_POST['bb_code'] : $arr_post['bbcode'];
$bb_code = $show_bbcode;
$edit_reason = isset($_POST['edit_reason']) ? htmlsafechars($_POST['edit_reason']) : '';
$show_edited_by = ((isset($_POST['show_edited_by']) && $_POST['show_edited_by'] === 'no' && $CURUSER['class'] >= $site_config['allowed']['show_edited_by'] && $CURUSER['id'] == $arr_post['id']) ? 'no' : 'yes');
if (isset($_POST['button']) && $_POST['button'] === 'Edit') {
    if (empty($body)) {
        stderr($lang['gl_error'], $lang['fe_body_text_can_not_be_empty']);
    }
    $changed = '<span style="color:red;">' . $lang['fe_changed'] . '</span>';
    $not_changed = '<span style="color:green;">' . $lang['fe_not_changed'] . '</span>';
    $post_history = main_div("
        <div class='w-100 padding10'>
            <div class='columns is-marginless'>
                <div class='column is-one-quarter round10 bg-02 padding20'>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>Edited:</div>
                         <div class='column is-paddingless'>" . get_date((int) $arr_post['edit_date'], 'LONG', 1, 0) . "</div>
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>Desc:</div>
                         <div class='column is-paddingless'>" . (!empty($arr_post['topic_desc']) ? 'yes' : 'none') . "</div>
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>{$lang['fe_title']}:</div>
                         <div class='column is-paddingless'>" . (!empty($arr_post['post_title']) ? 'yes' : 'none') . "</div>
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>{$lang['fe_icon']}:</div>
                         <div class='column is-paddingless'>" . (!empty($arr_post['icon']) ? 'yes' : 'none') . "</div>
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>{$lang['ep_bb_code']}:</div>
                         <div class='column is-paddingless'>" . ($show_bbcode === 'yes' ? 'on' : 'off') . '</div>' . ($can_edit ? "
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>Topic Name:</div>
                         <div class='column is-paddingless'>" . ((isset($_POST['topic_name']) && $_POST['topic_name'] !== $arr_post['topic_name']) ? $changed : $not_changed) . "</div>
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>Desc:</div>
                         <div class='column is-paddingless'>" . ((isset($_POST['topic_desc']) && $_POST['topic_desc'] !== $arr_post['topic_desc']) ? $changed : $not_changed) . '</div>' : '') . "
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>{$lang['fe_title']}:</div>
                         <div class='column is-paddingless'>" . ((isset($_POST['post_title']) && $_POST['post_title'] !== $arr_post['post_title']) ? $changed : $not_changed) . "</div>
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>{$lang['fe_icon']}:</div>
                         <div class='column is-paddingless'>" . ((isset($_POST['icon']) && $_POST['icon'] !== $arr_post['icon']) ? $changed : $not_changed) . "</div>
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>{$lang['ep_bb_code']}:</div>
                         <div class='column is-paddingless'>" . (($show_bbcode !== $arr_post['bbcode']) ? $changed : $not_changed) . "</div>
                    </div>
                    <div class='columns is-marginless'>
                        <div class='column is-paddingless is-one-third'>{$lang['fe_body']}:</div>
                         <div class='column is-paddingless'>" . ((isset($_POST['body']) && $_POST['body'] !== $arr_post['body']) ? $changed : $not_changed) . "</div>
                    </div>
                </div>
                <div class='column round10 bg-02 left10'>" . format_comment($arr_post['body']) . '</div>
            </div>
        </div>', (!empty($arr_post['post_history']) ? 'bottom20' : '')) . $arr_post['post_history'];
    sql_query('UPDATE posts SET body = ' . sqlesc(htmlsafechars($body)) . ', icon = ' . sqlesc($icon) . ', post_title = ' . sqlesc($post_title) . ', bbcode = ' . sqlesc($show_bbcode) . ', edit_reason = ' . sqlesc($edit_reason) . ', edited_by = ' . sqlesc($edited_by) . ', edit_date = ' . sqlesc(TIME_NOW) . ', post_history = ' . sqlesc($post_history) . ' WHERE id=' . sqlesc($post_id)) or sqlerr(__FILE__, __LINE__);
    clr_forums_cache($post_id);
    $cache->delete('forum_posts_' . $CURUSER['id']);
    if ($can_edit) {
        sql_query('UPDATE topics SET topic_name = ' . sqlesc($topic_name) . ', topic_desc = ' . sqlesc($topic_desc) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    }
    $extension_error = $size_error = 0;
    if (!empty($_FILES)) {
        require_once FORUM_DIR . 'attachment.php';
        $uploaded = upload_attachments($post_id);
        $extension_error = $uploaded[0];
        $size_error = $uploaded[1];
    }
    if (!empty($_POST['attachment_to_delete']) && is_array($_POST['attachment_to_delete'])) {
        foreach ($_POST['attachment_to_delete'] as $var) {
            $attachment_to_delete = intval($var);
            $attachments_res = sql_query('SELECT file FROM attachments WHERE id = ' . sqlesc($attachment_to_delete)) or sqlerr(__FILE__, __LINE__);
            $attachments_arr = mysqli_fetch_array($attachments_res);
            unlink($upload_folder . $attachments_arr['file']);
            sql_query('DELETE FROM attachments WHERE id = ' . sqlesc($attachment_to_delete) . ' AND post_id = ' . sqlesc($post_id)) or sqlerr(__FILE__, __LINE__);
        }
    }
    if ($CURUSER['class'] >= UC_STAFF && $CURUSER['id'] !== $arr_post['user_id']) {
        write_log('' . $CURUSER['username'] . ' ' . $lang['ep_edited_a_post_by'] . ' ' . htmlsafechars($arr_post['username']) . '. ' . $lang['ep_here_is_the'] . ' <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . $post_id . '&amp;forum_id=' . (int) $arr_post['forum_id'] . '&amp;topic_id=' . $topic_id . '">' . $lang['ep_link'] . '</a> ' . $lang['ep_to_the_post_history'] . '', $CURUSER['id']);
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id . ($extension_error !== 0 ? '&ee=' . $extension_error : '') . ($size_error !== 0 ? '&se=' . $size_error : ''));
    die();
}
$HTMLOUT .= '
	<h1 class="has-text-centered">' . $lang['ep_edit_post_by'] . ': ' . format_username((int) $arr_post['user_id']) . ' ' . $lang['ep_in_topic'] . ' 
	"<a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($arr_post['topic_name']) . '</a>"</h1>
	<form method="post" action="' . $site_config['paths']['baseurl'] . '/forums.php?action=edit_post&amp;topic_id=' . $topic_id . '&amp;post_id=' . $post_id . '&amp;page=' . $page . '" enctype="multipart/form-data" accept-charset="utf-8">';
require_once FORUM_DIR . 'editor.php';

$HTMLOUT .= '
	<div class="has-text-centered">
	<input type="submit" name="button" class="button is-small margin20" value="Edit">
    </div>
    </form>';

require_once FORUM_DIR . 'last_ten.php';
