<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $pdo, $fluent;

$lang = array_merge($lang, load_language('ad_mega_search'));
$stdhead = [
    'css' => [
        get_file_name('upload_css'),
    ],
];

$msg_to_analyze = (isset($_POST['msg_to_analyze']) ? htmlsafechars($_POST['msg_to_analyze']) : '');
$invite_code = (isset($_POST['invite_code']) ? htmlsafechars($_POST['invite_code']) : '');
$user_names = (isset($_POST['user_names']) ? $_POST['user_names'] : '');
$HTMLOUT = $found = $not_found = $count = $no_matches_for_this_email = $matches_for_email = $no_matches_for_this_ip = $matches_for_ip = '';
$number = 0;
$HTMLOUT .= '
        <div class="has-text-centered top20">
            <h1>' . $lang['mega_heading'] . '</h1>
        </div>';

$HTMLOUT .= main_div('
        <div class="has-text-centered size_4 has-text-white top10 bottom10">' . $lang['mega_analyze'] . '</div>
        <div class="bg-00 round10 padding20">
            <form method="post" action="' . $site_config['baseurl'] . 'staffpanel.php?tool=mega_search&amp;action=mega_search">
                ' . bubble($lang['mega_text'], $lang['mega_text_1']) . '
                <textarea name="msg_to_analyze" rows="20" class="w-100">' . $msg_to_analyze . '</textarea>
                <div class="has-text-centered top20">
                    <input type="submit" class="button is-small" value="' . $lang['mega_search_btn'] . '" />
                </div>
            </form>
        </div>', 'bottom20');
$HTMLOUT .= main_div('
        <div class="bg-00 round10 padding20 ">
            <form method="post" action="' . $site_config['baseurl'] . 'staffpanel.php?tool=mega_search&amp;action=mega_search">
                ' . bubble('<b>' . $lang['mega_invite'] . '</b>', $lang['mega_invite_1']) . '
                <input type="text" name="invite_code" class="w-100" value="' . $invite_code . '" />
                <div class="has-text-centered top20">
                    <input type="submit" class="button is-small" value="' . $lang['mega_search_btn'] . '" />
                </div>
            </form>
        </div>', 'bottom20');
$HTMLOUT .= main_div('
        <div class="bg-00 round10 padding20">
            <form method="post" action="' . $site_config['baseurl'] . 'staffpanel.php?tool=mega_search&amp;action=mega_search">
                ' . bubble('<b>' . $lang['mega_names'] . '</b>', $lang['mega_names_1']) . '
                <textarea name="user_names" rows="4" class="w-100">' . $user_names . '</textarea>
                <div class="has-text-centered top20">
                    <input type="submit" class="button is-small" value="' . $lang['mega_search_btn'] . '" />
                </div>
            </form>
        </div>');

if (!empty($user_names)) {
    $searched_users = explode(',', preg_replace('/\s+/s', ',', $user_names));
    $body = '';
    $failed = [];
    foreach ($searched_users as $search_users) {
        $users = [];
        $results = $fluent->from('users')
            ->select(null)
            ->select('id')
            ->select('INET6_NTOA(ip) AS ip')
            ->select('added')
            ->select('last_access')
            ->select('email')
            ->select('uploaded')
            ->select('downloaded')
            ->select('invitedby')
            ->where('username LIKE ?', "%{$search_users}%");
        foreach ($results as $result) {
            $users[] = $result;
        }

        if (count($users) > 0) {
            foreach ($users as $arr) {
                if ($arr['invitedby'] > 0) {
                    $inviter = format_username($arr['invitedby']);
                } else {
                    $inviter = $lang['mega_open'];
                }
                $body .= '
            <tr>
                <td>' . $search_users . '</td>
                <td>' . format_username($arr['id']) . '</td>
                <td>' . htmlsafechars($arr['email']) . '</td>
                <td>
                    <span class="tooltipper has-text-blue" title="added">' . get_date($arr['added'], '') . '</span><br>
                    <span class="tooltipper has-text-success" title="last access">' . get_date($arr['last_access'], '') . '</span>
                </td>
                <td>
                    <span class="has-text-success tooltipper" title="' . $lang['mega_uploaded'] . '">
                        <img src="' . $site_config['pic_base_url'] . 'up.png" alt="' . $lang['mega_up'] . '" /> 
                        ' . mksize($arr['uploaded']) . '
                    </span>
                    ' . ($site_config['ratio_free'] ? '
                </td>' : '<br>
                    <span class="text-red tooltipper" title="' . $lang['mega_downloaded'] . '">
                        <img src="' . $site_config['pic_base_url'] . 'dl.png" alt="' . $lang['mega_down'] . '" />  
                        ' . mksize($arr['downloaded']) . '
                    </span>
                </td>') . '
                <td>' . member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']) . '</td>
                <td>' . make_nice_address($arr['ip']) . '</td>
                <td>' . $inviter . '</td>
            </tr>';
            }
        } else {
            $failed[] = $search_users;
        }
    }
    if (!empty($failed)) {
        $body .= "
            <tr>
                <td colspan='8'><span class='size_4 text-red text-shadow'>Not Found: </span><span class='has-text-blue'>" . implode(', ', $failed) . "</span></td>
            </tr>";
    }
    if (empty($body)) {
        $body = "
            <tr>
                <td colspan='8'><span class='size_4 text-red text-shadow'>Not Found: </span><span class='has-text-blue'>" . implode(', ', $searched_users) . "</span></td>
            </tr>";
    }
    $heading = " 
                <tr>
                    <th>{$lang['mega_searched']}</th>
                    <th>{$lang['mega_member']}</th>
                    <th>{$lang['mega_email']}</th>
                    <th>{$lang['mega_registered']}<br>{$lang['mega_last_acc']}</th>
                    <th>{$lang['mega_stats']}</th>
                    <th>{$lang['mega_ratio']}</th>
                    <th>{$lang['mega_ip']}</th>
                    <th>{$lang['mega_invited_by']}</th>
                </tr>";
    $HTMLOUT .= main_table($body, $heading, 'top20');
}

if (isset($_POST['msg_to_analyze'])) {
    $email_search = $_POST['msg_to_analyze'];
    $regex = '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i';
    $email_to_test = [];
    $number_of_matches = preg_match_all($regex, $email_search, $email_to_test);
    $matches_for_email .= '<h1>' . $lang['mega_emails'] . '</h1>';
    $body = '';
    $failed = [];
    foreach ($email_to_test[0] as $tested_email) {
        $users = [];
        $results = $fluent->from('users')
            ->select(null)
            ->select('id')
            ->select('email')
            ->select('INET6_NTOA(ip) AS ip')
            ->select('added')
            ->select('last_access')
            ->select('uploaded')
            ->select('downloaded')
            ->select('invitedby')
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
                        $inviter = format_username($arr['invitedby']);
                    } else {
                        $inviter = $lang['mega_open'];
                    }
                    $body .= '
            <tr>
                <td><div class="level-left">' . format_username($arr['id']) . '</div></td>
                <td>' . htmlsafechars($arr['email']) . '</td>
                <td>
                    <span class="tooltipper has-text-blue" title="added">' . get_date($arr['added'], '') . '</span><br>
                    <span class="tooltipper has-text-success" title="last access">' . get_date($arr['last_access'], '') . '</span>
                </td>
                <td>
                    <span class="has-text-success tooltipper" title="' . $lang['mega_uploaded'] . '">
                        <img src="' . $site_config['pic_base_url'] . 'up.png" alt="' . $lang['mega_up'] . '" /> 
                        ' . mksize($arr['uploaded']) . '
                    </span>
                    ' . ($site_config['ratio_free'] ? '
                </td>' : '<br>
                    <span class="tooltipper text-red" title="' . $lang['mega_downloaded'] . '">
                        <img src="' . $site_config['pic_base_url'] . 'dl.png" alt="' . $lang['mega_down'] . '" />  
                        ' . mksize($arr['downloaded']) . '
                    </span>
                </td>') . '
                <td>' . member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']) . '</td>
                <td>' . make_nice_address($arr['ip']) . '</td>
                <td>' . $inviter . '</td>
            </tr>';
                }
            }
        }
    }
    if (!empty($failed)) {
        $body .= "
            <tr>
                <td colspan='7'>
                    <span class='size_4 text-red text-shadow'>Email" . plural($failed) . " Not Found: </span>
                    <span class='has-text-blue'>" . implode(', ', $failed) . "</span>
                </td>
            </tr>";
    }
    $heading = "
            <tr>
                <th>{$lang['mega_member']}</th>
                <th>{$lang['mega_matched_email']}</th>
                <th>{$lang['mega_registered']}<br>{$lang['mega_last_acc']}</th>
                <th>{$lang['mega_stats']}</th>
                <th>{$lang['mega_ratio']}</th>
                <th>{$lang['mega_ip']}</th>
                <th>{$lang['mega_invited_by']}</th>
           </tr>";
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
            ->where('email LIKE ?', "%$tested_email_like%");
        foreach ($results as $result) {
            $users[] = $result;
        }
        if (count($users) > 0) {
            //$email = preg_replace('/[^a-zA-Z0-9_-\s]/', '', $tested_email_like);
            $email = $tested_email_like;
            $similar_emails .= "<tr><td><h1>{$lang['mega_email_using']} $tested_email_like</h1>";
            $number = 1;
            foreach ($users as $arr) {
                $similar_emails .= "<div class='level-left'>" . str_ireplace($email, '<span style="color: red; font-weight: bold;">' . $email . '</span>', $arr['email']) . ' ' . $lang['mega_used_by'] . '<span class="level-left">&nbsp;' . format_username($arr['id']) . '</span></div></td></tr>';
            }
        }
    }

    $heading = '    
        <tr>
            <th>
                <h1>' . $lang['mega_search_sim'] . '</h1>
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
        $results = $fluent->from('users')
            ->select(null)
            ->select('id')
            ->select('email')
            ->select('INET6_NTOA(ip) AS ip')
            ->select('added')
            ->select('last_access')
            ->select('uploaded')
            ->select('downloaded')
            ->select('invitedby')
            ->where('INET6_NTOA(ip) = ?', $tested_ip);
        foreach ($results as $result) {
            $users[] = $result;
        }

        if (count($users) == 0) {
            $failed[] = $tested_ip;
        } else {
            $heading = '
                <div class="has-text-centered"> 
                    <h1>' . $lang['mega_used_ip'] . ' ' . $tested_ip . '</h1>
                </div>
                <tr>
                    <th>' . $lang['mega_member'] . '</th>
                    <th>' . $lang['mega_matched_ip'] . '</th>
                    <th>' . $lang['mega_email'] . '</th>
                    <th>' . $lang['mega_registered'] . '<br>' . $lang['mega_last_acc'] . '</th>
                    <th>' . $lang['mega_stats'] . '</th>
                    <th>' . $lang['mega_ratio'] . '</th>
                    <th>' . $lang['mega_ip'] . '</th>
                    <th>' . $lang['mega_invited_by'] . '</th>
                </tr>';
            foreach ($users as $arr) {
                if ($arr['username'] !== '') {
                    if ($arr['invitedby'] > 0) {
                        $res_inviter = sql_query('SELECT id, username, class, donor, suspended, leechwarn, chatpost, pirate, king, warned, enabled FROM users WHERE id = ' . sqlesc($arr['invitedby'])) or sqlerr(__FILE__, __LINE__);
                        $arr_inviter = mysqli_fetch_array($res_inviter);
                        $inviter = ($arr_inviter['username'] !== '' ? format_username($arr_inviter) : $lang['mega_open']);
                    } else {
                        $inviter = $lang['mega_open'];
                    }
                    $body .= '
                <tr>
                    <td>' . format_username($arr) . '</td>
                    <td><span style="color: red; font-weight: bold;">' . $tested_ip . ' </span></td>
                    <td>' . htmlsafechars($arr['email']) . '</td>
                    <td>
                        <span style="color: blue;" title="added">' . get_date($arr['added'], '') . '</span><br>
                        <span style="color: green;" title="last access">' . get_date($arr['last_access'], '') . '</span>
                    </td>
                    <td>
                        <img src="' . $site_config['pic_base_url'] . 'up.png" alt="' . $lang['mega_up'] . '" title="' . $lang['mega_uploaded'] . '" /> 
                        <span style="color: green;">' . mksize($arr['uploaded']) . '</span>
                        ' . ($site_config['ratio_free'] ? '' : '<br>
                        <img src="' . $site_config['pic_base_url'] . 'dl.png" alt="' . $lang['mega_down'] . '" title="' . $lang['mega_downloaded'] . '" />  
                        <span style="color: red;">' . mksize($arr['downloaded']) . '</span></td>') . '
                    <td>' . member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']) . '</td>
                    <td>' . make_nice_address($arr['ip']) . '<br>
                    </td>
                    <td>' . $inviter . '</td>
                </tr>';
                }
            }
        }
    }

    if (!empty($body) && empty($no_matches_for_this_ip)) {
        $HTMLOUT .= main_table($body, $heading, 'top20');
    } else {
        $HTMLOUT .= main_div("
                <div class='has-text-centered'> 
                    <h1>{$lang['mega_no_ips']}</h1>
                    <div class='bg-02 padding20 round20'>
                        $no_matches_for_this_ip
                    </div>
                </div>");
    }
}

