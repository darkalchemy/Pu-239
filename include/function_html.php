<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use Intervention\Image\Image;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use PHPMailer\PHPMailer\PHPMailer;
use Pu239\Cache;
use Pu239\Database;
use Pu239\ImageProxy;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once INCL_DIR . 'function_categories.php';

/**
 * @return string
 */
function begin_main_frame()
{
    return "
            <table class='table table-bordered table-striped'>
                <tr>
                    <td class='embedded'>";
}

/**
 * @return string
 */
function end_main_frame()
{
    return '
                    </td>
                </tr>
            </table>';
}

/**
 * @param string $caption
 * @param bool   $center
 *
 * @return string
 */
function begin_frame($caption = '', $center = false)
{
    $tdextra = '';
    $htmlout = '';
    $center = $center ? " class='has-text-centered'" : '';
    if ($caption) {
        $htmlout .= "<h1{$center}>$caption</h1>";
    }
    $htmlout .= "<table class='shit table table-bordered table-striped'><tr><td$tdextra>\n";

    return $htmlout;
}

/**
 * @return string
 */
function end_frame()
{
    return "</td></tr></table>\n";
}

/**
 * @param bool $striped
 *
 * @return string
 */
function begin_table($striped = false)
{
    $htmlout = '';
    $stripe = $striped ? ' table-striped' : '';
    $htmlout .= "<table class='table table-bordered{$stripe}'>\n";

    return $htmlout;
}

/**
 * @return string
 */
function end_table()
{
    return "</table>\n";
}

/**
 * @param        $x
 * @param        $y
 * @param bool   $noesc
 * @param string $class
 *
 * @return string
 */
function tr($x, $y, $noesc = false, $class = '')
{
    if ($noesc) {
        $a = $y;
    } else {
        $a = htmlsafechars($y);
        $a = str_replace("\n", "<br>\n", $a);
    }

    $class = !empty($class) ? " class='$class'" : '';

    return "
        <tr>
            <td class='rowhead'>
                $x
            </td>
            <td{$class}>
                $a
            </td>
        </tr>";
}

/**
 * @return string
 */
function insert_smilies_frame()
{
    global $smilies, $site_config;
    $htmlout = '';
    $htmlout .= begin_frame('Smilies', true);
    $htmlout .= begin_table(false);
    $htmlout .= "<tr><td class='colhead'>Type...</td><td class='colhead'>To make a...</td></tr>\n";
    foreach ($smilies as $code => $url) {
        $htmlout .= "<tr><td>$code</td><td><img src=\"{$site_config['paths']['images_baseurl']}smilies/{$url}\" alt=''></td></tr>\n";
    }
    $htmlout .= end_table();
    $htmlout .= end_frame();

    return $htmlout;
}

/**
 * @param string      $body
 * @param string|null $header
 * @param string|null $class
 * @param string|null $wrapper_class
 * @param string|null $striped
 * @param string|null $id
 * @param bool|null   $wrapper
 *
 * @return string
 */
function main_table(string $body, ?string $header = null, ?string $class = null, ?string $wrapper_class = null, ?string $striped = 'table-striped', ?string $id = null, ?bool $wrapper = true)
{
    $id = !empty($id) ? " id='$id'" : '';
    $thead = $header != null ? "
                        <thead>
                            $header
                        </thead>" : '';
    $table = "
                    <table{$id} class='table table-bordered $striped $class'>
                        $thead
                        <tbody>
                            $body
                        </tbody>
                    </table>";
    if ($wrapper) {
        return table_wrapper($table, $wrapper_class);
    }

    return $table;
}

/**
 * @param string      $table
 * @param string|null $wrapper_class
 *
 * @return string
 */
function table_wrapper(string $table, ?string $wrapper_class = null)
{
    return "
                <div class='table-wrapper $wrapper_class'>
                    $table
                </div>";
}

/**
 * @param      $text
 * @param null $outer_class
 * @param null $inner_class
 *
 * @return string|void
 */
function main_div($text, $outer_class = null, $inner_class = null)
{
    if ($text === '') {
        return;
    } else {
        return "
                <div class='bordered bg-02 $outer_class'>
                    <div class='alt_bordered bg-00 $inner_class'>$text
                    </div>
                </div>";
    }
}

/**
 * @param        $text
 * @param string $class
 *
 * @return string|void
 */
function wrapper($text, $class = '')
{
    if ($text === '') {
        return;
    } else {
        return "
            <div class='portlet $class'>
                $text
            </div>";
    }
}

