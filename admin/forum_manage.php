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

Beta Thurs Sept 9th 2010 v0.5

//===  forum manager by Retro, but newer \o/ march 2010
***************************************************************/
if (!defined('IN_INSTALLER09_ADMIN')) {
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
require_once (INCL_DIR . 'html_functions.php');
require_once (CLASS_DIR . 'class_check.php');
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_forum_manage'));
$HTMLOUT = $options = $options_2 = $options_3 = $options_4 = $options_5 = $options_6 = $option_7 = $option_8 = $option_9 = $option_10 = $option_11 = $count = $forums_stuff = '';
$row = 0;
//=== defaults:
$maxclass = $CURUSER['class'];
$id = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
$name = strip_tags(isset($_POST['name']) ? htmlsafechars($_POST['name']) : '');
$desc = strip_tags(isset($_POST['desc']) ? htmlsafechars($_POST['desc']) : '');
$sort = (isset($_POST['sort']) ? intval($_POST['sort']) : 0);
$parent_forum = (isset($_POST['parent_forum']) ? intval($_POST['parent_forum']) : 0);
$over_forums = (isset($_POST['over_forums']) ? intval($_POST['over_forums']) : 0);
$min_class_read = (isset($_POST['min_class_read']) ? intval($_POST['min_class_read']) : 0);
$min_class_write = (isset($_POST['min_class_write']) ? intval($_POST['min_class_write']) : 0);
$min_class_create = (isset($_POST['min_class_create']) ? intval($_POST['min_class_create']) : 0);
$main_links = '<p><a class="altlink" href="staffpanel.php?tool=over_forums&amp;action=over_forums">'.$lang['fm_overforum'].'</a> :: 
						<span style="font-weight: bold;">'.$lang['fm_forummanager'].'</span> :: 
						<a class="altlink" href="staffpanel.php?tool=forum_config&amp;action=forum_config">'.$lang['fm_configure'].'</a><br /></p>';
//=== post / get action posted so we know what to do :P
$posted_action = (isset($_GET['action2']) ? htmlsafechars($_GET['action2']) : (isset($_POST['action2']) ? htmlsafechars($_POST['action2']) : ''));
//=== add all possible actions here and check them to be sure they are ok
$valid_actions = array(
    'delete',
    'edit_forum',
    'add_forum',
    'edit_forum_page'
);
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'no_action');
//=== here we go with all the possibilities \\o\o/o//
switch ($action) {
    //=== delete forums
    
case 'delete':
    if (!$id) {
        header('Location: staffpanel.php?tool=forum_manage&action=forum_manage');
        die();
    }
    $res = sql_query('SELECT * FROM topics where forum_id = ' . sqlesc($id));
    $row = mysqli_fetch_array($res);
    sql_query('DELETE FROM posts where topic_id =' . sqlesc($row['id']));
    sql_query('DELETE FROM topics where forum_id = ' . sqlesc($id));
    sql_query('DELETE FROM forums where id = ' . sqlesc($id));
    header('Location: staffpanel.php?tool=forum_manage&action=forum_manage');
    die();
    break;
    //=== edit forum
    
case 'edit_forum':
    if (!$name && !$desc && !$id) {
        header('Location: staffpanel.php?tool=forum_manage&action=forum_manage');
        die();
    }
    sql_query('UPDATE forums SET sort = ' . sqlesc($sort) . ', name = ' . sqlesc($name) . ', parent_forum = ' . sqlesc($parent_forum) . ', description = ' . sqlesc($desc) . ', forum_id = ' . sqlesc($over_forums) . ', min_class_read = ' . sqlesc($min_class_read) . ', min_class_write = ' . sqlesc($min_class_write) . ', min_class_create = ' . sqlesc($min_class_create) . ' where id = ' . sqlesc($id));
    header('Location: staffpanel.php?tool=forum_manage&action=forum_manage');
    die();
    break;
    //=== add forum
    
case 'add_forum':
    if (!$name && !$desc) {
        header('Location: staffpanel.php?tool=forum_manage&action=forum_manage');
        die();
    }
    sql_query('INSERT INTO forums (sort, name, parent_forum, description,  min_class_read,  min_class_write, min_class_create, forum_id) VALUES (' . sqlesc($sort) . ', ' . sqlesc($name) . ', ' . sqlesc($parent_forum) . ', ' . sqlesc($desc) . ', ' . sqlesc($min_class_read) . ', ' . sqlesc($min_class_write) . ', ' . sqlesc($min_class_create) . ', ' . sqlesc($over_forums) . ')');
    header('Location: staffpanel.php?tool=forum_manage&action=forum_manage');
    die();
    break;
    //=== edit forum stuff
    
case 'edit_forum_page':
    $res = sql_query('SELECT * FROM forums where id = ' . sqlesc($id));
    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_array($res);
        $HTMLOUT.= $main_links . '<form method="post" action="staffpanel.php?tool=forum_manage&amp;action=forum_manage">
					<table  border="0" cellspacing="0" cellpadding="3" align="center">
					<tr>
					<td colspan="2" class="forum_head_dark"> '.$lang['fm_efp_edit'].' ' . htmlsafechars($row['name'], ENT_QUOTES) . '</td>
					</tr>
					<tr>
					<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_efp_name'].'</span></td>
					<td align="left" class="three"><input name="name" type="text" class="text_default" size="20" maxlength="60" value="' . htmlsafechars($row['name'], ENT_QUOTES) . '" /></td>
					</tr>
					<tr>
					<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_efp_description'].'</span></td>
					<td align="left" class="three"><input name="desc" type="text" class="text_default" size="30" maxlength="200" value="' . htmlsafechars($row['description'], ENT_QUOTES) . '" /></td>
					</tr>
					<tr>
					<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_efp_over'].'</span></td>
					<td  align="left" class="three">
					<select name="over_forums">';
        $forum_id = (int)$row['forum_id'];
        $res = sql_query('SELECT * FROM over_forums');
        while ($arr = mysqli_fetch_array($res)) {
            $i = (int)$arr['id'];
            $options.= '<option class="body" value="' . $i . '"' . ($forum_id == $i ? ' selected="selected"' : '') . '>' . htmlsafechars($arr['name'], ENT_QUOTES) . '</option>';
        }
        $HTMLOUT.= $options . '</select></td></tr>
				<tr>
				<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_efp_sub'].'</span></td>
				<td align="left" class="three">
				<select name="parent_forum">
				<option class="body" value="0"' . ($parent_forum == 0 ? ' selected="selected"' : '') . '>'.$lang['fm_efp_select'].'</option>';
        $res = sql_query('SELECT name, id FROM forums');
        while ($arr = mysqli_fetch_array($res)) {
            if (is_valid_id($arr['id'])) $options_2.= '<option class="body" value="' . (int)$arr['id'] . '"' . ($parent_forum == $arr['id'] ? ' selected="selected"' : '') . '>' . htmlsafechars($arr['name'], ENT_QUOTES) . '</option>';
        }
        $HTMLOUT.= $options_2 . '</select></td></tr>
				<tr>
				<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_efp_min_r'].'</span></td>
				<td  align="left" class="three">
				<select name="min_class_read">';
        for ($i = 0; $i <= $maxclass; ++$i) {
            $options_3.= '<option class="body" value="' . $i . '"' . ($row['min_class_read'] == $i ? ' selected="selected"' : '') . '>' . get_user_class_name($i) . '</option>';
        }
        $HTMLOUT.= $options_3 . '</select></td></tr><tr>
    			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_efp_min_w'].'</span></td>
    			<td align="left" class="three"><select name="min_class_write">';
        for ($i = 0; $i <= $maxclass; ++$i) {
            $options_4.= '<option class="body" value="' . $i . '"' . ($row['min_class_write'] == $i ? ' selected="selected"' : '') . '>' . get_user_class_name($i) . '</option>';
        }
        $HTMLOUT.= $options_4 . '</select></td></tr><tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_efp_min_c'].'</span></td>
			<td align="left" class="three"><select name="min_class_create">';
        for ($i = 0; $i <= $maxclass; ++$i) {
            $options_5.= '<option class="body" value="' . $i . '"' . ($row['min_class_create'] == $i ? ' selected="selected"' : '') . '>' . get_user_class_name($i) . '</option>';
        }
        $HTMLOUT.= $options_5 . '</select></td></tr><tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_efp_rank'].'</span> </td>
			<td align="left" class="three">
			<select name="sort">';
        $res = sql_query('SELECT sort FROM forums');
        $nr = mysqli_num_rows($res);
        $maxclass = $nr + 1;
        for ($i = 0; $i <= $maxclass; ++$i) {
            $options_6.= '<option class="body" value="' . htmlsafechars($i) . '"' . ($row['sort'] == $i ? ' selected="selected"' : '') . '>' . htmlsafechars($i) . '</option>';
        }
        $HTMLOUT.= $options_6 . '</select></td></tr>
			<tr>
			<td colspan="2" align="center" class="three">
			<input type="hidden" name="action2" value="edit_forum" />
			<input type="hidden" name="id" value="' . htmlsafechars($id) . '" />
			<input type="submit" name="button" class="button" value="'.$lang['fm_efp_btn'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</td>
			</tr></table></form><br /><br />';
    }
    break;
} //=== end switch
//=== basic page
$HTMLOUT.= $main_links . '<table width="750"  border="0" align="center" cellpadding="2" cellspacing="0">
		<tr><td class="forum_head_dark" align="left">'.$lang['fm_mp_name'].'</td>
		<td class="forum_head_dark" align="center">'.$lang['fm_mp_sub'].'</td>
		<td class="forum_head_dark" align="center">'.$lang['fm_mp_over'].'</td>
		<td class="forum_head_dark" align="center">'.$lang['fm_mp_read'].'</td>
		<td class="forum_head_dark" align="center">'.$lang['fm_mp_write'].'</td>
		<td class="forum_head_dark" align="center">'.$lang['fm_mp_create'].'</td>
		<td class="forum_head_dark" align="center">'.$lang['fm_mp_modify'].'</td></tr>';
