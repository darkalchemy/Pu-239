<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $container, $site_config;

$is_mod = $user['class'] >= UC_STAFF ? true : false;

$closewindow = true;
require_once CACHE_DIR . 'rep_settings_cache.php';

if (!$GVARS['rep_is_online']) {
    die(_('Reputation system offline, sorry'));
}

if (isset($_POST) || isset($_GET)) {
    $input = array_merge($_GET, $_POST);
}

if (isset($input['done'])) {
    rep_output(_('Reputation added!'));
}

$input['pid'] = isset($input['pid']) ? (int) $input['pid'] : 0;
$check = isset($input['pid']) ? is_valid_id($input['pid']) : false;
$locales = [
    'posts',
    'comments',
    'torrents',
    'users',
];
$rep_locale = isset($input['locale']) && (in_array($input['locale'], $locales)) ? $input['locale'] : 'posts';
if (!$check) {
    rep_output('Incorrect Access');
}
if ($rep_locale === 'posts') {
    $forum = sql_query('SELECT posts.topic_id AS locale, posts.user_id AS userid, forums.min_class_read, posts.anonymous as anon,
                            users.username, users.reputation
                        FROM posts
                        LEFT JOIN topics ON topic_id=topics.id
                        LEFT JOIN forums ON topics.forum_id=forums.id
                        LEFT JOIN users ON posts.user_id=users.id
                        WHERE posts.id=' . sqlesc($input['pid'])) or sqlerr(__FILE__, __LINE__);
} elseif ($rep_locale === 'comments') {
    $forum = sql_query('SELECT comments.id, comments.user AS userid, comments.anonymous AS anon,
     comments.torrent AS locale,
     users.username, users.reputation
     FROM comments
     LEFT JOIN users ON comments.user = users.id
     WHERE comments.id=' . sqlesc($input['pid'])) or sqlerr(__FILE__, __LINE__);
} elseif ($rep_locale === 'torrents') {
    $forum = sql_query('SELECT torrents.id as locale, torrents.owner AS userid, torrents.anonymous AS anon,
    users.username, users.reputation
    FROM torrents
    LEFT JOIN users ON torrents.owner = users.id
    WHERE torrents.id=' . sqlesc($input['pid'])) or sqlerr(__FILE__, __LINE__);
} elseif ($rep_locale === 'users') {
    $forum = sql_query('SELECT id AS userid, username, reputation, opt1, opt2 FROM users WHERE id=' . sqlesc($input['pid'])) or sqlerr(__FILE__, __LINE__);
}
switch ($rep_locale) {
    case 'comments':
        $this_rep = 'Comment';
        break;

    case 'torrents':
        $this_rep = 'Torrent';
        break;

    case 'users':
        $this_rep = 'Profile';
        break;

    default:
        $this_rep = 'Post';
}

if (!mysqli_num_rows($forum)) {
    rep_output($this_rep . ' Does Not Exist - Incorrect Access');
}

$res = mysqli_fetch_assoc($forum) or sqlerr(__LINE__, __FILE__);
if (isset($res['minclassread'])) {
    if ($user['class'] < $res['minclassread']) {
        rep_output('Wrong Permissions');
    }
}

$repeat = sql_query("SELECT postid FROM reputation WHERE postid ={$input['pid']} AND whoadded={$user['id']}") or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($repeat) > 0 && $rep_locale != 'users') {
    rep_output('You have already added Rep to this ' . $this_rep . '!');
}

if (!$is_mod) {
    if ($GVARS['rep_maxperday'] >= $GVARS['rep_repeat']) {
        $klimit = intval($GVARS['rep_maxperday'] + 1);
    } else {
        $klimit = intval($GVARS['rep_repeat'] + 1);
    }

    $flood = sql_query('SELECT dateadd, userid FROM reputation 
                                    WHERE whoadded = ' . sqlesc($user['id']) . ' 
                                    ORDER BY dateadd DESC
                                    LIMIT 0 , ' . sqlesc($klimit)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($flood)) {
        $i = 0;
        while ($check = mysqli_fetch_assoc($flood)) {
            if (($i < $GVARS['rep_repeat']) && ($check['userid'] == $user['id'])) { //$res['userid'] ) )
                rep_output(_('You cannot rep your own stuffs!'));
            }
            if ((($i + 1) == $GVARS['rep_maxperday']) && (($check['dateadd'] + 86400) > TIME_NOW)) {
                rep_output(_('The game is up, you rep spammer!'));
            }
            ++$i;
        }
    }
}
$r = sql_query('SELECT COUNT(id) FROM posts WHERE user_id = ' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
$a = mysqli_fetch_row($r);
$user['posts'] = $a[0];

$reason = '';
if (isset($input['reason']) && !empty($input['reason'])) {
    $reason = trim($input['reason']);
    $temp = stripslashes($input['reason']);
    if ((strlen(trim($temp)) < 2) || ($reason == '')) {
        rep_output(_('Reputation reasion is too short!'));
    }
    if (strlen(preg_replace('/&#([0-9]+);/', '-', stripslashes($input['reason']))) > 250) {
        rep_output(_('Reputation reasion is too long!'));
    }
}

if (isset($input['do']) && $input['do'] === 'addrep') {
    if ($res['userid'] == $user['id']) { // sneaky bastiges!
        rep_output(_('You cannot rep your own stuffs!'));
    }
    $score = fetch_reppower($user, $input['reputation']);
    $res['reputation'] += $score;
    sql_query('UPDATE users SET reputation = ' . (int) $res['reputation'] . ' WHERE id=' . sqlesc($res['userid'])) or sqlerr(__FILE__, __LINE__);
    $cache = $container->get(Cache::class);
    $cache->update_row('user_' . $res['userid'], [
        'reputation' => $res['reputation'],
    ], $site_config['expires']['user_cache']);
    $cache->delete('user_rep_' . $res['userid']);
    $save = [
        'reputation' => sqlesc($score),
        'whoadded' => sqlesc($user['id']),
        'reason' => sqlesc($reason),
        'dateadd' => sqlesc(TIME_NOW),
        'locale' => sqlesc($rep_locale),
        'postid' => sqlesc((int) $input['pid']),
        'userid' => sqlesc($res['userid']),
    ];

    sql_query('INSERT INTO reputation (' . implode(', ', array_keys($save)) . ') VALUES (' . implode(', ', $save) . ')') or sqlerr(__FILE__, __LINE__);
    header("Location: {$site_config['paths']['baseurl']}/reputation.php?pid={$input['pid']}&done=1");
} else {
    if ($res['userid'] == $user['id']) { // same as him!
        // check for fish!
        $query1 = sql_query('SELECT r.*, leftby.id AS leftby_id, leftby.username AS leftby_name
                                        FROM reputation AS r
                                        LEFT JOIN users leftby ON leftby.id=r.whoadded
                                        WHERE postid=' . sqlesc($input['pid']) . '
                                        AND r.locale = ' . sqlesc($input['locale']) . '
                                        ORDER BY dateadd DESC') or sqlerr(__FILE__, __LINE__);
        $reasonbits = $rep = '';
        if (mysqli_num_rows($query1) !== false) {
            $total = 0;
            while ($postrep = mysqli_fetch_assoc($query1)) {
                $total += $postrep['reputation'];
                if ($postrep['reputation'] > 0) {
                    $posneg = 'pos';
                } elseif ($postrep['reputation'] < 0) {
                    $posneg = 'neg';
                } else {
                    $posneg = 'balance';
                }
                if ($GVARS['g_rep_seeown']) {
                    $postrep['reason'] = $postrep['reason'] . " <span class='desc'>" . _('Left by') . ' ' . format_username((int) $postrep['leftby_id']) . '</span>';
                }
                $reasonbits .= "<tr>
    <td class='row2'><img src='{$site_config['paths']['images_baseurl']}rep/reputation_$posneg.gif' alt=''></td>
    <td class='row2'>{$postrep['reason']}</td>
</tr>";
            }

            if ($total == 0) {
                $rep = _('Even');
            } elseif ($total > 0 && $total <= 5) {
                $rep = _('Somewhat Positive');
            } elseif ($total > 5 && $total <= 15) {
                $rep = _('Positive');
            } elseif ($total > 15 && $total <= 25) {
                $rep = _('Very Positive');
            } elseif ($total > 25) {
                $rep = _('Extremely Positive');
            } elseif ($total < 0 && $total >= -5) {
                $rep = _('Somewhat Negative');
            } elseif ($total < -5 && $total >= -15) {
                $rep = _('Negative');
            } elseif ($total < -15 && $total >= -25) {
                $rep = _('Very Negative');
            } elseif ($total < -25) {
                $rep = _('Extremely Negative');
            }
        } else {
            $rep = _('Even'); //Ok, dunno what to do, so just make it quits!
        }
        switch ($rep_locale) {
            case 'comments':
                $rep_info = sprintf("Your reputation on <a href='{$site_config['paths']['baseurl']}/details.php?id=%d&amp;viewcomm=%d#comm%d' target='_blank'>this Comment</a> is %s<br>Total: %s points.", $res['locale'], $input['pid'], $input['pid'], $rep, $total);
                break;

            case 'torrents':
                $rep_info = sprintf("Your reputation on <a href='{$site_config['paths']['baseurl']}/details.php?id=%d' target='_blank'>this Torrent</a> is %s<br>Total: %s points.", $input['pid'], $rep, $total);
                break;

            case 'users':
                $rep_info = sprintf("Your reputation on <a href='{$site_config['paths']['baseurl']}/userdetails.php?id=%d' target='_blank'>your profile</a> is %s<br>Total: %s points.", $input['pid'], $rep, $total);
                break;

            default:
                $rep_info = sprintf("Your reputation on <a href='{$site_config['paths']['baseurl']}/forums.php?action=viewtopic&amp;topicid=%d&amp;page=p%d#%d' target='_blank'>this Post</a> is %s<br>Total: %s points.", $res['locale'], $input['pid'], $input['pid'], $rep, $total);
        }
        $rep_points = sprintf('' . _('You have') . ' %d ' . _('Reputation Point(s).') . '', $user['reputation']);
        $html = "
                        <tr>
                            <td class='has-text-centered'>{$rep_info}</td>
                        </tr>
                        <tr>
                            <td class='row2'>
                                <div class='tablepad'>";
        if ($reasonbits) {
            $html .= "
                                    <fieldset class='fieldset'>
                                        <legend>" . _('Reputation Comments') . "</legend>
                                        <table class='table table-bordered table-striped'>
                                            $reasonbits
                                        </table>
                                    </fieldset><br>";
        }
        $html .= "
                                    <div class='has-text-primary has-text-weight-bold has-text-centered formsubtitle'>{$rep_points}</div>
                                </div>
                            </td>
                        </tr>";
    } else {
        $res['username'] = $res['anon'] === 'yes' ? 'Anonymous' : $res['username'];
        $rep_text = sprintf("What do you think of %s's " . $this_rep . '?', htmlsafechars($res['username']));
        $negativerep = ($is_mod || $GVARS['g_rep_negative']) ? true : false;
        $closewindow = false;
        $html = "
                        <tr>
                            <td class='has-text-centered'>" . _('Add To Reputation') . ' <b>' . htmlsafechars($res['username']) . "</b></td>
                        </tr>
                        <tr>
                            <td class='row2'>
                                <form action='reputation.php' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                                    <div class='tablepad'>
                                        <fieldset>
                                            <legend>$rep_text</legend>
                                            <table class='table table-bordered table-striped'>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <label for='rb_reputation_pos'>
                                                                <input type='radio' name='reputation' value='pos' id='rb_reputation_pos' checked class='radiobutton'> &#160;" . _('I Approve') . '
                                                            </label>
                                                        </div>';
        if ($negativerep) {
            $html .= "
                                                        <div>
                                                            <label for='rb_reputation_neg'>
                                                                <input type='radio' name='reputation' value='neg' id='rb_reputation_neg' class='radiobutton'> &#160;" . _('I Disapprove') . '
                                                            </label>
                                                        </div>';
        }
        $html .= '                                  </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        ' . _('Your comments on this :') . ' ' . $this_rep . "<br>
                                                        <input type='text' maxlength='250' name='reason' class='w-100'>
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </div>
                                    <div class='has-text-centered padding10'>
                                        <input type='hidden' name='act' value='reputation'>
                                        <input type='hidden' name='do' value='addrep'>
                                        <input type='hidden' name='pid' value='{$input['pid']}'>
                                        <input type='hidden' name='locale' value='{$input['locale']}'>
                                        <input type='submit' value='" . _('Add To Reputation') . "' class='button is-small' accesskey='s'>
                                        <input type='button' value='Close Window' class='button is-small' accesskey='c' onclick='self.close()'>
                                    </div>
                                </form>
                            </td>
                        </tr>";
    }
    rep_output('', $html);
}

/**
 * @param string $msg
 * @param string $html
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws AuthError
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function rep_output($msg = '', $html = '')
{
    global $lang, $closewindow;
    $body_class = 'background-16 skin-2';
    if ($msg && empty($html)) {
        $html = "
        <tr>
            <td class='row2'>
                $msg
            </td>
        </tr>";
    }
    $htmlout = doc_head('Reputation System') . "
    <link rel='stylesheet' href='" . get_file_name('vendor_css') . "'>
    <link rel='stylesheet' href='" . get_file_name('css') . "'>
    <link rel='stylesheet' href='" . get_file_name('main_css') . "'>
</head>
<body class='$body_class'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
    </script>
    <div class='has-text-centered'>
        <div class='has-text-success'>Reputation System</div>
            <table class='table table-bordered table-striped'>
                $html";
    if ($closewindow) {
        $htmlout .= "
                <tr>
                    <td class='has-text-centered'>
                        <a href='javascript:self.close();'><b>" . _('Close Rep') . '</b></a>
                    </td>
                </tr>';
    }
    $htmlout .= '
            </table>
        </div>
    </div>
</body>
</html>';
    echo $htmlout;
    die();
}

/**
 * @param array  $user
 * @param string $rep
 *
 * @return int|string
 */
function fetch_reppower($user = [], $rep = 'pos')
{
    global $GVARS, $is_mod;

    $reppower = '';

    if (!$GVARS['g_rep_negative']) {
        $rep = 'pos';
    }
    if (!$GVARS['g_rep_use']) { // allowed to rep at all?
        $rep = 0;
    } elseif ($is_mod && $GVARS['rep_adminpower']) { // is a mod and has loadsa power?
        $reppower = ($rep != 'pos') ? (int) $GVARS['rep_adminpower'] * -1 : intval($GVARS['rep_adminpower']);
    } elseif (($user['posts'] < $GVARS['rep_minpost']) || ($user['reputation'] < $GVARS['rep_minrep'])) { // not an admin, then work out postal based power
        $reppower = 0;
    } else { // ok failed all tests, so ratio is 1:1 but not negative, unless allowed
        $reppower = 1;
        if ($GVARS['rep_pcpower']) { // percentage power
            $reppower += intval($user['posts'] / $GVARS['rep_pcpower']);
        }
        if ($GVARS['rep_kppower']) { // rep as based upon a constant of kppower global
            $reppower += intval($user['reputation'] / $GVARS['rep_kppower']);
        }
        if ($GVARS['rep_rdpower']) { // time based power
            $reppower += TIME_NOW - $user['registered'] / 86400 / $GVARS['rep_rdpower'];
        }
        if ($rep != 'pos') {
            $reppower = intval($reppower / 2);
            $reppower = ($reppower < 1) ? 1 : $reppower;
            $reppower *= -1;
        }
    }

    return $reppower;
}
