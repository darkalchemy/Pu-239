<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config;

$rep_set_cache = CACHE_DIR . 'rep_settings_cache.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_POST['submit']);
    //print_r($_POST);
    rep_cache();
    exit;
}

function rep_cache()
{
    global $site_config;

    $rep_out = '<' . "?php\n\ndeclare(strict_types=1);\n\nglobal \$CURUSER;\n\n\$GVARS=array(\n";
    foreach ($_POST as $k => $v) {
        $rep_out .= ($k === 'rep_undefined') ? "\t'{$k}' => '" . htmlsafechars($v) . "',\n" : "\t'{$k}' => " . (int) $v . ",\n";
    }
    $rep_out .= "\t'g_rep_negative' => true,\n";
    $rep_out .= "\t'g_rep_seeown' => true,\n";
    $rep_out .= "\t'g_rep_use' => \$CURUSER['class']>UC_MIN ? true : false\n";
    $rep_out .= "\n);";
    file_put_contents(CACHE_DIR . 'rep_settings_cache.php', $rep_out);
    redirect($site_config['paths']['baseurl'] . '/staffpanel.php?tool=reputation_settings', _('Reputation Settings Have Been Updated!'), 3);
}

/**
 * @return array
 */
function get_cache_array()
{
    return [
        'rep_is_online' => 1,
        'rep_adminpower' => 5,
        'rep_minpost' => 50,
        'rep_default' => 10,
        'rep_userrates' => 5,
        'rep_rdpower' => 365,
        'rep_pcpower' => 1000,
        'rep_kppower' => 100,
        'rep_minrep' => 10,
        'rep_maxperday' => 10,
        'rep_repeat' => 20,
        'rep_undefined' => _('is off the scale'),
        /*'g_rep_negative' => true,
        'g_rep_seeown' => true,
        'g_rep_use' => $CURUSER['class']>UC_MIN ? true : false*/
    ];
}

if (!file_exists($rep_set_cache)) {
    $GVARS = get_cache_array();
} else {
    require_once $rep_set_cache;
    if (!is_array($GVARS) || (count($GVARS) < 15)) {
        $GVARS = get_cache_array();
    }
}

$HTMLOUT = "
    <h1 class='has-text-centered'>" . _('Reputation System Settings') . "</h1>
    <p class='has-text-centered'>" . _('This section allows you to configure the User Reputation system.') . "</p>
    <form action='{$_SERVER['PHP_SELF']}?tool=reputation_settings' name='repoptions' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
    <h2 class='has-text-centered'>" . _('Reputation On/Off') . '</h2>';
