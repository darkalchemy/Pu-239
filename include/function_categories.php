<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

/**
 * @param bool $grouped
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool|mixed
 */
function genrelist(bool $grouped)
{
    global $container, $site_config;

    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);
    if ($grouped) {
        $ret = $cache->get('genrelist_grouped_');
        if ($ret === false || is_null($ret)) {
            $ret = [];
            $parents = $fluent->from('categories')
                              ->where('parent_id = 0')
                              ->orderBy('ordered');
            foreach ($parents as $parent) {
                $children = $fluent->from('categories')
                                   ->where('parent_id = ?', $parent['id'])
                                   ->orderBy('ordered')
                                   ->fetchAll();
                $parent['children'] = $children;
                $ret[] = $parent;
            }
            if (!empty($ret)) {
                $cache->set('genrelist_grouped_', $ret, $site_config['expires']['genrelist']);
            }
        }
    } else {
        $ret = $cache->get('genrelist_ordered_');
        if ($ret === false || is_null($ret)) {
            $cats = $fluent->from('categories AS c')
                           ->select('p.name AS parent_name')
                           ->leftJoin('categories AS p ON c.parent_id = p.id')
                           ->orderBy('ordered');

            foreach ($cats as $cat) {
                if (!empty($cat['parent_name'])) {
                    $cat['name'] = $cat['parent_name'] . ' :: ' . $cat['name'];
                }
                $ret[] = $cat;
            }
            if (!empty($ret)) {
                $cache->set('genrelist_ordered_', $ret, $site_config['expires']['genrelist']);
            }
        }
    }

    return $ret;
}

/**
 * @param int $catid
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return mixed|string
 */
function get_fullname_from_id(int $catid)
{
    $cats = genrelist(false);
    foreach ($cats as $cat) {
        if ($cat['id'] === $catid) {
            return $cat['name'];
        }
    }

    return '';
}