$res = sql_query('SELECT * FROM forums ORDER BY forum_id ASC');
if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_array($res)) {
        $forum_id = (int)$row['forum_id'];
        $res2 = sql_query('SELECT name FROM over_forums WHERE id=' . sqlesc($forum_id));
        $arr2 = mysqli_fetch_assoc($res2);
        $name = htmlsafechars($arr2['name'], ENT_QUOTES);
        $subforum = (int)$row['parent_forum'];
        if ($subforum) {
            $res3 = sql_query('SELECT name FROM forums WHERE id=' . sqlesc($subforum));
            $arr3 = mysqli_fetch_assoc($res3);
            $subforum_name = htmlsafechars($arr3['name'], ENT_QUOTES);
        } else {
            $subforum_name = '';
        }
        //=== change colors
        $count = (++$count) % 2;
        $class = ($count == 0 ? 'one' : 'two');
        $HTMLOUT.= '<tr><td class="' . $class . '"><a class="altlink" href="forums.php?action=view_forum&amp;forum_id=' . (int)$row['id'] . '">
			<span style="font-weight: bold;">' . htmlsafechars($row['name'], ENT_QUOTES) . '</span></a><br />
			' . htmlsafechars($row['description'], ENT_QUOTES) . '</td>
			<td class="' . $class . '" align="center"><span style="font-weight: bold;">' . $subforum_name . '</span></td>
			<td class="' . $class . '" align="center">' . $name . '</td>
			<td class="' . $class . '" align="center">' . get_user_class_name($row['min_class_read']) . '</td>
			<td class="' . $class . '" align="center">' . get_user_class_name($row['min_class_write']) . '</td>
			<td class="' . $class . '" align="center">' . get_user_class_name($row['min_class_create']) . '</td>
			<td align="center" class="' . $class . '"><a href="staffpanel.php?tool=forum_manage&amp;action=forum_manage&amp;action2=edit_forum_page&amp;id=' . (int)$row['id'] . '">
			<span style="font-weight: bold;">'.$lang['fm_mp_edit'].'</span></a>&nbsp;
			<a href="javascript:confirm_delete(\'' . (int)$row['id'] . '\');"><span style="font-weight: bold;">'.$lang['fm_mp_delete'].'</span></a>
			</td></tr>';
    }
}
$HTMLOUT.= '</table><br /><br />
			<form method="post" action="staffpanel.php?tool=forum_manage&amp;action=forum_manage">
			<table width="600"  border="0" cellspacing="0" cellpadding="3" align="center">
			<tr>
			<td colspan="2" class="forum_head_dark">'.$lang['fm_mp_make'].'</td>
			</tr>
			<tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_mp_fname'].'</span></td>
			<td align="left" class="three"><input name="name" type="text" class="text_default" size="20" maxlength="60" /></td>
			</tr>
			<tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_mp_description'].'</span>  </td>
			<td align="left" class="three"><input name="desc" type="text" class="text_default" size="30" maxlength="200" /></td>
			</tr>
			<tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_mp_over2'].'</span> </td>
			<td align="left" class="three">
			<select name="over_forums">';
