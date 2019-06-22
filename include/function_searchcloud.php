<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Searchcloud;

/**
 * @param int $limit
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function searchcloud($limit = 100)
{
    global $container;

    $cache = $container->get(Cache::class);
    $searchcloud = $cache->get('searchcloud_');
    if ($searchcloud === false || is_null($searchcloud)) {
        $fluent = $container->get(Database::class);
        $search = $fluent->from('searchcloud')
                         ->select('searchedfor')
                         ->select('howmuch')
                         ->select('search_column')
                         ->orderBy('howmuch DESC');
        if ($limit > 0) {
            $search = $search->limit($limit);
        }
        $searchcloud = [];
        $min = 100000;
        $max = 0;
        foreach ($search as $item) {
            $min = $min > $item['howmuch'] ? $item['howmuch'] : $min;
            $max = $max < $item['howmuch'] ? $item['howmuch'] : $max;
            $searchcloud[] = [
                'searchedfor' => $item['searchedfor'],
                'howmuch' => $item['howmuch'],
                'column' => $item['search_column'],
            ];
        }
        if (!empty($searchcloud)) {
            $searchcloud = [
                'search' => $searchcloud,
                'min' => $min,
                'max' => $max,
            ];
            $cache->set('searchcloud_', $searchcloud, 0);

            return $searchcloud;
        }

        return [];
    }

    return $searchcloud;
}

/**
 * @param $word
 * @param $column
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function searchcloud_insert($word, $column)
{
    global $container;

    $cache = $container->get(Cache::class);
    $searchcloud = searchcloud();
    $howmuch = 1;
    $add = true;
    if (!empty($searchcloud)) {
        foreach ($searchcloud['search'] as $cloud) {
            if (strtolower($word) === strtolower($cloud['searchedfor'])) {
                $add = false;
                $howmuch = $cloud['howmuch'] + 1;
            }
        }
    }

    if ($add) {
        $searchcloud['search'][] = [
            'searchedfor' => $word,
            'howmuch' => $howmuch,
            'column' => $column,
        ];
        $cache->set('searchcloud_', $searchcloud, 0);
    } else {
        $cache->delete('searchcloud_');
    }

    $values = [
        'searchedfor' => substr($word, 255),
        'search_column' => $column,
        'howmuch' => 1,
    ];
    $update = [
        'howmuch' => $howmuch,
        'search_column' => new Literal('VALUES(search_column)'),
    ];

    $seachcloud_class = $container->get(Searchcloud::class);
    $seachcloud_class->insert($values, $update);
}

/**
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return string
 */
function cloud()
{
    global $site_config;

    $small = 14;
    $big = 80;
    $tags = searchcloud();
    if (!empty($tags)) {
        $minimum_count = $tags['min'];
        $maximum_count = $tags['max'];
        $spread = $maximum_count - $minimum_count;
        if ($spread == 0) {
            $spread = 1;
        }
        $cloud_tags = $search = [];
        foreach ($tags['search'] as $tag) {
            if (!empty($tag['searchedfor'])) {
                $search[$tag['searchedfor']] = $tag;
            }
        }
        $tags = shuffle_assoc($search, 10);
        foreach ($tags as $tag => $values) {
            $count = $values['howmuch'];
            $size = floor($small + round(($count - $minimum_count) * ($big - $small) / $spread, 0, PHP_ROUND_HALF_UP));
            $color = random_color();
            $column = str_replace([
                'name',
                'descr',
                'imdb',
                'isbn',
            ], [
                'sn',
                'sd',
                'si',
                'ss',
            ], $values['column']);
            $cloud_tags[] = "
                            <a class='tooltipper tag_cloud' style='color:{$color}; font-size: {$size}px' href='{$site_config['paths']['baseurl']}/browse.php?{$column}=" . urlencode((string) $tag) . "&amp;incldead=1' title='<div class=\"size_5 has-text-primary has-text-centered\">\"" . htmlsafechars((string) $tag) . "\"</div><br>has been searched for {$count} times.'>
                                <span class='padding10 has-no-wrap'>" . htmlsafechars(stripslashes((string) $tag)) . '</span>
                            </a>';
        }
        $cloud_html = implode("\n", $cloud_tags) . "\n";

        return $cloud_html;
    }
}
