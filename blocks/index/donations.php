<?php

//== 09 Donation progress
$progress = '';
if (($totalfunds_cache = $mc1->get_value('totalfunds_')) === false) {
    $totalfunds_cache = mysqli_fetch_assoc(sql_query('SELECT sum(cash) as total_funds FROM funds'));
    $totalfunds_cache['total_funds'] = (int) $totalfunds_cache['total_funds'];
    $mc1->cache_value('totalfunds_', $totalfunds_cache, $site_config['expires']['total_funds']);
}
$funds_so_far = (int) $totalfunds_cache['total_funds'];
$funds_difference = $site_config['totalneeded'] - $funds_so_far;
$progress = number_format($funds_so_far / $site_config['totalneeded'] * 100, 1);
if ($progress >= 100) {
    $progress = 100;
}
$HTMLOUT .= "
        <a id='donations-hash'></a>
        <fieldset id='donations' class='header'>
            <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_donations']}</legend>
            <div class='text-center'>
                <p>
                    <a href='{$site_config['baseurl']}/donate.php'>
                        <img border='0' style='width: 92px; height: 42px;' src='{$site_config['pic_base_url']}makedonation.gif' alt='{$lang['index_donations']}' title='{$lang['index_donations']}' class='tooltipper' />
                    </a>
                </p>
                <div class='progress text-center' style='width: 250px;'>
                    <div class='progress-bar progress-bar-success' role='progressbar' aria-valuenow='{$progress}' aria-valuemin='0' aria-valuemax='100' style='width:{$progress}%'>
                        $progress% Complete
                    </div>
                </div>
            </div>
        </fieldset>";
