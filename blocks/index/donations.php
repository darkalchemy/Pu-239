<?php
global $site_config, $cache, $lang, $fluent;

$progress = '';
$funds = $cache->get('totalfunds_');
if ($funds === false || is_null($funds)) {
    $funds = $fluent->from('funds')
        ->select(null)
        ->select('cash AS total_funds')
        ->fetch();

    $funds = $funds['total_funds'] <= 0 ? 0 : $funds['total_funds'];
    $cache->set('totalfunds_', $funds, $site_config['expires']['total_funds']);
}

$funds_difference = $site_config['totalneeded'] - $funds;
$progress = number_format($funds / $site_config['totalneeded'] * 100, 1);
if ($progress >= 100) {
    $progress = 100;
}

$HTMLOUT .= "
        <a id='donations-hash'></a>
        <fieldset id='donations' class='header'>
            <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_donations']}</legend>
            <div class='bordered'>
                <div class='alt_bordered bg-00 has-text-centered'>
                    <div>
                        <a href='{$site_config['baseurl']}/donate.php'>
                            <img border='0' style='width: 92px; height: 42px;' src='{$site_config['pic_base_url']}makedonation.gif' alt='{$lang['index_donations']}' title='{$lang['index_donations']}' class='tooltipper' />
                        </a>
                    </div>
                    <div class='container top20 w-25'>
                        <progress class='progress is-success tooltipper' value='{$progress}' max='100' title='{$progress}% Complete'>{$progress}% Complete</progress>
                    </div>
                </div>
            </div>
        </fieldset>";
