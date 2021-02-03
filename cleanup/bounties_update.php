<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Bounty;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\User;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Delight\Auth\AuthError
 * @throws \Delight\Auth\NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws \PHPMailer\PHPMailer\Exception
 * @throws \Spatie\Image\Exceptions\InvalidManipulation
 */
function bounties_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $fluent = $container->get(Database::class);
    $bounties = $fluent->from('bounties AS b')
                       ->select(null)
                       ->select('SUM(b.amount) AS amount')
                       ->select('r.filled_by_user_id')
                       ->select('r.id')
                       ->select('r.name')
                       ->innerJoin('requests AS r ON b.requestid = r.id')
                       ->innerJoin('torrents AS t ON r.torrentid = t.id')
                       ->where("b.paid = 'no'")
                       ->where('r.filled_by_user_id != 0')
                       ->where('FROM_UNIXTIME(t.added + 86400 * 2) < NOW()')
                       ->groupBy('r.id')
                       ->groupBy('r.filled_by_user_id')
                       ->fetchAll();
    $msgs_buffer = [];
    if (!empty($bounties)) {
        $bounty_class = $container->get(Bounty::class);
        $user_class = $container->get(User::class);
        $cache = $container->get(Cache::class);
        $subject = 'Bounty Paid';
        foreach ($bounties as $bounty) {
            $amount = (float) $bounty['amount'];
            $id = $bounty['id'];
            $title = "[url={$site_config['paths']['baseurl']}/requests.php?action=view_request&id={$id}]{$bounty['name']}[/url]";
            $msg = 'You were automatically paid the bounty of ' . number_format($amount) . " for filling a request $title.\n\nThank you";
            $owner_id = $bounty['filled_by_user_id'];
            $owner_seedbonus = $user_class->get_item('seedbonus', $owner_id);
            $update = [
                'paid' => 'yes',
            ];
            $bounty_class->pay($update, $id);
            $update = [
                'seedbonus' => $owner_seedbonus + $amount,
            ];
            $user_class->update($update, $owner_id);
            $msgs_buffer[] = [
                'receiver' => $owner_id,
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $cache->delete('user_' . $owner_id);
        }
    }

    if (!empty($msgs_buffer)) {
        $messages_class = $container->get(Message::class);
        $messages_class->insert($msgs_buffer);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Cleanup - Bounties Paid to ' . count($msgs_buffer) . ' members');
        write_log('Bounties Paid Cleanup: Completed' . $text);
    }
}
