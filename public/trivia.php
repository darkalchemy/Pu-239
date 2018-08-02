<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config, $session;

$lang = array_merge(load_language('global'), load_language('trivia'));

$sql = 'SELECT qid FROM triviaq WHERE current = 1 AND asked = 1';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$qid = (int) $result['qid'];
$display = $answered = '';
$csrf = $site_config['session_csrf'];

/**
 * @param $data
 *
 * @return mixed
 */
function clean_data($data)
{
    foreach ($data as $key => $value) {
        $data[$key] = html_entity_decode(replace_unicode_strings(trim($value)));
    }

    return $data;
}

if (!empty($_POST) && (int) $_POST['qid'] === $qid) {
    if (!empty($_POST['qid']) && !empty($_POST['user_id']) && !empty($_POST['ans']) && !empty($_POST['gamenum'])) {
        $qid = (int) $_POST['qid'];
        $user_id = (int) $_POST['user_id'];
        $answer = $_POST['ans'];
        $gamenum = $_POST['gamenum'];
        $date = date('Y-m-d H:i:s');
        $ip = getip();

        if (empty($_POST['token']) || !$session->validateToken($_POST['token'])) {
            $username = get_one_row('users', 'username', 'WHERE id = ' . sqlesc($user_id));
            write_log("Trivia Game => using curl post => [$user_id]$username qid:$qid $date $ip");
        } else {
            $rowcount = get_row_count('triviausers', 'WHERE user_id = ' . sqlesc($user_id) . ' AND qid = ' . sqlesc($qid) . ' AND gamenum = ' . sqlesc($gamenum));

            if ($rowcount === 0) {
                $sql = 'SELECT canswer FROM triviaq WHERE qid = ' . sqlesc($qid);
                $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $result = mysqli_fetch_assoc($res);
                $canswer = $result['canswer'];
                if ($user_id === 0) {
                    if (function_exists('write_log')) {
                        write_log("Some asshole is using a user_id of 0! $user_id $qid $date $ip");
                    }
                } else {
                    $is_correct = $answer == $canswer ? 1 : 0;
                    $sql = 'INSERT INTO triviausers (user_id, gamenum, qid, correct, date) VALUES (' . sqlesc($user_id) . ', ' . sqlesc($gamenum) . ', ' . sqlesc($qid) . ', ' . sqlesc($is_correct) . ', ' . sqlesc($date) . ')';
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);
                }
            }
        }
    }
}

unset($_POST);
global $site_config;
$HTMLOUT = '';
$user_id = $CURUSER['id'];

$sql = "SELECT clean_time - unix_timestamp(NOW()) AS round_remaining FROM cleanup WHERE clean_file = 'trivia_update.php'";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$round_remaining = (int) $result['round_remaining'];

$sql = "SELECT clean_time - unix_timestamp(NOW()) AS game_remaining FROM cleanup WHERE clean_file = 'trivia_points_update.php'";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$game_remaining = (int) $result['game_remaining'];

$num_totalq = get_row_count('triviaq');
$num_remainingq = get_row_count('triviaq', 'WHERE asked = 0');

$sql = 'SELECT gamenum FROM triviasettings WHERE gameon = 1 LIMIT 1';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);
$gamenum = (int) $result['gamenum'];

$time_refresh = 600;
if (!empty($gamenum) && !empty($qid)) {
    $time_refresh = 10;
    if ($round_remaining >= 1) {
        $time_refresh = $round_remaining;
    }
}
$refresh = "<meta http-equiv='refresh' content={$time_refresh}; url={$site_config['baseurl']}/trivia.php'>";

$HTMLOUT = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    $refresh
    <title>Trivia</title>
    <link rel='stylesheet' href='" . get_file_name('css') . "' />
</head>
<body class='ajax-chat text-9'>
<script>
    var theme = localStorage.getItem('theme');
    if (theme) {
        var textMatch = theme.match(/text-\d+/);
        var styleMatch = theme.match(/h-style-\d+/);
        document.body.className = 'ajax-chat ' + textMatch[0] + ' ' + styleMatch[0];
    }
</script>";