/**
 * @param $data
 * @param $template
 */
function write_css($data, $template)
{
    $classdata = '';
    foreach ($data as $class) {
        $cname = strtolower(str_replace('UC_', '', $class['name']));
        $ccolor = strtolower($class['classColor']);
        if (!empty($cname)) {
            $classdata .= ".{$cname} {
    color: $ccolor;
}
";
        }
    }
    $classdata .= '#content .chatbot {
    color: #ff8b49;
    text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;
}
';
    foreach ($data as $class) {
        $cname = strtolower(str_replace('UC_', '', $class['name']));
        if (!empty($cname)) {
            $classdata .= "#content #chatList span.{$cname} {
    font-weight: bold;
}
";
        }
    }
    $classdata .= '#content #chatList span.chatbot {
    font-weight: bold;
    font-style: italic;
}
';
    foreach ($data as $class) {
        $cname = strtolower(str_replace('UC_', '', $class['name']));
        $ccolor = strtolower($class['classColor']);
        if (!empty($cname)) {
            $classdata .= ".{$cname}_bk {
    background-color: $ccolor;
}
";
        }
    }
    if (file_exists(ROOT_DIR . "chat/css/{$template}")) {
        file_put_contents(ROOT_DIR . "chat/css/{$template}/classcolors.css", $classdata . PHP_EOL);
    }
    if (file_exists(ROOT_DIR . "templates/{$template}/css")) {
        file_put_contents(ROOT_DIR . "templates/{$template}/css/classcolors.css", $classdata . PHP_EOL);
    }
}

/**
 * @param $data
 * @param $classes
 */
function write_classes($data, $classes)
{
    $html = file_get_contents(CHAT_DIR . 'js/config.js');
    $classes = "bbCodeTags: [\n        'b',\n        'i',\n        'u',\n        'quote',\n        'code',\n        'color',\n        'url',\n        'img',\n        'chatbot',\n        'center',\n        'updown',\n        'video',\n        'size_7',\n        'size_6',\n        'size_5',\n        'size_4',\n        'size_3',\n        'size_2',\n        'size_1',\n        '" . implode("',\n        '", $classes) . "'\n    ],";
    $html = preg_replace('/(bbCodeTags:\s+\[.*?\],)/s', $classes, $html);
    file_put_contents(CHAT_DIR . 'js/config.js', $html);

    $text = '

ajaxChat.getRoleClass = function(roleID) {
    switch (parseInt(roleID)) {';
    foreach ($data as $class) {
        $text .= "
        case parseInt($class):
            return '" . strtolower(str_replace('UC_', '', $class)) . "';";
    }
    $text .= "
        case parseInt(ajaxChat.chatBotRole):
            return 'chatbot';
        default:
            return 'user';
    }
};";

    file_put_contents(ROOT_DIR . 'chat/js/classes.js', $text, FILE_APPEND);
}

/**
 * @param $template
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function write_class_files($template)
{
    global $container;

    $fluent = $container->get(Database::class);
    $classes = $js_classes = $config_classes = $data = [];
    $t = 'define(';
    $configfile = "<?php\n\ndeclare(strict_types = 1);\n\n";
    $res = $fluent->from('class_config')
                  ->orderBy('value')
                  ->where('template = ?', $template);
    foreach ($res as $arr) {
        $configfile .= $t . "'{$arr['name']}', {$arr['value']});\n";
        if ($arr['name'] !== 'UC_STAFF' && $arr['name'] !== 'UC_MIN' && $arr['name'] !== 'UC_MAX') {
            $js_classes[] = $arr['name'];
            $config_classes[] = strtolower(str_replace('UC_', '', $arr['name']));
            $data[] = [
                'name' => $arr['name'],
                'className' => $arr['classname'],
                'classColor' => '#' . $arr['classcolor'],
            ];
        }
        $classes[] = "var {$arr['name']} = {$arr['value']};";
    }
    file_put_contents(ROOT_DIR . 'chat/js/classes.js', implode("\n", $classes) . PHP_EOL);
    write_classes($js_classes, $config_classes);
    write_css($data, $template);
    file_put_contents(CONFIG_DIR . 'classes.php', $configfile);
}

/**
 * @throws DependencyException
 * @throws NotFoundException
 */
function clear_image_cache()
{
    global $container;

    $cache = $container->get(Cache::class);
    $cache->deleteMulti([
        'latest_torrents_',
        'top_torrents_',
        'scroller_torrents_',
        'slider_torrents_',
        'torrent_poster_count_',
        'torrent_banner_count_',
        'backgrounds_',
        'staff_picks_',
        'motw_',
    ]);
}

