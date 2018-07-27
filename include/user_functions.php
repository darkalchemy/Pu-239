<?php

/**
 * @return mixed
 */
function get_anonymous()
{
    global $CURUSER;

    return $CURUSER['anonymous_until'];
}

/**
 * @return mixed
 */
function get_parked()
{
    global $CURUSER;

    return $CURUSER['parked_until'];
}

/**
 * @param     $msg
 * @param int $channel
 * @param int $ttl
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
 */
function autoshout($msg, $channel = 0, $ttl = 7200)
{
    global $site_config, $pdo;

    if (user_exists($site_config['chatBotID'])) {
        $values = [
            'userID' => $site_config['chatBotID'],
            'userName' => $site_config['chatBotName'],
            'userRole' => 100,
            'channel' => $channel,
            'dateTime' => gmdate('Y-m-d H:i:s', TIME_NOW),
            'ip' => '127.0.0.1',
            'text' => $msg,
            'ttl' => $ttl,
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO ajax_chat_messages
                        (userID, userName, userRole, channel, dateTime, ip, text, ttl)
                      VALUES
                        (:userID, :userName, :userRole, :channel, :dateTime, INET6_ATON(:ip), :text, :ttl)'
        );
        $stmt->execute($values);
    }
}

/**
 * @param        $user
 * @param string $mode
 * @param bool   $rep_is_on
 * @param int    $post_id
 *
 * @return string
 */
function get_reputation($user, $mode = '', $rep_is_on = true, $post_id = 0)
{
    global $site_config, $CURUSER, $user_stuffs;

    if (empty($user['username'])) {
        $user = $user_stuffs->getUserFromId($user);
    }

    $member_reputation = '';
    if ($rep_is_on) {
        include CACHE_DIR . 'rep_cache.php';
        //require_once (CLASS_DIR . 'class_user_options.php');
        // ok long winded file checking, but it's much better than file_exists
        if (!isset($reputations) || !is_array($reputations) || count($reputations) < 1) {
            return '<span title="Cache doesn\'t exist or zero length">Reputation: Offline</span>';
        }
        $user['g_rep_hide'] = isset($user['g_rep_hide']) ? $user['g_rep_hide'] : 0;
        //$user['username'] = (($user['opt1'] & user_options::ANONYMOUS) ? $user['username'] : 'Anonymous');
        $user['username'] = ($user['anonymous'] != 'yes') ? $user['username'] : 'Anonymous';
        // Hmmm...bit of jiggery-pokery here, couldn't think of a better way.
        $max_rep = max(array_keys($reputations));
        if ($user['reputation'] >= $max_rep) {
            $user_reputation = $reputations[$max_rep];
        } else {
            foreach ($reputations as $y => $x) {
                $old = $x;
                if ($y > $user['reputation']) {
                    $user_reputation = $old;
                    break;
                }
            }
        }
        //$rep_is_on = TRUE;
        //$CURUSER['g_rep_hide'] = FALSE;
        $rep_power = $user['reputation'];
        $posneg = '';
        if ($user['reputation'] == 0) {
            $rep_img = 'balance';
            $rep_power = $user['reputation'] * -1;
        } elseif ($user['reputation'] < 0) {
            $rep_img = 'neg';
            $rep_img_2 = 'highneg';
            $rep_power = $user['reputation'] * -1;
        } else {
            $rep_img = 'pos';
            $rep_img_2 = 'highpos';
        }

        $pips = 12;
        switch ($mode) {
            case 'comments':
                $pips = 12;
                break;

            case 'torrents':
                $pips = 1003;
                break;

            case 'users':
                $pips = 970;
                break;

            case 'posts':
                $pips = 12;
                break;

            default:
                $pips = 12; // statusbar
        }
        $rep_bar = intval($rep_power / 100);
        if ($rep_bar > 10) {
            $rep_bar = 10;
        }
        if ($user['g_rep_hide']) { // can set this to a group option if required, via admin?
            $posneg = 'off';
            $rep_level = 'rep_off';
        } else { // it ain't off then, so get on with it! I wanna see shiny stuff!!
            $rep_level = $user_reputation ? $user_reputation : 'rep_undefined'; // just incase
            for ($i = 0; $i <= $rep_bar; ++$i) {
                if ($i >= 5) {
                    $posneg .= "<img data-src='{$site_config['pic_baseurl']}rep/reputation_$rep_img_2.gif' alt=\"Reputation Power $rep_power\n" . htmlsafechars($user['username']) . " $rep_level\" title=\"Reputation Power $rep_power " . htmlsafechars($user['username']) . " $rep_level\" class='lazy' />";
                } else {
                    $posneg .= "<img data-src='{$site_config['pic_baseurl']}rep/reputation_$rep_img.gif' alt=\"Reputation Power $rep_power\n" . htmlsafechars($user['username']) . " $rep_level\" title=\"Reputation Power $rep_power " . htmlsafechars($user['username']) . " $rep_level\" class='lazy' />";
                }
            }
        }

        if (!empty($mode)) {
            return 'Rep: ' . $posneg . "<div><a href='javascript:;' onclick=\"PopUp('{$site_config['baseurl']}/reputation.php?pid=" . ($post_id != 0 ? (int) $post_id : (int) $user['id']) . '&amp;locale=' . $mode . "','Reputation',400,300,1,1);\"><img data-src='{$site_config['pic_baseurl']}forumicons/giverep.jpg' alt='Add reputation:: " . htmlsafechars($user['username']) . "' title='Add reputation:: " . htmlsafechars($user['username']) . "' class='lazy' /></a></div>";
        } else {
            return ' ' . $posneg;
        }
    }

    return '<span title="Set offline by admin setting">Rep System Offline</span>';
}

