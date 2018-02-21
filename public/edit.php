<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CACHE_DIR . 'subs.php';
check_user_status();
global $CURUSER, $site_config;

$cache = new Cache();

if (!mkglobal('id')) {
    die();
}
$id = (int)$id;
if (!$id) {
    die();
}

if ((isset($_GET['unedit']) && $_GET['unedit'] == 1) && $CURUSER['class'] >= UC_STAFF) {
    $cache->delete('editedby_' . $id);
    $returl = "details.php?id=$id";
    if (isset($_POST['returnto'])) {
        $returl .= '&returnto=' . urlencode($_POST['returnto']);
    }
    header("Refresh: 1; url=$returl");
    die();
}
$lang = array_merge(load_language('global'), load_language('edit'));
$stdfoot = [
    'js' => [
        get_file_name('upload_js'),
    ],
];
$stdhead = [
    'css' => [
        get_file_name('upload_css'),
    ],
];
$res = sql_query('SELECT * FROM torrents WHERE id = ' . sqlesc($id));
$row = mysqli_fetch_assoc($res);
if (!$row) {
    stderr($lang['edit_user_error'], $lang['edit_no_torrent']);
}
if (!isset($CURUSER) || ($CURUSER['id'] != $row['owner'] && $CURUSER['class'] < UC_STAFF)) {
    stderr($lang['edit_user_error'], sprintf($lang['edit_no_permission'], urlencode($_SERVER['REQUEST_URI'])));
}
$HTMLOUT = $mod_cache_name = $subs_list = '';

if ($CURUSER['class'] >= UC_STAFF) {
    $mod_cache_name = $cache->get('editedby_' . $id);
    if ($mod_cache_name === false || is_null($mod_cache_name)) {
        $mod_cache_name = $CURUSER['username'];
        $cache->add('editedby_' . $id, $mod_cache_name, $site_config['expires']['ismoddin']);
    }
    $HTMLOUT .= '<h1 class="has-text-centered"><span class="has-text-red">' . $mod_cache_name . '</span> is currently editing this torrent!</h1>';
}
$ismodd = '<tr><td class=\'colhead\' colspan=\'2\'><b>Edit Torrent</b> ' . (($CURUSER['class'] > UC_UPLOADER) ? '<small><a href="edit.php?id=' . $id . '&amp;unedit=1">Click here</a> to add temp edit notification while you edit this torrent</small>' : '') . '</td></tr>';
$HTMLOUT .= "<form method='post' id='edit_form' name='edit_form' action='takeedit.php' enctype='multipart/form-data'>
    <input type='hidden' name='id' value='$id' />";
if (isset($_GET['returnto'])) {
    $HTMLOUT .= "<input type='hidden' name='returnto' value='" . htmlsafechars($_GET['returnto']) . "' />\n";
}
$HTMLOUT .= "<table class='table table-bordered table-striped'>\n";
$HTMLOUT .= $ismodd;
$HTMLOUT .= tr($lang['edit_imdb_url'], "<input type='text' name='url' class='w-100' value='" . htmlsafechars($row['url']) . "' />", 1);
$HTMLOUT .= tr($lang['edit_isbn'], "<input type='text' name='isbn' class='w-100' value='" . htmlsafechars($row['isbn']) . "'/><br>{$lang['edit_isbn_details']}", 1);
$HTMLOUT .= tr($lang['edit_poster'], "<input type='text' name='poster' class='w-100' value='" . htmlsafechars($row['poster']) . "' /><br>{$lang['edit_poster1']}\n", 1);
$HTMLOUT .= tr($lang['edit_youtube'], "<input type='text' name='youtube' value='" . htmlsafechars($row['youtube']) . "'class='w-100' /><br>({$lang['edit_youtube_info']})\n", 1);
$HTMLOUT .= tr($lang['edit_torrent_name'], "<input type='text' name='name' value='" . htmlsafechars($row['name']) . "' class='w-100' />", 1);
$HTMLOUT .= tr($lang['edit_torrent_tags'], "<input type='text' name='tags' value='" . htmlsafechars($row['tags']) . "' class='w-100' /><br>({$lang['edit_tags_info']})\n", 1);
$HTMLOUT .= tr($lang['edit_torrent_description'], "<input type='text' name='description' value='" . htmlsafechars($row['description']) . "' class='w-100' />", 1);
$HTMLOUT .= tr($lang['edit_nfo'], "<input type='radio' name='nfoaction' value='keep' checked class='right5' />{$lang['edit_keep_current']}<br><input type='radio' name='nfoaction' value='update' class='right5' />{$lang['edit_update']}<br><input type='file' name='nfo' class='w-100' />", 1);
if ((strpos($row['ori_descr'], '<') === false) || (strpos($row['ori_descr'], '&lt;') !== false)) {
    $c = '';
} else {
    $c = ' checked';
}
$HTMLOUT .= tr($lang['edit_description'], '' . BBcode($row['ori_descr']) . "<br>({$lang['edit_tags']})", 1);
$s = "<select name='type'>\n";
$cats = genrelist();
foreach ($cats as $subrow) {
    $s .= "<option value='" . (int)$subrow['id'] . "'";
    if ($subrow['id'] == $row['category']) {
        $s .= " selected";
    }
    $s .= '>' . htmlsafechars($subrow['name']) . "</option>\n";
}
$s .= "</select>\n";
$HTMLOUT .= tr($lang['edit_type'], $s, 1);

