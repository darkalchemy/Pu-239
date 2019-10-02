<?php

declare(strict_types = 1);

use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config;

$file = $site_config['paths']['flood_file'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $limits = isset($_POST['limit']) && is_array($_POST['limit']) ? $_POST['limit'] : [];
    foreach ($limits as $class => $limit) {
        if ((int) $limit === 0) {
            unset($limits[$class]);
        }
    }
    $session = $container->get(Session::class);
    if (file_put_contents($file, json_encode($limits))) {
        $session->set('is-success', _('Flood Limits saved!'));
    } else {
        $session->set('is-error', '' . _('Something went wrong make sure ') . " $file " . _('exists and it is chmoded 0774') . '');
    }
}

if (!file_exists($file) || !is_array($limit = json_decode(file_get_contents($file)))) {
    $limit = [];
}
$out = "
        <form method='post' action='' enctype='multipart/form-data' accept-charset='utf-8'>";
$heading = '
        <tr>
            <th>' . _('User class') . '</th>
            <th>' . _('Limit') . '</th>
        </tr>';
$body = '';
for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
    $body .= '
        <tr>
            <td>' . get_user_class_name((int) $i) . "</td>
            <td><input name='limit[$i]' type='text' class='w-100' value='" . (isset($limit[$i]) ? $limit[$i] : 0) . "'></td>
        </tr>";
}
$out .= main_table($body, $heading) . "
        <div class='has-text-centered'>
            <p class='padding10'>" . _('Note: if you want no limit for the user class set the limit to 0') . "</p>
            <input type='submit' value='" . _('Save') . "' class='button is-small margin20'>
        </div>
        </form>";
$title = _('Flood Limit');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($out) . stdfoot();
