<?php

$preview = '';
$save_or_edit = (isset($_POST['edit']) ? 'edit' : (isset($_GET['edit']) ? 'edit' : 'save'));
if (isset($_POST['buttonval']) && 'save as draft' == $_POST['buttonval']) {
    //=== make sure they wrote something :P
    if (empty($_POST['subject'])) {
        stderr($lang['pm_error'], $lang['pm_draft_err']);
    }
    if (empty($_POST['body'])) {
        stderr($lang['pm_error'], $lang['pm_draft_err1']);
    }
    $body = sqlesc($_POST['body']);
    $subject = sqlesc(strip_tags($_POST['subject']));
    if ('save' === $save_or_edit) {
        sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, location, draft, unread, saved) VALUES  
                                                                        ('.sqlesc($CURUSER['id']).', '.sqlesc($CURUSER['id']).','.TIME_NOW.', '.$body.', '.$subject.', \'-2\', \'yes\',\'no\',\'yes\')') or sqlerr(__FILE__, __LINE__);
        $cache->increment('inbox_'.$CURUSER['id']);
    }
    if ('edit' === $save_or_edit) {
        sql_query('UPDATE messages SET msg = '.$body.', subject = '.$subject.' WHERE id = '.sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    }
    //=== Check if messages was saved as draft
    if (0 === mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr($lang['pm_error'], $lang['pm_draft_wasnt']);
    }
    header('Location: /pm_system.php?action=view_mailbox&box=-2&new_draft=1');
    die();
} //=== end save draft
//=== Code for preview Retros code
if (isset($_POST['buttonval']) && 'preview' == $_POST['buttonval']) {
    $subject = htmlsafechars(trim($_POST['subject']));
    $draft = trim($_POST['body']);
    $preview = '
    <table class="table table-bordered">
    <tr>
        <td colspan="2" class="colhead"><span style="font-weight: bold;">'.$lang['pm_draft_subject'].'</span>'.htmlsafechars($subject).'</td>
    </tr>
    <tr>
        <td width="80px" id="photocol">'.avatar_stuff($CURUSER).'</td>
        <td style="min-width:400px;padding:10px;vertical-align: top;text-align: left;">'.format_comment($draft).'</td>
    </tr>
    </table><br>';
} else {
    //=== Get the info
    $res = sql_query('SELECT * FROM messages WHERE id='.sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    $message = mysqli_fetch_assoc($res);
    $subject = htmlsafechars($message['subject']);
    $draft = $message['msg'];
}
//=== print out the page
//echo stdhead('Save / Edit Draft');
$HTMLOUT .= '<legend>'.$lang['pm_draft_save_edit'].''.$subject.'</legend>'.$top_links.$preview.'
        <form name="compose" action="pm_system.php" method="post">
        <input type="hidden" name="id" value="'.$pm_id.'" />
        <input type="hidden" name="'.$save_or_edit.'" value="1" />
        <input type="hidden" name="action" value="save_or_edit_draft" />
    <table class="table table-bordered">
    <tr>
        <td class="colhead" colspan="2">'.$lang['pm_edmail_edit'].'</td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">'.$lang['pm_draft_subject'].'</span></td>
        <td><input type="text" class="text_default" name="subject" value="'.$subject.'" /></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">'.$lang['pm_draft_body'].'</span></td>
        <td>'.BBcode($draft).'</td>
    </tr>
    <tr>
        <td colspan="2">
        <input type="submit" class="button is-small" name="buttonval" value="'.$lang['pm_draft_preview'].'"/>
        <input type="submit" class="button is-small" name="buttonval" value="'.$lang['pm_draft_save_as'].'" /></td>
    </tr>
    </table></form>';
