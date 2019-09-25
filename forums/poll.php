<?php

declare(strict_types = 1);

$topic_id = isset($_GET['topic_id']) ? (int) $_GET['topic_id'] : (isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0);
if (!is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
//=== sue me I got lazy :P but I still think  is_numeric is crappy
/**
 * @param $vote
 *
 * @return bool
 */
function is_valid_poll_vote($vote)
{
    return is_numeric($vote) && ($vote >= 0) && (floor($vote) == $vote);
}

$success = 0; //=== used for errors
//=== lets do that action 2 thing \\o\o/o//
$posted_action = strip_tags((isset($_GET['action_2']) ? $_GET['action_2'] : (isset($_POST['action_2']) ? $_POST['action_2'] : '')));
//=== add all possible actions here and check them to be sure they are ok
$valid_actions = [
    'poll_vote',
    'poll_add',
    'poll_delete',
    'poll_reset',
    'poll_close',
    'poll_open',
    'poll_edit',
    'reset_vote',
];
//=== check posted action, and if no match, kill it
$action = in_array($posted_action, $valid_actions) ? $posted_action : 1;
if ($action == 1) {
    stderr($lang['gl_error'], $lang['fe_bad_polls_action_msg']);
}
//=== casting a vote(s) ===========================================================================================//
global $CURUSER, $site_config;

switch ($action) {
    case 'poll_vote':
        //=== Get poll info
        $res_poll = sql_query('SELECT t.poll_id, t.locked, f.min_class_write, f.min_class_read, p.poll_starts, p.poll_ends, p.change_vote, p.multi_options, p.poll_closed FROM topics AS t LEFT JOIN forum_poll AS p ON t.poll_id=p.id LEFT JOIN forums AS f ON t.forum_id=f.id  WHERE t.id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_poll = mysqli_fetch_assoc($res_poll);
        //=== did they vote yet
        $res_poll_did_they_vote = sql_query('SELECT COUNT(id) FROM forum_poll_votes WHERE poll_id = ' . sqlesc($arr_poll['poll_id']) . ' AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res_poll_did_they_vote);
        $vote_count = number_format((int) $row[0]);
        $post_vote = isset($_POST['vote']) ? $_POST['vote'] : '';
        //=== let's do all the possible errors
        switch (true) {
            case !is_valid_id((int) $arr_poll['poll_id']) || count($post_vote) > $arr_poll['multi_options']: //=== no poll or trying to vote with too many options
                stderr($lang['gl_error'], '' . $lang['fe_bad_id'] . ' <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
                break;

            case $arr_poll['poll_closed'] === 'yes': //=== poll closed
                stderr($lang['gl_error'], '' . $lang['poll_poll_is_closed_you_cannot_vote'] . '. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
                break;

            case $arr_poll['poll_starts'] > TIME_NOW: //=== poll hasn't started yet
                stderr($lang['gl_error'], '' . $lang['poll_poll_hasnt_started_yet'] . ': ' . get_date((int) $arr_poll['poll_starts'], '') . '. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
                break;

            case $vote_count > 0 && $arr_poll['change_vote'] === 'no': //=== already voted and change vote set to no
                stderr($lang['gl_error'], '' . $lang['poll_you_have_already_voted'] . '. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
                break;

            case $CURUSER['class'] < $arr_poll['min_class_read']: //=== shouldn't be here!
                stderr($lang['gl_error'], $lang['gl_bad_id']);
                break;

            case $CURUSER['class'] < $arr_poll['min_class_write'] || $CURUSER['forum_post'] === 'no' || $CURUSER['status'] !== 0: //=== not alowed to post
                stderr($lang['gl_error'], '' . $lang['poll_you_are_not_permitted_to_vote_here'] . '.  <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>');
                break;

            case $arr_poll['locked'] === 'yes': //=== topic locked
                stderr($lang['gl_error'], '' . $lang['fe_this_topic_is_locked'] . '.  <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>');
                break;
        }
        //=== ok, all is good, lets enter the vote(s) into the DB
        $added = TIME_NOW;
        //=== if they selected "I just want to see the results!" only enter that one... 666 is reserved for that :)
        if (in_array('666', $post_vote)) {
            sql_query('INSERT INTO forum_poll_votes (`poll_id`, `user_id`, `options`, `added`) VALUES (' . sqlesc($arr_poll['poll_id']) . ', ' . sqlesc($CURUSER['id']) . ', 666, ' . $added . ')') or sqlerr(__FILE__, __LINE__);
            //=== all went well, send them back!
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
            die();
        } else {
            //=== if single vote (not array)
            if (is_valid_poll_vote($post_vote)) {
                sql_query('INSERT INTO forum_poll_votes (`poll_id`, `user_id`, `options`, `added`) VALUES(' . sqlesc($arr_poll['poll_id']) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($post_vote) . ', ' . $added . ')') or sqlerr(__FILE__, __LINE__);
                $success = 1;
            } else {
                foreach ($post_vote as $votes) {
                    $vote = 0 + $votes;
                    if (is_valid_poll_vote($vote)) {
                        sql_query('INSERT INTO forum_poll_votes (`poll_id`, `user_id`, `options`, `added`) VALUES(' . sqlesc($arr_poll['poll_id']) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($vote) . ', ' . $added . ')') or sqlerr(__FILE__, __LINE__);
                        $success = 1;
                    }
                }
            }
            //=== did it work?
            if ($success != 1) {
                stderr($lang['gl_error'], '' . sprintf($lang['poll_something_went_wrong_the_poll_was_not_x'], 'counted') . '!. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
            }
            //=== all went well, send them back!
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
            die();
        } //=== end of else
        break; //=== end casting a vote(s)
    //=== resetting vote ============================================================================================//

    case 'reset_vote':
        //=== Get poll info
        $res_poll = sql_query('SELECT t.poll_id, t.locked, f.min_class_write, f.min_class_read, p.poll_starts, p.poll_ends, p.change_vote, p.multi_options, p.poll_closed FROM topics AS t LEFT JOIN forum_poll AS p ON t.poll_id=p.id LEFT JOIN forums AS f ON t.forum_id=f.id  WHERE t.id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_poll = mysqli_fetch_assoc($res_poll);
        //=== did they vote yet
        $res_poll_did_they_vote = sql_query('SELECT COUNT(id) FROM forum_poll_votes WHERE poll_id=' . sqlesc($arr_poll['poll_id']) . ' AND user_id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res_poll_did_they_vote);
        $vote_count = number_format($row[0]);
        //=== let's do all the possible errors
        switch (true) {
            case !is_valid_id((int) $arr_poll['poll_id']): //=== no poll
                stderr($lang['gl_error'], '' . $lang['fe_bad_id'] . ' <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
                break;

            case $arr_poll['poll_closed'] === 'yes': //=== poll closed
                stderr($lang['gl_error'], '' . $lang['poll_poll_is_closed_you_cannot_vote'] . '. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
                break;

            case $arr_poll['poll_starts'] > TIME_NOW: //=== poll hasn't started yet
                stderr($lang['gl_error'], '' . $lang['poll_poll_hasnt_started_yet'] . ': ' . get_date((int) $arr_poll['poll_starts'], '') . '. <a href="forums.php?action=view_topic&topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
                break;

            case $arr_poll['change_vote'] === 'no': //===  vote set to no changes
                stderr($lang['gl_error'], '' . $lang['poll_you_have_already_voted'] . '. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
                break;

            case $CURUSER['class'] < $arr_poll['min_class_read']: //=== shouldn't be here!
                stderr($lang['gl_error'], $lang['gl_bad_id']);
                break;

            case $CURUSER['class'] < $arr_poll['min_class_write'] || $CURUSER['forum_post'] === 'no' || $CURUSER['status'] !== 0: //=== not alowed to vote
                stderr($lang['gl_error'], '' . $lang['poll_you_are_not_permitted_to_vote_here'] . '.  <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>');
                break;

            case $arr_poll['locked'] === 'yes': //=== topic locked
                stderr($lang['gl_error'], '' . $lang['fe_this_topic_is_locked'] . '.  <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>');
                break;
        }
        //=== ok all is well, let then change their votes :)
        sql_query('DELETE FROM forum_poll_votes WHERE poll_id=' . sqlesc($arr_poll['poll_id']) . ' AND user_id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        //=== all went well, send them back!
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break;
    //=== adding a poll ============================================================================================//

    case 'poll_add':
        //=== be sure there is no poll yet :P
        $res_poll = sql_query('SELECT poll_id, user_id, topic_name FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_poll = mysqli_fetch_assoc($res_poll);
        $poll_id = (int) $arr_poll['poll_id'];
        $user_id = (int) $arr_poll['user_id'];
        if (is_valid_id($poll_id)) {
            stderr($lang['gl_error'], '' . $lang['poll_there_can_only_be_one_poll_per_topic'] . '. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
        }
        if ($user_id != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr($lang['gl_error'], '' . $lang['poll_only_the_topic_owner_or_staff_can_start_a_poll'] . '. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
        }
        //=== enter it into the DB \o/
        if (isset($_POST['add_the_poll']) && $_POST['add_the_poll'] == 1) {
            //=== post stuff
            $poll_question = isset($_POST['poll_question']) ? htmlsafechars($_POST['poll_question']) : '';
            $poll_answers = isset($_POST['poll_answers']) ? htmlsafechars($_POST['poll_answers']) : '';
            $poll_ends = (isset($_POST['poll_ends']) && $_POST['poll_ends'] > 168) ? 1356048000 : (TIME_NOW + $_POST['poll_ends'] * 86400);
            $poll_starts = (isset($_POST['poll_starts']) && $_POST['poll_starts'] === 0) ? TIME_NOW : (TIME_NOW + $_POST['poll_starts'] * 86400);
            $poll_starts = $poll_starts > ($poll_ends + 1) ? TIME_NOW : $poll_starts;
            $change_vote = (isset($_POST['change_vote']) && $_POST['change_vote'] === 'yes') ? 'yes' : 'no';
            if ($poll_answers === '' && $poll_question === '') {
                stderr($lang['gl_error'], '' . $lang['poll_be_sure_to_fill_in_the_question'] . '.');
            }
            //=== make it an array with a max of 20 options
            $break_down_poll_options = explode("\n", $poll_answers);
            //=== be sure there are no blank options
            for ($i = 0; $i < count($break_down_poll_options); ++$i) {
                if (strlen($break_down_poll_options[$i]) < 2) {
                    stderr($lang['gl_error'], $lang['fe_no_blank_lines_in_poll']);
                }
            }
            if ($i > 20 || $i < 2) {
                stderr($lang['gl_error'], '' . $lang['fe_there_is_min_max_options'] . ' ' . $i . '.');
            }
            $multi_options = ((isset($_POST['multi_options']) && $_POST['multi_options'] <= $i) ? (int) $_POST['multi_options'] : 1);
            $poll_options = json_encode($break_down_poll_options);
            sql_query('INSERT INTO `forum_poll` (`user_id` ,`question` ,`poll_answers` ,`number_of_options` ,`poll_starts` ,`poll_ends` ,`change_vote` ,`multi_options`) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($poll_question) . ', ' . sqlesc($poll_options) . ', ' . $i . ', ' . $poll_starts . ', ' . $poll_ends . ', \'' . $change_vote . '\', ' . $multi_options . ')') or sqlerr(__FILE__, __LINE__);
            $poll_id = ((is_null($___mysqli_res = mysqli_insert_id($mysqli))) ? false : $___mysqli_res);
            if (is_valid_id((int) $poll_id)) {
                sql_query('UPDATE `topics` SET poll_id=' . sqlesc($poll_id) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
            } else {
                stderr($lang['gl_error'], '' . sprintf($lang['poll_something_went_wrong_the_poll_was_not_x'], 'added') . '.');
            }
            //=== all went well, send them back!
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
            die();
        } //=== end of posting poll to DB
        //=== ok looks like they can be here
        //=== options for amount of options lol
        for ($i = 2; $i < 21; ++$i) {
            $options .= '<option class="body" value="' . $i . '">' . $i . ' options</option>';
        }
        $HTMLOUT .= '<table class="main">
	<tr>
		<td class="embedded">
		<h1>' . $lang['poll_add_poll_in'] . ' "<a class="is-link" href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($arr_poll['topic_name']) . '</a>"</h1>
	<form action="forums.php?action=poll" method="post" name="poll" accept-charset="utf-8">
		<input type="hidden" name="topic_id" value="' . $topic_id . '">
		<input type="hidden" name="action_2" value="poll_add">
		<input type="hidden" name="add_the_poll" value="1">
	<table>
	<tr>
		<td colspan="3"><span style="color: white; font-weight: bold;"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/poll.gif" alt="' . $lang['fe_poll'] . '" title="' . $lang['fe_poll'] . '" style="vertical-align: middle;"> ' . $lang['poll_add_poll_to_topic'] . '!</span></td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/question.png" alt="' . $lang['fe_smilee_question'] . '" title="' . $lang['fe_smilee_question'] . '" width="24" style="vertical-align: middle;"></td>
		<td><span style="white - space:nowrap;font-weight: bold;">' . $lang['poll_question'] . ':</span></td>
		<td><input type="text" name="poll_question" class="w-100" value=""></td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/options.gif" alt="' . $lang['poll_options'] . '" title="' . $lang['poll_options'] . '" width="24" style="vertical-align: middle;"></td>
		<td><span style="white - space:nowrap;font-weight: bold;">' . $lang['poll_answers'] . ':</span></td>
		<td><textarea cols="30" rows="4" name="poll_answers" class="text_area_small"></textarea>
		<br> ' . $lang['poll_one_option_per_line_min_2_op_max_20_options_bbcode_is_enabled.'] . '</td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/clock.png" alt="' . $lang['poll_clock'] . '" title="' . $lang['poll_clock'] . '" width="30" style="vertical-align: middle;"></td>
		<td><span style="white - space:nowrap;font-weight: bold;">' . $lang['poll_starts'] . ':</span></td>
		<td><select name="poll_starts">
											<option class="body" value="0">' . $lang['poll_start_now'] . '!</option>
											<option class="body" value="1">' . sprintf($lang['poll_in_x_day'], 1) . '</option>
											<option class="body" value="2">' . sprintf($lang['poll_in_x_days'], 2) . '</option>
											<option class="body" value="3">' . sprintf($lang['poll_in_x_days'], 3) . '</option>
											<option class="body" value="4">' . sprintf($lang['poll_in_x_days'], 4) . '</option>
											<option class="body" value="5">' . sprintf($lang['poll_in_x_days'], 5) . '</option>
											<option class="body" value="6">' . sprintf($lang['poll_in_x_days'], 6) . '</option>
											<option class="body" value="7">' . sprintf($lang['poll_in_x_week'], 1) . '</option>
											</select> ' . $lang['poll_when_to_start_the_poll_default_is_start_now'] . '!" </td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/stop.png" alt = "' . $lang['poll_stop'] . '" title="' . $lang['poll_stop'] . '" width="20" style="vertical-align: middle;"></td>
		<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_ends'] . ':</span></td>
		<td><select name = "poll_ends">
											<option class="body" value = "1356048000">' . $lang['poll_run_forever'] . ' </option>
											<option class="body" value = "1">' . sprintf($lang['poll_in_x_day'], 1) . ' </option>
											<option class="body" value = "2">' . sprintf($lang['poll_in_x_days'], 2) . ' </option>
											<option class="body" value = "3">' . sprintf($lang['poll_in_x_days'], 3) . ' </option>
											<option class="body" value = "4">' . sprintf($lang['poll_in_x_days'], 4) . ' </option>
											<option class="body" value = "5">' . sprintf($lang['poll_in_x_days'], 5) . ' </option>
											<option class="body" value = "6">' . sprintf($lang['poll_in_x_days'], 6) . ' </option>
											<option class="body" value = "7">' . sprintf($lang['poll_in_x_week'], 1) . ' </option>
											<option class="body" value = "14">' . sprintf($lang['poll_in_x_weeks'], 2) . ' </option>
											<option class="body" value = "21">' . sprintf($lang['poll_in_x_weeks'], 3) . ' </option>
											<option class="body" value = "28">' . sprintf($lang['poll_in_x_month'], 1) . ' </option>
											<option class="body" value = "56">' . sprintf($lang['poll_in_x_months'], 2) . ' </option>
											<option class="body" value = "84">' . sprintf($lang['poll_in_x_months'], 3) . ' </option>
											</select>' . $lang['poll_how_long_should_this_poll_run'] . ' ? ' . $lang['poll_default_is'] . ' "' . $lang['poll_run_forever'] . '" </td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/multi.gif" alt = "' . $lang['poll_multi'] . '" title="' . $lang['poll_multi'] . '" width="20" style="vertical-align: middle;"></td>
		<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_multi_options'] . ':</span></td>
		<td><select name = "multi_options">
											<option class="body" value = "1">' . $lang['poll_single_option'] . '!</option>' . $options . '
											</select>' . $lang['poll_allow_members_to_have_more_then_one_selection'] . ' ? ' . $lang['poll_default_is'] . ' "' . $lang['poll_single_option'] . '!" </td>
	</tr>
	<tr>
		<td></td>
		<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_change_vote'] . ':</span></td>
		<td><input name = "change_vote" value = "yes" type = "radio"' . ($change_vote === 'yes' ? ' checked = "checked"' : '') . '>Yes
													<input name = "change_vote" value = "no" type = "radio"' . ($change_vote === 'no' ? ' checked = "checked"' : '') . '>No   <br>' . $lang['poll_allow_members_to_change_their_vote'] . ' ? ' . $lang['poll_default_is'] . ' "no" </td>
	</tr>
	<tr>
		<td colspan="3">
		<input type = "submit" name = "button" class="button" value = "' . $lang['fe_add_poll'] . '!"></td>
	</tr>
	</table></form><br></td>
	</tr>
	</table>';
        $HTMLOUT .= $the_bottom_of_the_page;
        break; //=== end add poll
    //=== deleting a poll ============================================================================================//

    case 'poll_delete':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr($lang['gl_error'], $lang['poll_non_staff_poll_del_msg']);
        }
        //=== be sure there is a poll to delete :P
        $res_poll = sql_query('SELECT poll_id FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_poll = mysqli_fetch_row($res_poll);
        $poll_id = $arr_poll[0];
        if (!is_valid_id($poll_id)) {
            stderr($lang['gl_error'], '' . $lang['fe_bad_id'] . ' .. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . ' </a>.');
        } else {
            //=== delete the poll
            sql_query('DELETE FROM forum_poll WHERE id=' . sqlesc($poll_id)) or sqlerr(__FILE__, __LINE__);
            //=== delete the votes
            sql_query('DELETE FROM forum_poll_votes WHERE poll_id=' . sqlesc($poll_id)) or sqlerr(__FILE__, __LINE__);
            //=== remove poll refrence from topic
            sql_query('UPDATE topics SET `poll_id` = 0 WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
            $success = 1;
        }
        //=== did it work?
        if ($success != 1) {
            stderr($lang['gl_error'], '' . sprintf($lang['poll_something_went_wrong_the_poll_was_not_x'], 'deleted') . '!. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . ' </a>.');
        }
        //=== all went well, send them back!
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic & topic_id=' . $topic_id);
        die();
        break; //=== end delete poll
    //=== reseting a poll ============================================================================================//

    case 'poll_reset':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr($lang['gl_error'], $lang['poll_non_staff_poll_reset_msg']);
        }
        //=== be sure there is a poll to reset :P
        $res_poll = sql_query('SELECT poll_id FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_poll = mysqli_fetch_row($res_poll);
        $poll_id = (int) $arr_poll[0];
        if (!is_valid_id($poll_id)) {
            stderr($lang['gl_error'], '' . $lang['fe_bad_id'] . ' .. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . ' </a>.');
        } else {
            //=== delete the votes
            sql_query('DELETE FROM forum_poll_votes WHERE poll_id=' . sqlesc($poll_id)) or sqlerr(__FILE__, __LINE__);
            $success = 1;
        }
        //=== did it work?
        if ($success != 1) {
            stderr($lang['gl_error'], '' . sprintf($lang['poll_something_went_wrong_the_poll_was_not_x'], 'reset') . '!. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . ' </a>.');
        }
        //=== all went well, send them back!
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic & topic_id=' . $topic_id);
        die();
        break; //=== end reset poll
    //=== closing a poll ============================================================================================//

    case 'poll_close':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr($lang['gl_error'], $lang['poll_non_staff_poll_close_msg']);
        }
        //=== be sure there is a poll to close :P
        $res_poll = sql_query('SELECT poll_id FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_poll = mysqli_fetch_row($res_poll);
        $poll_id = (int) $arr_poll[0];
        if (!is_valid_id($poll_id)) {
            stderr($lang['gl_error'], '' . $lang['fe_bad_id'] . ' .. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . ' </a>.');
        } else {
            //=== close the poll
            sql_query('UPDATE forum_poll SET `poll_closed` = \'yes\', poll_ends = ' . TIME_NOW . ' WHERE id=' . sqlesc($poll_id)) or sqlerr(__FILE__, __LINE__);
            $success = 1;
        }
        //=== did it work?
        if ($success != 1) {
            stderr($lang['gl_error'], '' . sprintf($lang['poll_something_went_wrong_the_poll_was_not_x'], 'closed') . '!. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
        }
        //=== all went well, send them back!
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break; //=== end of poll close
    //=== opening a poll  (either after it was closed, or timed out) ===============================================================================//

    case
    'poll_open':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr($lang['gl_error'], $lang['poll_non_staff_poll_open_msg']);
        }
        //=== be sure there is a poll to open :P
        $res_poll = sql_query('SELECT poll_id FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_poll = mysqli_fetch_row($res_poll);
        $poll_id = (int) $arr_poll[0];
        if (!is_valid_id($poll_id)) {
            stderr($lang['gl_error'], '' . $lang['fe_bad_id'] . '.. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
        } else {
            //=== open the poll
            sql_query('UPDATE forum_poll SET `poll_closed` = \'no\', poll_ends = \'1356048000\' WHERE id=' . sqlesc($poll_id)) or sqlerr(__FILE__, __LINE__);
            $success = 1;
        }
        //=== did it work?
        if ($success != 1) {
            stderr($lang['gl_error'], '' . sprintf($lang['poll_something_went_wrong_the_poll_was_not_x'], 'opened') . '!. <a href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '" class="is-link">' . $lang['fe_back_to_topic'] . '</a>.');
        }
        //=== all went well, send them back!
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break; //=== end of open poll
    //=== edit a poll ============================================================================================//

    case 'poll_edit':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr($lang['gl_error'], $lang['poll_non_staff_poll_edit_msg']);
        }
        //=== be sure there is a poll to edit :P
        $res_poll = sql_query('SELECT poll_id, topic_name FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_poll = mysqli_fetch_assoc($res_poll);
        $poll_id = (int) $arr_poll['poll_id'];
        if (!is_valid_id($poll_id)) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        //=== enter it into the DB \o/
        if (isset($_POST['do_poll_edit']) && $_POST['do_poll_edit'] == 1) {
            //=== post stuff
            $poll_question = isset($_POST['poll_question']) ? htmlsafechars($_POST['poll_question']) : '';
            $poll_answers = isset($_POST['poll_answers']) ? htmlsafechars($_POST['poll_answers']) : '';
            $poll_ends = (isset($_POST['poll_ends']) && $_POST['poll_ends'] == 1356048000) ? 1356048000 : (TIME_NOW + $_POST['poll_ends'] * 86400);
            $poll_starts = (isset($_POST['poll_starts']) && $_POST['poll_starts'] == 0) ? TIME_NOW : (TIME_NOW + $_POST['poll_starts'] * 86400);
            $poll_starts = $poll_starts > ($poll_ends + 1) ? TIME_NOW : $poll_starts;
            $change_vote = isset($_POST['change_vote']) && $_POST['change_vote'] == 'yes' ? 'yes' : 'no';
            if ($poll_answers === '' || $poll_question === '') {
                stderr($lang['gl_error'], '' . $lang['poll_be_sure_to_fill_in_the_question'] . '.');
            }
            //=== make it an array with a max of 20 options
            $break_down_poll_options = explode("\n", $poll_answers);
            //=== be sure there are no blank options
            for ($i = 0; $i < count($break_down_poll_options); ++$i) {
                if (strlen($break_down_poll_options[$i]) < 2) {
                    stderr($lang['gl_error'], $lang['fe_no_blank_lines_in_poll']);
                }
            }
            if ($i > 20 || $i < 2) {
                stderr($lang['gl_error'], '' . $lang['fe_there_is_min_max_options'] . ' ' . $i . '.');
            }
            $multi_options = ((isset($_POST['multi_options']) && $_POST['multi_options'] <= $i) ? (int) $_POST['multi_options'] : 1);
            $poll_options = json_encode($break_down_poll_options);
            sql_query('UPDATE forum_poll  SET question = ' . sqlesc($poll_question) . ', poll_answers = ' . sqlesc($poll_options) . ', number_of_options = ' . $i . ' , poll_starts = ' . $poll_starts . ' , poll_ends = ' . $poll_ends . ', change_vote = \'' . $change_vote . '\', multi_options = ' . $multi_options . ', poll_closed = \'no\' WHERE id=' . sqlesc($poll_id)) or sqlerr(__FILE__, __LINE__);
            //=== delete the votes
            sql_query('DELETE FROM forum_poll_votes WHERE poll_id=' . sqlesc($poll_id)) or sqlerr(__FILE__, __LINE__);
            //=== send them back!
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
            die();
        } //=== end of posting poll to DB
        //=== get poll stuff to edit
        $res_edit = sql_query('SELECT * FROM forum_poll WHERE id=' . sqlesc($poll_id)) or sqlerr(__FILE__, __LINE__);
        $arr_edit = mysqli_fetch_assoc($res_edit);
        $poll_question = strip_tags($arr_edit['question']);
        $poll_answers = json_decode($arr_edit['poll_answers'], true);
        $number_of_options = $arr_edit['number_of_options'];
        $poll_starts = (int) $arr_edit['poll_starts'];
        $poll_ends = (int) $arr_edit['poll_ends'];
        $change_vote = htmlsafechars($arr_edit['change_vote']);
        $multi_options = htmlsafechars($arr_edit['multi_options']);
        //=== make the answers all readable
        $poll_answers = implode("\n", $poll_answers);
        //=== options for amount of options lol
        for ($i = 2; $i < 21; ++$i) {
            $options .= '<option class="body" value="' . $i . '" ' . ($multi_options == $i ? 'selected="selected"' : '') . '>' . $i . ' options</option>';
        }
        //=== ok looks like they can be here
        $HTMLOUT .= '
	<form action="forums.php?action=poll" method="post" name="poll" accept-charset="utf-8">
	<table class="main">
	<tr>
		<td class="embedded">
		<h1>' . $lang['poll_edit_poll_in'] . ' <a class="is-link" href="forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($arr_poll['topic_name']) . ' </a>"</h1>
		<input type="hidden" name="topic_id" value="' . $topic_id . '">
		<input type="hidden" name="action_2" value="poll_edit">
		<input type="hidden" name="do_poll_edit" value="1">

	<table>
	<tr>
		<td colspan="3"><span style="color: white; font-weight: bold;"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/poll.gif" alt="' . $lang['fe_poll'] . '" title="' . $lang['fe_poll'] . '" style="vertical-align: middle;"> ' . $lang['poll_add_poll_to_topic'] . '!</span>
		        ' . $lang['poll_editing_the_poll_will_re_set_all_the_votes'] . '</td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/question.png" alt="' . $lang['fe_smilee_question'] . '" title="' . $lang['fe_smilee_question'] . '" width="24" style="vertical-align: middle;"></td>
		<td><span style="white - space:nowrap;font-weight: bold;">' . $lang['poll_question'] . ':</span></td>
		<td><input type="text" name="poll_question" class="w-100" value="' . $poll_question . '"></td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/options.gif" alt="' . $lang['poll_options'] . '" title="' . $lang['poll_options'] . '" width="24" style="vertical-align: middle;"></td>
		<td><span style="white - space:nowrap;font-weight: bold;">' . $lang['poll_answers'] . ':</span></td>
		<td><textarea cols="30" rows="4" name="poll_answers" class="text_area_small">' . strip_tags($poll_answers) . '</textarea><br> 
		' . $lang['poll_one_option_per_line_min_2_op_max_20_options_bbcode_is_enabled.'] . '</td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/clock.png" alt="' . $lang['poll_clock'] . '" title="' . $lang['poll_clock'] . '" width="30" style="vertical-align: middle;"></td>
		<td><span style="white - space:nowrap;font-weight: bold;">' . $lang['poll_starts'] . ':</span></td>
		<td><select name="poll_starts">
											<option class="body" value="0">' . $lang['poll_start_now'] . '!</option>
											<option class="body" value="1">' . sprintf($lang['poll_in_x_day'], 1) . '</option>
											<option class="body" value="2">' . sprintf($lang['poll_in_x_days'], 2) . '</option>
											<option class="body" value="3">' . sprintf($lang['poll_in_x_days'], 3) . '</option>
											<option class="body" value="4">' . sprintf($lang['poll_in_x_days'], 4) . '</option>
											<option class="body" value="5">' . sprintf($lang['poll_in_x_days'], 5) . '</option>
											<option class="body" value="6">' . sprintf($lang['poll_in_x_days'], 6) . '</option>
											<option class="body" value="7">' . sprintf($lang['poll_in_x_week'], 1) . '</option>
											</select> ' . $lang['poll_when_to_start_the_poll_default_is_start_now'] . '!" < br>
											Poll set to start: ' . get_date((int) $poll_starts, '') . ' </td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/stop.png" alt = "' . $lang['poll_stop'] . '" title="' . $lang['poll_stop'] . '" width="20" style="vertical-align: middle;"></td>
		<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_ends'] . ':</span></td>
		<td><select name = "poll_ends">
											<option class="body" value = "1356048000">' . $lang['poll_run_forever'] . ' </option>
											<option class="body" value = "1">' . sprintf($lang['poll_in_x_day'], 1) . ' </option>
											<option class="body" value = "2">' . sprintf($lang['poll_in_x_days'], 2) . ' </option>
											<option class="body" value = "3">' . sprintf($lang['poll_in_x_days'], 3) . ' </option>
											<option class="body" value = "4">' . sprintf($lang['poll_in_x_days'], 4) . ' </option>
											<option class="body" value = "5">' . sprintf($lang['poll_in_x_days'], 5) . ' </option>
											<option class="body" value = "6">' . sprintf($lang['poll_in_x_days'], 6) . ' </option>
											<option class="body" value = "7">' . sprintf($lang['poll_in_x_week'], 1) . ' </option>
											<option class="body" value = "14">' . sprintf($lang['poll_in_x_weeks'], 2) . ' </option>
											<option class="body" value = "21">' . sprintf($lang['poll_in_x_weeks'], 3) . ' </option>
											<option class="body" value = "28">' . sprintf($lang['poll_in_x_month'], 1) . ' </option>
											<option class="body" value = "56">' . sprintf($lang['poll_in_x_months'], 2) . ' </option>
											<option class="body" value = "84">' . sprintf($lang['poll_in_x_months'], 3) . ' </option>
											<option class="body" value = "168">in 6 months </option>
											</select>' . $lang['poll_how_long_should_this_poll_run'] . ' ? ' . $lang['poll_default_is'] . ' "' . $lang['poll_run_forever'] . '" < br>
											Poll set to end: ' . ($poll_ends === 1356048000 ? '' . $lang['poll_run_forever'] . '' : get_date((int) $poll_ends, '')) . ' </td>
	</tr>
	<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/multi.gif" alt = "' . $lang['poll_multi'] . '" title="' . $lang['poll_multi'] . '" width = "20" style="vertical-align: middle;"></td>
		<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_multi_options'] . ':</span></td>
		<td><select name = "multi_options">
											<option class="body" value = "1" ' . ($multi_options == 1 ? 'selected = "selected"' : '') . '>' . $lang['poll_single_option'] . '!</option>' . $options . '
											</select>' . $lang['poll_allow_members_to_have_more_then_one_selection'] . ' ? ' . $lang['poll_default_is'] . ' "' . $lang['poll_single_option'] . '!" </td>
	</tr>
	<tr>
		<td></td>
		<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_change_vote'] . ':</span></td>
		<td><input name = "change_vote" value = "yes" type = "radio"' . ($change_vote === 'yes' ? ' checked = "checked"' : '') . '>Yes
													<input name = "change_vote" value = "no" type = "radio"' . ($change_vote == 'no' ? ' checked = "checked"' : '') . '>No   <br>' . $lang['poll_allow_members_to_change_their_vote'] . ' ? ' . $lang['poll_default_is'] . ' "no" </td>
	</tr>
	<tr>
	<td colspan="3">
	<input type = "submit" name = "button" class="button" value = "' . $lang['poll_edit_poll'] . '!"></td>
	</tr>
	</table><br></td>
	</tr>
	</table></form>';
        $HTMLOUT .= $the_bottom_of_the_page;
        break; //=== end edit poll

    default:
        //=== at the end of the day, if they are messing about doing what they shouldn't, let's give then what for!
        stderr($lang['gl_error'], $lang['poll_epic_fail_last_msg']);
        die();
} //=== end switch all actions
