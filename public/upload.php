<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CACHE_DIR . 'subs.php';
check_user_status();
global $CURUSER, $site_config;
$lang = array_merge(load_language('global'), load_language('upload'));
$stdhead = [
    'css' => [
    ],
];
$stdfoot = [
    'js' => [
        get_file('upload_js')
    ],
];
$HTMLOUT = $offers = $subs_list = $request = $descr = '';
if ($CURUSER['class'] < UC_UPLOADER or $CURUSER['uploadpos'] == 0 || $CURUSER['uploadpos'] > 1 || $CURUSER['suspended'] == 'yes') {
    stderr($lang['upload_sorry'], $lang['upload_no_auth']);
}
$res_request = sql_query('SELECT id, request_name FROM requests WHERE filled_by_user_id = 0 ORDER BY request_name ASC') or sqlerr(__FILE__, __LINE__);
$request = '
    <tr>
    <td><span>Request:</span></td>
    <td>
        <select name="request">
        <option class="body" value="0"> Requests </option>';
if ($res_request) {
    while ($arr_request = mysqli_fetch_assoc($res_request)) {
        $request .= '<option class="body" value="' . (int)$arr_request['id'] . '">' . htmlsafechars($arr_request['request_name']) . '</option>';
    }
} else {
    $request .= '<option class="body" value="0">Currently no requests</option>';
}
$request .= "</select><span>If you are filling a request please select it here so interested members can be notified.</span></td>
    </tr>";
//=== offers list if member has made any offers
$res_offer = sql_query("SELECT id, offer_name
                        FROM offers
                        WHERE offered_by_user_id = " . sqlesc($CURUSER['id']) . " AND status = 'approved'
                        ORDER BY offer_name ASC") or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res_offer) > 0) {
    $offers = '
    <tr>
    <td><span>My Offers:</span></td>
    <td>
    <select name="offer">
    <option class="body" value="0">My Offers</option>';
    $message = '<option class="body" value="0">Your have no approved offers yet</option>';
    while ($arr_offer = mysqli_fetch_assoc($res_offer)) {
        $offers .= '<option class="body" value="' . (int)$arr_offer['id'] . '">' . htmlsafechars($arr_offer['offer_name']) . '</option>';
    }
    $offers .= '</select> If you are uploading one of your offers, please select it here so interested members will be notified.</td>
    </tr>';
}
$HTMLOUT .= "
    <form id='upload_form' name='upload_form' enctype='multipart/form-data' action='./takeupload.php' method='post'>
    <input type='hidden' name='MAX_FILE_SIZE' value='{$site_config['max_torrent_size']}' />
    <h1 class='has-text-centered'>Upload a Torrent</h1>
    <p class='top10 has-text-centered'>{$lang['upload_announce_url']}:<br><input type='text' class='has-text-centered w-100 top10' readonly='readonly' value='{$site_config['announce_urls'][0]}?torrent_pass={$CURUSER['torrent_pass']}' onclick='select()' /></p>";
