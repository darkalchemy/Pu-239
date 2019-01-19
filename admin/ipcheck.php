<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $user_stuffs;

$lang = array_merge($lang, load_language('ad_ipcheck'));
$res = sql_query("SELECT count(*) AS dupl, INET6_NTOA(ip) AS ip FROM users WHERE enabled = 'yes' AND ip != '' AND INET6_NTOA(ip) NOT IN ('127.0.0.1', '10.0.0.1', '10.10.10.10') GROUP BY users.ip ORDER BY dupl DESC, ip") or sqlerr(__FILE__, __LINE__);

$heading = "
    <tr>
        <th>{$lang['ipcheck_user']}</th>
        <th>{$lang['ipcheck_email']}</th>
        <th>{$lang['ipcheck_regged']}</th>
        <th>{$lang['ipcheck_lastacc']}</th>" . ($site_config['ratio_free'] ? '' : "
        <th>{$lang['ipcheck_dload']}</th>") . "
        <th>{$lang['ipcheck_upped']}</th>
        <th>{$lang['ipcheck_ratio']}</th>
        <th>{$lang['ipcheck_ip']}</th>
    </tr>";
$ip = '';
$uc = 0;
$body = '';
while ($ras = mysqli_fetch_assoc($res)) {
    if ($ras['dupl'] <= 1) {
        break;
    }
    if ($ras['ip'] != $ip) {
        $ros = $user_stuffs->getUsersFromIP($ras['ip']);
        if (count($ros) > 1) {
            ++$uc;
            foreach ($ros as $arr) {
                if ($arr['added'] == '0') {
                    $arr['added'] = '-';
                }
                if ($arr['last_access'] == '0') {
                    $arr['last_access'] = '-';
                }
                $uploaded = mksize($arr['uploaded']);
                $downloaded = mksize($arr['downloaded']);
                $added = get_date($arr['added'], 'DATE', 1, 0);
                $last_access = get_date($arr['last_access'], '', 1, 0);
                $body .= '
                <tr>
                    <td>' . format_username($arr['id']) . '</td>
                    <td>' . htmlsafechars($arr['email']) . "</td>
                    <td>$added</td>
                    <td>$last_access</td>" . ($site_config['ratio_free'] ? '' : "
                    <td>$downloaded</td>") . "
                    <td>$uploaded</td>
                    <td>" . member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']) . '</td>
                    <td><span style="font-weight: bold;">' . htmlsafechars($arr['ip']) . '</span></td>
                </tr>';
                $ip = htmlsafechars($arr['ip']);
            }
        }
    }
}

$HTMLOUT = '<h1 class="has-text-centered">Duplicate IP Check</h1>';
if (!empty($body)) {
    $HTMLOUT .= main_table($body, $heading);
} else {
    $HTMLOUT .= stdmsg($lang['ipcheck_sorry'], $lang['ipcheck_no_dupes']);
}
echo stdhead($lang['ipcheck_stdhead']) . wrapper($HTMLOUT) . stdfoot();
