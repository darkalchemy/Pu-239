<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
check_user_status();
$lang = load_language('global');
global $container, $CURUSER, $site_config;

$HTMLOUT = '';

$action = (isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : ''));
$mode = (isset($_GET['mode']) ? htmlsafechars($_GET['mode']) : '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'upload' || $action === 'edit') {
        $langs = isset($_POST['language']) ? htmlsafechars($_POST['language']) : '';
        if (empty($langs)) {
            stderr('Upload failed', 'No language selected');
        }
        $releasename = isset($_POST['releasename']) ? htmlsafechars($_POST['releasename']) : '';
        if (empty($releasename)) {
            stderr('Upload failed', 'Use a descriptive name for your subtitle');
        }
        $url = strip_tags(isset($_POST['imdb']) ? trim($_POST['imdb']) : '');
        $imdb = '';
        if (!empty($url)) {
            preg_match('/(tt\d{7})/i', $url, $imdb);
            $imdb = !empty($imdb[1]) ? 'https://www.imdb.com/title/' . $imdb[1] : '';
        }
        if (empty($imdb)) {
            stderr('Upload failed', 'Your IMDb link is invalid');
        }
        $comment = isset($_POST['comment']) ? htmlsafechars($_POST['comment']) : '';
        $poster = isset($_POST['poster']) ? htmlsafechars($_POST['poster']) : '';
        $fps = isset($_POST['fps']) ? htmlsafechars($_POST['fps']) : '';
        $cd = isset($_POST['cd']) ? htmlsafechars($_POST['cd']) : '';
        if ($action === 'upload') {
            $file = $_FILES['sub'];
            if (!isset($file)) {
                stderr('Upload failed', "The file can't be empty!");
            }
            if ($file['size'] > $site_config['subtitles']['max_size']) {
                stderr('Upload failed', 'Your file is too big.');
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
                stderr('Upload failed', 'File not allowed only .srt , .sub , .vtt or .txt  files');
            }
            $new_name = md5(TIME_NOW);
            $filename = "$new_name.$ext";
            $date = TIME_NOW;
            $owner = $CURUSER['id'];
            sql_query('INSERT INTO subtitles (name , filename,imdb,comment, lang, fps, poster, cds, added, owner ) VALUES (' . implode(',', array_map('sqlesc', [
                $releasename,
                $filename,
                $imdb,
                $comment,
                $langs,
                $fps,
                $poster,
                $cd,
                $date,
                $owner,
            ])) . ')') or sqlerr(__FILE__, __LINE__);
            move_uploaded_file($temp_name, UPLOADSUB_DIR . $filename);
            $id = ((is_null($___mysqli_res = mysqli_insert_id($mysqli))) ? false : $___mysqli_res);
            header("Refresh: 0; url=subtitles.php?mode=details&id=$id");
        }
        if ($action === 'edit') {
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if ($id == 0) {
                stderr('Err', 'Not a valid id');
            } else {
                $res = sql_query('SELECT * FROM subtitles WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                $arr = mysqli_fetch_assoc($res);
                if (mysqli_num_rows($res) == 0) {
                    stderr('Sorry', 'There is no subtitle with that id');
                }
                if ($CURUSER['id'] != $arr['owner'] && $CURUSER['class'] < UC_STAFF) {
                    bark("You're not the owner! How did that happen?\n");
                }
                $updateset = [];
                if ($arr['name'] != $releasename) {
                    $updateset[] = 'name = ' . sqlesc($releasename);
                }
                if ($arr['imdb'] != $imdb) {
                    $updateset[] = 'imdb = ' . sqlesc($imdb);
                }
                if ($arr['lang'] != $langs) {
                    $updateset[] = 'lang = ' . sqlesc($langs);
                }
                if ($arr['poster'] != $poster) {
                    $updateset[] = 'poster = ' . sqlesc($poster);
                }
                if ($arr['fps'] != $fps) {
                    $updateset[] = 'fps = ' . sqlesc($fps);
                }
                if ($arr['cds'] != $cd) {
                    $updateset[] = 'cds = ' . sqlesc($cd);
                }
                if ($arr['comment'] != $comment) {
                    $updateset[] = 'comment = ' . sqlesc($comment);
                }
                if (count($updateset) > 0) {
                    sql_query('UPDATE subtitles SET ' . implode(', ', $updateset) . ' WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
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
            stderr('Err', 'Not a valid id');
        } else {
            $res = sql_query('SELECT id, name, imdb, poster, fps, comment, cds, lang FROM subtitles WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);
            if (mysqli_num_rows($res) == 0) {
                stderr('Sorry', 'There is no subtitle with that id');
            }
        }
    }
    $HTMLOUT .= "
    <h1 class='has-text-centered'>" . ($mode === 'upload' ? 'New Subtitle' : 'Edit Subtitle</h1><h2 class="has-text-centered">' . htmlsafechars($arr['name'])) . "</h2>
    <form enctype='multipart/form-data' method='post' action='subtitles.php' accept-charset='utf-8'>";
    $body = '';
    if ($mode === 'upload') {
        $body .= "
        <tr>
            <td colspan='2'>
                <span class='has-text-danger'><b>Only .srt, .sub , .vtt or .txt files are accepted<br>Max file size: " . mksize($site_config['subtitles']['max_size']) . '</b></span>
            </td>
        </tr>';
    }
    $body .= "
        <tr>
            <td class='rowhead'>Language</td>
            <td>
                <select name='language' class='w-25' required>
                    <option value=''>- Select -</option>";
    $subs = $container->get('subtitles');
    foreach ($subs as $sub) {
        $body .= "
                    <option value='{$sub['id']}'" . ($mode === 'edit' && $arr['lang'] == $sub['id'] ? ' selected' : '') . ">{$sub['name']}</option>";
    }
    $body .= "
                </select>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>Release Name</td>
            <td>
                <input type='text' name='releasename' value='" . ($mode === 'edit' ? $arr['name'] : '') . "'  placeholder='Avatar.2009.EXTENDED.1080p.BluRay.x264-BestHD' class='w-100' required>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>IMDB link</td>
            <td>
                <input type='text' name='imdb' value='" . ($mode === 'edit' ? $arr['imdb'] : '') . "' placeholder='https://www.imdb.com/title/tt0499549/' class='w-100' pattern='.*[tt\d{7}]/' required>
            </td>
        </tr>";
    if ($mode === 'upload') {
        $body .= "
        <tr>
            <td class='rowhead'>SubFile</td>
            <td>
                <input type='file' name='sub' required>
            </td>
        </tr>";
    }
    $body .= "
        <tr>
            <td class='rowhead'>Poster</td>
            <td>
                <input type='text' name='poster' value='" . ($mode === 'edit' ? $arr['poster'] : '') . "' placeholder='https://m.media-amazon.com/images/M/MV5BMTYwOTEwNjAzMl5BMl5BanBnXkFtZTcwODc5MTUwMw@@._V1_.jpg' class='w-100' required>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>Comments</td>
            <td>
                <textarea rows='5' name='comment' title='Any specific details about this subtitle we need to know' class='w-100 tooltipper'>" . ($mode === 'edit' ? htmlsafechars($arr['comment']) : '') . "</textarea>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>FPS</td>
            <td>
                <select name='fps' class='w-25'>
                    <option value='0'>- Select -</option>
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
            <td class='rowhead'>CD Number</td>
            <td>
                <select name='cd' class='w-25'>
                    <option value='0'>- Select -</option>
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
                <input type='submit' value='Upload it' class='button is-small'>
                <input type='hidden' name='action' value='upload'>";
    } else {
        $body .= "
                <input type='submit' value='Edit it' class='button is-small'>
                <input type='hidden' name='action' value='edit'>
                <input type='hidden' name='id' value='" . (int) $arr['id'] . "'>";
    }
    $body .= '
            </td>
        </tr>';
    $HTMLOUT .= main_table($body) . '
    </form>';
    echo stdhead('' . ($mode === 'upload' ? 'Upload new Subtitle' : 'Edit subtitle ' . htmlsafechars($arr['name']) . '') . '') . wrapper($HTMLOUT) . stdfoot();
} elseif ($mode === 'delete') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id == 0) {
        stderr('Err', 'Not a valid id');
    } else {
        $res = sql_query('SELECT id, name, filename FROM subtitles WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (mysqli_num_rows($res) == 0) {
            stderr('Sorry', 'There is no subtitle with that id');
        }
        $sure = (isset($_GET['sure']) && $_GET['sure'] === 'yes') ? 'yes' : 'no';
        if ($sure === 'no') {
            stderr('Sanity check...', 'Your are about to delete subtitile <b>' . htmlsafechars($arr['name']) . "</b> . Click <a href='subtitles.php?mode=delete&amp;id=$id&amp;sure=yes'>here</a> if you are sure.", null);
        } else {
            sql_query('DELETE FROM subtitles WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $file = UPLOADSUB_DIR . $arr['filename'];
            @unlink($file);
            header('Refresh: 0; url=subtitles.php');
        }
    }
} elseif ($mode === 'details') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id == 0) {
        stderr('Err', 'Not a valid id');
    } else {
        $res = sql_query('SELECT s.id, s.name,s.lang, s.imdb,s.fps,s.poster,s.cds,s.hits,s.added,s.owner,s.comment, u.username FROM subtitles AS s LEFT JOIN users AS u ON s.owner=u.id  WHERE s.id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (mysqli_num_rows($res) == 0) {
            stderr('Sorry', 'There is no subtitle with that id');
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
        <tr><td><span class='level-left'>Language: {$langs}</span></td></tr>";
        if (!empty($arr['comment'])) {
            $body .= '
        <tr><td><fieldset><legend><b>Comment</b></legend> ' . htmlsafechars($arr['comment']) . '</fieldset></td></tr>';
        }
        $body .= '
        <tr><td>FPS : <b>' . ($arr['fps'] == 0 ? 'Unknown' : htmlsafechars($arr['fps'])) . '</b></td></tr>
        <tr><td>Cd# : <b>' . ($arr['cds'] == 0 ? 'Unknown' : ($arr['cds'] == 255 ? 'More than 5 ' : htmlsafechars($arr['cds']))) . '</b></td></tr>
        <tr><td>Hits : <b>' . (int) $arr['hits'] . '</b></td></tr>
        <tr>
            <td>Uploader : ' . format_username((int) $arr['owner']);
        if ($arr['owner'] == $CURUSER['id'] || $CURUSER['class'] > UC_STAFF) {
            $body .= "
                <a href='subtitles.php?mode=edit&amp;id=" . (int) $arr['id'] . "' title='Edit Sub' class='tooltipper'>
                    <i class='icon icon-edit' aria-hidden='true'></i>
                </a>
                <a href='subtitles.php?mode=delete&amp;id=" . (int) $arr['id'] . "' title='Delete Sub' class='tooltipper'>
                    <i class='icon icon-trash-empty has-text-danger' aria-hidden='true'></i>
                </a>";
        }
        $body .= '
            </td>
        </tr>
        <tr><td>Added : <b>' . get_date((int) $arr['added'], 'LONG', 0, 1) . '</b></td></tr>';
        $HTMLOUT .= "
        <div class='level-center'>
            $image" . main_table($body) . "
        </div>
        <div class='level-center-center'>
            <form action='downloadsub.php' method='post' accept-charset='utf-8'>
                <input type='hidden' name='sid' value='" . (int) $arr['id'] . "'>
                <input type='submit' value='Download' class='button is-small margin20'>
                <input type='hidden' name='action' value='download'>
            </form>
            <a href='subtitles.php?mode=preview&id={$arr['id']}' class='button is-small margin20'>Preview</a>
        </div>";
        echo stdhead('Details for ' . htmlsafechars($arr['name']) . '') . wrapper($HTMLOUT) . stdfoot();
    }
} elseif ($mode === 'preview') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id == 0) {
        stderr('Err', 'Not a valid id');
    } else {
        $res = sql_query('SELECT id, name,filename FROM subtitles WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (mysqli_num_rows($res) == 0) {
            stderr('Sorry', 'There is no subtitle with that id');
        }
        $file = UPLOADSUB_DIR . $arr['filename'];
        $fileContent = file_get_contents($file);
        $title = htmlsafechars($arr['name']);
        $HTMLOUT = "
    <ul class='bg-06 level-center'>
        <li class='margin10'><a href='subtitles.php?mode=details&amp;id={$id}'>Return to Details</a></li>
    </ul>
    <h1 class='has-text-centered'>Subtitle preview</h1>" . main_div("
    <div class='pre padding20'>" . substr(htmlsafechars($fileContent), 0, 1000) . '</div>');

        echo stdhead('Preview') . wrapper($HTMLOUT) . stdfoot();
    }
} else {
    $s = isset($_GET['s']) ? htmlsafechars($_GET['s']) : '';
    $w = isset($_GET['w']) ? htmlsafechars($_GET['w']) : '';
    $fluent = $container->get(Database::class);
    $count = $fluent->from('subtitles')
                    ->select(null)
                    ->select('COUNT(id) AS count');
    if ($s && $w === 'name') {
        $count = $count->where('name LIKE ?', '%%' . $s . '%');
        $where = "WHERE name LIKE '%%{$s}%'";
    } elseif ($s && $w === 'imdb') {
        $count = $count->where('imdb LIKE ?', '%%' . $s . '%');
        $where = "WHERE imdb LIKE '%%{$s}%'";
    } elseif ($s && $w === 'comment') {
        $count = $count->where('comment LIKE ?', '%%' . $s . '%');
        $where = "WHERE comment LIKE '%%{$s}%'";
    } else {
        $where = '';
    }
    $link = ($s && $w ? "s=$s&amp;w=$w&amp;" : '');
    $count = $count->fetch('count');

    if ($count == 0 && !$s && !$w) {
        stdmsg('', 'There is no subtitle, go <a href="subtitles.php?mode=upload">here</a> and start uploading.', false);
    }
    $perpage = 15;
    $pager = pager($perpage, $count, 'subtitles.php?' . $link);
    $res = sql_query("SELECT s.id, s.name,s.lang, s.imdb,s.fps,s.poster,s.cds,s.hits,s.added,s.owner,s.comment, u.username FROM subtitles AS s LEFT JOIN users AS u ON s.owner = u.id $where ORDER BY s.added DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= "
    <ul class='bg-06 level-center'>
        <li class='margin10'><a href='subtitles.php?mode=upload'>Upload a Subtitle</a></li>
    </ul>
    <div class='has-text-centered'>
        <h1>Search</h1>";
    $body = "
        <form action='subtitles.php' method='get' accept-charset='utf-8'>
            <div class='has-text-centered'>
                <input class='w-50 top20' value='" . $s . "' name='s' type='text'>
                <select name='w'>
                    <option value='name' " . ($w === 'name' ? 'selected' : '') . ">Name</option>
                    <option value='imdb' " . ($w === 'imdb' ? 'selected' : '') . ">IMDb</option>
                    <option value='comment' " . ($w === 'comment' ? 'selected' : '') . ">Comments</option>
                </select>
            </div>
            <div class='has-text-centered'>
                <input type='submit' value='Search' class='button is-small margin20'>
            </div>
        </form>";
    if ($s) {
        $body .= "
        <div class='top20 bg-00 padding20 round10>Search result for <i>'{$s}'</i><br>" . (mysqli_num_rows($res) == 0 ? 'Nothing found! Try again with a refined search string.' : '') . '</div>';
    }
    $HTMLOUT .= '
    </div>' . main_div($body);
    if (mysqli_num_rows($res) > 0) {
        $HTMLOUT .= "
    <div class='top20'></div>";
        if ($count > $perpage) {
            $HTMLOUT .= $pager['pagertop'];
        }
        $heading = '
    <tr>
        <th>Lang</th>
        <th>Name</th>
        <th>IMDb</th>
        <th>Added</th>
        <th>Hits</th>
        <th>FPS</th>
        <th>CD#</th>
        <th>Tools</th>
        <th>Upper</th>
    </tr>';

        $body = '';
        while ($arr = mysqli_fetch_assoc($res)) {
            $langs = '<b>Unknown</b>';
            foreach ($subs as $sub) {
                if ($sub['id'] == $arr['lang']) {
                    $langs = "<img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' alt='{$sub['name']}' class='tooltipper left10' title='{$sub['name']}'>";
                    break;
                }
            }
            $body .= "
    <tr>
        <td class='has-text-centered'>{$langs}</td>
        <td><a href='{$site_config['paths']['baseurl']}/subtitles.php?mode=details&amp;id=" . (int) $arr['id'] . "'>" . htmlsafechars($arr['name']) . "</a></td>
        <td class='has-text-centered'>
            <a href='" . htmlsafechars($arr['imdb']) . "'  target='_blank'>
                <img src='{$site_config['paths']['images_baseurl']}imdb.svg' alt='Imdb' title='Imdb' class='tooltipper' width='50px'>
            </a>
        </td>
        <td class='has-text-centered'>" . get_date((int) $arr['added'], 'LONG', 0, 1) . "</td>
        <td class='has-text-centered'>" . htmlsafechars($arr['hits']) . "</td>
        <td class='has-text-centered'>" . ($arr['fps'] == 0 ? '-' : htmlsafechars($arr['fps'])) . "</td>
        <td class='has-text-centered'>" . ($arr['cds'] == 0 ? '-' : ($arr['cds'] == 255 ? 'More than 5 ' : htmlsafechars($arr['cds']))) . '</td>';
            if ($arr['owner'] == $CURUSER['id'] || $CURUSER['class'] > UC_STAFF) {
                $body .= "
        <td class='has-text-centered'>
            <a href='subtitles.php?mode=edit&amp;id=" . (int) $arr['id'] . "' title='Edit Sub' class='tooltipper'>
                <i class='icon icon-edit' aria-hidden='true'></i>
            </a>
            <a href='subtitles.php?mode=delete&amp;id=" . (int) $arr['id'] . "' title='Delete Sub' class='tooltipper'>
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
    echo stdhead('Subtitles') . wrapper($HTMLOUT) . stdfoot();
}