/**
 * @param $ratio
 *
 * @return string
 */
function get_ratio_color($ratio)
{
    if ($ratio < 0.1) {
        return '#ff0000';
    }
    if ($ratio < 0.2) {
        return '#ee0000';
    }
    if ($ratio < 0.3) {
        return '#dd0000';
    }
    if ($ratio < 0.4) {
        return '#cc0000';
    }
    if ($ratio < 0.5) {
        return '#bb0000';
    }
    if ($ratio < 0.6) {
        return '#aa0000';
    }
    if ($ratio < 0.7) {
        return '#990000';
    }
    if ($ratio < 0.8) {
        return '#880000';
    }
    if ($ratio < 0.9) {
        return '#770000';
    }
    if ($ratio < 1) {
        return '#660000';
    }
    if (($ratio >= 1.0) && ($ratio < 2.0)) {
        return '#006600';
    }
    if (($ratio >= 2.0) && ($ratio < 3.0)) {
        return '#007700';
    }
    if (($ratio >= 3.0) && ($ratio < 4.0)) {
        return '#008800';
    }
    if (($ratio >= 4.0) && ($ratio < 5.0)) {
        return '#009900';
    }
    if (($ratio >= 5.0) && ($ratio < 6.0)) {
        return '#00aa00';
    }
    if (($ratio >= 6.0) && ($ratio < 7.0)) {
        return '#00bb00';
    }
    if (($ratio >= 7.0) && ($ratio < 8.0)) {
        return '#00cc00';
    }
    if (($ratio >= 8.0) && ($ratio < 9.0)) {
        return '#00dd00';
    }
    if (($ratio >= 9.0) && ($ratio < 10.0)) {
        return '#00ee00';
    }
    if ($ratio >= 10) {
        return '#00ff00';
    }

    return '#777777';
}

/**
 * @param $ratio
 *
 * @return string
 */
