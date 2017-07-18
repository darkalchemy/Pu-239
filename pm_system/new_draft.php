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
$preview = $subject = $draft = '';
//=== don't allow direct access
if (!defined('BUNNY_PM_SYSTEM')) {
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
if (isset($_POST['buttonval']) && $_POST['buttonval'] == 'save draft') {
    //=== make sure they wrote something :P
    if (empty($_POST['subject'])) stderr($lang['pm_error'], $lang['pm_draft_err']);
    if (empty($_POST['body'])) stderr($lang['pm_error'], $lang['pm_draft_err1']);
    $body = sqlesc($_POST['body']);
    $subject = sqlesc(strip_tags($_POST['subject']));
    $go_for_it = sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, location, draft, unread, saved) VALUES  
                                                                        (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($CURUSER['id']) . ',' . TIME_NOW . ', ' . $body . ', ' . $subject . ', \'-2\', \'yes\',\'no\',\'yes\')') or sqlerr(__FILE__, __LINE__);
    $mc1->delete_value('inbox_new_' . $CURUSER['id']);
    $mc1->delete_value('inbox_new_sb_' . $CURUSER['id']);
    $new_draft_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    //=== Check if messages was saved as draft
    if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) === 0) stderr($lang['pm_error'], $lang['pm_draft_err2']);
    header('Location: pm_system.php?action=view_message&new_draft=1&id=' . $new_draft_id);
    die();
} //=== end save draft
//=== Code for preview Retros code
if (isset($_POST['buttonval']) && $_POST['buttonval'] == 'preview') {
    $subject = htmlsafechars(trim($_POST['subject']));
    $draft = trim($_POST['body']);
    $preview = '
    <table class="table table-bordered">
    <tr>
        <td align="left" colspan="2" class="colhead"><span style="font-weight: bold;">subject: </span>' . htmlsafechars($subject) . '</td>
    </tr>
    <tr>
        <td align="center" valign="top" class="one" width="80px" id="photocol">' . avatar_stuff($CURUSER) . '</td>
        <td class="two" style="min-width:400px;padding:10px;vertical-align: top;text-align: left;">' . format_comment($draft) . '</td>
    </tr>
    </table><br />';
}
//=== print out the page
$HTMLOUT.= '<h1>' . $lang['pm_draft_new'] . '</h1>' . $top_links . $preview . '
        <form name="compose" action="pm_system.php" method="post">
        <input type="hidden" name="id" value="' . $pm_id . '" />
        <input type="hidden" name="action" value="new_draft" />
    <table class="table table-bordered">
    <tr>
        <td class="colhead" align="left" colspan="2">' . $lang['pm_draft_add'] . '</td>
    </tr>
    <tr>
        <td class="one" valign="top" align="right"><span style="font-weight: bold;">' . $lang['pm_draft_subject'] . '</span></td>
        <td class="one" valign="top" align="left"><input type="text" class="text_default" name="subject" value="' . $subject . '" /></td>
    </tr>
    <tr>
        <td class="one" valign="top" align="right"><span style="font-weight: bold;">' . $lang['pm_draft_body'] . '</span></td>
        <td class="one" valign="top" align="left">' . BBcode($draft, FALSE) . '</td>
    </tr>
    <tr>
		<div class="btn-group">
        <td colspan="two" align="center">
        <input type="submit" class="btn btn-primary" name="buttonval" value="' . $lang['pm_draft_preview'] . '"/>
        <input type="submit" class="btn btn-primary" name="buttonval" value="' . $lang['pm_draft_save'] . '"/></td>
		</div>
    </tr>
    </table></form>';
?>
