<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once CACHE_DIR . 'timezones.php';
dbconn();
global $CURUSER;
if (!$CURUSER) {
    get_template();
}
$stdfoot = [
    'js' => [
        get_file('captcha2_js')
    ],
];
$lang = array_merge(load_language('global'), load_language('signup'));
$res = sql_query('SELECT COUNT(*) FROM users') or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
if ($arr[0] >= $site_config['invites']) {
    stderr('Sorry', 'The current user account limit (' . number_format($site_config['invites']) . ') has been reached. Inactive accounts are pruned all the time, please check back again later...');
}
if (!$site_config['openreg_invites']) {
    stderr('Sorry', 'Invite Signups are closed presently');
}
// TIMEZONE STUFF
$offset = (string)$site_config['time_offset'];
$time_select = "<select name='user_timezone' class='w-100'>";
foreach ($TZ as $off => $words) {
    if (preg_match("/^time_(-?[\d\.]+)$/", $off, $match)) {
        $time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
    }
}
$time_select .= '</select>';
// TIMEZONE END
$HTMLOUT = $year = $month = $day = $gender = $country = '';
$countries = countries();
foreach ($countries as $cntry) {
    $country .= "<option value='" . (int)$cntry['id'] . "'" . ($CURUSER['country'] == $cntry['id'] ? " selected='selected'" : '') . '>' . htmlsafechars($cntry['name']) . "</option>\n";
}

$gender .= "<select name='gender' class='w-100'>
    <option value='Male'>{$lang['signup_male']}</option>
    <option value='Female'>{$lang['signup_female']}</option>
    <option value='NA'>{$lang['signup_na']}</option>
    </select>";
// Normal Entry Point...
//== click X by Retro
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
    <form method='post' action='{$site_config['baseurl']}/take_invite_signup.php'>
    <table class='table table-bordered bottom20'>
    <tr><td class='rowhead'>{$lang['signup_uname']}</td><td><input type='text' class='w-100' name='wantusername' id='wantusername' onblur='checkit();' /><div id='namecheck'></div></td></tr>
    <tr><td class='rowhead'>{$lang['signup_pass']}</td><td><input class='password w-100' type='password' name='wantpassword' /></td></tr>
    <tr><td class='rowhead'>{$lang['signup_passa']}</td><td><input type='password' class='w-100' name='passagain' /></td></tr>
    <tr><td class='rowhead'>{$lang['signup_invcode']}</td><td><input type='text' class='w-100' name='invite' /></td></tr>
    <tr><td class='rowhead'>{$lang['signup_email']}</td><td><input type='text' class='w-100' name='email' />
        <div class='alt_bordered top10'>{$lang['signup_valemail']}</div>
    </td></tr>
    <tr><td class='rowhead'>{$lang['signup_timez']}</td><td>{$time_select}</td></tr>";
//==09 Birthday mod
$year .= '<select name="year" class="w-25 right10 bottom10">';
$year .= "<option value='0000'>{$lang['signup_year']}</option>";
$i = date("Y");
while ($i >= 1920) {
    $year .= '<option value="' . $i . '">' . $i . '</option>';
    --$i;
}
$year .= '</select>';
$month .= "<select name='month' class='w-25 right10 bottom10'>
    <option value='00'>{$lang['signup_month']}</option>
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
$day .= '<select name="day" class="w-25 bottom10">';
$day .= "<option value='00'>{$lang['signup_day']}</option>";
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
$HTMLOUT .= "<tr><td class='rowhead'>{$lang['signup_birth']}<span>*</span></td><td>" . $year . $month . $day . '</td></tr>';
//==End
//==Passhint
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
$HTMLOUT .= "<tr><td class='rowhead'>{$lang['signup_select']}</td><td><select name='passhint' class='w-100'>\n$passhint\n</select></td></tr>
        <tr><td class='rowhead'>{$lang['signup_enter']}</td><td><input type='text' class='w-100'  name='hintanswer' /><br><div class='alt_bordered top10'>{$lang['signup_this_answer']}<br>{$lang['signup_this_answer1']}</div></td></tr>
            <tr>
                <td class='rowhead'>{$lang['signup_country']}</td>
                <td><select name='country' class='w-100'>\n$country\n</select></td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['signup_gender']}</td>
                <td>$gender</td>
            </tr>

      <tr><td class='rowhead'></td>
      <td>
      <input type='checkbox' name='rulesverify' value='yes' /> {$lang['signup_rules']}<br>
      <input type='checkbox' name='faqverify' value='yes' /> {$lang['signup_faq']}<br>
      <input type='checkbox' name='ageverify' value='yes' /> {$lang['signup_age']}</td></tr>
      " . ($site_config['captcha_on'] ? "<tr><td colspan='2' id='captcha_show'></td></tr>" : '') . "
      <tr><td colspan='2'>{$lang['signup_click']} <strong>{$lang['signup_x']}</strong> {$lang['signup_click1']}</td></tr><tr>
      <td colspan='2'><span class='tabs is-marginless'>";
for ($i = 0; $i < count($value); ++$i) {
    $HTMLOUT .= '<input name="submitme" type="submit" value="' . $value[$i] . '" class="button" />';
}
$HTMLOUT .= '</span></td></tr></table></form></div>';
echo stdhead('Invites') . $HTMLOUT . stdfoot($stdfoot);
