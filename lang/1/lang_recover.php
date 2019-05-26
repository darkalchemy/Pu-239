<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_html.php';
global $site_config;

$lang = [
    //stderr
    'stderr_errorhead' => 'Error',
    'stderr_successhead' => 'Success',
    'stderr_invalidemail' => 'You must enter an email address',
    'stderr_notfound' => 'The email address was not found in the database',
    'stderr_dberror' => 'Database error. Please contact an administrator about this.',
    'stderr_confmailsent' => 'If the email address exists, a confirmation email will be sent. Please allow a few minutes for the mail to arrive.',
    'stderr_nomail' => 'Unable to send mail. Please contact an administrator about this error.',
    'stderr_noupdate' => 'Unable to update user data. Please contact an administrator about this error.',
    'stderr_mailed' => 'The new account details have been mailed to the email on record. Please allow a few minutes for the mail to arrive.',
    //head
    'head_recover' => 'Recover',
    //email
    'email_head' => 'Error',
    'email_subjdetails' => 'account details',
    'email_subjreset' => 'password reset confirmation',
    'email_newpass' => "<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title>{$site_config['site']['name']} Reset Password</title>
</head>
<body>
<p>As per your request we have generated a new password for your account.</p>
<p>Here is the information we now have on file for this account:</p>
<br>
<p>
User name: %s<br>
Password:  %s
</p>
<br>
<p>You may login at %s/login.php?returnto=/usercp.php?action=security</p>
<p>and change your password.</p>
<p>--%s</p>
</body>
</html>",
    'email_request' => doc_head() . "
    <title>{$site_config['site']['name']} Reset Password Request</title>
</head>
<body>
<p>Someone, hopefully you, requested that the password for the account associated with this email address (%s) be reset.</p>
<p>The request originated from %s.</p>
<p>If you did not do this, you can ignore this email. Please do not reply.</p>
<p>Should you wish to confirm this request, please follow this link:</p>
<br>
<p><b>%s/recover.php?selector=%s&token=%s</b></p>
<br>
<p>After you do this, your password will be reset and emailed back to you.</p>
<p>--%s</p>
</body>
</html>",
    'email_newpass' => "<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title>{$site_config['site']['name']} Reset Password</title>
</head>
<body>
<p>As per your request we have generated a new password for your account.</p>
<p>Here is the information we now have on file for this account:</p>
<br>
<p>
User name: %s<br>
Password:  %s
</p>
<br>
<p>You may login at %s/login.php?returnto=/usercp.php?action=security</p>
<p>and change your password.</p>
<p>--%s</p>
</body>
</html>",
    //captcha
    'captcha_spam' => 'NO SPAM! Wait 10 seconds and then refresh page',
    'captcha_refresh' => 'Click to refresh image',
    'captcha_imagealt' => 'Captcha image',
    'captcha_pin' => 'PIN:',
    //recover
    'recover_unamepass' => 'Reset Password',
    'recover_form' => 'Enter your email address.',
    'recover_regdemail' => 'Email Address',
    'recover_btn' => 'Do it!',
    'set_new_password' => 'Set your new password',
];
