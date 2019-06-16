<?php

declare(strict_types = 1);

global $site_config;

$lang = [
    //Login
    'login_cookies' => '<span class="is-info">Note:</span> You need cookies enabled to sign up or log in.',
    'login_cookies1' => '<span class="is-info">Note:</span> If your experiencing login issues delete your old cookies.',
    'login_failed' => 'failed login attempts will result in your IP banned for 24 hours.',
    'login_failed_1' => 'You have',
    'login_failed_2' => 'login attempt%s remaining.',
    'login_click' => 'Now click the button marked ',
    'login_x' => 'X',
    'login_use_ssl' => 'SSL Strength',
    'login_ssl1' => 'Browse the site using https just this session',
    'login_ssl2' => 'Browse the site using https permanently',
    'login_member' => 'Member Login',
    'login_noscript' => 'Javascript must be enabled to login and use this site.',
    'login_att' => 'If you dont get the email wait a day or 2 and your account will be confirmed',
    'login_not_logged_in' => 'Not logged in!',
    'login_error' => "<span class='has-text-danger'>Error:</span> The page you tried to view can only be used when you're logged in.",
    'login_username' => 'Username:',
    'login_email' => 'Email',
    'login_password' => 'Password',
    'login_duration' => 'Duration:',
    'login_15mins' => 'Log me out after 15 minutes inactivity',
    'login_refresh' => 'Click to refresh image',
    'login_captcha' => 'Captcha image',
    'login_pin' => 'PIN:',
    'login_login' => 'Log in!',
    'login_signup' => "<a href='{$site_config['paths']['baseurl']}/signup.php' class='is-link'>Join us!</a>",
    'login_login_btn' => 'Login',
    'login_open' => 'Signup is open',
    'login_open1' => "Signup is open <a href='{$site_config['paths']['baseurl']}/signup.php' class='is-link'>Join us!</a>",
    'login_forgot_1' => "<a href='{$site_config['paths']['baseurl']}/recover.php' class='is-link'>Forgot Password</a>",
    'login_closed' => 'Signup is closed',
    'login_closed1' => 'Signup is closed, you need an invite',
    'login_email_pass_incorrect' => 'Your credentials could not be validated.',
    'login_not_verified' => 'You have not verified you email address. Please check your email and click the link to verify it.',
    'login_too_many' => 'Too many requests',
    'login_remember' => 'Remember Me?',
    'login_remember_title' => 'Keep me logged in',
];
