<?php

declare(strict_types = 1);

use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bitbucket.php';
$user = check_user_status();
global $container, $site_config;

$session = $container->get(Session::class);
if (!$site_config['bucket']['allowed']) {
    $session->set('is-warning', _('BitBucket has been disabled'));
    header("Location: {$site_config['paths']['baseurl']}/index.php");
    die();
}

$SaLt = $site_config['salt']['one'];
$SaLty = $site_config['salt']['two'];
$skey = $site_config['salt']['three'];
$maxsize = $site_config['bucket']['maxsize'];
$folders = date('Y/m');
$formats = $site_config['images']['formats'];
$str = implode('|', $formats);
$bucketdir = BITBUCKET_DIR . $folders . DIRECTORY_SEPARATOR;
$bucketlink = $folders . DIRECTORY_SEPARATOR;
$PICSALT = $SaLt . $user['username'];
$USERSALT = substr(md5($SaLty . $user['id']), 0, 6);
make_year(BITBUCKET_DIR);
make_month(BITBUCKET_DIR);

$stdfoot = [
    'js' => [
        get_file_name('dragndrop_js'),
    ],
];

if (isset($_GET['delete'])) {
    $getfile = htmlsafechars($_GET['delete']);
    $delfile = urldecode(decrypt($getfile, $PICSALT));
    $delhash = md5($delfile . $USERSALT . $SaLt);
    if ($delhash != $_GET['delhash']) {
        stderr(_('Umm'), _('what are you doing?'));
    }
    $myfile = BITBUCKET_DIR . $delfile;
    if ((($pi = pathinfo($myfile)) && preg_match('#^(' . $str . ')$#i', $pi['extension'])) && is_file($myfile)) {
        unlink($myfile);
        $session->set('is-success', _('Deleted Image ') . $delfile);
    } else {
        $session->set('is-danger', _('Image not found!'));
    }
}

if (!empty($_GET['avatar']) && $_GET['avatar'] != $user['avatar']) {
    $type = isset($_GET['type']) && $_GET['type'] == 1 ? 1 : 2;
    $update = ['avatar' => trim(strip_tags($_GET['avatar']))];
    $users_class = $container->get(User::class);
    $users_class->update($update, $user['id']);
    header("Location: {$site_config['paths']['baseurl']}/bitbucket.php?images=$type&updated=avatar");
} elseif (!empty($_GET['avatar']) && $_GET['avatar'] === $user['avatar']) {
    $session->set('is-warning', _('This is already your avatar!'));
}