$forum_id = (int)$row['forum_id'];
$res = sql_query('SELECT * FROM over_forums');
while ($arr = mysqli_fetch_array($res)) {
    $i = (int)$arr['id'];
    $option_7.= '<option class="body" value="' . htmlsafechars($i) . '"' . ($forum_id == $i ? ' selected="selected"' : '') . '>' . htmlsafechars($arr['name'], ENT_QUOTES) . '</option>';
}
$HTMLOUT.= $option_7 . '</select></td></tr>
			<tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_mp_sub2'].'</span></td>
			<td align="left" class="three">
			<select name="parent_forum">
			<option class="body" value="0">'.$lang['fm_mp_none'].'</option>';
$forum_id = (int)$row['forum_id'];
$res = sql_query('SELECT * FROM forums');
while ($arr = mysqli_fetch_array($res)) {
    $i = (int)$arr['id'];
    $option_8.= '<option class="body" value="' . htmlsafechars($i) . '"' . ($forum_id == $i ? ' selected="selected"' : '') . '>' . htmlsafechars($arr['name'], ENT_QUOTES) . '</option>';
}
$HTMLOUT.= $option_8 . '</select></td></tr><tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_mp_min_r'].'</span> </td>
			<td align="left" class="three">
			<select name="min_class_read">';
