<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'password_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

if (!$site_config['bucket_allowed']) {
    $session->set('is-warning', 'BitBucket has been disabled');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
use Blocktrail\CryptoJSAES\CryptoJSAES;

$lang = array_merge(load_language('global'), load_language('bitbucket'));
$HTMLOUT = '';

$SaLt = $site_config['site']['salt'];
$SaLty = $site_config['site']['salty'];
$skey = $site_config['site']['skey'];
$maxsize = $site_config['bucket_maxsize'];
$folders = date('Y/m');
$formats = $site_config['allowed_formats'];
$str = implode('|', $formats);
$str = str_replace('.', '', $str);

$bucketdir = (isset($_POST['avy']) ? AVATAR_DIR : BITBUCKET_DIR . $folders . '/');
$bucketlink = ((isset($_POST['avy']) || (isset($_GET['images']) && $_GET['images'] == 2)) ? 'avatar/' : $folders . '/');
$PICSALT = $SaLt . $CURUSER['username'];
$USERSALT = substr(md5($SaLty . $CURUSER['id']), 0, 6);
make_year(BITBUCKET_DIR);
make_month(BITBUCKET_DIR);
if (!isset($_FILES['file'])) {
    if (isset($_GET['delete'])) {
        $getfile = htmlsafechars($_GET['delete']);
        $delfile = urldecode(decrypt($getfile));
        $delhash = md5($delfile . $USERSALT . $SaLt);
        if ($delhash != $_GET['delhash']) {
            stderr($lang['bitbucket_umm'], "{$lang['bitbucket_wayd']}");
        }
        $myfile = BITBUCKET_DIR . $delfile;
        if ((($pi = pathinfo($myfile)) && preg_match('#^(' . $str . ')$#i', $pi['extension'])) && is_file($myfile)) {
            unlink($myfile);
        } else {
            stderr($lang['bitbucket_hey'], "{$lang['bitbucket_imagenf']}");
        }
        $folder_m = (!isset($_GET['month']) ? '&month=' . date('m') : '&month=' . (int) $_GET['month']);
        $yea = (!isset($_GET['year']) ? '&year=' . date('Y') : '&year=' . (int) $_GET['year']);
        if (isset($_GET['type']) && $_GET['type'] == 2) {
            header("Refresh: 2; url={$site_config['baseurl']}/bitbucket.php?images=2");
        } else {
            header("Refresh: 2; url={$site_config['baseurl']}/bitbucket.php?images=1" . $yea . $folder_m);
        }
        die($lang['bitbucket_deleting'] . $delfile . $lang['bitbucket_redir']);
    }
    if (isset($_GET['avatar']) && $_GET['avatar'] != '' && (($_GET['avatar']) != $CURUSER['avatar'])) {
        $type = isset($_GET['type']) && $_GET['type'] == 1 ? 1 : 2;
        if (preg_match("/^http:\/\/$/i", $_GET['avatar']) || preg_match('/[?&;]/', $_GET['avatar']) || preg_match('#javascript:#is', $_GET['avatar']) || !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $_GET['avatar'])) {
            stderr($lang['bitbucket_error'], "{$lang['bitbucket_mustbe']}");
        }
        $avatar = sqlesc($_GET['avatar']);
        sql_query("UPDATE users SET avatar = $avatar WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user' . $CURUSER['id'], [
            'avatar' => $_GET['avatar'],
        ], $site_config['expires']['user_cache']);
        header("Refresh: 0; url={$site_config['baseurl']}/bitbucket.php?images=$type&updated=avatar");
    }
    if (isset($_GET['updated']) && $_GET['updated'] === 'avatar') {
        $HTMLOUT .= "
        <h3>{$lang['bitbucket_updated']}
            <img src='" . url_proxy($CURUSER['avatar'], true, 500, 'auto') . "' width='50%' height='auto' alt='' />
        </h3>";
    }
    $HTMLOUT .= "
        <div class='container is-fluid portlet has-text-centered'>
            <form action='{$_SERVER['PHP_SELF']}' method='post' enctype='multipart/form-data'>
                <div>
                    <h2>{$lang['bitbucket_invalid_extension']}" . join(', ', $formats) . "</h2>
                </div>
                <div>
                    <h3>{$lang['bitbucket_max']}" . mksize($maxsize) . "</h3>
                </div>
                <div>
                    <p>{$lang['bitbucket_disclaimer']}</p>
                </div>
                <div class='margin10'>
                    <input type='file' name='file' /></td>
                </div>
                <div class='margin10'>
                    <input type='checkbox' name='avy' value='1' />{$lang['bitbucket_tick']}
                </div>
                <div class='margin10'>
                    <input class='button is-small' type='submit' value='{$lang['bitbucket_upload']}' />
                </div>
            </form>
            <script>
                function SelectAll(id) {
                    document.getElementById(id).focus();
                    document.getElementById(id).select();
                }
            </script>";
    if (isset($_GET['images']) && $_GET['images'] == 1) {
        $folder_month = (!isset($_GET['month']) ? date('m') : ($_GET['month'] < 10 ? '0' : '') . (int) $_GET['month']);
        $year = (!isset($_GET['year']) ? '&amp;year=' . date('Y') : '&amp;year=' . (int) $_GET['year']);
        $HTMLOUT .= "
            <div>
                <ul class='level-center bg-06 padding10'>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=2'>{$lang['bitbucket_viewmya']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php'>{$lang['bitbucket_hideimgs']}</a>
                    </li>
                </ul>
            </div>
            <div>
                <h2>{$lang['bitbucket_previosimg']}</h2>
                <ul class='level-center bg-06 padding10'>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month={$folder_month}&amp;year=" . (isset($_GET['year']) && $_GET['year'] != date('Y') ? date('Y') . "'>This" : (date('Y') - 1) . "'>" . $lang['bitbucket_last'] . '') . ' ' . $lang['bitbucket_year'] . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=01{$year}'>{$lang['bitbucket_jan']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=02{$year}'>{$lang['bitbucket_feb']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=03{$year}'>{$lang['bitbucket_mar']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=04{$year}'>{$lang['bitbucket_apr']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=05{$year}'>{$lang['bitbucket_may']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=06{$year}'>{$lang['bitbucket_jun']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=07{$year}'>{$lang['bitbucket_jul']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=08{$year}'>{$lang['bitbucket_aug']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=09{$year}'>{$lang['bitbucket_sep']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=10{$year}'>{$lang['bitbucket_oct']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=11{$year}'>{$lang['bitbucket_nov']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1&amp;month=12{$year}'>{$lang['bitbucket_dec']}</a>
                    </li>
                </ul>
            </div>";
    } elseif (isset($_GET['images']) && $_GET['images'] == 2) {
        $HTMLOUT .= "
            <div>
                <ul class='level-center bg-06 padding10'>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1'>{$lang['bitbucket_viewmonths']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php'>{$lang['bitbucket_hidemya']}</a>
                    </li>
                </ul>
            </div>";
    } else {
        $HTMLOUT .= "
            <div>
                <ul class='level-center bg-06 padding10'>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1'>{$lang['bitbucket_viewmonths']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=2'>{$lang['bitbucket_viewmya']}</a>
                    </li>
                </ul>
            </div>";
    }
    if (isset($_GET['images'])) {
        $folder_month = (!isset($_GET['month']) ? date('m') : ($_GET['month'] < 10 ? '0' : '') . (int) $_GET['month']);
        $folder_name = (!isset($_GET['year']) ? date('Y') . '/' : (int) $_GET['year'] . '/') . $folder_month;
        $bucketlink2 = ((isset($_POST['avy']) || (isset($_GET['images']) && $_GET['images'] == 2)) ? 'avatar/' : $folder_name . '/');
        foreach ((array) glob(($_GET['images'] == 2 ? AVATAR_DIR . $USERSALT : BITBUCKET_DIR . $folder_name . '/' . $USERSALT) . '_*') as $filename) {
            if (!empty($filename)) {
                $filename = basename($filename);
                $filename = $bucketlink2 . $filename;
                $encryptedfilename = urlencode(encrypt($filename));
                $eid = md5($filename);
                $HTMLOUT .= "
            <div class='bitbucket'>
                <div class='margin20'>
                    <a href='{$site_config['baseurl']}/img.php?{$filename}'>
                        <img src='{$site_config['baseurl']}/img.php?{$filename}' width='50%' height='auto' alt='' />
                    </a>
                </div>
                <h3>{$lang['bitbucket_directlink']}</h3>
                <div class='bottom10'>
                    <input id='d{$eid}d' onclick=\"SelectAll('d{$eid}d');\" type='text' size='70' value='{$site_config['baseurl']}/img.php?{$filename}' readonly='readonly' />
                </div>
                <h3>{$lang['bitbucket_tags']}</h3>
                <div class='bottom10'>
                    <input id='t{$eid}t' onclick=\"SelectAll('t{$eid}t');\" type='text' size='70' value='[img]{$site_config['baseurl']}/img.php?{$filename}[/img]' readonly='readonly' />
                </div>
                <div>
                    <ul class='level-center bg-06 padding10'>
                        <li>
                            <a href='{$site_config['baseurl']}/bitbucket.php?type=" . ((isset($_GET['images']) && $_GET['images'] == 2) ? '2' : '1') . "&amp;avatar={$site_config['baseurl']}/img.php?{$filename}'>{$lang['bitbucket_maketma']}</a>
                        </li>
                        <li>
                            <a href='{$site_config['baseurl']}/bitbucket.php?type=" . ((isset($_GET['images']) && $_GET['images'] == 2) ? '2' : '1') . '&amp;delete=' . $encryptedfilename . '&amp;delhash=' . md5($filename . $USERSALT . $SaLt) . '&amp;month=' . (!isset($_GET['month']) ? date('m') : ($_GET['month'] < 10 ? '0' : '') . (int) $_GET['month']) . '&amp;year=' . (!isset($_GET['year']) ? date('Y') : (int) $_GET['year']) . "'>{$lang['bitbucket_delete']}</a>
                        </li>
                    </ul>
                </div>
            </div>";
            } else {
                $HTMLOUT .= "
                {$lang['bitbucket_noimages']}";
            }
        }
    }
    $HTMLOUT .= '
        </div>';
    echo stdhead($lang['bitbucket_bitbucket']) . $HTMLOUT . stdfoot();
    die();
}
if ($_FILES['file']['size'] == 0) {
    stderr($lang['bitbucket_error'], $lang['bitbucket_upfail']);
}
if ($_FILES['file']['size'] > $maxsize) {
    stderr($lang['bitbucket_error'], $lang['bitbucket_to_large']);
}
$file = preg_replace('`[^a-z0-9\-\_\.]`i', '', $_FILES['file']['name']);
$allow = ',' . join(',', $formats);
if (stristr($allow, ',' . substr($file, -4)) === false) {
    stderr($lang['bitbucket_err'], $lang['bitbucket_invalid']);
}
if (!function_exists('exif_imagetype')) {
    /**
     * @param $filename
     *
     * @return bool
     */
    function exif_imagetype($filename)
    {
        if ((list($width, $height, $type, $attr) = getimagesize($filename)) !== false) {
            return $type;
        }

        return false;
    }
}
$it1 = exif_imagetype($_FILES['file']['tmp_name']);
if ($it1 != IMAGETYPE_GIF && $it1 != IMAGETYPE_JPEG && $it1 != IMAGETYPE_PNG) {
    $HTMLOUT .= "
        <h1>{$lang['bitbucket_upfail']}{$lang['bitbucket_sorry']}</h1>";
    die();
}
$file = strtolower($file);
$randb = make_password();
$path = $bucketdir . $USERSALT . '_' . $randb . $file;
$pathlink = $bucketlink . $USERSALT . '_' . $randb . $file;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
    stderr($lang['bitbucket_error'], $bucketdir . '<br>' . $USERSALT . '<br>' . $randb . '<br>' . $path . '<br>file move ' . $lang['bitbucket_upfail']);
}
if (!file_exists($path)) {
    stderr($lang['bitbucket_error'], 'path not exists ' . $lang['bitbucket_upfail']);
}
if (isset($_POST['from']) && $_POST['from'] === 'upload') {
    echo "
        <div>
            {$lang['bitbucket_success']}
        </div>
        <div>
            {$site_config['baseurl']}/img.php?{$pathlink}
        </div>";
    die();
}
$HTMLOUT .= "
        <div class='bitbucket has-text-centered'>
            <div>
                <a href='{$_SERVER['PHP_SELF']}' class='button is-small bottom10'>{$lang['bitbucket_up_another']}</a>
            </div>
            <h3 class='has-text-lime'>{$lang['bitbucket_thefile']}</h3>
            <div>
                <img src='{$site_config['baseurl']}/img.php?{$pathlink}' width='50%' height='auto' alt='' />
            </div>
            <h3>{$lang['bitbucket_directlink']}</h3>
            <div class='bottom10'>
                <input id='direct' onclick=\"SelectAll('direct');\" type='text' size='70' value='{$site_config['baseurl']}/img.php?{$pathlink}' readonly='readonly' />
            </div>
            <h3>{$lang['bitbucket_tags']}</h3>
            <div class='bottom10'>
                <input id='tag' onclick=\"SelectAll('tag');\" type='text' size='70' value='[img]{$site_config['baseurl']}/img.php?{$pathlink}[/img]' readonly='readonly' />
            </div>
            <div>
                <ul class='level-center bg-06 padding10'>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=1'>{$lang['bitbucket_viewmyi']}</a>
                    </li>
                    <li>
                        <a href='{$site_config['baseurl']}/bitbucket.php?images=2'>{$lang['bitbucket_viewmya']}</a>
                    </li>
                </ul>
            </div>
            <script>
                function SelectAll(id) {
                    document.getElementById(id).focus();
                    document.getElementById(id).select();
                }
            </script>
        </div>";
echo stdhead($lang['bitbucket_bitbucket']) . $HTMLOUT . stdfoot();

/**
 * @param $text
 *
 * @return string
 */
function encrypt($text)
{
    global $PICSALT;

    $encrypted = CryptoJSAES::encrypt($text, $PICSALT);

    return base64_encode($encrypted);
}

/**
 * @param $text
 *
 * @return string
 */
function decrypt($text)
{
    global $PICSALT;

    $str = base64_decode($text);

    return CryptoJSAES::decrypt($str, $PICSALT);
}

/**
 * @param $root
 * @param $input
 *
 * @return bool|null|string
 */
function valid_path($root, $input)
{
    $fullpath = $root . $input;
    $fullpath = realpath($fullpath);
    $root = realpath($root);
    $rl = strlen($root);

    return ($root != substr($fullpath, 0, $rl)) ? null : $fullpath;
}

/**
 * @param $path
 */
function make_year($path)
{
    $dir = $path . '/' . date('Y');
    if (!is_dir($dir)) {
        mkdir($dir);
    }
}

/**
 * @param $path
 */
function make_month($path)
{
    $dir = $path . '/' . date('Y/m');
    if (!is_dir($dir)) {
        mkdir($dir);
    }
}
