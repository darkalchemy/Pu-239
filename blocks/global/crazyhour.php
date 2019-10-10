<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

global $site_config;

if ($site_config['bonus']['crazy_hour']) {
    $htmlout .= crazyhour();
}

/**
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws Exception
 *
 * @return string
 */
function crazyhour()
{
    global $CURUSER, $container;

    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);
    $htmlout = $cz = '';
    $crazy_hour = (TIME_NOW + 3600);
    $crazyhour['crazyhour'] = $cache->get('crazyhour_');
    if ($crazyhour['crazyhour'] === false || is_null($crazyhour['crazyhour'])) {
        $crazyhour['crazyhour'] = $fluent->from('freeleech')
                                         ->select(null)
                                         ->select('var')
                                         ->select('amount')
                                         ->where("type = 'crazyhour'")
                                         ->fetch();
        if (empty($crazyhour['crazyhour'])) {
            $crazyhour['crazyhour']['var'] = random_int(TIME_NOW, (TIME_NOW + 86400));
            $crazyhour['crazyhour']['amount'] = 0;
            $update = [
                'var' => $crazyhour['crazyhour']['var'],
                'amount' => $crazyhour['crazyhour']['amount'],
            ];
            $fluent->update('freeleech')
                   ->set($update)
                   ->where("type = 'crazyhour'")
                   ->execute();
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
            $update = [
                'var' => $crazyhour['crazyhour']['var'],
                'amount' => $crazyhour['crazyhour']['amount'],
            ];
            $fluent->update('freeleech')
                   ->set($update)
                   ->where("type = 'crazyhour'")
                   ->execute();
            $cache->set('crazyhour_', $crazyhour['crazyhour'], 0);
            write_log('Next [color=#FFCC00][b]Crazyhour[/b][/color] is at ' . get_date((int) $crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'LONG') . '');
            $msg = 'Next [color=orange][b]Crazyhour[/b][/color] is at ' . get_date((int) $crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'LONG');
            autoshout($msg);
        }
    } elseif (($crazyhour['crazyhour']['var'] < $crazy_hour) && ($crazyhour['crazyhour']['var'] >= TIME_NOW)) {
        if ($crazyhour['crazyhour']['amount'] !== 1) {
            $crazyhour['crazyhour']['amount'] = 1;
            $cz_lock = $cache->set('crazyhour_lock_', 1, 10);
            if ($cz_lock !== false) {
                $update = [
                    'amount' => $crazyhour['crazyhour']['amount'],
                ];
                $fluent->update('freeleech')
                       ->set($update)
                       ->where("type = 'crazyhour'")
                       ->execute();
                $cache->set('crazyhour_', $crazyhour['crazyhour'], 0);
                $msg = _("It's CrazyHour");
                write_log($msg);
                autoshout($msg);
            }
        }
        $crazyhour['remaining'] = $crazyhour['crazyhour']['var'] - TIME_NOW;
        $crazytitle = _("It's Crazyhour!");
        $crazymessage = _('All torrents are FREE and upload stats are TRIPLED');
        $htmlout .= "
    <li>
        <a href='#'>
            <span class='button tag is-success dt-tooltipper-small' data-tooltip-content='#crazy_tooltip'>" . _('CrazyHour ON') . "</span>
            <div class='tooltip_templates'>
                <div id='crazy_tooltip' class='margin20'>
                    <div class='size_4 has-text-centered has-text-success has-text-weight-bold bottom10'>
                        " . _fe('CrazyHour {0} {1} Ends in {2} at {3}', $crazytitle, $crazymessage, mkprettytime($crazyhour['remaining']), get_date((int) $crazyhour['crazyhour']['var'], 'WITHOUT_SEC', 1, 1)) . '
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
            <span class='button tag is-success dt-tooltipper-small' data-tooltip-content='#crazy_tooltip'>" . _('CrazyHour') . "</span>
            <div class='tooltip_templates'>
                <div id='crazy_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-success has-text-weight-bold bottom10'>
                        " . _('CrazyHour') . "
                    </div>
                    <div class='has-text-centered is-primary'>
                        " . _('All torrents are FREE') . '<br>
                        ' . _('and triple upload credit!') . '<br>
                        ' . _('starts in %s', mkprettytime($crazyhour['crazyhour']['var'] - 3600 - TIME_NOW)) . '<br>
                        ' . _('at %s', get_date((int) $crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'TIME', 1)) . '
                    </div>
                </div>
            </div>
        </a>
    </li>';

    return $htmlout;
}
