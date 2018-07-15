<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$HTMLOUT = '';
if (!empty($_GET['action']) && $_GET['action'] === 'edit') {
    $file = $_GET['file'];
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $uncompress = $ext === 'gz' ? 'compress.zlib://' : '';
    $maxsize = 1048576; // 1MB
    $offset = filesize($file) - $maxsize;
    $offset = $offset <= 0 ? 0 : $offset;

    if (file_exists($file) && filesize($file) >= $maxsize) {
        $content = file_get_contents($uncompress . $file, false, null, $offset, $maxsize);
    } elseif (file_exists($file)) {
        $content = file_get_contents($uncompress . $file);
    } else {
        $content = '[b]' . $file . '[/b] does not exist';
    }
    $content = trim($content);
    if (!preg_match('/sqlerr_logs/i', $file)) {
        $contents = explode("\n", $content);
    } else {
        $contents = explode('===================================================', $content);
    }
    rsort($contents);
    $i = 0;
    $content = [];
    foreach ($contents as $line) {
        if (!empty($line)) {
            $class = $i++ % 2 === 0 ? 'bg-08 simple_border round10 padding20 has-text-black' : 'bg-03 simple_border round10 padding20 has-text-black';
            $line = trim($line);
            $content[] = "[class={$class}]{$line}[/class]";
        }
    }

    $content = implode("\n", $content);
    $content = '[pre]' . $content . '[/pre]';

    $HTMLOUT = main_div("
        <div class='bg-00 round10'>
            <h1 class='has-text-centered'>Viewing Error Log: $file</h1>" . format_comment($content) . '
        </div>', 'bottom20');
}

$paths = [
    '/var/log/apache2',
    '/var/log/nginx/',
    SQLERROR_LOGS_DIR,
    ROOT_DIR . 'logs',
];

$files = [];
foreach ($paths as $path) {
    if (file_exists($path)) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        $exts = [
            'log',
            'gz',
        ];
        foreach ($objects as $name => $object) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if (in_array($ext, $exts)) {
                $files[] = $name;
            }
        }
    }
}

natsort($files);

if (!empty($files)) {
    $heading = '
        <tr>
            <th>Filename</th>
            <th>Date</th>
            <th>Size</th>
        </tr>';
    $body = '';
    foreach ($files as $file) {
        $body .= "
        <tr>
            <td>
                <a href='{$_SERVER['PHP_SELF']}?tool=log_viewer&action=edit&file=" . urlencode($file) . "'>$file</a>
            </td>
            <td>
                " . get_date(filemtime($file), 'LONG') . "
            </td>
            <td class='has-text-right'>
                " . human_filesize(filesize($file)) . '
            </td>
        </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);
} else {
    $HTMLOUT .= main_div('There are no log files to view');
}

echo stdhead('Log Files') . wrapper($HTMLOUT, 'is-paddingless') . stdfoot();