/**
 *
 * @param int    $width
 * @param int    $height
 * @param string $color
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool|Image|mixed|string
 */
function placeholder_image(int $width = 10, int $height = 10, string $color = '#7d7e7d')
{
    global $container;

    $cache = $container->get(Cache::class);
    $image = $cache->get('placeholder_image_' . $width . '_' . $height . '_' . $color);
    if ($image === false || is_null($image)) {
        $image_proxy = new ImageProxy();
        $image = $image_proxy->create_image($width, $height, $color);
        $image = 'data:image/jpeg;charset=utf-8;base64,' . base64_encode((string) $image);
        $cache->set('placeholder_image_' . $width . '_' . $height . '_' . $color, $image, 0);
    }

    return $image;
}

/**
 * @param $url
 *
 * @return mixed|string|null
 */
function validate_url($url)
{
    $url = filter_var(strip_tags($url), FILTER_SANITIZE_URL);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }
    if (preg_match("/^https?:\/\/$/i", $url) || preg_match('/[&;]/', $url) || preg_match('#javascript:#is', $url) || !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $url)) {
        return null;
    }

    return $url;
}

/**
 * @param string $title
 * @param bool   $hidden
 *
 * @return string
 */
function doc_head(string $title, bool $hidden = true)
{
    global $site_config;

    return "<!doctype html>
<html lang='en-US'>
<head>" . ($hidden ? '
    <style>html{visibility: hidden;opacity:0;}</style>' : '') . "
    <meta property='og:title' content='{$title}'>
    <title>{$title}</title>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta property='og:url' content='{$site_config['paths']['baseurl']}'>
    <meta property='og:type' content='website'>
    <meta property='og:description' content='{$site_config['session']['domain']} - {$site_config['site']['name']}'>";
}

/**
 * @param $email
 * @param $subject
 * @param $html
 * @param $plain
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws AuthError
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 * @throws \PHPMailer\PHPMailer\Exception
 * @throws InvalidManipulation
 *
 * @return bool
 */
function send_mail($email, $subject, $html, $plain)
{
    global $container, $site_config;

    if (!$site_config['mail']['smtp_enable']) {
        return false;
    }
    $mail = $container->get(PHPMailer::class);
    if ($mail) {
        $mail->setFrom("{$site_config['site']['email']}", "{$site_config['chatbot']['name']}");
        $mail->addAddress($email);
        $mail->addReplyTo($site_config['site']['email']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $plain;
        try {
            $mail->send();

            return true;
        } catch (Exception $e) {
            stderr('PHPMailer Error', $e->getMessage());
        }
    }

    return false;
}

/**
 *
 * @param int    $id
 * @param string $code
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return mixed
 */
function validate_invite(int $id, string $code)
{
    global $container;

    $fluent = $container->get(Database::class);
    $email = $fluent->from('invite_codes')
                    ->select(null)
                    ->select('email')
                    ->where('id = ?', $id)
                    ->where('code = ?', $code)
                    ->where('status = "Pending"')
                    ->fetch('email');

    return $email;
}

/**
 *
 * @param string $code
 * @param bool   $full
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return mixed
 */
function validate_promo(string $code, bool $full)
{
    global $container;

    $fluent = $container->get(Database::class);
    $valid = $fluent->from('promo')
                    ->where('link = ?', htmlsafechars($code))
                    ->where('UNIX_TIMESTAMP(NOW()) < added + (days_valid * 86400)')
                    ->where('accounts_made < max_users')
                    ->fetch();

    if (!empty($valid)) {
        if ($full) {
            return $valid;
        }

        return $valid['link'];
    }

    return $valid;
}

/**
 *
 * @param array $classes
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return string
 */
function category_dropdown(array $classes = [])
{
    global $post_data;

    $cats = genrelist(true);
    $s = "
            <select id='upload_category' name='type' class='w-100' required>
                <option value='' disabled selected>" . _('Choose One') . '</option>';
    foreach ($cats as $cat) {
        foreach ($cat['children'] as $row) {
            if (empty($classes) || in_array($row['id'], $classes)) {
                $s .= "
                <option value='{$row['id']}' " . (!empty($post_data['category']) && $post_data['category'] === $row['id'] ? 'selected' : '') . '>' . htmlsafechars($cat['name']) . '::' . htmlsafechars($row['name']) . '</option>';
            }
        }
    }
    $s .= '
            </select>';

    return $s;
}
