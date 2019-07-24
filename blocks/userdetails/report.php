<?php

declare(strict_types = 1);
global $lang, $site_config;

$HTMLOUT .= tr($lang['userdetails_report'], "
    <form method='post' action='{$site_config['paths']['baseurl']}/report.php?type=User&amp;id={$id}' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='submit' value='{$lang['userdetails_report']}' class='button is-small'>{$lang['userdetails_report_click']}
    </form>", 1);