$body = '
            <tr>
                <td>
                    <b>' . _('Enable User Reputation system?') . '</b>
                    <div style="color: lightgray;">' . _("Set this option to 'Yes' if you want to enable the User Reputation system.") . '</div>
                </td>
                <td>
                    <div style="width: auto;"><#rep_is_online#></div>
                </td>
            </tr>
            <tr><td colspan="2" class="has-text-centered"><div class="padding20 size_6">' . _('Default Reputation Level') . '</div></td></tr>
            <tr>
                <td>
                    <b>' . _('Default Reputation') . ' </b>
                    <div style="color: lightgrey;">' . _('What reputation level shall new users receive upon registration? Make sure that you have a reputation level that is at least equal to or less than this value.') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_default" value="<#rep_default#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . _('Default Reputation Phrase') . ' </b>
                    <div style="color: lightgrey;">' . _('If you have any user gain a reputation that exceeds your lowest negative level, then this phrase will be used for them. If you do not wish to use this phrase, make sure you set a negative reputation that is larger than the largest score (negative) that a user on your forum has.') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_undefined" value="<#rep_undefined#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td><b>' . _('Number of Reputation Ratings to Display') . ' </b><div style="color: lightgrey;">' . _("Controls how many ratings to display in the user's profile (userdetails).") . ' </div></td>
                <td><div style="width: auto;"><input name="rep_userrates" value="<#rep_userrates#>" size="30" type="text"></div></td>
            </tr>
            <tr><td colspan="2" class="has-text-centered"><div class="padding20 size_6">' . _('Reputation Powers') . '</div></td></tr>
            <tr>
                <td>
                    <b>' . _("Administrator's Reputation Power") . ' </b>
                    <div style="color: lightgrey;">' . _('How many reputation points does an administrator give or take away with each click?<br>') . ' <br>' . _('Set to 0 to have administrators follow the same rules as everyone else.') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_adminpower" value="<#rep_adminpower#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . _('Register Date Factor') . ' </b>
                    <div style="color: lightgrey;">' . _('For every X number of days, users gain 1 point of reputation-altering power.') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_rdpower" value="<#rep_rdpower#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . _('Post Count Factor') . ' </b>
                    <div style="color: lightgrey;">' . _('For every X number of posts, users gain 1 point of reputation-altering power.') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_pcpower" value="<#rep_pcpower#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . _('Reputation Point Factor') . ' </b>
                    <div style="color: lightgrey;">' . _('For every X points of reputation, users gain 1 point of reputation-altering power.') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_kppower" value="<#rep_kppower#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr><td colspan="2" class="has-text-centered"><div class="padding20 size_6">' . _('User Reputation Settings') . '</div></td></tr>
            <tr>
                <td>
                    <b>' . _('Minimum Post Count') . ' </b>
                    <div style="color: lightgrey;">' . _('How many posts must a user have before his reputation hits count on others?') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_minpost" value="<#rep_minpost#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . _('Minimum Reputation Count') . ' </b>
                    <div style="color: lightgrey;">' . _('How much reputation must a user have before his reputation hits count on others?') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_minrep" value="<#rep_minrep#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . _('Daily Reputation Clicks Limit') . ' </b>
                    <div style="color: lightgrey;">' . _('How many reputation clicks can a user give over each 24 hour period? Administrators are exempt from this limit.') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_maxperday" value="<#rep_maxperday#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . _('Reputation User Spread') . ' </b>
                    <div style="color: lightgrey;">' . _('How many different users must you give reputation to before you can hit the same person again? Administrators are exempt from this limit.') . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_repeat" value="<#rep_repeat#>" size="30" type="text"></div>
                </td>
            </tr>';
$HTMLOUT .= main_table($body) . '
        <div class="has-text-centered margin20">
            <input type="submit" name="submit" value="' . _('Submit') . '" class="button is-small" tabindex="2" accesskey="s">
        </div>
        </form>';

$HTMLOUT = preg_replace_callback(' |<#(.*?)#>|', 'template_out', $HTMLOUT);
$title = _('Reputation Manager');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();

/**
 * @param array $matches
 *
 * @return string
 */
function template_out(array $matches)
{
    global $GVARS;

    if ($matches[1] === 'rep_is_online') {
        return _('Yes') . '<input name="rep_is_online" value="1" ' . ($GVARS['rep_is_online'] == 1 ? 'checked' : '') . ' type="radio">&#160;&#160;&#160;<input name="rep_is_online" value="0" ' . ($GVARS['rep_is_online'] == 1 ? '' : 'checked') . ' type="radio">' . _('No') . '';
    } else {
        return $GVARS[$matches[1]];
    }
}

/**
 * @param     $url
 * @param     $text
 * @param int $time
 */
function redirect($url, $text, $time = 2)
{
    global $site_config;

    $html = doc_head(_('Admin Rep Redirection')) . "
<link rel='stylesheet' href='" . get_file_name('css') . "'>
</head>
<body>
    <div>
        <div>" . _('Redirecting') . "</div>
            <div style='padding: 8px;'>
                <div style='font-size: 12px;'>$text
                <br>
                <br>
                <a href='{$site_config['paths']['baseurl']}/{$url}'>" . _('Click here if not redirected...') . '</a>
            </div>
        </div>
    </div>
</body>
</html>';
    echo $html;
    exit;
}
