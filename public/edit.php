<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Roles;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_categories.php';
$user = check_user_status();
global $container, $site_config;

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('upload_js'),
        get_file_name('sceditor_js'),
    ],
];
$id = $_GET['id'];
if (empty($id)) {
    die();
}
$id = (int) $id;
$cache = $container->get(Cache::class);
if ((isset($_GET['unedit']) && $_GET['unedit'] == 1) && $user['class'] >= UC_STAFF) {
    $cache->delete('editedby_' . $id);
    $returl = "details.php?id=$id";
    if (isset($_POST['returnto'])) {
        $returl .= '&returnto=' . urlencode($_POST['returnto']);
    }
    header("Refresh: 1; url=$returl");
    die();
}
$fluent = $container->get(Database::class);
$row = $fluent->from('torrents')
              ->where('id = ?', $id)
              ->fetch();
if (!$row) {
    stderr(_('USER ERROR'), _('No torrent found'));
}
if (!isset($user) || ($user['id'] != $row['owner'] && !has_access($user['class'], UC_STAFF, 'torrent_mod'))) {
    stderr(_('USER ERROR'), _f("<h1>Can't edit this torrent</h1><p>You're not the rightful owner, or you're not <a href='" . $site_config['paths']['baseurl'] . "/login.php?returnto=%s&amp;nowarn=1'>logged in</a> properly.</p>", urlencode($_SERVER['REQUEST_URI'])));
}
$HTMLOUT = $currently_editing = $subs_list = $audios_list = '';
if ($user['class'] >= UC_STAFF) {
    $currently_editing = $cache->get('editedby_' . $id);
    if ($currently_editing === false || is_null($currently_editing)) {
        $currently_editing = $user['username'];
        $cache->set('editedby_' . $id, $currently_editing, $site_config['expires']['ismoddin']);
    }
    if ($currently_editing != $user['username']) {
        $HTMLOUT .= '<h1 class="has-text-centered"><span class="has-text-danger">' . $currently_editing . '</span> is currently editing this torrent!</h1>';
    }
}
$HTMLOUT .= "<form method='post' id='edit_form' name='edit_form' action='takeedit.php' enctype='multipart/form-data' accept-charset='utf-8'>
    <input type='hidden' name='id' value='$id'>";