if (isset($_POST['invite_code'])) {
    $heading = $body = $user = '';
    $user = $fluent->from('users')
        ->select(null)
        ->select('users.id')
        ->select('users.email')
        ->select('INET6_NTOA(users.ip) AS ip')
        ->select('users.added')
        ->select('users.last_access')
        ->select('users.uploaded')
        ->select('users.downloaded')
        ->select('users.invitedby')
        ->select('invite_codes.id AS invite_id')
        ->select('invite_codes.added AS invite_added')
        ->leftJoin('invite_codes ON users.id = invite_codes.sender')
        ->where('invite_codes.code = ?', $invite_code)
        ->fetch();

    if ($user['id'] == '') {
        $HTMLOUT .= stdmsg($lang['mega_error'], $lang['mega_invite_gone'], 'top20');
    } else {
        $heading = '
                <h1 class="top10 left10">Invite Code Created By:</h1>
                <tr>
                    <th>Invite Creator</th>
                    <th>' . $lang['mega_email'] . '</th>
                    <th>' . $lang['mega_ip'] . '</th>
                    <th>' . $lang['mega_last_acc'] . '</th>
                    <th>' . $lang['mega_joined'] . '</th>
                    <th>' . $lang['mega_ud'] . '</th>
                    <th>' . $lang['mega_ratio'] . '</th>
                    <th>' . $lang['mega_invited_by'] . '</th>
                </tr>';
        $body = '
                <tr>
                    <td>' . format_username($user['id']) . '</td>
                    <td>' . htmlsafechars($user['email']) . '</td>
                    <td>' . htmlsafechars($user['ip']) . '</td>
                    <td>' . get_date($user['last_access'], '') . '</td>
                    <td>' . get_date($user['added'], '') . '</td>
                    <td><img src="' . $site_config['pic_base_url'] . 'up.png" alt="' . $lang['mega_up'] . '" title="' . $lang['mega_uploaded'] . '" /> <span style="color: green;">' . mksize($user['uploaded']) . '</span>
                    ' . ($site_config['ratio_free'] ? '' : '<br>
                    <img src="' . $site_config['pic_base_url'] . 'dl.png" alt="' . $lang['mega_down'] . '" title="' . $lang['mega_downloaded'] . '" />  
                    <span style="color: red;">' . mksize($user['downloaded']) . '</span></td>') . '
                    <td>' . member_ratio($user['uploaded'], $site_config['ratio_free'] ? '0' : $user['downloaded']) . '</td>
                    <td>' . ($user['invitedby'] == 0 ? $lang['mega_open'] : format_username($user['invitedby'])) . '</td>
                </tr>';
        $HTMLOUT .= wrapper(main_table($body, $heading), 'top20');
    }

    $user_invited = [];
    $heading = $body = $users = '';
    $user_invited = $fluent->from('users')
        ->select(null)
        ->select('users.id')
        ->select('users.email')
        ->select('INET6_NTOA(users.ip) AS ip')
        ->select('users.added')
        ->select('users.last_access')
        ->select('users.uploaded')
        ->select('users.downloaded')
        ->select('users.invitedby')
        ->select('invite_codes.id AS invite_id')
        ->leftJoin('invite_codes ON users.id = invite_codes.receiver')
        ->where('invite_codes.code = ?', $invite_code)
        ->fetch();


    if ($user_invited['id'] == '') {
        $HTMLOUT .= stdmsg($lang['mega_error'], $lang['mega_not_used'], 'top20');
    } else {
        $heading = '
                <h1 class="top10 left10">Invite Code Used By:</h1>
                <tr>
                    <th>' . $lang['mega_invited'] . '</th>
                    <th>' . $lang['mega_email'] . '</th>
                    <th>' . $lang['mega_ip'] . '</th>
                    <th>' . $lang['mega_last_acc'] . '</th>
                    <th>' . $lang['mega_joined'] . '</th>
                    <th>' . $lang['mega_ud'] . '</th>
                    <th>' . $lang['mega_ratio'] . '</th>
                    <th>' . $lang['mega_invited_by'] . '</th>
                </tr>';
        $body = '
                <tr>
                    <td>' . format_username($user_invited['id']) . '</td>
                    <td>' . htmlsafechars($user_invited['email']) . '</td>
                    <td>' . htmlsafechars($user_invited['ip']) . '</td>
                    <td>' . get_date($user_invited['last_access'], '') . '</td>
                    <td>' . get_date($user_invited['added'], '') . '</td>
                    <td><img src="' . $site_config['pic_base_url'] . 'up.png" alt="' . $lang['mega_up'] . '" title="' . $lang['mega_uploaded'] . '" /> <span style="color: green;">' . mksize($user_invited['uploaded']) . '</span>
                    ' . ($site_config['ratio_free'] ? '' : '<br>
                    <img src="' . $site_config['pic_base_url'] . 'dl.png" alt="' . $lang['mega_down'] . '" title="' . $lang['mega_downloaded'] . '" />  
                    <span style="color: red;">' . mksize($user_invited['downloaded']) . '</span></td>') . '
                    <td>' . member_ratio($user_invited['uploaded'], $site_config['ratio_free'] ? '0' : $user_invited['downloaded']) . '</td>
                    <td>' . ($user_invited['invitedby'] == 0 ? $lang['mega_open'] : format_username($user_invited['receiver'])) . '</td>
                </tr>';
        $HTMLOUT .= wrapper(main_table($body, $heading));
    }
}

echo stdhead($lang['mega_stdhead'], true, $stdhead) . wrapper($HTMLOUT) . stdfoot();
