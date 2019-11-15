<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$input = array_merge($_GET, $_POST);
$input['mode'] = isset($input['mode']) ? $input['mode'] : '';
$reputationid = 0;
$time_offset = 0;
$a = explode(',', gmdate('Y,n,j,G,i,s', TIME_NOW + $time_offset));
$now_date = [
    'year' => $a[0],
    'mon' => $a[1],
    'mday' => $a[2],
    'hours' => $a[3],
    'minutes' => $a[4],
    'seconds' => $a[5],
];
switch ($input['mode']) {
    case 'modify':
        show_level($input);
        break;

    case 'add':
        show_form($input, 'new');
        break;

    case 'doadd':
        do_update($input, 'new');
        break;

    case 'edit':
        show_form($input, 'edit');
        break;

    case 'doedit':
        do_update($input, 'edit');
        break;

    case 'doupdate':
        do_update($input, '');
        break;

    case 'dodelete':
        do_delete($input);
        break;

    case 'list':
        view_list($now_date, $input, $time_offset);
        break;

    case 'editrep':
        //show_form_rep('edit');
        show_form_rep($input);
        break;

    case 'doeditrep':
        do_edit_rep($input);
        break;

    case 'dodelrep':
        do_delete_rep($input);
        break;

    default:
        show_level($input);
        break;
}

/**
 * @param array $input
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws Exception
 */
function show_level(array $input)
{
    global $site_config;

    $title = _('User Reputation Manager - Overview');
    $html = '';
    $query = sql_query('SELECT * FROM reputationlevel ORDER BY minimumreputation') or sqlerr(__FILE__, __LINE__);
    if (!mysqli_num_rows($query)) {
        do_update($input, 'new');

        return;
    }
    $html .= "
        <h1 class='has-text-centered'>" . _('User Reputation Manager') . "</h1>
        <p class='margin20'>" . _('On this page you can modify the minimum amount required for each reputation level. Make sure you press Update Minimum Levels to save your changes. You cannot set the same minimum amount to more than one level.') . '<br>' . _('From here you can also choose to edit or remove any single level. Click the Edit link to modify the Level description (see Editing a Reputation Level) or click Remove to delete a level. If you remove a level or modify the minimum reputation needed to be at a level, all users will be updated to reflect their new level if necessary.') . "</p>
        <div class='has-text-centered bottom20'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reputation_ad&amp;mode=list'>
                <span class='button is-small has-text-black'>
                    " . _('View comments') . '
                </span>
            </a>
        </div>';
    $html .= "<form action='{$_SERVER['PHP_SELF']}?tool=reputation_ad' name='show_rep_form' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                <input name='mode' value='doupdate' type='hidden'>";
    $heading = '
        <tr>
            <th>' . _('ID') . '</th>
            <th>' . _('Reputation Level') . '</th>
            <th>' . _('Minimum Reputation Level') . '</th>
            <th>' . _('Controls') . '</th>
        </tr>';
    $body = '';
    while ($res = mysqli_fetch_assoc($query)) {
        $body .= "
        <tr>
            <td>#{$res['reputationlevelid']}</td>
            <td>" . _fe('User <b>{0}</b>', format_comment($res['level'])) . "</b></td>
            <td><input type='text' name='reputation[" . $res['reputationlevelid'] . "]' value='" . $res['minimumreputation'] . "'></td>
            <td>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reputation_ad&amp;mode=edit&amp;reputationlevelid=" . $res['reputationlevelid'] . "'>
                    <i class='icon-edit icon has-text-info' aria-hidden='true'></i>
                </a>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reputation_ad&amp;mode=dodelete&amp;reputationlevelid=" . $res['reputationlevelid'] . "'>
                    <i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i>
                </a>
            </td>
        </tr>";
    }
    $body .= "
        <tr>
            <td colspan='4' class='has-text-centered'>
                <input type='submit' value='" . _('Update') . "' accesskey='s' class='button is-small'>
                <input type='reset' value='" . _('Reset') . "' accesskey='r' class='button is-small'>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reputation_ad&amp;mode=add'>
                    <span class='button is-small has-text-black'>
                        " . _('Add New') . '
                    </span>
                </a>
            </td>
        </tr>';

    $html .= main_table($body, $heading);
    $html .= '</form>';
    html_out($html, $title);
}

