<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Mood;
use Pu239\User;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 * @param        $rows
 * @param string $variant
 *
 * @throws Exception
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 *
 * @return string|null
 */
function commenttable($rows, $variant = 'torrent')
{
    global $container, $CURUSER, $site_config;

    $users_class = $container->get(User::class);
    require_once INCL_DIR . 'function_users.php';
    require_once INCL_DIR . 'function_html.php';
    $lang = array_merge(load_language('torrenttable_functions'), load_language('forums_global'));
    $count = 0;
    $variant_options = [
        'torrent' => 'comment',
        'request' => 'requests',
        'offer' => 'offers',
        'usercomment' => 'usercomment',
    ];
    if (isset($variant_options[$variant])) {
        $type = $variant_options[$variant];
    } else {
        return null;
    }
    $extra_link = ($variant === 'request' ? '&amp;type=request' : ($variant === 'offer' ? '&amp;type=offer' : ''));
    $delete = ($variant === 'request' || $variant === 'offer') ? 'action=delete_comment' : 'action=delete';
    $htmlout = '';
    $i = 0;
    $variantc = $variant;
    foreach ($rows as $row) {
        $cid = $row['id'];
        if ($variant === 'torrent') {
            $variantc = 'comment';
        }
        $usersdata = $users_class->getUserFromId($row['user']);
        $this_text = '';
        $mood = $container->get(Mood::class);
        $moods = $mood->get();
        $moodname = (isset($moods['name'][$usersdata['mood']]) ? htmlsafechars($moods['name'][$usersdata['mood']]) : 'is feeling neutral');
        $moodpic = (isset($moods['image'][$usersdata['mood']]) ? htmlsafechars($moods['image'][$usersdata['mood']]) : 'noexpression.gif');
        $this_text .= "
            <div>
                <span class='level-left padding10'>#{$row['id']} {$lang['commenttable_by']} ";
        $likes = $att_str = '';
        $likers = $user_likes = [];
        if ($row['user_likes'] > 0) {
            $cache = $container->get(Cache::class);
            $user_likes = $cache->get("{$type}_user_likes_" . $cid);
            if ($user_likes === false || is_null($user_likes)) {
                $fluent = $container->get(Database::class);
                $likes = $fluent->from('likes')
                                ->select(null)
                                ->select('user_id')
                                ->where("{$variantc}_id = ?", $cid);
                foreach ($likes as $like) {
                    $user_likes[] = $like['user_id'];
                }
                $cache->set("{$type}_user_likes_" . $cid, $user_likes, 86400);
            }
            if ($user_likes) {
                foreach ($user_likes as $userid) {
                    $likers[] = format_username((int) $userid);
                }
                $likes = implode(', ', $likers);
                $count = count($user_likes);
            }
        }
        if (!empty($likes) && $count > 0) {
            if (in_array($CURUSER['id'], $user_likes)) {
                if ($count === 1) {
                    $att_str = "<span class='chg'>You like this</span>";
                } else {
                    $att_str = "<span class='chg'>you and " . ($count - 1) . ' other' . !plural($count - 1) . ' like' . plural($count - 1) . ' this</span>';
                }
            } else {
                $att_str = $likes . ' like' . plural($count) . ' this';
            }
        }
        $wht = $count > 0 && in_array($CURUSER['id'], $user_likes) ? 'unlike' : 'like';
        if (isset($row['user'])) {
            if ($row['anonymous'] === '1') {
                $this_text .= $CURUSER['class'] >= UC_STAFF ? get_anonymous_name() . ' - Posted by: ' . format_username((int) $row['user']) : get_anonymous_name();
            } else {
                $title = empty($usersdata['title']) ? get_user_class_name((int) $usersdata['class']) : htmlsafechars($usersdata['title']);
                $this_text .= "<span class='left5 tooltipper' title='$title'>" . format_username((int) $row['user']) . '</span>';
                $this_text .= '
                    <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);" class="left5">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" class="tooltipper" title="' . ($row['anonymous'] === '1' ? '<i>' . get_anonymous_name() . '</i>' : htmlsafechars($usersdata['username'])) . ' ' . $moodname . '!">
                    </a>';
            }
        } else {
            $this_text .= '<i>(' . $lang['commenttable_orphaned'] . ')</i></a>';
        }
        $this_text .= "<span class='left5'>" . get_date((int) $row['added'], '') . '</span>';
        $row['id'] = (int) $row['id'];
        $tid = !empty($row[$variant]) ? "&amp;tid={$row[$variant]}" : '';
        $this_text .= ($row['user'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['paths']['baseurl']}/{$type}.php?action=edit&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_edit']}</a>" : '') . ($CURUSER['class'] >= UC_VIP ? "
                    <a href='{$site_config['paths']['baseurl']}/report.php?type=Comment&amp;id={$row['id']}' class='button is-small left10'>Report this Comment</a>" : '') . ($CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['paths']['baseurl']}/{$type}.php?{$delete}&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_delete']}</a>" : '') . ($row['editedby'] && $CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['paths']['baseurl']}/{$type}.php?action=vieworiginal&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_view_original']}</a>" : '') . "
                    <span data-id='{$cid}' data-type='{$variant}' class='mlike button is-small left10'>" . ucfirst($wht) . "</span>
                    <span class='tot-{$cid} left10'>{$att_str}</span>
                </span>
            </div>";
        $avatar = get_avatar($row);
        $text = format_comment($row['text']);
        if ($row['editedby']) {
            $text = "
            <div class='flex-vertical comments h-100 padding10'>
                <div>$text</div>
                <div class='size_3'>{$lang['commenttable_last_edited_by']} " . format_username((int) $row['editedby']) . " {$lang['commenttable_last_edited_at']} " . get_date((int) $row['editedat'], 'DATE') . '</div>
            </div>';
        }
        $top = $i++ >= 1 ? 'top20' : '';
        $image = placeholder_image();
        $user = $users_class->getUserFromId($row['user']);
        $member_reputation = !empty($usersdata['username']) ? get_reputation($user, 'comments', true, 0, ($row['anonymous']) === '1' ? true : false) : '';
        if ($variant === 'request' || $variant === 'offer') {
            $htmlout .= format_table_no_border($row, $image, $this_text, $avatar, $CURUSER, $usersdata, $text, $member_reputation, $lang);
        } else {
            $htmlout .= format_table_border($row, $image, $this_text, $avatar, $CURUSER, $usersdata, $text, $top, $member_reputation, $lang);
        }
    }

    return $htmlout;
}

