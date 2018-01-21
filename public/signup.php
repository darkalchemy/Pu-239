<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once CACHE_DIR . 'timezones.php';
dbconn();
global $CURUSER, $site_config, $fluent;

if (!$CURUSER) {
    get_template();
} else {
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
$stdfoot = [
    'js' => [
        get_file_name('captcha2_js'),
    ],
];

if (!$site_config['openreg']) {
    stderr('Sorry', 'Invite only - Signups are presently closed. If you have an invite code click <a href="' . $site_config['baseurl'] . '/invite_signup.php"><b>Here</b></a>');
}
$HTMLOUT = $year = $month = $day = $gender = $country = '';
$lang = array_merge(load_language('global'), load_language('signup'));
$signup_vars = getSessionVar('signup_variables');
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

$offset = !empty($signup_vars['user_timezone']) ? $signup_vars['user_timezone'] : $site_config['time_offset'];
$time_select = "
    <select name='user_timezone' class='w-100'>";
foreach ($TZ as $off => $words) {
    if (preg_match("/^time_(-?[\d\.]+)$/", $off, $match)) {
        $time_select .= $match[1] == $offset ? "
        <option value='{$match[1]}' selected>$words</option>" : "
        <option value='{$match[1]}'>$words</option>";
    }
}
$time_select .= '
    </select>';

$countries = countries();
$country .= "
        <option value='999999'" . (empty($CURUSER['country']) ? " selected" : '') . ">Atlantis</option>";

foreach ($countries as $cntry) {
    $country .= "
        <option value='" . (int)$cntry['id'] . "'" . ($signup_vars['country'] == $cntry['id'] ? " selected" : '') . '>' . htmlsafechars($cntry['name']) . "</option>";
}

$gender .= "
    <select name='gender' class='w-100'>
        <option value='Male'" . ($signup_vars['gender'] == 'Male' ? ' selected ' : '') . ">{$lang['signup_male']}</option>
        <option value='Female'" . ($signup_vars['gender'] == 'Female' ? ' selected ' : '') . ">{$lang['signup_female']}</option>
        <option value='NA'" . ($signup_vars['gender'] == 'NA' ? ' selected ' : '') . ">{$lang['signup_na']}</option>
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
    <form id='signup' method='post' action='{$site_config['baseurl']}/takesignup.php' autocomplete='on'>
        <table class='table table-bordered bottom20'>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_uname']}</td>
                <td><input type='text' name='wantusername' id='wantusername' class='w-100' onblur='checkit();' value='{$signup_vars['wantusername']}' /><div id='namecheck'></div></td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_pass']}</td>
                <td>
                    <input type='password' name='wantpassword' id='myElement1' class='password w-100 left' data-display='myDisplayElement1' /> <div class='left' id='myDisplayElement1'></div>
                    <div class='clear'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_passa']}</td>
                <td>
                    <input type='password' name='passagain' id='myElement2' class='password w-100 left' data-display='myDisplayElement2' /> <div class='left' id='myDisplayElement2'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_email']}</td>
                <td>
                    <input type='text' name='email' class='w-100' value='{$signup_vars['email']}' />
                    <div class='alt_bordered top10'>{$lang['signup_valemail']}</div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_timez']}</td>
                <td>{$time_select}</td>
            </tr>";

$year .= "
    <select name='year' class='w-100 bottom10'>
        <option value='0000'>{$lang['signup_year']}</option>";
$i = date("Y");
while ($i >= 1920) {
    $year .= "
        <option value='$i'" . ($signup_vars['year'] == $i ? ' selected ' : '') . ">$i</option>";
    --$i;
}
$year .= '
    </select>';
$month .= "
    <select name='month' class='w-100 bottom10'>
        <option value='00'>{$lang['signup_month']}</option>
        <option value='01'" . ($signup_vars['month'] == '01' ? ' selected ' : '') . ">{$lang['signup_jan']}</option>
        <option value='02'" . ($signup_vars['month'] == '02' ? ' selected ' : '') . ">{$lang['signup_feb']}</option>
        <option value='03'" . ($signup_vars['month'] == '03' ? ' selected ' : '') . ">{$lang['signup_mar']}</option>
        <option value='04'" . ($signup_vars['month'] == '04' ? ' selected ' : '') . ">{$lang['signup_apr']}</option>
        <option value='05'" . ($signup_vars['month'] == '05' ? ' selected ' : '') . ">{$lang['signup_may']}</option>
        <option value='06'" . ($signup_vars['month'] == '06' ? ' selected ' : '') . ">{$lang['signup_jun']}</option>
        <option value='07'" . ($signup_vars['month'] == '07' ? ' selected ' : '') . ">{$lang['signup_jul']}</option>
        <option value='08'" . ($signup_vars['month'] == '08' ? ' selected ' : '') . ">{$lang['signup_aug']}</option>
        <option value='09'" . ($signup_vars['month'] == '09' ? ' selected ' : '') . ">{$lang['signup_sep']}</option>
        <option value='10'" . ($signup_vars['month'] == '10' ? ' selected ' : '') . ">{$lang['signup_oct']}</option>
        <option value='11'" . ($signup_vars['month'] == '11' ? ' selected ' : '') . ">{$lang['signup_nov']}</option>
        <option value='12'" . ($signup_vars['month'] == '12' ? ' selected ' : '') . ">{$lang['signup_dec']}</option>
    </select>";
$day .= '
    <select name="day" class="w-100 bottom10">';
$day .= "
        <option value='00'>{$lang['signup_day']}</option>";
$i = 1;
while ($i <= 31) {
    $day .= "
        <option value='$i'" . ($signup_vars['day'] == $i ? ' selected ' : '') . ">$i</option>";
    ++$i;
}
$day .= '
    </select>';
$HTMLOUT .= "
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_birth']}<span>*</span></td>
                <td>" . $year . $month . $day . '</td>
            </tr>';

$passhint = '';
$questions = [
    [
        'id'       => '1',
        'question' => "{$lang['signup_q1']}",
    ],
    [
        'id'       => '2',
        'question' => "{$lang['signup_q2']}",
    ],
    [
        'id'       => '3',
        'question' => "{$lang['signup_q3']}",
    ],
    [
        'id'       => '4',
        'question' => "{$lang['signup_q4']}",
    ],
    [
        'id'       => '5',
        'question' => "{$lang['signup_q5']}",
    ],
    [
        'id'       => '6',
        'question' => "{$lang['signup_q6']}",
    ],
];
foreach ($questions as $sph) {
    $passhint .= "<option value='" . $sph['id'] . "'>" . $sph['question'] . "</option>\n";
}
$HTMLOUT .= "
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_select']}</td>
                <td><select name='passhint' class='w-100'>\n$passhint\n</select></td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_enter']}</td>
                <td>
                    <input type='text' name='hintanswer' class='w-100' value='{$signup_vars['hintanswer']}'     /><br>
                    <span>
                        {$lang['signup_this_answer']}<br>
                        {$lang['signup_this_answer1']}
                    </span>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_country']}</td>
                <td><select name='country' class='w-100'>\n$country\n</select></td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_gender']}</td>
                <td>$gender</td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'></td>
                <td>
                    <input type='checkbox' name='rulesverify' value='yes'" . ($signup_vars['rulesverify'] == 'yes' ? ' checked ' : '') . "/> {$lang['signup_rules']}<br>
                    <input type='checkbox' name='faqverify' value='yes'" . ($signup_vars['faqverify'] == 'yes' ? ' checked ' : '') . "/> {$lang['signup_faq']}<br>
                    <input type='checkbox' name='ageverify' value='yes'" . ($signup_vars['ageverify'] == 'yes' ? ' checked ' : '') . "/> {$lang['signup_age']}
                </td>
            </tr>" . ($site_config['captcha_on'] ? "
            <tr class='no_hover'>
                <td colspan='2' id='captcha_show'></td>
            </tr>" : '') . "
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
                        <input name="submitme" type="submit" value="' . $value[$i] . '" class="button is-small" />';
}
$HTMLOUT .= '
                    </span>
                </td>
            </tr>
        </table>
    </form>
    </div>';
echo stdhead($lang['head_signup']) . $HTMLOUT . stdfoot($stdfoot);
