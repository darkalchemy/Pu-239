<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\BotReplies;
use Pu239\Cache;
use Pu239\Database;
use Pu239\User;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CHAT_DIR . 'lib/config.php';

global $container, $site_config;

$user_class = $container->get(User::class);
$user = $user_class->getUserFromId((int) $argv[1]);
$user['channel'] = (int) $argv[2];
$user['text'] = urldecode($argv[3]);
$user['random'] = mt_rand(1, $site_config['chatbot']['gift_odds']);

$run = false;
if ($user['channel'] === 0) {
    $run = bot_respond($user);
}
if (!$run && $user['channel'] === 0 && $user['random'] === 1 && !empty($user['text']) && !preg_match('/^\//', $user['text'])) {
    random_gifts($user);
}

/**
 * @param $user
 *
 * @return bool
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @throws DependencyException
 */
function bot_respond($user)
{
    global $container;

    $cache = $container->get(Cache::class);
    $text = str_replace([
        '&#039;',
        '"',
        "'",
        ',',
        '.',
        '!',
        '@',
        '#',
        '$',
        '%',
        '^',
        '&',
        '*',
        ':',
    ], '', $user['text']);

    $replies = $cache->get('bot_replies_');
    if ($replies === false || is_null($replies)) {
        $replies_class = $container->get(BotReplies::class);
        $replies = $replies_class->get_approved_replies();
        if (!empty($replies)) {
            $cache->set('bot_replies_', $replies, 86400);
        }
    }
    $bot_replies = [];
    foreach ($replies as $trigger => $reply) {
        $trigger = str_replace([
            '&#039;',
            '"',
            "'",
            ',',
            '.',
            '!',
            '@',
            '#',
            '$',
            '%',
            '^',
            '&',
            '*',
            ':',
        ], '', $trigger);
        $words = explode(' ', $trigger);
        $counter = 0;
        foreach ($words as $word) {
            preg_match("/\b{$word}\b/i", $text, $match);
            if (!empty($match[0])) {
                ++$counter;
            }
        }
        if ($counter === count($words)) {
            $bot_replies[] = htmlspecialchars($reply);
        }
    }
    if (!empty($bot_replies)) {
        $random = mt_rand(0, count($bot_replies) - 1);
        $msg = $bot_replies[$random];
        if (strstr($msg, 'username')) {
            $user_class = get_user_class_name($user['class'], true);
            if (!empty($user_class)) {
                $nick = "[{$user_class}]{$user['username']}[/{$user_class}]";
            } else {
                $nick = $user['username'];
            }
            $msg = str_replace('username', $nick, $msg);
        }
        if (!empty($msg)) {
            global $site_config;

            usleep(mt_rand(1000000, 3000000));
            $fluent = $container->get(Database::class);
            $values = [
                'userID' => $site_config['chatbot']['id'],
                'userName' => $site_config['chatbot']['name'],
                'userRole' => $site_config['chatbot']['role'],
                'channel' => 0,
                'dateTime' => get_date(TIME_NOW, 'MYSQL'),
                'ttl' => 0,
                'text' => $msg,
            ];
            $fluent->insertInto('ajax_chat_messages')
                   ->values($values)
                   ->execute();

            return true;
        }
    }

    return false;
}

