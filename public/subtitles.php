<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
$user = check_user_status();
global $container, $site_config;

$HTMLOUT = '';

$action = (isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : ''));
$mode = (isset($_GET['mode']) ? htmlsafechars($_GET['mode']) : '');
$fluent = $container->get(Database::class);
$subs = $container->get('subtitles');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'upload' || $action === 'edit') {
        $langs = isset($_POST['language']) ? htmlsafechars($_POST['language']) : '';
        if (empty($langs)) {
            stderr(_('Error'), _('No language selected'));
        }
        $releasename = isset($_POST['releasename']) ? htmlsafechars($_POST['releasename']) : '';
        if (empty($releasename)) {
            stderr(_('Error'), _('Use a descriptive name for your subtitle'));
        }
        $url = strip_tags(isset($_POST['imdb']) ? trim($_POST['imdb']) : '');
        $imdb = '';
        if (!empty($url)) {
            preg_match('/(tt\d{7,8})/i', $url, $imdb);
            $imdb = !empty($imdb[1]) ? 'https://www.imdb.com/title/' . $imdb[1] : '';
        }
        if (empty($imdb)) {
            stderr(_('Error'), _('Your IMDb link is invalid'));
        }
        $comment = isset($_POST['comment']) ? htmlsafechars($_POST['comment']) : '';
        $poster = isset($_POST['poster']) ? htmlsafechars($_POST['poster']) : '';
        $fps = isset($_POST['fps']) ? htmlsafechars($_POST['fps']) : '';
        $cd = isset($_POST['cd']) ? (int) $_POST['cd'] : '';
        if ($action === 'upload') {
            $file = $_FILES['sub'];
            if (!isset($file)) {
                stderr(_('Error'), _("The file can't be empty!"));
            }
            if ($file['size'] > $site_config['subtitles']['max_size']) {
                stderr(_('Error'), _('Your file is too big.'));
            }
            $fname = $file['name'];
            $temp_name = $file['tmp_name'];
            $ext = pathinfo($fname, PATHINFO_EXTENSION);
            $allowed = [
                'srt',
                'sub',
                'txt',
                'vtt',
            ];
            if (!in_array($ext, $allowed)) {
                stderr(_('Error'), _('File not allowed only .srt , .sub , .vtt or .txt files'));
            }
            $new_name = md5((string) TIME_NOW);
            $filename = "$new_name.$ext";
            $date = TIME_NOW;
            $owner = $user['id'];
            $values = [
                'name' => $releasename,
                'filename' => $filename,
                'imdb' => $imdb,
                'comment' => $comment,
                'lang' => $langs,
                'fps' => $fps,
                'poster' => $poster,
                'cds' => $cd,
                'added' => $date,
                'owner' => $owner,
            ];
            $id = $fluent->insertInto('subtitles')
                         ->values($values)
                         ->execute();
            move_uploaded_file($temp_name, UPLOADSUB_DIR . $filename);
            header("Refresh: 0; url=subtitles.php?mode=details&id=$id");
        }
        if ($action === 'edit') {
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if ($id == 0) {
                stderr(_('Error'), _('Invalid ID'));
            } else {
                $arr = $fluent->from('subtitles')
                              ->where('id = ?', $id)
                              ->fetch();
                if (empty($arr)) {
                    stderr(_('Error'), _('Invalid ID'));
                }
                if ($user['id'] != $arr['owner'] && $user['class'] < UC_STAFF) {
                    stderr(_('Error'), _("You're not the owner!"));
                }
                $updateset = [];
                if ($arr['name'] != $releasename) {
                    $updateset['name'] = $releasename;
                }
                if ($arr['imdb'] != $imdb) {
                    $updateset['imdb'] = $imdb;
                }
                if ($arr['lang'] != $langs) {
                    $updateset['lang'] = $langs;
                }
                if ($arr['poster'] != $poster) {
                    $updateset['poster'] = $poster;
                }
                if ($arr['fps'] != $fps) {
                    $updateset['fps'] = $fps;
                }
                if ($arr['cds'] != $cd) {
                    $updateset['cds'] = $cd;
                }
                if ($arr['comment'] != $comment) {
                    $updateset['comment'] = $comment;
                }
                if (count($updateset) > 0) {
                    $fluent->update('subtitle')
                           ->set($updateset)
                           ->where('id = ?', $id)
                           ->execute();
                }
                header("Refresh: 0; url=subtitles.php?mode=details&id=$id");
            }
        }
    }
}

