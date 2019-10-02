<?php

declare(strict_types = 1);
global $site_config;

$HTMLOUT .= tr(_('Report User'), "
    <form method='post' action='{$site_config['paths']['baseurl']}/report.php?type=User&amp;id={$id}' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='submit' value='" . _('Report User') . "' class='button is-small'>" . _(' Click to Report this user for Breaking the rules.') . '
    </form>', 1);
