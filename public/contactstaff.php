<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

$lang    = array_merge(load_language('global'), load_language('contactstaff'));
$stdhead = [
    'css' => [
        get_file_name('contactstaff_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('upload_js'),
    ],
];

$msg = '';
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $msg      = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
    $subject  = isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : '';
    $returnto = isset($_POST['returnto']) ? htmlsafechars($_POST['returnto']) : $_SERVER['PHP_SELF'];
    $fail     = false;
    if (empty($msg)) {
        $session->set('is-warning', $lang['contactstaff_no_msg']);
        $fail = true;
    }
    if (empty($subject)) {
        $session->set('is-warning', $lang['contactstaff_no_sub']);
        $fail = true;
    }

    if (!$fail) {
        $sql = 'INSERT INTO staffmessages (sender, added, msg, subject) VALUES(' . sqlesc($CURUSER['id']) . ', ' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
        if (sql_query($sql)) {
            $cache->delete('staff_mess_');
            header('Refresh: 3; url=' . urldecode($returnto)); //redirect but wait 3 seconds
            $session->set('is-success', $lang['contactstaff_success_msg']);
        } else {
            $session->set('is-warning', sprintf($lang['contactstaff_mysql_err'], ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
        }
    }
} else {
    $HTMLOUT = "
            <form method='post' name='message' action='" . $_SERVER['PHP_SELF'] . "'>";
    $header = "
                    <tr>
                        <th colspan='2'>
                            <div class='has-text-centered'>
                                <h1>{$lang['contactstaff_title']}</h1>
                                <p class='small'>{$lang['contactstaff_info']}</p>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th class='w-10'>
                            {$lang['contactstaff_subject']}
                        </th>
                        <th>
                            <input type='text' name='subject' class='w-100'/>
                        </th>
                    </tr>";

    $body = "
                    <tr>
                        <td colspan='2'>" .
        BBcode($msg) . "
                       </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' value='{$lang['contactstaff_sendit']}' class='button is-small' />
                            </div>
                        </td>
                    </tr>";
    if (isset($_GET['returnto'])) {
        $body .= "
                    <input type='hidden' name='returnto' value='" . urlencode($_GET['returnto']) . "' />";
    }

    $HTMLOUT .= main_table($body, $header);

    $HTMLOUT .= '
            </form>';

    echo stdhead($lang['contactstaff_header'], true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
}
