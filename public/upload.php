<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Roles;
use Pu239\Session;

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
        get_file_name('dragndrop_js'),
        get_file_name('sceditor_js'),
    ],
];
$auth = $container->get(Auth::class);
if (!$auth->hasRole(Roles::UPLOADER) || $user['uploadpos'] != 1 || $user['status'] === 5) {
    stderr(_('Error'), _fe('You are not authorized to upload torrents.  (See {0}Uploading{1} in the FAQ.)', "<a href='{$site_config['paths']['baseurl']}/faq.php#up'>", '</a>'));
}
$cache = $container->get(Cache::class);
$upload_vars = $cache->get('user_upload_variables_' . $user['id']);
$poster = $youtube = $strip = $uplver = $allow_comments = $free_length = $half_length = $tags = $description = $body = '';
$HTMLOUT = $subs_list = $audios_list = $descr = $has_offers = $has_requests = $has_recipes = '';

if (!empty($upload_vars)) {
    $upload_vars = json_decode($upload_vars, true);
}
$vars = [
    'url',
    'isbn',
    'title',
    'poster',
    'youtube',
    'name',
    'tags',
    'description',
    'body',
    'type',
    'request',
    'offer',
    'recipe',
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
$fluent = $container->get(Database::class);
$res_cooker = $fluent->from('upcoming')
                     ->select(null)
                     ->select('id')
                     ->select('name')
                     ->where('torrentid = 0')
                     ->orderBy('name')
                     ->fetchAll();

if ($res_cooker) {
    $has_recipes = '
            <tr>
                <td>' . _('Recipes') . ":</span></td>
                <td>
                    <select name='recipe' class='w-100'>
                        <option value='0'>" . _('Select') . '</option>';
    foreach ($res_cooker as $arr_recipe) {
        $has_recipes .= "
                        <option value='{$arr_recipe['id']}' " . ($recipe == $arr_recipe['id'] ? 'selected' : '') . '>' . htmlsafechars($arr_recipe['name']) . '</option>';
    }
    $has_recipes .= '
                    </select>' . _('If you are completing a recipe from the Cooker, please select it here so interested members can be notified.') . '
                </td>
            </tr>';
}
$res_requests = $fluent->from('requests')
                       ->select(null)
                       ->select('id')
                       ->select('name')
                       ->where('filled_by_user_id = 0')
                       ->orderBy('name')
                       ->fetchAll();

if ($res_requests) {
    $has_requests = '
            <tr>
                <td>' . _('Requests') . ":</span></td>
                <td>
                    <select name='request' class='w-100'>
                        <option value='0'>" . _('Select') . '</option>';
    foreach ($res_requests as $arr_request) {
        $has_requests .= "
                        <option value='{$arr_request['id']}' " . ($request == $arr_request['id'] ? 'selected' : '') . '>' . htmlsafechars($arr_request['name']) . '</option>';
    }
    $has_requests .= '
                    </select>' . _('If you are uploading one of your offers, please select it here so interested members will be notified.') . '
                </td>
            </tr>';
}

$res_offers = $fluent->from('offers')
                     ->select(null)
                     ->select('id')
                     ->select('name')
                     ->where('userid = ?', $user['id'])
                     ->where('status = "approved"')
                     ->orderBy('name')
                     ->fetchAll();

if ($res_offers) {
    $has_offers = '
            <tr>
                <td>' . _('My Offers') . ":</td>
                <td>
                    <select name='offer' class='w-100'>
                        <option value='0'>" . _('Select') . '</option>';
    foreach ($res_offers as $arr_offer) {
        $has_offers .= "
                        <option value='{$arr_offer['id']}' " . ($offer == $arr_offer['id'] ? 'selected' : '') . '>' . htmlsafechars($arr_offer['name']) . '</option>';
    }
    $has_offers .= '
                    </select>' . _('If you are filling a request please select it here so interested members can be notified.') . ':
                </td>
            </tr>';
}
$session = $container->get(Session::class);
$usessl = $session->get('scheme') === 'https' || $site_config['site']['https_only'] === true ? 'announce_url_ssl' : 'announce_url_nonssl';
if ($site_config['tracker']['radiance']) {
    $announce_url = "{$site_config['tracker'][$usessl][0]}:{$site_config['tracker']['announce_port']}" . ($site_config['upload']['show_torrent_pass'] ? "/{$user['torrent_pass']}/announce" : '');
} else {
    $announce_url = "{$site_config['tracker'][$usessl][0]}/announce.php" . ($site_config['upload']['show_torrent_pass'] ? "?torrent_pass={$user['torrent_pass']}" : '');
}

$HTMLOUT .= "
    <form id='upload_form' name='upload_form' action='{$site_config['paths']['baseurl']}/takeupload.php' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='hidden' name='MAX_FILE_SIZE' value='{$site_config['site']['max_torrent_size']}'>
        <input type='hidden' id='csrf' name='csrf' data-ebooks=" . json_encode($site_config['categories']['ebook']) . ' data-movies=' . json_encode(array_merge($site_config['categories']['movie'], $site_config['categories']['tv'])) . ">
        <h1 class='has-text-centered'>" . _('Upload a Torrent') . "</h1>
        <div class='has-text-centered margin10'>" . _("The tracker's announce url is") . ":<br>
            <input type='text' class='has-text-centered w-100' readonly='readonly' value='{$announce_url}' id='announce_url' onClick=\"this.select();\">
        </div>
        <div class='banner_container has-text-centered w-100'></div>
        <table class='table table-bordered table-striped top20'>";

$HTMLOUT .= "
            <tr>
                <td class='rowhead'>" . _('Category') . '</td>
                <td>' . category_dropdown() . "</td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Torrent file') . "</td>
                <td>
                    <input type='file' name='file' id='torrent' onchange='getname()' class='inputfile'>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Torrent name') . "</td>
                <td><input type='text' id='name' name='name' maxlength='255' value='$name' class='w-100' required><br>(" . _('Taken from filename if not specified. <b>Please use descriptive names.</b>') . ")</td>
            </tr>
            <tr>
                <td class='rowhead'><a href='" . url_proxy('https://www.imdb.com') . "' target='_blank'>" . _('IMDb Url') . "</a></td>
                <td>
                    <input type='url' id='url' name='url' maxlength='80' class='w-100' value='{$url}'><br>
                    " . _('(Taken from Imdb - ') . _('Add the Imdb url to display Imdb data on details.)') . "
                    <div id='imdb_outer'></div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('ISBN') . "</td>
                <td>
                    <input type='text' id='isbn' name='isbn' minlength='10' maxlength='13' class='w-100' value='$isbn'><br>
                    " . _('(Used for eBooks, ISBN 13 or ISBN 10, no spaces or dashes. Either the ISBN or a clean title is required for lookup)') . "
                    <div id='isbn_outer'></div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Book Title') . "</td>
                <td>
                    <input type='text' id='title' name='title' class='w-100' value='$title'><br>
                    " . _('(Used for eBooks, either the ISBN or a clean title is required for lookup)') . "
                    <div id='title_outer'></div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Poster') . "</td>
                <td>
                    <input type='url' id='image_url' placeholder='External Image URL' class='w-100' onchange=\"return grab_url(event)\">
                    <input type='url' id='poster' maxlength='255' name='poster' class='w-100 is-hidden' value='$poster'>
                    <br>" . _('(Minimum Poster Width should be 400 px, larger sizes will be scaled.)') . "
                    <div class='poster_container has-text-centered'></div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'><b>" . _('Bitbucket') . "</b></td>
                <td class='has-text-centered'>
                    <div id='droppable' class='droppable bg-03'>
                        <span id='comment'>" . _('Drop images or click here to select images.') . "</span>
                        <div id='loader' class='is-hidden'>
                            <img src='{$site_config['paths']['images_baseurl']}forums/updating.svg' alt='Loading...'>
                        </div>
                    </div>
                    <div class='output-wrapper output'></div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'><a href='" . url_proxy('https://youtube.com') . "' target='_blank'>" . _('Youtube') . "</a></td>
                <td><input type='url' id='youtube' name='youtube' maxlength='45' class='w-100' value='$youtube'><br>(" . _("Direct link to youtube, will be shown on torrent's details page.<br>Link should look like <b>http://www.youtube.com/watch?v=camI8yuoy8U</b>") . ")</td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Tags') . "</td>
                <td><input type='text' name='tags' value='$tags' class='w-100'><br>(" . _('Multiple tags must be seperated by a comma like tag1,tag2') . ")</td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Small Description') . "</td>
                <td><input type='text' name='description' value='$description' class='w-100' maxlength='120'><br>(" . _('Small Description for the uploaded file. This description is shown on browse.php under the torrent name.') . ")</td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('NFO file') . "</td>
                <td><input type='file' id='nfo' name='nfo'><br>(" . _('<b>Optional.</b> Can only be viewed by power users.') . ')</td>
            </tr>
            <tr>
                <td>' . _('Strip ASCII') . "</td>
                <td>
                    <div class='level-left'>
                        <input type='checkbox' name='strip' id='strip' value='strip' " . ($strip === 'strip' ? 'checked' : '') . ">
                        <label for='strip' class='left5'>
                            <a href='https://en.wikipedia.org/wiki/ASCII_art' target='_blank'>" . _('What is this?') . "</a>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Description') . "</td>
                <td class='is-paddingless'>" . BBcode($body) . "
                    <div class='margin10'>(" . _('HTML is <b>not</b> allowed.') . ')</div>
                </td>
            </tr>';
$HTMLOUT .= $has_offers;
$HTMLOUT .= $has_requests;
$HTMLOUT .= $has_recipes;
$subs_list .= "
                <div id='subs' class='level-center'>";
$subs = $container->get('subtitles');
$s = [
    'name' => '',
    'pic' => '',
];
foreach ($subs as $s) {
    $subs_list .= "
                    <div class='w-15 margin10 tooltipper bordered level-center-center' title='" . htmlsafechars($s['name']) . "'>
                        <input name='subs[]' type='checkbox' value='{$s['name']}' " . (in_array($s['name'], $has_subs) ? 'checked' : '') . " class='margin20'>
                        <img class='sub_flag' src='{$site_config['paths']['images_baseurl']}/{$s['pic']}' alt='" . htmlsafechars($s['name']) . "'>
                        <span class='has-text-centered margin20'>" . format_comment($s['name']) . '</span>
                    </div>';
}
$subs_list .= '
                </div>';

$audio_list = str_replace([
    'subs[]',
    "id='subs'",
], [
    'audios[]',
    "id='audios'",
], $subs_list);
$HTMLOUT .= "
            <tr'>
                <td>" . _('Subtitles') . "</td>
                <td>{$subs_list}</td>
            </tr>
            <tr>
                <td>" . _('Audio Languages') . "</td>
                <td>{$audio_list}</td>
            </tr>";

$rg = "
            <select name='release_group' class='w-100'>
                <option value='none'>" . _('None') . "</option>
                <option value='p2p' " . ($release_group === 'p2p' ? 'selected' : '') . '>' . _('p2p') . "</option>
                <option value='scene' " . ($release_group === 'scene' ? 'selected' : '') . '>' . _('Scene') . '</option>
            </select>';
$HTMLOUT .= tr(_('Category'), $rg, 1);
$HTMLOUT .= tr(_('Anonymous Uploader'), "<div class='level-left'><input type='checkbox' name='uplver' id='uplver' value='1' " . ($uplver ? 'checked' : '') . "><label for='uplver' class='left5'>" . _("Don't show my username in 'Uploaded By' field in browse.") . '</label></div>', 1);
if ($user['class'] >= $site_config['allowed']['torrents_disable_comments']) {
    $HTMLOUT .= tr(_('Allow Comments'), "
    <select name='allow_comments' class='w-100'>
        <option value='yes' " . ($allow_comments === 'yes' ? 'selected' : '') . '>' . _('Yes') . "</option>
        <option value='no' " . ($allow_comments === 'no' ? 'selected' : '') . '>' . _('No') . '</option>
    </select>', 1);
}
if (has_access($user['class'], UC_MIN, 'uploader')) {
    $HTMLOUT .= "
    <tr>
        <td class='rowhead'>" . _('Free Leech') . "</td>
        <td>
            <select name='free_length' class='w-100'>
                <option value='0'>" . _('Not Free') . "</option>
                <option value='42' " . ($free_length == '42' ? 'selected' : '') . '>' . _fe('Free for {0} day', 1) . "</option>
                <option value='1' " . ($free_length == '1' ? 'selected' : '') . '>' . _fe('Free for {0} week', 1) . "</option>
                <option value='2' " . ($free_length == '2' ? 'selected' : '') . '>' . _fe('Free for {0} weeks', 2) . "</option>
                <option value='4' " . ($free_length == '4' ? 'selected' : '') . '>' . _fe('Free for {0} weeks', 4) . "</option>
                <option value='8' " . ($free_length == '8' ? 'selected' : '') . '>' . _fe('Free for {0} weeks', 8) . "</option>
                <option value='255' " . ($free_length == '255' ? 'selected' : '') . '>' . _('Unlimited') . "</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class='rowhead'>" . _('Silver Torrent') . "</td>
        <td>
            <select name='half_length' class='w-100'>
                <option value='0'>" . _('Not Silver') . "</option>
                <option value='42' " . ($half_length == '42' ? 'selected' : '') . '>' . _fe('Silver for {0} day', 1) . "</option>
                <option value='1' " . ($half_length == '1' ? 'selected' : '') . '>' . _fe('Silver for {0} week', 1) . "</option>
                <option value='2' " . ($half_length == '2' ? 'selected' : '') . '>' . _fe('Silver for {0} weeks', 2) . "</option>
                <option value='4' " . ($half_length == '4' ? 'selected' : '') . '>' . _fe('Silver for {0} weeks', 4) . "</option>
                <option value='8' " . ($half_length == '8' ? 'selected' : '') . '>' . _fe('Silver for {0} weeks', 8) . "</option>
                <option value='255' " . ($half_length == '255' ? 'selected' : '') . '>' . _('Unlimited') . '</option>
            </select>
        </td>
    </tr>';
}
require_once PARTIALS_DIR . 'genres.php';

if (has_access($user['class'], UC_USER, 'uploader')) {
    $HTMLOUT .= tr(_('VIP Torrent'), "<div class='level-left'><input type='checkbox' name='vip' id='vip' value='1' " . ($vip == 1 ? 'checked' : '') . "><label for='vip' class='left5'>" . _("If this one is checked, only VIP's can download this torrent") . '</label></div>', 1);
}
$HTMLOUT .= "
        <tr>
            <td colspan='2'>
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' value='" . _('Upload Torrent') . "'>
                </div>
            </td>
        </tr>
        </table>
        </form>";

$title = _('Upload Torrent');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
