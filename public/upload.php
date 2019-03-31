<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CACHE_DIR . 'subs.php';
check_user_status();
global $CURUSER, $site_config, $session, $cache, $fluent;

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
$HTMLOUT = $offers = $subs_list = $has_request = $descr = '';
if ($CURUSER['class'] < $site_config['upload_min_class'] || $CURUSER['uploadpos'] != 1 || $CURUSER['suspended'] === 'yes') {
    stderr($lang['upload_sorry'], $lang['upload_no_auth']);
}
$upload_vars = $cache->get('user_upload_variables_' . $CURUSER['id']);
if (!empty($upload_vars)) {
    $upload_vars = unserialize($upload_vars);
}
$vars = [
    'url',
    'isbn',
    'poster',
    'youtube',
    'name',
    'tags',
    'description',
    'body',
    'type',
    'request',
    'offer',
    'release_group',
    'free_length',
    'half_length',
    'genre',
    'vip',
    'uplver',
    'allow_comments',
    'strip',
    'free_length',
    'half_length',
    'subs',
    'movie',
    'tv',
    'music',
    'game',
    'apps',
];

foreach ($vars as $var) {
    if ($var === 'subs') {
        $has_subs = isset($upload_vars[$var]) ? $upload_vars[$var] : [];
    } else {
        ${$var} = isset($upload_vars[$var]) ? $upload_vars[$var] : '';
    }
}
$res_request = $fluent->from('requests')
                      ->select(null)
                      ->select('id')
                      ->select('request_name')
                      ->where('filled_by_user_id = 0')
                      ->orderBy('request_name')
                      ->fetchAll();

if ($res_request) {
    $has_request = "
            <tr>
                <td>{$lang['upload_request']}:</span></td>
                <td>
                    <select name='request' class='w-100'>
                        <option value='0'>{$lang['upload_request']}</option>";
    foreach ($res_request as $arr_request) {
        $has_request .= "
                        <option value='{$arr_request['id']}'" . ($request == $arr_request['id'] ? ' selected' : '') . '>' . htmlsafechars($arr_request['request_name']) . '</option>';
    }
    $has_request .= "
                    </select>{$lang['upload_request_msg']}
                </td>
            </tr>";
}

$res_offers = $fluent->from('offers')
                     ->select(null)
                     ->select('id')
                     ->select('offer_name')
                     ->where('offered_by_user_id = ?', $CURUSER['id'])
                     ->where('status = "approved"')
                     ->orderBy('offer_name')
                     ->fetchAll();

if ($res_offers) {
    $offers = "
            <tr>
                <td>{$lang['upload_offer']}:</td>
                <td>
                    <select name='offer' class='w-100'>
                        <option value='0'>{$lang['upload_offer']}</option>";
    foreach ($res_offers as $arr_offer) {
        $offers .= "
                        <option value='{$arr_offer['id']}'" . ($offer == $arr_offer['id'] ? ' selected' : '') . '>' . htmlsafechars($arr_offer['offer_name']) . '</option>';
    }
    $offers .= "
                    </select>{$lang['upload_offer_msg']}:
                </td>
            </tr>";
}
$announce_url = $site_config['announce_urls'][0];
if (get_scheme() === 'https') {
    $announce_url = $site_config['announce_urls'][1];
}
$HTMLOUT .= "
    <form id='upload_form' name='upload_form' enctype='multipart/form-data' action='{$site_config['baseurl']}/takeupload.php' method='post' accept-charset='utf-8'>
        <input type='hidden' name='MAX_FILE_SIZE' value='{$site_config['max_torrent_size']}'>
        <input type='hidden' id='csrf' name='csrf' value='" . $session->get('csrf_token') . "' data-ebooks=" . json_encode($site_config['ebook_cats']) . ' data-movies=' . json_encode(array_merge($site_config['movie_cats'], $site_config['tv_cats'])) . ">
        <h1 class='has-text-centered'>{$lang['updload_h1']}</h1>
        <div class='has-text-centered margin10'>{$lang['upload_announce_url']}:<br>
            <input type='text' class='has-text-centered w-100' readonly='readonly' value='{$announce_url}' id='announce_url'>
        </div>
        <div class='banner_container has-text-centered w-100'></div>
        <table class='table table-bordered table-striped top20'>";
