<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CACHE_DIR . 'timezones.php';
check_user_status();
global $CURUSER, $site_config;

$lang = array_merge(load_language('global'), load_language('usercp'));
$HTMLOUT = $stylesheets = $wherecatina = '';
$templates = sql_query('SELECT id, name FROM stylesheets ORDER BY id');
while ($templ = mysqli_fetch_assoc($templates)) {
    if (file_exists(ROOT_DIR . "templates/$templ[id]/template.php")) {
        $stylesheets .= "<option value='" . (int)$templ['id'] . "'" . ($templ['id'] == get_stylesheet() ? " selected" : '') . '>' . htmlsafechars($templ['name']) . '</option>';
    }
}
$countries = "<option value='0'>---- {$lang['usercp_none']} ----</option>\n";
$ct_r = sql_query('SELECT id,name FROM countries ORDER BY name') or sqlerr(__FILE__, __LINE__);
while ($ct_a = mysqli_fetch_assoc($ct_r)) {
    $countries .= "<option value='" . (int)$ct_a['id'] . "'" . ($CURUSER['country'] == $ct_a['id'] ? " selected" : '') . '>' . htmlsafechars($ct_a['name']) . "</option>\n";
}
$offset = ($CURUSER['time_offset'] != '') ? (string)$CURUSER['time_offset'] : (string)$site_config['time_offset'];
$time_select = "
            <select name='user_timezone' style='min-width: 400px'>";
foreach ($TZ as $off => $words) {
    if (preg_match("/^time_(-?[\d\.]+)$/", $off, $match)) {
        $time_select .= $match[1] == $offset ? "
                <option value='{$match[1]}' selected>$words</option>" : "
                <option value='{$match[1]}'>$words</option>";
    }
}
$time_select .= '
            </select>';
if ($CURUSER['dst_in_use']) {
    $dst_check = 'checked';
} else {
    $dst_check = '';
}
if ($CURUSER['auto_correct_dst']) {
    $dst_correction = 'checked';
} else {
    $dst_correction = '';
}
$possible_actions = [
    'avatar',
    'signature',
    'social',
    'location',
    'security',
    'links',
    'torrents',
    'api',
    'personal',
    'default',
];
$action = isset($_GET['action']) ? htmlsafechars(trim($_GET['action'])) : '';
if (!in_array($action, $possible_actions)) {
    setSessionVar('is-warning', "[h2]Error! Change a few things up and try submitting again.[/h2]");
}
if (isset($_GET['edited'])) {
    setSessionVar('is-success', "[h2]{$lang['usercp_updated']}![/h2]");
    if (isset($_GET['mailsent'])) {
        setSessionVar('is-success', "[h2]{$lang['usercp_mail_sent']}![/h2]");
    }
} elseif (isset($_GET['emailch'])) {
    setSessionVar('is-success', "[h2]{$lang['usercp_emailch']}![/h2]");
}

$HTMLOUT .= "
        <div>
            <div class='w-100'>
                <form method='post' action='takeeditcp.php'>
                    <div class='bottom20'>
                        <ul class='level-center bg-06'>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=avatar'>Avatar</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=signature'>Signature</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=default'>PM's</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=security'>Security</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=torrents'>Torrents</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=api'>API</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=personal'>Personal</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=social'>Social</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=location'>Location</a></li>
                            <li class='altlink margin20'><a href='{$site_config['baseurl']}/usercp.php?action=links'>Links</a></li>
                        </ul>
                    </div>
                    <h1 class='has-text-centered'>Welcome " . format_username((int)$CURUSER['id']) . "!</h1>
                    <div class='level has-text-centered flex-top'>
                        <span class='margin20'>";
