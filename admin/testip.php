<?php
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_testip'));
$HTMLOUT = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ip = isset($_POST['ip']) ? $_POST['ip'] : false;
} else {
    $ip = isset($_GET['ip']) ? $_GET['ip'] : false;
}
if ($ip) {
    $nip = ipToStorageFormat($ip);
    if ($nip == -1) {
        stderr($lang['testip_error'], $lang['testip_error1']);
    }
    $res = sql_query("SELECT * FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        stderr($lang['testip_result'], sprintf($lang['testip_notice'], htmlentities($ip, ENT_QUOTES)));
    } else {
        $HTMLOUT .= "<table class='main' border='0' cellspacing='0' cellpadding='5'>
        <tr>
          <td class='colhead'>{$lang['testip_first']}</td>
          <td class='colhead'>{$lang['testip_last']}</td>
          <td class='colhead'>{$lang['testip_comment']}</td>
        </tr>\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $first = ipFromStorageFormat($arr['first']);
            $last = ipfromStorageFormat($arr['last']);
            $comment = htmlsafechars($arr['comment']);
            $HTMLOUT .= "<tr><td>$first</td><td>$last</td><td>$comment</td></tr>\n";
        }
        $HTMLOUT .= "</table>\n";
        stderr($lang['testip_result'], "<table border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded' style='padding-right: 5px'><img src='{$site_config['pic_base_url']}smilies/excl.gif' alt='' /></td><td class='embedded'>" . sprintf($lang['testip_notice2'], $ip) . "</td></tr></table><p>$HTMLOUT</p>");
    }
}
$HTMLOUT .= "
    <h1>{$lang['testip_title']}</h1>
    <form method='post' action='staffpanel.php?tool=testip&amp;action=testip'>
    <table border='1' cellspacing='0' cellpadding='5'>
    <tr><td class='rowhead'>{$lang['testip_address']}</td><td><input type='text' name='ip' /></td></tr>
    <tr><td colspan='2'><input type='submit' class='button' value='{$lang['testip_ok']}' /></td></tr>
    </table>
    </form>";
echo stdhead($lang['testip_windows_title']) . $HTMLOUT . stdfoot();
