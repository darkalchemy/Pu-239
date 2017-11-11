<?php
//=== Anonymous function
/**
 * @return mixed
 */
function get_anonymous()
{
    global $CURUSER;

    return $CURUSER['anonymous_until'];
}

//== + Parked function
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
 */
function autoshout($msg, $channel = 0, $ttl = 7200)
{
    global $site_config;
    require_once INCL_DIR . 'bbcode_functions.php';
    if (user_exists($site_config['chatBotID'])) {
        sql_query('INSERT INTO ajax_chat_messages (userID, userName, userRole, channel, dateTime, ip, text, ttl) VALUES (' . sqlesc($site_config['chatBotID']) . ', ' . sqlesc($site_config['chatBotName']) . ', 100, ' . sqlesc($channel) . ', NOW(), ' . sqlesc(ipToStorageFormat('127.0.0.1')) . ', ' . sqlesc($msg) . ', ' . sqlesc($ttl) . ')') or sqlerr(__FILE__, __LINE__);
    }
}

//== Get rep by CF
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
    global $site_config, $CURUSER;
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
                if ($y > $user['reputation']) {
                    $user_reputation = $old;
                    break;
                }
                $old = $x;
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
        /**
         *
         */
        // shiny, shiny, shiny boots...
        // ok, now we can work out the number of bars/pippy things
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
                    $posneg .= "<img src='{$site_config['pic_base_url']}rep/reputation_$rep_img_2.gif' alt=\"Reputation Power $rep_power\n" . htmlsafechars($user['username']) . " $rep_level\" title=\"Reputation Power $rep_power " . htmlsafechars($user['username']) . " $rep_level\" />";
                } else {
                    $posneg .= "<img src='{$site_config['pic_base_url']}rep/reputation_$rep_img.gif' alt=\"Reputation Power $rep_power\n" . htmlsafechars($user['username']) . " $rep_level\" title=\"Reputation Power $rep_power " . htmlsafechars($user['username']) . " $rep_level\" />";
                }
            }
        }
        // now decide the locale
        if ($mode != '') {
            return 'Rep: ' . $posneg . "<br><br><a href='javascript:;' onclick=\"PopUp('{$site_config['baseurl']}/reputation.php?pid=" . ($post_id != 0 ? (int)$post_id : (int)$user['id']) . '&amp;locale=' . $mode . "','Reputation',400,300,1,1);\"><img src='{$site_config['pic_base_url']}forumicons/giverep.jpg' border='0' alt='Add reputation:: " . htmlsafechars($user['username']) . "' title='Add reputation:: " . htmlsafechars($user['username']) . "' /></a>";
        } else {
            return ' ' . $posneg;
        }
    } // END IF ONLINE
    // default
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
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/yay.gif" alt="Yay" title="Yay" />';
            break;

        case $ratio_to_check >= 4:
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/pimp.gif" alt="Pimp" title="Pimp" />';
            break;

        case $ratio_to_check >= 3:
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/w00t.gif" alt="W00t" title="W00t" />';
            break;

        case $ratio_to_check >= 2:
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/grin.gif" alt="Grin" title="Grin" />';
            break;

        case $ratio_to_check >= 1.5:
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/evo.gif" alt="Evo" title="Evo" />';
            break;

        case $ratio_to_check >= 1:
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/smile1.gif" alt="Smile" title="Smile" />';
            break;

        case $ratio_to_check >= 0.5:
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/noexpression.gif" alt="Blank" title="Blank" />';
            break;

        case $ratio_to_check >= 0.25:
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/cry.gif" alt="Cry" title="Cry" />';
            break;

        case $ratio_to_check < 0.25:
            return '<img src="' . $site_config['pic_base_url'] . 'smilies/shit.gif" alt="Shit" title="Shit" />';
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
    $class = (int)$class;
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
    $class = (int)$class;
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
    $class = (int)$class;
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
    $class = (int)$class;

    return (bool)($class >= UC_MIN && $class <= UC_MAX);
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
    $minclass = (int)$min;
    $maxclass = (int)$max;
    if (!isset($CURUSER)) {
        return false;
    }
    if (!valid_class($minclass) || !valid_class($maxclass)) {
        return false;
    }
    if ($maxclass < $minclass) {
        return false;
    }

    return (bool)($CURUSER['class'] >= $minclass && $CURUSER['class'] <= $maxclass);
}

