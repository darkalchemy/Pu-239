<?php

/**
 * @param $data
 */
function inactive_update($data)
{
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $users = [];

    $secs = 2 * 86400;
    $dt = (TIME_NOW - $secs);
    $res = sql_query("SELECT id FROM users
                        WHERE id != 2 AND status != 'confirmed' AND added < $dt") or sqlerr(__FILE__, __LINE__);
    while ($user = mysqli_fetch_assoc($res)) {
        $users[] = $user['id'];
    }

    $secs = 180 * 86400;
    $dt = (TIME_NOW - $secs);
    $maxclass = UC_STAFF;
    $res = sql_query("SELECT id FROM users
                        WHERE id != 2 AND immunity = 'no' AND parked = 'no' AND status = 'confirmed' AND class < $maxclass AND last_access < $dt") or sqlerr(__FILE__, __LINE__);
    while ($user = mysqli_fetch_assoc($res)) {
        $users[] = $user['id'];
    }

    $secs = 365 * 86400; // change the time to fit your needs
    $dt = (TIME_NOW - $secs);
    $maxclass = UC_STAFF;
    $res = sql_query("SELECT id FROM users
                        WHERE id != 2 AND immunity = 'no' AND parked = 'yes' AND status = 'confirmed' AND class < $maxclass AND last_access < $dt") or sqlerr(__FILE__, __LINE__);
    while ($user = mysqli_fetch_assoc($res)) {
        $users[] = $user['id'];
    }
    if (count($users) >= 1) {
        delete_cleanup(implode(', ', $users), true);
    }

    if ($data['clean_log'] && $queries > 0) {
        write_log("Inactive Cleanup: Completed using $queries queries");
    }
}

/**
 * @param      $users
 * @param bool $using_foreign_keys
 */
function delete_cleanup($users, $using_foreign_keys = true)
{
    global $cache;

    if (empty($users)) {
        return;
    }
    $cache->delete('all_users_');
    sql_query("DELETE FROM users WHERE id IN ({$users})") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM staffmessages WHERE sender IN ({$users})") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM staffmessages_answers WHERE sender IN ({$users})") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM messages WHERE sender IN ({$users})") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM messages WHERE receiver IN ({$users})") or sqlerr(__FILE__, __LINE__);

    if (!$using_foreign_keys) {
        sql_query("DELETE FROM achievements WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM ajax_chat_invitations WHERE userID IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM ajax_chat_messages WHERE userID IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM announcement_main WHERE owner_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM announcement_process WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM attachments WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM blackjack WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM blackjack_history WHERE player1_userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM blackjack_history WHERE player2_userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM blocks WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM bookmarks WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM bugs WHERE sender IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM casino WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM casino_bets WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM cheaters WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM coins WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM comments WHERE user IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM dbbackup WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM events WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM flashscores WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM forum_poll WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM forum_poll_votes WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM freeslots WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM friends WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM friends WHERE friendid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM funds WHERE user IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM happyhour WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM happylog WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM highscores WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM invite_codes WHERE sender IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM ips WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM likes WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM manage_likes WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM news WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM notconnectablepmlog WHERE user IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM now_viewing WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM offer_votes WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM offers WHERE offered_by_user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM poll_voters WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM peers WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM pmboxes WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM posts WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM rating WHERE user IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM read_posts WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM reputation WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM reputation WHERE whoadded IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM request_votes WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM requests WHERE requested_by_user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM shit_list WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM snatched WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM subtitles WHERE owner IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM subscriptions WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM thanks WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM thankyou WHERE uid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM tickets WHERE user IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM triviausers WHERE user_id IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM uploadapp WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM user_blocks WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM usercomments WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM userhits WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM usersachiev WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM ustatus WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM wiki WHERE userid IN ({$users})") or sqlerr(__FILE__, __LINE__);
    }
}
