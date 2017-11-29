<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
dbconn();
global $CURUSER, $site_config, $cache;

if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('passhint'));
$stdfoot = [
    'js' => [
        get_file('captcha1_js'),
    ],
];
$HTMLOUT = '';
global $CURUSER;
if ($CURUSER) {
    stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error1']}");
}
$step = (isset($_GET['step']) ? (int)$_GET['step'] : (isset($_POST['step']) ? (int)$_POST['step'] : ''));
if ($step == '1') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!mkglobal('email' . ($site_config['captcha_on'] ? ':captchaSelection' : '') . '')) {
            stderr('Oops', 'Missing form data - You must fill all fields');
        }
        if ($site_config['captcha_on']) {
            if (empty($captchaSelection) || !hash_equals($captchaSelection, getSessionVar('simpleCaptchaAnswer'))) {
                stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error2']}");
                exit();
            }
        }
        if (empty($email)) {
            stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_invalidemail']}");
        }
        if (!validemail($email)) {
            stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_invalidemail1']}");
        }
        $check = sql_query('SELECT id, status, passhint, hintanswer FROM users WHERE email = ' . sqlesc($email)) or sqlerr(__FILE__, __LINE__);
        $assoc = mysqli_fetch_assoc($check) or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_notfound']}");
        if (empty($assoc['passhint']) || empty($assoc['hintanswer'])) {
            stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error3']}");
        }
        if ($assoc['status'] != 'confirmed') {
            stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error4']}");
        } else {
            $HTMLOUT .= "
            <div class='half-container has-text-centered portlet'>
                <form method='post' action='" . $_SERVER['PHP_SELF'] . "?step=2'>
                    <table class='table table-bordered top20 bottom20'>
                        <tr class='no_hover'>
                            <td class='rowhead'>{$lang['main_question']}</td>";
            $id[1] = '/1/';
            $id[2] = '/2/';
            $id[3] = '/3/';
            $id[4] = '/4/';
            $id[5] = '/5/';
            $id[6] = '/6/';
            $question[1] = "{$lang['main_question1']}";
            $question[2] = "{$lang['main_question2']}";
            $question[3] = "{$lang['main_question3']}";
            $question[4] = "{$lang['main_question4']}";
            $question[5] = "{$lang['main_question5']}";
            $question[6] = "{$lang['main_question6']}";
            $passhint = preg_replace($id, $question, (int)$assoc['passhint']);
            $HTMLOUT .= "
                            <td><i><b>{$passhint}?</b></i><input type='hidden' name='id' value='" . (int)$assoc['id'] . "' class='w-100' /></td>
                        </tr>
                        <tr class='no_hover'>
                            <td class='rowhead'>{$lang['main_sec_answer']}</td>
                            <td><input type='text' class='w-100' name='answer' /></td>
                        </tr>
                        <tr class='no_hover'>
                            <td colspan='2'>
                                <div class='has-text-centered'>
                                    <input type='submit' value='{$lang['main_next']}' class='button' />
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>";
            echo stdhead('Reset Lost Password') . $HTMLOUT . stdfoot();
        }
    }
} elseif ($step == '2') {
    if (!mkglobal('id:answer')) {
        exit();
    }
    $select = sql_query('SELECT id, username, hintanswer FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $fetch = mysqli_fetch_assoc($select);
    if (!$fetch) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error5']}");
    }
    if (empty($answer)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error6']}");
    }
    if (!password_verify($answer, $fetch['hintanswer'])) {
        $ip = getip();
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $msg = '' . htmlsafechars($fetch['username']) . ', on ' . get_date(TIME_NOW, '', 1, 0) . ", {$lang['main_message']}" . "\n\n{$lang['main_message1']} " . $ip . ' (' . @gethostbyaddr($ip) . ')' . "\n {$lang['main_message2']} " . $useragent . "\n\n {$lang['main_message3']}\n {$lang['main_message4']}\n";
        $subject = 'Failed password reset';
        sql_query('INSERT INTO messages (receiver, msg, subject, added) VALUES (' . sqlesc((int)$fetch['id']) . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ', ' . TIME_NOW . ')') or sqlerr(__FILE__, __LINE__);
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error7']}");
    } else {
        $sechash = $fetch['hintanswer'];
        $HTMLOUT .= "
        <div class='half-container has-text-centered portlet'>
            <form method='post' action='?step=3'>
                <table class='table table-bordered top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['main_new_pass']}</td>
                        <td><input type='password' class='w-100' name='newpass' /></td>
                    </tr>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['main_new_pass_confirm']}</td><td><input type='password' class='w-100' name='newpassagain' /></td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' value='{$lang['main_changeit']}' class='button' />
                                <input type='hidden' name='id' value='" . (int)$fetch['id'] . "' />
                                <input type='hidden' name='hash' value='" . $sechash . "' />
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>";

        echo stdhead('Reset Lost Password') . $HTMLOUT . stdfoot();
    }
} elseif ($step == '3') {
    if (!mkglobal('id:newpass:newpassagain:hash')) {
        exit();
    }
    $select = sql_query('SELECT id, hintanswer FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $fetch = mysqli_fetch_assoc($select) or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error8']}");
    if (empty($newpass)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error9']}");
    }
    if ($newpass != $newpassagain) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error10']}");
    }
    if (strlen($newpass) < 6) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error11']}");
    }
    if (strlen($newpass) > 255) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error12']}");
    }
    if (!hash_equals($hash, $fetch['hintanswer'])) {
        die('invalid hash');
    }
    $newpassword = make_passhash($newpass);
    sql_query('UPDATE users SET passhash = ' . sqlesc($newpassword) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('MyUser_' . $id, [
        'passhash' => $newpassword,
    ], $site_config['expires']['curuser']);
    $cache->update_row('user' . $id, [
        'passhash' => $newpassword,
    ], $site_config['expires']['user_cache']);
    unsetSessionVar('simpleCaptchaAnswer');
    unsetSessionVar('simpleCaptchaTimestamp');
    if (!mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error13']}");
    } else {
        stderr("{$lang['stderr_successhead']}", "{$lang['stderr_error14']} <a href='{$site_config['baseurl']}/login.php' class='altlink'><b>{$lang['stderr_error15']}</b></a> {$lang['stderr_error16']}", false);
    }
} else {
    $HTMLOUT .= "
    <div class='half-container has-text-centered portlet'>
        <form method='post' action='" . $_SERVER['PHP_SELF'] . "?step=1'>
            <table class='table table-bordered top20 bottom20'>
                <tr class='no_hover'>
                    <td colspan='2'><p>{$lang['main_body']}</p><br></td>
                </tr>
                <tr class='no_hover'>
                    <td class='rowhead'>{$lang['main_email_add']}</td>
                    <td><input type='text' class='w-100' name='email' /></td>
                </tr>" . ($site_config['captcha_on'] ? "
                <tr class='no_hover'>
                    <td colspan='2' id='captcha_show'></td>
                </tr>" : '') . "
                <tr class='no_hover'>
                    <td colspan='2'>
                        <div class='has-text-centered'>
                            <input type='submit' value='{$lang['main_recover']}' class='button' />
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>";
    echo stdhead('Reset Lost Password', true) . $HTMLOUT . stdfoot($stdfoot);
}