$HTMLOUT .= "
    <div class='bg-02 round10'>
        <div>";

if (!empty($gamenum) && !empty($qid)) {
    $display = "
            <h3 class='has-text-info'>
                <div id='clock_round'>
                    <span>{$lang['trivia_next_question']}: </span><span class='days'></span><span class='hours'></span><span class='minutes'></span>:<span class='seconds'></span>
                </div>
                <div id='clock_game'>
                    <span>Game Ends in: </span><span class='days'></span> <span class='hours'></span> Hours, <span class='minutes'></span> Minutes, <span class='seconds'></span> Seconds
                </div>
            </h3>";
}

if (empty($gamenum) || empty($qid)) {
    $HTMLOUT .= "
            <div class='has-text-centered padding20'>
                {$lang['trivia_game_stopped']}
            </div>";
} else {
    if ($num_remainingq === 0) {
        $HTMLOUT .= "
            {$lang['trivia_no_more']}<br><br>{$lang['trivia_wait']}";
    } else {
        $sql = 'SELECT question, answer1, answer2, answer3, answer4, answer5, asked FROM triviaq WHERE qid = ' . sqlesc($qid);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_assoc($res);
        $row = clean_data($row);

        $sql = 'SELECT * FROM triviausers WHERE user_id = ' . sqlesc($user_id) . ' AND qid = ' . sqlesc($qid) . ' AND gamenum = ' . sqlesc($gamenum);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $row2 = mysqli_fetch_assoc($res);
        $num_rows = !empty($row2) ? count($row2) : 0;

        if ($num_rows != 0) {
            $sql = 'SELECT t.user_id, COUNT(t.correct) AS correct, u.username,
                            (SELECT COUNT(correct) AS incorrect FROM triviausers WHERE gamenum = ' . sqlesc($gamenum) . ' AND correct = 0 AND user_id = t.user_id) AS incorrect
                        FROM triviausers AS t
                        INNER JOIN users AS u ON u.id = t.user_id
                        WHERE t.correct = 1 AND gamenum = ' . sqlesc($gamenum) . '
                        GROUP BY t.user_id
                        ORDER BY correct DESC, incorrect ASC
                        LIMIT 10';
            $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $table = '';
            if (mysqli_num_rows($res) > 0) {
                $heading = "
                        <tr>
                            <th class='has-text-left' width='5%'>Username</th>
                            <th class='has-text-centered' width='5%'>Ratio</th>
                            <th class='has-text-centered' width='5%'>Correct</th>
                            <th class='has-text-centered' width='5%'>Incorrect</th>
                        </tr>";
                $body = '';
                while ($result = mysqli_fetch_assoc($res)) {
                    extract($result);
                    $body .= "
                        <tr>
                            <td width='5%'><div class='is-pulled-left'>" . format_username($user_id) . "</div></td>
                            <td class='has-text-centered' width='5%'>" . sprintf('%.2f%%', $correct / ($correct + $incorrect) * 100) . "</td>
                            <td class='has-text-centered' width='5%'>$correct</td>
                            <td class='has-text-centered' width='5%'>$incorrect</td>
                        </tr>";
                }
                $table = main_table($body, $heading);
            }
            if ($row2['correct'] == 1) {
                $answered = "<h2 class='has-text-success'>{$lang['trivia_correct']}</h2>";
                $HTMLOUT .= $table;
            } else {
                $answered = "<h2 class='has-text-danger'>{$lang['trivia_incorrect']}</h2>";
                $HTMLOUT .= $table;
            }
        } else {
            $HTMLOUT .= "
            <div class='padding20'>
                <h1 class='has-text-centered has-text-primary'>" . htmlspecialchars_decode($row['question']) . "</h1>
                <div class='is-centered is-small'>
                    <ul class='level-center has-text-centered'>
                        <li>
                            <form id='happy' method='post' action='trivia.php'>
                                <input type='hidden' name='qid' value='{$qid}'>
                                <input type='hidden' name='user_id' value='{$user_id}'>
                                <input type='hidden' name='ans' value='answer1'>
                                <input type='hidden' name='gamenum' value='{$gamenum}'>
                                <input type='hidden' name='token' value='" . $session->get($csrf) . "'>
                                <input type='submit' value='" . htmlspecialchars_decode($row['answer1']) . "' class='button margin20'>
                            </form>
                        </li>
                        <li>
                            <form id='submit1' method='post' action='trivia.php'>
                                <input type='hidden' name='qid' value='{$qid}'>
                                <input type='hidden' name='user_id' value='{$user_id}'>
                                <input type='hidden' name='ans' value='answer2'>
                                <input type='hidden' name='gamenum' value='{$gamenum}'>
                                <input type='hidden' name='token' value='" . $session->get($csrf) . "'>
                                <input type='submit' value='" . htmlspecialchars_decode($row['answer2']) . "' class='button margin20'>
                            </form>
                        </li>";

            if ($row['answer3'] != null) {
                $HTMLOUT .= "
                        <li>
                            <form id='submit2' method='post' action='trivia.php'>
                                <input type='hidden' name='qid' value='{$qid}'>
                                <input type='hidden' name='user_id' value='{$user_id}'>
                                <input type='hidden' name='ans' value='answer3'>
                                <input type='hidden' name='gamenum' value='{$gamenum}'>
                                <input type='hidden' name='token' value='" . $session->get($csrf) . "'>
                                <input type='submit' value='" . htmlspecialchars_decode($row['answer3']) . "' class='button margin20'>
                            </form>
                        </li>";
            }
            if ($row['answer4'] != null) {
                $HTMLOUT .= "
                        <li>
                            <form id='submit3' method='post' action='trivia.php'>
                                <input type='hidden' name='qid' value='{$qid}'>
                                <input type='hidden' name='user_id' value='{$user_id}'>
                                <input type='hidden' name='ans' value='answer4'>
                                <input type='hidden' name='gamenum' value='{$gamenum}'>
                                <input type='hidden' name='token' value='" . $session->get($csrf) . "'>
                                <input type='submit' value='" . htmlspecialchars_decode($row['answer4']) . "' class='button margin20'>
                            </form>
                        </li>";
            }
            if ($row['answer5'] != null) {
                $HTMLOUT .= "
                        <li>
                            <form id='submit4' method='post' action='trivia.php'>
                                <input type='hidden' name='qid' value='{$qid}'>
                                <input type='hidden' name='user_id' value='{$user_id}'>
                                <input type='hidden' name='ans' value='answer5'>
                                <input type='hidden' name='gamenum' value='{$gamenum}'>
                                <input type='hidden' name='token' value='" . $session->get($csrf) . "'>
                                <input type='submit' value='" . htmlspecialchars_decode($row['answer5']) . "' class='button margin20'>
                            </form>
                        </li>";
            }

            $HTMLOUT .= '
                    </ul>
                </div>
            </div>';
        }
    }
}

