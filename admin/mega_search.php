<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config;

$msg_to_analyze = (isset($_POST['msg_to_analyze']) ? htmlsafechars($_POST['msg_to_analyze']) : '');
$invite_code = (isset($_POST['invite_code']) ? htmlsafechars($_POST['invite_code']) : '');
$user_names = (isset($_POST['user_names']) ? $_POST['user_names'] : '');
$HTMLOUT = $found = $not_found = $count = $no_matches_for_this_email = $matches_for_email = $no_matches_for_this_ip = $matches_for_ip = '';
$number = 0;
$HTMLOUT .= '
        <div class="has-text-centered top20">
            <h1>' . _('Mega Search') . '</h1>
        </div>';

$HTMLOUT .= main_div('
        <div class="has-text-centered size_4 has-text-primary top10 bottom10">' . _('Analyze text - auto detect IP/Email addresses and search them in the database') . '</div>
        <div class="bg-00 round10 padding20">
            <form method="post" action="' . $_SERVER['PHP_SELF'] . '?tool=mega_search&action=mega_search" accept-charset="utf-8">
                ' . bubble(_('Text:'), _('Use this section to search emails and IPs whithin a block of text. Everything else will be ignored!')) . '
                <textarea name="msg_to_analyze" rows="20" class="w-100">' . $msg_to_analyze . '</textarea>
                <div class="has-text-centered top20">
                    <input type="submit" class="button is-small" value="' . _('Search!') . '">
                </div>
            </form>
        </div>', 'bottom20');
$HTMLOUT .= main_div('
        <div class="bg-00 round10 padding20 ">
            <form method="post" action="' . $_SERVER['PHP_SELF'] . '?tool=mega_search&action=mega_search" accept-charset="utf-8">
                ' . bubble('<b>' . _('Invite Code') . ':</b>', _('To search for an invite code, use this box. It will show you who make the code, and who used it!')) . '
                <input type="text" name="invite_code" class="w-100" value="' . $invite_code . '">
                <div class="has-text-centered top20">
                    <input type="submit" class="button is-small" value="' . _('Search!') . '">
                </div>
            </form>
        </div>', 'bottom20');
$HTMLOUT .= main_div('
        <div class="bg-00 round10 padding20">
            <form method="post" action="' . $_SERVER['PHP_SELF'] . '?tool=mega_search&action=mega_search" accept-charset="utf-8">
                ' . bubble('<b>' . _('User Names') . ':</b>', _('Use this section to search for multiple usernames. The search is not case sensitive, but you must seperate all usernames with a space! Line breaks are ignored as are any non alpha numeric charecters except - and _')) . '
                <textarea name="user_names" rows="4" class="w-100">' . $user_names . '</textarea>
                <div class="has-text-centered top20">
                    <input type="submit" class="button is-small" value="' . _('Search!') . '">
                </div>
            </form>
        </div>');

if (!empty($user_names)) {
    $searched_users = explode(',', preg_replace('/\s+/s', ',', $user_names));
    $body = '';
    $failed = [];
    foreach ($searched_users as $search_users) {
        $users = [];
        $results = $fluent->from('users as u')
                          ->select(null)
                          ->select('u.id')
                          ->select('u.registered')
                          ->select('u.last_access')
                          ->select('u.email')
                          ->select('u.uploaded')
                          ->select('u.downloaded')
                          ->select('u.invitedby')
                          ->select('INET6_NTOA(ip) AS ip')
                          ->leftJoin('ips AS i ON u.id = i.userid')
                          ->where('u.username LIKE ?', "%{$search_users}%");
        foreach ($results as $result) {
            $users[] = $result;
        }
        if (count($users) > 0) {
            foreach ($users as $arr) {
                if ($arr['invitedby'] > 0) {
                    $inviter = format_username((int) $arr['invitedby']);
                } else {
                    $inviter = _('open signups');
                }
                $body .= '
            <tr>
                <td>' . $search_users . '</td>
                <td>' . format_username((int) $arr['id']) . '</td>
                <td>' . htmlsafechars($arr['email']) . '</td>
                <td>
                    <span class="tooltipper is-blue" title="added">' . get_date((int) $arr['registered'], '') . '</span><br>
                    <span class="tooltipper has-text-success" title="last access">' . get_date((int) $arr['last_access'], '') . '</span>
                </td>
                <td>
                    <span class="has-text-success tooltipper" title="' . _('Uploaded') . '">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'up.png" alt="' . _('Up') . '"> 
                        ' . mksize($arr['uploaded']) . '
                    </span>
                    ' . ($site_config['site']['ratio_free'] ? '
                </td>' : '<br>
                    <span class="has-text-danger tooltipper" title="' . _('Downloaded') . '">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'dl.png" alt="' . _('Down') . '">  
                        ' . mksize($arr['downloaded']) . '
                    </span>
                </td>') . '
                <td>' . member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']) . '</td>
                <td>' . (!empty($arr['ip']) ? htmlsafechars($arr['ip']) : '') . '</td>
                <td>' . $inviter . '</td>
            </tr>';
            }
        } else {
            $failed[] = $search_users;
        }
    }
    if (!empty($failed)) {
        $body .= "<tr>
                <td colspan='8'><span class='size_4 has-text-danger text-shadow'>Not Found: </span><span class='is-blue'>" . implode(', ', $failed) . '</span></td>
            </tr>';
    }
    if (empty($body)) {
        $body = "
            <tr>
                <td colspan='8'><span class='size_4 has-text-danger text-shadow'>Not Found: </span><span class='is-blue'>" . implode(', ', $searched_users) . '</span></td>
            </tr>';
    }
    $heading = ' 
                <tr>
                    <th>' . _('Searched Username') . '</th>
                    <th>' . _('Member') . '</th>
                    <th>' . _('Email') . '</th>
                    <th>' . _('Registered') . '<br>' . _('Last access') . '</th>
                    <th>' . _('Stats') . '</th>
                    <th>' . _('Ratio') . '</th>
                    <th>' . _('IP') . '</th>
                    <th>' . _('Invited By') . '</th>
                </tr>';
    $HTMLOUT .= main_table($body, $heading, 'top20');
}

if (isset($_POST['msg_to_analyze'])) {
    $email_search = $_POST['msg_to_analyze'];
    $regex = '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i';
    $email_to_test = [];
    $number_of_matches = preg_match_all($regex, $email_search, $email_to_test);
    $matches_for_email .= '<h1>' . _('Searched Emails') . '</h1>';
    $body = '';
    $failed = [];
    foreach ($email_to_test[0] as $tested_email) {
        $users = [];
        $results = $fluent->from('users as u')
                          ->select(null)
                          ->select('u.id')
                          ->select('u.registered')
                          ->select('u.last_access')
                          ->select('u.email')
                          ->select('u.uploaded')
                          ->select('u.downloaded')
                          ->select('u.invitedby')
                          ->select('INET6_NTOA(ip) AS ip')
                          ->leftJoin('ips AS i ON u.id = i.userid')
                          ->where('email = ?', $tested_email);

        foreach ($results as $result) {
            $users[] = $result;
        }

        if (count($users) == 0) {
            $failed[] = $tested_email;
        } else {
            $number = 1;
            foreach ($users as $arr) {
                if ($arr['id'] !== '') {
                    if ($arr['invitedby'] > 0) {
                        $inviter = format_username((int) $arr['invitedby']);
                    } else {
                        $inviter = _('open signups');
                    }
                    $body .= '
            <tr>
                <td><div class="level-left">' . format_username((int) $arr['id']) . '</div></td>
                <td>' . htmlsafechars($arr['email']) . '</td>
                <td>
                    <span class="tooltipper is-blue" title="added">' . get_date((int) $arr['registered'], '') . '</span><br>
                    <span class="tooltipper has-text-success" title="last access">' . get_date((int) $arr['last_access'], '') . '</span>
                </td>
                <td>
                    <span class="has-text-success tooltipper" title="' . _('Uploaded') . '">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'up.png" alt="' . _('Up') . '"> 
                        ' . mksize($arr['uploaded']) . '
                    </span>
                    ' . ($site_config['site']['ratio_free'] ? '
                </td>' : '<br>
                    <span class="tooltipper has-text-danger" title="' . _('Downloaded') . '">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'dl.png" alt="' . _('Down') . '">  
                        ' . mksize($arr['downloaded']) . '
                    </span>
                </td>') . '
                <td>' . member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']) . '</td>
                <td>' . (!empty($arr['ip']) ? htmlsafechars($arr['ip']) : '') . '</td>
                <td>' . $inviter . '</td>
            </tr>';
                }
            }
        }
    }
    if (!empty($failed)) {
        $body .= "<tr>
                <td colspan='7'>
                    <span class='size_4 has-text-danger text-shadow'>Email" . plural(count($failed)) . " Not Found: </span>
                    <span class='is-blue'>" . implode(', ', $failed) . '</span>
                </td>
            </tr>';
    }
    $heading = '
            <tr>
                <th>' . _('Member') . '</th>
                <th>' . _('Matched Email') . '</th>
                <th>' . _('Registered') . '<br>' . _('Last access') . '</th>
                <th>' . _('Stats') . '</th>
                <th>' . _('Ratio') . '</th>
                <th>' . _('IP') . '</th>
                <th>' . _('Invited By') . '</th>
           </tr>';
    $HTMLOUT .= main_table($body, $heading, 'top20');

    $regex = '/[\._a-zA-Z0-9-]+@/i';
    $email_to_test_like = $users = [];
    $number_of_matches_like = preg_match_all($regex, $email_search, $email_to_test_like);
    $number = 0;
    $similar_emails = '';
    foreach ($email_to_test_like[0] as $tested_email_like) {
        $users = [];
        $results = $fluent->from('users')
                          ->select(null)
                          ->select('id')
                          ->select('email')
                          ->where('email LIKE ?', "%{$tested_email_like}%");
        foreach ($results as $result) {
            $users[] = $result;
        }
        if (count($users) > 0) {
            $email = $tested_email_like;
            $similar_emails .= '<tr><td><h1>' . _fe('Emails found using: {0}', $tested_email_like) . '</h1>';
            $number = 1;
            foreach ($users as $arr) {
                $similar_emails .= "<div class='level-left'>" . str_ireplace($email, '<span class="has-color-lime has-text-weight-bold">' . $email . '</span>', $arr['email']) . ' ' . _('used by') . '<span class="level-left">&nbsp;' . format_username((int) $arr['id']) . '</span></div></td></tr>';
            }
        }
    }

    $heading = '    
        <tr>
            <th>
                <h1>' . _('Search for similar emails') . ':</h1>
            </th>
        </tr>';
    $body = $similar_emails;
    if ($number === 1) {
        $HTMLOUT .= main_table($body, $heading, 'top20');
    }

    $ip_history = $_POST['msg_to_analyze'];
    $regex = '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/';
    $failed = $ip_to_test = [];
    $number_of_matches = preg_match_all($regex, $ip_history, $ip_to_test);
    $heading = $body = '';

    foreach ($ip_to_test[0] as $tested_ip) {
        $users = [];
        $results = $fluent->from('users as u')
                          ->select(null)
                          ->select('u.id')
                          ->select('u.registered')
                          ->select('u.last_access')
                          ->select('u.email')
                          ->select('u.uploaded')
                          ->select('u.downloaded')
                          ->select('u.invitedby')
                          ->select('INET6_NTOA(ip) AS ip')
                          ->leftJoin('ips AS i ON u.id = i.userid')
                          ->where('INET6_NTOA(i.ip) = ?', $tested_ip);

        foreach ($results as $result) {
            $users[] = $result;
        }

        if (count($users) == 0) {
            $failed[] = $tested_ip;
        } else {
            $heading = '
                <div class="has-text-centered"> 
                    <h1>' . _('Members who used IP') . ': ' . $tested_ip . '</h1>
                </div>
                <tr>
                    <th>' . _('Member') . '</th>
                    <th>' . _('Matched IP') . '</th>
                    <th>' . _('Email') . '</th>
                    <th>' . _('Registered') . '<br>' . _('Last access') . '</th>
                    <th>' . _('Stats') . '</th>
                    <th>' . _('Ratio') . '</th>
                    <th>' . _('IP') . '</th>
                    <th>' . _('Invited By') . '</th>
                </tr>';
            foreach ($users as $arr) {
                if ($arr['username'] !== '') {
                    if ($arr['invitedby'] > 0) {
                        $res_inviter = sql_query('SELECT id, username, class, donor, leechwarn, chatpost, pirate, king, warned, status FROM users WHERE id=' . sqlesc($arr['invitedby'])) or sqlerr(__FILE__, __LINE__);
                        $arr_inviter = mysqli_fetch_array($res_inviter);
                        $inviter = ($arr_inviter['username'] !== '' ? format_username($arr_inviter['id']) : _('open signups'));
                    } else {
                        $inviter = _('open signups');
                    }
                    $body .= '
                <tr>
                    <td>' . format_username((int) $arr['id']) . '</td>
                    <td><span class="has-color-lime has-text-weight-bold">' . $tested_ip . ' </span></td>
                    <td>' . htmlsafechars($arr['email']) . '</td>
                    <td>
                        <span class="has-color-blue" title="added">' . get_date((int) $arr['registered'], '') . '</span><br>
                        <span class="has-color-lime" title="last access">' . get_date((int) $arr['last_access'], '') . '</span>
                    </td>
                    <td>
                        <img src="' . $site_config['paths']['images_baseurl'] . 'up.png" alt="' . _('Up') . '" title="' . _('Uploaded') . '"> 
                        <span class="has-color-lime">' . mksize($arr['uploaded']) . '</span>
                        ' . ($site_config['site']['ratio_free'] ? '' : '<br>
                        <img src="' . $site_config['paths']['images_baseurl'] . 'dl.png" alt="' . _('Down') . '" title="' . _('Downloaded') . '">  
                        <span class="has-color-danger">' . mksize($arr['downloaded']) . '</span></td>') . '
                    <td>' . member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']) . '</td>
                    <td>' . (!empty($arr['ip']) ? htmlsafechars($arr['ip']) : '') . '</td>
                    <td>' . $inviter . '</td>
                </tr>';
                }
            }
        }
    }

    if (!empty($body) && empty($no_matches_for_this_ip)) {
        $HTMLOUT .= main_table($body, $heading, 'top20');
    } else {
        $HTMLOUT .= main_div(" <div class='has-text-centered'>
                    <h1>" . _('No matches for the following IPs') . "</h1>
                    <div class='bg-02 padding20 round20'>$no_matches_for_this_ip
                    </div>
                </div>");
    }
}

