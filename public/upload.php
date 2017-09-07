<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CACHE_DIR . 'subs.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('upload'));
$stdhead = [
    'css' => [
        'c1970cc2bf64b39bc420e74f0437f096.min',
    ],
];
$stdfoot = [
    'js' => [
        '859c6d8f53d8ba83a5a80596082ae411.min',
    ],
];
$HTMLOUT = $offers = $subs_list = $request = $descr = '';
if ($CURUSER['class'] < UC_UPLOADER or $CURUSER['uploadpos'] == 0 || $CURUSER['uploadpos'] > 1 || $CURUSER['suspended'] == 'yes') {
    stderr($lang['upload_sorry'], $lang['upload_no_auth']);
}
//==== request dropdown
$res_request = sql_query('SELECT id, request_name FROM requests WHERE filled_by_user_id = 0 ORDER BY request_name ASC');
$request = '
    <tr>
    <td  valign="middle" align="right"><span style="font-weight: bold;">Request:</span></td>
    <td valign="top" align="left" >
        <select name="request">
        <option class="body" value="0"> Requests </option>';
if ($res_request) {
    while ($arr_request = mysqli_fetch_assoc($res_request)) {
        $request .= '<option class="body" value="' . (int)$arr_request['id'] . '">' . htmlsafechars($arr_request['request_name']) . '</option>';
    }
} else {
    $request .= '<option class="body" value="0">Currently no requests</option>';
}
$request .= "</select><span class='left5'>If you are filling a request please select it here so interested members can be notified.</span></td>
    </tr>";