$subs_list .= "
        <div class='flex-grid'>";
foreach ($subs as $s) {
    $subs_list .= "
            <div class='flex_cell_5'>
                <input name='subs[]' type='checkbox' class='reset' value='{$s['id']}' />
                <image class='sub_flag left5' src='{$s['pic']}' alt='{$s['name']}' title='" . htmlsafechars($s['name']) . "' />
                <span class='left5'>" . htmlsafechars($s['name']) . "</span>
            </div>";
}
$subs_list .= "
        </div>";

$HTMLOUT .= tr('Subtitiles', $subs_list, 1);

$rg = "<select name='release_group'>\n<option value='scene'" . ($row['release_group'] == 'scene' ? " selected" : '') . ">Scene</option>\n<option value='p2p'" . ($row['release_group'] == 'p2p' ? " selected" : '') . ">p2p</option>\n<option value='none'" . ($row['release_group'] == 'none' ? " selected" : '') . ">None</option> \n</select>\n";
$HTMLOUT .= tr('Release Group', $rg, 1);
$HTMLOUT .= tr($lang['edit_visible'], "<input type='checkbox' name='visible'" . (($row['visible'] == 'yes') ? " checked" : '') . " value='1' /> {$lang['edit_visible_mainpage']}<br><table class='table table-bordered table-striped'><tr><td class='embedded'>{$lang['edit_visible_info']}</td></tr></table>", 1);
if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT .= tr($lang['edit_banned'], "<input type='checkbox' name='banned'" . (($row['banned'] == 'yes') ? " checked" : '') . " value='1' /> {$lang['edit_banned']}", 1);
}
$HTMLOUT .= tr($lang['edit_recommend_torrent'], "<input type='radio' name='recommended' " . (($row['recommended'] == 'yes') ? "checked" : '') . " value='yes' class='right5' />Yes!<input type='radio' name='recommended' " . ($row['recommended'] == 'no' ? "checked" : '') . " value='no' class='right5' />No!<br><font class='small' >{$lang['edit_recommend']}</font>", 1);
if ($CURUSER['class'] >= UC_UPLOADER) {
    $HTMLOUT .= tr('Nuked', "<input type='radio' name='nuked'" . ($row['nuked'] == 'yes' ? " checked" : '') . " value='yes' class='right5' />Yes <input type='radio' name='nuked'" . ($row['nuked'] == 'no' ? " checked" : '') . " value='no' class='right5' />No", 1);
    $HTMLOUT .= tr('Nuke Reason', "<input type='text' name='nukereason' value='" . htmlsafechars($row['nukereason']) . "' class='w-100' />", 1);
}
if ($CURUSER['class'] >= UC_STAFF && !XBT_TRACKER) {
    $HTMLOUT .= tr('Free Leech', ($row['free'] != 0 ? "<input type='checkbox' name='fl' value='1' /> Remove Freeleech" : "
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
        $HTMLOUT .= tr('Free Leech Duration', ($row['free'] != 1 ? 'Until ' . get_date($row['free'], 'DATE') . '
         (' . mkprettytime($row['free'] - TIME_NOW) . ' to go)' : 'Unlimited'), 1);
    }
    $HTMLOUT .= tr('Silver torrent ', ($row['silver'] != 0 ? "<input type='checkbox' name='slvr' value='1' /> Remove Silver torrent" : "
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
        $HTMLOUT .= tr('Silver Torrent Duration', ($row['silver'] != 1 ? 'Until ' . get_date($row['silver'], 'DATE') . '
         (' . mkprettytime($row['silver'] - TIME_NOW) . ' to go)' : 'Unlimited'), 1);
    }
}

if ($CURUSER['class'] >= UC_STAFF && $CURUSER['class'] == UC_MAX) {
    if ($row['allow_comments'] == 'yes') {
        $messc = '&#160;Comments are allowed for everyone on this torrent!';
    } else {
        $messc = '&#160;Only staff members are able to comment on this torrent!';
    }
    $HTMLOUT .= "<tr>
    <td><span class='has-text-danger'>&#160;*&#160;</span><b>&#160;{$lang['edit_comment']}</b></td>
    <td>
    <select name='allow_comments'>
    <option value='" . htmlsafechars($row['allow_comments']) . "'>" . htmlsafechars($row['allow_comments']) . "</option>
    <option value='yes'>Yes</option><option value='no'>No</option></select>{$messc}</td></tr>\n";
}

if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT .= tr('Sticky', "<input type='checkbox' name='sticky'" . (($row['sticky'] == 'yes') ? " checked" : '') . " value='yes' />Sticky this torrent !", 1);
    $HTMLOUT .= tr($lang['edit_anonymous'], "<input type='checkbox' name='anonymous'" . (($row['anonymous'] == 'yes') ? " checked" : '') . " value='1' />{$lang['edit_anonymous1']}", 1);
    if (!XBT_TRACKER) {
        $HTMLOUT .= tr('VIP Torrent?', "<input type='checkbox' name='vip'" . (($row['vip'] == 1) ? " checked" : '') . " value='1' /> If this one is checked, only VIPs can download this torrent", 1);
    }
    if (XBT_TRACKER) {
        $HTMLOUT .= tr('Freeleech', "<input type='checkbox' name='freetorrent'" . (($row['freetorrent'] == 1) ? " checked" : '') . " value='1' /> Check this to make this torrent freeleech", 1);
    }
}

$HTMLOUT .= "
    <tr>
    <td class='rowhead'><b>Genre</b>&#160;&#160;&#160;<br>Optional&#160;&#160;&#160;
    </td>
    <td>
    <table class='table table-bordered table-striped'>
    <tr>
    <td>
    <input type='radio' name='genre' value='keep' checked class='right5' />Dont touch it [ Current: " . htmlsafechars($row['newgenre']) . " ]<br></td>
    <td><input type='radio' name='genre' value='movie' class='right5' />Movie</td>
    <td><input type='radio' name='genre' value='music' class='right5' />Music</td>
    <td><input type='radio' name='genre' value='game' class='right5' />Game</td>
    <td><input type='radio' name='genre' value='apps' class='right5' />Apps</td>
    <td><input type='radio' name='genre' value='none' class='right5' />None</td>
    </tr>
    </table>
    <table class='table table-bordered table-striped'>
    <tr>
    <td colspan='4'>
    <label>
    <input type='hidden' class='Depends on genre being movie or genre being music' /></label>";
$movie = [
    'Action',
    'Comedy',
    'Thriller',
    'Adventure',
    'Family',
    'Adult',
    'Sci-fi',
];
for ($x = 0; $x < count($movie); ++$x) {
    $HTMLOUT .= "<label><input type='checkbox' value='$movie[$x]'  name='movie[]' class='DEPENDS ON genre BEING movie' />$movie[$x]</label>";
}
$music = [
    'Hip Hop',
    'Rock',
    'Pop',
    'House',
    'Techno',
    'Commercial',
];
for ($x = 0; $x < count($music); ++$x) {
    $HTMLOUT .= "<label><input type='checkbox' value='$music[$x]' name='music[]' class='DEPENDS ON genre BEING music' />$music[$x]</label>";
}
$game = [
    'Fps',
    'Strategy',
    'Adventure',
    '3rd Person',
    'Acton',
];
for ($x = 0; $x < count($game); ++$x) {
    $HTMLOUT .= "<label><input type='checkbox' value='$game[$x]' name='game[]' class='DEPENDS ON genre BEING game' />$game[$x]</label>";
}
$apps = [
    'Burning',
    'Encoding',
    'Anti-Virus',
    'Office',
    'Os',
    'Misc',
    'Image',
];
for ($x = 0; $x < count($apps); ++$x) {
    $HTMLOUT .= "<label><input type='checkbox' value='$apps[$x]' name='apps[]' class='DEPENDS ON genre BEING apps' />$apps[$x]</label>";
}
$HTMLOUT .= "
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan='2'>
                    <div class='has-text-centered'>
                        <input type='submit' value='{$lang['edit_submit']}' class='button is-small right20' />
                        <input type='reset' value='{$lang['edit_revert']}' class='button is-small' />
                    </div>
                </td>
            </tr>
        </table>
    </form>
    <form name='delete_form' method='post' action='delete.php'>";
$body = "
            <tr>
                <td class='colhead' colspan='2'><b>{$lang['edit_delete_torrent']}.</b> {$lang['edit_reason']}</td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='1' class='right5' />{$lang['edit_dead']}
                </td>
                <td> {$lang['edit_peers']}</td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='2' class='right5' />{$lang['edit_dupe']}
                </td>
                <td><input type='text' size='40' name='reason[]' /></td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='3' class='right5' />{$lang['edit_nuked']}
                </td>
                <td><input type='text' size='40' name='reason[]' /></td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='4' class='right5' />{$lang['edit_rules']}
                </td>
                <td><input type='text' size='40' name='reason[]' class='right5' />({$lang['edit_req']})</td>
            </tr>
            <tr>
                <td>
                    <input name='reasontype' type='radio' value='5' checked class='right5' />{$lang['edit_other']}
                </td>
                <td>
                    <input type='text' size='40' name='reason[]' class='right5' />({$lang['edit_req']})
                    <input type='hidden' name='id' value='$id' />
                </td>
            </tr>";
if (isset($_GET['returnto'])) {
    $body .= "
        <input type='hidden' name='returnto' value='" . htmlsafechars($_GET['returnto']) . "' />\n";
}
$body .= "
            <tr>
                <td colspan='2'>
                    <div class='has-text-centered margin20'>
                        <input type='submit' value='{$lang['edit_delete']}' class='button is-small' />
                    </div>
                </td>
            </tr>";
$HTMLOUT .= main_table($body, null, 'top20') . "
    </form>";

echo stdhead("{$lang['edit_stdhead']} '{$row['name']}'", true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