function get_slr_color($ratio)
{
    if ($ratio < 0.025) {
        return '#ff0000';
    }
    if ($ratio < 0.05) {
        return '#ee0000';
    }
    if ($ratio < 0.075) {
        return '#dd0000';
    }
    if ($ratio < 0.1) {
        return '#cc0000';
    }
    if ($ratio < 0.125) {
        return '#bb0000';
    }
    if ($ratio < 0.15) {
        return '#aa0000';
    }
    if ($ratio < 0.175) {
        return '#990000';
    }
    if ($ratio < 0.2) {
        return '#880000';
    }
    if ($ratio < 0.225) {
        return '#770000';
    }
    if ($ratio < 0.25) {
        return '#660000';
    }
    if ($ratio < 0.275) {
        return '#550000';
    }
    if ($ratio < 0.3) {
        return '#440000';
    }
    if ($ratio < 0.325) {
        return '#330000';
    }
    if ($ratio < 0.35) {
        return '#220000';
    }
    if ($ratio < 0.375) {
        return '#110000';
    }
    if (($ratio >= 1.0) && ($ratio < 2.0)) {
        return '#006600';
    }
    if (($ratio >= 2.0) && ($ratio < 3.0)) {
        return '#007700';
    }
    if (($ratio >= 3.0) && ($ratio < 4.0)) {
        return '#008800';
    }
    if (($ratio >= 4.0) && ($ratio < 5.0)) {
        return '#009900';
    }
    if (($ratio >= 5.0) && ($ratio < 6.0)) {
        return '#00aa00';
    }
    if (($ratio >= 6.0) && ($ratio < 7.0)) {
        return '#00bb00';
    }
    if (($ratio >= 7.0) && ($ratio < 8.0)) {
        return '#00cc00';
    }
    if (($ratio >= 8.0) && ($ratio < 9.0)) {
        return '#00dd00';
    }
    if (($ratio >= 9.0) && ($ratio < 10.0)) {
        return '#00ee00';
    }
    if ($ratio >= 10) {
        return '#00ff00';
    }

    return '#777777';
}

/**
 * @param $ratio_to_check
 *
 * @return string
 */
function ratio_image_machine($ratio_to_check)
{
    global $site_config;
    switch ($ratio_to_check) {
        case $ratio_to_check >= 5:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/yay.gif" alt="Yay" title="Yay" class="lazy" />';
            break;

        case $ratio_to_check >= 4:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/pimp.gif" alt="Pimp" title="Pimp" class="lazy" />';
            break;

        case $ratio_to_check >= 3:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/w00t.gif" alt="W00t" title="W00t" class="lazy" />';
            break;

        case $ratio_to_check >= 2:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/grin.gif" alt="Grin" title="Grin" class="lazy" />';
            break;

        case $ratio_to_check >= 1.5:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/evo.gif" alt="Evo" title="Evo" class="lazy" />';
            break;

        case $ratio_to_check >= 1:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/smile1.gif" alt="Smile" title="Smile" class="lazy" />';
            break;

        case $ratio_to_check >= 0.5:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/noexpression.gif" alt="Blank" title="Blank" class="lazy" />';
            break;

        case $ratio_to_check >= 0.25:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/cry.gif" alt="Cry" title="Cry" class="lazy" />';
            break;

        case $ratio_to_check < 0.25:
            return '<img data-src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="Shit" title="Shit" class="lazy" />';
            break;
    }
}

/**
 * @param      $class
 * @param bool $to_lower
 *
 * @return string
 */
function get_user_class_name($class, $to_lower = false)
{
    global $class_names;
    $class = (int) $class;
    if (!valid_class($class)) {
        return '';
    }
    if (isset($class_names[$class]) && $to_lower) {
        return strtolower(str_replace(' ', '_', $class_names[$class]));
    } elseif (isset($class_names[$class])) {
        return $class_names[$class];
    } else {
        return '';
    }
}

/**
 * @param $class
 *
 * @return string
 */
function get_user_class_color($class)
{
    global $class_colors;
    $class = (int) $class;
    if (!valid_class($class)) {
        return '';
    }
    if (isset($class_colors[$class])) {
        return $class_colors[$class];
    } else {
        return '';
    }
}

/**
 * @param $class
 *
 * @return string
 */
function get_user_class_image($class)
{
    global $class_images;
    $class = (int) $class;
    if (!valid_class($class)) {
        return '';
    }
    if (isset($class_images[$class])) {
        return $class_images[$class];
    } else {
        return '';
    }
}

/**
 * @param $class
 *
 * @return bool
 */
function valid_class($class)
{
    $class = (int) $class;

    return (bool) ($class >= UC_MIN && $class <= UC_MAX);
}

/**
 * @param int $min
 * @param int $max
 *
 * @return bool
 */
