<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('trivia'));

$sql = "SELECT gamenum, IFNULL(unix_timestamp(finished), 0) AS ended, IFNULL(unix_timestamp(started), 0) AS started FROM triviasettings GROUP BY gamenum ORDER BY gamenum DESC LIMIT 10";
//$sql = "SELECT gamenum, unix_timestamp(finished) AS ended, unix_timestamp(started) AS started FROM triviasettings GROUP BY gamenum ORDER BY gamenum DESC LIMIT 10";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$table = "
            <div class='container is-fluid portlet'>";
while ($result = mysqli_fetch_assoc($res)) {
    $gamenum = (int)$result['gamenum'];
    $ended = $result['ended'] >= 1 ? get_date($result['ended'], 'LONG') : 0;
    $started = $result['started'] >= 1 ? get_date($result['started'], 'LONG') : 0;
    $sql = 'SELECT t.gamenum, t.user_id, COUNT(t.correct) AS correct,
                (SELECT COUNT(correct) AS incorrect FROM triviausers WHERE correct = 0 AND user_id = t.user_id AND gamenum = ' . sqlesc($gamenum) . ') AS incorrect,
                u.username, u.modcomment
            FROM triviausers AS t
            INNER JOIN users AS u ON u.id = t.user_id
            INNER JOIN triviasettings AS s ON s.gamenum = t.gamenum
            WHERE t.correct = 1 AND t.gamenum = ' . sqlesc($gamenum) . '
            GROUP BY t.user_id
            ORDER BY correct DESC, incorrect ASC
            LIMIT 10';
    $query = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($query) > 0) {
        $i = 0;
        $date = $result['ended'] >= 1 ? "Ended: $ended" : "Started: $started";
        $table .= "
                <div class='bg-02 has-text-centered top20 round5'>
                    <div class='padtop20'>
                        <h3>Game #{$gamenum} $date</h3>
                    </div>
                    <table class='table table-bordered table-striped bottom20'>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Ratio</th>
                                <th>Correct</th>
                                <th>Incorrect</th>
                            </tr>
                        </thead>
                        <tbody>";

        while ($player = mysqli_fetch_assoc($query)) {
            extract($player);
            $table .= "
                        <tr>
                            <td>" . format_username((int)$user_id) . "</td>
                            <td>" . sprintf('%.2f%%', $correct / ($correct + $incorrect) * 100) . "</td>
                            <td>$correct</td>
                            <td>$incorrect</td>
                        </tr>";
        }
        $table .= "
                        </tbody>
                    </table>
                </div>";
    }
}
$table .= "
            </div>";
echo stdhead('Trivia') . $table . stdfoot();
