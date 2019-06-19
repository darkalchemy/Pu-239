<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Database;
use Pu239\Message;
use Pu239\User;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function pu_demote_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $user_class = $container->get(User::class);
    $messages_class = $container->get(Message::class);
    $dt = TIME_NOW;
    $promos = $fluent->from('class_promo AS p')
                    ->select('c.classname')
                    ->select('c.value AS class_value')
                    ->select('c.value - 1 AS prev_class_value')
                    ->where('c.name != ?', 'UC_MIN')
                    ->where('c.name != ?', 'UC_MAX')
                    ->where('c.name != ?', 'UC_STAFF')
                    ->leftJoin('class_config AS c ON p.name = c.name')
                    ->orderBy('p.id')
                    ->fetchAll();

    foreach ($promos as $ac) {
        $class_config = [
            'id' => $ac['id'],
            'name' => $ac['name'],
            'min_ratio' => (float) $ac['min_ratio'],
            'uploaded' => (int) $ac['uploaded'],
            'time' => (int) $ac['time'],
            'low_ratio' => (float) $ac['low_ratio'],
            'class_value' => $ac['class_value'],
            'class_name' => $ac['classname'],
            'prev_class_value' => (int) $ac['prev_class_value'],
            'prev_class_name' => $site_config['class_names'][$ac['prev_class_value']],
        ];
        $items = [
            'id',
            'class',
            'uploaded',
            'downloaded',
            'modcomment',
        ];
        $where = [
            'class =' => $class_config['class_value'],
            'uploaded / NULLIF(downloaded, 0) <' => $class_config['low_ratio'],
        ];
        $users = $user_class->search($items, $where);
        $msgs_buffer = [];
        if (!empty($users)) {
            $subject = 'Auto Demotion';
            $msg = "You have been auto-demoted from [b]{$class_config['class_name']}[/b] to [b]{$class_config['prev_class_name']}[/b] because your share ratio has dropped below {$class_config['low_ratio']}.\n";
            foreach ($users as $arr) {
                $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
                $modcomment = $arr['modcomment'];
                $modcomment = get_date((int) TIME_NOW, 'DATE', 1) . ' - Demoted To ' . $class_config['prev_class_name'] . ' by System (UL=' . mksize($arr['uploaded']) . ', DL=' . mksize($arr['downloaded']) . ', R=' . $ratio . ").\n" . $modcomment;
                $update = [
                    'class' => $class_config['prev_class_value'],
                    'modcomment' => $modcomment,
                ];
                $user_class->update($update, $arr['id']);
                $msgs_buffer[] = [
                    'receiver' => $arr['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
            }
            if (!empty($msgs_buffer)) {
                $messages_class->insert($msgs_buffer);
            }
            if ($data['clean_log'] && !empty($users)) {
                write_log('Cleanup: Demoted ' . count($users) . ' member' . plural(count($users)) . " from {$class_config['class_name']} to {$class_config['prev_class_name']}");
            }
            unset($msgs_buffer);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log("{$class_config['prev_class_name']} Demotion Updates Cleanup: Completed" . $text);
        }
    }
}