//=== offers list if member has made any offers
$res_offer = sql_query('SELECT id, offer_name FROM offers WHERE offered_by_user_id = ' . sqlesc($CURUSER['id']) . ' AND status = \'approved\' ORDER BY offer_name ASC');
if (mysqli_num_rows($res_offer) > 0) {
    $offers = '
    <tr>
    <td valign="middle" align="right"><span style="font-weight: bold;">My Offers:</span></td>
    <td valign="top" align="left" >
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
    <div align='center'>
    <form id='upload_form' name='upload_form' enctype='multipart/form-data' action='./takeupload.php' method='post'>
    <input type='hidden' name='MAX_FILE_SIZE' value='{$INSTALLER09['max_torrent_size']}' />
    <p class='top10'>{$lang['upload_announce_url']}:<b><input type='text' class='left5 text-center' size='80' readonly='readonly' value='{$INSTALLER09['announce_urls'][0]}' onclick='select()' /></b></p>";
$HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='10'>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_imdb_url']}</td>
    <td valign='top' align='left'><input type='text' name='url' size='80' /><br>{$lang['upload_imdb_tfi']}{$lang['upload_imdb_rfmo']}</td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_poster']}</td>
    <td valign='top' align='left'><input type='text' name='poster' size='80' /><br>{$lang['upload_poster1']}</td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_youtube']}</td>
    <td valign='top' align='left'><input type='text' name='youtube' size='80' /><br>({$lang['upload_youtube_info']})</td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'><b>{$lang['upload_bitbucket']}</b></td>
    <td valign='top' align='left'>
    <iframe src='imgup.html' style='width:600px; height:48px; border:none' frameborder='0'></iframe>
    <br>{$lang['upload_bitbucket_1']}
    </td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_torrent']}</td>
    <td valign='top' align='left'><input type='file' name='file' id='torrent' onchange='getname()' size='80' /></td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_name']}</td>
    <td valign='top' align='left'><input type='text' id='name' name='name' size='80' /><br>({$lang['upload_filename']})</td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_tags']}</td>
    <td valign='top' align='left'><input type='text' name='tags' size='80' /><br>({$lang['upload_tag_info']})</td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_small_description']}</td>
    <td valign='top' align='left'><input type='text' name='description' size='80' /><br>({$lang['upload_small_descr']})</td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_nfo']}</td>
    <td valign='top' align='left'><input type='file' name='nfo' size='80' /><br>({$lang['upload_nfo_info']})</td>
    </tr>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_description']}</td>
    <td valign='top' align='left' style='white-space: nowrap;'>" . BBcode(false) . "
    <br>({$lang['upload_html_bbcode']})</td>
    </tr>";
$s = "<select name='type'>\n<option value='0'>({$lang['upload_choose_one']})</option>\n";
$cats = genrelist();
foreach ($cats as $row) {
    $s .= "<option value='" . (int)$row['id'] . "'>" . htmlsafechars($row['name']) . "</option>\n";
}
$s .= "</select>\n";
$HTMLOUT .= "<tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_type']}</td>
    <td valign='top' align='left'>$s</td>
    </tr>";
$HTMLOUT .= $offers;
$HTMLOUT .= $request;
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
$rg = "<select name='release_group'>\n<option value='none'>None</option>\n<option value='p2p'>p2p</option>\n<option value='scene'>Scene</option>\n</select>\n";
$HTMLOUT .= tr('Release Type', $rg, 1);
$HTMLOUT .= tr("{$lang['upload_anonymous']}", "<div class='flex'><input type='checkbox' name='uplver' value='yes' /><span class='left5'>{$lang['upload_anonymous1']}</span></div>", 1);
if ($CURUSER['class'] == UC_MAX) {
    $HTMLOUT .= tr("{$lang['upload_comment']}", "<div class='flex'><input type='checkbox' name='allow_commentd' value='yes' /><span class='left5'>{$lang['upload_discom1']}</span></div>", 1);
}
$HTMLOUT .= tr('Strip ASCII', "<div class='flex'><input type='checkbox' name='strip' value='strip' checked='checked' /><span class='left5'><a href='http://en.wikipedia.org/wiki/ASCII_art' target='_blank'>What is this ?</a></span></div>", 1);
if ($CURUSER['class'] >= UC_UPLOADER and XBT_TRACKER == false) {
    $HTMLOUT .= "<tr>
    <td class='heading' valign='top' align='right'>Free Leech</td>
    <td valign='top' align='left'>
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
    <td class='heading' valign='top' align='right'>Silver Torrent</td>
    <td valign='top' align='left'>
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
    $HTMLOUT .= tr('Freeleech', "<div class='flex'><input type='checkbox' name='freetorrent' value='1' /><span class='left5'>Check this to make this torrent freeleech</span></div>", 1);
}

$genres = [
            'Movie',
            'Music',
            'Game',
            'Apps',
        ];

$HTMLOUT .= "
    <tr>
        <td class='heading' align='right'><b>Genre</b></td>
        <td>
            <div class='flex-grid'>";

for ($x = 0; $x < count($genres); ++$x) {
    $HTMLOUT .= "
                <div class='flex_cell_5'>
                    <input type='radio' value='" . strtolower($genres[$x]) . "' name='genre' />
                    <span class='left5'>{$genres[$x]}</span>
                </div>";
}

$HTMLOUT .= "
                <div class='flex_cell_5'>
                    <input type='radio' name='genre' value='' checked='checked' />
                    <span class='left5'>None</span>
                </div>
            </div>
            <label style='margin-bottom: 1em; padding-bottom: 1em; border-bottom: 3px silver groove;'>
            <input type='hidden' class='Depends on genre being movie or genre being music' /></label>
            <div class='flex-grid'>";

//== 09 Genre mod no mysql by Traffic
//$HTMLOUT.= "
    //<tr>
        //<td class='heading' class='text-right'>
            //<b>Genre</b>
        //</td>
        //<td class='text-left'>
            //<table>
                //<tr>
    //<td style='border:none'><input type='radio' name='genre' value='movie' />Movie</td>
    //<td style='border:none'><input type='radio' name='genre' value='music' />Music</td>
    //<td style='border:none'><input type='radio' name='genre' value='game' />Game</td>
    //<td style='border:none'><input type='radio' name='genre' value='apps' />Apps</td>
    //<td style='border:none'><input type='radio' name='genre' value='' checked='checked' />None</td>
    //</tr>
    //</table>
    //<table>
    //<tr>
    //<td colspan='4' style='border:none'>
    //<label style='margin-bottom: 1em; padding-bottom: 1em; border-bottom: 3px silver groove;'>
    //<input type='hidden' class='Depends on genre being movie or genre being music' /></label>";
$movie = array(
    'Action',
    'Comedy',
    'Thriller',
    'Adventure',
    'Family',
    'Adult',
    'Sci-fi'
);
for ($x = 0; $x < count($movie); $x++) {
    $HTMLOUT.= "
                <label>
                    <input type='checkbox' value='{$movie[$x]}' name='{movie[]}' class='DEPENDS ON genre BEING movie' />
                    <span class='left5'>{$movie[$x]}</span>
                </label>";
}
$music = array(
    'Hip Hop',
    'Rock',
    'Pop',
    'House',
    'Techno',
    'Commercial'
);
for ($x = 0; $x < count($music); $x++) {
    $HTMLOUT.= "
                <label>
                    <input type='checkbox' value='{$music[$x]}' name='{music[]}' class='DEPENDS ON genre BEING music' />
                    <span class='left5'>{$music[$x]}</span>
                </label>";
}
$game = array(
    'Fps',
    'Strategy',
    'Adventure',
    '3rd Person',
    'Acton'
);
for ($x = 0; $x < count($game); $x++) {
    $HTMLOUT.= "
                <label>
                    <input type='checkbox' value='{$game[$x]}' name='{game[]}' class='DEPENDS ON genre BEING game' />
                    <span class='left5'>{$game[$x]}</span>
                </label>";
}
$apps = array(
    'Burning',
    'Encoding',
    'Anti-Virus',
    'Office',
    'Os',
    'Misc',
    'Image'
);
for ($x = 0; $x < count($apps); $x++) {
    $HTMLOUT.= "
                <label>
                    <input type='checkbox' value='{$apps[$x]}' name='{apps[]}' class='DEPENDS ON genre BEING apps' />
                    <span class='left5'>{$apps[$x]}</span>
                </label>";
}
$HTMLOUT.= "</td></tr></table></td></tr>";
//== End


if ($CURUSER['class'] >= UC_UPLOADER and XBT_TRACKER == false) {
    $HTMLOUT .= tr('Vip Torrent', "<div class='flex'><input type='checkbox' name='vip' value='1' /><span class='left5'>If this one is checked, only Vip's can download this torrent</span></div>", 1);
}
$HTMLOUT .= "
        <tr>
            <td align='center' colspan='2'><input type='submit' class='btn' value='{$lang['upload_submit']}' /></td>
        </tr>
        </table>
        </form>
        </div>";
////////////////////////// HTML OUTPUT //////////////////////////
echo stdhead($lang['upload_stdhead'], true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
