<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
/**********************************************************
New 2010 forums that don't suck for TB based sites....
pretty much coded page by page, but coming from a 
history ot TBsourse and TBDev and the many many 
coders who helped develop them over time.
proper credits to follow :)
Powered by Bunnies!!!
**********************************************************/
if (!defined('BUNNY_FORUMS')) {
    $HTMLOUT = '';
    $HTMLOUT.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>ERROR</title>
        </head><body>
        <h1 style="text-align:center;">ERROR</h1>
        <p style="text-align:center;">How did you get here? silly rabbit Trix are for kids!.</p>
        </body></html>';
    echo $HTMLOUT;
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
$colour = $attachments = $extension_error = $size_error = '';
//=== if there are attachments, let's get them!
if (!empty($arr_post['file'])) {
    $attachments = '<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">'.$lang['fe_attachments'].':</span></td>
	<td align="left" class="two">
   <table border="0" cellspacing="5" cellpadding="5" align="left">
	<tr>
	<td class="forum_head" align="left" valign="middle" colspan="2"><span style="font-weight: bold">'.$lang['fe_delete'].'</span></td>
	</tr>';
    $attachments_res = sql_query('SELECT id, file_name, extension, size FROM attachments WHERE post_id =' . sqlesc($post_id) . ' AND user_id = ' . sqlesc($arr_post['id']));
    while ($attachments_arr = mysqli_fetch_assoc($attachments_res)) {
        $attachments.= '
	<tr>
	<td class="three" align="center" valign="middle" width="18">
	<input type="checkbox" name="attachment_to_delete[]" value="' . (int)$attachments_arr['id'] . '" /></td><td class="three" align="left" valign="middle">
	<span style="white-space:nowrap;">' . ($attachments_arr['extension'] === 'zip' ? ' <img src="' . $INSTALLER09['pic_base_url'] . 'forums/zip.gif" alt="'.$lang['fe_zip'].'" title="'.$lang['fe_zip'].'" width="18" style="vertical-align: middle;" /> ' : '<img src="' . $INSTALLER09['pic_base_url'] . 'forums/rar.gif" alt="'.$lang['fe_rar'].'" title="'.$lang['fe_rar'].'" width="18" />') . ' 
	<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=download_attachment&amp;id=' . (int)$attachments_arr['id'] . '" title="'.$lang['fe_download_attachment'].'" target="_blank">' . htmlsafechars($attachments_arr['file_name']) . '</a> <span style="font-weight: bold; font-size: xx-small;">[' . mksize($attachments_arr['size']) . ']</span></span></td>
	</tr>';
    }
    $attachments.= '</table></td></tr>';
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
    stderr($lang['gl_error'], ''.$lang['fe_this_is_not_your_post_to_edit'].'');
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
    $changed = '<span style="color:red;">'.$lang['fe_changed'].'</span>';
    $not_changed = '<span style="color:green;">'.$lang['fe_not_changed'].'</span>';
    $post_history = '<table border="0" cellspacing="5" cellpadding="10" width="90%">
	<tr>
	<td class="forum_head" align="left" valign="middle" width="120px">#' . $post_id . '  ' . print_user_stuff($arr_post) . '</td>
	<td class="forum_head" align="left" valign="middle">' . (empty($arr_post['post_history']) ? ''.$lang['fe_first_post'].'' : ''.$lang['fe_post_edited'].'') . ' By: ' . print_user_stuff($CURUSER) . ' On: ' . date('l jS \of F Y h:i:s A', TIME_NOW) . ' GMT ' . ($post_title !== '' ? '&nbsp;&nbsp;&nbsp;&nbsp; '.$lang['fe_title'].': <span style="font-weight: bold;">' . $post_title . '</span>' : '') . ($icon !== '' ? ' <img src="' . $INSTALLER09['pic_base_url'] . 'smilies/' . $icon . '.gif" alt="' . $icon . '" title="' . $icon . '" />' : '') . '</td>
	<tr>
	<td class="two" align="left" valign="top" width="120px">' . (empty($arr_post['post_history']) ? ($can_edit ? '<span style="white-space:nowrap;">Desc: ' . ($arr_post['topic_desc'] !== '' ? 'yes' : 'none') . '</span><br />' : '') . '<span style="white-space:nowrap;">'.$lang['fe_title'].': ' . ($arr_post['post_title'] !== '' ? 'yes' : 'none') . '</span><br /><span style="white-space:nowrap;">'.$lang['fe_icon'].': ' . ($arr_post['icon'] !== '' ? 'yes' : 'none') . '</span><br /><span style="white-space:nowrap;">'.$lang['ep_bb_code'].': ' . ($arr_post['bbcode'] !== 'yes' ? 'off' : 'on') . '</span><br />' : ($can_edit ? '<span style="white-space:nowrap;">Topic Name: ' . ((isset($_POST['topic_name']) && $_POST['topic_name'] !== $arr_post['topic_name']) ? $changed : $not_changed) . '</span><br /><span style="white-space:nowrap;">Desc: ' . ((isset($_POST['topic_desc']) && $_POST['topic_desc'] !== $arr_post['topic_desc']) ? $changed : $not_changed) . '</span><br />' : '') . '<span style="white-space:nowrap;">'.$lang['fe_title'].': ' . ((isset($_POST['post_title']) && $_POST['post_title'] !== $arr_post['post_title']) ? $changed : $not_changed) . '</span><br /><span style="white-space:nowrap;">'.$lang['fe_icon'].': ' . ((isset($_POST['icon']) && $_POST['icon'] !== $arr_post['icon']) ? $changed : $not_changed) . '</span><br /><span style="white-space:nowrap;">'.$lang['ep_bb_code'].': ' . ((isset($_POST['show_bbcode']) && $_POST['show_bbcode'] !== $arr_post['bbcode']) ? $changed : $not_changed) . '</span><br /><span style="white-space:nowrap;">'.$lang['fe_body'].': ' . ((isset($_POST['body']) && $_POST['body'] !== $arr_post['body']) ? $changed : $not_changed) . '</span><br />') . '
	</td>
	<td class="one" align="left" valign="top">' . ($arr_post['bbcode'] == 'yes' ? format_comment($arr_post['body']) : format_comment_no_bbcode($arr_post['body'])) . '</td>
	</tr>
	</table><br />' . $arr_post['post_history'];
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
                $accepted_file_types = array(
                    'application/zip',
                    'application/x-zip',
                    'application/rar',
                    'application/x-rar'
                );
                $extension_error = $size_error = 0;
                //=== allowed file types (2 checks) but still can't really trust it
                $the_file_extension = strrpos($name, '.');
                $file_extension = strtolower(substr($name, $the_file_extension)); //===  make sure the name is only alphanumeric or _ or -
                $name = preg_replace('#[^a-zA-Z0-9_-]#', '', $name); // hell, it could even be 0_0 if it wanted to!
                switch (true) {
                case ($size > $max_file_size);
                $size_error = ($size_error + 1);
                break;

            case (!in_array($file_extension, $accepted_file_extension) && $accepted_file_extension == false):
                $extension_error = ($extension_error + 1);
                break;

            case ($accepted_file_extension === 0):
                $extension_error = ($extension_error + 1);
                break;

            case (!in_array($type, $accepted_file_types)):
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
    $attachment_to_delete = array();
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
    write_log('' . $CURUSER['username'] . ' '.$lang['ep_edited_a_post_by'].' ' . htmlsafechars($arr_post['username']) . '. '.$lang['ep_here_is_the'].' <a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . $post_id . '&amp;forum_id=' . (int)$arr_post['forum_id'] . '&amp;topic_id=' . $topic_id . '">'.$lang['ep_link'].'</a> '.$lang['ep_to_the_post_history'].'', $CURUSER['id']);
}
//header('Location: forums.php?action=view_topic&topic_id='.$topic_id.'&page='.$page.'#'.$post_id);
header('Location: ' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&topic_id=' . $topic_id . ($extension_error !== 0 ? '&ee=' . $extension_error : '') . ($size_error !== 0 ? '&se=' . $size_error : ''));
die();
}
$HTMLOUT.= '<table class="main" width="750px" border="0" cellspacing="0" cellpadding="0">
	<tr><td class="embedded" align="center">
	<h1 style="text-align: center;">'.$lang['ep_edit_post_by'].':' . print_user_stuff($arr_post) . ' '.$lang['ep_in_topic'].' 
	"<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($arr_post['topic_name'], ENT_QUOTES) . '</a>"</h1>
	<form method="post" action="' . $INSTALLER09['baseurl'] . '/forums.php?action=edit_post&amp;topic_id=' . $topic_id . '&amp;post_id=' . $post_id . '&amp;page=' . $page . '" enctype="multipart/form-data">
	
	' . (isset($_POST['button']) && $_POST['button'] == ''.$lang['fe_preview'].'' ? '<br />
	<table align="center" width="80%" border="0" cellspacing="5" cellpadding="5">
	<tr><td class="forum_head" colspan="2"><span style="color: black; font-weight: bold;">'.$lang['fe_preview'].'</span></td></tr>
	<tr><td width="80" valign="top" class="one">' . avatar_stuff($CURUSER) . '</td>
	<td valign="top" align="left" class="two">' . ($show_bbcode === 'yes' ? format_comment($body) : format_comment_no_bbcode($body)) . '</td>
	</tr></table><br />' : '') . '
	<table align="center" width="80%" border="0" cellspacing="0" cellpadding="5">
	<tr><td align="left" class="forum_head_dark" colspan="2">'.$lang['fe_compose'].'</td></tr>
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">'.$lang['fe_icon'].'</span></td>
	<td align="left" class="two">
	<table>
	<tr>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/smile1.gif" alt="'.$lang['fe_smile'].'" title="'.$lang['fe_smile'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/grin.gif" alt="'.$lang['fe_smilee_grin'].'" title="'.$lang['fe_smilee_grin'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/tongue.gif" alt="'.$lang['fe_smilee_tongue'].'" title="'.$lang['fe_smilee_tongue'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/cry.gif" alt="'.$lang['fe_smilee_cry'].'" title="'.$lang['fe_smilee_cry'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/wink.gif" alt="'.$lang['fe_smilee_wink'].'" title="'.$lang['fe_smilee_wink'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/rolleyes.gif" alt="'.$lang['fe_smilee_roll_eyes'].'" title="'.$lang['fe_smilee_roll_eyes'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/blink.gif" alt="'.$lang['fe_smilee_blink'].'" title="'.$lang['fe_smilee_blink'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/bow.gif" alt="'.$lang['fe_smilee_bow'].'" title="'.$lang['fe_smilee_bow'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/clap2.gif" alt="'.$lang['fe_smilee_clap'].'" title="'.$lang['fe_smilee_clap'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/hmmm.gif" alt="'.$lang['fe_smilee_hmm'].'" title="'.$lang['fe_smilee_hmm'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/devil.gif" alt="'.$lang['fe_smilee_devil'].'" title="'.$lang['fe_smilee_devil'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/angry.gif" alt="'.$lang['fe_smilee_angry'].'" title="'.$lang['fe_smilee_angry'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/shit.gif" alt="'.$lang['fe_smilee_shit'].'" title="'.$lang['fe_smilee_shit'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/sick.gif" alt="'.$lang['fe_smilee_sick'].'" title="'.$lang['fe_smilee_sick'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/tease.gif" alt="'.$lang['fe_smilee_tease'].'" title="'.$lang['fe_smilee_tease'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/love.gif" alt="'.$lang['fe_smilee_love'].'" title="'.$lang['fe_smilee_love'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/ohmy.gif" alt="'.$lang['fe_smilee_oh_my'].'" title="'.$lang['fe_smilee_oh_my'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/yikes.gif" alt="'.$lang['fe_smilee_yikes'].'" title="'.$lang['fe_smilee_yikes'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/spider.gif" alt="'.$lang['fe_smilee_spider'].'" title="'.$lang['fe_smilee_spider'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/wall.gif" alt="'.$lang['fe_smilee_wall'].'" title="'.$lang['fe_smilee_wall'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/idea.gif" alt="'.$lang['fe_smilee_idea'].'" title="'.$lang['fe_smilee_idea'].'" /></td>
	<td class="two" align="center" valign="middle"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/question.gif" alt="'.$lang['fe_smilee_question'].'" title="'.$lang['fe_smilee_question'].'" /></td>
	</tr>
	<tr>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="smile1"' . ($icon == 'smile1' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="grin"' . ($icon == 'grin' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="tongue"' . ($icon == 'tongue' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="cry"' . ($icon == 'cry' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="wink"' . ($icon == 'wink' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="rolleyes"' . ($icon == 'rolleyes' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="blink"' . ($icon == 'blink' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="bow"' . ($icon == 'bow' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="clap2"' . ($icon == 'clap2' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="hmmm"' . ($icon == 'hmmm' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="devil"' . ($icon == 'devil' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="angry"' . ($icon == 'angry' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="shit"' . ($icon == 'shit' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="sick"' . ($icon == 'sick' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="tease"' . ($icon == 'tease' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="love"' . ($icon == 'love' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="ohmy"' . ($icon == 'ohmy' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="yikes"' . ($icon == 'yikes' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="spider"' . ($icon == 'spider' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="wall"' . ($icon == 'wall' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="idea"' . ($icon == 'idea' ? ' checked="checked"' : '') . ' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="question"' . ($icon == 'question' ? ' checked="checked"' : '') . ' /></td>
	</tr>
	</table>
	</td></tr>
	' . ($can_edit ? '<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">'.$lang['fe_name'].'</span></td>
	<td align="left" class="two"><input type="text"  name="topic_name" value="' . trim(strip_tags($topic_name)) . '" class="text_default" /></td></tr>
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">'.$lang['fe_desc'].'</span></td>
	<td align="left" class="two"><input type="text" maxlength="120" name="topic_desc" value="' . trim(strip_tags($topic_desc)) . '" class="text_default" /> [ optional ]</td></tr>' : '') . '
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">'.$lang['fe_title'].'</span></td>
	<td align="left" class="two"><input type="text" maxlength="120" name="post_title" value="' . trim(strip_tags($post_title)) . '" class="text_default" /> [ optional ]</td></tr>
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">'.$lang['fe_bbcode'].'</span></td>
	<td align="left" class="two">
	<input type="radio" name="show_bbcode" value="yes" ' . ($show_bbcode == 'yes' ? 'checked="checked"' : '') . ' /> '.$lang['fe_yes_enable'].' '.$lang['fe_bbcode_in_post'].' 
	<input type="radio" name="show_bbcode" value="no" ' . ($show_bbcode == 'no' ? 'checked="checked"' : '') . ' /> '.$lang['fe_no_disable'].' '.$lang['fe_bbcode_in_post'].' 
	</td></tr>
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">'.$lang['fe_reason'].'</span></td>
	<td align="left" class="two"><input type="text" maxlength="20" name="edit_reason" value="' . trim(strip_tags($edit_reason)) . '" class="text_default" /> [ optional ] 
	&nbsp;&nbsp;&nbsp;&nbsp;
	</td></tr>
	' . (($CURUSER['class'] == UC_MAX OR $CURUSER['id'] == $arr_post['id']) ? '<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">Edit By</span></td>
	<td align="left" class="two">
	<input type="radio" name="show_edited_by" value="yes"' . ($show_edited_by == 'yes' ? ' checked="checked"' : '') . ' /> yes
	<input type="radio" name="show_edited_by" value="no"' . ($show_edited_by == 'no' ? ' checked="checked"' : '') . ' /> no
	</td></tr>' : '') . $attachments . '
	<tr><td align="right" valign="top" class="two"><span style="white-space:nowrap; font-weight: bold;">'.$lang['fe_body'].'</span></td>
	<td align="left" class="two">' . BBcode($body) . $more_options . '
	</td></tr>
	<tr><td align="center" colspan="2" class="two">
	<input type="submit" name="button" class="button" value="'.$lang['fe_preview'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
	<input type="submit" name="button" class="button_tiny" value="Edit" onmouseover="this.className=\'button_tiny_hover\'" onmouseout="this.className=\'button_tiny\'" />
	</td></tr>
	</table></form>';
//=== get last ten posts
$res_posts = sql_query('SELECT p.id AS post_id, p.user_id, p.added, p.body, p.icon, p.post_title, p.bbcode, p.anonymous, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.avatar, u.chatpost, u.leechwarn, u.pirate, u.king, u.offensive_avatar FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND' : '')) . '  topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1, 10');
$HTMLOUT.= '<br /><span style="text-align: center;">'.$lang['fe_last_ten_posts_in_reverse_order'].'</span>
	<table border="0" cellspacing="5" cellpadding="10" width="90%" align="center">';
//=== lets start the loop \o/
while ($arr = mysqli_fetch_assoc($res_posts)) {
    //=== change colors
    $colour = (++$colour) % 2;
    $class = ($colour == 0 ? 'one' : 'two');
    $class_alt = ($colour == 0 ? 'two' : 'one');
    $HTMLOUT.= '<tr><td class="forum_head" align="left" width="100" valign="middle"><a name="' . (int)$arr['post_id'] . '"></a>
		<span style="white-space:nowrap;">#' . (int)$arr['post_id'] . '
		<span style="font-weight: bold;">' . ($arr['anonymous'] == 'yes' ? '<i>'.$lang['fe_anonymous'].'</i>' : htmlsafechars($arr['username'])) . '</span></span></td>
		<td class="forum_head" align="left" valign="middle"><span style="white-space:nowrap;"> '.$lang['fe_posted_on'].': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span></td></tr>';
    $width = 100;
    if ($arr['anonymous'] == 'yes') {
        if ($CURUSER['class'] < UC_STAFF && $arr['user_id'] != $CURUSER['id']) $HTMLOUT.= '<tr><td class="' . $class_alt . '" align="center" width="100" valign="top"><img style="max-width:' . $width . 'px;" src="' . $INSTALLER09['pic_base_url'] . 'anonymous_1.jpg" alt="avatar" /><br /><i>'.$lang['fe_anonymous'].'</i></td>';
        else $HTMLOUT.= '<tr><td class="' . $class_alt . '" align="center" width="100" valign="top">' . avatar_stuff($arr) . '<br /><i>Anonymous </i>[' . print_user_stuff($arr) . ']</td>';
    } else {
        $HTMLOUT.= '<tr><td class="' . $class_alt . '" align="center" width="100" valign="top">' . avatar_stuff($arr) . '<br />' . print_user_stuff($arr) . '</td>';
    }
    $HTMLOUT.= '<td class="' . $class . '" align="left" valign="top" colspan="2">' . ($arr['bbcode'] == 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body'])) . '</td></tr>';
}
//=== end while loop
$HTMLOUT.= '</table><br /></td></tr></table><br />';
?>