<?php

$lang = [
    //takesignup
    'takesignup_error' => 'Error',
    'takesignup_limit' => 'Sorry, user limit reached. Please try again later.',
    'takesignup_user_error' => 'USER ERROR',
    'takesignup_form_data' => 'Invalid form data!',
    'takesignup_username_length' => 'Username too long or too short',
    'takesignup_allowed_chars' => 'Invalid characters users.',
    'takesignup_blank' => "Don't leave any fields blank.",
    'takesignup_nomatch' => "The passwords didn't match! Must've typoed. Try again.",
    'takesignup_pass_short' => 'Sorry, password is too short (min is 6 chars)',
    'takesignup_pass_long' => 'Sorry, password is too long (max is 40 chars)',
    'takesignup_same' => 'Sorry, password cannot be same as user name.',
    'takesignup_validemail' => "That doesn't look like a valid email address.",
    'takesignup_invalidname' => 'Invalid username.',
    'takesignup_failed' => 'Signup failed',
    'takesignup_qualify' => "Sorry, you're not qualified to become a member of this site.",
    'takesignup_email_used' => 'The e-mail address is already in use.',
    'takesignup_user_exists' => 'Username already exists!',
    'takesignup_fatal_error' => 'Fatal Error!',
    'takesignup_mail' => '',
    'takesignup_confirm' => 'user registration confirmation',
    'takesignup_badusername' => 'The username your trying to use <b>(%s)</b> is on our black list chose another one',
    'takesignup_bannedmail' => 'This email address is banned!<br><br><strong>Reason</strong>:',
    'takesignup_from' => 'From:',
    'takesignup_email_body' => "<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title>{$site_config['site_name']} Registration</title>
</head>
<body>
<p>You have requested a new user account on <#SITENAME#> and you have specified this address (<#USEREMAIL#>) as user contact.</p>
<p>If you did not do this, please ignore this email. The person who entered your email address had the IP address <#IP_ADDRESS#>. Please do not reply.</p>
<p>To confirm your user registration, you have to follow this link:</p>
<p><#REG_LINK#></p>
<p>After you do this, you will be able to use your new account. If you fail to do this, your account will be deleted within a few days. We urge you to read the RULES and FAQ before you start using <#SITENAME#>.</p>
</body>
</html>",
];
