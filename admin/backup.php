<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang;

/* add your ids and uncomment this check*/
$allowed_ids = [
    1,
];
if (!in_array($CURUSER['id'], $allowed_ids)) {
    stderr($lang['backup_stderr'], $lang['backup_stderr1']);
}

$lang = array_merge($lang, load_language('ad_backup'));

$HTMLOUT        = '';
$required_class = UC_MAX;

if (is_array($required_class)) {
    if (!in_array($CURUSER['class'], $required_class)) {
        stderr($lang['backup_stderr'], $lang['backup_stderr']);
    }
} else {
    if ($required_class != $CURUSER['class']) {
        stderr($lang['backup_stderr'], $lang['backup_stderr1']);
    }
}
$mode = (isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : ''));
if (empty($mode)) {
    $res = sql_query('SELECT db.id, db.name, db.added, u.id AS uid, u.username
                        FROM dbbackup AS db
                        LEFT JOIN users AS u ON u.id = db.userid
                        ORDER BY db.added DESC') or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $HTMLOUT .= "
        <div class='container is-fluid portlet has-text-centered top20 bottom20 padding20'>
            <form method='post' action='{$site_config['baseurl']}/staffpanel.php?tool=backup&amp;mode=delete'>
                <input type='hidden' name='action' value='delete' />
                {$lang['backup_welcome']}
                <table id='checkbox_container' class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr>
                            <th>{$lang['backup_name']}</th>
                            <th>{$lang['backup_addedon']}</th>
                            <th>{$lang['backup_addedby']}</th>
                            <th><input type='checkbox' id='checkThemAll' class='tooltipper' title='{$lang['backup_markall']}' /></th>
                        </tr>
                    </thead>
                    <tbody>";
        while ($arr = mysqli_fetch_assoc($res)) {
            $HTMLOUT .= "
                        <tr>
                            <td><a href='{$site_config['baseurl']}/staffpanel.php?tool=backup&amp;mode=download&amp;id=" . (int) $arr['id'] . "'>" . htmlsafechars($arr['name']) . '</a></td>
                            <td>' . get_date($arr['added'], 'LONG', 1, 0) . '</td>
                            <td>';
            if (!empty($arr['uid'])) {
                $HTMLOUT .= format_username($arr['uid']);
            } else {
                $HTMLOUT .= '
                                unknown[' . (int) $arr['uid'] . ']';
            }
            $HTMLOUT .= "
                            </td>
                            <td>
                                <input type='checkbox' name='ids[]' class='tooltipper' title='{$lang['backup_mark']}' value='" . (int) $arr['id'] . "' />
                            </td>
                        </tr>";
        }

        $HTMLOUT .= "
                    </tbody>
                </table>
                <div class='has-text-centered top20 bottom20 level-center flex-center'>
                    <a class='button is-small' href='{$site_config['baseurl']}/staffpanel.php?tool=backup&amp;mode=backup'>{$lang['backup_dbbackup']}</a>
                    <input type='submit' class='button is-small' value='{$lang['backup_delselected']}' onclick=\"return confirm('{$lang['backup_confirm']}');\" />
                </div>
            </form>
            <div class='has-text-centered top20 bottom20'>
                <span class='flipper has-text-primary pointer'>
                    <i class='fa icon-down-open size_3 has-text-primary' aria-hidden='true'></i>
                    <span class='has-text-primary size_4 left10'>{$lang['backup_settingschk']}</span>
                </span>
                <div class='container is-fluid portlet is_hidden top20 bottom20'>
                    <table class='table table-bordered table-striped top20 bottom20'>
                        <tbody>
                            <tr>
                                <td>{$lang['backup_qzip']}</td>
                                <td>{$lang['backup_optional']}</td>
                                <td class='rowhead'>" . ($site_config['db_use_gzip'] ? "<div class='has-text-centered has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . "</td>
                            </tr>
                            <tr>
                                <td>{$lang['backup_qzippath']}</td>
                                <td>{$site_config['db_backup_gzip_path']}</td>
                                <td>" . (is_file($site_config['db_backup_gzip_path']) ? "<div class='has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . "</td>
                            </tr>
                            <tr>
                                <td>{$lang['backup_pathfolder']}</td>
                                <td>{$site_config['backup_dir']}</td>
                                <td>" . (is_dir($site_config['backup_dir']) ? "<div class='has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . "</td>
                            </tr>
                            <tr>
                                <td colspan='2'>{$lang['backup_readfolder']}</td>
                                <td>" . (is_readable($site_config['backup_dir']) ? "<div class='has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . "</td>
                            </tr>
                            <tr>
                                <td colspan='2'>{$lang['backup_writable']}</td>
                                <td>" . (is_writable($site_config['backup_dir']) ? "<div class='has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . "</td>
                            </tr>
                            <tr>
                                <td>{$lang['backup_mysqldump']}</td>
                                <td>{$site_config['db_backup_mysqldump_path']}</td>
                                <td>" . (preg_match('/mysqldump/i', exec($site_config['db_backup_mysqldump_path'])) ? "<div class='has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . "</td>
                            </tr>
                            <tr>
                                <td colspan='2'>{$lang['backup_downafter']}</td>
                                <td>" . ($site_config['db_backup_auto_download'] ? "<div class='has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . "</td>
                            </tr>
                            <tr>
                                <td colspan='2'>{$lang['backup_delafter']}</td>
                                <td>" . ($site_config['db_backup_auto_delete'] ? "<div class='has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . "</td>
                            </tr>
                            <tr>
                                <td colspan='2'>{$lang['backup_writeact']}</td>
                                <td>" . ($site_config['db_backup_write_to_log'] ? "<div class='has-text-centered has-text-green'>{$lang['backup_yes']}</div>" : "<div class='has-text-centered has-text-red'>{$lang['backup_no']}</div>") . '</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>';
    } else {
        $HTMLOUT .= "<h2>'{$lang['backup_nofound']}'</h2>";
    }

    if (isset($_GET['backedup'])) {
        $HTMLOUT .= stdmsg($lang['backup_success'], $lang['backup_backedup']);
    } elseif (isset($_GET['deleted'])) {
        $HTMLOUT .= stdmsg($lang['backup_success'], $lang['backup_deleted']);
    } elseif (isset($_GET['noselection'])) {
        $HTMLOUT .= stdmsg($lang['backup_stderr'], $lang['backup_selectb']);
    }
    echo stdhead($lang['backup_stdhead']) . $HTMLOUT . stdfoot();
} elseif ($mode === 'backup') {
    global $site_config;
    $mysql_host = $_ENV['DB_HOST'];
    $mysql_user = $_ENV['DB_USERNAME'];
    $mysql_pass = $_ENV['DB_PASSWORD'];
    $mysql_db   = $_ENV['DB_DATABASE'];
    $ext        = $mysql_db . '-' . date('d') . '-' . date('m') . '-' . date('Y') . '_' . date('H') . '-' . date('i') . '-' . date('s') . '_' . date('D') . '.sql';
    $filepath   = $site_config['backup_dir'] . '/' . $ext;
    exec("{$site_config['db_backup_mysqldump_path']} -h $mysql_host -u $mysql_user -p$mysql_pass $mysql_db > $filepath");
    if ($site_config['db_backup_use_gzip']) {
        exec("{$site_config['db_backup_gzip_path']} --best $filepath");
    }
    sql_query('INSERT INTO dbbackup (name, added, userid) VALUES (' . sqlesc($ext . ($site_config['db_backup_use_gzip'] ? '.gz' : '')) . ', ' . TIME_NOW . ', ' . sqlesc($CURUSER['id']) . ')') or sqlerr(__FILE__, __LINE__);
    $location = 'mode=backup';
    if ($site_config['db_backup_auto_download']) {
        $id       = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
        $location = 'mode=download&id=' . $id;
    }
    if ($site_config['db_backup_write_to_log']) {
        write_log($CURUSER['username'] . '(' . get_user_class_name($CURUSER['class']) . ') ' . $lang['backup_successfully'] . '');
    }
    header('Location: staffpanel.php?tool=backup');
    die();
} elseif ($mode === 'download') {
    $id = (isset($_GET['id']) ? (int) $_GET['id'] : 0);
    if (!is_valid_id($id)) {
        stderr($lang['backup_stderr'], $lang['backup_id']);
    }
    $res      = sql_query('SELECT name FROM dbbackup WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $arr      = mysqli_fetch_assoc($res);
    $filename = $site_config['backup_dir'] . '/' . $arr['name'];

    if (!is_file($filename)) {
        stderr($lang['backup_stderr'], $lang['backup_inexistent']);
    }
    $file_extension = strtolower(substr(strrchr($filename, '.'), 1));
    switch ($file_extension) {
        case 'sql':
            $ctype = 'application/sql';
            break;

        case 'sql.gz':
        case 'gz':
            $ctype = 'application/x-gzip';
            break;

        default:
            $ctype = 'application/force-download';
    }
    if ($site_config['db_backup_write_to_log']) {
        write_log($CURUSER['username'] . '(' . get_user_class_name($CURUSER['class']) . ') downloaded a database(' . htmlsafechars($arr['name']) . ').');
    }
    header('Refresh: 0; url=staffpanel.php' . ($site_config['db_backup_auto_download'] && !$site_config['db_backup_auto_delete'] ? '' : '?tool=backup&mode=delete&id=' . $id));
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header("Content-Type: $ctype");
    header('Content-Disposition: attachment; filename="' . basename($filename) . '";');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
} elseif ($mode === 'delete') {
    $ids = (isset($_POST['ids']) ? $_POST['ids'] : (isset($_GET['id']) ? [
        $_GET['id'],
    ] : []));
    if (!empty($ids)) {
        foreach ($ids as $id) {
            if (!is_valid_id($id)) {
                stderr($lang['backup_stderr'], $lang['backup_id']);
            }
        }
        $res   = sql_query('SELECT name FROM dbbackup WHERE id IN (' . implode(', ', array_map('sqlesc', $ids)) . ')') or sqlerr(__FILE__, __LINE__);
        $count = mysqli_num_rows($res);
        if ($count > 0) {
            while ($arr = mysqli_fetch_assoc($res)) {
                $filename = $site_config['backup_dir'] . '/' . $arr['name'];
                if (is_file($filename)) {
                    unlink($filename);
                }
            }
            sql_query('DELETE FROM dbbackup WHERE id IN (' . implode(', ', array_map('sqlesc', $ids)) . ')') or sqlerr(__FILE__, __LINE__);
            if ($site_config['db_backup_write_to_log']) {
                write_log($CURUSER['username'] . '(' . get_user_class_name($CURUSER['class']) . ') ' . $lang['backup_deleted1'] . ' ' . $count . ($count > 1 ? $lang['backup_database_plural'] : $lang['backup_database_singular']) . '.');
            }
            $location = 'backup';
        } else {
            $location = 'noselection';
        }
    } else {
        $location = 'noselection';
    }
    header('Location:staffpanel.php?tool=backup&mode=' . $location);
    die();
} else {
    stderr($lang['backup_srry'], $lang['backup_unknow']);
}
