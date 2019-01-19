<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
global $site_config, $session, $CURUSER;

if (!$session->get('LoggedIn')) {
    dbconn();
    get_template();
} else {
    check_user_status();
}
$lang = array_merge(load_language('global'), load_language('rules'));

$HTMLOUT = "
        <fieldset id='rules'>
            <legend>
                <img src='{$site_config['pic_baseurl']}info.png' alt='' class='tooltipper right5' title='Guidelines' width='25'>Guidelines
            </legend>";

$main_div = "
                    <div id='accordion'>
                        <p class='accordion-toggle has-text-black round5-top'>
                            {$lang['rules_general_header']}<span class='has-text-blue'>{$lang['rules_general_header_sub']}</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul>
                                <li>{$lang['rules_general_body']}</li>
                                <li>{$lang['rules_general_body1']}</li>
                                <li><a id='warning'></a>{$lang['rules_general_body2']}</li>
                            </ul>
                        </div>
                        <p class='accordion-toggle has-text-black'>
                            {$lang['rules_downloading_header']}<span class='has-text-blue'>{$lang['rules_downloading_header_sub']}</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul>
                                <li>{$lang['rules_downloading_body']}</li>
                                <li>{$lang['rules_downloading_body1']}</li>
                            </ul>
                        </div>
                        <p class='accordion-toggle has-text-black'>
                            {$lang['rules_forum_header']}<span class='has-text-blue'>{$lang['rules_forum_header_sub']}</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul>
                                <li>{$lang['rules_forum_body']}</li>
                                <li>{$lang['rules_forum_body1']}</li>
                                <li>{$lang['rules_forum_body2']}</li>
                                <li>{$lang['rules_forum_body3']}</li>
                                <li>{$lang['rules_forum_body4']}</li>
                                <li>{$lang['rules_forum_body5']}</li>
                                <li>{$lang['rules_forum_body6']}</li>
                                <li>{$lang['rules_forum_body7']}</li>
                                <li>{$lang['rules_forum_body8']}</li>
                                <li>{$lang['rules_forum_body9']}</li>
                                <li>{$lang['rules_forum_body10']}</li>
                                <li>{$lang['rules_forum_body11']}</li>
                            </ul>
                        </div>
                        <p class='accordion-toggle has-text-black" . ($CURUSER['class'] < $site_config['upload_min_class'] ? ' round5-bottom' : '') . "'>
                            {$lang['rules_avatar_header']}<span class='has-text-blue'>{$lang['rules_avatar_header_sub']}</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul>
                                <li>{$lang['rules_avatar_body']}</li>
                                <li>{$lang['rules_avatar_body1']}</li>
                                <li>{$lang['rules_avatar_body2']}</li>
                            </ul>
                        </div>";

if (isset($CURUSER) && $CURUSER['class'] >= $site_config['upload_min_class']) {
    $main_div .= "
                        <p class='accordion-toggle has-text-black" . ($CURUSER['class'] < UC_STAFF ? ' round5-bottom' : '') . "'>
                            {$lang['rules_uploading_header']}<span class='has-text-blue'>{$lang['rules_uploading_header_sub']}</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul>
                                <li>{$lang['rules_uploading_body']}</li>
                                <li>{$lang['rules_uploading_body1']}</li>
                                <li>{$lang['rules_uploading_body2']}</li>
                                <li>{$lang['rules_uploading_body3']}</li>
                                <li>{$lang['rules_uploading_body4']}</li>
                                <li>{$lang['rules_uploading_body5']}</li>
                                <li>{$lang['rules_uploading_body6']}</li>
                                <li>{$lang['rules_uploading_body7']}</li>
                                <li>{$lang['rules_uploading_body8']}</li>
                                <li>{$lang['rules_uploading_body9']}</li>
                            </ul>
                        </div>";
}
if (isset($CURUSER) && $CURUSER['class'] >= UC_STAFF) {
    $main_div .= "
                        <p class='accordion-toggle has-text-black'>
                            {$lang['rules_moderating_header']}<span class='has-text-blue'>{$lang['rules_moderating_header_sub']}</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <table class='table table-bordered table-striped'>
                                <tr>
                                    <td class='w-15'>
                                        <span class='power_user'>{$lang['rules_moderating_pu']}</span>
                                    </td>
                                    <td>{$lang['rules_moderating_body']}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src='{$site_config['pic_baseurl']}star.png' alt='Donor' class='tooltipper' title='Donor'>
                                    </td>
                                    <td>{$lang['rules_moderating_body1']}</td>
                                </tr>
                                <tr>
                                    <td><span class='vip'>{$lang['rules_moderating_vip']}</span></td>
                                    <td>{$lang['rules_moderating_body2']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['rules_moderating_other']}</td>
                                    <td>{$lang['rules_moderating_body3']}</td>
                                </tr>
                                <tr>
                                    <td><span class='uploader'>{$lang['rules_moderating_uploader']}</span></td>
                                    <td>{$lang['rules_moderating_body4']}</td>
                                </tr>
                                <tr>
                                    <td><span class='moderator'>{$lang['rules_moderating_mod']}</span></td>
                                    <td>{$lang['rules_moderating_body5']}</td>
                                </tr>
                            </table>
                        </div>
                        <p class='accordion-toggle has-text-black'>
                            {$lang['rules_mod_rules_header']}<span class='has-text-blue'>{$lang['rules_mod_rules_header_sub']}</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul>
                                <li>{$lang['rules_mod_rules_body']}</li>
                                <li>{$lang['rules_mod_rules_body1']}</li>
                                <li>{$lang['rules_mod_rules_body2']}</li>
                                <li>{$lang['rules_mod_rules_body3']}</li>
                                <li>{$lang['rules_mod_rules_body4']}</li>
                                <li>{$lang['rules_mod_rules_body5']}</li>
                                <li>{$lang['rules_mod_rules_body6']}</li>
                                <li>{$lang['rules_mod_rules_body7']}</li>
                                <li>{$lang['rules_mod_rules_body8']}</li>
                                <li>{$lang['rules_mod_rules_body9']}</li>
                                <li>{$lang['rules_mod_rules_body10']}</li>
                                <li>{$lang['rules_mod_rules_body11']}</li>
                            </ul>
                        </div>
                        <p class='accordion-toggle has-text-black round5-bottom'>
                            {$lang['rules_mod_options_header']}<span class='has-text-blue'>{$lang['rules_mod_options_header_sub']}</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul>
                                <li>{$lang['rules_mod_options_body']}</li>
                                <li>{$lang['rules_mod_options_body1']}</li>
                                <li>{$lang['rules_mod_options_body2']}</li>
                                <li>{$lang['rules_mod_options_body3']}</li>
                                <li>{$lang['rules_mod_options_body4']}</li>
                                <li>{$lang['rules_mod_options_body5']}</li>
                                <li>{$lang['rules_mod_options_body6']}</li>
                                <li>{$lang['rules_mod_options_body7']}</li>
                                <li>{$lang['rules_mod_options_body8']}</li>
                            </ul>
                        </div>";
}

$main_div .= '
                    </div>';
$HTMLOUT .= main_div($main_div) . '
    </fieldset>';

echo stdhead('Rules') . wrapper($HTMLOUT) . stdfoot();
