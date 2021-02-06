<?php

declare(strict_types = 1);

use Pu239\IP;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $container;

$users_class = $container->get(User::class);
$ips_class = $container->get(IP::class);
$data = $ips_class->get_duplicates();
$heading = '
    <tr>
        <th>' . _('User') . '</th>
        <th>' . _('Email') . '</th>
        <th>' . _('Registered') . '</th>
        <th>' . _('Last access') . '</th>' . ($site_config['site']['ratio_free'] ? '' : '
        <th>' . _('Downloaded') . '</th>') . '
        <th>' . _('Uploaded') . '</th>
        <th>' . _('Ratio') . '</th>
        <th>' . _('IP') . '</th>
    </tr>';
$ip = '';
$uc = 0;
$body = '';
foreach ($data as $ras) {
    if ($ras['count'] <= 1) {
        break;
    }
    if ($ras['ip'] !== $ip) {
        $ros = $ips_class->getUsersFromIP($ras['ip']);
        if (count($ros) > 1) {
            ++$uc;
            foreach ($ros as $arr) {
                if ($arr['last_access'] == '0') {
                    $arr['last_access'] = '-';
                }
                $uploaded = mksize($arr['uploaded']);
                $downloaded = mksize($arr['downloaded']);
                $added = get_date((int) $arr['registered'], 'DATE', 1, 0);
                $last_access = get_date((int) $arr['last_access'], '', 1, 0);
                $body .= '
                <tr>
                    <td>' . format_username((int) $arr['id']) . '</td>
                    <td>' . format_comment($arr['email']) . "</td>
                    <td>$added</td>
                    <td>$last_access</td>" . ($site_config['site']['ratio_free'] ? '' : "
                    <td>$downloaded</td>") . "
                    <td>$uploaded</td>
                    <td>" . member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']) . '</td>
                    <td><span class="has-text-weight-bold">' . format_comment($arr['ip']) . '</span></td>
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
    $HTMLOUT .= stdmsg(_('Error'), _("There are no duplicate IP's in use."));
}
$title = _('IP Check');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
