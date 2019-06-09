<?php

declare(strict_types = 1);

use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_floodlimit'));
global $container, $site_config;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $limits = isset($_POST['limit']) && is_array($_POST['limit']) ? $_POST['limit'] : [];
    foreach ($limits as $class => $limit) {
        if ((int) $limit === 0) {
            unset($limits[$class]);
        }
    }
    $session = $container->get(Session::class);
    if (file_put_contents($site_config['paths']['flood_file'], json_encode($limits))) {
        $session->set('is-success', $lang['floodlimit_saved']);
    } else {
        $session->set('is-error', $lang['floodlimit_wentwrong'] . $site_config['paths']['flood_file'] . $lang['floodlimit_exist']);
    }
}


    if (!file_exists($site_config['paths']['flood_file']) || !is_array($limit = json_decode(file_get_contents($site_config['paths']['flood_file'])))) {
        $limit = [];
    }
    $out = "
        <form method='post' action='' accept-charset='utf-8'>";
    $heading = "
        <tr>
            <th>{$lang['floodlimit_userclass']}</th>
            <th>{$lang['floodlimit_limit']}</th>
        </tr>";
    $body = '';
    for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
        $body .= '
        <tr>
            <td>' . get_user_class_name($i) . "</td>
            <td><input name='limit[$i]' type='text' class='w-100' value='" . (isset($limit[$i]) ? $limit[$i] : 0) . "'></td>
        </tr>";
    }
    $out .= main_table($body, $heading) . "
        <div class='has-text-centered'>
            <p class='padding10'>{$lang['floodlimit_note']}</p>
            <input type='submit' value='{$lang['floodlimit_save']}' class='button is-small margin20'>
        </div>
        </form>";

    echo stdhead($lang['floodlimit_std']) . wrapper($out) . stdfoot();
