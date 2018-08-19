<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once CACHE_DIR . 'timezones.php';
dbconn();
global $CURUSER, $site_config, $fluent;

$lang = array_merge(load_language('global'), load_language('signup'));
if (!$CURUSER) {
    get_template();
} else {
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
if (!$site_config['openreg_invites']) {
    stderr('Sorry', 'Invite Signups are presently closed');
}
$code = empty($_GET['code']) ? '' : $_GET['code'];
if (!empty($code) && strlen($code) != 64) {
    stderr($lang['stderr_errorhead'], 'Invalid Invite Code!');
}
$stdfoot = [
    'js' => [
        get_file_name('captcha2_js'),
    ],
];
$HTMLOUT = $year = $month = $day = $gender = $country = '';
$count = $fluent->from('users')
    ->select(null)
    ->select('COUNT(*) AS count')
    ->fetch('count');

if ($count >= $site_config['maxusers']) {
    stderr($lang['stderr_errorhead'], sprintf($lang['stderr_ulimit'], $site_config['maxusers']));
}

$time_select = "
    <select name='user_timezone' class='w-100 required'>
        <option value=''>Select Your Timezone</option>";
foreach ($TZ as $off => $words) {
    if (preg_match("/^time_(-?[\d\.]+)$/", $off, $match)) {
        $time_select .= "
        <option value='{$match[1]}'>$words</option>";
    }
}
$time_select .= '
    </select>';

$countries = countries();
$country .= "<option value=''>Select your Country</option>\n";

foreach ($countries as $cntry) {
    $country .= "<option value='" . (int) $cntry['id'] . "'" . ($CURUSER['country'] == $cntry['id'] ? ' selected' : '') . '>' . htmlsafechars($cntry['name']) . "</option>\n";
}

$gender .= "<select name='gender' class='w-100 required'>
    <option value=''>Select Your Gender</option>
    <option value='Male'>{$lang['signup_male']}</option>
    <option value='Female'>{$lang['signup_female']}</option>
    <option value='NA'>{$lang['signup_na']}</option>
    </select>";

$value = [
    '...',
    '...',
    '...',
    '...',
    '...',
    '...',
];
$value[random_int(1, count($value) - 1)] = 'X';

$HTMLOUT .= "
    <div class='half-container has-text-centered portlet'>
    <p class='left10 top10'>{$lang['signup_cookies']}</p>
    <form id='validate_form' method='post' action='{$site_config['baseurl']}/take_invite_signup.php' autocomplete='on'>
        <div class='has-text-centered error size_6 margin20'><span></span></div>
        <table class='table table-bordered bottom20'>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_uname']}</td>
                <td>
                    <input type='text' name='wantusername' id='wantusername' class='w-100 required' onblur='checkit();' minlength='3' />
                    <div id='namecheck'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_pass']}</td>
                <td>
                    <input type='password' name='wantpassword' id='myElement1' class='password w-100 required left' data-display='myDisplayElement1' /> <div class='left' id='myDisplayElement1'></div>
                    <div class='clear'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_passa']}</td>
                <td>
                    <input type='password' name='passagain' id='myElement2' class='password w-100 required left' data-display='myDisplayElement2' /> <div class='left' id='myDisplayElement2'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_invcode']}</td>
                <td><input type='text' class='w-100 required' name='invite' value='{$code}' /></td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_timez']}</td>
                <td>{$time_select}</td>
            </tr>";

$year .= '<select name="year" class="w-100 required bottom10">';
$year .= "<option value=''>{$lang['signup_year']}</option>";
$i = date('Y');
while ($i >= 1920) {
    $year .= '<option value="' . $i . '">' . $i . '</option>';
    --$i;
}
$year .= '</select>';
$month .= "<select name='month' class='w-100 required bottom10'>
    <option value=''>{$lang['signup_month']}</option>
    <option value='01'>{$lang['signup_jan']}</option>
    <option value='02'>{$lang['signup_feb']}</option>
    <option value='03'>{$lang['signup_mar']}</option>
    <option value='04'>{$lang['signup_apr']}</option>
    <option value='05'>{$lang['signup_may']}</option>
    <option value='06'>{$lang['signup_jun']}</option>
    <option value='07'>{$lang['signup_jul']}</option>
    <option value='08'>{$lang['signup_aug']}</option>
    <option value='09'>{$lang['signup_sep']}</option>
    <option value='10'>{$lang['signup_oct']}</option>
    <option value='11'>{$lang['signup_nov']}</option>
    <option value='12'>{$lang['signup_dec']}</option>
    </select>";
$day .= '<select name="day" class="w-100 required bottom10">';
$day .= "<option value=''>{$lang['signup_day']}</option>";
$i = 1;
while ($i <= 31) {
    if ($i < 10) {
        $day .= '<option value="0' . $i . '">0' . $i . '</option>';
    } else {
        $day .= '<option value="' . $i . '">' . $i . '</option>';
    }
    ++$i;
}
$day .= '</select>';
$HTMLOUT .= "
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_birth']}<span>*</span></td>
                <td>" . $year . $month . $day . '</td>
            </tr>';
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
    $passhint .= "<option value='" . $sph['id'] . "'>" . $sph['question'] . "</option>\n";
}
$HTMLOUT .= "
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_select']}</td>
                <td>
                    <select name='passhint' class='w-100 required'>
                        <option value=''>Select a Hint Question</option>
                        $passhint
                    </select>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_enter']}</td>
                <td>
                    <input type='text' name='hintanswer' class='w-100 required' /><br><span>{$lang['signup_this_answer']}<br>{$lang['signup_this_answer1']}</span>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_country']}</td>
                <td>
                    <select name='country' class='w-100 required'>
                        $country
                    </select>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_gender']}</td>
                <td>$gender</td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'></td>
                <td>
                    <input type='checkbox' class='required' name='rulesverify' value='yes' /> {$lang['signup_rules']}<br>
                    <input type='checkbox' class='required' name='faqverify' value='yes' /> {$lang['signup_faq']}<br>
                    <input type='checkbox' class='required' name='ageverify' value='yes' /> {$lang['signup_age']}
                </td>
            </tr>";
if (!empty($_ENV['RECAPTCHA_SITE_KEY'])) {
    $HTMLOUT .= "
                    <tr>
                        <td colspan='2'>
                            <div class='g-recaptcha level-center' data-theme='dark' data-sitekey='{$_ENV['RECAPTCHA_SITE_KEY']}'></div>
                        </td>
                    </tr>";
}

$HTMLOUT .= "
            <tr class='no_hover'>
                <td colspan='2'>
                {$lang['signup_click']} <span class='has-text-danger is-bold'>{$lang['signup_x']}</span> {$lang['signup_click1']}
                </td>
            </tr>
            <tr class='no_hover'>
                <td colspan='2'>
                    <span class='tabs is-marginless'>";
for ($i = 0; $i < count($value); ++$i) {
    $HTMLOUT .= '
                        <input name="submitme" type="submit" value="' . $value[$i] . '" class="button is-small" disabled />';
}
$HTMLOUT .= '
                    </span>
                </td>
            </tr>
        </table>
    </form>
    </div>';
echo stdhead('Invite Signup') . $HTMLOUT . stdfoot($stdfoot);
