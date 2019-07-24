<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_repsettings'));
global $site_config;

$rep_set_cache = CACHE_DIR . 'rep_settings_cache.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    unset($_POST['submit']);
    //print_r($_POST);
    rep_cache();
    exit;
}

function rep_cache()
{
    global $site_config, $lang;

    $rep_out = '<' . "?php\n\ndeclare(strict_types=1);\n\nglobal \$CURUSER;\n\n\$GVARS=array(\n";
    foreach ($_POST as $k => $v) {
        $rep_out .= ($k === 'rep_undefined') ? "\t'{$k}' => '" . htmlsafechars($v) . "',\n" : "\t'{$k}' => " . (int) $v . ",\n";
    }
    $rep_out .= "\t'g_rep_negative' => true,\n";
    $rep_out .= "\t'g_rep_seeown' => true,\n";
    $rep_out .= "\t'g_rep_use' => \$CURUSER['class']>UC_MIN ? true : false\n";
    $rep_out .= "\n);";
    file_put_contents(CACHE_DIR . 'rep_settings_cache.php', $rep_out);
    redirect($site_config['paths']['baseurl'] . '/staffpanel.php?tool=reputation_settings', $lang['repset_updated'], 3);
}

/**
 * @return array
 */
function get_cache_array()
{
    global $lang;

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
        'rep_undefined' => $lang['repset_scale'],
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
    <h1 class='has-text-centered'>{$lang['repset_settings']}</h1>
    <p class='has-text-centered'>{$lang['repset_section']}</p>
    <form action='{$_SERVER['PHP_SELF']}?tool=reputation_settings' name='repoptions' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
    <h2 class='has-text-centered'>{$lang['repset_onoff']}</h2>";
$body = '
            <tr>
                <td>
                    <b>' . $lang['repset_enable'] . '</b>
                    <div style="color: lightgray;">' . $lang['repset_setop'] . '</div>
                </td>
                <td>
                    <div style="width: auto;"><#rep_is_online#></div>
                </td>
            </tr>
            <tr><td colspan="2" class="has-text-centered"><div class="padding20 size_6">' . $lang['repset_defaultlvl'] . '</div></td></tr>
            <tr>
                <td>
                    <b>' . $lang['repset_defaultrep'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_msg'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_default" value="<#rep_default#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . $lang['repset_defaultphrase'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_msg1'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_undefined" value="<#rep_undefined#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td><b>' . $lang['repset_display'] . ' </b><div style="color: lightgrey;">' . $lang['repset_cont'] . ' </div></td>
                <td><div style="width: auto;"><input name="rep_userrates" value="<#rep_userrates#>" size="30" type="text"></div></td>
            </tr>
            <tr><td colspan="2" class="has-text-centered"><div class="padding20 size_6">' . $lang['repset_power'] . '</div></td></tr>
            <tr>
                <td>
                    <b>' . $lang['repset_admin'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_adminmsg'] . ' < br>' . $lang['repset_adminmsg1'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_adminpower" value="<#rep_adminpower#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . $lang['repset_regdate'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_regdatemsg'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_rdpower" value="<#rep_rdpower#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . $lang['repset_post'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_postmsg'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_pcpower" value="<#rep_pcpower#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . $lang['repset_point'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_pointmsg'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_kppower" value="<#rep_kppower#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr><td colspan="2" class="has-text-centered"><div class="padding20 size_6">' . $lang['repset_userset'] . '</div></td></tr>
            <tr>
                <td>
                    <b>' . $lang['repset_minpost'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_minpostmsg'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_minpost" value="<#rep_minpost#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . $lang['repset_minrep'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_minrepmsg'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_minrep" value="<#rep_minrep#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . $lang['repset_daily'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_dailymsg'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_maxperday" value="<#rep_maxperday#>" size="30" type="text"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <b>' . $lang['repset_userspread'] . ' </b>
                    <div style="color: lightgrey;">' . $lang['repset_userspreadmsg'] . ' </div>
                </td>
                <td>
                    <div style="width: auto;"><input name="rep_repeat" value="<#rep_repeat#>" size="30" type="text"></div>
                </td>
            </tr>';
$HTMLOUT .= main_table($body) . '
        <div class="has-text-centered margin20">
            <input type="submit" name="submit" value="' . $lang['repset_submit'] . '" class="button is-small" tabindex="2" accesskey="s">
        </div>
        </form>';

$HTMLOUT = preg_replace_callback(' |<#(.*?)#>|', 'template_out', $HTMLOUT);
echo stdhead($lang['repset_stdhead']) . wrapper($HTMLOUT) . stdfoot();

/**
 * @param array $matches
 *
 * @return string
 */
function template_out(array $matches)
{
    global $lang, $GVARS;

    if ($matches[1] === 'rep_is_online') {
        return '' . $lang['repset_yes'] . '<input name="rep_is_online" value="1" ' . ($GVARS['rep_is_online'] == 1 ? 'checked' : '') . ' type="radio">&#160;&#160;&#160;<input name="rep_is_online" value="0" ' . ($GVARS['rep_is_online'] == 1 ? '' : 'checked') . ' type="radio">' . $lang['repset_no'] . '';
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
    global $site_config, $lang;

    $html = doc_head() . "
<meta property='og:title' content='{$lang['repset_adminredir']}'>
<title>{$lang['repset_adminredir']}</title>
<link rel='stylesheet' href='" . get_file_name('css') . "'>
</head>
<body>
    <div>
        <div>{$lang['repset_redirecting']}</div>
            <div style='padding: 8px;'>
                <div style='font-size: 12px;'>$text
                <br>
                <br>
                <a href='{$site_config['paths']['baseurl']}/{$url}'>{$lang['repset_clickredirect']}</a>
            </div>
        </div>
    </div>
</body>
</html>";
    echo $html;
    exit;
}
