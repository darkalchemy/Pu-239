<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class.bencdec.php';
require_once INCL_DIR . 'function_memcache.php';
global $site_config, $fluent, $session, $user_stuffs, $cache;

$torrent_pass = $auth = $bot = $owner_id = '';
extract($_GET);
unset($_GET);
extract($_POST);
unset($_POST);
if (!empty($bot) && !empty($auth)) {
    $owner_id = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('class > ? AND username = ? AND auth = ? AND torrent_pass = ? AND uploadpos = 1 AND suspended = "no"', UC_UPLOADER, $bot, $auth, $torrent_pass)
        ->fetch('id');
} else {
    check_user_status();
    global $CURUSER;
    $owner_id = $CURUSER['id'];
}

$user_data = $user_stuffs->getUserFromId($owner_id);

ini_set('upload_max_filesize', $site_config['max_torrent_size']);
ini_set('memory_limit', '64M');
$lang = array_merge(load_language('global'), load_language('takeupload'));

if ($user_data['class'] < UC_UPLOADER || $user_data['uploadpos'] == 0 || $user_data['uploadpos'] > 1 || $user_data['suspended'] === 'yes') {
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if (empty($body) || empty($type) || empty($name)) {
    $session->set('is-warning', $lang['takeupload_no_formdata']);
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if (!isset($_FILES['file'])) {
    $session->set('is-warning', $lang['takeupload_no_formdata']);
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}

$url = strip_tags(isset($url) ? trim($url) : '');
$poster = strip_tags(isset($poster) ? trim($poster) : '');
$f = $_FILES['file'];
$fname = unesc($f['name']);
if (empty($fname)) {
    $session->set('is-warning', $lang['takeupload_no_filename']);
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}

if (isset($uplver) && $uplver === 'yes') {
    $anonymous = 'yes';
    $anon = get_anonymous_name();
} else {
    $anonymous = 'no';
    $anon = $user_data['username'];
}
if (isset($allow_comments) && $allow_comments === 'yes') {
    $allow_comments = 'no';
    $disallow = 'Yes';
} else {
    $allow_comments = 'yes';
    $disallow = 'No';
}
if (isset($music)) {
    $genre = implode(',', $music);
} elseif (isset($movie)) {
    $genre = implode(',', $movie);
} elseif (isset($game)) {
    $genre = implode(',', $game);
} elseif (isset($apps)) {
    $genre = implode(',', $apps);
} else {
    $genre = '';
}

$nfo = sqlesc('');

if (isset($_FILES['nfo']) && !empty($_FILES['nfo']['name'])) {
    $nfofile = $_FILES['nfo'];
    if ($nfofile['name'] == '') {
        $session->set('is-warning', $lang['takeupload_no_nfo']);
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    if ($nfofile['size'] == 0) {
        $session->set('is-warning', $lang['takeupload_0_byte']);
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    if ($nfofile['size'] > NFO_SIZE) {
        $session->set('is-warning', $lang['takeupload_nfo_big']);
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    $nfofilename = $nfofile['tmp_name'];
    if (@!is_uploaded_file($nfofilename)) {
        $session->set('is-warning', $lang['takeupload_nfo_failed']);
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    $nfo_content = str_ireplace([
                                    "\x0d\x0d\x0a",
                                    "\xb0",
                                ], [
                                    "\x0d\x0a",
                                    '',
                                ], file_get_contents($nfofilename));
    $nfo = sqlesc($nfo_content);
}

$free2 = 0;
if (isset($free_length) && ($free_length = (int) $free_length)) {
    if ($free_length == 255) {
        $free2 = 1;
    } elseif ($free_length == 42) {
        $free2 = (86400 + TIME_NOW);
    } else {
        $free2 = (TIME_NOW + $free_length * 604800);
    }
}

$silver = 0;
if (isset($half_length) && ($half_length = (int) $half_length)) {
    if ($half_length == 255) {
        $silver = 1;
    } elseif ($half_length == 42) {
        $silver = (86400 + TIME_NOW);
    } else {
        $silver = (TIME_NOW + $half_length * 604800);
    }
}

$freetorrent = (((isset($freetorrent) && is_valid_id($freetorrent)) ? intval($freetorrent) : 0));
$descr = strip_tags(isset($body) ? trim($body) : '');

if (!$descr) {
    if (isset($_FILES['nfo']) && !empty($_FILES['nfo']['name'])) {
        $descr = preg_replace('/[^\\x20-\\x7e\\x0a\\x0d]/', ' ', $nfo);
    } else {
        $session->set('is-warning', $lang['takeupload_no_descr']);
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
}
$description = strip_tags(isset($description) ? trim($description) : '');
if (isset($strip) && $strip) {
    require_once INCL_DIR . 'strip.php';
    $descr = preg_replace('/[^\\x20-\\x7e\\x0a\\x0d]/', ' ', $descr);
    strip($descr);
}
$catid = (int) $type;
if (!is_valid_id($catid)) {
    $session->set('is-warning', $lang['takeupload_no_cat']);
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
$request = (((isset($request) && is_valid_id($request)) ? intval($request) : 0));
$offer = (((isset($offer) && is_valid_id($offer)) ? intval($offer) : 0));
$subs = isset($subs) ? implode(',', $subs) : '';
$release_group_array = [
    'scene' => 1,
    'p2p' => 1,
    'none' => 1,
];
$release_group = isset($release_group, $release_group_array[$release_group]) ? $release_group : 'none';
$youtube = '';
if (isset($youtube) && preg_match($youtube_pattern, $youtube, $temp_youtube)) {
    $youtube = $temp_youtube[0];
}
$tags = strip_tags(isset($tags) ? trim($tags) : '');

if (!validfilename($fname)) {
    $session->set('is-warning', $lang['takeupload_invalid']);
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}

if (empty($isbn)) {
    $isbn = '';
} else {
    $isbn = str_replace([
                            '-',
                            ' ',
                        ], '', $isbn);
}

if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches)) {
    $session->set('is-warning', $lang['takeupload_not_torrent']);
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
$shortfname = $torrent = $matches[1];
if (!empty($name)) {
    $torrent = unesc($name);
}
$tmpname = $f['tmp_name'];
if (!is_uploaded_file($tmpname)) {
    $session->set('is-warning', $lang['takeupload_eek']);
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if (!filesize($tmpname)) {
    $session->set('is-warning', $lang['takeupload_no_file']);
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
$dict = bencdec::decode_file($tmpname, $site_config['max_torrent_size'], bencdec::OPTION_EXTENDED_VALIDATION);
if ($dict === false) {
    $session->set('is-warning', 'What did you upload? This is not a bencoded file!');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if (isset($dict['announce-list'])) {
    unset($dict['announce-list']);
}
$dict['info']['private'] = 1;
if (!isset($dict['info'])) {
    $session->set('is-warning', 'invalid torrent, info dictionary does not exist');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
$info = &$dict['info'];
$infohash = pack('H*', sha1(bencdec::encode($info)));

if (get_row_count('torrents', 'WHERE info_hash = ' . sqlesc($infohash)) > 0) {
    $session->set('is-warning', 'This torrent has already been uploaded! Please use the search function before uploading.');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if (bencdec::get_type($info) != 'dictionary') {
    $session->set('is-warning', 'invalid torrent, info is not a dictionary');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if (!isset($info['name']) || !isset($info['piece length']) || !isset($info['pieces'])) {
    $session->set('is-warning', 'invalid torrent, missing parts of the info dictionary');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if (bencdec::get_type($info['name']) != 'string' || bencdec::get_type($info['piece length']) != 'integer' || bencdec::get_type($info['pieces']) != 'string') {
    $session->set('is-warning', 'invalid torrent, invalid types in info dictionary');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
$dname = $info['name'];
$plen = $info['piece length'];
$pieces_len = strlen($info['pieces']);
if ($pieces_len % 20 != 0) {
    $session->set('is-warning', 'invalid pieces');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if ($plen % 4096) {
    $session->set('is-warning', 'piece size is not mod(4096), invalid torrent.');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
$filelist = [];
if (isset($info['length'])) {
    if (bencdec::get_type($info['length']) != 'integer') {
        $session->set('is-warning', 'length must be an integer');
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    $totallen = $info['length'];
    $filelist[] = [
        $dname,
        $totallen,
    ];
} else {
    if (!isset($info['files'])) {
        $session->set('is-warning', 'missing both length and files');
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    if (bencdec::get_type($info['files']) != 'list') {
        $session->set('is-warning', 'invalid files, not a list');
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    $flist = &$info['files'];
    if (!count($flist)) {
        $session->set('is-warning', 'no files');
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    $totallen = 0;
    foreach ($flist as $fn) {
        if (!isset($fn['length']) || !isset($fn['path'])) {
            $session->set('is-warning', 'file info not found');
            header("Location: {$site_config['baseurl']}/upload.php");
            die();
        }
        if (bencdec::get_type($fn['length']) != 'integer' || bencdec::get_type($fn['path']) != 'list') {
            $session->set('is-warning', 'invalid file info');
            header("Location: {$site_config['baseurl']}/upload.php");
            die();
        }
        $ll = $fn['length'];
        $ff = $fn['path'];
        $totallen += $ll;
        $ffa = [];
        foreach ($ff as $ffe) {
            if (bencdec::get_type($ffe) != 'string') {
                $session->set('is-warning', 'filename type error');
                header("Location: {$site_config['baseurl']}/upload.php");
                die();
            }
            $ffa[] = $ffe;
        }
        if (!count($ffa)) {
            $session->set('is-warning', 'filename error');
            header("Location: {$site_config['baseurl']}/upload.php");
            die();
        }
        $ffe = implode('/', $ffa);
        $filelist[] = [
            $ffe,
            $ll,
        ];
    }
}

$num_pieces = $pieces_len / 20;
$expected_pieces = (int) ceil($totallen / $plen);
if ($num_pieces != $expected_pieces) {
    $session->set('is-warning', 'total file size and number of pieces do not match');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}

$tmaker = (isset($dict['created by']) && !empty($dict['created by'])) ? sqlesc($dict['created by']) : sqlesc($lang['takeupload_unkown']);
$dict['comment'] = ("In using this torrent you are bound by the {$site_config['site_name']} Confidentiality Agreement By Law"); // change torrent comment

$visible = (XBT_TRACKER ? 'yes' : 'no');
$torrent = str_replace('_', ' ', $torrent);
$vip = (isset($vip) ? '1' : '0');

$sql = 'INSERT INTO torrents (isbn, search_text, filename, owner, visible, vip, release_group, newgenre, poster, anonymous, allow_comments, info_hash, name, size, numfiles, offer, request, url, subs, descr, ori_descr, description, category, free, silver, save_as, youtube, tags, added, last_action, mtime, ctime, freetorrent, nfo, client_created_by) VALUES (' . implode(',', array_map('sqlesc', [
        $isbn,
        searchfield("$shortfname $dname $torrent"),
        $fname,
        $owner_id,
        $visible,
        $vip,
        $release_group,
        $genre,
        $poster,
        $anonymous,
        $allow_comments,
        $infohash,
        $torrent,
        $totallen,
        count($filelist),
        $offer,
        $request,
        $url,
        $subs,
        $descr,
        $descr,
        $description,
        (int) $type,
        $free2,
        $silver,
        $dname,
        $youtube,
        $tags,
    ])) . ', ' . TIME_NOW . ', ' . TIME_NOW . ', ' . TIME_NOW . ', ' . TIME_NOW . ", $freetorrent, $nfo, $tmaker)";

$ret = sql_query($sql) or sqlerr(__FILE__, __LINE__);

if (!$ret) {
    if (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
        $session->set('is-warning', $lang['takeupload_already']);
        header("Location: {$site_config['baseurl']}/upload.php");
        die();
    }
    $session->set('is-warning', 'mysql puked: ' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
if (!XBT_TRACKER) {
    remove_torrent($infohash);
}
$id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
$cache->delete('MyPeers_' . $owner_id);
$cache->delete('lastest_tor_');
$cache->delete('last5_tor_');
$cache->delete('top5_tor_');
$cache->delete('scroll_tor_');
$cache->delete('torrent_poster_count_');
$cache->delete('backgrounds_');
$cache->delete('posters_');
$hashes = $cache->get('hashes_');
if (!empty($hashes)) {
    foreach ($hashes as $hash) {
        $cache->delete('suggest_torrents_' . $hash);
    }
    $cache->delete('hashes_');
}

if (isset($uplver) && $uplver === 'yes') {
    $message = "New Torrent : [url={$site_config['baseurl']}/details.php?id=$id] [b]" . htmlsafechars($torrent) . '[/b][/url] Uploaded by ' . get_anonymous_name();
} else {
    $message = "New Torrent : [url={$site_config['baseurl']}/details.php?id=$id] [b]" . htmlsafechars($torrent) . '[/b][/url] Uploaded by ' . htmlsafechars($user_data['username']);
}
$messages = "{$site_config['site_name']} New Torrent: $torrent Uploaded By: $anon " . mksize($totallen) . " {$site_config['baseurl']}/details.php?id=$id";
sql_query('DELETE FROM files WHERE torrent = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

/**
 * @param $arr
 * @param $id
 *
 * @return string
 */
function file_list($arr, $id)
{
    $new = [];
    foreach ($arr as $v) {
        $new[] = "($id," . sqlesc($v[0]) . ',' . $v[1] . ')';
    }

    return join(',', $new);
}

sql_query('INSERT INTO files (torrent, filename, size) VALUES ' . file_list($filelist, $id)) or sqlerr(__FILE__, __LINE__);

$dir = $site_config['torrent_dir'] . '/' . $id . '.torrent';
if (!bencdec::encode_file($dir, $dict)) {
    $session->set('is-warning', 'Could not properly encode file');
    header("Location: {$site_config['baseurl']}/upload.php");
    die();
}
@unlink($tmpname);
chmod($dir, 0664);

if ($site_config['seedbonus_on'] == 1) {
    $seedbonus = $user_data['seedbonus'];
    sql_query('UPDATE users SET seedbonus = seedbonus + ' . sqlesc($site_config['bonus_per_upload']) . ', numuploads = numuploads+ 1  WHERE id = ' . sqlesc($owner_id)) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($seedbonus + $site_config['bonus_per_upload']);
    $cache->update_row('user' . $owner_id, [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_cache']);
}
if ($site_config['autoshout_on'] == 1) {
    autoshout($message);
    autoshout($message, 2, 0);
}
if ($offer > 0) {
    $res_offer = sql_query("SELECT user_id FROM offer_votes WHERE vote = 'yes' AND user_id != " . sqlesc($owner_id) . ' AND offer_id = ' . sqlesc($offer)) or sqlerr(__FILE__, __LINE__);
    $subject = sqlesc('An offer you voted for has been uploaded!');
    $message = sqlesc("Hi, \n An offer you were interested in has been uploaded!!! \n\n Click  [url=" . $site_config['baseurl'] . '/details.php?id=' . $id . ']' . htmlsafechars($torrent, ENT_QUOTES) . '[/url] to see the torrent page!');
    while ($arr_offer = mysqli_fetch_assoc($res_offer)) {
        sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
    VALUES(0, ' . sqlesc($arr_offer['user_id']) . ', ' . TIME_NOW . ', ' . $message . ', ' . $subject . ', "yes", 1)') or sqlerr(__FILE__, __LINE__);
        $cache->increment('inbox_' . $arr_offer['user_id']);
    }
    write_log('Offered torrent ' . $id . ' (' . htmlsafechars($torrent) . ') was uploaded by ' . $user_data['username']);
    $filled = 1;
}
$filled = 0;
if ($request > 0) {
    $res_req = sql_query('SELECT user_id FROM request_votes WHERE vote = "yes" AND request_id = ' . sqlesc($request)) or sqlerr(__FILE__, __LINE__);
    $subject = sqlesc('A  request you were interested in has been uploaded!');
    $message = sqlesc("Hi :D \n A request you were interested in has been uploaded!!! \n\n Click  [url=" . $site_config['baseurl'] . '/details.php?id=' . $id . ']' . htmlsafechars($torrent, ENT_QUOTES) . '[/url] to see the torrent page!');
    while ($arr_req = mysqli_fetch_assoc($res_req)) {
        sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
    VALUES(0, ' . sqlesc($arr_req['user_id']) . ', ' . TIME_NOW . ', ' . $message . ', ' . $subject . ', "yes", 1)') or sqlerr(__FILE__, __LINE__);
        $cache->increment('inbox_' . $arr_req['user_id']);
    }
    sql_query('UPDATE requests SET filled_by_user_id = ' . sqlesc($owner_id) . ', filled_torrent_id = ' . sqlesc($id) . ' WHERE id = ' . sqlesc($request)) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE usersachiev SET reqfilled = reqfilled + 1 WHERE userid = ' . sqlesc($owner_id)) or sqlerr(__FILE__, __LINE__);
    write_log('Request for torrent ' . $id . ' (' . htmlsafechars($torrent) . ') was filled by ' . $user_data['username']);
    $filled = 1;
}
if ($filled == 0) {
    write_log(sprintf($lang['takeupload_log'], $id, $torrent, $user_data['username']));
}

$session->set('is-success', $lang['takeupload_success']);
header("Location: {$site_config['baseurl']}/details.php?id=$id");
