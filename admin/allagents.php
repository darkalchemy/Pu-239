<?php

require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

$lang    = array_merge($lang, load_language('ad_allagents'));
$res     = sql_query('SELECT agent, HEX(peer_id) AS peer_id FROM peers GROUP BY agent') or sqlerr(__FILE__, __LINE__);
$heading =  "
        <tr>
            <th>{$lang['allagents_client']}</th>
            <th>{$lang['allagents_peerid']}</th>
        </tr>";
$body = '';
while ($arr = mysqli_fetch_assoc($res)) {
    $body .= '
        <tr>
            <td>' . htmlsafechars($arr['agent']) . '</td>
            <td>' . htmlsafechars($arr['peer_id']) . '</td>
        </tr>';
}
$HTMLOUT = main_table($body, $heading);
echo stdhead($lang['allagents_allclients']) . wrapper($HTMLOUT) . stdfoot();
