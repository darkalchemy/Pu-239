<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Roles;
use Rakit\Validation\Validator;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
$user = check_user_status();
global $container, $site_config;

$HTMLOUT = '';

$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
$messages_class = $container->get(Message::class);
$auth = $container->get(Auth::class);
if ($auth->hasRole(Roles::UPLOADER)) {
    stderr(_('Access Denied'), _('It appears you are already part of our uploading team.'));
}
function check_status(Database $fluent, int $userid)
{
    $applicant = $fluent->from('uploadapp')
        ->where('userid = ?', $userid)
        ->fetch();
    if (!empty($applicant)) {
        if ($applicant['status'] === 'pending') {
            stderr(
                _('Access Denied'),
                _('It appears you are currently pending confirmation of your uploader application.')
            );
        } elseif ($applicant['status'] === 'rejected') {
            stderr(
                _('Access Denied'),
                _(
                    'It appears you have applied for uploader before and have been rejected. If you would like a second chance please contact an administrator.'
                )
            );
        }
    }
}
check_status($fluent, $user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_id((int) $_POST['userid'])) {
        stderr(_('Error'), _fe('It appears something went wrong while sending your application. Please {0}try again{1}', "<a href='{$site_config['paths']['baseurl']}/uploadapp.php'>", '</a>'));
    }
    if (!$_POST['speed']) {
        stderr(_('Error'), _('It appears you have left the field with your upload speed blank.'));
    }
    if (!$_POST['offer']) {
        stderr(_('Error'), _('It appears you have left the field with the things you have to offer blank.'));
    }
    if (!$_POST['reason']) {
        stderr(_('Error'), _('It appears you have left the field with the reason why we should promote you blank.'));
    }
    if ($_POST['sites'] === 'yes' && empty($_POST['sitenames'])) {
        stderr(_('Error'), _('It appears you have left the field with the sites you are uploader at blank.'));
    }
    check_status($fluent, $_POST['userid']);
    $validator = $container->get(Validator::class);
    $validation = $validator->validate($_POST, [
        'userid' => 'required|integer',
        'connectable' => 'required|in:Pending,Yes,Yo',
        'speed' => 'required',
        'offer' => 'required',
        'reason' => 'required',
        'sites' => 'required|in:yes,no',
        'sitenames' => 'required',
        'scene' => 'required|in:yes,no',
        'creating' => 'required|in:yes,no',
        'seeding' => 'required|in:yes,no',
    ]);
    if ($validation->fails()) {
        stderr(_('Error'), 'Invalid data supplied');
    }
    $values = [
        'userid' => (int) $_POST['userid'],
        'applied' => TIME_NOW,
        'connectable' => htmlsafechars($_POST['connectable']),
        'speed' => htmlsafechars($_POST['speed']),
        'offer' => htmlsafechars($_POST['offer']),
        'reason' => htmlsafechars($_POST['reason']),
        'sites' => htmlsafechars($_POST['sites']),
        'sitenames' => htmlsafechars($_POST['sitenames']),
        'scene' => htmlsafechars($_POST['scene']),
        'moderator' => '',
        'creating' => htmlsafechars($_POST['creating']),
        'seeding' => htmlsafechars($_POST['seeding']),
    ];
    $res = $fluent->insertInto('uploadapp')
        ->values($values)
        ->execute();
    $cache->delete('new_uploadapp_');
    if (!$res) {
        stderr(_('Error'), _fe('It appears something went wrong while sending your application. Please {0}try again{1}', "<a href='{$site_config['paths']['baseurl']}/uploadapp.php'>", '</a>'));
    } else {
        $subject = 'Uploader application';
        $msg = "An uploader application has just been filled in by [url={$site_config['paths']['baseurl']}/userdetails.php?id=" . (int) $user['id'] . "][b]{$user['username']}[/b][/url]. Click [url={$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&action=app][b]Here[/b][/url] to go to the uploader applications page.";
        $dt = TIME_NOW;
        $subres = $fluent->from('users')
            ->select(null)
            ->select('id')
            ->where('class >= ?', UC_STAFF)
            ->fetchAll();

        foreach ($subres as $arr) {
            $msgs_buffer[] = [
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        }
        if (!empty($msgs_buffer)) {
            $messages_class->insert($msgs_buffer);
        }
        stderr(_('Application sent'), _('Your application has successfully been sent to the staff.'));
    }
}

