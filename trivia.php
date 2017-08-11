<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn(true);
loggedinorreturn();
$lang = array_merge(load_language('global'), load_language('trivia'));
parked();

$sql = 'SELECT qid FROM triviaq WHERE current = 1 AND asked = 1';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$qid = (int)$result['qid'];
$display = $answered = '';
global $INSTALLER09;
$csrf = $INSTALLER09['session_csrf'];

if (!empty($_POST) && (int)$_POST['qid'] === $qid) {
    if (!empty($_POST['qid']) && !empty($_POST['user_id']) && !empty($_POST['ans']) && !empty($_POST['gamenum'])) {

        $qid = (int)$_POST['qid'];
        $user_id = (int)$_POST['user_id'];
        $answer = $_POST['ans'];
        $gamenum = $_POST['gamenum'];
        $date = date('Y-m-d H:i:s');
        $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'No IP';

        file_put_contents('/var/log/nginx/csrf.log', $_POST['token'] . PHP_EOL, FILE_APPEND);
        file_put_contents('/var/log/nginx/csrf.log', $csrf . PHP_EOL, FILE_APPEND);
        if (empty($_POST['token']) || !validateToken($_POST['token'])) {
            $username = get_one_row('users', 'username', 'WHERE id = ' . sqlesc($user_id));
            write_log("Trivia Game => using curl post => [$user_id]$username qid:$qid $date $ip");
        } else {
            $rowcount = get_row_count('triviausers', 'WHERE user_id = ' . sqlesc($user_id) . ' AND qid = ' . sqlesc($qid));

            if ($rowcount === 0) {
                $sql = 'SELECT canswer FROM triviaq WHERE qid = ' . sqlesc($qid);
                $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $result = mysqli_fetch_assoc($res);
                $canswer = $result['canswer'];
                if ($user_id === 0) {
                    if (function_exists('write_log')) {
                        write_log("Some asshole is using a user_id of 0!!!!!!!!!!!!!!!! $user_id $qid $date $ip");
                    }
                } else {
                    $is_correct = $answer == $canswer ? 1 : 0;
                    $sql = 'INSERT INTO triviausers (user_id, gamenum, qid, correct, date) VALUES (' . sqlesc($user_id) . ', ' . sqlesc($gamenum). ', ' . sqlesc($qid) . ', ' . sqlesc($is_correct) . ', ' . sqlesc($date) . ')';
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);
                }
            }
        }
    }
}

unset($_POST);
global $INSTALLER09;
$HTMLOUT = '';
$user_id = $CURUSER['id'];

$sql = "SELECT clean_time - unix_timestamp(NOW()) AS round_remaining FROM cleanup WHERE clean_file = 'trivia_update.php'";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$round_remaining = (int)$result['round_remaining'];

$sql = "SELECT clean_time - unix_timestamp(NOW()) AS game_remaining FROM cleanup WHERE clean_file = 'trivia_points_update.php'";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$game_remaining = (int)$result['game_remaining'];

$num_totalq = get_row_count('triviaq');
$num_remainingq = get_row_count('triviaq', 'WHERE asked = 0');

$sql = 'SELECT gamenum FROM triviasettings WHERE gameon = 1 LIMIT 1';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$gamenum = (int)$result['gamenum'];

$refresh = 10;
if ($round_remaining >= 1) {
    $refresh = $round_remaining;
}

$HTMLOUT = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<title>Trivia</title>
<meta http-equiv='refresh' content={$refresh}; url=./trivia.php' />
<link rel='stylesheet' href='./templates/{$INSTALLER09['stylesheet']}/default.css' />
<link rel='stylesheet' href='./templates/{$INSTALLER09['stylesheet']}/bootstrap.css' />
<script src='./scripts/jquery-1.5.js'></script>
</head>
<body>";

$HTMLOUT .= "
    <div style='calc(width: 100% - 20px); margin: 10px; font-size: 1.25em;'>
        <div>";

