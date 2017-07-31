<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn(true);
loggedinorreturn();
$lang = array_merge(load_language('global'), load_language('trivia'));
parked();

if ($CURUSER['class'] < UC_USER) {
    stderr($lang['trivia_sorry'], $lang['trivia_you_must_be_pu']);
}

$sql = 'SELECT qid FROM triviaq WHERE current = 1 AND asked = 1';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$qid = (int)$result['qid'];

if (!empty($_POST) && (int)$_POST['qid'] === $qid) {
    $qid = (int)$_POST['qid'];
    $user_id = (int)$_POST['user_id'];
    $answer = $_POST['ans'];

    if (function_exists('write_log')) {
        write_log("Trivia Q Id: $qid");
        write_log("Trivia UserId: $user_id");
    }

    $rowcount = get_row_count('triviausers', 'WHERE user_id = ' . sqlesc($user_id) . ' AND qid = ' . sqlesc($qid));

    if ($rowcount === 0) {
        $sql = 'SELECT canswer FROM triviaq WHERE qid = ' . sqlesc($qid);
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
                $sql = 'INSERT INTO triviausers (user_id, qid, correct, date) VALUES (' . sqlesc($user_id) . ', ' . sqlesc($qid) . ', 1, ' . sqlesc($date) . ')';
                sql_query($sql) or sqlerr(__FILE__, __LINE__);
            } else {
                $sql = 'INSERT INTO triviausers (user_id, qid, correct, date) VALUES (' . sqlesc($user_id) . ', ' . sqlesc($qid) . ', 0, ' . sqlesc($date) . ')';
                sql_query($sql) or sqlerr(__FILE__, __LINE__);
            }
        }
    }
}

unset($_POST);
global $INSTALLER09;
$HTMLOUT = '';
$user_id = $CURUSER['id'];

$sql = "SELECT clean_time, clean_time - unix_timestamp(NOW()) AS remaining FROM cleanup WHERE clean_file = 'trivia_update.php'";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$remaining = (int)$result['remaining'];
$date = new DateTime("@{$result['clean_time']}");
$date_string = $date->format('D, d M y H:i:s O');

$num_totalq = get_row_count('triviaq');
$num_remainingq = get_row_count('triviaq', 'WHERE asked = 0');

$sql = 'SELECT gamenum FROM triviasettings WHERE gameon = 1 LIMIT 1';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$gamenum = (int)$result['gamenum'];

$refresh = 10;
if ($remaining >= 1) {
    $refresh = $remaining;
}

$HTMLOUT = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<title>Trivia</title>
<meta http-equiv='refresh' content={$refresh}; url=./trivia.php' />
<link rel='stylesheet' href='./templates/{$INSTALLER09['stylesheet']}/default.css' type='text/css' />
</head>
<body>";

$HTMLOUT .= "
    <div style='calc(width: 100% - 20px); margin: 10px; font-size: 1.25em;'>
        <div style='width: 30%; float: left;'>
            <span'>";

if ($remaining >= 1) {
    $HTMLOUT .= "
                {$lang['trivia_next_question']}
                <span id='clockdiv'>
                    <span class='minutes'></span>:<span class='seconds'></span>
                </span><br>
                {$lang['trivia_question']} $qid / $num_totalq <br>
                {$lang['trivia_questions_remaining']}: $num_remainingq<br>";
} else {
    $HTMLOUT .= '<br><br><br>';
}
$HTMLOUT .= '
            </span><br><br><br>';