if (!empty($CURUSER['avatar']) && $CURUSER['av_w'] > 5 && $CURUSER['av_h'] > 5) {
    $HTMLOUT .= "
                            <img class='img-polaroid' src='{$CURUSER['avatar']}' width='{$CURUSER['av_w']}' height='{$CURUSER['av_h']}' alt='' />
                        </span>";
} else {
    $HTMLOUT .= "
                            <img class='img-polaroid' src='{$site_config['pic_baseurl']}forumicons/default_avatar.gif' alt='' /></td>
                        </span>";
}
if ($action == 'avatar') {
    $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='avatar' />
                                            Avatar Options
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";
    if (!($CURUSER['avatarpos'] == 0 or $CURUSER['avatarpos'] != 1)) {
        $HTMLOUT .= "
                                    <tr>
                                        <td class='rowhead'>{$lang['usercp_avatar']}</td>
                                        <td>
                                            <input type='text' name='avatar' class='w-100' value='" . htmlsafechars($CURUSER['avatar']) . "' />
                                            <p class='small'>
                                                Width should be 150px. (Will be resized if necessary)
                                            </p>
                                            <p class='small'>
                                                If you need a avatar, try our  <a href='{$site_config['baseurl']}/avatar/index.php'>Avatar creator</a>.<br>
                                                If you need a host for your image, try our  <a href='{$site_config['baseurl']}/bitbucket.php'>Bitbucket</a>.
                                            </p>
                                        </td>
                                    </tr>";
    } else {
        $HTMLOUT .= "
                                    <tr>
                                        <td class='rowhead'>{$lang['usercp_avatar']}</td>
                                        <td>
                                            <input type='text' name='avatar' class='w-100' value='" . htmlsafechars($CURUSER['avatar']) . "' readonly='readonly' />
                                            {$lang['usercp_no_avatar_allow']}
                                        </td>
                                    </tr>";
    }
    $HTMLOUT .= tr('Is your avatar offensive', '
                                            <input type="radio" name="offensive_avatar" ' . ($CURUSER['offensive_avatar'] == 'yes' ? 'checked' : '') . ' value="yes" /> Yes
                                            <input type="radio" name="offensive_avatar" ' . ($CURUSER['offensive_avatar'] == 'no' ? 'checked' : '') . ' value="no" /> No', 1);
    $HTMLOUT .= tr('View offensive avatars', '
                                            <input type="radio" name="view_offensive_avatar" ' . ($CURUSER['view_offensive_avatar'] == 'yes' ? 'checked' : '') . ' value="yes" /> Yes
                                            <input type="radio" name="view_offensive_avatar" ' . ($CURUSER['view_offensive_avatar'] == 'no' ? 'checked' : '') . ' value="no" /> No', 1);
    $HTMLOUT .= tr('View avatars', '
                                            <input type="radio" name="avatars" ' . ($CURUSER['avatars'] == 'yes' ? 'checked' : '') . ' value="yes" /> Yes (Low bandwidth user may want to disable this)
                                            <input type="radio" name="avatars" ' . ($CURUSER['avatars'] == 'no' ? 'checked' : '') . ' value="no" /> No', 1);
    $HTMLOUT .= "
                                    <tr>
                                        <td colspan='2'>
                                            <div class='has-text-centered'>
                                                <input class='button is-small' type='submit' value='Submit changes!' />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>";
} elseif ($action == 'signature') {
    $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='signature' />
                                            Signature Options
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";
    $HTMLOUT .= tr('View Signatures', '
                                            <input type="radio" name="signatures" ' . ($CURUSER['signatures'] == 'yes' ? 'checked' : '') . ' value="yes" /> Yes
                                            <input type="radio" name="signatures" ' . ($CURUSER['signatures'] == 'no' ? 'checked' : '') . ' value="no" /> No', 1);
    $HTMLOUT .= tr('Signature', '
                                            <textarea name="signature" class="w-100" rows="4">' . htmlsafechars($CURUSER['signature'], ENT_QUOTES) . '</textarea><br>BBcode can be used', 1);
    $HTMLOUT .= tr($lang['usercp_info'], "
                                            <textarea name='info' class='w-100' rows='4'>" . htmlsafechars($CURUSER['info'], ENT_QUOTES) . "</textarea><br>{$lang['usercp_tags']}", 1);
    $HTMLOUT .= "
                                    <tr>
                                        <td colspan='2'>
                                            <div class='has-text-centered'>
                                                <input class='button is-small' type='submit' value='Submit changes!' />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>";
} elseif ($action == 'api') {
    $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='api' />
                                            API
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";
    $HTMLOUT .= tr('Torrent Pass', '<input type="text" class="w-100" name="torrent_pass"  value="' . htmlsafechars($CURUSER['torrent_pass']) . '" readonly onClick="this.select();" /><div class="left10 top10">This is used for downloading and seeding torrents, in your torrent client and your rss reader.</div>', 1);
    $HTMLOUT .= tr('Auth', '<input type="text" class="w-100" name="auth"  value="' . htmlsafechars($CURUSER['auth']) . '" readonly onClick="this.select();" /><div class="left10 top10">This is only used by an upload script, msg any staff member for the details.</div>', 1);
    $HTMLOUT .= tr('API Key', '<input type="text" class="w-100" name="auth"  value="' . htmlsafechars($CURUSER['apikey']) . '" readonly onClick="this.select();" /><div class="left10 top10">This is only used by auto downloaders, such as CouchPotato, SickRage and others. (API not implemented, yet)</div>', 1);
    $HTMLOUT .= "
                                </tbody>";
} elseif ($action == 'social') {
    $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='social' />
                                            Social
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";
    $HTMLOUT .= tr('<img src="' . $site_config['pic_baseurl'] . 'forums/google_talk.gif" alt="Google Talk" class="tooltipper right10" title="Google Talk" />Google Talk', '
                                            <input type="text" class="w-100" name="google_talk"  value="' . htmlsafechars($CURUSER['google_talk']) . '" />', 1);
    $HTMLOUT .= tr('<img src="' . $site_config['pic_baseurl'] . 'forums/msn.gif" alt="Msn" class="tooltipper right10" title="Msn" />MSN ', '
                                            <input type="text" class="w-100" name="msn"  value="' . htmlsafechars($CURUSER['msn']) . '" />', 1);
    $HTMLOUT .= tr('<img src="' . $site_config['pic_baseurl'] . 'forums/aim.gif" alt="Aim" class="tooltipper right10" title="Aim" />AIM', '
                                            <input type="text" class="w-100" name="aim"  value="' . htmlsafechars($CURUSER['aim']) . '" />', 1);
    $HTMLOUT .= tr('<img src="' . $site_config['pic_baseurl'] . 'forums/yahoo.gif" alt="Yahoo" class="tooltipper right10" title="Yahoo" />Yahoo ', '
                                            <input type="text" class="w-100" name="yahoo"  value="' . htmlsafechars($CURUSER['yahoo']) . '" />', 1);
    $HTMLOUT .= tr('<img src="' . $site_config['pic_baseurl'] . 'forums/icq.gif" alt="Icq" class="tooltipper right10" title="Icq" />icq ', '
                                            <input type="text" class="w-100" name="icq"  value="' . htmlsafechars($CURUSER['icq']) . '" />', 1);
    $HTMLOUT .= tr('<img src="' . $site_config['pic_baseurl'] . 'forums/www.gif" alt="www" class="tooltipper right10" title="www" width="16px" height="16px" />Website ', '
                                            <input type="text" class="w-100" name="website"  value="' . htmlsafechars($CURUSER['website']) . '" />', 1);
    $HTMLOUT .= "
                                    <tr>
                                        <td colspan='2'>
                                            <div class='has-text-centered'>
                                                <input class='button is-small' type='submit' value='Submit changes!' />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>";
} elseif ($action == 'location') {
    $datetime = unixstamp_to_human(TIME_NOW);
    $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='location' />
                                            Location Options => Is this the correct time? [{$datetime['hour']}:{$datetime['minute']} {$datetime['ampm']}]
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";

    $HTMLOUT .= tr($lang['usercp_tz'], $time_select, 1);
    $HTMLOUT .= tr($lang['usercp_checkdst'], "
                                            <input type='checkbox' name='checkdst' id='tz-checkdst' value='1' $dst_correction /> {$lang['usercp_auto_dst']}
                                            <div id='tz-checkmanual' class='is_hidden'>
                                                <input type='checkbox' name='manualdst' value='1' $dst_check /> {$lang['usercp_is_dst']}
                                            </div>", 1);
    $HTMLOUT .= tr($lang['usercp_country'], "
                                            <select name='country' class='w-100'>
                                                $countries
                                            </select>", 1);
    $HTMLOUT .= tr($lang['usercp_language'], "
                                            <select name='language' class='w-100'>
                                                <option value='1'" . (get_language() == '1' ? " selected" : '') . ">En</option>
                                                <option value='2'" . (get_language() == '2' ? " selected" : '') . ">Dk</option>
                                                <option value='3'" . (get_language() == '3' ? " selected" : '') . '>New</option>
                                            </select>', get_language());
    $HTMLOUT .= "
                                    <tr>
                                        <td colspan='2'>
                                            <div class='has-text-centered'>
                                                <input class='button is-small' type='submit' value='Submit changes!' />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>";
} elseif ($action == 'links') {
    $HTMLOUT .= "
                        <div class='table-wrapper w-25'>
                            <table class='table table-bordered table-striped top20 bottom20 w-100'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='links' />
                                            " . htmlsafechars($CURUSER['username'], ENT_QUOTES) . "'s Menu
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/mytorrents.php'>{$lang['usercp_edit_torrents']}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/friends.php'>{$lang['usercp_edit_friends']}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/users.php'>{$lang['usercp_search']}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/invite.php'>Invites</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/tenpercent.php'>Lifesaver</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class='table-wrapper w-25'>
                            <table class='table table-bordered table-striped top20 bottom20 w-100'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>" . htmlsafechars($CURUSER['username'], ENT_QUOTES) . "'s Entertainment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><a href='{$site_config['baseurl']}/topmoods.php'>Top Member Mood's</a></td>
                                    </tr>";
    if ($CURUSER['class'] >= UC_POWER_USER) {
        $HTMLOUT .= "
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/games.php'>{$site_config['site_name']} Games</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/blackjack.php'>{$site_config['site_name']} Blackjack</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/casino.php'>{$site_config['site_name']} Casino</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/arcade.php'>{$site_config['site_name']} Arcade</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href='{$site_config['baseurl']}/lottery.php'>{$site_config['site_name']} Lottery</a>
                                        </td>
                                    </tr>
                                </tbody>";
    }
} elseif ($action == 'security') {
    $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='security' />
                                            Security Options
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";
    if (get_scheme() === 'https') {
        $HTMLOUT .= tr('SSL options', "
                                        <select name='ssluse' class='w-100'>
                                            <option value='1' " . ($CURUSER['ssluse'] == 1 ? "selected" : '') . ">SSL for Nothing</option>
                                            <option value='2' " . ($CURUSER['ssluse'] == 2 ? "selected" : '') . ">SSL only for site browsing</option>
                                            <option value='3' " . ($CURUSER['ssluse'] == 3 ? "selected" : '') . ">SSL for site browsing and downloading (recommended)</option>
                                        </select>
                                        <div class='top10'>
                                            <span class='size_2'>SSL (Secure Socket Layer) is a network layer security protocol which is reponsible for ensuring security of data</span>
                                        </div>", 1);
    }
    if (get_parked() == '1') {
        $HTMLOUT .= tr($lang['usercp_acc_parked'], "
                                        <input type='radio' name='parked'" . ($CURUSER['parked'] == 'yes' ? " checked" : '') . " value='yes' /> Yes
                                        <input type='radio' name='parked'" . ($CURUSER['parked'] == 'no' ? " checked" : '') . " value='no' /> No
                                        <div class='size_2'>
                                            <p>{$lang['usercp_acc_parked_message']}<br>
                                            {$lang['usercp_acc_parked_message1']}</p>
                                        </div>", 1);
    }
    if (get_anonymous() != '0') {
        $HTMLOUT .= tr($lang['usercp_anonymous'], "
                                        <input type='checkbox' name='anonymous'" . ($CURUSER['anonymous'] == 'yes' ? " checked" : '') . " /> {$lang['usercp_default_anonymous']}", 1);
    }
    $HTMLOUT .= tr('Hide current seed and leech', "
                                        <input type='radio' name='hidecur'" . ($CURUSER['hidecur'] == 'yes' ? " checked" : '') . " value='yes' /> Yes
                                        <input type='radio' name='hidecur'" . ($CURUSER['hidecur'] == 'no' ? " checked" : '') . " value='no' /> No", 1);
    if ($CURUSER['class'] > UC_USER) {
        $HTMLOUT .= tr('My Paranoia', "
                                        <select name='paranoia'>
                                            <option value='0'" . ($CURUSER['paranoia'] == 0 ? " selected" : '') . ">I'm totally relaxed</option>
                                            <option value='1'" . ($CURUSER['paranoia'] == 1 ? " selected" : '') . ">I feel sort of relaxed</option>
                                            <option value='2'" . ($CURUSER['paranoia'] == 2 ? " selected" : '') . ">I'm paranoid</option>
                                            <option value='3'" . ($CURUSER['paranoia'] == 3 ? " selected" : '') . ">I wear a tin-foil hat</option>
                                        </select>
                                        <div class='mw-100'>
                                            <div class='flipper has-text-primary top10'>
                                                <a id='paranoia_open'>Paranoia Levels explained! <i class='fa icon-down-open size_3' aria-hidden='true'></i></a>
                                            </div>
                                            <div id='paranoia_info' class='is_hidden wrap padding20'>
                                                <p>
                                                    I'm totally relaxed<br>
                                                    Default setting, nothing is hidden except your IP, passkey, email. the same as any tracker.
                                                </p>
                                                <p>
                                                    I'm a little paranoid<br>
                                                    All info about torrents are hidden from other members except your share ratio, join date, last seen and PM button if you accept PMs. Your comments are not hidden, and though your actual stats (up and down) are hidden on the forums, your actual ratio isn't, also, you will appear on snatched lists.
                                                </p>
                                                <p>
                                                    I'm paranoid<br>
                                                    Same as 'a little paranoid' except your name will not appear on snatched lists, your ratio and stats as well as anything to do with actual filesharing will not be visible to other members. You will appear as 'anonymous' on torrent comments, snatched lists et al. The member ratings and comments on your details page will also be disabled.
                                                </p>
                                                <p>
                                                    I wear a tin-foil hat<br>
                                                    No information will be available to other members on your details page. Your comments and thank you(s) on torrents will be anonymous, your userdetails page will not be accessible, your stats will not appear at all, including your share ratio.
                                                </p>
                                                <p>
                                                    Please remember!<br>
                                                    All of the above will not apply to staff... staff see all and know all... <br>Even at the highest level of paranoia, you can still be reported (though they won't know who they are reporting) and you are not immune to our auto scripts...
                                                </p>
                                            </div>
                                        </div>", 1);
    }
    $HTMLOUT .= tr($lang['usercp_email'], "
                                        <input type='text' name='email' class='w-100' value='" . htmlsafechars($CURUSER['email']) . "' />
                                        <p class='margin20'>{$lang['usercp_email_pass']}</p>
                                        <input type='password' name='chmailpass' class='w-100' />", 1);
    $HTMLOUT .= "
                                <tr>
                                    <td colspan='2'>{$lang['usercp_note']}</td>
                                </tr>";
    $HTMLOUT .= tr('Show Email', '
                                        <input type="radio" name="show_email" ' . ($CURUSER['show_email'] == 'yes' ? ' checked' : '') . ' value="yes" /> Yes
                                        <input type="radio" name="show_email" ' . ($CURUSER['show_email'] == 'no' ? ' checked' : '') . ' value="no" /> No
                                        <p>Do you wish to have your email address visible on the forums?</p>', 1);
    $HTMLOUT .= tr($lang['usercp_chpass'], "
                                        <input type='password' name='chpassword' class='w-100' />", 1);
    $HTMLOUT .= tr($lang['usercp_pass_again'], "
                                        <input type='password' name='passagain' class='w-100' />", 1);
    $secretqs = "<option value='0'>{$lang['usercp_none_select']}</option>";
    $questions = [
        [
            'id'       => '1',
            'question' => "{$lang['usercp_q1']}",
        ],
        [
            'id'       => '2',
            'question' => "{$lang['usercp_q2']}",
        ],
        [
            'id'       => '3',
            'question' => "{$lang['usercp_q3']}",
        ],
        [
            'id'       => '4',
            'question' => "{$lang['usercp_q4']}",
        ],
        [
            'id'       => '5',
            'question' => "{$lang['usercp_q5']}",
        ],
        [
            'id'       => '6',
            'question' => "{$lang['usercp_q6']}",
        ],
    ];
    foreach ($questions as $sctq) {
        $secretqs .= "
                                            <option value='" . $sctq['id'] . "'" . ($CURUSER['passhint'] == $sctq['id'] ? " selected" : '') . '>' . $sctq['question'] . "</option>";
    }
    $HTMLOUT .= tr($lang['usercp_question'], "
                                        <select name='changeq'>
                                            $secretqs
                                        </select>", 1);
    $HTMLOUT .= tr($lang['usercp_sec_answer'], "
                                        <input type='text' name='secretanswer' class='w-100' />", 1);
    $HTMLOUT .= "
                                    <tr>
                                        <td colspan='2'>
                                            <div class='has-text-centered'>
                                                <input class='button is-small' type='submit' value='Submit changes!' />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>";
} elseif ($action == 'torrents') {
    $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='torrents' />
                                            Torrent Options
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";
    $categories = '';
    $r = sql_query('SELECT id, image, name FROM categories ORDER BY name') or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($r) > 0) {
        $categories .= "
                                            <div id='cat-container' class='level-center'>";
        while ($a = mysqli_fetch_assoc($r)) {
            $categories .= "
                                                <span class='margin20 bordered tooltipper' title='" . htmlsafechars($a['name']) . "'>
                                                    <input name='cat{$a['id']}' type='checkbox' " . (strpos($CURUSER['notifs'], "[cat{$a['id']}]") !== false ? " checked" : '') . " value='yes' />
                                                    <span class='cat-image left10'>
                                                        <a href='{$site_config['baseurl']}/browse.php?c" . (int)$a['id'] . "'>
                                                            <img class='radius-sm' src='{$site_config['pic_baseurl']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($a['image']) . "'alt='" . htmlsafechars($a['name']) . "' />
                                                        </a>
                                                    </span>
                                                </span>";
        }
        $categories .= "
                                            </div>";
    }

    $HTMLOUT .= tr($lang['usercp_email_notif'], "
                                            <input type='checkbox' name='emailnotif'" . (strpos($CURUSER['notifs'], '[email]') !== false ? " checked" : '') . " value='yes' /> {$lang['usercp_notify_torrent']}\n", 1);
    $HTMLOUT .= tr($lang['usercp_browse'], $categories, 1);
    $HTMLOUT .= tr($lang['usercp_clearnewtagmanually'], "
                                            <input type='checkbox' name='clear_new_tag_manually' value='yes'" . (($CURUSER['opt1'] & user_options::CLEAR_NEW_TAG_MANUALLY) ? " checked" : '') . " /> {$lang['usercp_default_clearnewtagmanually']}", 1);
    $HTMLOUT .= tr($lang['usercp_scloud'], "
                                            <input type='checkbox' name='viewscloud' value='yes'" . (($CURUSER['opt1'] & user_options::VIEWSCLOUD) ? " checked" : '') . " /> {$lang['usercp_scloud1']}", 1);
    $HTMLOUT .= tr($lang['usercp_split'], "
                                            <input type='checkbox' name='split'" . (($CURUSER['opt2'] & user_options_2::SPLIT) ? " checked" : '') . " value='yes' />(Split torrents uploaded by days)", 1);
    $HTMLOUT .= tr($lang['usercp_icons'], "
                                            <input type='checkbox' name='browse_icons'" . (($CURUSER['opt2'] & user_options_2::BROWSE_ICONS) ? " checked" : '') . " value='yes' />(View categories as icons)", 1);
    $HTMLOUT .= tr($lang['usercp_cats_sets'], "
                                            <select name='categorie_icon'>
                                                <option value='1'" . (get_category_icons() == 1 ? " selected" : '') . ">Default</option>
                                                <option value='2'" . (get_category_icons() == 2 ? " selected" : '') . ">Future</option>
                                                <option value='3'" . (get_category_icons() == 3 ? " selected" : '') . ">Alt</option>
                                                <option value='4'" . (get_category_icons() == 4 ? " selected" : '') . '>Pirate</option>
                                            </select>', get_category_icons());
    $HTMLOUT .= tr($lang['usercp_tor_perpage'], "
                                            <input type='text' class='w-25' name='torrentsperpage' value='{$CURUSER['torrentsperpage']}' />
                                            <div>{$lang['usercp_default']}</div>", 1);
    $HTMLOUT .= "
                                    <tr>
                                        <td colspan='2'>
                                            <div class='has-text-centered'>
                                                <input class='button is-small' type='submit' value='Submit changes!' />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>";
} elseif ($action == 'personal') {
    $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='personal' />
                                            Personal Options
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";
    if ($CURUSER['class'] >= UC_VIP) {
        $HTMLOUT .= tr($lang['usercp_title'], "
                                                <input type='text' class='w-100' value='" . htmlsafechars($CURUSER['title']) . "' name='title' />", 1);
    }
    $CURUSER['archive'] = unserialize($CURUSER['archive']);
    $HTMLOUT .= "
                                        <tr>
                                            <td class='rowhead'>Online status</td>
                                            <td>
                                                <div>
                                                    <span class='size_4'>Status update</span>
                                                </div>";
    if (isset($CURUSER['last_status']) && $CURUSER['last_status'] != '') {
        $HTMLOUT .= "
                                                <div id='current_holder'>
                                                    <span class='size_3'>Current status</span>
                                                    <h2 id='current_status' title='Click to edit' onclick='status_pedit()'>" . format_urls($CURUSER['last_status']) . "</h2>
                                                </div>";
    }
    $HTMLOUT .= "
                                                <span class='size_3'>Update status</span>
                                                <textarea name='status' id='status' onkeyup='status_count()' class='w-100' rows='4'></textarea>
                                                <div>
                                                    <span>NO bbcode or html allowed</span>
                                                    <div id='status_count'>140</div>
                                                <div></div></div>";
    if (!empty($CURUSER['archive']) && count($CURUSER['archive'])) {
        $HTMLOUT .= "
                                                <div>
                                                <div>
                                                    <span class='size_3'>Status archive</span>
                                                </div>
                                                <div id='status_archive_click' class='is_hidden' onclick='status_slide()'>+</div>
                                                <div></div>
                                                <div id='status_archive'>";
        if (is_array($CURUSER['archive'])) {
            foreach (array_reverse($CURUSER['archive'], true) as $a_id => $sa) {
                $HTMLOUT .= '
                                                    <div id="status_' . $a_id . '">
                                                        <div>' . htmlsafechars($sa['status']) . '
                                                            <small>added ' . get_date($sa['date'], '', 0, 1) . '</small>
                                                        </div>
                                                        <div>
                                                            <span onclick="status_delete(' . $a_id . ')"></span>
                                                        </div>
                                                        <div></div>
                                                    </div>';
            }
        }
        $HTMLOUT .= '
                                                </div>
                                            </div>';
    }
    $HTMLOUT .= '                       </td>
                                    </tr>';
    $HTMLOUT .= tr($lang['usercp_top_perpage'], "
                                            <input type='text' class='w-100' name='topicsperpage' value='$CURUSER[topicsperpage]' /> {$lang['usercp_default']}", 1);
    $HTMLOUT .= tr($lang['usercp_post_perpage'], "
                                            <input type='text' class='w-100' name='postsperpage' value='$CURUSER[postsperpage]' /> {$lang['usercp_default']}", 1);
    $HTMLOUT .= tr('Forum Sort Order', "
                                            <input type='radio' name='forum_sort' " . ($CURUSER['forum_sort'] == 'ASC' ? " checked" : '') . " value='ASC' />At Bottom <input type='radio' name='forum_sort' " . ($CURUSER['forum_sort'] != 'ASC' ? " checked" : '') . " value='DESC' />At Top<br>What order you want the posts to be listed in.", 1);
    $HTMLOUT .= tr($lang['usercp_stylesheet'], "
                                            <select name='stylesheet'>
                                                $stylesheets
                                            </select>", 1);
    $HTMLOUT .= tr($lang['usercp_ajaxchat_height'], "
                                            <input type='text' class='w-100' name='ajaxchat_height' value='$CURUSER[ajaxchat_height]' /> {$lang['usercp_default']}", 1);
    $HTMLOUT .= tr($lang['usercp_gender'], "
                                            <div class='level-center'>
                                                <span>
                                                    <input type='radio' name='gender'" . ($CURUSER['gender'] == 'Male' ? " checked" : '') . " value='Male' /> {$lang['usercp_male']}
                                                </span>
                                                <span>
                                                    <input type='radio' name='gender'" . ($CURUSER['gender'] == 'Female' ? " checked" : '') . " value='Female' /> {$lang['usercp_female']}
                                                </span>
                                                <span>
                                                    <input type='radio' name='gender'" . ($CURUSER['gender'] == 'N/A' ? " checked" : '') . " value='N/A' /> {$lang['usercp_na']}
                                                </span>
                                            </div>", 1);

    $day = $month = $year = '';
    $birthday = $CURUSER['birthday'];
    $birthday = date('Y-m-d', strtotime($birthday));
    list($year1, $month1, $day1) = explode('-', $birthday);
    if ($CURUSER['birthday'] != '0000-00-00') {
        $year .= "
                                            <select name='year' class='w-25 bottom10'>
                                                <option value='0000'>--</option>";
        $i = '1920';
        while ($i <= (date('Y', TIME_NOW) - 13)) {
            $year .= "
                                                <option value='{$i}'>{$i}</option>";
            ++$i;
        }
        $year .= "
                                            </select>";
        $birthmonths = [
            '01' => 'January',
            '02' => 'Febuary',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
        $month = "
                                            <select name='month' class='w-25 bottom10'>
                                                <option value='00'>--</option>";
        foreach ($birthmonths as $month_no => $show_month) {
            $month .= "
                                                <option value='{$month_no}'>{$show_month}</option>";
        }
        $month .= "
                                            </select>";
        $day .= "
                                            <select name='day' class='w-25 bottom10'>
                                                <option value='00'>--</option>";
        $i = 1;
        while ($i <= 31) {
            if ($i < 10) {
                $day .= "
                                                <option value='0{$i}'>0{$i}</option>";
            } else {
                $day .= "
                                                <option value='{$i}''>{$i}</option>";
            }
            ++$i;
        }
        $day .= "
                                            </select>";
        $HTMLOUT .= tr('Birthday', "
                                            <div class='level-center'>
                                                {$year}{$month}{$day}
                                            </div>", 1);
    }
    $HTMLOUT .= "
                    <tr>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input class='button is-small' type='submit' value='Submit changes!' />
                            </div>
                        </td>
                    </tr>
                </tbody>";
} else {
    if ($action == 'default') {
        $HTMLOUT .= "
                        <div class='table-wrapper w-75'>
                            <table class='table table-bordered table-striped top20 bottom20'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>
                                            <input type='hidden' name='action' value='default' />
                                            PM options
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>";
        $HTMLOUT .= tr($lang['usercp_email_notif'], "
                                            <input type='checkbox' name='pmnotif'" . (strpos($CURUSER['notifs'], '[pm]') !== false ? " checked" : '') . " value='yes' /> {$lang['usercp_notify_pm']}", 1);
        $HTMLOUT .= tr($lang['usercp_accept_pm'], "
                                            <div class='level-center'>
                                                <span>
                                                    <input type='radio' name='acceptpms'" . ($CURUSER['acceptpms'] == 'yes' ? " checked" : '') . " value='yes' /> {$lang['usercp_except_blocks']}
                                                </span>
                                                <span>
                                                    <input type='radio' name='acceptpms'" . ($CURUSER['acceptpms'] == 'friends' ? " checked" : '') . " value='friends' /> {$lang['usercp_only_friends']}
                                                </span>
                                                <span>
                                                    <input type='radio' name='acceptpms'" . ($CURUSER['acceptpms'] == 'no' ? " checked" : '') . " value='no' /> {$lang['usercp_only_staff']}
                                                </span>
                                            </div>", 1);
        $HTMLOUT .= tr($lang['usercp_delete_pms'], "
                                            <input type='checkbox' name='deletepms'" . ($CURUSER['deletepms'] == 'yes' ? " checked" : '') . " /> {$lang['usercp_default_delete']}", 1);
        $HTMLOUT .= tr($lang['usercp_save_pms'], "
                                            <input type='checkbox' name='savepms'" . ($CURUSER['savepms'] == 'yes' ? " checked" : '') . " /> {$lang['usercp_default_save']}", 1);
        $HTMLOUT .= tr('Forum Subscribe PM', "
                                            <input type='radio' name='subscription_pm' " . ($CURUSER['subscription_pm'] == 'yes' ? " checked" : '') . " value='yes' /> Yes
                                            <input type='radio' name='subscription_pm' " . ($CURUSER['subscription_pm'] == 'no' ? " checked" : '') . " value='no' /> No<br>When someone posts in a subscribed thread, you will be PMed.", 1);
        $HTMLOUT .= tr('Torrent deletion PM', "
                                            <input type='radio' name='pm_on_delete' " . ($CURUSER['pm_on_delete'] == 'yes' ? " checked" : '') . " value='yes' /> Yes
                                            <input type='radio' name='pm_on_delete' " . ($CURUSER['pm_on_delete'] == 'no' ? " checked" : '') . " value='no' /> No<br>When any of your uploaded torrents are deleted, you will be PMed.", 1);
        $HTMLOUT .= tr('Torrent comment PM', "
                                            <input type='radio' name='commentpm' " . ($CURUSER['commentpm'] == 'yes' ? " checked" : '') . " value='yes' /> Yes
                                            <input type='radio' name='commentpm' " . ($CURUSER['commentpm'] == 'no' ? " checked" : '') . " value='no' /> No<br>When any of your uploaded torrents are commented on, you will be PMed.", 1);
        $HTMLOUT .= "
                                    <tr>
                                        <td colspan='2'>
                                            <div class='has-text-centered'>
                                                <input class='button is-small' type='submit' value='Submit changes!' />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>";
    }
}
$HTMLOUT .= '
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>';

echo stdhead(htmlsafechars($CURUSER['username'], ENT_QUOTES) . "{$lang['usercp_stdhead']} ") . wrapper($HTMLOUT) . stdfoot();
