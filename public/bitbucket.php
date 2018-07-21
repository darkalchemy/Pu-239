<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'dragndrop.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache, $session;

$lang = array_merge(load_language('global'), load_language('bitbucket'));

if (!$site_config['bucket_allowed']) {
    $session->set('is-warning', 'BitBucket has been disabled');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}

$SaLt = $site_config['site']['salt'];
$SaLty = $site_config['site']['salty'];
$skey = $site_config['site']['skey'];
$maxsize = $site_config['bucket_maxsize'];
$folders = date('Y/m');
$formats = $site_config['allowed_formats'];
$str = implode('|', $formats);
$str = str_replace('.', '', $str);

$bucketdir = BITBUCKET_DIR . $folders . '/';
$bucketlink = $folders . '/';
$PICSALT = $SaLt . $CURUSER['username'];
$USERSALT = substr(md5($SaLty . $CURUSER['id']), 0, 6);
make_year(BITBUCKET_DIR);
make_month(BITBUCKET_DIR);

$stdfoot = [
    'js' => [
        get_file_name('dragndrop_js'),
    ],
];

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
        $session->set('is-success', $lang['bitbucket_deleting'] . $delfile);
    } else {
        $session->set('is-danger', $lang['bitbucket_imagenf']);
    }
}

if (!empty($_GET['avatar']) && $_GET['avatar'] != $CURUSER['avatar']) {
    $type = isset($_GET['type']) && $_GET['type'] == 1 ? 1 : 2;
    $set = ['avatar' => trim($_GET['avatar'])];
    $fluent->update('users')
        ->set($set)
        ->where('id = ?', $CURUSER['id'])
        ->execute();

    $cache->update_row('user' . $CURUSER['id'], [
        'avatar' => $_GET['avatar'],
    ], $site_config['expires']['user_cache']);
    header("Location: {$site_config['baseurl']}/bitbucket.php?images=$type&updated=avatar");
} elseif (!empty($_GET['avatar']) && $_GET['avatar'] === $CURUSER['avatar']) {
    $session->set('is-warning', 'This is already your avatar!');
}

if (!empty($_GET['updated']) && $_GET['updated'] === 'avatar') {
    $session->set('is-info', "
        [h3]{$lang['bitbucket_updated']}[/h3]
        [class=mw-150 has-text-centered]
            [img]" . url_proxy($CURUSER['avatar'], true, 150, null) . "[/img]
        [/class]");
}

$htmlout = "
    <div>
        <ul class='level-center bg-06 padding10'>
            <li>" . (empty($_GET['images']) ? "
                <a href='{$site_config['baseurl']}/bitbucket.php?images=1'>{$lang['bitbucket_viewmonths']}</a>" : "
                <a href='{$site_config['baseurl']}/bitbucket.php'>{$lang['bitbucket_hideimgs']}</a>") . "
            </li>
        </ul>
    </div>";

$htmlout .= "
    <h1>BitBucket Image Uploader</h1>
    <p class='bottom20'>{$lang['bitbucket_disclaimer']}</p>";

$htmlout .= main_div("
    <div id='droppable' class='droppable bg-03'>
        <span id='comment'>{$lang['bitbucket_dragndrop']}</span>
        <div id='loader' class='is-hidden'>
            <img src='{$site_config['pic_baseurl']}forums/updating.svg' alt='Loading...' />
        </div>
    </div>");

$htmlout .= main_div("
    <div class='output'></div>", 'output-wrapper is-hidden');


$folder_month = empty($_GET['month']) ? date('m') : ($_GET['month'] < 10 ? '0' : '') . (int) $_GET['month'];

if (isset($_GET['images']) && $_GET['images'] == 1) {
    $year = !isset($_GET['year']) ? '&amp;year=' . date('Y') : '&amp;year=' . (int) $_GET['year'];
    $htmlout .= "
            <div class='top20'>
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

}

if (isset($_GET['images'])) {
    $folder_name = (!isset($_GET['year']) ? date('Y') . '/' : (int) $_GET['year'] . '/') . $folder_month;
    $bucketlink2 = ((isset($_POST['avy']) || (isset($_GET['images']) && $_GET['images'] == 2)) ? 'avatar/' : $folder_name . '/');
    $files = glob(BITBUCKET_DIR . $folder_name . '/' . $USERSALT . '_*');
    if (!empty($files)) {
        foreach ($files as $filename) {
            $filename = basename($filename);
            $filename = $bucketlink2 . $filename;
            $encryptedfilename = urlencode(encrypt($filename));
            $eid = md5($filename);
            $htmlout .= main_div("
            <div class='padding20 margin20 round10 bg-00'>
                <div class='margin20'>
                    <a href='{$site_config['baseurl']}/img.php?{$filename}' data-lightbox='bitbucket' />
                        <img src='{$site_config['baseurl']}/img.php?{$filename}' class='w-50 img-responsive' alt='' />
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
                    <ul class='level-center margin10'>
                        <li>
                            <a href='{$site_config['baseurl']}/bitbucket.php?type=" . ((isset($_GET['images']) && $_GET['images'] == 2) ? '2' : '1') . "&amp;avatar={$site_config['baseurl']}/img.php?{$filename}' class='button is-small'>{$lang['bitbucket_maketma']}</a>
                        </li>
                        <li>
                            <a href='{$site_config['baseurl']}/bitbucket.php?images=1&type=" . ((isset($_GET['images']) && $_GET['images'] == 2) ? '2' : '1') . '&amp;delete=' . $encryptedfilename . '&amp;delhash=' . md5($filename . $USERSALT . $SaLt) . '&amp;month=' . (!isset($_GET['month']) ? date('m') : ($_GET['month'] < 10 ? '0' : '') . (int) $_GET['month']) . '&amp;year=' . (!isset($_GET['year']) ? date('Y') : (int) $_GET['year']) . "' class='button is-small's>{$lang['bitbucket_delete']}</a>
                        </li>
                    </ul>
                </div>
            </div>", 'top20');
        }
    } else {
        $htmlout .= main_div("
                {$lang['bitbucket_noimages']}", 'top20');
    }

}

echo stdhead('Bitbucket Image Uploader') . wrapper($htmlout, 'has-text-centered') . stdfoot($stdfoot);
