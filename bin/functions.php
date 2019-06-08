<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return array
 */
function get_styles()
{
    global $container;

    $fluent = $container->get(Database::class);
    $query = $fluent->from('stylesheets')
                    ->select(null)
                    ->select('id')
                    ->select('uri');

    $styles = [];
    foreach ($query as $style) {
        $styles[] = $style['id'];
    }

    return $styles;
}

/**
 * @param array $styles
 * @param bool  $create
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return array
 */
function get_classes(array $styles, bool $create)
{
    global $container;

    $fluent = $container->get(Database::class);
    $all_classes = [];
    foreach ($styles as $style) {
        $classes = $fluent->from('class_config')
                          ->select(null)
                          ->select('name')
                          ->select('value')
                          ->select('classname')
                          ->select('classcolor')
                          ->select('classpic')
                          ->orderBy('value')
                          ->where('template = ?', $style)
                          ->fetchAll();

        if (empty($classes)) {
            if (!$create) {
                die("You do have not classes for template {$style}\n\nto create them rerun this script\nphp bin/uglify.php classes\n");
            } else {
                foreach ($all_classes[0] as $values) {
                    $values['template'] = $style;
                    $fluent->insertInto('class_config')
                           ->values($values)
                           ->execute();
                }
                die("Classes added for template {$style}\n");
            }
        }
        $all_classes[] = $classes;
    }

    return $all_classes;
}

/**
 * @param $group
 */
function cleanup($group)
{
    global $site_config;

    if (file_exists($site_config['files']['path'])) {
        chmod_r($site_config['files']['path'], $group);
        chgrp_r($site_config['files']['path'], $group);
    }
}

/**
 * @param $path
 * @param $group
 */
function chgrp_r($path, $group)
{
    if (!file_exists($path)) {
        return;
    }
    $dir = new DirectoryIterator($path);
    chgrp($path, $group);
    foreach ($dir as $item) {
        chgrp($item->getPathname(), $group);
        if ($item->isDir() && !$item->isDot()) {
            chgrp_r($item->getPathname(), $group);
        }
    }
}

/**
 * @param $path
 * @param $group
 */
function chmod_r($path, $group)
{
    if (!file_exists($path)) {
        return;
    }
    $dir = new DirectoryIterator($path);
    foreach ($dir as $item) {
        chmod($item->getPathname(), 0775);
        chown($item->getPathname(), $group);
        if ($item->isDir() && !$item->isDot()) {
            chmod_r($item->getPathname(), $group);
        }
    }
}

/**
 * @return string
 */
function get_webserver_user()
{
    if (php_sapi_name() == 'cli') {
        $group = trim(`ps -ef | egrep '(httpd|apache2|apache|nginx)' | grep -v \`whoami\` | grep -v root | head -n1 | awk '{print $1}'`);
    } else {
        $group = posix_getpwuid(posix_geteuid());
        $group = $group['name'];
    }
    if (empty($group)) {
        $group = 'www-data';
    }

    return $group;
}

/**
 * @return mixed|null
 */
function get_username()
{
    if (php_sapi_name() == 'cli') {
        $user = null;
        $commands = [
            trim(`logname`),
            trim(`who | awk '{print $1}'`),
            trim(exec('echo $SUDO_USER')),
        ];
        $i = 0;
        while (empty($user)) {
            $user = $commands[$i];
            ++$i;
        }

        return $user;
    }

    return get_webserver_user();
}