function min_class($min = UC_MIN, $max = UC_MAX)
{
    global $CURUSER;
    $minclass = (int) $min;
    $maxclass = (int) $max;
    if (!isset($CURUSER)) {
        return false;
    }
    if (!valid_class($minclass) || !valid_class($maxclass)) {
        return false;
    }
    if ($maxclass < $minclass) {
        return false;
    }

    return (bool) ($CURUSER['class'] >= $minclass && $CURUSER['class'] <= $maxclass);
}

/**
 * @param int  $user_id
 * @param bool $icons
 * @param bool $tooltipper
 *
 * @return string
 */
function format_username(int $user_id, $icons = true, $tooltipper = true)
{
    global $site_config, $user_stuffs;

    $users_data = $user_stuffs->getUserFromId($user_id);
    $peer = new DarkAlchemy\Pu239\Peer();
    $peers = $peer->getPeersFromUserId($user_id);

    if ($users_data['id'] === 0) {
        return 'System';
    } elseif (empty($users_data['username'])) {
        return "<span class='has-text-red'>unknown_id[$user_id]</span>";
    }
    $avatar = get_avatar($users_data);
    $tip = $tooltip = '';
    if ($tooltipper) {
        $tip = "
                <div class='tooltip_templates'>
                    <div id='userid_{$users_data['id']}_tooltip' class='is-flex tooltip'>
                        <div class='right20'>{$avatar}</div>
                        <div style='min-width: 150px;'>
                            <span class='level is-marginless'>
                                <span class='level-left " . get_user_class_name($users_data['class'], true) . "'>" . htmlsafechars($users_data['username']) . "</span>
                                <span class='level-right " . get_user_class_name($users_data['class'], true) . "'>" . get_user_class_name($users_data['class'], false) . "</span>
                            </span>
                            <span class='level is-marginless'>
                                <span class='level-left'>Uploaded: </span>
                                <span class='level-right'>" . human_filesize($users_data['uploaded']) . "</span>
                            </span>
                            <span class='level is-marginless'>
                                <span class='level-left'>Downloaded: </span>
                                <span class='level-right'>" . human_filesize($users_data['downloaded']) . "</span>
                            </span>
                            <span class='level is-marginless'>
                                <span class='level-left'>Karma: </span>
                                <span class='level-right'>" . number_format($users_data['seedbonus']) . "</span>
                            </span>
                            <span class='level is-marginless'>
                                <span class='level-left'>Seeding: </span>
                                <span class='level-right'>" . number_format($peers['yes']) . "</span>
                            </span>
                            <span class='level is-marginless'>
                                <span class='level-left'>Leeching: </span>
                                <span class='level-right'>" . number_format($peers['no']) . '</span>
                            </span>
                        </div>
                    </div>
                </div>';
        $tooltip = "class='" . get_user_class_name($users_data['class'], true) . " dt-tooltipper-large' data-tooltip-content='#userid_{$users_data['id']}_tooltip'";
    } else {
        $tooltip = "class='" . get_user_class_name(($users_data['override_class'] != 255 ? $users_data['override_class'] : $users_data['class']), true) . "'";
    }

    $str = "
                <span>
                $tip
                <a href='{$site_config['baseurl']}/userdetails.php?id={$users_data['id']}' target='_blank'><span {$tooltip}>" . htmlsafechars($users_data['username']) . '</span></a>';

    if ($icons != false) {
        $str .= (isset($users_data['king']) && $users_data['king'] >= TIME_NOW ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'king.png" alt="King" title="King" />' : '');
        $str .= ($users_data['donor'] === 'yes' ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'star.png" alt="Donor" title="Donor" />' : '');
        $str .= ($users_data['warned'] >= 1 ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'alertred.png" alt="Warned" title="Warned" />' : '');
        $str .= ($users_data['leechwarn'] >= 1 ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'alertblue.png" alt="Leech Warned" title="Leech Warned" />' : '');
        $str .= ($users_data['enabled'] != 'yes' ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'disabled.gif" alt="Disabled" title="Disabled" />' : '');
        $str .= (isset($users_data['downloadpos']) && $users_data['downloadpos'] != 1 ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'downloadpos.gif" alt="Download Disabled" title="Download Disabled" />' : '');
        $str .= ($users_data['chatpost'] == 0 ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'warned.png" alt="No Chat" title="Shout disabled" />' : '');
        $str .= ($users_data['pirate'] != 0 ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'pirate.png" alt="Pirate" title="Pirate" />' : '');
        if (Christmas()) {
            $str .= (isset($users_data['gotgift']) && $users_data['gotgift'] === 'yes' ? '<img class="lazy tooltipper icon left5" data-src="' . $site_config['pic_baseurl'] . 'gift.png" alt="Christmas Gift" title="Has Claimed a Christmas Gift" />' : '');
        }
    }

    $str .= '
                </span>';

    return $str;
}

