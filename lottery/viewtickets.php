<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'html_functions.php';
$lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($lconf)) {
    $lottery_config[$ac['name']] = $ac['value'];
}
if (!$lottery_config['enable']) {
    stderr('Sorry', 'Lottery is closed');
}
$html .= '
    <div class="margin20 has-text-centered">
        <h1>' . $site_config['site_name'] . ' Lottery</h1>
        <span class="size_4">
            Started: <b>' . get_date($lottery_config['start_date'], 'LONG') . '</b><br>
            Ends: <b>' . get_date($lottery_config['end_date'], 'LONG') . "</b><br>
            Time Remaining: <span class='has-text-danger'>" . mkprettytime($lottery_config['end_date'] - TIME_NOW) . '</span>
        </span>
    </div>';
$qs = sql_query('SELECT count(t.id) AS tickets , u.id, u.seedbonus FROM tickets AS t LEFT JOIN users AS u ON u.id = t.user GROUP BY u.id ORDER BY tickets DESC, username ASC') or sqlerr(__FILE__, __LINE__);
$header = $body = '';

if (!mysqli_num_rows($qs)) {
    $html .= '<h2 class="has-text-centered">No tickets have been purchased!</h2>';
    $html = main_div($html);
} else {
    $header = '
    <tr>
      <th>Username</th>
      <th>tickets</th>
      <th>seedbonus</th>
    </tr>';
    while ($ar = mysqli_fetch_assoc($qs)) {
        $body .= '
    <tr>
        <td>' . format_username($ar['id']) . '</a></td>
        <td>' . number_format($ar['tickets']) . '</td>
        <td>' . number_format($ar['seedbonus']) . '</td>
    </tr>';
    }
    $html .= main_table($body, $header);
}

echo stdhead('Lottery tickets') . wrapper($html) . stdfoot();