for ($i = 0; $i <= $maxclass; ++$i) {
    $option_9.= '<option class="body" value="' . htmlsafechars($i) . '">' . get_user_class_name($i) . '</option>';
}
$HTMLOUT.= $option_9 . '</select></td></tr>
			<tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_mp_min_w'].'</span> </td>
			<td align="left" class="three">
			<select name="min_class_write">';
for ($i = 0; $i <= $maxclass; ++$i) {
    $option_10.= '<option class="body" value="' . htmlsafechars($i) . '">' . get_user_class_name($i) . '</option>';
}
$HTMLOUT.= $option_10 . '</select></td></tr>
			<tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_mp_min_c'].'</span> </td>
			<td align="left" class="three">
			<select name="min_class_create">';
for ($i = 0; $i <= $maxclass; ++$i) {
    $option_10.= '<option class="body" value="' . htmlsafechars($i) . '">' . get_user_class_name($i) . '</option>';
}
$HTMLOUT.= $option_10 . '</select></td></tr>
			<tr>
			<td align="right" class="three"><span style="font-weight: bold;">'.$lang['fm_mp_rank'].'</span> </td>
			<td align="left" class="three">
			<select name="sort">';
$res = sql_query('SELECT sort FROM forums');
$nr = mysqli_num_rows($res);
$maxclass = $nr + 1;
for ($i = 0; $i <= $maxclass; ++$i) {
    $option_11.= '<option class="body" value="' . htmlsafechars($i) . '">' . htmlsafechars($i) . '</option>';
}
$HTMLOUT.= $option_11 . '</select></td></tr>
			<tr>
			<td colspan="2" align="center" class="three">
			<input type="hidden" name="action2" value="add_forum" />
			<input type="submit" name="button" class="button" value="'.$lang['fm_mp_btn'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" /></td>
			</tr>
			</table></form>
	      <script type="text/javascript">
			/*<![CDATA[*/
			function confirm_delete(id)
			{
			   if(confirm(\''.$lang['fm_mp_btn'].'\'))
			   {
			      self.location.href=\'staffpanel.php?tool=forum_manage&amp;action=forum_manage&action2=delete&id=\'+id;
			   }
			}
		/*]]>*/
	</script>';
echo stdhead($lang['fm_stdhead']) . $HTMLOUT . stdfoot();
?>
