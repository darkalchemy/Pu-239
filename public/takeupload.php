<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Files;
use Pu239\Message;
use Pu239\Peer;
use Pu239\Roles;
use Pu239\Session;
use Pu239\Torrent;
use Pu239\User;
use Pu239\Usersachiev;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class.bencdec.php';
require_once INCL_DIR . 'function_announce.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_ircbot.php';
require_once INCL_DIR . 'function_categories.php';
global $container, $site_config;

$data = $_POST;
$torrent_pass = isset($data['torrent_pass']) ? $data['torrent_pass'] : '';
$auth = isset($data['auth']) ? $data['auth'] : '';
$bot = isset($data['bot']) ? $data['bot'] : '';
$strip = isset($data['strip']) && is_bool($data['strip']) ? (bool) $data['strip'] : false;
$name = isset($data['name']) ? htmlsafechars($data['name']) : '';
$url = isset($data['url']) ? htmlsafechars($data['url']) : '';
$isbn = isset($data['isbn']) ? htmlsafechars($data['isbn']) : '';
$title = isset($data['title']) ? htmlsafechars($data['title']) : '';
$poster = isset($data['poster']) ? htmlsafechars($data['poster']) : '';
$youtube = isset($data['youtube']) ? htmlsafechars($data['youtube']) : '';
$tags = isset($data['tags']) ? htmlsafechars($data['tags']) : '';
$description = isset($data['description']) ? htmlsafechars($data['description']) : '';
$descr = isset($data['body']) ? htmlsafechars($data['body']) : '';
$release_group = isset($data['release_group']) ? htmlsafechars($data['release_group']) : '';
$free_length = isset($data['free_length']) && is_valid_id((int) $data['free_length']) ? (int) $data['free_length'] : 0;
$half_length = isset($data['half_length']) && is_valid_id((int) $data['half_length']) ? (int) $data['half_length'] : 0;
$music = isset($data['music']) && is_array($data['music']) ? $data['music'] : [];
$movie = isset($data['movie']) && is_array($data['movie']) ? $data['movie'] : [];
$game = isset($data['game']) && is_array($data['game']) ? $data['game'] : [];
$apps = isset($data['apps']) && is_array($data['apps']) ? $data['apps'] : [];
$subs = isset($data['subs']) && is_array($data['subs']) ? implode('|', $data['subs']) : '';
$audios = isset($data['audios']) && is_array($data['audios']) ? implode('|', $data['audios']) : '';
$genre = isset($data['genre']) ? $data['genre'] : '';
$catid = isset($data['type']) && is_valid_id((int) $data['type']) ? (int) $data['type'] : 0;
$recipe = isset($data['recipe']) && is_valid_id((int) $data['recipe']) ? (int) $data['recipe'] : 0;
$request = isset($data['request']) && is_valid_id((int) $data['request']) ? (int) $data['request'] : 0;
$offer = isset($data['offer']) && is_valid_id((int) $data['offer']) ? (int) $data['offer'] : 0;
$uplver = isset($data['uplver']) && $data['uplver'] === 'yes' ? 'yes' : 'no';
$allow_comments = isset($data['allow_comments']) && $data['allow_comments'] === 'no' ? 'no' : 'yes';

$cache = $container->get(Cache::class);
$users_class = $container->get(User::class);
if (!empty($bot) && !empty($auth) && !empty($torrent_pass)) {
    $owner_id = $users_class->get_bot_id($bot, $torrent_pass, $auth);
    $user = $users_class->getUserFromId($owner_id);
} else {
    $user = check_user_status();
    $owner_id = $user['id'];
    $cache->set('user_upload_variables_' . $user['id'], json_encode($data), 3600);
}
$dt = TIME_NOW;
ini_set('upload_max_filesize', (string) $site_config['site']['max_torrent_size']);
ini_set('memory_limit', '64M');
$session = $container->get(Session::class);
if (!$user['roles_mask'] & Roles::UPLOADER || $user['uploadpos'] != 1 || $user['status'] === 5) {
    $cache->delete('user_upload_variables_' . $owner_id);
    $session->set('is-warning', _('You do not have permission to upload torrents'));
    why_die(_('You do not have permission to upload torrents'));
}
if (empty($descr) || empty($catid) || empty($name) || empty($_FILES['file'])) {
    $session->set('is-warning', _('Missing form data'));
    why_die(_('Missing form data'));
}
if (!empty($url)) {
    preg_match('/(tt\d{7,8})/i', $url, $imdb);
    $imdb = !empty($imdb[1]) ? $imdb[1] : '';
}
$f = $_FILES['file'];
$fname = unesc($f['name']);
if (empty($fname)) {
    $session->set('is-warning', _('Empty filename!'));
    why_die(_('Empty filename!'));
}

