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
function funds_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $fluent = $container->get(Database::class);
    $sql = $fluent->from('users')
                  ->select(null)
                  ->select('id')
                  ->select('modcomment')
                  ->select('vipclass_before')
                  ->where('donor = "yes"')
                  ->where('donoruntil < ?', $dt)
                  ->where('donoruntil != 0')
                  ->fetchAll();
    $msgs_buffer = [];
    if (!empty($sql)) {
        $user_class = $container->get(User::class);
        $subject = 'Donor status removed by system.';
        $msg = "Your Donor status has timed out and has been auto-removed by the system, and your Vip status has been removed. We would like to thank you once again for your support to {$site_config['site']['name']}. If you wish to re-new your donation, Visit the site donate link. Cheers!\n";
        foreach ($sql as $arr) {
            $modcomment = get_date($dt, 'DATE', 1) . " - Donation status Automatically Removed By System.\n" . $arr['modcomment'];
            $msgs_buffer[] = [
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $update = [
                'class' => $arr['vipclass_before'],
                'modcomment' => $modcomment,
                'donor' => 'no',
                'donoruntil' => 0,
            ];
            $user_class->update($update, $arr['id']);
        }
        $count = count($msgs_buffer);
        if ($data['clean_log'] && $count > 0) {
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
        }
        if ($data['clean_log']) {
            write_log('Cleanup: Donation status expired - ' . $count . ' Member(s)');
        }
        unset($users_buffer, $msgs_buffer, $update, $count);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Delete Old Funds Cleanup: Completed' . $text);
    }
}