function format_table_border($row, $image, $this_text, $avatar, $CURUSER, $usersdata, $text, $top, $member_reputation, $lang)
{
    global $site_config;

    return main_div("
            <a id='comm{$row['id']}'></a>
            $this_text
            <div class='w-100 padding10'>
                <div class='columns is-marginless'>
                    <div class='column round10 bg-02 is-2-widescreen is-3-desktop is-4-tablet is-12-mobile has-text-centered'>
                        " . $avatar . '<br>' . ($row['anonymous'] === '1' ? '<i>' . get_anonymous_name() . '</i>' : format_username((int) $row['user'])) . ($row['anonymous'] === '1' || empty($usersdata['title']) ? '' : '<br><span style=" font-size: xx-small;">[' . htmlsafechars($usersdata['title']) . ']</span>') . '<br>
                        <span>' . ($row['anonymous'] === '1' ? '' : get_user_class_name((int) $usersdata['class'])) . '</span><br>
                        ' . ($usersdata['last_access'] > (TIME_NOW - 300) && get_anonymous($usersdata['id']) ? ' <img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/online.gif" alt="Online" title="Online" class="tooltipper icon is-small lazy"> Online' : ' <img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/offline.gif" alt="' . $lang['fe_offline'] . '" title="' . $lang['fe_offline'] . '" class="tooltipper icon is-small lazy"> ' . $lang['fe_offline'] . '') . '<br>' . $lang['fe_karma'] . ': ' . number_format((float) $usersdata['seedbonus']) . '<br>' . $member_reputation . '<br>' . (!empty($usersdata['website']) ? ' <a href="' . htmlsafechars($usersdata['website']) . '" target="_blank" title="' . $lang['fe_click_to_go_to_website'] . '"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/website.gif" alt="website" class="tooltipper emoticon lazy"></a> ' : '') . ($usersdata['show_email'] === 'yes' ? ' <a href="mailto:' . htmlsafechars($usersdata['email']) . '"  title="' . $lang['fe_click_to_email'] . '" target="_blank"><i class="icon-mail icon tooltipper" aria-hidden="true" title="email"><i></a>' : '') . ($CURUSER['class'] >= UC_STAFF && !empty($usersdata['ip']) ? '
                        <ul class="level-center">
                            <li class="margin10"><a href="' . url_proxy('https://ws.arin.net/?queryinput=' . htmlsafechars($usersdata['ip'])) . '" title="' . $lang['vt_whois_to_find_isp_info'] . '" target="_blank" class="button is-small">' . $lang['vt_ip_whois'] . '</a></li>
                        </ul>' : '') . "
                    </div>
                    <div class='column round10 bg-02 left10'>
                        $text
                    </div>
                </div>
            </div>", $top);
}

function format_table_no_border($row, $image, $this_text, $avatar, $CURUSER, $usersdata, $text, $member_reputation, $lang)
{
    global $site_config;

    return "
        <div class='columns bg-03 top20 round10'>
            <div class='column'>
            <a id='comm{$row['id']}'></a>
            $this_text
            <div class='w-100 padding10'>
                <div class='columns is-marginless'>
                    <div class='column round10 bg-02 is-2-widescreen is-3-desktop is-4-tablet is-12-mobile has-text-centered'>
                        " . $avatar . '<br>' . ($row['anonymous'] === '1' ? '<i>' . get_anonymous_name() . '</i>' : format_username((int) $row['user'])) . ($row['anonymous'] === '1' || empty($usersdata['title']) ? '' : '<br><span style=" font-size: xx-small;">[' . htmlsafechars($usersdata['title']) . ']</span>') . '<br>
                        <span>' . ($row['anonymous'] === '1' ? '' : get_user_class_name((int) $usersdata['class'])) . '</span><br>
                        ' . ($usersdata['last_access'] > (TIME_NOW - 300) && get_anonymous($usersdata['id']) ? ' <img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/online.gif" alt="Online" title="Online" class="tooltipper icon is-small lazy"> Online' : ' <img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/offline.gif" alt="' . $lang['fe_offline'] . '" title="' . $lang['fe_offline'] . '" class="tooltipper icon is-small lazy"> ' . $lang['fe_offline'] . '') . '<br>' . $lang['fe_karma'] . ': ' . number_format((float) $usersdata['seedbonus']) . '<br>' . $member_reputation . '<br>' . (!empty($usersdata['website']) ? ' <a href="' . htmlsafechars($usersdata['website']) . '" target="_blank" title="' . $lang['fe_click_to_go_to_website'] . '"><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/website.gif" alt="website" class="tooltipper emoticon lazy"></a> ' : '') . ($usersdata['show_email'] === 'yes' ? ' <a href="mailto:' . htmlsafechars($usersdata['email']) . '"  title="' . $lang['fe_click_to_email'] . '" target="_blank"><i class="icon-mail icon tooltipper" aria-hidden="true" title="email"><i></a>' : '') . ($CURUSER['class'] >= UC_STAFF && !empty($usersdata['ip']) ? '
                        <ul class="level-center">
                            <li class="margin10"><a href="' . url_proxy('https://ws.arin.net/?queryinput=' . htmlsafechars($usersdata['ip'])) . '" title="' . $lang['vt_whois_to_find_isp_info'] . '" target="_blank" class="button is-small">' . $lang['vt_ip_whois'] . '</a></li>
                        </ul>' : '') . "
                    </div>
                    <div class='column round10 bg-02 left10'>
                        $text
                    </div>
                </div>
            </div>
            </div>
        </div>";
}
