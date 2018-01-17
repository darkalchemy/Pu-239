<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

$lang = array_merge($lang, load_language('ad_repsettings'));
$rep_set_cache = './cache/rep_settings_cache.php';
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    unset($_POST['submit']);
    //print_r($_POST);
    rep_cache();
    exit;
}

function rep_cache()
{
    $rep_out = '<' . "?php\n\n\$GVARS = array(\n";
    foreach ($_POST as $k => $v) {
        $rep_out .= ($k == 'rep_undefined') ? "\t'{$k}' => '" . htmlsafechars($v, ENT_QUOTES) . "',\n" : "\t'{$k}' => " . intval($v) . ",\n";
    }
    $rep_out .= "\t'g_rep_negative' => true,\n";
    $rep_out .= "\t'g_rep_seeown' => true,\n";
    $rep_out .= "\t'g_rep_use' => \$CURUSER['class'] > UC_USER ? true : false\n";
    $rep_out .= "\n);";
    file_put_contents(CACHE_DIR . 'rep_settings_cache.php', $rep_out);
    redirect('staffpanel.php?tool=reputation_settings', $lang['repset_updated'], 3);
}

/**
 * @return array
 */
function get_cache_array()
{
    global $lang;

    return [
        'rep_is_online'  => 1,
        'rep_adminpower' => 5,
        'rep_minpost'    => 50,
        'rep_default'    => 10,
        'rep_userrates'  => 5,
        'rep_rdpower'    => 365,
        'rep_pcpower'    => 1000,
        'rep_kppower'    => 100,
        'rep_minrep'     => 10,
        'rep_minpost'    => 50,
        'rep_maxperday'  => 10,
        'rep_repeat'     => 20,
        'rep_undefined'  => $lang['repset_scale'],
        /*'g_rep_negative' => true,
        'g_rep_seeown' => true,
        'g_rep_use' => $CURUSER['class'] > UC_USER ? true : false*/
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
$HTMLOUT = '<div>
                <table width="100%" border="0">
                   <tr>
                    <td style="font-size: 12px; vertical-align: middle; font-weight: bold; color: rgb(0, 0, 0);">' . $lang['repset_settings'] . '</td></tr>

                    <tr><td>' . $lang['repset_section'] . '</td>
                                 </tr>
                                 </table>
</div>
<br>
<div style="border: 1px solid rgb(0, 0, 0); padding: 5px;">

    <form action="staffpanel.php?tool=reputation_settings" name="repoptions" method="post">

                <div>' . $lang['repset_onoff'] . '</div>
                    <div style="padding: 5px; background-color: rgb(30,30, 30);">
                            <div style="border: 1px solid rgb(0, 0, 0);">

                            <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_enable'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgray;">' . $lang['repset_setop'] . '</div></td>

                             <td width="23%"><div style="width: auto;"><#rep_is_online#></div></td>
                             </tr>
                  </table>
                  </div></div>

                  <div>' . $lang['repset_defaultlvl'] . '</div>
                 <div style="padding: 5px; background-color: rgb(30, 30, 30);">
                        <div>
                            <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_defaultrep'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_msg'] . '</div></td>
                             <td width="20%"><div style="width: auto;"><input name="rep_default" value="<#rep_default#>" size="30" type="text"></div></td>
                             </tr>
                  </table>

                  <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_defaultphrase'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_msg1'] . '</div></td>
                             <td width="20%"><div style="width: auto;"><input name="rep_undefined" value="<#rep_undefined#>" size="30" type="text"></div></td>
                             </tr>
                  </table>

                    <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_display'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_cont'] . '</div></td>
                             <td width="20%"><div style="width: auto;"><input name="rep_userrates" value="<#rep_userrates#>" size="30" type="text"></div></td>
                             </tr>

                  </table>
                  </div></div>

                  <div>' . $lang['repset_power'] . '</div>
                 <div style="padding: 5px; background-color: rgb(30, 30, 30);">
                            <div style="border: 1px solid rgb(0, 0, 0);">

                            <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_admin'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_adminmsg'] . '<br>' . $lang['repset_adminmsg1'] . '</div></td>
                             <td class="tablerow2" width="20%"><div style="width: auto;"><input name="rep_adminpower" value="<#rep_adminpower#>" size="30" type="text"></div></td>

                             </tr>
                  </table>

                  <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_regdate'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_regdatemsg'] . '</div></td>

                             <td width="20%"><div style="width: auto;"><input name="rep_rdpower" value="<#rep_rdpower#>" size="30" type="text">

</div></td>
                             </tr>

                  </table>

                  <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_post'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_postmsg'] . '</div></td>

                             <td width="20%"><div style="width: auto;"><input name="rep_pcpower" value="<#rep_pcpower#>" size="30" type="text"></div></td>
                             </tr>
                  </table>

                  <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_point'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_pointmsg'] . '</div></td>

                             <td width="20%"><div style="width: auto;"><input name="rep_kppower" value="<#rep_kppower#>" size="30" type="text"></div></td>
                             </tr>
                  </table>
                  </div></div>

                  <div>' . $lang['repset_userset'] . '</div>
                  <div style="padding: 5px; background-color: rgb(30, 30, 30);">
                        <div>

                            <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_minpost'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_minpostmsg'] . '</div></td>

                             <td width="20%"><div style="width: auto;"><input name="rep_minpost" value="<#rep_minpost#>" size="30" type="text"></div></td>
                             </tr>
                  </table>

                  <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_minrep'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_minrepmsg'] . '</div></td>

                             <td width="20%"><div style="width: auto;"><input name="rep_minrep" value="<#rep_minrep#>" size="30" type="text"></div></td>
                             </tr>
                  </table>

                  <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_daily'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_dailymsg'] . '</div></td>

                             <td width="20%"><div style="width: auto;"><input name="rep_maxperday" value="<#rep_maxperday#>" size="30" type="text"></div></td>
                             </tr>
                  </table>

                  <table width="100%" border="0">
                             <tr>
                             <td width="70%"><b>' . $lang['repset_userspread'] . '</b><div><hr style="color:#A83838;" size="1" /></div><div style="color: lightgrey;">' . $lang['repset_userspreadmsg'] . '</div></td>

                             <td width="20%"><div style="width: auto;"><input name="rep_repeat" value="<#rep_repeat#>" size="30" type="text"></div></td>
                             </tr>
                  </table>
                  </div></div>

<input type="submit" name="submit" value="' . $lang['repset_submit'] . '" class="button is-small" tabindex="2" accesskey="s" />
</form>
</div>';
$HTMLOUT = preg_replace_callback('|<#(.*?)#>|', 'template_out', $HTMLOUT);
echo stdhead($lang['repset_stdhead']) . $HTMLOUT . stdfoot();
/**
 * @param $matches
 *
 * @return string
 */
function template_out($matches)
{
    global $GVARS, $lang;
    if ($matches[1] == 'rep_is_online') {
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

    $html = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
<title>{$lang['repset_adminredir']}</title>
<link rel='stylesheet' href='" . get_file_name('css') . "' />
</head>
<body>
    <div>
        <div>{$lang['repset_redirecting']}</div>
            <div style='padding: 8px;'>
                <div style='font-size: 12px;'>$text
                <br>
                <br>
                <a href='{$site_config['baseurl']}/{$url}'>{$lang['repset_clickredirect']}</a>
            </div>
        </div>
    </div>
</body>
</html>";
    echo $html;
    exit;
}
