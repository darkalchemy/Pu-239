<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_allagents'));
global $container;

$fluent = $container->get(Database::class);
$agents = $fluent->from('peers')
                 ->select(null)
                 ->select('agent')
                 ->select('LEFT(peer_id, 8) AS peer_id')
                 ->groupBy('agent')
                 ->groupBy('peer_id')
                 ->fetchAll();

if (!empty($agents)) {
    $heading = "
        <tr>
            <th>{$lang['allagents_client']}</th>
            <th>{$lang['allagents_peerid']}</th>
        </tr>";
    $body = '';
    foreach ($agents as $arr) {
        $body .= '
        <tr>
            <td>' . format_comment($arr['agent']) . '</td>
            <td>' . format_comment($arr['peer_id']) . '</td>
        </tr>';
    }
    $HTMLOUT = main_table($body, $heading);
} else {
    $HTMLOUT = stdmsg($lang['allagents_sorry'], $lang['allagents_empty']);
}
echo stdhead($lang['allagents_allclients']) . wrapper($HTMLOUT) . stdfoot();
