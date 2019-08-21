<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;

global $site_config;

if ($site_config['bonus']['crazy_hour']) {
    $htmlout .= crazyhour();
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws Exception
 * @throws NotFoundException
 *
 * @return string
 */
function crazyhour()
{
    global $CURUSER, $container, $lang, $site_config;

    $cache = $container->get(Cache::class);
    $htmlout = $cz = '';
    $crazy_hour = (TIME_NOW + 3600);
    $crazyhour['crazyhour'] = $cache->get('crazyhour_');
    if ($crazyhour['crazyhour'] === false || is_null($crazyhour['crazyhour'])) {
        $crazyhour['crazyhour_sql'] = sql_query('SELECT var, amount FROM freeleech WHERE type = "crazyhour"') or sqlerr(__FILE__, __LINE__);
        $crazyhour['crazyhour'] = [];
        if (mysqli_num_rows($crazyhour['crazyhour_sql']) !== 0) {
            $crazyhour['crazyhour'] = mysqli_fetch_assoc($crazyhour['crazyhour_sql']);
        } else {
            $crazyhour['crazyhour']['var'] = random_int(TIME_NOW, (TIME_NOW + 86400));
            $crazyhour['crazyhour']['amount'] = 0;
            sql_query('UPDATE freeleech SET var = ' . $crazyhour['crazyhour']['var'] . ', amount = ' . $crazyhour['crazyhour']['amount'] . ' WHERE type = "crazyhour"') or sqlerr(__FILE__, __LINE__);
        }
        $cache->set('crazyhour_', $crazyhour['crazyhour'], 0);
    }
    if ($crazyhour['crazyhour']['var'] < TIME_NOW) { // if crazyhour over
        $cz_lock = $cache->set('crazyhour_lock_', 1, 10);
        if ($cz_lock !== false) {
            $crazyhour['crazyhour_new'] = mktime(23, 59, 59, (int) date('m'), (int) date('d'), (int) date('y'));
            $crazyhour['crazyhour']['var'] = random_int($crazyhour['crazyhour_new'], ($crazyhour['crazyhour_new'] + 86400));
            $crazyhour['crazyhour']['amount'] = 0;
            $crazyhour['remaining'] = ($crazyhour['crazyhour']['var'] - TIME_NOW);
            sql_query('UPDATE freeleech SET var = ' . $crazyhour['crazyhour']['var'] . ', amount = ' . $crazyhour['crazyhour']['amount'] . ' WHERE type = "crazyhour"') or sqlerr(__FILE__, __LINE__);
            $cache->set('crazyhour_', $crazyhour['crazyhour'], 0);
            write_log('Next [color=#FFCC00][b]Crazyhour[/b][/color] is at ' . get_date((int) $crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'LONG') . '');
            $msg = 'Next [color=orange][b]Crazyhour[/b][/color] is at ' . get_date((int) $crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'LONG');
            autoshout($msg);
        }
    } elseif (($crazyhour['crazyhour']['var'] < $crazy_hour) && ($crazyhour['crazyhour']['var'] >= TIME_NOW)) { // if crazyhour
        if ($crazyhour['crazyhour']['amount'] !== 1) {
            $crazyhour['crazyhour']['amount'] = 1;
            $cz_lock = $cache->set('crazyhour_lock_', 1, 10);
            if ($cz_lock !== false) {
                sql_query('UPDATE freeleech SET amount = ' . $crazyhour['crazyhour']['amount'] . ' WHERE type = "crazyhour"') or sqlerr(__FILE__, __LINE__);
                $cache->set('crazyhour_', $crazyhour['crazyhour'], 0);
                write_log('w00t! It\'s [color=#FFCC00][b]Crazyhour[/b][/color]!');
                $msg = 'w00t! It\'s [color=orange][b]Crazyhour[/b][/color] :w00t:';
                autoshout($msg);
            }
        }
        $crazyhour['remaining'] = $crazyhour['crazyhour']['var'] - TIME_NOW;
        $crazytitle = $lang['gl_crazy_title'];
        $crazymessage = $lang['gl_crazy_message'] . ' <b> ' . $lang['gl_crazy_message1'] . '</b> ' . $lang['gl_crazy_message2'] . ' <strong> ' . $lang['gl_crazy_message3'] . '</strong>!';
        $htmlout .= "
    <li>
        <a href='#'>
            <span class='button tag is-success dt-tooltipper-small' data-tooltip-content='#crazy_tooltip'>{$lang['gl_crazy_on']}</span>
            <div class='tooltip_templates'>
                <div id='crazy_tooltip' class='margin20'>
                    <div class='size_4 has-text-centered has-text-success has-text-weight-bold bottom10'>
                        {$lang['gl_crazy_']} {$crazytitle} {$crazymessage} {$lang['gl_crazy_ends']}<br>" . mkprettytime($crazyhour['remaining']) . "<br>{$lang['gl_crazy_at']} " . get_date((int) $crazyhour['crazyhour']['var'], 'WITHOUT_SEC', 1, 1) . '
                    </div>
                </div>
            </div>
        </a>
    </li>';

        return $htmlout;
    }
    $htmlout .= "
    <li>
        <a href='#'>
            <span class='button tag is-success dt-tooltipper-small' data-tooltip-content='#crazy_tooltip'>{$lang['gl_crazy_']}</span>
            <div class='tooltip_templates'>
                <div id='crazy_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-success has-text-weight-bold bottom10'>
                        {$lang['gl_crazy_']}
                    </div>
                    <div class='has-text-centered is-primary'>
                        {$lang['gl_crazy_message4']}<br>
                        {$lang['gl_crazy_message5']}<br>
                        {$lang['gl_crazy_message6']} " . mkprettytime($crazyhour['crazyhour']['var'] - 3600 - TIME_NOW) . "<br>{$lang['gl_crazy_at']} " . get_date((int) $crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'TIME', 1) . '
                    </div>
                </div>
            </div>
        </a>
    </li>';

    return $htmlout;
}
