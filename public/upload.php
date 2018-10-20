<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CACHE_DIR . 'subs.php';
check_user_status();
global $CURUSER, $site_config, $session;

$lang = array_merge(load_language('global'), load_language('upload'), load_language('bitbucket'));
$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('upload_js'),
        get_file_name('dragndrop_js'),
        get_file_name('sceditor_js'),
    ],
];
$HTMLOUT = $offers = $subs_list = $request = $descr = '';
if ($CURUSER['class'] < UC_UPLOADER || $CURUSER['uploadpos'] === 0 || $CURUSER['uploadpos'] > 1 || $CURUSER['suspended'] === 'yes') {
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
        $request .= '<option class="body" value="' . (int) $arr_request['id'] . '">' . htmlsafechars($arr_request['request_name']) . '</option>';
    }
} else {
    $request .= '<option class="body" value="0">Currently no requests</option>';
}
$request .= '</select><span>If you are filling a request please select it here so interested members can be notified.</span></td>
    </tr>';
//=== offers list if member has made any offers
$res_offer = sql_query('SELECT id, offer_name
                        FROM offers
                        WHERE offered_by_user_id = ' . sqlesc($CURUSER['id']) . " AND status = 'approved'
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
        $offers .= '<option class="body" value="' . (int) $arr_offer['id'] . '">' . htmlsafechars($arr_offer['offer_name']) . '</option>';
    }
    $offers .= '</select> If you are uploading one of your offers, please select it here so interested members will be notified.</td>
    </tr>';
}
$announce_url = $site_config['announce_urls'][0];
if (get_scheme() === 'https') {
    $announce_url = $site_config['announce_urls'][1];
}
$HTMLOUT .= "
    <form id='upload_form' name='upload_form' enctype='multipart/form-data' action='" . $site_config['baseurl'] . "/takeupload.php' method='post'>
    <input type='hidden' name='MAX_FILE_SIZE' value='{$site_config['max_torrent_size']}'>
    <input type='hidden' id='csrf' name='csrf' value='" . $session->get('csrf_token') . "'>
    <h1 class='has-text-centered'>Upload a Torrent</h1>
    <p class='top10 has-text-centered'>{$lang['upload_announce_url']}:<br>
        <input type='text' class='has-text-centered w-100 top10' readonly='readonly' value='{$announce_url}' onclick='select()'>
    </p>
    <div class='banner_container has-text-centered w-100'></div>";
$HTMLOUT .= "<table class='table table-bordered table-striped top20 bottom20'>
    <tr>
    <td class='rowhead'>{$lang['upload_imdb_url']}</td>
    <td>
        <input type='url' id='url' name='url' class='w-100' data-csrf='" . $session->get('csrf_token') . "'><br>
        {$lang['upload_imdb_tfi']}{$lang['upload_imdb_rfmo']}
        <div class='imdb_outer'>
            <div class='imdb_inner'>
            </div>
        </div>
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_isbn']}</td>
    <td>
        <input type='text' id='isbn' name='isbn' class='w-100' data-csrf='" . $session->get('csrf_token') . "'><br>
        {$lang['upload_isbn_details']}
        <div class='isbn_outer'>
            <div class='isbn_inner'>
            </div>
        </div>
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_poster']}</td>
    <td>
        <input type='url' id='poster' name='poster' class='w-100' required>
        <br>{$lang['upload_poster1']}
        <div class='poster_container has-text-centered'></div>
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_youtube']}</td>
    <td><input type='url' id='youtube' name='youtube' class='w-100'><br>({$lang['upload_youtube_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'><b>{$lang['upload_bitbucket']}</b></td>
    <td class='has-text-centered'>
        <div id='droppable' class='droppable bg-03'>
            <span id='comment'>{$lang['bitbucket_dragndrop']}</span>
            <div id='loader' class='is-hidden'>
                <img src='{$site_config['pic_baseurl']}forums/updating.svg' alt='Loading...'>
            </div>
        </div>
        <div class='output-wrapper output'></div>
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_torrent']}</td>
    <td>
        <input type='file' name='file' id='torrent' onchange='getname()' class='inputfile' required>
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_name']}</td>
    <td><input type='text' id='name' name='name' class='w-100' required><br>({$lang['upload_filename']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_tags']}</td>
    <td><input type='text' name='tags' class='w-100'><br>({$lang['upload_tag_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_small_description']}</td>
    <td><input type='text' name='description' class='w-100' maxlength='120'><br>({$lang['upload_small_descr']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_nfo']}</td>
    <td><input type='file' name='nfo'><br>({$lang['upload_nfo_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_description']}</td>
    <td class='is-paddingless'>" . BBcode() . "
    <br>({$lang['upload_html_bbcode']})</td>
    </tr>";
$s = "<select name='type'>\n<option value='0'>({$lang['upload_choose_one']})</option>\n";
$cats = genrelist();
foreach ($cats as $row) {
    $s .= "<option value='" . (int) $row['id'] . "'>" . htmlsafechars($row['name']) . "</option>\n";
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
                    <input name='subs[]' type='checkbox' value='{$s['id']}'>
                    <image class='sub_flag' src='{$s['pic']}' alt='" . htmlsafechars($s['name']) . "'>
                </span>
                <span class='has-text-centered'>" . htmlsafechars($s['name']) . '</span>
            </div>';
}
$subs_list .= '
        </div>';

$HTMLOUT .= tr('Subtitiles', $subs_list, 1);
$rg = "<select name='release_group'>\n<option value='none'>None</option>\n<option value='p2p'>p2p</option>\n<option value='scene'>Scene</option>\n</select>\n";
$HTMLOUT .= tr('Release Type', $rg, 1);
$HTMLOUT .= tr("{$lang['upload_anonymous']}", "<div class='level-left'><input type='checkbox' name='uplver' id='uplver' value='yes'><label for='uplver' class='left5'>{$lang['upload_anonymous1']}</label></div>", 1);
if ($CURUSER['class'] === UC_MAX) {
    $HTMLOUT .= tr("{$lang['upload_comment']}", "<div class='level-left'><input type='checkbox' name='allow_commentd' id='allow_commentd' value='yes'><label for='allow_commentd' class='left5'>{$lang['upload_discom1']}</label></div>", 1);
}
$HTMLOUT .= tr('Strip ASCII', "<div class='level-left'><input type='checkbox' name='strip' id='strip' value='strip'><label for='strip' class='left5'><a href='https://en.wikipedia.org/wiki/ASCII_art' target='_blank'>What is this?</a></label></div>", 1);
if ($CURUSER['class'] >= UC_UPLOADER && !XBT_TRACKER) {
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
require_once PARTIALS_DIR . 'genres.php';

if ($CURUSER['class'] >= UC_UPLOADER && !XBT_TRACKER) {
    $HTMLOUT .= tr('Vip Torrent', "<div class='level-left'><input type='checkbox' name='vip' id='vip' value='1'><label for='vip' class='left5'>If this one is checked, only Vip's can download this torrent</label></div>", 1);
}
$HTMLOUT .= "
        <tr>
            <td colspan='2'>
                <div class='has-text-centered'>
                    <input type='submit' class='button is-small' value='{$lang['upload_submit']}'>
                </div>
            </td>
        </tr>
        </table>
        </form>";

echo stdhead($lang['upload_stdhead'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
