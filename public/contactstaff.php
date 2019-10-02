<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
$user = check_user_status();
global $container;

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('upload_js'),
        get_file_name('sceditor_js'),
    ],
];

$msg = '';
$session = $container->get(Session::class);
$cache = $container->get(Cache::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
    $subject = isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : '';
    $returnto = isset($_POST['returnto']) ? htmlsafechars($_POST['returnto']) : $_SERVER['PHP_SELF'];
    $fail = false;
    if (empty($msg)) {
        $session->set('is-warning', _("Your messages doesn't have a body"));
        $fail = true;
    }
    if (empty($subject)) {
        $session->set('is-warning', _("Your messages doesn't have a subject"));
        $fail = true;
    }

    if (!$fail) {
        $sql = 'INSERT INTO staffmessages (sender, added, msg, subject) VALUES(' . sqlesc($user['id']) . ', ' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
        if (sql_query($sql)) {
            $cache->delete('staff_mess_');
            $session->set('is-success', _('Message was sent! Wait for staff to respond now!'));
            header('Location: ' . $site_config['paths']['baseurl']);
        } else {
            $session->set('is-warning', _f('There was something wrong, Mysql Err: %s') . ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        }
    }
} else {
    $HTMLOUT = "
            <form method='post' name='message' action='" . $_SERVER['PHP_SELF'] . "' enctype='multipart/form-data' accept-charset='utf-8'>";
    $header = "
                    <tr>
                        <th colspan='2'>
                            <div class='has-text-centered'>
                                <h1>" . _('Send message to staff') . "</h1>
                                <p class='small'>" . _('If you wish to contact the staff due to a certain user or just a general problem please use this!') . "</p>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th class='w-10'>
                            " . _('Subject') . "
                        </th>
                        <th>
                            <input type='text' name='subject' class='w-100'/>
                        </th>
                    </tr>";

    $body = "
                    <tr>
                        <td colspan='2' class='is-paddingless'>" . BBcode($msg) . "
                       </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' value='" . _('Send It!') . "' class='button is-small'>
                            </div>
                        </td>
                    </tr>";
    if (isset($_GET['returnto'])) {
        $body .= "
                    <input type='hidden' name='returnto' value='" . urlencode($_GET['returnto']) . "'>";
    }

    $HTMLOUT .= main_table($body, $header);

    $HTMLOUT .= '
            </form>';
    $title = _('Contact Staff');
    $breadcrumbs = [
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
}
