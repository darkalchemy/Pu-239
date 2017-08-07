<?php
if (!defined('IN_INSTALLER09_ADMIN')) {
    setSessionVar('error', 'Access Not Allowed');
    header("Location: {$INSTALLER09['baseurl']}/index.php");
    die();
}
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_allagents'));
$HTMLOUT = '';
$res = sql_query('SELECT agent, peer_id FROM peers GROUP BY agent') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= "<table align='center' border='3' cellspacing='0' cellpadding='5'>
	<tr><td class='colhead'>{$lang['allagents_client']}</td><td class='colhead'>{$lang['allagents_peerid']}</td></tr>";
while ($arr = mysqli_fetch_assoc($res)) {
    $HTMLOUT .= "<tr><td align='left'>" . htmlsafechars($arr['agent']) . "</td><td align='left'>" . htmlsafechars($arr['peer_id']) . "</td></tr>\n";
}
$HTMLOUT .= "</table>\n";
echo stdhead($lang['allagents_allclients']) . $HTMLOUT . stdfoot();
