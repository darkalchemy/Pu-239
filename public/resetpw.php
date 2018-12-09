<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once INCL_DIR . 'function_recaptcha.php';
dbconn();
global $CURUSER, $site_config, $cache, $session, $mysqli;

if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('passhint'));
$HTMLOUT = '';

$stdfoot = [
    'js' => [
        get_file_name('recaptcha_js'),
    ],
];

if ($CURUSER) {
    stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error1']}");
}
$step = (isset($_GET['step']) ? (int) $_GET['step'] : (isset($_POST['step']) ? (int) $_POST['step'] : ''));
if ($step == '1') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!mkglobal('email')) {
            stderr('Oops', 'Missing form data - You must fill all fields');
        }
        if (!empty($_ENV['RECAPTCHA_SECRET_KEY'])) {
            $response = !empty($_POST['token']) ? $_POST['token'] : '';
            $result = verify_recaptcha($response);
            if ($result !== 'valid') {
                $session->set('is-warning', "[h2]reCAPTCHA failed. {$result}[/h2]");
                header("Location: {$site_config['baseurl']}/resetpw.php");
                die();
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
                <form method='post' action='" . $_SERVER['PHP_SELF'] . "?step=2'>
                    <div class='level-center'>";
            $body = "
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
            $passhint = preg_replace($id, $question, (int) $assoc['passhint']);
            $body .= "
                            <td><i><b>{$passhint}?</b></i><input type='hidden' name='id' value='" . (int) $assoc['id'] . "' class='w-100'></td>
                        </tr>
                        <tr class='no_hover'>
                            <td class='rowhead'>{$lang['main_sec_answer']}</td>
                            <td><input type='text' class='w-100' name='answer'></td>
                        </tr>
                        <tr class='no_hover'>
                            <td colspan='2'>
                                <div class='has-text-centered'>
                                    <input type='submit' value='{$lang['main_next']}' class='button is-small'>
                                </div>
                            </td>
                        </tr>";
            $HTMLOUT .= main_table($body, '', '', 'w-50', '') . '
                </form>';
            echo stdhead('Reset Lost Password') . $HTMLOUT . stdfoot();
        }
    }
} elseif ($step == '2') {
    if (!mkglobal('id:answer')) {
        die();
    }
    $select = sql_query('SELECT id, username, hintanswer FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $fetch = mysqli_fetch_assoc($select);
    if (!$fetch) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error5']}");
    }
    if (empty($answer)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error6']}");
    }
    if (!password_verify($answer, $fetch['hintanswer']) && md5($answer) !== $fetch['hintanswer']) {
        $ip = getip();
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $msg = '' . htmlsafechars($fetch['username']) . ', on ' . get_date(TIME_NOW, '', 1, 0) . ", {$lang['main_message']}" . "\n\n{$lang['main_message1']} " . $ip . ' (' . @gethostbyaddr($ip) . ')' . "\n {$lang['main_message2']} " . $useragent . "\n\n {$lang['main_message3']}\n {$lang['main_message4']}\n";
        $subject = 'Failed password reset';
        sql_query('INSERT INTO messages (receiver, msg, subject, added) VALUES (' . sqlesc((int) $fetch['id']) . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ', ' . TIME_NOW . ')') or sqlerr(__FILE__, __LINE__);
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error7']}");
    } else {
        $stdfoot = [
            'js' => [
                get_file_name('pStrength_js'),
            ],
        ];

        $sechash = $fetch['hintanswer'];
        $HTMLOUT .= "
            <form method='post' action='?step=3'>
                <div class='level-center'>";
        $HTMLOUT .= main_table("
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['main_new_pass']}</td>
                        <td>
                            <input type='password' class='w-100' name='wantpassword' id='myElement1' data-display='myDisplayElement1' autocomplete='on' required minlength='6'>
                            <div id='myDisplayElement1'></div>
                            <div class='clear'></div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['main_new_pass_confirm']}</td>
                        <td>
                            <input type='password' class='w-100' name='passagain' id='myElement2' data-display='myDisplayElement2' autocomplete='on' required minlength='6'>
                            <div id='myDisplayElement2'></div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' value='{$lang['main_changeit']}' class='button is-small'>
                                <input type='hidden' name='id' value='" . (int) $fetch['id'] . "'>
                                <input type='hidden' name='hash' value='" . $sechash . "'>
                            </div>
                        </td>
                    </tr>", '', '', 'w-50', '') . '
            </form>';

        echo stdhead('Reset Lost Password') . $HTMLOUT . stdfoot($stdfoot);
    }
} elseif ($step == '3') {
    //dd($_POST);
    if (!mkglobal('id:wantpassword:passagain:hash')) {
        die();
    }
    $select = sql_query('SELECT id, hintanswer FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $fetch = mysqli_fetch_assoc($select) or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error8']}");
    if (empty($wantpassword)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error9']}");
    }
    if ($wantpassword != $passagain) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error10']}");
    }
    if (strlen($wantpassword) < 6) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error11']}");
    }
    if (strlen($wantpassword) > 255) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error12']}");
    }
    if (!hash_equals($hash, $fetch['hintanswer'])) {
        die('invalid hash');
    }
    $set = [
        'passhash' => make_passhash($wantpassword),
    ];
    if ($user_stuffs->update($set, $id, false)) {
        stderr("{$lang['stderr_successhead']}", "{$lang['stderr_error14']} <a href='{$site_config['baseurl']}/login.php' class='altlink'><b>{$lang['stderr_error15']}</b></a> {$lang['stderr_error16']}", false);
    } else {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error13']}");
    }
} else {
    $HTMLOUT .= "
        <form method='post' action='" . $_SERVER['PHP_SELF'] . "?step=1'>
            <div class='level-center'>";
    $HTMLOUT .= main_table("
                <tr class='no_hover'>
                    <td class='has-text-centered' colspan='2'>
                        <p>{$lang['main_body']}</p>
                    </td>
                </tr>
                <tr class='no_hover'>
                    <td class='rowhead'>{$lang['main_email_add']}</td>
                    <td>
                        <input type='text' class='w-100' name='email'>
                        <input type='hidden' id='token' name='token' value=''>
                    </td>
                </tr>
                <tr class='no_hover'>
                    <td colspan='2'>
                        <div class='has-text-centered'>
                            <input id='recover_captcha_check' type='submit' value='" . (!empty($_ENV['RECAPTCHA_SITE_KEY']) ? 'Verifying reCAPTCHA' : 'Recover') . "' class='button is-small'" . (!empty($_ENV['RECAPTCHA_SITE_KEY']) ? ' disabled' : '') . '/>
                        </div>
                    </td>
                </tr>', '', '', 'w-50', '') . '
        </form>';
    echo stdhead('Reset Lost Password') . wrapper($HTMLOUT) . stdfoot($stdfoot);
}