/**
 * @param array  $input
 * @param string $type
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws Exception
 */
function show_form(array $input, string $type)
{
    $html = _('This allows you to add a new reputation level or edit an existing reputation level.');
    $res = [];
    if ($type === 'edit') {
        $query = sql_query('SELECT * FROM reputationlevel WHERE reputationlevelid = ' . (int) $input['reputationlevelid']) or sqlerr(__LINE__, __FILE__);
        if (!$res = mysqli_fetch_assoc($query)) {
            stderr(_('Error'), _('Invalid ID.'));
        }
        $title = _('Edit Reputation Level');
        $html .= '<br>' . _fe('{0} (ID: #{1})', format_comment($res['level']), $res['reputationlevelid']) . '<br>';
        $button = _('Update');
        $extra = "<input type='button' class='button is-small' value='" . _('Back') . "' accesskey='b' class='button is-small' onclick='history.back()'>";
        $mode = 'doedit';
    } else {
        $title = _('Add New Reputation Level');
        $button = _('Save');
        $mode = 'doadd';
        $extra = "<input type='button' value='" . _('Back') . "' accesskey='b' class='button is-small' onclick='history.back()'>";
    }
    $replevid = isset($res['reputationlevelid']) ? $res['reputationlevelid'] : '';
    $replevel = isset($res['level']) ? $res['level'] : '';
    $minrep = isset($res['minimumreputation']) ? $res['minimumreputation'] : '';
    $html .= "<form action='staffpanel.php?tool=reputation_ad' name='show_rep_form' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                <input name='reputationlevelid' value='{$replevid}' type='hidden'>
                <input name='mode' value='{$mode}' type='hidden'>";
    $html .= "<h2>$title</h2><table><tr>
        <td>&#160;</td>
        <td>&#160;</td></tr>";
    $html .= '<tr><td>' . _('Level Description') . "<div class='desctext'>" . _('This is what is displayed for the user when their reputation points are above the amount entered as the minimum.') . '</div></td>';
    $html .= "<td><input type='text' name='level' value=\"{$replevel}\" maxlength='250'></td></tr>";
    $html .= '<tr><td>' . _('Minimum amount of reputation points required for this level') . '<div>' . _("This can be a positive or a negative amount. When the user's reputation points reaches this amount, the above description will be displayed.") . '</div></td>';
    $html .= "<td><input type='text' name='minimumreputation' value=\"{$minrep}\" maxlength='10'></td></tr>";
    $html .= "<tr><td colspan='2' class='has-text-centered'><input type='submit' value='$button' accesskey='s' class='button is-small'> <input type='reset' value='" . _('Reset') . "' accesskey='r' class='button is-small'> $extra</td></tr>";
    $html .= '</table>';
    $html .= '</form>';
    html_out($html, $title);
}

/**
 * @param array  $input
 * @param string $type
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws Exception
 */
function do_update(array $input, string $type)
{
    $minrep = $level = $redirect = '';
    if ($type != '') {
        $level = strip_tags($input['level']);
        $level = trim($level);
        if ((strlen($input['level']) < 2) || ($level == '')) {
            stderr(_('Error'), _('The text you entered was too short.'));
        }
        if (strlen($input['level']) > 250) {
            stderr(_('Error'), _('The text entry is too long.'));
        }
        $level = sqlesc($level);
        $minrep = sqlesc(intval($input['minimumreputation']));
        $redirect = _fe('Saved Reputation Level <i>{0}</i> Successfully.', format_comment($input['level']));
    }
    // what we gonna do?
    if ($type === 'new') {
        sql_query("INSERT INTO reputationlevel (minimumreputation, level) VALUES ($minrep, $level)") or sqlerr(__FILE__, __LINE__);
    } elseif ($type === 'edit') {
        $levelid = intval($input['reputationlevelid']);
        if (!is_valid_id($levelid)) {
            stderr(_('Error'), _('Invalid ID'));
        }
        // check it's a valid rep id
        $query = sql_query("SELECT reputationlevelid FROM reputationlevel WHERE reputationlevelid=$levelid") or sqlerr(__FILE__, __LINE__);
        if (!mysqli_num_rows($query)) {
            stderr(_('Error'), _('Invalid ID.'));
        }
        sql_query("UPDATE reputationlevel SET minimumreputation = $minrep, level = $level WHERE reputationlevelid=$levelid") or sqlerr(__FILE__, __LINE__);
    } else {
        $ids = $input['reputation'];
        if (is_array($ids) && count($ids)) {
            foreach ($ids as $k => $v) {
                sql_query('UPDATE reputationlevel SET minimumreputation = ' . (int) $v . ' WHERE reputationlevelid=' . sqlesc($k)) or sqlerr(__FILE__, __LINE__);
            }
        } else {
            stderr(_('Error'), _('Invalid ID.'));
        }
        $redirect = _('Saved Reputation Level Successfully.');
    }
    rep_cache();
    redirect('staffpanel.php?tool=reputation_ad&amp;mode=done', $redirect);
}

