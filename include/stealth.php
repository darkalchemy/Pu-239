<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;

/**
 * @param      $id
 * @param bool $stealth
 *
 * @throws UnbegunTransaction
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function stealth($id, $stealth = true)
{
    global $container, $site_config, $CURUSER;

    $setbits = $clrbits = 0;
    if ($stealth) {
        $display = 'is';
        $setbits |= PERMS_STEALTH;
    } else {
        $display = 'is not';
        $clrbits |= PERMS_STEALTH;
    }

    if ($setbits || $clrbits) {
        sql_query('UPDATE users SET perms = ((perms | ' . $setbits . ') & ~' . $clrbits . ') WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    }
    $res = sql_query('SELECT username, perms, modcomment FROM users WHERE id=' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int) $row['perms'];
    $modcomment = get_date((int) TIME_NOW, '', 1) . ' - ' . $display . ' in Stealth Mode thanks to ' . $CURUSER['username'] . "\n" . $row['modcomment'];
    sql_query('UPDATE users SET modcomment = ' . sqlesc($modcomment) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $cache = $container->get(Cache::class);
    $cache->update_row('user_' . $id, [
        'perms' => $row['perms'],
        'modcomment' => $modcomment,
    ], $site_config['expires']['user_cache']);
    if ($id == $CURUSER['id']) {
        $cache->update_row('user_' . $CURUSER['id'], [
            'perms' => $row['perms'],
            'modcomment' => $modcomment,
        ], $site_config['expires']['user_cache']);
    }
    write_log('Member [b][url=userdetails.php?id=' . $id . ']' . (htmlsafechars($row['username'])) . '[/url][/b] ' . $display . ' in Stealth Mode thanks to [b]' . $CURUSER['username'] . '[/b]');
    $cache->set('display_stealth_' . $CURUSER['id'], $display, 5);
    header("Location: {$site_config['paths']['baseurl']}/userdetails.php?id=$id");
    die();
}
