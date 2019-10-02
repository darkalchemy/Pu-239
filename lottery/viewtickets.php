<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
$lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($lconf)) {
    $lottery_config[$ac['name']] = $ac['value'];
}
if (!$lottery_config['enable']) {
    stderr('Sorry', 'Lottery is closed');
}
global $site_config;

$html .= '
    <div class="margin20 has-text-centered">
        <h1>' . $site_config['site']['name'] . ' Lottery</h1>
        <span class="size_4">
            Started: <b>' . get_date((int) $lottery_config['start_date'], 'LONG') . '</b><br>
            Ends: <b>' . get_date((int) $lottery_config['end_date'], 'LONG') . "</b><br>
            Time Remaining: <span class='has-text-danger'>" . mkprettytime($lottery_config['end_date'] - TIME_NOW) . '</span>
        </span>
    </div>';
$qs = sql_query('SELECT count(t.id) AS tickets , u.id, u.seedbonus FROM tickets AS t LEFT JOIN users AS u ON u.id=t.user GROUP BY u.id ORDER BY tickets DESC, username') or sqlerr(__FILE__, __LINE__);
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
        <td>' . format_username((int) $ar['id']) . '</a></td>
        <td>' . number_format((int) $ar['tickets']) . '</td>
        <td>' . number_format((float) $ar['seedbonus']) . '</td>
    </tr>';
    }
    $html .= main_table($body, $header);
}

$title = _('Lottery Tickets');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/games.php'>" . _('Games') . '</a>',
    "<a href='{$site_config['paths']['baseurl']}/lottery.php'>" . _('Lottery') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($html) . stdfoot();