$ratio = member_ratio($user['uploaded'], $user['downloaded']);
$connect = $fluent->from('peers')
    ->select(null)
    ->select('connectable')
    ->where('userid = ?', $user['id'])
    ->fetch();
if (!empty($connect)) {
    $Conn_Y = 'yes';
    if ($connect == $Conn_Y) {
        $connectable = 'Yes';
    } else {
        $connectable = 'No';
    }
} else {
    $connectable = 'Pending';
}

$HTMLOUT .= '
        <h1 class="has-text-centered">' . _('Uploader application') . "</h1>
        <form action='./uploadapp.php' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <div class='table-wrapper'>
                <table class='table table-bordered table-striped'>
                    <tr>
                        <td class='rowhead'>" . _('My username is') . "</td>
                        <td>
                            <input name='userid' type='hidden' value='" . (int) $user['id'] . "'>
                            {$user['username']}
                         </td>
                    </tr>
                    <tr>
                        <td class='rowhead'>" . _('I joined') . '</td>
                        <td>' . get_date((int) $user['registered'], '', 0, 1) . "</td>
                    </tr>
                    <tr>
                        <td class='rowhead'>" . _('I have a positive ratio') . '</td>
                        <td>' . ($ratio >= 1 ? 'No' : 'Yes') . "</td>
                    </tr>
                    <tr>
                        <td class='rowhead'>
                            " . _('I am connectable') . "
                        </td>
                        <td>
                            <input name='connectable' type='hidden' value='$connectable'>$connectable
                        </td>
                    </tr>
                    <tr>
                        <td class='rowhead'>
                            " . _('My upload speed is') . "
                        </td>
                        <td>
                            <input type='text' name='speed' class='w-100' maxlength='20'>
                        </td>
                    </tr>
                    <tr>
                        <td class='rowhead'>
                            " . _('What I have to offer') . "
                        </td>
                        <td>
                            <textarea class='w-100' name='offer' rows='2'></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class='rowhead'>
                            " . _('Why I should be promoted') . "
                        </td>
                        <td>
                            <textarea class='w-100' name='reason' rows='2'></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class='rowhead'>
                            " . _('I am an uploader at other sites') . "
                        </td>
                        <td>
                            <div class='level-left'>
                                <input type='radio' name='sites' value='yes' class='right5'>" . _('Yes') . "
                                <input name='sites' type='radio' value='no' class='left20 right5' checked>" . _('No') . "
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class='rowhead'>
                            " . _('Those sites are') . "</td>
                        <td>
                            <input type='text' class='w-100' name='sitenames' maxlength='150'>
                        </td>
                    </tr>
                    <tr>
                        <td class='rowhead'>
                            " . _('I have scene access') . "
                        </td>
                        <td>
                            <div class='level-left'>
                                <input type='radio' name='scene' value='yes' class='right5'>" . _('Yes') . "
                                <input name='scene' type='radio' value='no' class='left20 right5' checked>" . _('No') . "
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div class='level-left top5'>
                                <span class='right10'>
                                    " . _('I know how to create, upload and seed torrents') . "
                                </span>
                                <input type='radio' name='creating' value='yes' class='right5'>" . _('Yes') . "
                                <input type='radio' name='creating' value='no' class='left20 right5' checked>" . _('No') . "
                            </div>
                            <div class='level-left top5 bottom5'>
                                <span class='right10'>
                                    " . _('I understand that I have to keep seeding my torrents until there are at least two other seeders') . "
                                </span>
                                <input type='radio' name='seeding' value='yes' class='right5'>" . _('Yes') . "
                                <input name='seeding' type='radio' value='no' class='left20 right5' checked>" . _('No') . "
                            </div>
                            <input name='form' type='hidden' value='1'>
                        </td>
                    </tr>
                </table>
            </div>
            <div class='has-text-centered margin20'>
                <input type='submit' name='Submit' value='" . _('Send') . "' class='button is-small'>
            </div>
        </form>";
$title = _('Uploader Application');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
