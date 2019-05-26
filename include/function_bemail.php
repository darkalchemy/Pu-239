<?php

declare(strict_types = 1);

/**
 * @param $email
 *
 * @throws Exception
 */
function check_banned_emails($email)
{
    global $lang;

    $expl = explode('@', $email);
    $wildemail = '*@' . $expl[1];
    $res = sql_query('SELECT id, comment FROM bannedemails WHERE email = ' . sqlesc($email) . ' OR email = ' . sqlesc($wildemail)) or sqlerr(__FILE__, __LINE__);
    if ($arr = mysqli_fetch_assoc($res)) {
        stderr("{$lang['takesignup_user_error']}", "{$lang['takesignup_bannedmail']}" . htmlsafechars($arr['comment']));
    }
}
