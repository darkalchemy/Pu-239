<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 */
function sitepot_update($data)
{
    $time_start = microtime(true);
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
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Sitepot Cleanup completed' . $text);
    }
}
