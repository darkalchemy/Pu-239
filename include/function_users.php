<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Peer;
use Pu239\User;
use Spatie\Image\Exceptions\InvalidManipulation;

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
 * @param string $msg
 * @param int    $channel
 * @param int    $ttl
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function autoshout(string $msg, int $channel = 0, int $ttl = 7200)
{
    global $container, $site_config;

    if (user_exists($site_config['chatbot']['id'])) {
        $values = [
            'userID' => $site_config['chatbot']['id'],
            'userName' => $site_config['chatbot']['name'],
            'userRole' => 100,
            'channel' => $channel,
            'dateTime' => gmdate('Y-m-d H:i:s', TIME_NOW),
            'text' => $msg,
            'ttl' => $ttl,
        ];

        $fluent = $container->get(Database::class);
        $fluent->insertInto('ajax_chat_messages')
               ->values($values)
               ->execute();
    }
}

/**
 * @param array  $user
 * @param string $mode
 * @param bool   $rep_is_on
 * @param int    $post_id
 * @param bool   $anonymous
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return string
 */
function get_reputation(array $user, string $mode = '', bool $rep_is_on = true, int $post_id = 0, bool $anonymous = false)
{
    global $site_config;

    if ($rep_is_on) {
        include CACHE_DIR . 'rep_cache.php';
        require_once INCL_DIR . 'function_html.php';
        $image = placeholder_image();

        if (!isset($reputations) || !is_array($reputations) || count($reputations) < 1) {
            return '<span title="Cache doesn\'t exist or zero length" class="tooltipper">Reputation: Offline</span>';
        }
        $user['g_rep_hide'] = isset($user['g_rep_hide']) ? $user['g_rep_hide'] : 0;
        $user['username'] = $anonymous || $user['anonymous'] === 'yes' ? 'Anonymous' : $user['username'];
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
        $rep_power = $user['reputation'];
        $posneg = $rep_img = $rep_img_2 = '';
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
                $pips = 12;
        }
        $rep_bar = intval($rep_power / 100);
        if ($rep_bar > 10) {
            $rep_bar = 10;
        }
        if ($user['g_rep_hide']) {
            $posneg = 'off';
            $rep_level = 'rep_off';
        } else {
            $rep_level = isset($user_reputation) ? $user_reputation : 'rep_undefined';

            for ($i = 0; $i <= $rep_bar; ++$i) {
                $posneg .= "<span title='Reputation Power $rep_power<br> " . htmlsafechars($user['username']) . " $rep_level' class='tooltipper'>";
                if ($i >= 5) {
                    $posneg .= "<img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}rep/reputation_{$rep_img_2}.gif' alt=\"Reputation Power $rep_power " . htmlsafechars($user['username']) . " $rep_level\" class='lazy'>";
                } else {
                    $posneg .= "<img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}rep/reputation_{$rep_img}.gif' alt=\"Reputation Power $rep_power " . htmlsafechars($user['username']) . " $rep_level\" class='lazy'>";
                }
                $posneg .= '</span>';
            }
        }

        if (!empty($mode)) {
            return '<div>Rep: ' . $posneg . "</div><span><a href='javascript:;' onclick=\"PopUp('{$site_config['paths']['baseurl']}/reputation.php?pid=" . ($post_id != 0 ? (int) $post_id : (int) $user['id']) . '&amp;locale=' . $mode . "','Reputation',400,300,1,1);\" title='Add reputation: " . htmlsafechars($user['username']) . "' class='tooltipper'><i class='icon-ok icon has-text-success'></i></a></span>";
        } else {
            return ' ' . $posneg;
        }
    }

    return '<span title="Set offline by admin setting" class="tooltipper">Rep System Offline</span>';
}

/**
 * @param float $ratio
 *
 * @return string
 */
function get_ratio_color(float $ratio)
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
 * @param float $ratio
 *
 * @return string
 */
function get_slr_color(float $ratio)
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
 * @param float $ratio_to_check
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return string|null
 */