$HTMLOUT .= "
            <div class='has-text-centered'>
                $answered
                $display
                <a href='{$site_config['baseurl']}/trivia_results.php' target='_top' class='button is-small margin20'>Trivia Results</a>
            </div>
        </div>
    </div>
</body>";

if (!empty($gamenum) && !empty($qid)) {
    $HTMLOUT .= "
<script src='" . get_file_name('js') . "'></script>
<script>
    <!-- https://www.sitepoint.com/build-javascript-countdown-timer-no-dependencies/ -->
    function getTimeRemaining(endtime){
        var t = String(Date.parse(endtime) - Date.parse(String(new Date())));
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
        var clock = document.getElementById(id);
        var ending = new Date();
        ending = new Date(ending.getTime() + 1000  * remaining);
        function updateClock() {
            var t = getTimeRemaining(ending);
            var daysSpan = clock.querySelector('.days');
            var hoursSpan = clock.querySelector('.hours');
            var minutesSpan = clock.querySelector('.minutes');
            var secondsSpan = clock.querySelector('.seconds');
            if (t.days > 0) {
                daysSpan.innerHTML = t.days + ' Days, ';
            }
            if (t.hours > 0) {
                hoursSpan.innerHTML = String(t.hours);
            }
            minutesSpan.innerHTML = String(t.minutes);
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