if ($uplver === 'yes') {
    $anonymous = 1;
    $anon = get_anonymous_name();
} else {
    $anonymous = 0;
    $anon = $user['username'];
}

if (!empty($music)) {
    $genre = implode(',', $music);
} elseif (!empty($movie)) {
    $genre = implode(',', $movie);
} elseif (!empty($game)) {
    $genre = implode(',', $game);
} elseif (!empty($apps)) {
    $genre = implode(',', $apps);
}
$nfo = $nfofilename = '';

if (!empty($_FILES['nfo']) && !empty($_FILES['nfo']['name'])) {
    $nfofile = $_FILES['nfo'];
    if ($nfofile['name'] == '') {
        $session->set('is-warning', _('No NFO!'));
        why_die(_('No NFO!'));
    } elseif ($nfofile['size'] == 0) {
        $session->set('is-warning', _('0-byte NFO'));
        why_die(_('0-byte NFO'));
    } elseif ($nfofile['size'] > $site_config['site']['nfo_size']) {
        $session->set('is-warning', _('NFO is too big! Max 65,535 bytes.'));
        why_die(_('NFO is too big! Max 65,535 bytes.'));
    } else {
        $nfofilename = $nfofile['tmp_name'];
    }
    if (@!is_uploaded_file($nfofilename)) {
        $session->set('is-warning', _('NFO upload failed'));
        why_die(_('NFO upload failed'));
    }
    $nfo_content = str_ireplace([
        "\xEF\xBB\xBF",
        "\x0d\x0d\x0a",
        "\xb0",
    ], [
        '',
        "\x0d\x0a",
        '',
    ], file_get_contents($nfofilename));
    $nfo = $nfo_content;
    if ($strip) {
        $nfo = preg_replace('`/[^\\x20-\\x7e\\x0a\\x0d]`', ' ', $nfo);
        $nfo = preg_replace('`[\x00-\x08\x0b-\x0c\x0e-\x1f\x7f-\xff]`', '', $nfo);
    }
}

$free2 = 0;
$free_text = $free_text_irc = '';
if (!empty($free_length)) {
    if ($free_length === 255) {
        $free2 = 1;
        $free_text = '[b]Freeleech:[/b] Forever';
        $free_text_irc = '\0038Freeleech:\0039 Forever ';
    } elseif ($free_length === 42) {
        $free2 = 86400 + $dt;
        $free_text = '[b]Freeleech:[/b] 24 Hours';
        $free_text_irc = '\0038Freeleech:\0039 24 Hours ';
    } else {
        $free2 = $dt + $free_length * 604800;
        $free_text = "[b]Freeleech:[/b] $free_length Weeks";
        $free_text_irc = "\0038Freeleech:\0039 $free_length Weeks ";
    }
}

$silver = 0;
if (!empty($half_length)) {
    if ($half_length === 255) {
        $silver = 1;
    } elseif ($half_length === 42) {
        $silver = 86400 + $dt;
    } else {
        $silver = $dt + $half_length * 604800;
    }
}