if ($mode === 'upload' || $mode === 'edit') {
    if ($mode === 'edit') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id == 0) {
            stderr(_('Error'), _('Invalid ID'));
        } else {
            $arr = $fluent->from('subtitles')
                          ->where('id = ?', $id)
                          ->fetch();
            if (empty($arr)) {
                stderr(_('Error'), _('Invalid ID'));
            }
        }
    }
    $HTMLOUT .= "
    <h2 class='has-text-centered'>" . ($mode === 'upload' ? _('New Subtitle') . '</h2>' : _('Edit Subtitle') . '</h1><h2 class="has-text-centered">' . format_comment($arr['name']) . '</h2>') . "
    <form method='post' action='subtitles.php' enctype='multipart/form-data' accept-charset='utf-8'>";
    $body = '';
    if ($mode === 'upload') {
        $body .= "
        <tr>
            <td colspan='2'>
                <span class='has-text-danger'><b>" . _('Only .srt, .sub , .vtt or .txt files are accepted') . '<br>' . _('Max file size') . ': ' . mksize($site_config['subtitles']['max_size']) . '</b></span>
            </td>
        </tr>';
    }
    $body .= "
        <tr>
            <td class='rowhead'>" . _('Language') . "</td>
            <td>
                <select name='language' class='w-25' required>
                    <option value=''>" . _('Select') . '</option>';
    foreach ($subs as $sub) {
        $body .= "
                    <option value='{$sub['id']}' " . ($mode === 'edit' && $arr['lang'] == $sub['id'] ? 'selected' : '') . ">{$sub['name']}</option>";
    }
    $body .= "
                </select>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('Release Name') . "</td>
            <td>
                <input type='text' name='releasename' value='" . ($mode === 'edit' ? $arr['name'] : '') . "'  placeholder='Avatar.2009.EXTENDED.1080p.BluRay.x264-BestHD' class='w-100' required>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('IMDb URL') . "</td>
            <td>
                <input type='text' name='imdb' value='" . ($mode === 'edit' ? $arr['imdb'] : '') . "' placeholder='https://www.imdb.com/title/tt0499549/' class='w-100' pattern='.*[tt\d{7,8}]/' required>
            </td>
        </tr>";
    if ($mode === 'upload') {
        $body .= "
        <tr>
            <td class='rowhead'>" . _('Subfile') . "</td>
            <td>
                <input type='file' name='sub' required>
            </td>
        </tr>";
    }
    $body .= "
        <tr>
            <td class='rowhead'>" . _('Poster') . "</td>
            <td>
                <input type='text' name='poster' value='" . ($mode === 'edit' ? $arr['poster'] : '') . "' placeholder='https://m.media-amazon.com/images/M/MV5BMTYwOTEwNjAzMl5BMl5BanBnXkFtZTcwODc5MTUwMw@@._V1_.jpg' class='w-100' required>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('Comments') . "</td>
            <td>
                <textarea rows='5' name='comment' title='Any specific details about this subtitle we need to know' class='w-100 tooltipper'>" . ($mode === 'edit' ? htmlsafechars($arr['comment']) : '') . "</textarea>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('FPS') . "</td>
            <td>
                <select name='fps' class='w-25'>
                    <option value='0'>" . _('Select') . "</option>
                    <option value='23.976' " . ($mode === 'edit' && $arr['fps'] == '23.976' ? 'selected' : '') . ">23.976</option>
                    <option value='23.980' " . ($mode === 'edit' && $arr['fps'] == '23.980' ? 'selected' : '') . ">23.980</option>
                    <option value='24.000' " . ($mode === 'edit' && $arr['fps'] == '24.000' ? 'selected' : '') . ">24.000</option>
                    <option value='25.000' " . ($mode === 'edit' && $arr['fps'] == '25.000' ? 'selected' : '') . ">25.000</option>
                    <option value='29.970' " . ($mode === 'edit' && $arr['fps'] == '29.970' ? 'selected' : '') . ">29.970</option>
                    <option value='30.000' " . ($mode === 'edit' && $arr['fps'] == '30.000' ? 'selected' : '') . ">30.000</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('CD Number') . "</td>
            <td>
                <select name='cd' class='w-25'>
                    <option value='0'>" . _('Select') . "</option>
                    <option value='1' " . ($mode === 'edit' && $arr['cds'] == '1' ? 'selected' : '') . ">1CD</option>
                    <option value='2' " . ($mode === 'edit' && $arr['cds'] == '2' ? 'selected' : '') . ">2CD</option>
                    <option value='3' " . ($mode === 'edit' && $arr['cds'] == '3' ? 'selected' : '') . ">3CD</option>
                    <option value='4' " . ($mode === 'edit' && $arr['cds'] == '4' ? 'selected' : '') . ">4CD</option>
                    <option value='5' " . ($mode === 'edit' && $arr['cds'] == '5' ? 'selected' : '') . ">5CD</option>
                    <option value='255' " . ($mode === 'edit' && $arr['cds'] == '255' ? 'selected' : '') . ">More</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan='2' class='colhead has-text-centered'>";
    if ($mode == 'upload') {
        $body .= "
                <input type='submit' value='" . _('Upload it') . "' class='button is-small'>
                <input type='hidden' name='action' value='upload'>";
    } else {
        $body .= "
                <input type='submit' value='" . _('Edit it') . "' class='button is-small'>
                <input type='hidden' name='action' value='edit'>
                <input type='hidden' name='id' value='" . $arr['id'] . "'>";
    }
    $body .= '
            </td>
        </tr>';
    $HTMLOUT .= main_table($body) . '
    </form>';
    $title = $mode === 'upload' ? _('Upload New Subtitle') : _('Edit Subtitle');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
} elseif ($mode === 'delete') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id == 0) {
        stderr(_('Error'), _('Invalid ID'));
    } else {
        $arr = $fluent->from('subtitles')
                      ->where('id = ?', $id)
                      ->fetch();
        if (empty($arr)) {
            stderr(_('Error'), _('Invalid ID'));
        }
        $sure = (isset($_GET['sure']) && $_GET['sure'] === 'yes') ? 'yes' : 'no';
        if ($sure === 'no') {
            stderr(_('Sanity check...'), _fe('Your are about to delete subtitle <b>{0}</b>, Click {1}here{2} if you are sure.', format_comment($arr['name']) . "<a href='{$site_config['paths']['baseurl']}/subtitles.php?mode=delete&amp;id=$id&amp;sure=yes'>", '</a>'));
        } else {
            $fluent->deleteFrom('subtitles')
                   ->where('id = ?', $id)
                   ->execute();
            $file = UPLOADSUB_DIR . $arr['filename'];
            @unlink($file);
            header('Refresh: 0; url=subtitles.php');
        }
    }
} elseif ($mode === 'details') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id == 0) {
        stderr(_('Error'), _('Invalid ID'));
    } else {
        $arr = $fluent->from('subtitles AS s')
                      ->where('s.id = ?', $id)
                      ->fetch();
        if (empty($arr)) {
            stderr(_('Error'), _('Invalid ID'));
        }
        $langs = '<b>Unknown</b>';
        foreach ($subs as $sub) {
            if ($sub['id'] == $arr['lang']) {
                $langs = "<img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' alt='{$sub['name']}' class='tooltipper left10' title='{$sub['name']}'>";
                break;
            }
        }
        $image = "
        <div class='margin20'>
            <img src='" . url_proxy($arr['poster'], true, 250) . "' width='250' alt='" . htmlsafechars($arr['name']) . "' class='round10'>
        </div>";
        $body = '
        <tr><td>Name : <b>' . htmlsafechars($arr['name']) . "</b></td></tr>
        <tr><td>IMDb : <a href='" . htmlsafechars($arr['imdb']) . "' target='_blank'>" . htmlsafechars($arr['imdb']) . "</a></td></tr>
        <tr><td><span class='level-left'>" . _('Language') . ": {$langs}</span></td></tr>";
        if (!empty($arr['comment'])) {
            $body .= '
        <tr><td><fieldset><legend><b>' . _('Comment') . '</b></legend> ' . htmlsafechars($arr['comment']) . '</fieldset></td></tr>';
        }
        $body .= '
        <tr><td>FPS : <b>' . ($arr['fps'] == 0 ? _('Unknown') : htmlsafechars($arr['fps'])) . '</b></td></tr>
        <tr><td>Cd# : <b>' . ($arr['cds'] == 0 ? _('Unknown') : ($arr['cds'] == 255 ? _('More than 5') : $arr['cds'])) . '</b></td></tr>
        <tr><td>Hits : <b>' . $arr['hits'] . '</b></td></tr>
        <tr>
            <td>Uploader : ' . format_username($arr['owner']);
        if ($arr['owner'] == $user['id'] || $user['class'] > UC_STAFF) {
            $body .= "
                <a href='subtitles.php?mode=edit&amp;id=" . $arr['id'] . "' title='" . _('Edit Subtitle') . "' class='tooltipper'>
                    <i class='icon icon-edit' aria-hidden='true'></i>
                </a>
                <a href='subtitles.php?mode=delete&amp;id=" . $arr['id'] . "' title='" . _('Delete Subtitle') . "' class='tooltipper'>
                    <i class='icon icon-trash-empty has-text-danger' aria-hidden='true'></i>
                </a>";
        }
        $body .= '
            </td>
        </tr>
        <tr><td>' . _('Added') . ': <b>' . get_date($arr['added'], 'LONG', 0, 1) . '</b></td></tr>';
        $HTMLOUT .= "
        <div class='level-center'>
            $image" . main_table($body) . "
        </div>
        <div class='level-center-center'>
            <form action='downloadsub.php' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                <input type='hidden' name='sid' value='" . $arr['id'] . "'>
                <input type='submit' value='" . _('Download') . "' class='button is-small margin20'>
                <input type='hidden' name='action' value='download'>
            </form>
            <a href='subtitles.php?mode=preview&id={$arr['id']}' class='button is-small margin20'>" . _('Preview') . '</a>
        </div>';
        $title = _('Subtitle Details');
        $breadcrumbs = [
            "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
            "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
        ];
        echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
    }
} elseif ($mode === 'preview') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id == 0) {
        stderr(_('Error'), _('Invalid ID'));
    } else {
        $arr = $fluent->from('subtitles')
                      ->where('id = ?', $id)
                      ->fetch();
        if (empty($arr)) {
            stderr(_('Error'), _('Invalid ID'));
        }
        $file = UPLOADSUB_DIR . $arr['filename'];
        $content = file_get_contents($file);
        $fileContent = substr(strip_tags($content), 0, 1000);
        $HTMLOUT = "
    <ul class='bg-06 level-center'>
        <li class='margin10'><a href='subtitles.php?mode=details&amp;id={$id}'>" . _('Return to Details') . "</a></li>
    </ul>
    <h1 class='has-text-centered'>" . _('Subtitle Preview') . '</h1>' . main_div("
    <div class='pre padding20'>" . $fileContent . '</div>');
        $title = ('Subtitle Preview');
        $breadcrumbs = [
            "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
            "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
        ];
        echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
    }
} else {
    $s = isset($_GET['s']) ? htmlsafechars($_GET['s']) : '';
    $w = isset($_GET['w']) ? htmlsafechars($_GET['w']) : '';
    $count = $fluent->from('subtitles')
                    ->select(null)
                    ->select('COUNT(id) AS count');
    $select = $fluent->from('subtitles AS s');
    if ($s && $w === 'name') {
        $count = $count->where('name LIKE ?', '%' . $s . '%');
        $select = $select->where('s.name LIKE ?', '%' . $s . '%');
    } elseif ($s && $w === 'imdb') {
        $count = $count->where('imdb LIKE ?', '%' . $s . '%');
        $select = $select->where('s.imdb LIKE ?', '%' . $s . '%');
    } elseif ($s && $w === 'comment') {
        $count = $count->where('comment LIKE ?', '%' . $s . '%');
        $select = $select->where('s.comment LIKE ?', '%' . $s . '%');
    }
    $link = ($s && $w ? "s=$s&amp;w=$w&amp;" : '');
    $count = $count->fetch('count');
    $title = empty($s) ? _('Search') : _fe("Search result for <i>'{0}'</i>", format_comment($s));
    if ($count === 0 && !$s && !$w) {
        stdmsg(_('Error'), _fe('There are no subtitles, go {0}here{1} and start uploading.', '<a href="' . $site_config['paths']['baseurl'] . '/subtitles.php?mode=upload">', '</a>'));
    }
    $perpage = 15;
    $pager = pager($perpage, $count, 'subtitles.php?' . $link);
    $select = $select->orderBy('s.added')
                     ->limit($pager['pdo']['limit'])
                     ->offset($pager['pdo']['offset'])
                     ->fetchAll();
    $HTMLOUT .= "
    <ul class='bg-06 level-center'>
        <li class='margin10'><a href='subtitles.php?mode=upload'>" . _('Upload a Subtitle') . "</a></li>
    </ul>
    <div class='has-text-centered'>
        <h1>$title</h1>";
    $body = "
        <form action='subtitles.php' method='get' enctype='multipart/form-data' accept-charset='utf-8'>
            <div class='has-text-centered'>
                <input class='w-50 top20' value='" . $s . "' name='s' type='text'>
                <select name='w'>
                    <option value='name' " . ($w === 'name' ? 'selected' : '') . '>' . _('Name') . "</option>
                    <option value='imdb' " . ($w === 'imdb' ? 'selected' : '') . '>' . _('IMDb') . "</option>
                    <option value='comment' " . ($w === 'comment' ? 'selected' : '') . '>' . _('Comments') . "</option>
                </select>
            </div>
            <div class='has-text-centered'>
                <input type='submit' value='" . _('Search') . "' class='button is-small margin20'>
            </div>
        </form>";

    if ($count === 0) {
        $body .= "
        <div class='has-text-centered padding20'>
            " . _('Nothing found! Try again with a refined search string.') . '
        </div>';
    }
    $HTMLOUT .= '
    </div>' . main_div($body);
    if ($count > 0) {
        $HTMLOUT .= "
    <div class='top20'></div>";
        if ($count > $perpage) {
            $HTMLOUT .= $pager['pagertop'];
        }
        $heading = '
    <tr>
        <th>' . _('Language') . '</th>
        <th>' . _('Name') . '</th>
        <th>' . _('IMDb') . '</th>
        <th>' . _('Added') . '</th>
        <th>' . _('Hits') . '</th>
        <th>' . _('FPS') . '</th>
        <th>' . _('CD#') . '</th>
        <th>' . _('Tools') . '</th>
        <th>' . _('Uploader') . '</th>
    </tr>';

        $body = '';
        foreach ($select as $arr) {
            $langs = '<b>' . _('Unknown') . '</b>';
            foreach ($subs as $sub) {
                if ($sub['id'] == $arr['lang']) {
                    $langs = "<img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' alt='{$sub['name']}' class='tooltipper left10' title='{$sub['name']}'>";
                    break;
                }
            }
            $body .= "
    <tr>
        <td class='has-text-centered'>{$langs}</td>
        <td><a href='{$site_config['paths']['baseurl']}/subtitles.php?mode=details&amp;id=" . $arr['id'] . "'>" . format_comment($arr['name']) . "</a></td>
        <td class='has-text-centered'>
            <a href='" . htmlsafechars($arr['imdb']) . "'  target='_blank'>
                <img src='{$site_config['paths']['images_baseurl']}imdb.svg' alt='Imdb' title='Imdb' class='tooltipper' width='50px'>
            </a>
        </td>
        <td class='has-text-centered'>" . get_date((int) $arr['added'], 'LONG', 0, 1) . "</td>
        <td class='has-text-centered'>" . $arr['hits'] . "</td>
        <td class='has-text-centered'>" . ($arr['fps'] === 0 ? '-' : format_comment($arr['fps'])) . "</td>
        <td class='has-text-centered'>" . ($arr['cds'] === 0 ? '-' : ($arr['cds'] == 255 ? _('More than 5') : $arr['cds'])) . '</td>';
            if ($arr['owner'] == $user['id'] || $user['class'] > UC_STAFF) {
                $body .= "
        <td class='has-text-centered'>
            <a href='subtitles.php?mode=edit&amp;id=" . $arr['id'] . "' title='" . _('Edit Subtitle') . "' class='tooltipper'>
                <i class='icon icon-edit' aria-hidden='true'></i>
            </a>
            <a href='subtitles.php?mode=delete&amp;id=" . $arr['id'] . "' title='" . _('Delete Subtitle') . "' class='tooltipper'>
                <i class='icon icon-trash-empty has-text-danger' aria-hidden='true'></i>
            </a>
        </td>";
            } else {
                $body .= '
        <td></td>';
            }
            $body .= "
        <td class='has-text-centered'>" . format_username((int) $arr['owner']) . '</td>
    </tr>';
        }
        $HTMLOUT .= main_table($body, $heading);
        if ($count > $perpage) {
            $HTMLOUT .= $pager['pagerbottom'];
        }
    }
    $title = _('Subtitles');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
}
