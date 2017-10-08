<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class.bencdec.php';
//require_once INCL_DIR . 'function_ircbot.php';
require_once INCL_DIR . 'function_memcache.php';
check_user_status();
ini_set('upload_max_filesize', $site_config['max_torrent_size']);
ini_set('memory_limit', '64M');
$lang = array_merge(load_language('global'), load_language('takeupload'));
global $site_config;

if ($CURUSER['class'] < UC_UPLOADER or $CURUSER['uploadpos'] == 0 || $CURUSER['uploadpos'] > 1 || $CURUSER['suspended'] == 'yes') {
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
foreach (explode(':', 'body:type:name') as $v) {
    if (!isset($_POST[$v])) {
        setSessionVar('error', $lang['takeupload_no_formdata']);
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
}
if (!isset($_FILES['file'])) {
    setSessionVar('error', $lang['takeupload_no_formdata']);
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
$url = strip_tags(isset($_POST['url']) ? trim($_POST['url']) : '');
$poster = strip_tags(isset($_POST['poster']) ? trim($_POST['poster']) : '');
$f = $_FILES['file'];
$fname = unesc($f['name']);
if (empty($fname)) {
    setSessionVar('error', $lang['takeupload_no_filename']);
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if (isset($_POST['uplver']) && $_POST['uplver'] == 'yes') {
    $anonymous = 'yes';
    $anon = 'Anonymous';
} else {
    $anonymous = 'no';
    $anon = $CURUSER['username'];
}
if (isset($_POST['allow_comments']) && $_POST['allow_comments'] == 'yes') {
    $allow_comments = 'no';
    $disallow = 'Yes';
} else {
    $allow_comments = 'yes';
    $disallow = 'No';
}
if (isset($_POST['music'])) {
    $genre = implode(',', $_POST['music']);
} elseif (isset($_POST['movie'])) {
    $genre = implode(',', $_POST['movie']);
} elseif (isset($_POST['game'])) {
    $genre = implode(',', $_POST['game']);
} elseif (isset($_POST['apps'])) {
    $genre = implode(',', $_POST['apps']);
} else {
    $genre = '';
}
$nfo = sqlesc('');
/////////////////////// NFO FILE ////////////////////////
if (isset($_FILES['nfo']) && !empty($_FILES['nfo']['name'])) {
    $nfofile = $_FILES['nfo'];
    if ($nfofile['name'] == '') {
        setSessionVar('error', $lang['takeupload_no_nfo']);
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    if ($nfofile['size'] == 0) {
        setSessionVar('error', $lang['takeupload_0_byte']);
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    if ($nfofile['size'] > 65535) {
        setSessionVar('error', $lang['takeupload_nfo_big']);
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    $nfofilename = $nfofile['tmp_name'];
    if (@!is_uploaded_file($nfofilename)) {
        setSessionVar('error', $lang['takeupload_nfo_failed']);
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    $nfo = sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename)));
}
/////////////////////// NFO FILE END /////////////////////
/// Set Freeleech on Torrent Time Based
$free2 = 0;
if (isset($_POST['free_length']) && ($free_length = (int)$_POST['free_length'])) {
    if ($free_length == 255) {
        $free2 = 1;
    } elseif ($free_length == 42) {
        $free2 = (86400 + TIME_NOW);
    } else {
        $free2 = (TIME_NOW + $free_length * 604800);
    }
}
/// end
/// Set Silver Torrent Time Based
$silver = 0;
if (isset($_POST['half_length']) && ($half_length = (int)$_POST['half_length'])) {
    if ($half_length == 255) {
        $silver = 1;
    } elseif ($half_length == 42) {
        $silver = (86400 + TIME_NOW);
    } else {
        $silver = (TIME_NOW + $half_length * 604800);
    }
}
/// end
//==Xbt freetorrent
$freetorrent = (((isset($_POST['freetorrent']) && is_valid_id($_POST['freetorrent'])) ? intval($_POST['freetorrent']) : 0));
$descr = strip_tags(isset($_POST['body']) ? trim($_POST['body']) : '');
if (!$descr) {
    if (isset($_FILES['nfo']) && !empty($_FILES['nfo']['name'])) {
        $descr = preg_replace('/[^\\x20-\\x7e\\x0a\\x0d]/', ' ', $nfo);
    } else {
        setSessionVar('error', $lang['takeupload_no_descr']);
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
}
$description = strip_tags(isset($_POST['description']) ? trim($_POST['description']) : '');
if (isset($_POST['strip']) && $_POST['strip']) {
    require_once INCL_DIR . 'strip.php';
    $descr = preg_replace('/[^\\x20-\\x7e\\x0a\\x0d]/', ' ', $descr);
    strip($descr);
    //$descr = preg_replace("/\n+/","\n",$descr);
}
$catid = ((int)$_POST['type']);
if (!is_valid_id($catid)) {
    setSessionVar('error', $lang['takeupload_no_cat']);
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
$request = (((isset($_POST['request']) && is_valid_id($_POST['request'])) ? intval($_POST['request']) : 0));
$offer = (((isset($_POST['offer']) && is_valid_id($_POST['offer'])) ? intval($_POST['offer']) : 0));
$subs = isset($_POST['subs']) ? implode(',', $_POST['subs']) : '';
$release_group_array = [
    'scene' => 1,
    'p2p'   => 1,
    'none'  => 1,
];
$release_group = isset($_POST['release_group']) && isset($release_group_array[$_POST['release_group']]) ? $_POST['release_group'] : 'none';
$youtube = '';
if (isset($_POST['youtube']) && preg_match($youtube_pattern, $_POST['youtube'], $temp_youtube)) {
    $youtube = $temp_youtube[0];
}
$tags = strip_tags(isset($_POST['tags']) ? trim($_POST['tags']) : '');
if (!validfilename($fname)) {
    setSessionVar('error', $lang['takeupload_invalid']);
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches)) {
    setSessionVar('error', $lang['takeupload_not_torrent']);
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
$shortfname = $torrent = $matches[1];
if (!empty($_POST['name'])) {
    $torrent = unesc($_POST['name']);
}
$tmpname = $f['tmp_name'];
if (!is_uploaded_file($tmpname)) {
    setSessionVar('error', $lang['takeupload_eek']);
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if (!filesize($tmpname)) {
    setSessionVar('error', $lang['takeupload_no_file']);
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
// bencdec by djGrrr <3
$dict = bencdec::decode_file($tmpname, $site_config['max_torrent_size'], bencdec::OPTION_EXTENDED_VALIDATION);
if ($dict === false) {
    setSessionVar('error', 'What did you upload? This is not a bencoded file!');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if (isset($dict['announce-list'])) {
    unset($dict['announce-list']);
}
$dict['info']['private'] = 1;
if (!isset($dict['info'])) {
    setSessionVar('error', 'invalid torrent, info dictionary does not exist');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
$info = &$dict['info'];
$infohash = pack('H*', sha1(bencdec::encode($info)));
if (get_row_count('torrents', "WHERE info_hash = " . sqlesc($infohash)) > 0) {
    setSessionVar('error', 'This torrent has already been uploaded! Please use the search function before uploading.');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if (bencdec::get_type($info) != 'dictionary') {
    setSessionVar('error', 'invalid torrent, info is not a dictionary');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if (!isset($info['name']) || !isset($info['piece length']) || !isset($info['pieces'])) {
    setSessionVar('error', 'invalid torrent, missing parts of the info dictionary');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if (bencdec::get_type($info['name']) != 'string' || bencdec::get_type($info['piece length']) != 'integer' || bencdec::get_type($info['pieces']) != 'string') {
    setSessionVar('error', 'invalid torrent, invalid types in info dictionary');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
$dname = $info['name'];
$plen = $info['piece length'];
$pieces_len = strlen($info['pieces']);
if ($pieces_len % 20 != 0) {
    setSessionVar('error', 'invalid pieces');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if ($plen % 4096) {
    setSessionVar('error', 'piece size is not mod(4096), invalid torrent.');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
$filelist = [];
if (isset($info['length'])) {
    if (bencdec::get_type($info['length']) != 'integer') {
        setSessionVar('error', 'length must be an integer');
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    $totallen = $info['length'];
    $filelist[] = [
        $dname,
        $totallen,
    ];
} else {
    if (!isset($info['files'])) {
        setSessionVar('error', 'missing both length and files');
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    if (bencdec::get_type($info['files']) != 'list') {
        setSessionVar('error', 'invalid files, not a list');
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    $flist = &$info['files'];
    if (!count($flist)) {
        setSessionVar('error', 'no files');
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    $totallen = 0;
    foreach ($flist as $fn) {
        if (!isset($fn['length']) || !isset($fn['path'])) {
            setSessionVar('error', 'file info not found');
            header("Location: {$site_config['baseurl']}/upload.php");
            exit();
        }
        if (bencdec::get_type($fn['length']) != 'integer' || bencdec::get_type($fn['path']) != 'list') {
            setSessionVar('error', 'invalid file info');
            header("Location: {$site_config['baseurl']}/upload.php");
            exit();
        }
        $ll = $fn['length'];
        $ff = $fn['path'];
        $totallen += $ll;
        $ffa = [];
        foreach ($ff as $ffe) {
            if (bencdec::get_type($ffe) != 'string') {
                setSessionVar('error', 'filename type error');
                header("Location: {$site_config['baseurl']}/upload.php");
                exit();
            }
            $ffa[] = $ffe;
        }
        if (!count($ffa)) {
            setSessionVar('error', 'filename error');
            header("Location: {$site_config['baseurl']}/upload.php");
            exit();
        }
        $ffe = implode('/', $ffa);
        $filelist[] = [
            $ffe,
            $ll,
        ];
    }
}
$num_pieces = $pieces_len / 20;
$expected_pieces = (int)ceil($totallen / $plen);
if ($num_pieces != $expected_pieces) {
    setSessionVar('error', 'total file size and number of pieces do not match');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
//==
$tmaker = (isset($dict['created by']) && !empty($dict['created by'])) ? sqlesc($dict['created by']) : sqlesc($lang['takeupload_unkown']);
$dict['comment'] = ("In using this torrent you are bound by the {$site_config['site_name']} Confidentiality Agreement By Law"); // change torrent comment
// Replace punctuation characters with spaces
$visible = (XBT_TRACKER == true ? 'yes' : 'no');
$torrent = str_replace('_', ' ', $torrent);
$vip = (isset($_POST['vip']) ? '1' : '0');
$sql = 'INSERT INTO torrents (search_text, filename, owner, visible, vip, release_group, newgenre, poster, anonymous, allow_comments, info_hash, name, size, numfiles, offer, request, url, subs, descr, ori_descr, description, category, free, silver, save_as, youtube, tags, added, last_action, mtime, ctime, freetorrent, nfo, client_created_by) VALUES (' . implode(',', array_map('sqlesc', [
        searchfield("$shortfname $dname $torrent"),
        $fname,
        $CURUSER['id'],
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
        (int)$_POST['type'],
        $free2,
        $silver,
        $dname,
        $youtube,
        $tags,
    ])) . ', ' . TIME_NOW . ', ' . TIME_NOW . ', ' . TIME_NOW . ', ' . TIME_NOW . ", $freetorrent, $nfo, $tmaker)";
$ret = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (!$ret) {
    if (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
        setSessionVar('error', $lang['takeupload_already']);
        header("Location: {$site_config['baseurl']}/upload.php");
        exit();
    }
    setSessionVar('error', 'mysql puked: ' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
if (XBT_TRACKER == false) {
    remove_torrent($infohash);
}
$id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
$mc1->delete_value('MyPeers_' . $CURUSER['id']);
$mc1->delete_value('lastest_tor_');
$mc1->delete_value('last5_tor_');
$mc1->delete_value('scroll_tor_');
if (isset($_POST['uplver']) && $_POST['uplver'] == 'yes') {
    $message = "New Torrent : [url={$site_config['baseurl']}/details.php?id=$id] " . htmlsafechars($torrent) . '[/url] Uploaded - Anonymous User';
} else {
    $message = "New Torrent : [url={$site_config['baseurl']}/details.php?id=$id] " . htmlsafechars($torrent) . '[/url] Uploaded by ' . htmlsafechars($CURUSER['username']) . '';
}
$messages = "{$site_config['site_name']} New Torrent: $torrent Uploaded By: $anon " . mksize($totallen) . " {$site_config['baseurl']}/details.php?id=$id";
sql_query('DELETE FROM files WHERE torrent = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
function file_list($arr, $id)
{
    foreach ($arr as $v) {
        $new[] = "($id," . sqlesc($v[0]) . ',' . $v[1] . ')';
    }

    return join(',', $new);
}

sql_query('INSERT INTO files (torrent, filename, size) VALUES ' . file_list($filelist, $id)) or sqlerr(__FILE__, __LINE__);
//==
$dir = $site_config['torrent_dir'] . '/' . $id . '.torrent';
if (!bencdec::encode_file($dir, $dict)) {
    setSessionVar('error', 'Could not properly encode file');
    header("Location: {$site_config['baseurl']}/upload.php");
    exit();
}
@unlink($tmpname);
chmod($dir, 0664);
//==
if ($site_config['seedbonus_on'] == 1) {
    //===add karma
    sql_query('UPDATE users SET seedbonus = seedbonus + ' . sqlesc($site_config['bonus_per_upload']) . ', numuploads = numuploads+ 1  WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    //===end
    $update['seedbonus'] = ($CURUSER['seedbonus'] + $site_config['bonus_per_upload']);
    $mc1->begin_transaction('userstats_' . $CURUSER['id']);
    $mc1->update_row(false, [
        'seedbonus' => $update['seedbonus'],
    ]);
    $mc1->commit_transaction($site_config['expires']['u_stats']);
    $mc1->begin_transaction('user_stats_' . $CURUSER['id']);
    $mc1->update_row(false, [
        'seedbonus' => $update['seedbonus'],
    ]);
    $mc1->commit_transaction($site_config['expires']['user_stats']);
}
if ($site_config['autoshout_on'] == 1) {
    autoshout($message);
    //ircbot($messages);
}
//=== if it was an offer notify the folks who liked it :D
if ($offer > 0) {
    $res_offer = sql_query("SELECT user_id FROM offer_votes WHERE vote = 'yes' AND user_id != " . sqlesc($CURUSER['id']) . " AND offer_id = " . sqlesc($offer)) or sqlerr(__FILE__, __LINE__);
    $subject = sqlesc('An offer you voted for has been uploaded!');
    $message = sqlesc("Hi, \n An offer you were interested in has been uploaded!!! \n\n Click  [url=" . $site_config['baseurl'] . '/details.php?id=' . $id . ']' . htmlsafechars($torrent, ENT_QUOTES) . '[/url] to see the torrent page!');
    while ($arr_offer = mysqli_fetch_assoc($res_offer)) {
        sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
    VALUES(0, ' . sqlesc($arr_offer['user_id']) . ', ' . TIME_NOW . ', ' . $message . ', ' . $subject . ', "yes", 1)') or sqlerr(__FILE__, __LINE__);
        $mc1->delete_value('inbox_new_' . $arr_offer['user_id']);
        $mc1->delete_value('inbox_new_sb_' . $arr_offer['user_id']);
    }
    write_log('Offered torrent ' . $id . ' (' . htmlsafechars($torrent) . ') was uploaded by ' . $CURUSER['username']);
    $filled = 1;
}
$filled = 0;
//=== if it was a request notify the folks who voted :D
if ($request > 0) {
    $res_req = sql_query('SELECT user_id FROM request_votes WHERE vote = "yes" AND request_id = ' . sqlesc($request)) or sqlerr(__FILE__, __LINE__);
    $subject = sqlesc('A  request you were interested in has been uploaded!');
    $message = sqlesc("Hi :D \n A request you were interested in has been uploaded!!! \n\n Click  [url=" . $site_config['baseurl'] . '/details.php?id=' . $id . ']' . htmlsafechars($torrent, ENT_QUOTES) . '[/url] to see the torrent page!');
    while ($arr_req = mysqli_fetch_assoc($res_req)) {
        sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
    VALUES(0, ' . sqlesc($arr_req['user_id']) . ', ' . TIME_NOW . ', ' . $message . ', ' . $subject . ', "yes", 1)') or sqlerr(__FILE__, __LINE__);
        $mc1->delete_value('inbox_new_' . $arr_req['user_id']);
        $mc1->delete_value('inbox_new_sb_' . $arr_req['user_id']);
    }
    sql_query('UPDATE requests SET filled_by_user_id = ' . sqlesc($CURUSER['id']) . ', filled_torrent_id = ' . sqlesc($id) . ' WHERE id = ' . sqlesc($request)) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE usersachiev SET reqfilled = reqfilled + 1 WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    write_log('Request for torrent ' . $id . ' (' . htmlsafechars($torrent) . ') was filled by ' . $CURUSER['username']);
    $filled = 1;
}
if ($filled == 0) {
    write_log(sprintf($lang['takeupload_log'], $id, $torrent, $CURUSER['username']));
}
/* RSS feeds */
/*
if (($fd1 = @fopen('rss.xml', 'w')) && ($fd2 = fopen('rssdd.xml', 'w'))) {
    $cats = '';
    $res = sql_query('SELECT id, name FROM categories') or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        $cats[$arr['id']] = $arr['name'];
    }
    $s = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n<rss version=\"0.91\">\n<channel>\n" . "<title>{$site_config['site_name']}</title>\n<description>Installer09 is the best!</description>\n<link>{$site_config['baseurl']}/</link>\n";
    @fwrite($fd1, $s);
    @fwrite($fd2, $s);
    $r = sql_query('SELECT id, name, descr, filename, category
                    FROM torrents
                    ORDER BY added DESC
                    LIMIT 15') or sqlerr(__FILE__, __LINE__);
    while ($a = mysqli_fetch_assoc($r)) {
        $cat = $cats[$a['category']];
        $s = "<item>\n<title>" . htmlsafechars($a['name'] . " ($cat)") . "</title>\n" . '<description>' . htmlsafechars($a['descr']) . "</description>\n";
        @fwrite($fd1, $s);
        @fwrite($fd2, $s);
        @fwrite($fd1, "<link>{$site_config['baseurl']}/details.php?id=" . (int)$a['id'] . "&amp;hit=1</link>\n</item>\n");
        $filename = htmlsafechars($a['filename']);
        @fwrite($fd2, "<link>{$site_config['baseurl']}/download.php?torrent=" . (int)$a['id'] . "/$filename</link>\n</item>\n");
    }
    $s = "</channel>\n</rss>\n";
    @fwrite($fd1, $s);
    @fwrite($fd2, $s);
    @fclose($fd1);
    @fclose($fd2);
}
*/
/* Email notifs */
/*******************
 *
 * $res = sql_query("SELECT name FROM categories WHERE id=".sqlesc($catid)) or sqlerr(__FILE__, __LINE__);
 * $arr = mysqli_fetch_assoc($res);
 * $cat = htmlsafechars($arr["name"]);
 * $res = sql_query("SELECT email FROM users WHERE enabled='yes' AND notifs LIKE '%[cat$catid]%'") or sqlerr(__FILE__, __LINE__);
 * $uploader = $CURUSER['username'];
 *
 * $size = mksize($totallen);
 * $description = ($html ? strip_tags($descr) : $descr);
 *
 * $body = <<<EOD
 * A new torrent has been uploaded.
 *
 * Name: $torrent
 * Size: $size
 * Category: $cat
 * Uploaded by: $uploader
 *
 * Description
 * -------------------------------------------------------------------------------
 * $description
 * -------------------------------------------------------------------------------
 *
 * You can use the URL below to download the torrent (you may have to login).
 *
 * {$site_config['baseurl']}/details.php?id=$id&hit=1
 *
 * --
 * {$site_config['site_name']}
 * EOD;
 *
 * $to = "";
 * $nmax = 100; // Max recipients per message
 * $nthis = 0;
 * $ntotal = 0;
 * $total = mysqli_num_rows($res);
 * while ($arr = mysqli_fetch_row($res))
 * {
 * if ($nthis == 0)
 * $to = $arr[0];
 * else
 * $to .= "," . $arr[0];
 * ++$nthis;
 * ++$ntotal;
 * if ($nthis == $nmax || $ntotal == $total)
 * {
 * if (!mail("Multiple recipients <{$site_config['site_email']}>", "New torrent - $torrent", $body,
 * "From: {$site_config['site_email']}\r\nBcc: $to"))
 * stderr("Error", "Your torrent has been been uploaded. DO NOT RELOAD THE PAGE!\n" .
 * "There was however a problem delivering the e-mail notifcations.\n" .
 * "Please let an administrator know about this error!\n");
 * $nthis = 0;
 * }
 * }
 *******************/

setSessionVar('success', $lang['details_success']);
header("Location: {$site_config['baseurl']}/details.php?id=$id");