$freetorrent = !empty($freetorrent) && is_valid_id($freetorrent) ? (int) $freetorrent : 0;
if (empty($descr) && !empty($_FILES['nfo']) && !empty($_FILES['nfo']['name'])) {
    $descr = preg_replace('/[^\\x20-\\x7e\\x0a\\x0d]/', ' ', $nfo);
}
if (empty($descr)) {
    $session->set('is-warning', _('You must enter a description or a Nfo!'));
    why_die(_('You must enter a description or a Nfo!'));
}
if (!is_valid_id($catid)) {
    $session->set('is-warning', _('You must select a category to put the torrent in!'));
    why_die(_('You must select a category to put the torrent in!'));
}
$release_group_array = [
    'scene' => 1,
    'p2p' => 1,
    'none' => 1,
];
$release_group = !empty($release_group) && !empty($release_group_array[$release_group]) ? $release_group : 'none';

if (!empty($youtube) && preg_match('#' . $site_config['youtube']['pattern'] . '#i', $youtube, $temp_youtube)) {
    $youtube = $temp_youtube[0];
}

if (!validfilename($fname)) {
    $session->set('is-warning', _('Invalid filename!'));
    why_die(_('Invalid filename!'));
}

if (!empty($isbn)) {
    $isbn = str_replace([
        '-',
        ' ',
    ], '', $isbn);
}

if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches)) {
    $session->set('is-warning', _('Invalid filename (not a .torrent).'));
    why_die(_('Invalid filename (not a .torrent).'));
}
$shortfname = $torrent = $matches[1];
if (!empty($name)) {
    $torrent = unesc($name);
}
$tmpname = $f['tmp_name'];
if (!is_uploaded_file($tmpname)) {
    $session->set('is-warning', _('eek'));
    why_die(_('eek'));
}
if (!filesize($tmpname)) {
    $session->set('is-warning', _('Empty file!'));
    why_die(_('Empty file!'));
}
$dict = bencdec::decode_file($tmpname, $site_config['site']['max_torrent_size'], bencdec::OPTION_EXTENDED_VALIDATION);
if ($dict === false) {
    $session->set('is-warning', _('What did you upload? This is not a bencoded file!'));
    why_die(_('What did you upload? This is not a bencoded file!'));
}
if (!empty($dict['announce-list'])) {
    unset($dict['announce-list']);
}
$dict['info']['private'] = 1;
if (empty($dict['info'])) {
    $session->set('is-warning', _('invalid torrent, info dictionary does not exist'));
    why_die(_('invalid torrent, info dictionary does not exist'));
}
$info = &$dict['info'];
$infohash = pack('H*', sha1(bencdec::encode($info)));
$fluent = $container->get(Database::class);
$count = $fluent->from('torrents')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->where('info_hash = ?', $infohash)
                ->fetch('count');