if (isset($_POST['invite_code'])) {
    $heading = $body = $user = '';
    $user = $fluent->from('users as u')
                   ->select(null)
                   ->select('u.id')
                   ->select('u.registered')
                   ->select('u.last_access')
                   ->select('u.email')
                   ->select('u.uploaded')
                   ->select('u.downloaded')
                   ->select('u.invitedby')
                   ->select('INET6_NTOA(ip) AS ip')
                   ->leftJoin('ips AS i ON u.id = i.userid')
                   ->leftJoin('invite_codes AS c ON u.id = c.sender')
                   ->where('c.code = ?', $invite_code)
                   ->fetch();

    if ($user['id'] == '') {
        $HTMLOUT .= stdmsg(_('Error'), _('No user was found! Whoever made this invite is no longer with us.'), 'top20');
    } else {
        $heading = '
                <h1 class="top10 left10">Invite Code Created By:</h1>
                <tr>
                    <th>Invite Creator</th>
                    <th>' . _('Email') . '</th>
                    <th>' . _('IP') . '</th>
                    <th>' . _('Last access') . '</th>
                    <th>' . _('Joined') . '</th>
                    <th>' . _('Up / Down') . '</th>
                    <th>' . _('Ratio') . '</th>
                    <th>' . _('Invited By') . '</th>
                </tr>';
        $body = '
                <tr>
                    <td>' . format_username($user['id']) . '</td>
                    <td>' . htmlsafechars($user['email']) . '</td>
                    <td>' . (!empty($user['ip']) ? htmlsafechars($user['ip']) : '') . '</td>
                    <td>' . get_date($user['last_access'], '') . '</td>
                    <td>' . get_date($user['registered'], '') . '</td>
                    <td><img src="' . $site_config['paths']['images_baseurl'] . 'up.png" alt="' . _('Up') . '" title="' . _('Uploaded') . '"> <span class="has-color-lime">' . mksize($user['uploaded']) . '</span>
                    ' . ($site_config['site']['ratio_free'] ? '' : '<br>
                    <img src="' . $site_config['paths']['images_baseurl'] . 'dl.png" alt="' . _('Down') . '" title="' . _('Downloaded') . '">  
                    <span class="has-color-danger">' . mksize($user['downloaded']) . '</span></td>') . '
                    <td>' . member_ratio($user['uploaded'], $user['downloaded']) . '</td>
                    <td>' . ($user['invitedby'] == 0 ? _('open signups') : format_username($user['invitedby'])) . '</td>
                </tr>';
        $HTMLOUT .= wrapper(main_table($body, $heading), 'top20');
    }

    $user_invited = [];
    $heading = $body = $users = '';
    $user_invited = $fluent->from('users as u')
                           ->select(null)
                           ->select('u.id')
                           ->select('u.registered')
                           ->select('u.last_access')
                           ->select('u.email')
                           ->select('u.uploaded')
                           ->select('u.downloaded')
                           ->select('u.invitedby')
                           ->select('INET6_NTOA(ip) AS ip')
                           ->leftJoin('ips AS i ON u.id = i.userid')
                           ->leftJoin('invite_codes AS c ON u.id = c.receiver')
                           ->where('c.code = ?', $invite_code)
                           ->fetch();

    if ($user_invited['id'] == '') {
        $HTMLOUT .= stdmsg(_('Error'), _('This invite code was either not used, or the member who used it is not longer with us.'), 'top20');
    } else {
        $heading = '
                <h1 class="top10 left10">Invite Code Used By:</h1>
                <tr>
                    <th>' . _('Invited') . '</th>
                    <th>' . _('Email') . '</th>
                    <th>' . _('IP') . '</th>
                    <th>' . _('Last access') . '</th>
                    <th>' . _('Joined') . '</th>
                    <th>' . _('Up / Down') . '</th>
                    <th>' . _('Ratio') . '</th>
                    <th>' . _('Invited By') . '</th>
                </tr>';
        $body = '
                <tr>
                    <td>' . format_username($user_invited['id']) . '</td>
                    <td>' . htmlsafechars($user_invited['email']) . '</td>
                    <td>' . (!empty($user_invited['ip']) ? htmlsafechars($user_invited['ip']) : '') . '</td>
                    <td>' . get_date($user_invited['last_access'], '') . '</td>
                    <td>' . get_date($user_invited['added'], '') . '</td>
                    <td><img src="' . $site_config['paths']['images_baseurl'] . 'up.png" alt="' . _('Up') . '" title="' . _('Uploaded') . '"> <span class="has-color-lime">' . mksize($user_invited['uploaded']) . '</span>
                    ' . ($site_config['site']['ratio_free'] ? '' : '<br>
                    <img src="' . $site_config['paths']['images_baseurl'] . 'dl.png" alt="' . _('Down') . '" title="' . _('Downloaded') . '">  
                    <span class="has-color-danger">' . mksize($user_invited['downloaded']) . '</span></td>') . '
                    <td>' . member_ratio($user_invited['uploaded'], $user_invited['downloaded']) . '</td>
                    <td>' . ($user_invited['invitedby'] == 0 ? _('open signups') : format_username($user_invited['receiver'])) . '</td>
                </tr>';
        $HTMLOUT .= wrapper(main_table($body, $heading));
    }
}
$title = _('Mega Search');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
