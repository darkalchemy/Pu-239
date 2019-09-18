<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

/**
 * @param int    $modifier
 * @param int    $begin
 * @param int    $expires
 * @param int    $setby
 * @param string $title
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function set_event(int $modifier, int $begin, int $expires, int $setby, string $title)
{
    global $container;

    $fluent = $container->get(Database::class);
    $cache = $container->get(Cache::class);
    $values = [
        'modifier' => $modifier,
        'begin' => $begin,
        'expires' => $expires,
        'setby' => $setby,
        'title' => $title,
    ];
    $fluent->insertInto('events')
           ->values($values)
           ->execute();

    $cache->set('site_events_', $values, $expires);
}

/**
 * @param int $expires
 * @param int $new_expires
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function update_event(int $expires, int $new_expires)
{
    global $container;

    $fluent = $container->get(Database::class);
    $cache = $container->get(Cache::class);

    $set = [
        'expires' => $new_expires,
    ];
    $fluent->update('events')
           ->set($set)
           ->where('expires = ?', $expires)
           ->execute();

    $free = [
        'modifier' => 0,
        'expires' => 0,
    ];

    $cache->set('site_events_', $free, $free['expires']);
}

/**
 * @param bool $all
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return array|bool|mixed
 */
function get_event(bool $all)
{
    global $container;

    $fluent = $container->get(Database::class);
    $cache = $container->get(Cache::class);
    if (!$all) {
        $free = $cache->get('site_events_');
        if ($free === false || is_null($free)) {
            $free = $fluent->from('events')
                           ->where('expires>?', TIME_NOW)
                           ->orderBy('id DESC')
                           ->limit(1)
                           ->fetch();

            if (empty($free)) {
                $free = [
                    'modifier' => 0,
                    'expires' => 0,
                ];
            }
            $cache->set('site_events_', $free, $free['expires']);
        }
    } else {
        $free = $fluent->from('events')
                       ->orderBy('id DESC')
                       ->limit(20)
                       ->fetchAll();

        $free = array_reverse($free);
    }

    return $free;
}

function get_events_data()
{
    $is_free = [
        'free' => 0,
        'double' => 0,
        'silver' => 0,
    ];
    $free = get_event(true);
    if (!empty($free)) {
        foreach ($free as $fl) {
            if (!empty($fl['modifier'])) {
                switch ($fl['modifier']) {
                    case 1:
                        $is_free['free'] = $fl['expires'];
                        break;

                    case 2:
                        $is_free['double'] = $fl['expires'];
                        break;

                    case 3:
                        $is_free['free'] = $fl['expires'];
                        $is_free['double'] = $fl['expires'];
                        break;

                    case 4:
                        $is_free['silver'] = $fl['expires'];
                        break;
                }
            }
        }
    }

    return $is_free;
}