if ($count > 0) {
    $session->set('is-warning', _('This torrent has already been uploaded! Please use the search function before uploading.'));
    why_die(_('This torrent has already been uploaded! Please use the search function before uploading.'));
}
if (bencdec::get_type($info) != 'dictionary') {
    $session->set('is-warning', _('invalid torrent, info is not a dictionary'));
    why_die(_('invalid torrent, info is not a dictionary'));
}
if (empty($info['name']) || empty($info['piece length']) || empty($info['pieces'])) {
    $session->set('is-warning', _('invalid torrent, missing parts of the info dictionary'));
    why_die(_('invalid torrent, missing parts of the info dictionary'));
}
if (bencdec::get_type($info['name']) != 'string' || bencdec::get_type($info['piece length']) != 'integer' || bencdec::get_type($info['pieces']) != 'string') {
    $session->set('is-warning', _('invalid torrent, invalid types in the info dictionary'));
    why_die(_('invalid torrent, invalid types in the info dictionary'));
}
$dname = $info['name'];
$plen = $info['piece length'];
$pieces_len = strlen($info['pieces']);
if ($pieces_len % 20 != 0) {
    $session->set('is-warning', _('invalid pieces'));
    why_die(_('invalid pieces'));
}
if ($plen % 4096) {
    $session->set('is-warning', _('piece size is not mod(4096), invalid torrent.'));
    why_die(_('piece size is not mod(4096), invalid torrent.'));
}
$filelist = [];
if (!empty($info['length'])) {
    if (bencdec::get_type($info['length']) != 'integer') {
        $session->set('is-warning', _('Invalid filename!'));
        why_die(_('Invalid filename!'));
    }
    $totallen = $info['length'];
    $filelist[] = [
        $dname,
        $totallen,
    ];
} else {
    if (empty($info['files'])) {
        $session->set('is-warning', _('missing both length and files'));
        why_die(_('missing both length and files'));
    }
    if (bencdec::get_type($info['files']) != 'list') {
        $session->set('is-warning', _('invalid files, not a list'));
        why_die(_('invalid files, not a list'));
    }
    $flist = &$info['files'];
    if (!count($flist)) {
        $session->set('is-warning', _('no files'));
        why_die(_('no files'));
    }
    $totallen = 0;
    $fn = [
        'length' => 0,
        'path' => '',
    ];
    foreach ($flist as $fn) {
        if (empty($fn['length']) || empty($fn['path'])) {
            $session->set('is-warning', _('file info not found, empty filename in torrent file?'));
            why_die(_('file info not found, empty filename in torrent file?'));
        }
        if (bencdec::get_type($fn['length']) != 'integer' || bencdec::get_type($fn['path']) != 'list') {
            $session->set('is-warning', _('invalid file info'));
            why_die(_('invalid file info'));
        }
        $ll = $fn['length'];
        $ff = $fn['path'];
        $totallen += $ll;
        $ffa = [];
        foreach ($ff as $ffe) {
            if (bencdec::get_type($ffe) != 'string') {
                $session->set('is-warning', _('filename type error'));
                why_die(_('filename type error'));
            }
            $ffa[] = $ffe;
        }
        if (!count($ffa)) {
            $session->set('is-warning', _('filename error'));
            why_die(_('filename error'));
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
    $session->set('is-warning', _('total file size and number of pieces do not match'));
    why_die(_('total file size and number of pieces do not match'));
}

$tmaker = !empty($dict['created by']) && !empty($dict['created by']) ? $dict['created by'] : _('Unknown');
$dict['comment'] = _('In using this torrent you are bound by the Crafty Confidentiality Agreement By Law');

$visible = 'no';
$torrent = str_replace('_', ' ', $torrent);
$vip = (!empty($vip) ? '1' : '0');

$values = [
    'isbn' => $isbn,
    'title' => $title,
    'search_text' => searchfield("$shortfname $dname $torrent"),
    'filename' => $fname,
    'owner' => $owner_id,
    'visible' => $visible,
    'vip' => $vip,
    'release_group' => $release_group,
    'newgenre' => $genre,
    'poster' => $poster,
    'anonymous' => $anonymous,
    'allow_comments' => $allow_comments,
    'info_hash' => $infohash,
    'name' => $torrent,
    'size' => $totallen,
    'numfiles' => count($filelist),
    'offer' => $offer,
    'request' => $request,
    'url' => $url,
    'subs' => $subs,
    'audios' => $audios,
    'descr' => $descr,
    'ori_descr' => $descr,
    'description' => $description,
    'category' => $catid,
    'free' => $free2,
    'silver' => $silver,
    'save_as' => $dname,
    'youtube' => $youtube,
    'tags' => $tags,
    'added' => $dt,
    'last_action' => get_date($dt, 'MYSQL', 1, 0),
    'mtime' => $dt,
    'ctime' => $dt,
    'freetorrent' => $freetorrent,
    'nfo' => $nfo,
    'client_created_by' => $tmaker,
];
if (!empty($imdb)) {
    $values['imdb_id'] = $imdb;
}
$torrents_class = $container->get(Torrent::class);
$id = (int) $torrents_class->add($values);

if (!$id) {
    $session->set('is-warning', _('Upload failed!'));
    why_die(_('Upload failed!'));
}

$torrents_class->remove_torrent($infohash);
$torrents_class->get_torrent_from_hash($infohash);
$cache->delete('peers_' . $owner_id);
$peer_class = $container->get(Peer::class);
$peer_class->get_peers_from_userid($owner_id);
clear_image_cache();

$files = $container->get(Files::class);
$files->delete($id);

/**
 * @param $arr
 * @param $id
 *
 * @return array
 */
function file_list($arr, $id)
{
    $new = [];
    foreach ($arr as $v) {
        $new[] = [
            'torrent' => $id,
            'filename' => $v[0],
            'size' => $v[1],
        ];
    }

    return $new;
}

$files->insert(file_list($filelist, $id));
$dir = TORRENTS_DIR . $id . '.torrent';
if (!bencdec::encode_file($dir, $dict)) {
    $session->set('is-warning', _('Could not properly encode file'));
    why_die(_('Could not properly encode file'));
}
try {
    unlink($tmpname);
} catch (Exception $e) {
    //TODO
}
if ($site_config['bonus']['on']) {
    $seedbonus = $user['seedbonus'];
    $update = [
        'seedbonus' => $user['seedbonus'] + $site_config['bonus']['per_upload'],
        'numuploads' => $user['numuploads'] + 1,
    ];
    $users_class->update($update, $owner_id);
}
$cat_name = get_fullname_from_id($catid);
if ($site_config['site']['autoshout_chat']) {
    if (!empty($uplver) && $uplver === 'yes') {
        $msg = get_anonymous_name() . " has just added a torrent in [color=lightgreen][b]{$cat_name}[/b][/color]
        [url={$site_config['paths']['baseurl']}/details.php?id=$id&hit=1] [b][i]" . htmlsafechars($torrent) . '[/i][/b][/url]
        [b]Size:[/b] ' . mksize($totallen) . (!empty($free_text) ? "
        $free_text" : '');
    } else {
        $msg = htmlsafechars($user['username']) . " has just added a torrent in [color=lightgreen][b]{$cat_name}[/b][/color]
        [url={$site_config['paths']['baseurl']}/details.php?id=$id&hit=1] [b][i]" . htmlsafechars($torrent) . '[/i][/b][/url]
        [b]Size:[/b] ' . mksize($totallen) . (!empty($free_text) ? "
        $free_text" : '');
    }
    autoshout($msg);
    autoshout($msg, 2, 0);
}
if ($site_config['site']['autoshout_irc']) {
    $messages = "\0034New Torrent\0039 in \0038$cat_name\0039 $torrent \0034Uploaded By:\0039 $anon \0034Size:\0039 " . mksize($totallen) . "{$free_text_irc}\0034 Link:\0038 " . $site_config['paths']['baseurl'] . '/details.php?id=' . $id . '&hit=1';
    ircbot($messages);
}
$messages_class = $container->get(Message::class);
if ($recipe > 0) {
    $update = [
        'torrentid' => $id,
        'status' => 'uploaded',
    ];
    $cooker = $fluent->update('upcoming')
                     ->set($update)
                     ->where('id = ?', $recipe);
    if ($user['class'] < UC_STAFF) {
        $cooker = $cooker->where('userid = ?', $user['id']);
    }
    $cooker = $cooker->execute();
    if (!empty($cooker)) {
        $recipes = $fluent->from('upcoming_notify')
                          ->select(null)
                          ->select('userid')
                          ->where('upcomingid = ?', $recipe)
                          ->fetchAll();
        $subject = _('A Recipe has just come out of the oven');
        $msg = "Hi, \n An reciper you were interested in has been uploaded!!! \n\n Click  [url=" . $site_config['paths']['baseurl'] . '/details.php?id=' . $id . '&hit=1]' . htmlsafechars($torrent) . '[/url] to see the torrent details page!';
        foreach ($recipes as $arr_recipe) {
            $msgs_buffer[] = [
                'receiver' => $arr_recipe['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        }
        if (!empty($msgs_buffer)) {
            $messages_class->insert($msgs_buffer);
        }
        write_log('Cooker Recipe: torrent ' . $id . ' (' . htmlsafechars($torrent) . ') was uploaded by ' . $user['username']);
    }
}

if ($offer > 0) {
    $offers = $fluent->from('offer_votes')
                     ->select(null)
                     ->select('user_id')
                     ->where('vote = "yes"')
                     ->where('user_id != ?', $owner_id)
                     ->where('offer_id = ?', $offer)
                     ->fetchAll();
    $subject = _('An offer you voted for has been uploaded!');
    $msg = "Hi, \n An recipe you were interested in has been uploaded!!! \n\n Click  [url=" . $site_config['paths']['baseurl'] . '/details.php?id=' . $id . '&hit=1]' . htmlsafechars($torrent) . '[/url] to see the torrent details page!';
    foreach ($offers as $arr_offer) {
        $msgs_buffer[] = [
            'receiver' => $arr_offer['user_id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (!empty($msgs_buffer)) {
        $messages_class->insert($msgs_buffer);
    }
    write_log('Offered torrent ' . $id . ' (' . htmlsafechars($torrent) . ') was uploaded by ' . $user['username']);
    $filled = 1;
    $set = [
        'torrentid' => $id,
        'updated' => $dt,
    ];
    $fluent->update('offers')
           ->set($set)
           ->where('id = ?', $offer)
           ->execute();
}
$filled = 0;
if ($request > 0) {
    $requests = $fluent->from('request_votes')
                       ->select(null)
                       ->select('user_id')
                       ->where('vote = "yes"')
                       ->where('user_id != ?', $owner_id)
                       ->where('request_id = ?', $request)
                       ->fetchAll();

    $subject = _('A request you were interested in has been uploaded!');
    $msg = "Hi :D \n A request you were interested in has been uploaded!!! \n\n Click  [url=" . $site_config['paths']['baseurl'] . '/details.php?id=' . $id . '&hit=1]' . htmlsafechars($torrent) . '[/url] to see the torrent details page!';
    foreach ($requests as $arr_req) {
        $msgs_buffer[] = [
            'receiver' => $arr_req['user_id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (!empty($msgs_buffer)) {
        $messages_class->insert($msgs_buffer);
    }
    if ($site_config['bonus']['on']) {
        $set = [
            'seedbonus' => $update['seedbonus'] + $site_config['bonus']['per_request'],
        ];
        $users_class->update($set, $user['id']);
    }
    $set = [
        'filled_by_user_id' => $owner_id,
        'torrentid' => $id,
        'updated' => $dt,
    ];
    $fluent->update('requests')
           ->set($set)
           ->where('id = ?', $request)
           ->execute();

    $users_achieve = $container->get(Usersachiev::class);
    $update = [
        'reqfilled' => new Literal('reqfilled + 1'),
    ];
    $users_achieve->update($update, $owner_id);
    write_log('Request for torrent ' . $id . ' (' . htmlsafechars($torrent) . ') was filled by ' . $user['username']);
    $filled = 1;
}
if ($filled == 0) {
    write_log(_fe('Torrent {0} ({1}) was uploaded by {2}', $id, $torrent, $user['username']));
}

$notify = $users_class->get_notifications($catid);
if (!empty($notify)) {
    $subject = _('New Torrent Uploaded!');
    $msg = "A torrent in one of your default categories has been uploaded! \n\n Click  [url=" . $site_config['paths']['baseurl'] . '/details.php?id=' . $id . ']' . htmlsafechars($torrent) . '[/url] to see the torrent details page!';
    foreach ($notify as $notif) {
        if (strpos($notif['notifs'], 'pmail') !== false) {
            $msgs_buffer[] = [
                'receiver' => $notif['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        }
    }
    if (!empty($msgs_buffer)) {
        $messages_class->insert($msgs_buffer);
    }
}

$cache->delete('user_upload_variables_' . $owner_id);
$session->set('is-success', _('Successfully uploaded!'));
header("Location: {$site_config['paths']['baseurl']}/details.php?id=$id&uploaded=1");

/**
 * @param string $why
 */
function why_die(string $why)
{
    if (!empty($_SERVER['HTTP_REFERER'])) {
        header("Location: {$_SERVER['HTTP_REFERER']}");
        die();
    }
    die($why);
}