if ($round_remaining >= 1) {
    $display = "
            {$lang['trivia_next_question']}
            <span id='clock_round'>
                <span style='display: none;' class='days'></span><span style='display: none;' class='hours'></span><span class='minutes'></span>:<span class='seconds'></span>
            </span><br>
            Game Ends in:
            <span id='clock_game'>
                <span class='days'></span> Days, <span class='hours'></span> Hours, <span class='minutes'></span> Minutes, <span class='seconds'></span> Seconds
            </span>";
}

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

        $sql = 'SELECT * FROM triviausers WHERE user_id = ' . sqlesc($user_id) . ' AND qid = ' . sqlesc($qid) . ' AND gamenum = ' . sqlesc($gamenum);
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
            $sql = 'SELECT t.user_id, COUNT(t.correct) AS correct, u.username,
                            (SELECT COUNT(correct) AS incorrect FROM triviausers WHERE gamenum = ' . sqlesc($gamenum) . ' AND correct = 0 AND user_id = t.user_id) AS incorrect
                        FROM triviausers AS t
                        INNER JOIN users AS u ON u.id = t.user_id
                        WHERE t.correct = 1 AND gamenum = ' . sqlesc($gamenum) . '
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
                    <td align='left' width='5%'>" . format_username((int)$user_id) . "</td>
                    <td align='center' width='5%'>" . sprintf('%.2f%%', $correct / ($correct + $incorrect) * 100) . "</td>
                    <td align='center' width='5%'>$correct</td>
                    <td align='center' width='5%'>$incorrect</td>
                </tr>";
            }
            $table .= "
            </table>";
            if ($row2['correct'] == 1) {
                $answered = $lang['trivia_correct'] . '<br>';
                $HTMLOUT .= "
        </div>
        <div>$table";
            } else {
                $answered = $lang['trivia_incorrect'] . '<br>';
            $HTMLOUT .= "
        </div>
        <div>$table";
            }
        } else {
            $HTMLOUT .= "
        </div>
        <div>
            <h4 class='text-center'>" . htmlspecialchars_decode($row['question']) . "</h4>
            <br>
            <ul class='answers-container' style='list-style: none;>
                <li style='margin-bottom: 5px;'>
                    <form id='happy' method='post' action='trivia.php'>
                        <input type='hidden' name='qid' value='{$qid}'>
                        <input type='hidden' name='user_id' value='{$user_id}'>
                        <input type='hidden' name='ans' value='answer1'>
                        <input type='hidden' name='gamenum' value='{$gamenum}'>
                        <input type='hidden' name='token' value='" . getSessionVar($csrf) . "'>
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer1']) . "' class='btnflex'>
                    </form>
                </li>
                <li style='margin-bottom: 5px;'>
                    <form id='submit1' method='post' action='trivia.php'>
                        <input type='hidden' name='qid' value='{$qid}'>
                        <input type='hidden' name='user_id' value='{$user_id}'>
                        <input type='hidden' name='ans' value='answer2'>
                        <input type='hidden' name='gamenum' value='{$gamenum}'>
                        <input type='hidden' name='token' value='" . getSessionVar($csrf) . "'>
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer2']) . "' class='btnflex'>
                    </form>
                </li>";

            if ($row['answer3'] != null) {
                $HTMLOUT .= "
                <li style='margin-bottom: 5px;'>
                    <form id='submit2' method='post' action='trivia.php'>
                        <input type='hidden' name='qid' value='{$qid}'>
                        <input type='hidden' name='user_id' value='{$user_id}'>
                        <input type='hidden' name='ans' value='answer3'>
                        <input type='hidden' name='gamenum' value='{$gamenum}'>
                        <input type='hidden' name='token' value='" . getSessionVar($csrf) . "'>
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer3']) . "' class='btnflex'>
                    </form>
                </li>";
            }
            if ($row['answer4'] != null) {
                $HTMLOUT .= "
                <li style='margin-bottom: 5px;'>
                    <form id='submit3' method='post' action='trivia.php'>
                        <input type='hidden' name='qid' value='{$qid}'>
                        <input type='hidden' name='user_id' value='{$user_id}'>
                        <input type='hidden' name='ans' value='answer4'>
                        <input type='hidden' name='gamenum' value='{$gamenum}'>
                        <input type='hidden' name='token' value='" . getSessionVar($csrf) . "'>
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer4']) . "' class='btnflex'>
                    </form>
                </li>";
            }
            if ($row['answer5'] != null) {
                $HTMLOUT .= "
                <li style='margin-bottom: 5px;'>
                    <form id='submit4' method='post' action='trivia.php'>
                        <input type='hidden' name='qid' value='{$qid}'>
                        <input type='hidden' name='user_id' value='{$user_id}'>
                        <input type='hidden' name='ans' value='answer5'>
                        <input type='hidden' name='gamenum' value='{$gamenum}'>
                        <input type='hidden' name='token' value='" . getSessionVar($csrf) . "'>
                        <input type='submit' value='" . htmlspecialchars_decode($row['answer5']) . "' class='btnflex'>
                    </form>
                </li>";
            }

            $HTMLOUT .= '
            </ul>
        </div>';
        }
    }
}

$HTMLOUT .= "
    </div>
    <br>
    <div class='text-center'>
        <a href='./trivia_results.php' target='_top' class='btn-clean'>Trivia Results</a>
    </div>
    <div class='text-center' style='margin-top: 20px'>
        $answered
        $display
    </div>
    <br>
</body>";

if ($round_remaining >= 1) {
    $HTMLOUT .= "
<script src='./scripts/iframeResizer.contentWindow.min.js'></script>
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

    function initializeClock(id, remaining) {
        console.log(remaining);
        var clock = document.getElementById(id);
        var ending = new Date();
        ending = new Date(ending.getTime() + 1000  * remaining);
        function updateClock() {
            var t = getTimeRemaining(ending);
            var daysSpan = clock.querySelector('.days');
            var hoursSpan = clock.querySelector('.hours');
            var minutesSpan = clock.querySelector('.minutes');
            var secondsSpan = clock.querySelector('.seconds');
            daysSpan.innerHTML = t.days;
            hoursSpan.innerHTML = t.hours;
            minutesSpan.innerHTML = t.minutes;
            secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
            if(t.total<=0) {
                clearInterval(timeinterval);
            }
        }
        updateClock();
        var timeinterval = setInterval(updateClock,1000);
    }

    initializeClock('clock_round', '$round_remaining');
    initializeClock('clock_game', '$game_remaining');
</script>";
}
$HTMLOUT .= '
</html>';

echo $HTMLOUT;
