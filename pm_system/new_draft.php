<?php

$subject = $draft = '';
if (isset($_POST['buttonval']) && 'save draft' == $_POST['buttonval']) {
    //=== make sure they wrote something :P
    if (empty($_POST['subject'])) {
        stderr($lang['pm_error'], $lang['pm_draft_err']);
    }
    if (empty($_POST['body'])) {
        stderr($lang['pm_error'], $lang['pm_draft_err1']);
    }
    $body      = sqlesc($_POST['body']);
    $subject   = sqlesc(strip_tags($_POST['subject']));
    $go_for_it = sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, location, draft, unread, saved) VALUES  
                                                                        (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($CURUSER['id']) . ',' . TIME_NOW . ', ' . $body . ', ' . $subject . ', \'-2\', \'yes\',\'no\',\'yes\')') or sqlerr(__FILE__, __LINE__);
    $cache->increment('inbox_' . $CURUSER['id']);
    $new_draft_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
    //=== Check if messages was saved as draft
    if (0 === mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr($lang['pm_error'], $lang['pm_draft_err2']);
    }
    header('Location: pm_system.php?action=view_message&new_draft=1&id=' . $new_draft_id);
    die();
}

$HTMLOUT .= $top_links . '<h1>' . $lang['pm_draft_new'] . '</h1>' . '
        <form name="compose" action="pm_system.php" method="post">
        <input type="hidden" name="id" value="' . $pm_id . '" />
        <input type="hidden" name="action" value="new_draft" />
    <table class="table table-bordered">
    <tr>
        <td class="colhead" colspan="2">' . $lang['pm_draft_add'] . '</td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_draft_subject'] . '</span></td>
        <td><input type="text" class="w-100" name="subject" value="' . $subject . '" /></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_draft_body'] . '</span></td>
        <td>' . BBcode($draft) . '</td>
    </tr>
    <tr>
        <td colspan="2">
            <div class="has-text-centered">
                <input type="submit" class="button is-small" name="buttonval" value="' . $lang['pm_draft_save'] . '"/>
            </div>
        </td>
    </tr>
    </table></form>';