function ratio_image_machine(float $ratio_to_check)
{
    global $site_config;

    $image = placeholder_image();
    switch ($ratio_to_check) {
        case $ratio_to_check >= 5:
            return '<span class="tooltipper" title="Yay"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/yay.gif" alt="Yay" class="lazy emoticon"></span>';

        case $ratio_to_check >= 4:
            return '<span class="tooltipper" title="Pimp"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/pimp.gif" alt="Pimp" class="lazy emoticon"></span>';

        case $ratio_to_check >= 3:
            return '<span class="tooltipper" title="W00t"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/w00t.gif" alt="W00t" class="lazy emoticon"></span>';

        case $ratio_to_check >= 2:
            return '<span class="tooltipper" title="Grin"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/grin.gif" alt="Grin" class="lazy emoticon"></span>';

        case $ratio_to_check >= 1.5:
            return '<span class="tooltipper" title="Evo"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/evo.gif" alt="Evo" class="lazy emoticon"></span>';

        case $ratio_to_check >= 1:
            return '<span class="tooltipper" title="Smile"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/smile1.gif" alt="Smile" class="lazy emoticon"></span>';

        case $ratio_to_check >= 0.5:
            return '<span class="tooltipper" title="Blank"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/noexpression.gif" alt="Blank" class="lazy emoticon"></span>';

        case $ratio_to_check >= 0.25:
            return '<span class="tooltipper" title="Cry"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/cry.gif" alt="Cry" class="lazy emoticon"></span>';

        case $ratio_to_check < 0.25:
            return '<span class="tooltipper" title="Shit"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt="Shit" class="lazy emoticon"></span>';
    }

    return null;
}

/**
 * @param int  $class
 * @param bool $to_lower
 *
 * @return string
 */
function get_user_class_name(int $class, bool $to_lower = false)
{
    global $site_config;

    if (!valid_class($class)) {
        return '';
    }
    if (isset($site_config['class_names'][$class]) && $to_lower) {
        return strtolower(str_replace(' ', '_', $site_config['class_names'][$class]));
    } elseif (isset($site_config['class_names'][$class])) {
        return $site_config['class_names'][$class];
    } else {
        return '';
    }
}

/**
 * @param int $class
 *
 * @return string
 */
function get_user_class_color(int $class)
{
    global $site_config;

    if (!valid_class($class)) {
        return '';
    }
    if (isset($site_config['class_colors'][$class])) {
        return $site_config['class_colors'][$class];
    } else {
        return '';
    }
}

/**
 * @param int $class
 *
 * @return string
 */
function get_user_class_image(int $class)
{
    global $site_config;

    if (!valid_class($class)) {
        return '';
    }
    if (isset($site_config['class_images'][$class])) {
        return $site_config['class_images'][$class];
    } else {
        return '';
    }
}

/**
 * @param int $class
 *
 * @return bool
 */
function valid_class(int $class)
{
    return (bool) ($class >= UC_MIN && $class <= UC_MAX);
}

/**
 * @param int $minclass
 * @param int $maxclass
 *
 * @return bool
 */
