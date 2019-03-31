<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once CACHE_DIR . 'timezones.php';
dbconn();
global $CURUSER, $site_config, $fluent, $session;

$lang = array_merge(load_language('global'), load_language('signup'));
if (!$CURUSER) {
    get_template();
} else {
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
if (!$site_config['openreg']) {
    stderr('Sorry', 'Invite only - Signups are presently closed. If you have an invite code click <a href="' . $site_config['baseurl'] . '/invite_signup.php"><span class="has-text-success">Here</span></a>');
}

$stdfoot = [
    'js' => [
        get_file_name('check_username_js'),
        get_file_name('pStrength_js'),
    ],
];
if (!empty($_ENV['RECAPTCHA_SECRET_KEY'])) {
    $stdfoot = array_merge_recursive($stdfoot, [
        'js' => [
            get_file_name('recaptcha_js'),
        ],
    ]);
}

$HTMLOUT = $date = $gender = $country = '';
$signup_vars = $session->get('signup_variables');
if (!empty($signup_vars)) {
    $signup_vars = unserialize($signup_vars);
}
$count = $fluent->from('users')
                ->select(null)
                ->select('COUNT(*) AS count')
                ->fetch('count');

if ($count >= $site_config['maxusers']) {
    stderr($lang['stderr_errorhead'], sprintf($lang['stderr_ulimit'], $site_config['maxusers']));
}

$time_select = "
    <select name='user_timezone' class='w-100' required>
        <option value=''>Select Your Timezone</option>";
foreach ($TZ as $off => $words) {
    if (preg_match("/^time_(-?[\d\.]+)$/", $off, $match)) {
        $time_select .= "
        <option value='{$match[1]}'" . ($signup_vars['user_timezone'] == $match[1] ? ' selected' : '') . ">$words</option>";
    }
}
$time_select .= '
    </select>';

$countries = countries();
$country .= "
        <option value=''>Select your Country</option>";

foreach ($countries as $cntry) {
    $country .= "
        <option value='" . (int) $cntry['id'] . "'" . ($signup_vars['country'] == $cntry['id'] ? ' selected' : '') . '>' . htmlsafechars($cntry['name']) . '</option>';
}

$HTMLOUT .= "
    <form method='post' action='{$site_config['baseurl']}/takesignup.php' accept-charset='utf-8'>
        <div class='level-center'>";

$body = "
            <tr>
                <td class='has-text-centered' colspan='2'>
                    <p>{$lang['signup_cookies']}</p>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_uname']}</td>
                <td>
                    <input type='text' name='wantusername' id='wantusername' class='w-100' onblur='checkit();' value='{$signup_vars['wantusername']}' autocomplete='on' required pattern='[\p{L}\p{N}_-]{3,64}'>
                    <div id='namecheck'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_pass']}</td>
                <td>
                    <input type='password' name='wantpassword' id='myElement1' class='w-100' data-display='myDisplayElement1' autocomplete='on' required minlength='6'> <div id='myDisplayElement1'></div>
                    <div class='clear'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_passa']}</td>
                <td>
                    <input type='password' name='passagain' id='myElement2' class='w-100' data-display='myDisplayElement2' autocomplete='on' required minlength='6'> <div id='myDisplayElement2'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_email']}</td>
                <td>
                    <input type='email' name='email' class='w-100' value='{$signup_vars['email']}' autocomplete='on' required>
                    <div class='alt_bordered top10'>{$lang['signup_valemail']}</div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_timez']}</td>
                <td>{$time_select}</td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_birth']}<span>*</span></td>
                <td><input type='date' id='date' name='date' class='w-100' value='{$signup_vars['date']}' required></td>
            </tr>";

$passhint = '';
$questions = [
    [
        'id' => '1',
        'question' => "{$lang['signup_q1']}",
    ],
    [
        'id' => '2',
        'question' => "{$lang['signup_q2']}",
    ],
    [
        'id' => '3',
        'question' => "{$lang['signup_q3']}",
    ],
    [
        'id' => '4',
        'question' => "{$lang['signup_q4']}",
    ],
    [
        'id' => '5',
        'question' => "{$lang['signup_q5']}",
    ],
    [
        'id' => '6',
        'question' => "{$lang['signup_q6']}",
    ],
];
foreach ($questions as $sph) {
    $passhint .= "<option value='" . $sph['id'] . "'" . ($signup_vars['passhint'] == $sph['id'] ? ' selected' : '') . '>' . $sph['question'] . "</option>\n";
}
$body .= "
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_select']}</td>
                <td>
                    <select name='passhint' class='w-100' required>
                        <option value=''>Select a Hint Question</option>
                        $passhint
                    </select>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_enter']}</td>
                <td>
                    <input type='text' name='hintanswer' class='w-100' value='{$signup_vars['hintanswer']}' autocomplete='on' required><br>
                    <span>
                        {$lang['signup_this_answer']}<br>
                        {$lang['signup_this_answer1']}
                    </span>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_country']}</td>
                <td>
                    <select name='country' class='w-100' required>
                        $country
                    </select>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_gender']}</td>
                <td>
                    <div class='level-left'>
                        <label for='male'>{$lang['signup_male']}</label>
                        <input type='radio' name='gender' value='Male' class='left5'" . ($signup_vars['gender'] == 'Male' ? ' checked' : '') . " required>
                        <label for='female' class='left10'>{$lang['signup_female']}</label>
                        <input type='radio' name='gender' value='Female' class='left5'" . ($signup_vars['gender'] == 'Female' ? ' checked' : '') . ">
                    </div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'></td>
                <td>
                    <input type='checkbox' name='rulesverify' value='yes'" . (!empty($signup_vars['rulesverify']) && $signup_vars['rulesverify'] === 'yes' ? ' checked ' : '') . " required> {$lang['signup_rules']}<br>
                    <input type='checkbox' name='faqverify' value='yes'" . (!empty($signup_vars['faqverify']) && $signup_vars['faqverify'] === 'yes' ? ' checked ' : '') . " required> {$lang['signup_faq']}<br>
                    <input type='checkbox' name='ageverify' value='yes'" . (!empty($signup_vars['ageverify']) && $signup_vars['ageverify'] === 'yes' ? ' checked ' : '') . " required> {$lang['signup_age']}
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='has-text-centered' colspan='2'>
                     <span class='has-text-centered margin5'>
                        <input type='hidden' id='token' name='token' value=''>
                        <input type='hidden' id='csrf' name='csrf' value='" . $session->get('csrf_token') . "'>
                        <input id='signup_captcha_check' type='submit' value='" . (!empty($_ENV['RECAPTCHA_SITE_KEY']) ? 'Verifying reCAPTCHA' : 'Signup') . "' class='button is-small' disabled>
                    </span>
                </td>
            </tr>";
$HTMLOUT .= main_table($body, '', '', 'w-50', '') . '
        </div>
    </form>';
echo stdhead($lang['head_signup']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
