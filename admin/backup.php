<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $CURUSER, $site_config;

$dt = TIME_NOW;
$HTMLOUT = '';
$required_class = UC_MAX;

if (is_array($required_class)) {
    if (!in_array($CURUSER['class'], $required_class)) {
        stderr(_('Error'), _('You do not have permission to do this.'));
    }
} else {
    if ($required_class != $CURUSER['class']) {
        stderr(_('Error'), _('Access denied!'));
    }
}
$mode = (isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : ''));

$fluent = $container->get(Database::class);
if (empty($mode)) {
    $backups = $fluent->from('dbbackup')
                      ->orderBy('added DESC')
                      ->fetchAll();

    if ($backups) {
        $HTMLOUT .= "
            <form method='post' action='{$_SERVER['PHP_SELF']}?tool=backup&amp;mode=delete' enctype='multipart/form-data' accept-charset='utf-8'>
                <input type='hidden' name='action' value='delete'>
                <h1 class='has-text-centered'>" . _fe('Welcome {0}, to the Database Backup Manager.', $CURUSER['username']) . "</h1>
                <table id='checkbox_container' class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr>
                            <th>" . _('Name') . "</th>
                            <th class='has-text-centered'>" . _('Added on') . "</th>
                            <th class='has-text-centered'>" . _('Added by') . "</th>
                            <th class='has-text-centered'><input type='checkbox' id='checkThemAll' class='tooltipper' title='" . _('Mark all') . "'></th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($backups as $arr) {
            $HTMLOUT .= "
                        <tr>
                            <td><a href='{$_SERVER['PHP_SELF']}?tool=backup&amp;mode=download&amp;id=" . $arr['id'] . "'>" . htmlsafechars($arr['name']) . '</a></td>
                            <td class="has-text-centered">' . get_date((int) $arr['added'], 'LONG', 1, 0) . '</td>
                            <td class="has-text-centered">';
            if (!empty($arr['userid'])) {
                $HTMLOUT .= format_username((int) $arr['userid']);
            } else {
                $HTMLOUT .= '
                                unknown[' . $arr['userid'] . ']';
            }
            $HTMLOUT .= "
                            </td>
                            <td class='has-text-centered'>
                                <input type='checkbox' name='ids[]' class='tooltipper' title='" . _('Mark') . "' value='" . $arr['id'] . "'>
                            </td>
                        </tr>";
        }

        $HTMLOUT .= "
                    </tbody>
                </table>
                <div class='has-text-centered top20 bottom20 level-center flex-center'>
                    <a class='button is-small' href='{$_SERVER['PHP_SELF']}?tool=backup&amp;mode=backup'>" . _('Backup Database') . "</a>
                    <input type='submit' class='button is-small' value='" . _('Delete Selected') . "' onclick=\"return confirm('" . _('Are you sure you want to delete the selected backups?') . "');\">
                </div>
            </form>
            <div class='has-text-centered'>
                <div class='flipper has-text-primary pointer bottom10'>
                    <i class='icon-up-open size_3 has-text-primary' aria-hidden='true'></i>
                    <span class='has-text-primary size_4 left10'>" . _('Settings Check') . "</span>
                </div>
                <div class='portlet is_hidden'>
                    <table class='table table-bordered table-striped'>
                        <tbody>
                            <tr>
                                <td>" . _('Use gzip compression') . '</td>
                                <td>' . _('Optional') . "</td>
                                <td class='rowhead'>" . ($site_config['backup']['use_gzip'] ? "<div class='has-text-centered has-text-centered is-success'>" . _('Yes') . '</div>' : "<div class='has-text-centered has-text-danger'>" . _('No') . '</div>') . '</td>
                            </tr>
                            <tr>
                                <td>' . _('Correct path to gzip') . '</td>
                                <td>' . GZIP . '</td>
                                <td>' . (is_file(GZIP) ? "<div class='has-text-centered is-success'>" . _('Yes') . '</div>' : "<div class='has-text-centered has-text-danger'>" . _('No') . '</div>') . '</td>
                            </tr>
                            <tr>
                                <td>' . _('Correct path to backup folder') . '</td>
                                <td>' . BACKUPS_DIR . '</td>
                                <td>' . (is_dir(BACKUPS_DIR) ? "<div class='has-text-centered is-success'>" . _('Yes') . '</div>' : "<div class='has-text-centered has-text-danger'>" . _('No') . '</div>') . "</td>
                            </tr>
                            <tr>
                                <td colspan='2'>" . _('Readable backup folder') . '</td>
                                <td>' . (is_readable(BACKUPS_DIR) ? "<div class='has-text-centered is-success'>" . _('Yes') . '</div>' : "<div class='has-text-centered has-text-danger'>" . _('No') . '</div>') . "</td>
                            </tr>
                            <tr>
                                <td colspan='2'>" . _('Writable backup folder') . '</td>
                                <td>' . (is_writable(BACKUPS_DIR) ? "<div class='has-text-centered is-success'>" . _('Yes') . '</div>' : "<div class='has-text-centered has-text-danger'>" . _('No') . '</div>') . '</td>
                            </tr>
                            <tr>
                                <td>' . _('Correct path to the mysqldump file') . '</td>
                                <td>' . MYSQLDUMP . '</td>
                                <td>' . (is_file(MYSQLDUMP) ? "<div class='has-text-centered is-success'>" . _('Yes') . '</div>' : "<div class='has-text-centered has-text-danger'>" . _('No') . '</div>') . "</td>
                            </tr>
                            <tr>
                                <td colspan='2'>" . _('Write actions to log(backup/download/delete)') . '</td>
                                <td>' . ($site_config['backup']['write_to_log'] ? "<div class='has-text-centered is-success'>" . _('Yes') . '</div>' : "<div class='has-text-centered has-text-danger'>" . _('No') . '</div>') . '</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>';
    } else {
        $HTMLOUT .= main_div("
                <div class='padding20 has-text-centered'>
                    " . _('Nothing Found') . "
                    <div class='top20'>
                        <a class='button is-small' href='{$_SERVER['PHP_SELF']}?tool=backup&amp;mode=backup'>" . _('Backup Database') . '</a>
                    </div>
                </div>');
    }

    if (isset($_GET['backedup'])) {
        $HTMLOUT .= stdmsg(_('Success'), _('Database backed up.'));
    } elseif (isset($_GET['deleted'])) {
        $HTMLOUT .= stdmsg(_('Success'), _('Backup(s) Deleted.'));
    } elseif (isset($_GET['noselection'])) {
        $HTMLOUT .= stdmsg(_('Error'), _('Please select a backup to delete.'));
    }
    $title = _('Backup Manager');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
} elseif ($mode === 'backup') {
    $host = $site_config['db']['host'];
    $user = $site_config['db']['username'];
    $pass = quotemeta($site_config['db']['password']);
    $db = $site_config['db']['database'];
    $ext = $db . '_' . date('Y.m.d-H.i.s', $dt) . '.sql';
    $bdir = BACKUPS_DIR . 'db' . DIRECTORY_SEPARATOR . date('Y.m.d', $dt) . DIRECTORY_SEPARATOR;
    make_dir($bdir, 0774);
    $filepath = $bdir . $ext;
    if ($site_config['backup']['use_gzip']) {
        exec(MYSQLDUMP . " -h $host -u'{$user}' -p'{$pass}' $db | gzip -q9>{$filepath}.gz");
    } else {
        exec(MYSQLDUMP . " -h $host -u'{$user}' -p'{$pass}' $db>$filepath");
    }
    $values = [
        'name' => $ext . ($site_config['backup']['use_gzip'] ? '.gz' : ''),
        'added' => $dt,
        'userid' => $CURUSER['id'],
    ];
    $fluent->insertInto('dbbackup')
           ->values($values)
           ->execute();

    if ($site_config['backup']['write_to_log']) {
        write_log($CURUSER['username'] . '(' . get_user_class_name((int) $CURUSER['class']) . ') ' . _('successfully backed-up the database.'));
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=backup');
    die();
} elseif ($mode === 'delete') {
    $ids = (isset($_POST['ids']) ? $_POST['ids'] : (isset($_GET['id']) ? [
        $_GET['id'],
    ] : []));
    if (!empty($ids)) {
        foreach ($ids as $id) {
            if (!is_valid_id((int) $id)) {
                stderr(_('Error'), _('Invalid ID'));
            }
        }
        $files = $fluent->from('dbbackup')
                        ->select(null)
                        ->select('name')
                        ->where('id', $ids)
                        ->fetchAll();

        if ($files) {
            $count = count($files);
            foreach ($files as $arr) {
                preg_match('/\d{4}\.\d{2}\.\d{2}/', $arr['name'], $match);
                if (isset($match[0])) {
                    $filename = BACKUPS_DIR . 'db' . DIRECTORY_SEPARATOR . $match[0] . DIRECTORY_SEPARATOR . $arr['name'];
                    if (is_file($filename)) {
                        unlink($filename);
                    }
                }
            }
            $fluent->deleteFrom('dbbackup')
                   ->where('id', $ids)
                   ->execute();

            if ($site_config['backup']['write_to_log']) {
                write_log($CURUSER['username'] . '(' . get_user_class_name((int) $CURUSER['class']) . ') ' . _(' successfully deleted') . ' ' . $count . ' ' . ($count > 1 ? _('databases') : _('database')) . '.');
            }
            $location = 'backup';
        } else {
            $location = 'noselection';
        }
    } else {
        $location = 'noselection';
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=backup&mode=' . $location);
    die();
} else {
    stderr(_('Error'), _('Unknown action!'));
}