if (empty($gamenum) || empty($qid)) {
    $HTMLOUT .= "
            {$lang['trivia_game_stopped']}";
} else {
    if ($num_remainingq === 0) {
        $HTMLOUT .= "
            {$lang['trivia_no_more']}<br><br>{$lang['trivia_wait']}";
    } else {
        $sql = 'SELECT question, answer1, answer2, answer3, answer4, answer5, asked FROM triviaq WHERE qid = ' . sqlesc($qid);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_assoc($res);

        $sql = 'SELECT * FROM triviausers WHERE user_id = ' . sqlesc($user_id) . ' AND qid = ' . sqlesc($qid);
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
            $sql = 'SELECT t.user_id, COUNT(t.correct) AS correct, u.username, (SELECT COUNT(correct) AS incorrect FROM triviausers WHERE correct = 0 AND user_id = t.user_id) AS incorrect
                        FROM triviausers AS t
                        INNER JOIN users AS u ON u.id = t.user_id
                        WHERE t.correct=1
                        GROUP BY t.user_id
                        ORDER BY correct DESC, incorrect ASC
                        LIMIT 10';
            $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $i = 0;
            while ($result = mysqli_fetch_assoc($res)) {
                extract($result);
                $class = $i++ % 2 == 0 ? 'one' : 'two';
                $table .= "
                <tr class='$class'>
                    <td align='left' width='5%'>$username</td>
                    <td align='center' width='5%'>" . sprintf('%.2f%%', $correct / ($correct + $incorrect) * 100) . "</td>
                    <td align='center' width='5%'>$correct</td>
                    <td align='center' width='5%'>$incorrect</td>
                </tr>";
            }
            $table .= '
            </table>';
            if ($row2['correct'] == 1) {
                $HTMLOUT .= "
            {$lang['trivia_correct']}
        </div>
        <div style='margin-left: 30%; height: 225px;'>$table";
            } else {
                $HTMLOUT .= "
            {$lang['trivia_incorrect']}
        </div>
        <div style='margin-left: 30%; height: 225px;'>$table";
            }
        } else {
            $HTMLOUT .= "
        </div>
        <div style='margin-left: 30%; padding-left: 10px; padding-right: 10px;'>
            <h4>" . htmlspecialchars_decode($row['question']) . "</h4>
            <ul class='answers-container' style='list-style: none;>
                <li style='margin-bottom: 5px;'>
                    <form id='happy' method='post' action='trivia.php'>
                        <input id='qid' type='hidden' name='qid' value='" . $qid . "'>
                        <input id='user_id' type='hidden' name='user_id' value='" . $user_id . "'>
                        <input id='user_id' type='hidden' name='ans' value='answer1'>
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer1']) . "' class='btn flex'>
                    </form>
                </li>
                <li style='margin-bottom: 5px;'>
                    <form id='submit1' method='post' action='trivia.php'>
                        <input id='qid' type='hidden' name='qid' value='" . $qid . "'>
                        <input id='user_id' type='hidden' name='user_id' value='" . $user_id . "'>
                        <input id='user_id' type='hidden' name='ans' value='answer2'>
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer2']) . "' class='btn flex'>
                    </form>
                </li>";

            if ($row['answer3'] != null) {
                $HTMLOUT .= "
                <li style='margin-bottom: 5px;'>
                    <form id='submit2' method='post' action='trivia.php'>
                        <input id='qid' type='hidden' name='qid' value='" . $qid . "'>
                        <input id='user_id' type='hidden' name='user_id' value='" . $user_id . "'>
                        <input id='user_id' type='hidden' name='ans' value='answer3'>
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer3']) . "' class='btn flex'>
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
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer4']) . "' class='btn flex'>
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
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer5']) . "' class='btn1'>
                    </form>
                </li>";
            }

            $HTMLOUT .= '
            </ul>
        </div>';
        }
    }
}

$HTMLOUT .= '
    </div>
</body>';
if ($remaining >= 1) {
    $HTMLOUT .= "
<script>
    function getTimeRemaining(endtime){
        var t = Date.parse(endtime) - Date.parse(new Date());
        var seconds = Math.floor( (t/1000) % 60 );
        var minutes = Math.floor( (t/1000/60) % 60 );
        var hours = Math.floor( (t/(1000*60*60)) % 24 );
        var days = Math.floor( t/(1000*60*60*24) );
        return {
            'total': t,
            'days': days,
            'hours': hours,
            'minutes': minutes,
            'seconds': seconds
        };
    }

    function initializeClock(id, endtime){
        var clock = document.getElementById(id);
        function updateClock(){
            var t = getTimeRemaining(endtime);
            var minutesSpan = clock.querySelector('.minutes');
            var secondsSpan = clock.querySelector('.seconds');
            minutesSpan.innerHTML = t.minutes;
            secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
            if(t.total<=0){
                clearInterval(timeinterval);
            }
        }
        updateClock();
        var timeinterval = setInterval(updateClock,1000);
    }
    initializeClock('clockdiv', '$date_string');
</script>";
}
$HTMLOUT .= '
</html>';

echo $HTMLOUT;