$HTMLOUT .= "<table class='table table-bordered table-striped top20 bottom20'>
    <tr>
    <td class='rowhead'>{$lang['upload_imdb_url']}</td>
    <td><input type='text' name='url' class='w-100' /><br>{$lang['upload_imdb_tfi']}{$lang['upload_imdb_rfmo']}</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_poster']}</td>
    <td><input type='text' name='poster' class='w-100' /><br>{$lang['upload_poster1']}</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_youtube']}</td>
    <td><input type='text' name='youtube' class='w-100' /><br>({$lang['upload_youtube_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'><b>{$lang['upload_bitbucket']}</b></td>
    <td>
    <iframe src='imgup.html'></iframe>
    <br>{$lang['upload_bitbucket_1']}
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_torrent']}</td>
    <td>
        <input type='file' name='file' id='torrent' onchange='getname()' class='inputfile' />
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_name']}</td>
    <td><input type='text' id='name' name='name' class='w-100' /><br>({$lang['upload_filename']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_tags']}</td>
    <td><input type='text' name='tags' class='w-100' /><br>({$lang['upload_tag_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_small_description']}</td>
    <td><input type='text' name='description' class='w-100' /><br>({$lang['upload_small_descr']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_nfo']}</td>
    <td><input type='file' name='nfo' /><br>({$lang['upload_nfo_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_description']}</td>
    <td>" . BBcode() . "
    <br>({$lang['upload_html_bbcode']})</td>
    </tr>";
$s = "<select name='type'>\n<option value='0'>({$lang['upload_choose_one']})</option>\n";
$cats = genrelist();
foreach ($cats as $row) {
    $s .= "<option value='" . (int)$row['id'] . "'>" . htmlsafechars($row['name']) . "</option>\n";
}
$s .= "</select>\n";
$HTMLOUT .= "<tr>
    <td class='rowhead'>{$lang['upload_type']}</td>
    <td>$s</td>
    </tr>";
$HTMLOUT .= $offers;
$HTMLOUT .= $request;
$subs_list .= "
        <div class='level-center'>";
foreach ($subs as $s) {
    $subs_list .= "
            <div class='w-15 margin10 tooltipper bordered level-center' title='" . htmlsafechars($s['name']) . "'>
                <span class='has-text-centered'>
                    <input name='subs[]' type='checkbox' value='{$s['id']}' />
                    <image class='sub_flag' src='{$s['pic']}' alt='" . htmlsafechars($s['name']) . "' />
                </span>
                <span class='has-text-centered'>" . htmlsafechars($s['name']) . "</span>
            </div>";
}
$subs_list .= "
        </div>";

$HTMLOUT .= tr('Subtitiles', $subs_list, 1);
$rg = "<select name='release_group'>\n<option value='none'>None</option>\n<option value='p2p'>p2p</option>\n<option value='scene'>Scene</option>\n</select>\n";
$HTMLOUT .= tr('Release Type', $rg, 1);
$HTMLOUT .= tr("{$lang['upload_anonymous']}", "<div class='flex'><input type='checkbox' name='uplver' value='yes' /><span>{$lang['upload_anonymous1']}</span></div>", 1);
if ($CURUSER['class'] == UC_MAX) {
    $HTMLOUT .= tr("{$lang['upload_comment']}", "<div class='flex'><input type='checkbox' name='allow_commentd' value='yes' /><span>{$lang['upload_discom1']}</span></div>", 1);
}
$HTMLOUT .= tr('Strip ASCII', "<div class='flex'><input type='checkbox' name='strip' value='strip' checked='checked' /><span><a href='http://en.wikipedia.org/wiki/ASCII_art' target='_blank'>What is this ?</a></span></div>", 1);
if ($CURUSER['class'] >= UC_UPLOADER and XBT_TRACKER == false) {
    $HTMLOUT .= "<tr>
    <td class='rowhead'>Free Leech</td>
    <td>
    <select name='free_length'>
    <option value='0'>Not Free</option>
    <option value='42'>Free for 1 day</option>
    <option value='1'>Free for 1 week</option>
    <option value='2'>Free for 2 weeks</option>
    <option value='4'>Free for 4 weeks</option>
    <option value='8'>Free for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select></td>
    </tr>";
    $HTMLOUT .= "<tr>
    <td class='rowhead'>Silver Torrent</td>
    <td>
    <select name='half_length'>
    <option value='0'>Not Silver</option>
    <option value='42'>Silver for 1 day</option>
    <option value='1'>Silver for 1 week</option>
    <option value='2'>Silver for 2 weeks</option>
    <option value='4'>Silver for 4 weeks</option>
    <option value='8'>Silver for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select></td>
    </tr>";
}
if (XBT_TRACKER == true) {
    $HTMLOUT .= tr('Freeleech', "<div class='flex'><input type='checkbox' name='freetorrent' value='1' /><span>Check this to make this torrent freeleech</span></div>", 1);
}

$genres = [
            'Movie',
            'Music',
            'Game',
            'Apps',
        ];

$HTMLOUT .= "
    <tr>
        <td class='rowhead'><b>Genre</b></td>
        <td>
            <div class='flex-grid'>";

for ($x = 0; $x < count($genres); ++$x) {
    $HTMLOUT .= "
                <div class='flex_cell_5'>
                    <input type='radio' value='" . strtolower($genres[$x]) . "' name='genre' />
                    <span>{$genres[$x]}</span>
                </div>";
}

$HTMLOUT .= "
                <div class='flex_cell_5'>
                    <input type='radio' name='genre' value='' checked='checked' />
                    <span>None</span>
                </div>
            </div>
            <label>
            <input type='hidden' class='Depends on genre being movie or genre being music' /></label>
            <div class='flex-grid'>";

$movie = [
    'Action',
    'Comedy',
    'Thriller',
    'Adventure',
    'Family',
    'Adult',
    'Sci-fi'
];
for ($x = 0; $x < count($movie); $x++) {
    $HTMLOUT.= "
                <label>
                    <input type='checkbox' value='{$movie[$x]}' name='{movie[]}' class='DEPENDS ON genre BEING movie' />
                    <span>{$movie[$x]}</span>
                </label>";
}
$music = [
    'Hip Hop',
    'Rock',
    'Pop',
    'House',
    'Techno',
    'Commercial'
];
for ($x = 0; $x < count($music); $x++) {
    $HTMLOUT.= "
                <label>
                    <input type='checkbox' value='{$music[$x]}' name='{music[]}' class='DEPENDS ON genre BEING music' />
                    <span>{$music[$x]}</span>
                </label>";
}
$game = [
    'Fps',
    'Strategy',
    'Adventure',
    '3rd Person',
    'Acton'
];
for ($x = 0; $x < count($game); $x++) {
    $HTMLOUT.= "
                <label>
                    <input type='checkbox' value='{$game[$x]}' name='{game[]}' class='DEPENDS ON genre BEING game' />
                    <span>{$game[$x]}</span>
                </label>";
}
$apps = [
    'Burning',
    'Encoding',
    'Anti-Virus',
    'Office',
    'Os',
    'Misc',
    'Image'
];
for ($x = 0; $x < count($apps); $x++) {
    $HTMLOUT.= "
                <label>
                    <input type='checkbox' value='{$apps[$x]}' name='{apps[]}' class='DEPENDS ON genre BEING apps' />
                    <span>{$apps[$x]}</span>
                </label>";
}
$HTMLOUT.= "
            </td>
        </tr>";


if ($CURUSER['class'] >= UC_UPLOADER and XBT_TRACKER == false) {
    $HTMLOUT .= tr('Vip Torrent', "<div class='flex'><input type='checkbox' name='vip' value='1' /><span>If this one is checked, only Vip's can download this torrent</span></div>", 1);
}
$HTMLOUT .= "
        <tr>
            <td colspan='2'>
                <div class='has-text-centered'>
                    <input type='submit' class='button' value='{$lang['upload_submit']}' />
                </div>
            </td>
        </tr>
        </table>
        </form>";

echo stdhead($lang['upload_stdhead'], true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
