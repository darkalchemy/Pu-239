<?php

require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once INCL_DIR.'user_functions.php';
dbconn(true);
loggedinorreturn();
$lang = array_merge(load_language('global'), load_language('trivia'));
parked();

if ($CURUSER['class'] < UC_POWER_USER) {
    stderr($lang['triviaq_sorry'], $lang['trivia_you_must_be_pu']);
}

if (!empty($_POST)) {
	$qid = (int)$_POST['qid'];
	$user_id = (int)$_POST['user_id'];
	$answer = $_POST['ans'];

	if (function_exists('write_log')) {
		write_log("Trivia Q Id: $qid");
		write_log("Trivia UserId: $user_id");
	}

	$rowcount = get_row_count('triviausers', 'WHERE user_id = ' . sqlesc($user_id) . ' AND qid = ' . sqlesc($qid));

	if ($rowcount === 0) {
		$sql = "SELECT canswer FROM triviaq WHERE qid = " . sqlesc($qid);
		$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
		$result = mysqli_fetch_assoc($res);
		$canswer = $result['canswer'];
		$date = date('Y-m-d H:i:s');
		if ($user_id === 0) {
			if (function_exists('write_log')) {
				$ip = $_SERVER['REMOTE_ADDR'];
				write_log("Some asshole is using a user_id of 0!!!!!!!!!!!!!!!! $user_id $qid $date $ip");
			}
		} else {
			if ($answer == $canswer) {
				$sql = "INSERT INTO triviausers (user_id, qid, correct, date) VALUES (" . sqlesc($user_id) . ", " . sqlesc($qid) . ", 1, " . sqlesc($date) . ")";
				sql_query($sql) or sqlerr(__FILE__, __LINE__);
			} else {
				$sql = "INSERT INTO triviausers (user_id, qid, correct, date) VALUES (" . sqlesc($user_id) . ", " . sqlesc($qid) . ", 0, " . sqlesc($date) . ")";
				sql_query($sql) or sqlerr(__FILE__, __LINE__);
			}
		}
	}
}

unset($_POST);
global $INSTALLER09;

$HTMLOUT = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<title>Trivia</title>
<meta http-equiv='refresh' content='30; url=./trivia.php' />
<link rel='stylesheet' href='./templates/{$INSTALLER09['stylesheet']}/{$INSTALLER09['stylesheet']}.css' type='text/css' />
</head>
<body>";

$user_id = $CURUSER['id'];

$sql = "SELECT clean_time, clean_time - unix_timestamp(NOW()) AS remaining FROM cleanup WHERE clean_id = 82";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$remaining = $result['remaining'];

$sql = "SELECT qid FROM triviaq WHERE current = 1 AND asked = 1";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$qid = (int)$result['qid'];

$num_totalq = get_row_count('triviaq');
$num_remainingq = get_row_count('triviaq', 'WHERE asked = 0');

$sql = "SELECT gameon FROM triviasettings WHERE gamenum = 1 LIMIT 1";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$gameon = (int)$result['gameon'];

if ($remaining >= 0) {
    $sec = ltrim(date('i:s', $remaining), 0);
} else {
    $sec = 'working...';
}

$HTMLOUT .= "
    <div style='calc(width: 100% - 20px); margin: 10px;'>
        <div style='width: 45%; float: left;'>
            <h3 style='margin-top:0;'>Trivia</h3>
            <span style='font-size: .85em;'>
                {$lang['trivia_next_question']} $sec<br>
                {$lang['trivia_question']} $qid / $num_totalq <br>
                {$lang['trivia_questions_remaining']}: $num_remainingq
            </span><br><br><br>";