/**
 * @param      $user_id
 * @param bool $icons
 * @param bool $tooltipper
 *
 * @return string|void
 */
function format_username($user_id, $icons = true, $tooltipper = true)
{
    global $site_config, $mc1;
    if (empty($user_id)) {
        return;
    }
    $user_id = is_array($user_id) && !empty($user_id['id']) ? (int)$user_id['id'] : (int)$user_id;
    if (!is_array($user_id) && is_numeric($user_id)) {
        if (($user = $mc1->get_value('user_icons_' . $user_id)) === false) {
            $res = sql_query("SELECT gotgift, gender, id, class, username, donor, title, suspended, warned, leechwarn, downloadpos, chatpost, pirate, king, enabled, perms, avatar
                                FROM users
                                WHERE id = " . sqlesc($user_id)) or sqlerr(__FILE__, __LINE__);
            $user = mysqli_fetch_assoc($res);
            $mc1->cache_value('user_icons_' . $user_id, $user, 60);
        }
    } else {
        file_put_contents('/var/log/nginx/format_username.log', json_encode(debug_backtrace()) . PHP_EOL, FILE_APPEND);
        return '';
    }

    $avatar = !empty($user['avatar']) ? "<img src='{$user['avatar']}' class='avatar' />" : "<img src='./images/forumicons/default_avatar.gif' class='avatar' />";
    $tip = $tooltip = '';
    if ($tooltipper) {
        $tip = "
                        <div class='tooltip_templates'>
                            <span id='id_{$user['id']}_tooltip' class='is-flex tooltip'>
                                <div class='right20'>
                                    {$avatar}
                                </div>
                                <div style='min-width: 150px; align: left;'>
                                     <span style='color:#" . get_user_class_color($user['class']) . ";'>" . htmlsafechars($user['username']) . "</span>
                                </div>
                            </span>
                        </div>";
        $tooltip = "class='dt-tooltipper-large' data-tooltip-content='#id_{$user['id']}_tooltip' ";
    }

    $user['class'] = (int)$user['class'];
    if ($user['id'] == 0) {
        return 'System';
    } elseif ($user['username'] == '') {
        return 'unknown[' . $user['id'] . ']';
    }

    $str = "
            <span>
                <a class='user_{$user['id']}' href='./userdetails.php?id={$user['id']}' target='_blank'>
                    <span {$tooltip}style='color:#" . get_user_class_color($user['class']) . ";'>" . htmlsafechars($user['username']) . "$tip</span>
                </a>";

    if ($icons != false) {
        $str .= (isset($user['king']) && $user['king'] >= TIME_NOW ? '<img class="tooltipper" src="' . $site_config['pic_base_url'] . 'king.png" alt="King" title="King" width="14px" height="14px" />' : '');
        $str .= ($user['donor'] == 'yes' ? '<img class="tooltipper" src="' . $site_config['pic_base_url'] . 'star.png" alt="Donor" title="Donor" width="14px" height="14px" />' : '');
        $str .= ($user['warned'] >= 1 ? '<img class="tooltipper" src="' . $site_config['pic_base_url'] . 'alertred.png" alt="Warned" title="Warned" width="14px" height="14px" />' : '');
        $str .= ($user['leechwarn'] >= 1 ? '<img class="tooltipper" src="' . $site_config['pic_base_url'] . 'alertblue.png" alt="Leech Warned" title="Leech Warned" width="14px" height="14px" />' : '');
        $str .= ($user['enabled'] != 'yes' ? '<img class="tooltipper" src="' . $site_config['pic_base_url'] . 'disabled.gif" alt="Disabled" title="Disabled" width="14px" height="14px" />' : '');
        $str .= (isset($user['downloadpos']) && $user['downloadpos'] != 1 ? '<img class="tooltipper" src="' . $site_config['pic_base_url'] . 'downloadpos.gif" alt="Download Disabled" title="Download Disabled" width="14px" height="14px" />' : '');
        $str .= ($user['chatpost'] == 0 ? '<img class="tooltipper" src="' . $site_config['pic_base_url'] . 'warned.png" alt="No Chat" title="Shout disabled" width="14px" height="14px" />' : '');
        $str .= ($user['pirate'] != 0 ? '<img class="tooltipper" src="' . $site_config['pic_base_url'] . 'pirate.png" alt="Pirate" title="Pirate" width="14px" height="14px" />' : '');
        $str .= (isset($user['gotgift']) && $user['gotgift'] == 'yes' ? '<img class="tooltipper" height="16px" src="' . $site_config['pic_base_url'] . 'gift.png" alt="Christmas Gift" title="Has Claimed a Christmas Gift" />' : '');
    }
    $str .= '
            </span>';

    return trim($str);
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
            $ratio = '<span style="color: ' . get_ratio_color($up / 1) . ';">Inf</span>';
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
            return ' <img src="' . $site_config['pic_base_url'] . 'smilies/shit.gif" alt=" Bad ratio :("  title=" Bad ratio :("/>';
            break;

        case $ratio <= 0.7:
            return ' <img src="' . $site_config['pic_base_url'] . 'smilies/weep.gif" alt=" Could be better"  title=" Could be better" />';
            break;

        case $ratio <= 0.8:
            return ' <img src="' . $site_config['pic_base_url'] . 'smilies/cry.gif" alt=" Getting there!" title=" Getting there!" />';
            break;

        case $ratio <= 1.5:
            return ' <img src="' . $site_config['pic_base_url'] . 'smilies/smile1.gif" alt=" Good Ratio :)" title=" Good Ratio :)" />';
            break;

        case $ratio <= 2.0:
            return ' <img src="' . $site_config['pic_base_url'] . 'smilies/grin.gif" alt=" Great Ratio :)" title=" Great Ratio :)" />';
            break;

        case $ratio <= 3.0:
            return ' <img src="' . $site_config['pic_base_url'] . 'smilies/w00t.gif" alt=" Wow! :D" title=" Wow! :D" />';
            break;

        case $ratio <= 4.0:
            return ' <img src="' . $site_config['pic_base_url'] . 'smilies/pimp.gif" alt=" Fa-boo Ratio!" title=" Fa-boo Ratio!" />';
            break;

        case $ratio > 4.0:
            return ' <img src="' . $site_config['pic_base_url'] . 'smilies/yahoo.gif" alt=" Great ratio :-D" title=" Great ratio :-D" />';
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
function avatar_stuff($avatar, $width = 80)
{
    global $CURUSER, $site_config;
    $avatar_show = ($CURUSER['avatars'] == 'no' ? '' : (!$avatar['avatar'] ? '<img style="max-width:' . $width . 'px;" src="' . $site_config['pic_base_url'] . 'forumicons/default_avatar.gif" alt="avatar" />' : (($avatar['offensive_avatar'] === 'yes' && $CURUSER['view_offensive_avatar'] === 'no') ? '<img style="max-width:' . $width . 'px;" src="' . $site_config['pic_base_url'] . 'fuzzybunny.gif" alt="avatar" />' : '<img style="max-width:' . $width . 'px;" src="' . htmlsafechars($avatar['avatar']) . '" alt="avatar" />')));

    return $avatar_show;
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
    $the_names = str_replace(',', ",\n", trim($the_names, ','));
    $the_colors = str_replace(',', ",\n", trim($the_colors, ','));
    $the_images = str_replace(',', ",\n", trim($the_images, ','));
    $configfile .= "\n\n\n" . '$class_names = array(
  ' . $the_names . '
  );';
    // adding class colors like in user_functions
    $configfile .= "\n\n\n" . '$class_colors = array(
  ' . $the_colors . '
  );';
    // adding class pics like in user_functions
    $configfile .= "\n\n\n" . '$class_images = array(
  ' . $the_images . '
  );';

    return $configfile;
}

/**
 * @param $post_id
 */
function clr_forums_cache($post_id)
{
    global $mc1, $site_config;
    $uclass = UC_MIN;
    while ($uclass <= UC_MAX) {
        $mc1->delete_value('last_post_' . $post_id . '_' . $uclass);
        $mc1->delete_value('sv_last_post_' . $post_id . '_' . $uclass);
        $mc1->delete_value('last_posts_' . $uclass);
        ++$uclass;
    }
}
