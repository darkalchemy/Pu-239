<?php

function set_event(int $modifier, int $begin, int $expires, int $setby, string $title)
{
    global $cache, $fluent;

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

function update_event(int $expires, int $new_expires)
{
    global $cache, $fluent;

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

function get_event(bool $all)
{
    global $cache, $fluent;

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