function min_class(int $minclass = UC_MIN, int $maxclass = UC_MAX)
{
    global $CURUSER;

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
 * @param bool $tag
 * @param bool $comma
 *
 * @throws Exception
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function format_username(int $user_id, $icons = true, $tooltipper = true, $tag = false, $comma = false)
{
    global $container, $site_config;

    $users_class = $container->get(User::class);
    $users_data = $users_class->getUserFromId($user_id);
    $peer = $container->get(Peer::class);
    $peers = $peer->getPeersFromUserId($user_id);
    $tag = $tag ? '@' : '';

    if ($users_data['id'] === 0) {
        return 'System';
    } elseif (empty($users_data['username'])) {
        return "<span class='has-text-danger'>unknown_id[$user_id]</span>";
    }
    $avatar = get_avatar($users_data);
    $tip = $tooltip = '';
    $uniqueid = uniqid();
    if ($tooltipper) {
        $tip = "
                <span class='tooltip_templates'>
                    <span id='$uniqueid' class='is-flex tooltip'>
                        <span class='right20'>{$avatar}</span>
                        <span style='min-width: 150px;'>
                            <span class='level is-marginless'>
                                <span class='level-left " . get_user_class_name((int) $users_data['class'], true) . "'>" . htmlsafechars($users_data['username']) . "</span>
                                <span class='level-right " . get_user_class_name((int) $users_data['class'], true) . "'>" . get_user_class_name((int) $users_data['class'], false) . "</span>
                            </span>
                            <span class='level is-marginless'>
                                <span class='level-left'>Last Seen: </span>
                                <span class='level-right'>" . get_date((int) $users_data['last_access'], (date('Ymd') == date('Ymd', $users_data['last_access']) ? 'TIME' : 'FORM'), 1, 0) . "</span>
                            </span>
                            <span class='level is-marginless'>
                                <span class='level-left'>Uploaded: </span>
                                <span class='level-right'>" . mksize($users_data['uploaded']) . "</span>
                            </span>
                            <span class='level is-marginless'>
                                <span class='level-left'>Downloaded: </span>
                                <span class='level-right'>" . mksize($users_data['downloaded']) . "</span>
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
                        </span>
                    </span>
                </span>';
        $tooltip = "class='" . get_user_class_name((int) $users_data['class'], true) . " dt-tooltipper-large' data-tooltip-content='#$uniqueid'";
    } else {
        $tooltip = "class='" . get_user_class_name((int) ($users_data['override_class'] != 255 ? $users_data['override_class'] : $users_data['class']), true) . "'";
    }

    $username = $users_data['enabled'] != 'yes' ? '<s>' . htmlsafechars($users_data['username']) . '</s>' : $tag . htmlsafechars($users_data['username']);
    $str = "
                <span>$tip<a href='{$site_config['paths']['baseurl']}/userdetails.php?id={$users_data['id']}' target='_blank'><span {$tooltip}>{$username}</span></a>";

    if ($icons != false) {
        require_once INCL_DIR . 'function_html.php';
        $image = placeholder_image();
        $str .= $users_data['donor'] === 'yes' ? '<span' . ($tooltipper ? ' class="tooltipper" title="Donor"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'star.png" alt="Donor"></span>' : '';
        $str .= $users_data['king'] >= TIME_NOW ? '<span' . ($tooltipper ? ' class="tooltipper" title="King"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'king.png" alt="King"></span>' : '';
        $str .= $users_data['pirate'] >= TIME_NOW ? '<span' . ($tooltipper ? ' class="tooltipper" title="Pirate"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'pirate.png" alt="Pirate"></span>' : '';
        $str .= $users_data['warned'] >= 1 ? '<span' . ($tooltipper ? ' class="tooltipper" title="Warned"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'alertred.png" alt="Warned"></span>' : '';
        $str .= $users_data['leechwarn'] >= 1 ? '<span' . ($tooltipper ? ' class="tooltipper" title="Leech Warned"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'alertblue.png" alt="Leech Warned"></span>' : '';
        $str .= $users_data['enabled'] != 'yes' ? '<span' . ($tooltipper ? ' class="tooltipper" title="Disabled"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'disabled.gif" alt="Disabled"></span>' : '';
        $str .= $users_data['downloadpos'] != 1 ? '<span' . ($tooltipper ? ' class="tooltipper" title="Download Disabled"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'downloadpos.gif" alt="Download Disabled"></span>' : '';
        $str .= $users_data['chatpost'] != 1 ? '<span' . ($tooltipper ? ' class="tooltipper" title="Shout Disabled"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'warned.png" alt="No Chat"></span>' : '';
        if (Christmas()) {
            $str .= isset($users_data['gotgift']) && $users_data['gotgift'] === 'yes' ? '<span' . ($tooltipper ? ' class="tooltipper" title="Has Claimed a Christmas Gift"' : '') . '><img class="lazy icon left5" src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'gift.png" alt="Christmas Gift"></span>' : '';
        }
    }

    $str .= $comma ? ', ' : '';
    $str .= '
                </span>';

    return preg_replace('/\s{2,}/', '', $str);
}

/**
 * @param int $id
 *
 * @return bool
 */
function is_valid_id(int $id)
{
    return $id > 0;
}

/**
 * @param float $up
 * @param float $down
 *
 * @return string
 */
function member_ratio(float $up, float $down)
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
 * @param float $ratio
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return string
 */
function get_user_ratio_image(float $ratio)
{
    global $site_config;

    require_once INCL_DIR . 'function_html.php';
    $image = placeholder_image();

    switch ($ratio) {
        case $ratio == 0:
            return '';

        case $ratio < 0.6:
            return '<span class="tooltipper" title="Bad ratio :("><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt="Bad ratio :("  class="lazy emoticon"></span>';

        case $ratio <= 0.7:
            return '<span class="tooltipper" title="Could be better"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/weep.gif" alt="Could be better" class="lazy emoticon"></span>';

        case $ratio <= 0.8:
            return '<span class="tooltipper" title="Getting there!"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/cry.gif" alt="Getting there!" class="lazy emoticon"></span>';

        case $ratio <= 1.5:
            return '<span class="tooltipper" title=" Good Ratio :)"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/smile1.gif" alt=" Good Ratio :)" title=" Good Ratio :)" class="lazy emoticon"></span>';

        case $ratio <= 2.0:
            return '<span class="tooltipper" title="Great Ratio :)"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/grin.gif" alt="Great Ratio :)" class="lazy emoticon"></span>';

        case $ratio <= 3.0:
            return '<span class="tooltipper" title="Wow! :D"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/w00t.gif" alt="Wow! :D" class="lazy emoticon"></span>';

        case $ratio <= 4.0:
            return '<span class="tooltipper" title="Fa-boo Ratio!"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/pimp.gif" alt="Fa-boo Ratio!" class="lazy emoticon"></span>';

        case $ratio > 4.0:
            return '<span class="tooltipper" title="Great ratio :-D"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'smilies/yahoo.gif" alt="Great ratio :-D" class="lazy emoticon"></span>';
    }

    return '';
}

/**
 * @param $avatar
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool|mixed|string|null
 */
function get_avatar($avatar)
{
    global $container, $CURUSER, $site_config;

    if (!isset($avatar['avatar']) && !empty($avatar['user'])) {
        $users_class = $container->get(User::class);
        $user = $users_class->getUserFromId($avatar['user']);
        $avatar = $user;
        unset($user);
    }

    $avatar['anonymous'] = !empty($avatar['anonymous']) ? $avatar['anonymous'] : 'no';
    $avatar['offensive_avatar'] = !empty($avatar['offensive_avatar']) ? $avatar['offensive_avatar'] : 'no';
    if (!empty($avatar['avatar']) && !preg_match('#' . $site_config['paths']['baseurl'] . '#', $avatar['avatar'])) {
        $avatar['avatar'] = url_proxy($avatar['avatar'], true, 150);
    }
    if ($CURUSER['avatars'] === 'yes') {
        if ($avatar['anonymous'] === 'yes') {
            $avatar = "{$site_config['paths']['images_baseurl']}anonymous_1.jpg";
        } elseif ($avatar['offensive_avatar'] === 'yes' && $CURUSER['view_offensive_avatar'] === 'no') {
            $avatar = "<img src='{$site_config['paths']['images_baseurl']}fuzzybunny.gif' alt='avatar' class='avatar mw-150'>";
        } elseif (empty($avatar['avatar'])) {
            $avatar = "<img src='{$site_config['paths']['images_baseurl']}forumicons/default_avatar.gif' alt='avatar' class='avatar mw-150'>";
        } else {
            $avatar = "<img src='" . htmlsafechars($avatar['avatar']) . "' alt='avatar' class='avatar mw-150'>";
        }

        return $avatar;
    }

    return null;
}

/**
 * @param string $fo
 *
 * @return bool
 */
function blacklist(string $fo)
{
    global $site_config;

    $badwords = explode('|', $site_config['site']['badwords']);
    $blacklist = file_exists($site_config['paths']['nameblacklist']) && is_array(unserialize(file_get_contents($site_config['paths']['nameblacklist']))) ? unserialize(file_get_contents($site_config['paths']['nameblacklist'])) : [];
    if (isset($blacklist[$fo]) && $blacklist[$fo] == 1 || in_array(strtolower($fo), $badwords)) {
        return false;
    }

    return true;
}

/**
 * @param int $post_id
 *
 * @throws DependencyException
 * @throws NotFoundException
 */
function clr_forums_cache(int $post_id)
{
    global $container;

    $cache = $container->get(Cache::class);
    $uclass = UC_MIN;
    while ($uclass <= UC_MAX) {
        $cache->deleteMulti([
            'forum_last_post_' . $post_id . '_' . $uclass,
            'sv_last_post_' . $post_id . '_' . $uclass,
            'last_posts_' . $uclass,
        ]);
        ++$uclass;
    }
}