/**
 * @param array $input
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws Exception
 */
function do_delete(array $input)
{
    if (!isset($input['reputationlevelid']) || !is_valid_id((int) $input['reputationlevelid'])) {
        stderr(_('Error'), 'No valid ID.');
    }
    $levelid = intval($input['reputationlevelid']);
    // check the id is valid within db
    $query = sql_query("SELECT reputationlevelid FROM reputationlevel WHERE reputationlevelid = $levelid") or sqlerr(__FILE__, __LINE__);
    if (!mysqli_num_rows($query)) {
        stderr(_('Error'), _("Rep ID doesn't exist"));
    }
    // if we here, we delete it!
    sql_query("DELETE FROM reputationlevel WHERE reputationlevelid = $levelid") or sqlerr(__FILE__, __LINE__);
    rep_cache();
    redirect('staffpanel.php?tool=reputation_ad&amp;mode=done', _('Reputation deleted successfully'), 5);
}

/**
 * @param array $input
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws Exception
 */
function show_form_rep(array $input)
{
    global $site_config;

    if (!isset($input['reputationid']) || !is_valid_id((int) $input['reputationid'])) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $title = _('User Reputation Manager');
    $query = sql_query('SELECT r.*, p.topic_id, t.topic_name, leftfor.username AS leftfor_name, 
                    leftby.username AS leftby_name
                    FROM reputation r
                    LEFT JOIN posts p ON p.id=r.postid
                    LEFT JOIN topics t ON p.topic_id=t.id
                    LEFT JOIN users leftfor ON leftfor.id=r.userid
                    LEFT JOIN users leftby ON leftby.id=r.whoadded
                    WHERE reputationid=' . sqlesc($input['reputationid'])) or sqlerr(__FILE__, __LINE__);
    if (!$res = mysqli_fetch_assoc($query)) {
        stderr(_('Error'), _("Erm, it's not there!"));
    }
    $html = "<form action='staffpanel.php?tool=reputation_ad' name='show_rep_form' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                <input name='reputationid' value='{$res['reputationid']}' type='hidden'>
                <input name='oldreputation' value='{$res['reputation']}' type='hidden'>
                <input name='mode' value='doeditrep' type='hidden'>";
    $html .= '<h2>' . _('Edit Reputation') . '</h2>';
    $html .= '<table>';
    $html .= '<tr><td>' . _('Topic') . "</td><td><a href='{$site_config['paths']['baseurl']}/forums.php?action=viewtopic&amp;topicid={$res['topic_id']}&amp;page=p{$res['postid']}#{$res['postid']}' target='_blank'>" . htmlsafechars($res['topic_name']) . '</a></td></tr>';
    $html .= '<tr><td>' . _('Left By') . "</td><td>{$res['leftby_name']}</td></tr>";
    $html .= '<tr><td>' . _('Left For') . "</td><td>{$res['leftfor_name']}</td></tr>";
    $html .= '<tr><td>' . _('Comment') . "</td><td><input type='text' name='reason' value='" . htmlsafechars($res['reason']) . "' maxlength='250'></td></tr>";
    $html .= '<tr><td>' . _('Reputation') . "</td><td><input type='text' name='reputation' value='{$res['reputation']}' maxlength='10'></td></tr>";
    $html .= "<tr><td colspan='2' class='has-text-centered'><input type='submit' value='" . _('Save') . "' accesskey='s' class='button is-small'> <input type='reset' tabindex='1' value='" . _('Reset') . "' accesskey='r' class='button is-small'></td></tr>";
    $html .= '</table></form>';
    html_out($html, $title);
}

/**
 * @param array $now_date
 * @param array $input
 * @param int   $time_offset
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function view_list(array $now_date, array $input, int $time_offset)
{
    global $site_config;

    $title = _('User Reputation Manager');
    $html = '<h2>' . _('View Reputation Comments') . '</h2>';
    $html .= '<p>' . _('This page allows you to search for reputation comments left by / for specific users over the specified date range.') . '</p>';
    $html .= "<form action='{$_SERVER['PHP_SELF']}?tool=reputation_ad' name='list_form' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                <input name='mode' value='list' type='hidden'>
                <input name='dolist' value='1' type='hidden'>";
    $html .= '<table>';
    $html .= '<tr><td>' . _('Left For') . "</td><td><input type='text' name='leftfor' value='' maxlength='250' tabindex='1'></td></tr>";
    $html .= "<tr><td colspan='2'><div>" . _('To limit the comments left for a specific user, enter the username here. Leave this field empty to receive comments left for every user.') . '</div></td></tr>';
    $html .= '<tr><td>' . _('Left By') . "</td><td><input type='text' name='leftby' value='' maxlength='250' tabindex='2'></td></tr>";
    $html .= "<tr><td colspan='2'><div>" . _('To limit the comments left by a specific user, enter the username here. Leave this field empty to receive comments left by every user.') . '</div></td></tr>';
    $html .= '<tr><td>' . _('Start Date') . "</td><td>
        <div>
                <span style='padding-right:5px; float:left;'>" . _('Month') . "<br><select name='start[month]' tabindex='3'>" . get_month_dropdown($now_date) . "</select></span>
                <span style='padding-right:5px; float:left;'>" . _('Day') . "<br><input type='text' name='start[day]' value='" . ($now_date['mday'] + 1) . "' maxlength='2' tabindex='3'></span>
                <span>{" . _('Year') . "}<br><input type='text' name='start[year]' value='" . $now_date['year'] . "' maxlength='4' tabindex='3'></span>
            </div></td></tr>";
    $html .= "<tr><td class='tdrow2' colspan='2'><div class='desctext'>{" . _('Select a start date for this report. Select a month, day, and year. The selected statistic must be no older than this date for it to be included in the report.') . '}</div></td></tr>';
    $html .= '<tr><td>' . _('End Date') . "</td><td>
            <div>
                <span style='padding-right:5px; float:left;'>" . _('Month') . "<br><select name='end[month]' class='textinput' tabindex='4'>" . get_month_dropdown($now_date) . "</select></span>
                <span style='padding-right:5px; float:left;'>" . _('Day') . "<br><input type='text' class='textinput' name='end[day]' value='" . $now_date['mday'] . "' maxlength='2' tabindex='4'></span>
                <span>" . _('Year') . "<br><input type='text' class='textinput' name='end[year]' value='" . $now_date['year'] . "' maxlength='4' tabindex='4'></span>
            </div></td></tr>";
    $html .= "<tr><td class='tdrow2' colspan='2'><div class='desctext'>" . _("Select an end date for this report. Select a month, day, and year. The selected statistic must not be newer than this date for it to be included in the report. You can use this setting in conjunction with the 'Start Date' setting to create a window of time for this report.") . '</div></td></tr>';
    $html .= "<tr><td colspan='2'><input type='submit' value='" . _('Search') . "' accesskey='s' class='button is-small' tabindex='5'> <input type='reset' value='" . _('Reset') . "' accesskey='r' class='button is-small' tabindex='6'></td></tr>";
    $html .= '</table></form>';

    if (isset($input['dolist'])) {
        $input['orderby'] = isset($input['orderby']) ? $input['orderby'] : '';
        $who = isset($input['who']) ? (int) $input['who'] : 0;
        $user = isset($input['user']) ? $input['user'] : 0;
        $first = isset($input['page']) ? (int) $input['page'] : 0;
        $cond = $who ? 'r.whoadded=' . sqlesc($who) : '';
        $start = isset($input['startstamp']) ? (int) $input['startstamp'] : mktime(0, 0, 0, $input['start']['month'], $input['start']['day'], $input['start']['year']) + $time_offset;
        $end = isset($input['endstamp']) ? (int) $input['endstamp'] : mktime(0, 0, 0, $input['end']['month'], $input['end']['day'] + 1, $input['end']['year']) + $time_offset;
        if (!$start) {
            $start = TIME_NOW - (3600 * 24 * 30);
        }
        if (!$end) {
            $end = TIME_NOW;
        }
        if ($start >= $end) {
            stderr(_('Error'), _('Start date is after the end date.'));
        }
        if (!empty($input['leftby'])) {
            $left_b = sql_query('SELECT id FROM users WHERE username = ' . sqlesc($input['leftby'])) or sqlerr(__FILE__, __LINE__);
            if (!mysqli_num_rows($left_b)) {
                stderr(_('Error'), _fe('Could not find user {0}', format_comment($input['leftby'])));
            }
            $leftby = mysqli_fetch_assoc($left_b);
            $who = $leftby['id'];
            $cond = 'r.whoadded=' . $who;
        }
        if (!empty($input['leftfor'])) {
            $left_f = sql_query('SELECT id FROM users WHERE username = ' . sqlesc($input['leftfor'])) or sqlerr(__FILE__, __LINE__);
            if (!mysqli_num_rows($left_f)) {
                stderr(_('Error'), _fe('Could not find user {0}', format_comment($input['leftfor'])));
            }
            $leftfor = mysqli_fetch_assoc($left_f);
            $user = $leftfor['id'];
            $cond .= ($cond ? ' AND' : '') . ' r.userid=' . $user;
        }
        if ($start) {
            $cond .= ($cond ? ' AND' : '') . " r.dateadd>= $start";
        }
        if ($end) {
            $cond .= ($cond ? ' AND' : '') . " r.dateadd <= $end";
        }
        switch ($input['orderby']) {
            case 'leftbyuser':
                $order = 'leftby.username';
                $orderby = 'leftbyuser';
                break;

            case 'leftforuser':
                $order = 'leftfor.username';
                $orderby = 'leftforuser';
                break;

            default:
                $order = 'r.dateadd';
                $orderby = 'dateadd';
        }
        $html = '<h2>' . _('Reputation Comments') . '</h2>';
        $table_header = '<table><tr>';
        $table_header .= '<td>' . _('ID') . '</td>';
        $table_header .= "<td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reputation_ad&amp;mode=list&amp;dolist=1&amp;who=" . $who . '&amp;user=' . $user . "&amp;orderby=leftbyuser&amp;startstamp=$start&amp;endstamp=$end&amp;page=$first'>" . _('Left By') . '</a></td>';
        $table_header .= "<td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reputation_ad&amp;mode=list&amp;dolist=1&amp;who=" . $who . '&amp;user=' . $user . "&amp;orderby=leftforuser&amp;startstamp=$start&amp;endstamp=$end&amp;page=$first'>" . _('Left For') . '</a></td>';
        $table_header .= "<td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reputation_ad&amp;mode=list&amp;dolist=1&amp;who=" . $who . '&amp;user=' . $user . "&amp;orderby=date&amp;startstamp=$start&amp;endstamp=$end&amp;page=$first'>" . _('Date') . '</a></td>';
        $table_header .= '<td>' . _('Point') . '</td>';
        $table_header .= '<td>' . _('Reason') . '</td>';
        $table_header .= '<td>' . _('Controls') . '</td></tr>';
        $html .= $table_header;
        // do the count for pager etc
        $query = sql_query("SELECT COUNT(reputationid) AS cnt FROM reputation r WHERE $cond") or sqlerr(__FILE__, __LINE__);
        //echo_r($input); exit;
        $total = mysqli_fetch_assoc($query);
        if (!$total['cnt']) {
            $html .= "<tr><td colspan='7'>" . _('No Matches Found!') . '</td></tr>';
        }
        // do the pager thang!
        $deflimit = 10;
        $links = '<span style="background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;">' . _fe('{0} Records', $total['cnt']) . '</span>';
        if ($total['cnt'] > $deflimit) {
            require_once INCL_DIR . 'function_pager.php';
            $links = pager_rep([
                'count' => $total['cnt'],
                'perpage' => $deflimit,
                'start_value' => $first,
                'url' => 'staffpanel.php?tool=reputation_ad&amp;mode=list&amp;dolist=1&amp;who=' . $who . '&amp;user=' . $user . "&amp;orderby=$orderby&amp;startstamp=$start&amp;endstamp=$end",
            ]);
        }
        // mofo query!
        $query = sql_query("SELECT r.*, p.topic_id, leftfor.id AS leftfor_id, 
                                    leftfor.username AS leftfor_name, leftby.id AS leftby_id, 
                                    leftby.username AS leftby_name 
                                    FROM reputation r 
                                    left join posts p ON p.id=r.postid 
                                    left join users leftfor ON leftfor.id=r.userid 
                                    left join users leftby ON leftby.id=r.whoadded 
                                    WHERE $cond ORDER BY $order LIMIT $first, $deflimit") or sqlerr(__FILE__, __LINE__);
        if (!mysqli_num_rows($query)) {
            stderr(_('Error'), _('Nothing here'));
        }
        while ($r = mysqli_fetch_assoc($query)) {
            $r['dateadd'] = date('M j, Y, g:i a', $r['dateadd']);
            $html .= "
            <tr>
                <td>#{$r['reputationid']}</td>
                <td>" . format_username((int) $r['leftby_id']) . '</td>
                <td>' . format_username((int) $r['leftfor_id']) . "</td>
                <td>{$r['dateadd']}</td>
                <td>{$r['reputation']}</td>
                <td>
                    <a href='{$site_config['paths']['baseurl']}/forums.php?action=viewtopic&amp;topicid={$r['topic_id']}&amp;page=p{$r['postid']}#{$r['postid']}' target='_blank'>" . htmlsafechars($r['reason']) . "</a>
                </td>
                <td>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reputation_ad&amp;mode=editrep&amp;reputationid={$r['reputationid']}'>
                        <i class='icon-edit icon has-text-info' aria-hidden='true'></i>
                    </a>
                    <a href='{$site_config['paths']['baseurl']}/reputation_ad.php?mode=dodelrep&amp;reputationid={$r['reputationid']}'>
                        <i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i>
                    </a>
                </td>
            </tr>";
        }
        $html .= '</table>';
        $html .= "<br><div>$links</div>";
    }
    html_out($html, $title);
}

/**
 * @param array $input
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws Exception
 */
function do_delete_rep(array $input)
{
    global $container, $site_config;

    if (!is_valid_id((int) $input['reputationid'])) {
        stderr(_('Error'), _('Invalid ID'));
    }
    // check it's a valid ID.
    $query = sql_query('SELECT reputationid, reputation, userid FROM reputation WHERE reputationid=' . sqlesc($input['reputationid'])) or sqlerr(__FILE__, __LINE__);
    if (($r = mysqli_fetch_assoc($query)) === false) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $sql = sql_query('SELECT reputation ' . 'FROM users ' . 'WHERE id=' . sqlesc($input['reputationid'])) or sqlerr(__FILE__, __LINE__);
    $User = mysqli_fetch_assoc($sql);
    // do the delete
    sql_query('DELETE FROM reputation WHERE reputationid=' . sqlesc($r['reputationid'])) or sqlerr(__FILE__, __LINE__);
    sql_query("UPDATE users SET reputation = (reputation-{$r['reputation']} ) WHERE id=" . sqlesc($r['userid'])) or sqlerr(__FILE__, __LINE__);
    $update['rep'] = ($User['reputation'] - $r['reputation']);
    $cache = $container->get(Cache::class);
    $cache->update_row('user_' . $r['userid'], [
        'reputation' => $update['rep'],
    ], $site_config['expires']['user_cache']);
    redirect('staffpanel.php?tool=reputation_ad&amp;mode=list', _('Deleted Reputation Successfully'), 5);
}

/**
 * @param array $input
 *
 * @throws UnbegunTransaction
 * @throws DependencyException
 * @throws NotFoundException
 * @throws Exception
 */
function do_edit_rep(array $input)
{
    global $container, $site_config;

    $reason = '';
    if (isset($input['reason']) && !empty($input['reason'])) {
        $reason = str_replace('<br>', '', $input['reason']);
        $reason = trim($reason);
        if ((strlen(trim($reason)) < 2) || ($reason == '')) {
            stderr(_('Error'), _('The text you entered was too short.'));
        }
        if (strlen($input['reason']) > 250) {
            stderr(_('Error'), _('The text entry is too long.'));
        }
    }
    $oldrep = $input['oldreputation'];
    $newrep = $input['reputation'];
    $query = sql_query('SELECT reputationid, reason, userid FROM reputation WHERE reputationid = ' . sqlesc($input['reputationid'])) or sqlerr(__FILE__, __LINE__);
    if ($r = mysqli_fetch_assoc($query) === false) {
        stderr(_('Error'), _('Invalid ID'));
    }
    if ($oldrep != $newrep) {
        if ($r['reason'] != $reason) {
            sql_query('UPDATE reputation SET reputation = ' . sqlesc($newrep) . ', reason = ' . sqlesc($reason) . ' WHERE reputationid=' . sqlesc($r['reputationid'])) or sqlerr(__FILE__, __LINE__);
        }
        $sql = sql_query('SELECT reputation ' . 'FROM users ' . 'WHERE id=' . sqlesc($input['reputationid'])) or sqlerr(__FILE__, __LINE__);
        $User = mysqli_fetch_assoc($sql);
        $diff = $oldrep - $newrep;
        sql_query("UPDATE users SET reputation = (reputation-{$diff}) WHERE id=" . sqlesc($r['userid'])) or sqlerr(__FILE__, __LINE__);
        $update['rep'] = ($User['reputation'] - $diff);
        $cache = $container->get(Cache::class);
        $cache->update_row('user_' . $r['userid'], [
            'reputation' => $update['rep'],
        ], $site_config['expires']['user_cache']);
        $cache->delete('user_' . $r['userid']);
    }
    redirect('staffpanel.php?tool=reputation_ad&amp;mode=list', _fe('Saved Reputation ID: #{0} Successfully', $r['reputationid']), 5);
}

/**
 * @param string $html
 * @param string $title
 *
 * @throws Exception
 */
function html_out($html = '', $title = '')
{
    global $site_config;

    if (empty($html)) {
        stderr(_('Error'), _('Nothing to output'));
    }
    $title = empty($title) ? _('Reputation') : $title;
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($html) . stdfoot();
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
<meta http-equiv='refresh' content='{$time}; url={$site_config['paths']['baseurl']}/{$url}'>
<link rel='stylesheet' href='" . get_file_name('css') . "'>
</head>
<body>
    <div>
        <div>" . _('Redirecting...') . "</div>
            <div style='padding: 8px;'>
                <div>$text
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

/**
 * @param int   $i
 * @param array $now_date
 *
 * @return string
 */
function get_month_dropdown(array $now_date, $i = 0)
{
    $return = '';
    $month = [
        '',
        _('January'),
        _('February'),
        _('March'),
        _('April'),
        _('May'),
        _('June'),
        _('July'),
        _('August'),
        _('September'),
        _('October'),
        _('November'),
        _('December'),
    ];
    foreach ($month as $k => $m) {
        $return .= "\t<option value='" . $k . "' ";
        $return .= (($k + $i) == $now_date['mon']) ? 'selected' : '';
        $return .= '>' . $m . "</option>\n";
    }

    return $return;
}

/**
 * @throws Exception
 */
function rep_cache()
{
    $query = sql_query('SELECT * FROM reputationlevel') or sqlerr(__FILE__, __LINE__);
    if (!mysqli_num_rows($query)) {
        stderr(_('Error'), _('No items to cache'));
    }
    $rep_out = '<' . "?php\n\n\$reputations = [\n";
    while ($row = mysqli_fetch_assoc($query)) {
        $rep_out .= "\t{$row['minimumreputation']} => '{$row['level']}',\n";
    }
    $rep_out .= "\n];";
    file_put_contents(CACHE_DIR . 'rep_cache.php', $rep_out);
}
