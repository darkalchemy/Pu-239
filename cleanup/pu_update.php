<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;
use Pu239\Message;
use Pu239\User;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function pu_update($data)
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
            'uploaded' => (int) $ac['uploaded'] * 1024 * 1024 * 1024,
            'time' => $dt - 86400 * $ac['time'],
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
            'invites',
            'modcomment',
        ];
        $where = [
            'class =' => $class_config['prev_class_value'],
            'uploaded / NULLIF(downloaded, 0) >' => $class_config['low_ratio'],
            'enabled = ' => 'yes',
            'registered < ' => $class_config['time'],
            'uploaded >= ' => $class_config['uploaded'],
        ];
        $users = $user_class->search($items, $where);
        $msgs_buffer = $users_buffer = [];
        if (!empty($users)) {
            $subject = 'Class Promotion';
            $msg = 'Congratulations, you have been promoted to [b]' . $class_config['class_name'] . "[/b]. :)\n You get one extra invite.\n";
            foreach ($users as $arr) {
                $ratio = $arr['downloaded'] === 0 ? 'Infinite' : number_format($arr['uploaded'] / $arr['downloaded'], 3);
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - Promoted to ' . $class_config['class_name'] . ' by System (UL=' . mksize($arr['uploaded']) . ', DL=' . mksize($arr['downloaded']) . ', R=' . $ratio . ").\n" . $arr['modcomment'];
                $invites = $user_class->get_item('invites', $arr['id']);
                $update = [
                    'invites' => $invites + 1,
                    'class' => $class_config['class_value'],
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
                write_log('Cleanup: Promoted ' . count($users) . ' member' . plural(count($users)) . " from {$class_config['prev_class_name']} to {$class_config['class_name']}");
            }
            unset($msgs_buffer);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log("{$class_config['class_name']} Promotion Updates Cleanup: Completed" . $text);
        }
    }
}
