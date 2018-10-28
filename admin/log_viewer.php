<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$HTMLOUT = '';
$count = 0;
$perpage = 25;

if (!empty($_GET['action']) && $_GET['action'] === 'view') {
    $file = $_GET['file'];
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $name = basename($file);
    $uncompress = $ext === 'gz' ? 'compress.zlib://' : '';

	if (file_exists($file)) {
        $content = file_get_contents($uncompress . $file);
    } else {
        $content = '<b>' . $file . '</b> does not exist';
    }

    $content = trim($content);
    if (!preg_match('/sqlerr_logs/i', $file) && !preg_match('/access\.log/', $name)) {
        preg_match_all('!(\d{4}/\d{2}/\d{2}\s+\d{2}:\d{2}:\d{2}.*?)!iU', $content, $matches);
        if (!empty($matches[1])) {
            $contents = $matches[1];
        } else {
            $contents = explode("\n", $content);
        }
    } elseif (preg_match('/access\.log/', $name)) {
        preg_match_all('!(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}.*?)!iU', $content, $matches);
        if (!empty($matches[1])) {
            $contents = $matches[1];
        } else {
            $contents = explode("\n", $content);
        }
    } else {
        $contents = explode('===================================================', $content);
    }
    if (!empty($contents)) {
		rsort($contents);
		$count = count($contents);
		$pager = pager($perpage, $count, "{$site_config['baseurl']}/staffpanel.php?tool=log_viewer&action=view&file=" . urlencode($file) . '&amp;');
	}
    $i = 0;
    $content = [];
    foreach ($contents as $line) {
        if (!empty($line)) {
			$i++;
			if ($i >= $pager['pdo'][0]) {
				$class = $i % 2 === 0 ? 'bg-01 simple_border round10 padding20 has-text-black bottom5' : 'bg-light simple_border round10 padding20 has-text-black bottom5';
				$line = trim($line);
				$content[] = "<div class='{$class}'>{$line}</div>";
			}
            if ($i >= $pager['pdo'][0] + $pager['pdo'][1]) {
                break;
            }
        }
    }
    $content = ($count > $perpage ? $pager['pagertop'] : '') . implode("\n", $content) . ($count > $perpage ? $pager['pagerbottom'] : '');

    $HTMLOUT = main_div("
        <div class='bg-00 round10'>
            <h1 class='has-text-centered'>Viewing Log: $file</h1>$content
        </div>", 'bottom20');
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
                <a href='{$_SERVER['PHP_SELF']}?tool=log_viewer&amp;action=view&amp;file=" . urlencode($file) . "'>$file</a>
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