$s = "
            <select id='upload_category' name='type' class='w-100' required>
                <option value='0'>({$lang['upload_choose_one']})</option>";
$cats = genrelist(true);
foreach ($cats as $cat) {
    foreach ($cat['children'] as $row) {
        $s .= "
                <option value='{$row['id']}'" . ($type == $row['id'] ? ' selected' : '') . '>' . htmlsafechars($cat['name']) . '::' . htmlsafechars($row['name']) . '</option>';
    }
}
$s .= '
            </select>';
$HTMLOUT .= "
            <tr>
                <td class='rowhead'>{$lang['upload_type']}</td>
                <td>$s</td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_torrent']}</td>
                <td>
                    <input type='file' name='file' id='torrent' onchange='getname()' class='inputfile'>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_name']}</td>
                <td><input type='text' id='name' name='name' maxlength='255' value='$name' class='w-100' required><br>({$lang['upload_filename']})</td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_imdb_url']}</td>
                <td>
                    <input type='url' id='url' name='url' maxlength='80' class='w-100' data-csrf='" . $session->get('csrf_token') . "' value='{$url}'><br>
                    {$lang['upload_imdb_tfi']}{$lang['upload_imdb_rfmo']}
                    <div id='imdb_outer'></div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_isbn']}</td>
                <td>
                    <input type='text' id='isbn' name='isbn' maxlength='13' class='w-100' data-csrf='" . $session->get('csrf_token') . "' value='$isbn'><br>
                    {$lang['upload_isbn_details']}
                    <div id='isbn_outer'></div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_poster']}</td>
                <td>
                    <input type='url' id='image_url' data-csrf='" . $session->get('csrf_token') . "' placeholder='External Image URL' class='w-100' onchange=\"return grab_url(event)\">
                    <input type='url' id='poster' maxlength='255' name='poster' class='w-100 is-hidden' value='$poster'>
                    <br>{$lang['upload_poster1']}
                    <div class='poster_container has-text-centered'></div>
                </td>
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
                <td class='rowhead'>{$lang['upload_youtube']}</td>
                <td><input type='url' id='youtube' name='youtube' maxlength='45' class='w-100' value='$youtube'><br>({$lang['upload_youtube_info']})</td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_tags']}</td>
                <td><input type='text' name='tags' value='$tags' class='w-100'><br>({$lang['upload_tag_info']})</td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_small_description']}</td>
                <td><input type='text' name='description' value='$description' class='w-100' maxlength='120'><br>({$lang['upload_small_descr']})</td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_nfo']}</td>
                <td><input type='file' id='nfo' name='nfo'><br>({$lang['upload_nfo_info']})</td>
            </tr>
            <tr>
                <td>{$lang['upload_strip']}</td>
                <td>
                    <div class='level-left'>
                        <input type='checkbox' name='strip' id='strip' value='strip'" . ($strip === 'strip' ? ' checked' : '') . ">
                        <label for='strip' class='left5'>
                            <a href='https://en.wikipedia.org/wiki/ASCII_art' target='_blank'>{$lang['upload_what_this']}</a>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['upload_description']}</td>
                <td class='is-paddingless'>" . BBcode($body) . "
                    <div class='margin10'>({$lang['upload_html_bbcode']})</div>
                </td>
            </tr>";
$HTMLOUT .= $offers;
$HTMLOUT .= $has_request;
$subs_list .= "
                <div id='subs' class='level-center'>";
foreach ($subs as $s) {
    $subs_list .= "
                    <div class='w-15 margin10 tooltipper bordered level-center' title='" . htmlsafechars($s['name']) . "'>
                        <span class='has-text-centered'>
                            <input name='subs[]' type='checkbox' value='{$s['id']}'" . (in_array($s['id'], $has_subs) ? ' checked' : '') . ">
                            <image class='sub_flag' src='{$s['pic']}' alt='" . htmlsafechars($s['name']) . "'>
                        </span>
                        <span class='has-text-centered'>" . htmlsafechars($s['name']) . '</span>
                    </div>';
}
$subs_list .= '
                </div>';

