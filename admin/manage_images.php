<?php

declare(strict_types = 1);

use Pu239\Image;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config;

$perpage = 25;
$image = $container->get(Image::class);
$session = $container->get(Session::class);
$terms = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete']) && $_POST['delete'] === 'Delete') {
    foreach ($_POST['images'] as $url) {
        $item = $image->get_image($url);
        if (!empty($item)) {
            $hashes = [
                hash('sha256', $item['url'] . '_converted_' . 20),
                hash('sha256', $item['url'] . '_450'),
                hash('sha256', $item['url'] . '_250'),
                hash('sha256', $item['url'] . '_150'),
                hash('sha256', $item['url']),
            ];
            foreach ($hashes as $hash) {
                $file = PROXY_IMAGES_DIR . $hash;
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            $image->delete_image($item['url']);
            $session->set('is-success', $item['url'] . ' was deleted.');
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['terms'])) {
    $terms = strip_tags($_POST['terms']);
    $search = '&amp;search=' . urlencode($terms);
    $count = (int) $image->count_search_images($terms);
    $pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?tool=manage_images{$search}&amp;");
    $images = $image->search_images($terms, $pager['pdo']['limit'], $pager['pdo']['offset']);
} else {
    $terms = !empty($_GET['search']) ? strip_tags($_GET['search']) : '';
    $search = !empty($_GET['search']) ? '&amp;search=' . urlencode($terms) : '';
    if (empty($terms)) {
        $count = $image->get_image_count();
    } else {
        $count = (int) $image->count_search_images($terms);
    }
    $pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?tool=manage_images{$search}&amp;");
    if (empty($terms)) {
        $images = $image->get_images($pager['pdo']['limit'], $pager['pdo']['offset']);
    } else {
        $images = $image->search_images($terms, $pager['pdo']['limit'], $pager['pdo']['offset']);
    }
}
if (!empty($images)) {
    $heading = "
        <tr>
            <th>Preview</th>
            <th class='has-text-centered'>Type</th>
            <th class='has-text-centered'>IMDb</th>
            <th class='has-text-centered'>TMDb</th>
            <th class='has-text-centered'>TvMaze ID</th>
            <th class='has-text-centered'>ISBN</th>
            <th class='has-text-centered'>Lang</th>
            <th class='has-text-centeredtooltipper' title='If image has been fetched and is in your filesystem'>Fetched</th>
            <th class='has-text-centered tooltipper' title='If IMDb or TMDb not empty, when it was updated'>Updated</th>
            <th class='has-text-centered tooltipper' title='If IMDb or TMDb is empty, the last time we looked it up'>Checked</th>
            <th class='has-text-centered tooltipper' title='Select All'><input type='checkbox' id='checkThemAll'></th>
            <th class='has-text-centered tooltipper' title='Ignore'>Ignore</th>
        </tr>";
    $body = '';
    foreach ($images as $image) {
        $hash = hash('sha256', $image['url']);
        $dims = getimagesize(PROXY_IMAGES_DIR . $hash);
        $size = mksize(filesize(PROXY_IMAGES_DIR . $hash));
        $body .= "
        <tr>
            <td class='has-text-centered'>
                <a href='{$image['url']}' class='tooltipper' title='<span class=\"has-text-success\">Hash: </span>{$hash}<br><span class=\"has-text-success\">Size: </span>{$size}<br><span class=\"has-text-success\">Dims: </span>{$dims[0]}x{$dims[1]}'>
                    <img src='" . url_proxy($image['url'], true, 250) . "' alt='Poster' class='img-responsive'>
                </a>
            </td>
            <td class='has-text-centered'>{$image['type']}</td>
            <td class='has-text-centered'>{$image['imdb_id']}</td>
            <td class='has-text-centered'>{$image['tmdb_id']}</td>
            <td class='has-text-centered'>{$image['tvmaze_id']}</td>
            <td class='has-text-centered'>{$image['isbn']}</td>
            <td class='has-text-centered w-10'><input type='text' value='{$image['lang']}' class='w-100'></td>
            <td class='has-text-centered'>{$image['fetched']}</td>
            <td class='has-text-centered'>
                " . get_date((int) $image['updated'], 'LONG') . "
            </td>
            <td class='has-text-centered'>
                " . get_date((int) $image['checked'], 'LONG') . "
            </td>
            <td class='has-text-centered w-10'>
                <input type='checkbox' name='images[]' value='{$image['url']}'>
            </td>
            <td class='has-text-centered w-10'>
                <div data-id='{$image['url']}' data-pick='{$image['ignore']}' class='ignore-image tooltipper button is-small' title='" . ($image['ignore'] === 1 ? 'Image is Ignored and will not be displayed' : 'Image is NOT Ignored and will be displayed') . "'>" . ($image['ignore'] === 1 ? 'Ignored' : 'Ignore') . '</div>
            </td>
        </tr>';
    }
    $HTMLOUT .= "
        <h1 class='has-text-centered'>Manage Images</h1>" . ($count > $perpage ? $pager['pagertop'] : '') . "
        <form action='{$_SERVER['PHP_SELF']}?tool=manage_images' method='post' name='terms' enctype='multipart/form-data' accept-charset='utf-8'>
            <div class='has-text-centered margin20 tooltipper' title='Search by IMDb, TMDb, TvMaze ID, ISBN, type'>
                <input type='text' name='terms' value='$terms'>
                <input type='submit' class='button is-small' name='search' value='Search'>
            </div>
        <form>
        <form action='{$_SERVER['PHP_SELF']}?tool=manage_images' method='post' name='checkme' enctype='multipart/form-data' accept-charset='utf-8'>" . main_table($body, $heading) . "
            <div class='has-text-centered margin20'>
                <input type='submit' class='button is-small' name='delete' value='Delete'>
            </div>
        <form>" . ($count > $perpage ? $pager['pagerbottom'] : '');
} else {
    $HTMLOUT .= main_div('There are no images to view', '', 'padding20');
}

echo stdhead('Manage Images') . wrapper($HTMLOUT, 'is-paddingless') . stdfoot();