if (!empty($_GET['updated']) && $_GET['updated'] === 'avatar') {
    $session->set('is-info', '
        [class=has-text-centered]
            [h3]' . _('Updated avatar to') . '[/h3]
            [img width=150]' . url_proxy($user['avatar'], true, 150) . '[/img]
        [/class]');
}

$htmlout = "
    <div>
        <ul class='level-center bg-06 padding10'>
            <li>" . (empty($_GET['images']) ? "
                <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1'>" . _('View My Images') . '</a>' : "
                <a href='{$site_config['paths']['baseurl']}/bitbucket.php'>" . _('Hide My Images') . '</a>') . '
            </li>
        </ul>
    </div>';

$htmlout .= '
    <h1>' . _('BitBucket Image Host') . "</h1>
    <p class='has-text-centered margin20'>" . _("<b>Disclaimer:</b> Do not upload unauthorized or illegal pictures. Uploaded pictures should be considered 'Public Domain'. Do not upload pictures you wouldn't want a stranger to have access to.") . '</p>';
$htmlout .= main_div("
        <div class='padding20'>
            <h2>" . _('Upload from URL') . "</h2>
            <input type='url' id='image_url' placeholder='" . _('External Image URL') . "' class='w-100 top20 bottom20'>
            <span class='button is-small' onclick=\"return grab_url(event)\">" . _('Upload') . '</span>
        </div>', 'bottom20');

$htmlout .= main_div("
    <div id='droppable' class='droppable bg-03'>
        <span id='comment'>" . _('Drop images here or click here to select images.') . "</span>
        <div id='loader' class='is-hidden'>
            <img src='{$site_config['paths']['images_baseurl']}forums/updating.svg' alt='" . _('Loading...') . "'>
        </div>
    </div>");

$htmlout .= main_div("
    <div class='output'></div>", 'output-wrapper is-hidden');

$folder_month = empty($_GET['month']) ? date('m') : ($_GET['month'] < 10 ? 0 : '') . (int) $_GET['month'];

if (isset($_GET['images']) && $_GET['images'] == 1) {
    $year = !isset($_GET['year']) ? '&amp;year=' . date('Y') : '&amp;year=' . (int) $_GET['year'];
    $htmlout .= "
            <div class='top20'>
                <h2>" . _('Previous Months Images') . "</h2>
                <ul class='level-center bg-06 padding10'>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month={$folder_month}&amp;year=" . (isset($_GET['year']) && $_GET['year'] != date('Y') ? date('Y') . "'>" . _('This Year') : (date('Y') - 1) . "'>" . _('Last Year')) . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=01{$year}'>" . _('January') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=02{$year}'>" . _('February') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=03{$year}'>" . _('March') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=04{$year}'>" . _('April') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=05{$year}'>" . _('May') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=06{$year}'>" . _('June') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=07{$year}'>" . _('July') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=08{$year}'>" . _('August') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=09{$year}'>" . _('September') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=10{$year}'>" . _('October') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=11{$year}'>" . _('November') . "</a>
                    </li>
                    <li>
                        <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&amp;month=12{$year}'>" . _('December') . '</a>
                    </li>
                </ul>
            </div>';
}

if (isset($_GET['images'])) {
    $folder_name = (!isset($_GET['year']) ? date('Y') . DIRECTORY_SEPARATOR : (int) $_GET['year'] . DIRECTORY_SEPARATOR) . $folder_month;
    $bucketlink2 = ((isset($_POST['avy']) || (isset($_GET['images']) && $_GET['images'] == 2)) ? 'avatar/' : $folder_name . DIRECTORY_SEPARATOR);
    $files = glob(BITBUCKET_DIR . $folder_name . DIRECTORY_SEPARATOR . $USERSALT . '_*');
    if (!empty($files)) {
        foreach ($files as $filename) {
            $filename = basename($filename);
            $filename = $bucketlink2 . $filename;
            $encryptedfilename = urlencode(encrypt($filename, $PICSALT));
            $eid = md5($filename);
            $htmlout .= main_div("
            <div class='padding20 round10 bg-00'>
                <div class='margin20'>
                    <a href='{$site_config['paths']['baseurl']}/img.php?{$filename}' data-lightbox='bitbucket'>
                        <img src='{$site_config['paths']['baseurl']}/img.php?{$filename}' class='w-50 img-responsive' alt=''>
                    </a>
                </div>
                <h2 class='has-text-centered padding20'>" . _('You can use width and/or height as shown in the second link. You can use auto for one or the other.') . '</h2>
                <h3>' . _('Direct link to image') . "</h3>
                <div class='bottom10'>
                    <input id='d{$eid}d' onclick=\"SelectAll('d{$eid}d');\" type='text' class='w-75' value='{$site_config['paths']['baseurl']}/img.php?{$filename}' readonly>
                </div>
                <h3 class='top20'>" . _('Tag for forums or comments') . "</h3>
                <div class='bottom10'>
                    <input id='t{$eid}t' onclick=\"SelectAll('t{$eid}t');\" type='text' class='w-75' value='[img width=250 height=auto]{$site_config['paths']['baseurl']}/img.php?{$filename}[/img]' readonly>
                </div>
                <div>
                    <ul class='level-center margin10'>
                        <li>
                            <a href='{$site_config['paths']['baseurl']}/bitbucket.php?type=" . ((isset($_GET['images']) && $_GET['images'] == 2) ? '2' : '1') . "&amp;avatar={$site_config['paths']['baseurl']}/img.php?{$filename}' class='button is-small'>" . _('Make this my Avatar!') . "</a>
                        </li>
                        <li>
                            <a href='{$site_config['paths']['baseurl']}/bitbucket.php?images=1&type=" . ((isset($_GET['images']) && $_GET['images'] == 2) ? '2' : '1') . '&amp;delete=' . $encryptedfilename . '&amp;delhash=' . md5($filename . $USERSALT . $SaLt) . '&amp;month=' . (!isset($_GET['month']) ? date('m') : ($_GET['month'] < 10 ? 0 : '') . (int) $_GET['month']) . '&amp;year=' . (!isset($_GET['year']) ? date('Y') : (int) $_GET['year']) . "' class='button is-small'>" . _('Delete this image') . '</a>
                        </li>
                    </ul>
                </div>
            </div>', 'top20');
        }
    } else {
        $htmlout .= main_div("
                <div class='padding20'>" . _('No Images Found') . '</div>', 'top20');
    }
}

$title = _('Bitbucket Image Host');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout, 'has-text-centered') . stdfoot($stdfoot);
