<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('uploadapp'));
global $CURUSER, $site_config, $fluent, $cache, $message_stuffs;

$CURUSER['class'] = 1;
$HTMLOUT = '';

if (isset($_POST['form']) != 1) {
    $res = sql_query('SELECT status FROM uploadapp WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if ($CURUSER['class'] >= $site_config['upload_min_class']) {
        stderr($lang['uploadapp_user_error'], $lang['uploadapp_alreadyup']);
    } elseif ($arr['status'] === 'pending') {
        stderr($lang['uploadapp_user_error'], $lang['uploadapp_pending']);
    } elseif ($arr['status'] === 'rejected') {
        stderr($lang['uploadapp_user_error'], $lang['uploadapp_rejected']);
    } else {
        $HTMLOUT .= "
        <h1>{$lang['uploadapp_application']}</h1>
        <form action='./uploadapp.php' method='post' enctype='multipart/form-data'>
            <table class='table table-bordered table-striped'>";
        $ratio = member_ratio($CURUSER['uploaded'], $CURUSER['downloaded']);
        $connect = $fluent->from('peers')
            ->select(null)
            ->select('connectable')
            ->where('userid = ?', $CURUSER['id'])
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
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead'>{$lang['uploadapp_username']}</td>
                    <td>
                        <input name='userid' type='hidden' value='" . (int) $CURUSER['id'] . "'>
                        {$CURUSER['username']}
                     </td>
                </tr>
                <tr>
                    <td class='rowhead'>{$lang['uploadapp_joined']}</td>
                    <td>" . get_date($CURUSER['added'], '', 0, 1) . "</td>
                </tr>
                <tr>
                    <td class='rowhead'>{$lang['uploadapp_ratio']}</td>
                    <td>" . ($ratio >= 1 ? 'No' : 'Yes') . "</td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['uploadapp_connectable']}
                    </td>
                    <td>
                        <input name='connectable' type='hidden' value='$connectable'>$connectable
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['uploadapp_upspeed']}
                    </td>
                    <td>
                        <input type='text' name='speed' class='w-100'>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['uploadapp_offer']}
                    </td>
                    <td>
                        <textarea class='w-100' name='offer' rows='1'></textarea>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['uploadapp_why']}
                    </td>
                    <td>
                        <textarea class='w-100' name='reason' rows='2'></textarea>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['uploadapp_uploader']}</td><td><input type='radio' name='sites' value='yes'>{$lang['uploadapp_yes']}
                        <input name='sites' type='radio' value='no' checked>{$lang['uploadapp_no']}
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['uploadapp_sites']}</td>
                    <td>
                        <textarea class='w-100' name='sitenames' rows='1'></textarea>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['uploadapp_scene']}
                    </td>
                    <td>
                        <input type='radio' name='scene' value='yes'>{$lang['uploadapp_yes']}
                        <input name='scene' type='radio' value='no' checked>{$lang['uploadapp_no']}
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <p>
                            <span class='right10'>
                                {$lang['uploadapp_create']}
                            </span>
                            <input type='radio' name='creating' value='yes'>{$lang['uploadapp_yes']}
                            <input type='radio' name='creating' value='no' checked>{$lang['uploadapp_no']}
                        </p>
                        <p>
                            <span class='right10'>
                                {$lang['uploadapp_seeding']}
                            </span>
                            <input type='radio' name='seeding' value='yes'>{$lang['uploadapp_yes']}
                            <input name='seeding' type='radio' value='no' checked>{$lang['uploadapp_no']}
                        </p>
                        <input name='form' type='hidden' value='1'>
                    </td>
                </tr>
            </table>
            <div class='has-text-centered margin20'>
                <input type='submit' name='Submit' value='{$lang['uploadapp_send']}' class='button is-small'>
            </div>
        </form>";
    }
} else {
    if (!is_valid_id($_POST['userid'])) {
        stderr($lang['uploadapp_error'], $lang['uploadapp_tryagain']);
    }
    if (!$_POST['speed']) {
        stderr($lang['uploadapp_error'], $lang['uploadapp_speedblank']);
    }
    if (!$_POST['offer']) {
        stderr($lang['uploadapp_error'], $lang['uploadapp_offerblank']);
    }
    if (!$_POST['reason']) {
        stderr($lang['uploadapp_error'], $lang['uploadapp_reasonblank']);
    }
    if ($_POST['sites'] === 'yes' && !$_POST['sitenames']) {
        stderr($lang['uploadapp_error'], $lang['uploadapp_sitesblank']);
    }
    $dupe = $fluent->from('uploadapp')
        ->where('userid = ?', $_POST['userid'])
        ->fetch();
    if (!empty($dupe)) {
        stderr($lang['uploadapp_error'], $lang['uploadapp_twice']);
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
        'creating' => htmlsafechars($_POST['creating']),
        'seeding' => htmlsafechars($_POST['seeding']),
    ];
    $res = $fluent->insertInto('uploadapp')
        ->values($values)
        ->execute();
    $cache->delete('new_uploadapp_');
    if (!$res) {
        stderr($lang['uploadapp_error'], $lang['uploadapp_tryagain']);
    } else {
        $subject = 'Uploader application';
        $msg = "An uploader application has just been filled in by [url={$site_config['baseurl']}/userdetails.php?id=" . (int) $CURUSER['id'] . "][b]{$CURUSER['username']}[/b][/url]. Click [url={$site_config['baseurl']}/staffpanel.php?tool=uploadapps&action=app][b]Here[/b][/url] to go to the uploader applications page.";
        $dt = TIME_NOW;
        $subres = $fluent->from('users')
            ->select(null)
            ->select('id')
            ->where('class >= ?', UC_STAFF)
            ->fetchAll();

        foreach ($subres as $arr) {
            $msgs_buffer[] = [
                'sender' => 0,
                'receiver' => $arr['id'],
                'added' => TIME_NOW,
                'msg' => $msg,
                'subject' => $subject,
                'poster' => 0,
            ];
        }
        if (!empty($msgs_buffer)) {
            $message_stuffs->insert($msgs_buffer);
        }
        stderr($lang['uploadapp_appsent'], $lang['uploadapp_success']);
    }
}
echo stdhead('Uploader application page') . wrapper($HTMLOUT) . stdfoot();
