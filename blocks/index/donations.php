<?php
global $site_config, $cache, $lang;

$progress = '';
if (($totalfunds_cache = $cache->get('totalfunds_')) === false) {
    $totalfunds_cache = mysqli_fetch_assoc(sql_query('SELECT sum(cash) AS total_funds FROM funds'));
    $totalfunds_cache['total_funds'] = (int)$totalfunds_cache['total_funds'];
    $cache->set('totalfunds_', $totalfunds_cache, $site_config['expires']['total_funds']);
}
$funds_so_far = (int)$totalfunds_cache['total_funds'];
$funds_difference = $site_config['totalneeded'] - $funds_so_far;
$progress = number_format($funds_so_far / $site_config['totalneeded'] * 100, 1);
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
