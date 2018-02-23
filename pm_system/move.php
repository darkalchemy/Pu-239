<?php

sql_query('UPDATE messages SET location = '.sqlesc($mailbox).' WHERE id = '.sqlesc($pm_id).' AND receiver = '.sqlesc($CURUSER['id']));
if (0 === mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
    stderr($lang['pm_error'], ''.$lang['pm_move_err'].'<a class="altlink" href="pm_system.php?action=view_message&id='.$pm_id.'>'.$lang['pm_move_back'].'</a>'.$lang['pm_move_msg'].'');
}
header('Location: pm_system.php?action=view_mailbox&singlemove=1&box='.$mailbox);
die();
