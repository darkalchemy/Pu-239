<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
$lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($lconf)) {
    $lottery_config[$ac['name']] = $ac['value'];
}
if (!$lottery_config['enable']) {
    stderr(_('Error'), _('Lottery is closed'));
}
global $site_config;

$html .= '
    <div class="has-text-centered padding20">
        <h1>' . _fe('{0} Lottery', $site_config['site']['name']) . '</h1>
        <span class="size_4">
            Started: ' . get_date((int) $lottery_config['start_date'], 'LONG') . '<br>
            Ends: ' . get_date((int) $lottery_config['end_date'], 'LONG') . "<br>
            Time Remaining: <span class='has-text-danger'>" . mkprettytime($lottery_config['end_date'] - TIME_NOW) . '</span>
        </span>
    </div>';
$qs = sql_query('SELECT count(t.id) AS tickets , u.id, u.seedbonus FROM tickets AS t LEFT JOIN users AS u ON u.id = t.user GROUP BY u.id ORDER BY tickets DESC, username') or sqlerr(__FILE__, __LINE__);
$header = $body = $html = '';

if (!mysqli_num_rows($qs)) {
    $html .= '<div class="has-text-centered size_5 padding20">' . _('No tickets have been purchased!') . '</div>';
    $html = main_div($html);
} else {
    $header = '
    <tr>
      <th>' . _('Username') . '</th>
      <th>' . _('tickets') . '</th>
      <th>' . _('seedbonus') . '</th>
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