$HTMLOUT .= "
            <tr'>
                <td>{$lang['upload_subtitles']}</td>
                <td>{$subs_list}</td>
            </tr>";

$rg = "
            <select name='release_group' class='w-100'>
                <option value='none'>{$lang['upload_none']}</option>
                <option value='p2p'" . ($release_group === 'p2p' ? ' selected' : '') . ">p2p</option>
                <option value='scene'" . ($release_group === 'scene' ? ' selected' : '') . '>Scene</option>
            </select>';
$HTMLOUT .= tr($lang['upload_type'], $rg, 1);
$HTMLOUT .= tr("{$lang['upload_anonymous']}", "<div class='level-left'><input type='checkbox' name='uplver' id='uplver' value='1'" . ($uplver ? ' checked' : '') . "><label for='uplver' class='left5'>{$lang['upload_anonymous1']}</label></div>", 1);
if ($CURUSER['class'] >= $site_config['staff_allowed']['torrents_disable_comments']) {
    $HTMLOUT .= tr("{$lang['upload_comment']}", "<div class='level-left'><input type='checkbox' name='allow_comments' id='allow_comments' value='yes'" . ($allow_comments === 'yes' ? ' checked' : '') . "><label for='allow_comments' class='left5'>{$lang['upload_discom1']}</label></div>", 1);
}
if ($CURUSER['class'] >= UC_UPLOADER) {
    $HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['upload_free']}</td>
        <td>
            <select name='free_length' class='w-100'>
                <option value='0'>{$lang['upload_not_free']}</option>
                <option value='42'" . ($free_length == '42' ? ' selected' : '') . ">{$lang['upload_free_1_day']}</option>
                <option value='1'" . ($free_length == '1' ? ' selected' : '') . ">{$lang['upload_free_1_week']}</option>
                <option value='2'" . ($free_length == '2' ? ' selected' : '') . ">{$lang['upload_free_2_weeks']}</option>
                <option value='4'" . ($free_length == '4' ? ' selected' : '') . ">{$lang['upload_free_4_weeks']}</option>
                <option value='8'" . ($free_length == '8' ? ' selected' : '') . ">{$lang['upload_free_8_weeks']}</option>
                <option value='255'" . ($free_length == '255' ? ' selected' : '') . ">{$lang['upload_unlimited']}</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class='rowhead'>{$lang['upload_silver']}</td>
        <td>
            <select name='half_length' class='w-100'>
                <option value='0'>{$lang['upload_not_silver']}</option>
                <option value='42'" . ($half_length == '42' ? ' selected' : '') . ">{$lang['upload_silver_1_day']}</option>
                <option value='1'" . ($half_length == '1' ? ' selected' : '') . ">{$lang['upload_silver_1_week']}</option>
                <option value='2'" . ($half_length == '2' ? ' selected' : '') . ">{$lang['upload_silver_2_weeks']}</option>
                <option value='4'" . ($half_length == '4' ? ' selected' : '') . ">{$lang['upload_silver_4_weeks']}</option>
                <option value='8'" . ($half_length == '8' ? ' selected' : '') . ">{$lang['upload_silver_8_weeks']}</option>
                <option value='255'" . ($half_length == '255' ? ' selected' : '') . ">{$lang['upload_unlimited']}</option>
            </select>
        </td>
    </tr>";
}
require_once PARTIALS_DIR . 'genres.php';

if ($CURUSER['class'] >= UC_UPLOADER) {
    $HTMLOUT .= tr($lang['upload_vip'], "<div class='level-left'><input type='checkbox' name='vip' id='vip' value='1'" . ($vip == 1 ? ' checked' : '') . "><label for='vip' class='left5'>{$lang['upload_vip_msg']}</label></div>", 1);
}
$HTMLOUT .= "
        <tr>
            <td colspan='2'>
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' value='{$lang['upload_submit']}'>
                </div>
            </td>
        </tr>
        </table>
        </form>";

echo stdhead($lang['upload_stdhead'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