/**
 * @param $id
 *
 * @return bool
 */
function is_valid_id($id)
{
    return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

/**
 * @param $up
 * @param $down
 *
 * @return string
 */
function member_ratio($up, $down)
{
    switch (true) {
        case $down > 0 && $up > 0:
            $ratio = '<span style="color:' . get_ratio_color($up / $down) . ';">' . number_format($up / $down, 3) . '</span>';
            break;

        case $down > 0 && $up == 0:
            $ratio = '<span style="color:' . get_ratio_color(1 / $down) . ';">' . number_format(1 / $down, 3) . '</span>';
            break;

        case $down == 0 && $up > 0:
            $ratio = '<span style="color: ' . get_ratio_color($up) . ';">Inf</span>';
            break;

        default:
            $ratio = '---';
    }

    return $ratio;
}

/**
 * @param $ratio
 *
 * @return string|void
 */
function get_user_ratio_image($ratio)
{
    global $site_config;

    switch ($ratio) {
        case $ratio == 0:
            return;
            break;

        case $ratio < 0.6:
            return ' <img data-src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt=" Bad ratio :("  title=" Bad ratio :(" class="lazy tooltipper emoticon" />';
            break;

        case $ratio <= 0.7:
            return ' <img data-src="' . $site_config['pic_baseurl'] . 'smilies/weep.gif" alt=" Could be better"  title=" Could be better" class="lazy tooltipper emoticon" />';
            break;

        case $ratio <= 0.8:
            return ' <img data-src="' . $site_config['pic_baseurl'] . 'smilies/cry.gif" alt=" Getting there!" title=" Getting there!" class="lazy tooltipper emoticon" />';
            break;

        case $ratio <= 1.5:
            return ' <img data-src="' . $site_config['pic_baseurl'] . 'smilies/smile1.gif" alt=" Good Ratio :)" title=" Good Ratio :)" class="lazy tooltipper emoticon" />';
            break;

        case $ratio <= 2.0:
            return ' <img data-src="' . $site_config['pic_baseurl'] . 'smilies/grin.gif" alt=" Great Ratio :)" title=" Great Ratio :)" class="lazy tooltipper emoticon" />';
            break;

        case $ratio <= 3.0:
            return ' <img data-src="' . $site_config['pic_baseurl'] . 'smilies/w00t.gif" alt=" Wow! :D" title=" Wow! :D" class="lazy tooltipper emoticon" />';
            break;

        case $ratio <= 4.0:
            return ' <img data-src="' . $site_config['pic_baseurl'] . 'smilies/pimp.gif" alt=" Fa-boo Ratio!" title=" Fa-boo Ratio!" class="lazy tooltipper emoticon" />';
            break;

        case $ratio > 4.0:
            return ' <img data-src="' . $site_config['pic_baseurl'] . 'smilies/yahoo.gif" alt=" Great ratio :-D" title=" Great ratio :-D" class="lazy tooltipper emoticon" />';
            break;
    }

    return '';
}

/**
 * @param     $avatar
 * @param int $width
 *
 * @return string
 */
function get_avatar($avatar)
{
    require_once CLASS_DIR . 'class_user_options.php';
    global $CURUSER, $site_config, $user_stuffs;

    $avatar['anonymous'] = !empty($avatar['anonymous']) ? $avatar['anonymous'] : 'no';
    $avatar['offensive_avatar'] = !empty($avatar['offensive_avatar']) ? $avatar['offensive_avatar'] : 'no';

    if ($CURUSER['avatars'] === 'yes') {
        if ($avatar['anonymous'] === 'yes') {
            $avatar = "{$site_config['pic_baseurl']}anonymous_1.jpg";
        } elseif ($avatar['offensive_avatar'] === 'yes' && $CURUSER['view_offensive_avatar'] === 'no') {
            $avatar = "<img src='{$site_config['pic_baseurl']}fuzzybunny.gif' alt='avatar' class='avatar mw-150'>";
        } elseif (empty($avatar['avatar'])) {
            $avatar = "<img src='{$site_config['pic_baseurl']}forumicons/default_avatar.gif' alt='avatar' class='avatar mw-150'>";
        } else {
            $avatar = "<img src='" . htmlsafechars($avatar['avatar']) . "' alt='avatar' class='avatar mw-150'>";
        }

        return $avatar;
    }

    return null;
}

/**
 * @param $fo
 *
 * @return bool
 */
function blacklist($fo)
{
    global $site_config;
    $blacklist = file_exists($site_config['nameblacklist']) && is_array(unserialize(file_get_contents($site_config['nameblacklist']))) ? unserialize(file_get_contents($site_config['nameblacklist'])) : [];
    if (isset($blacklist[$fo]) && $blacklist[$fo] == 1) {
        return false;
    }

    return true;
}

/**
 * @param int $windows
 *
 * @return float
 */
function get_server_load($windows = 0)
{
    if (class_exists('COM')) {
        $wmi = new COM('WinMgmts:\\\\.');
        $cpus = $wmi->InstancesOf('Win32_Processor');
        $i = 1;
        // Use the while loop on PHP 4 and foreach on PHP 5
        //while ($cpu = $cpus->Next()) {
        foreach ($cpus as $cpu) {
            $cpu_stats = 0;
            $cpu_stats += $cpu->LoadPercentage;
            ++$i;
        }

        return round($cpu_stats / 4); // remove /4 for single processor systems
    }
}

/**
 * @param $the_names
 * @param $the_colors
 * @param $the_images
 *
 * @return string
 */
function get_cache_config_data($the_names, $the_colors, $the_images)
{
    $configfile = '';
    $the_names = str_replace(',', ",\n  ", trim($the_names, ','));
    $the_colors = str_replace(',', ",\n  ", trim($the_colors, ','));
    $the_images = str_replace(',', ",\n  ", trim($the_images, ','));
    $configfile .= "\n\n\n" . '$class_names = [
  ' . $the_names . ',
];';
    // adding class colors like in user_functions
    $configfile .= "\n\n\n" . '$class_colors = [
  ' . $the_colors . ',
];';
    // adding class pics like in user_functions
    $configfile .= "\n\n\n" . '$class_images = [
  ' . $the_images . ',
];';

    return $configfile;
}

/**
 * @param $post_id
 */
function clr_forums_cache($post_id)
{
    global $cache;

    $uclass = UC_MIN;
    while ($uclass <= UC_MAX) {
        $cache->delete('last_post_' . $post_id . '_' . $uclass);
        $cache->delete('sv_last_post_' . $post_id . '_' . $uclass);
        $cache->delete('last_posts_' . $uclass);
        ++$uclass;
    }
}

/**
 * @param $userid
 */
function clearUserCache($userid)
{
    global $cache;

    $cache->delete('peers_' . $userid);
    $cache->delete('user' . $userid);
    $cache->delete('useravatar_' . $userid);
    $cache->delete('inbox_' . $userid);
    $cache->delete('userstatus_' . $userid);
    $cache->delete('user_rep_' . $userid);
    $cache->delete('poll_votes_' . $userid);
    $cache->delete('userhnrs_' . $userid);
    $cache->delete('get_all_boxes_' . $userid);
    $cache->delete('insertJumpTo' . $userid);
    if ($username = get_one_row('users', 'username', 'WHERE id = ' . sqlesc($userid))) {
        $cache->delete('userclasses_' . $username);
        $cache->delete('users_names_' . $username);
    }
}