if (isset($_GET['returnto'])) {
    $HTMLOUT .= "<input type='hidden' name='returnto' value='" . htmlsafechars($_GET['returnto']) . "'>\n";
}
$HTMLOUT .= "<table class='table table-bordered table-striped'>\n";
$HTMLOUT .= tr(_("<a href='https://nullrefer.com/?https://www.imdb.com' target='_blank'>IMDb Url</a>"), "<input type='text' name='url' class='w-100' value='" . (!empty($row['url']) ? htmlsafechars($row['url']) : '') . "'>", 1);
$HTMLOUT .= tr(_('ISBN'), "<input type='text' name='isbn' min_length='10' max_length='13' class='w-100' value='" . (!empty($row['isbn']) ? htmlsafechars($row['isbn']) : '') . "'><br>" . _('Used for Books, ISBN 13 or ISBN 10, no spaces or dashes') . '', 1);
$HTMLOUT .= tr(_('Title'), "<input type='text' name='title' class='w-100' value='" . (!empty($row['title']) ? htmlsafechars($row['title']) : '') . "'><br>" . _('Either this or the ISBN must be set in order to lookup the books details. The ISBN should yield better results.') . '', 1);
$HTMLOUT .= tr(_('Poster'), "<input type='text' name='poster' class='w-100' value='" . (!empty($row['poster']) ? htmlsafechars($row['poster']) : '') . "'><br>" . _('Minimum Poster Width should be 400 Px , larger sizes will be scaled.') . "\n", 1);
$HTMLOUT .= tr(_("<a href='https://nullrefer.com/?https://youtube.com' target='_blank'>Youtube</a>"), "<input type='text' name='youtube' value='" . (!empty($row['youtube']) ? htmlsafechars($row['youtube']) : '') . "' class='w-100'><br>(" . _('Link should look like <b>http://www.youtube.com/watch?v=camI8yuoy8U</b>') . ")\n", 1);
$HTMLOUT .= tr(_('Torrent name'), "<input type='text' name='name' value='" . (!empty($row['name']) ? htmlsafechars($row['name']) : '') . "' class='w-100'>", 1);
$HTMLOUT .= tr(_('Torrent tags'), "<input type='text' name='tags' value='" . (!empty($row['tags']) ? htmlsafechars($row['tags']) : '') . "' class='w-100'><br>(" . _('Multiple tags must be seperated by a comma like tag1,tag2') . ")\n", 1);
$HTMLOUT .= tr(_('Small Description'), "<input type='text' name='description' value='" . (!empty($row['description']) ? htmlsafechars($row['description']) : '') . "' class='w-100'>", 1);
$HTMLOUT .= tr(_('NFO file'), "
    <label for='nfoaction'>" . _('Keep current') . "</label>
    <input type='radio' id='nfoaction' name='nfoaction' value='keep' checked class='right5'><br>
    <input type='radio' name='nfoaction' value='update' class='right5'>" . _('Update:') . "<br>
    <input type='file' name='nfo' class='w-100'>", 1);
if ((strpos($row['ori_descr'], '<') === false) || (strpos($row['ori_descr'], '&lt;') !== false)) {
    $c = '';
} else {
    $c = 'checked';
}
$HTMLOUT .= tr(_('Description'), BBcode($row['ori_descr']) . '<br>(' . _("HTML is not allowed. <a href='http://Pu239.silly/tags.php'>Click here</a> for information on available tags.") . ')', 1, 'is-paddingless');
$s = "
    <select name='type'>";
$cats = genrelist(true);
foreach ($cats as $cat) {
    foreach ($cat['children'] as $subrow) {
        $s .= "
        <option value='{$subrow['id']}' " . ($subrow['id'] == $row['category'] ? 'selected' : '') . '>' . htmlsafechars($cat['name']) . '::' . htmlsafechars($subrow['name']) . '</option>';
    }
}
$s .= '
    </select>';
$HTMLOUT .= tr(_('Type'), $s, 1);

$subs_list .= "
        <div class='level-center'>";
$audios_list .= "
        <div class='level-center'>";
$subs = $container->get('subtitles');
$s = [
    'name' => '',
    'pic' => '',
];
foreach ($subs as $s) {
    $torrent_subs = explode('|', $row['subs']);
    $subs_list .= "
            <div class='w-15 margin10 tooltipper bordered level-center-center' title='" . htmlsafechars($s['name']) . "'>
                <input name='subs[]' type='checkbox' value='{$s['name']}' " . (in_array($s['name'], $torrent_subs) ? 'checked' : '') . " class='margin20'>
                <img class='sub_flag' src='{$site_config['paths']['images_baseurl']}/{$s['pic']}' alt='{$s['name']}' title='" . htmlsafechars($s['name']) . "'>
                <span class='margin20'>" . htmlsafechars($s['name']) . '</span>
            </div>';
    $torrent_audios = !empty($row['audios']) ? explode('|', $row['audios']) : [];
    $audios_list .= "
            <div class='w-15 margin10 tooltipper bordered level-center-center' title='" . htmlsafechars($s['name']) . "'>
                <input name='audios[]' type='checkbox' value='{$s['name']}' " . (in_array($s['name'], $torrent_audios) ? 'checked' : '') . " class='margin20'>
                <img class='sub_flag' src='{$site_config['paths']['images_baseurl']}/{$s['pic']}' alt='{$s['name']}' title='" . htmlsafechars($s['name']) . "'>
                <span class='margin20'>" . htmlsafechars($s['name']) . '</span>
            </div>';
}
$subs_list .= '
        </div>';
$audios_list .= '
        </div>';
$HTMLOUT .= tr('Subtitles', $subs_list, 1);
$HTMLOUT .= tr('Audios', $audios_list, 1);
$rg = "<select name='release_group'>\n<option value='scene' " . ($row['release_group'] === 'scene' ? 'selected' : '') . ">Scene</option>\n<option value='p2p' " . ($row['release_group'] === 'p2p' ? 'selected' : '') . ">p2p</option>\n<option value='none' " . ($row['release_group'] === 'none' ? 'selected' : '') . ">None</option> \n</select>\n";
$HTMLOUT .= tr('Release Group', $rg, 1);
$HTMLOUT .= tr(_('Visible'), "<input type='checkbox' name='visible' " . (($row['visible']) === 'yes' ? 'checked' : '') . " value='1'> " . _('Visible on main page') . "<br><table class='table table-bordered table-striped'><tr><td class='embedded'>" . _("Note that the torrent will automatically become visible when there's a seeder, and will become automatically invisible(dead) when there has been no seeder for a while.  switch to speed the process up manually . Also note that invisible(dead) torrents can still be viewed or searched for, it's just not the default.") . '</td></tr></table>', 1);
if ($user['class'] >= UC_STAFF) {
    $HTMLOUT .= tr(_('Banned'), "<input type='checkbox' name='banned' " . (($row['banned']) === 'yes' ? 'checked' : '') . " value='1'> " . _('Banned') . '', 1);
}
$HTMLOUT .= tr(_('Recommend Torrent'), "<input type='radio' name='recommended' " . (($row['recommended'] === 'yes') ? 'checked' : '') . " value='yes' class='right5'>Yes!<input type='radio' name='recommended' " . ($row['recommended'] === 'no' ? 'checked' : '') . " value='no' class='right5'>No!<br><font class='small'>" . _('If you want to recommend this torrent check this box!') . '</font>', 1);
$auth = $container->get(Auth::class);
if ($auth->hasRole(Roles::UPLOADER)) {
    $HTMLOUT .= tr('Nuked', "<input type='radio' name='nuked' " . ($row['nuked'] === 'yes' ? 'checked' : '') . " value='yes' class='right5'>Yes <input type='radio' name='nuked' " . ($row['nuked'] === 'no' ? 'checked' : '') . " value='no' class='right5'>No", 1);
    $HTMLOUT .= tr('Nuke Reason', "<input type='text' name='nukereason' value='" . (!empty($row['nukereason']) ? htmlsafechars($row['nukereason']) : '') . "' class='w-100'>", 1);
}
if ($user['class'] >= UC_STAFF) {
    $HTMLOUT .= tr('Free Leech', ($row['free'] != 0 ? "<input type='checkbox' name='fl' value='1'> Remove Freeleech" : "
    <select name='free_length'>
    <option value='0'>------</option>
    <option value='42'>Free for 1 day</option>
    <option value='1'>Free for 1 week</option>
    <option value='2'>Free for 2 weeks</option>
    <option value='4'>Free for 4 weeks</option>
    <option value='8'>Free for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select>"), 1);
    if ($row['free'] != 0) {
        $HTMLOUT .= tr('Free Leech Duration', ($row['free'] != 1 ? 'Until ' . get_date((int) $row['free'], 'DATE') . '
         (' . mkprettytime($row['free'] - TIME_NOW) . ' to go)' : 'Unlimited'), 1);
    }
    $HTMLOUT .= tr('Silver torrent ', ($row['silver'] != 0 ? "<input type='checkbox' name='slvr' value='1'> Remove Silver torrent" : "
    <select name='half_length'>
    <option value='0'>------</option>
    <option value='42'>Silver for 1 day</option>
    <option value='1'>Silver for 1 week</option>
    <option value='2'>Silver for 2 weeks</option>
    <option value='4'>Silver for 4 weeks</option>
    <option value='8'>Silver for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select>"), 1);
    if ($row['silver'] != 0) {
        $HTMLOUT .= tr('Silver Torrent Duration', ($row['silver'] != 1 ? 'Until ' . get_date((int) $row['silver'], 'DATE') . '
         (' . mkprettytime($row['silver'] - TIME_NOW) . ' to go)' : 'Unlimited'), 1);
    }
}

if ($user['class'] >= $site_config['allowed']['torrents_disable_comments']) {
    if ($row['allow_comments'] === 'yes') {
        $messc = '&#160;Comments are allowed for everyone on this torrent!';
    } else {
        $messc = '&#160;Only staff members are able to comment on this torrent!';
    }
    $HTMLOUT .= "<tr>
    <td><span class='has-text-danger'>&#160;*&#160;</span>&#160;" . _('Allow Comments') . "</td>
    <td>
    <select name='allow_comments'>
    <option value='" . htmlsafechars($row['allow_comments']) . "'>" . htmlsafechars($row['allow_comments']) . "</option>
    <option value='yes'>Yes</option><option value='no'>No</option></select>{$messc}</td></tr>\n";
}

if ($user['class'] >= UC_STAFF) {
    $HTMLOUT .= tr('Sticky', "<input type='checkbox' name='sticky' " . (($row['sticky']) === 'yes' ? 'checked' : '') . " value='yes'>Sticky this torrent !", 1);
    $HTMLOUT .= tr(_('Anonymous Uploader'), "<input type='checkbox' name='anonymous' " . (($row['anonymous'] === '1') ? 'checked' : '') . " value='1'>" . _('Check this box to hide the uploader of the torrent') . '', 1);
    $HTMLOUT .= tr('VIP Torrent?', "<input type='checkbox' name='vip' " . (($row['vip'] == 1) ? 'checked' : '') . " value='1'> If this one is checked, only VIPs can download this torrent", 1);
}

require_once PARTIALS_DIR . 'genres.php';

$HTMLOUT .= "
            <tr>
                <td colspan='2'>
                    <div class='has-text-centered margin20'>
                        <input type='submit' value='" . _('Edit it!') . "' class='button is-small right20'>
                        <input type='reset' value='" . _('Revert changes') . "' class='button is-small'>
                    </div>
                </td>
            </tr>
        </table>
    </form>
    <form name='delete_form' method='post' action='{$site_config['paths']['baseurl']}/delete.php' enctype='multipart/form-data' accept-charset='utf-8'>";
$body = "
            <tr>
                <td class='colhead' colspan='2'>" . _('Delete torrent') . '. ' . _('Reason:') . "</td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='1' class='right5'>" . _('Dead') . '
                </td>
                <td> ' . _('0 seeders, 0 leechers = 0 peers total') . "</td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='2' class='right5'>" . _('Dupe') . "
                </td>
                <td><input type='text' size='40' name='reason[]' class='w-100' placeholder='" . _('required') . "'></td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='3' class='right5'>" . _('Nuked') . "
                </td>
                <td><input type='text' size='40' name='reason[]' class='w-100' placeholder='" . _('required') . "'></td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='4' class='right5'>" . _fe('{0} Rules', $site_config['site']['name']) . "
                </td>
                <td><input type='text' size='40' name='reason[]' class='w-100' placeholder='" . _('required') . "'></td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='5' checked class='right5'>" . _('Other:') . "
                </td>
                <td>
                    <input type='text' size='40' name='reason[]' class='w-100 right5' placeholder='" . _('required') . "'>
                    <input type='hidden' name='id' value='$id'>
                </td>
            </tr>";
if (isset($_GET['returnto'])) {
    $body .= "
        <input type='hidden' name='returnto' value='" . htmlsafechars($_GET['returnto']) . "'>\n";
}
$body .= "
            <tr>
                <td colspan='2'>
                    <div class='has-text-centered margin20'>
                        <input type='submit' value='" . _('Delete it!') . "' class='button is-small'>
                    </div>
                </td>
            </tr>";
$HTMLOUT .= main_table($body, null, 'top20') . '
    </form>';
$title = _('Edit Torrent');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
