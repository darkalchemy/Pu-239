<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER;

$cache = new DarkAlchemy\Pu239\Cache();

$lang = array_merge(load_language('global'), load_language('contactstaff'));
$stdhead = [
    /* include css **/
    'css' => [
        'contact_staff',
    ],
];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg = isset($_POST['msg']) ? htmlsafechars($_POST['msg']) : '';
    $subject = isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : '';
    $returnto = isset($_POST['returnto']) ? htmlsafechars($_POST['returnto']) : $_SERVER['PHP_SELF'];
    if (empty($msg)) {
        stderr($lang['contactstaff_error'], $lang['contactstaff_no_msg']);
    }
    if (empty($subject)) {
        stderr($lang['contactstaff_error'], $lang['contactstaff_no_sub']);
    }
    if (sql_query('INSERT INTO staffmessages (sender, added, msg, subject) VALUES(' . sqlesc($CURUSER['id']) . ', ' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')')) {
        $cache->delete('staff_mess_');
        header('Refresh: 3; url=' . urldecode($returnto)); //redirect but wait 3 seconds
        stderr($lang['contactstaff_success'], $lang['contactstaff_success_msg']);
    } else {
        stderr($lang['contactstaff_error'], sprintf($lang['contactstaff_mysql_err'], ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
    }
} else {
    $HTMLOUT = "
    <!--<div ><img src='images/global.design/support.png' alt='' title='Support' class='global_image' width='25'/></div>
        <div >Contact Staff</div><br>-->
        <div ><br>
    <form method='post' name='message' action='" . $_SERVER['PHP_SELF'] . "'>
                 <table class='main' width='450' >
                  <tr><td colspan='2'>
                    <h1>{$lang['contactstaff_title']}</h1>
                    <p class='small'>{$lang['contactstaff_info']}</p>
                  </td></tr>
                  <tr><td>
                    {$lang['contactstaff_subject']}
                  </td><td>
                    <input type='text' size='50' name='subject' style='margin-left: 5px;' />
                  </td></tr>
        <tr><td colspan='2'>";
    if (isset($_GET['returnto'])) {
        $HTMLOUT .= "<input type='hidden' name='returnto' value='" . urlencode($_GET['returnto']) . "' />";
    }
    $HTMLOUT .= "<textarea name='msg' cols='80' rows='15'></textarea>
                       </td>
                     </tr>
                    <tr><td colspan='2'><input type='submit' value='{$lang['contactstaff_sendit']}' class='button is-small' /></td></tr>
                    </table>
        </form></div>";
    echo stdhead($lang['contactstaff_header'], true, $stdhead) . $HTMLOUT . stdfoot();
}
