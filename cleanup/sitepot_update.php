<?php

/**
 * @param $data
 */
function sitepot_update($data)
{
    global $fluent, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $set = [
        'value_i' => 0,
        'value_s' => '0',
    ];
    $fluent->update('avps')
        ->set($set)
        ->where('arg = ?', 'sitepot')
        ->where('value_u < ?', TIME_NOW)
        ->where('value_s = ?', '1')
        ->execute();

    $cache->delete('Sitepot_');
    if ($data['clean_log']) {
        write_log('Sitepot Cleanup completed');
    }
}