if ($gameon === 0 || empty($qid)) {
    $HTMLOUT .= "
            {$lang['trivia_game_stopped']}";
} else {
    if ($num_remainingq === 0) {
        $HTMLOUT .= "
            {$lang['trivia_no_more']}<br><br>{$lang['trivia_wait']}";
    } else {
        $sql = "SELECT question, answer1, answer2, answer3, answer4, answer5, asked FROM triviaq WHERE qid = " . sqlesc($qid);
		$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_assoc($res);

        $sql = "SELECT * FROM triviausers WHERE user_id = " . sqlesc($user_id) . " AND qid = " . sqlesc($qid);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $row2 = mysqli_fetch_assoc($res);
        $num_rows = count($row2);

        if ($num_rows != 0) {
            $table = "
            <table class='table table-bordered center-block'>
                <tr>
                    <th align='left' width='5%'>Username</th>
                    <th align='center' width='5%'>Ratio</th>
                    <th align='center' width='5%'>Correct</th>
                    <th align='center' width='5%'>Incorrect</th>
                </tr>";
            $sql = "SELECT t.user_id, COUNT(t.correct) AS correct, u.username FROM triviausers AS t INNER JOIN users AS u ON u.id = t.user_id WHERE t.correct=1 GROUP BY t.user_id ORDER BY COUNT(t.correct) DESC LIMIT 5";
            $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $i = 0;
            while ($result = mysqli_fetch_assoc($res)) {
                extract($result);
                $sql = "SELECT COUNT(correct) AS incorrect FROM triviausers WHERE correct = 0 AND user_id = " . sqlesc($user_id);
                $query = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $inc = mysqli_fetch_assoc($query);
                $incorrect = $inc['incorrect'];
                $class = $i++ % 2 == 0 ? 'one' : 'two';
                $table .= "
                <tr class='$class'>
                    <td align='left' width='5%'>$username</td>
                    <td align='center' width='5%'>" . sprintf("%.2f%%", $correct / ($correct + $incorrect) * 100) . "</td>
                    <td align='center' width='5%'>$correct</td>
                    <td align='center' width='5%'>$incorrect</td>
                </tr>";
            }
            $table .= "
            </table>";
            if ($row2['correct'] == 1) {
                $HTMLOUT .= "
            {$lang['trivia_correct']}
        </div>
        <div style='margin-left: 45%; height: 225px;'>$table";
            } else {
                $HTMLOUT .= "
            {$lang['trivia_incorrect']}
        </div>
        <div style='margin-left: 45%; height: 225px;'>$table";
            }
        } else {
            $HTMLOUT .= "
            " . htmlsafechars($row['question']) . "<br>
        </div>
        <div style='margin-left: 45%; padding-left: 10px;'>
            <h3>Answers:</h3>
            <ul style='list-style: none;'>
                <li style='margin-bottom: 5px;'>
                    <form id='happy' method='post' action='trivia.php'>
                        <input id='qid' type='hidden' name='qid' value='" . $qid . "'>
                        <input id='user_id' type='hidden' name='user_id' value='" . $user_id . "'>
                        <input id='user_id' type='hidden' name='ans' value='answer1'>
                        <input type='submit' value='" . htmlsafechars($row['answer1']) . "' class='btn'>
                    </form>
                </li>
                <li style='margin-bottom: 5px;'>
                    <form id='submit1' method='post' action='trivia.php'>
                        <input id='qid' type='hidden' name='qid' value='" . $qid . "'>
                        <input id='user_id' type='hidden' name='user_id' value='" . $user_id ."'>
                        <input id='user_id' type='hidden' name='ans' value='answer2'>
                        <input type='submit' value='" . htmlsafechars($row['answer2']) . "' class='btn'>
                    </form>
                </li>";

			if ($row['answer3'] != null) {
				$HTMLOUT .= "
                <li style='margin-bottom: 5px;'>
                    <form id='submit2' method='post' action='trivia.php'>
                        <input id='qid' type='hidden' name='qid' value='" . $qid . "'>
                        <input id='user_id' type='hidden' name='user_id' value='" . $user_id . "'>
                        <input id='user_id' type='hidden' name='ans' value='answer3'>
                        <input type='submit' value='" . htmlsafechars($row['answer3']) . "' class='btn'>
                    </form>
                </li>";
			}
			if ($row['answer4'] != null) {
				$HTMLOUT .= "
                <li style='margin-bottom: 5px;'>
                    <form id='submit3' method='post' action='trivia.php'>
                        <input id='qid' type='hidden' name='qid' value='" . $qid . "'>
                        <input id='user_id' type='hidden' name='user_id' value='" . $user_id . "'>
                        <input id='user_id' type='hidden' name='ans' value='answer4'>
                        <input type='submit' value='" . htmlsafechars($row['answer4']) . "' class='btn'>
                    </form>
                </li>";
			}
            if ($row['answer5'] != null) {
                $HTMLOUT .= "
                <li style='margin-bottom: 5px;'>
                    <form id='submit4' method='post' action='trivia.php'>
                        <input id='qid' type='hidden' name='qid' value='" . $qid . "'>
                        <input id='user_id' type='hidden' name='user_id' value='" . $user_id . "'>
                        <input id='user_id' type='hidden' name='ans' value='answer5'>
                        <input type='submit' value='" . htmlsafechars($row['answer5']) . "' class='btn'>
                    </form>
                </li>";
            }   

			$HTMLOUT .= "
            </ul>
        </div>";
		}
	}
}
$HTMLOUT .= "
    </div>
</body>
</html>";
echo $HTMLOUT;
