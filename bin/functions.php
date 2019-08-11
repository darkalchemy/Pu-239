<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
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
 * @return string
 */
function get_webserver_user()
{
    global $site_config;

    if (php_sapi_name() === 'cli') {
        $group = shell_exec("ps -ef | egrep '(httpd|apache2|apache|nginx)' | grep -v \`whoami\` | grep -v root | head -n1 | awk '{print $1}'");
    } else {
        $group = posix_getpwuid(posix_geteuid());
        $group = $group['name'];
    }
    if (empty($group)) {
        return $site_config['webserver']['username'];
    } else {
        return trim($group);
    }
}

/**
 * @return mixed|string|null
 */
function get_username()
{
    if (php_sapi_name() === 'cli') {
        $user = null;
        $commands = [
            `logname`,
            `who | awk '{print $1}'`,
            exec('echo $SUDO_USER'),
        ];
        $i = 0;
        while (empty($user)) {
            $user = $commands[$i];
            if (!empty($user)) {
                $user = trim($user);
            }
            ++$i;
        }
        if (!empty($user)) {
            return $user;
        }
    }

    return get_webserver_user();
}

/**
 * @param string $group
 */
function cleanup(string $group)
{
    global $site_config;

    if (file_exists($site_config['files']['path'])) {
        if (php_sapi_name() === 'cli') {
            passthru("sudo chown -R $group:$group " . $site_config['files']['path']);
        } else {
            try {
                chown($site_config['files']['path'], $group);
                chgrp($site_config['files']['path'], $group);
            } catch (Exception $exception) {
                // TODO logger
            }
        }
    }
    if (php_sapi_name() === 'cli') {
        if (file_exists(DI_CACHE_DIR)) {
            passthru("sudo chown -R $group:$group " . DI_CACHE_DIR);
        }
    }
}

/**
 * @param bool $before
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return int
 */
function toggle_site_status(bool $before)
{
    global $container;

    $fluent = $container->get(Database::class);
    $cache = $container->get(Cache::class);
    $online = $fluent->from('site_config')
                     ->select(null)
                     ->select('value')
                     ->where('parent = "site"')
                     ->where('name = "online"')
                     ->fetch('value');
    $online = (bool) $online;
    $disabled = $online ? 0 : 1;
    $set = [
        'value' => $disabled,
    ];
    if ($before) {
        clear_di_cache();
    }
    $fluent->update('site_config')
           ->set($set)
           ->where('parent = "site"')
           ->where('name = "online"')
           ->execute();
    if (!$before) {
        clear_di_cache();
    }
    $cache->set('site_settings_', false);

    return $disabled;
}