/**
 * @param $user
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function random_gifts($user)
{
    global $container, $site_config;

    $user_class = get_user_class_name($user['class'], true);
    $username = '[' . $user_class . ']' . $user['username'] . '[/' . $user_class . ']';
    $bonuses = [
        'upload',
        'karma',
        'reputation',
        'freeslots',
        'freeleech',
        'bankrupt',
        'porned',
        'missyou',
    ];
    if ($user['class'] >= 1) {
        $bonuses[] = 'invites';
    }
    $bonus = $bonuses[mt_rand(0, count($bonuses) - 1)];
    switch ($bonus) {
        case 'invites':
            $amount = mt_rand(1, 3);
            $update = [
                'bonuscomment' => get_date(TIME_NOW, 'DATE', 1) . ' - Awarded ' . $amount . " invites from Chat.\n" . $user['bonuscomment'],
                'invites' => $user['invites'] + $amount,
            ];
            $msg = "{$username} has been randomly selected to receive $amount invites from " . $site_config['chatbot']['name'];
            break;
        case 'upload':
            $amount = mt_rand(1, 50);
            $GB = $amount * 1024 * 1024 * 1024;
            $update = [
                'bonuscomment' => get_date(TIME_NOW, 'DATE', 1) . ' - Awarded ' . $amount . " GB ($GB) Seedbonus from Chat.\n" . $user['bonuscomment'],
                'uploaded' => $GB + $user['uploaded'],
            ];
            $msg = "{$username} has been randomly selected to receive " . number_format($amount) . ' GB upload credit from ' . $site_config['chatbot']['name'];
            break;
        case 'karma':
            $amount = mt_rand(500, 25000);
            $update = [
                'bonuscomment' => get_date(TIME_NOW, 'DATE', 1) . ' - Awarded ' . $amount . " Karma from Chat\n" . $user['bonuscomment'],
                'seedbonus' => $amount + $user['seedbonus'],
            ];
            $msg = "{$username} has been randomly selected to receive " . number_format($amount) . ' karma points from ' . $site_config['chatbot']['name'];
            break;
        case 'reputation':
            $amount = mt_rand(1, 15);
            $update = [
                'bonuscomment' => get_date(TIME_NOW, 'DATE', 1) . ' - Awarded ' . $amount . " Reputation from Chat.\n" . $user['bonuscomment'],
                'reputation' => $amount + $user['reputation'],
            ];
            $msg = "{$username} has been randomly selected to receive $amount reputation points from " . $site_config['chatbot']['name'];
            break;
        case 'freeslots':
            $amount = mt_rand(1, 10);
            $update = [
                'bonuscomment' => get_date(TIME_NOW, 'DATE', 1) . ' - Awarded ' . $amount . " Freeslots from Chat.\n" . $user['bonuscomment'],
                'freeslots' => $amount + $user['freeslots'],
            ];
            $msg = "{$username} has been randomly selected to receive $amount freeslot" . plural($amount) . ' from ' . $site_config['chatbot']['name'];
            break;
        case 'freeleech':
            $amount = mt_rand(1, 72);
            $hours = $amount * 3600;
            $update = [
                'bonuscomment' => get_date(TIME_NOW, 'DATE', 1) . ' - Awarded ' . $amount . " hours of Freeleech from Chat.\n" . $user['bonuscomment'],
                'free_switch' => $hours + $user['free_switch'],
            ];
            $msg = "{$username} has been randomly selected to receive $amount hours of freeleech from " . $site_config['chatbot']['name'];
            break;
        case 'bankrupt':
            $msg = "{$username} has been randomly selected to be demoted, bankrupted and stripped of all upload credit. Have a nice day!";
            break;
        case 'porned':
            $msg = "{$username} has been randomly reported to the authorities for watching too much porn. Have a nice day!";
            break;
        case 'missyou':
            $msg = "Hey [i]{$username}[/i], when can I see you again? I've really missed you.";
            break;
    }
    if (!empty($update)) {
        $user_class = $container->get(User::class);
        $user_class->update($update, $user['id']);
    }
    if (!empty($msg)) {
        $values = [
            'userID' => $site_config['chatbot']['id'],
            'userName' => $site_config['chatbot']['name'],
            'userRole' => $site_config['chatbot']['role'],
            'channel' => 0,
            'dateTime' => get_date(TIME_NOW, 'MYSQL'),
            'ttl' => 3600,
            'text' => $msg,
        ];
        $fluent = $container->get(Database::class);
        $fluent->insertInto('ajax_chat_messages')
               ->values($values)
               ->execute();
    }
}
