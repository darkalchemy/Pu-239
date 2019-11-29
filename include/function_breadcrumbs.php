<?php

declare(strict_types = 1);

/**
 * @param array $breadcrumbs
 *
 * @return string
 */
function breadcrumbs(array $breadcrumbs)
{
    global $site_config;

    $crumbs = "
                    <nav class='breadcrumb round5' aria-label='breadcrumbs'>
                        <ul>
                            <li><a href='{$site_config['paths']['baseurl']}'>" . _('Home') . '</a></li>';
    foreach ($breadcrumbs as $link) {
        if (!empty($link)) {
            $link = str_replace(",", '', $link);
            $crumbs .= "
                            <li>$link</li>";
        }
    }
    $crumbs .= '
                        </ul>
                    </nav>';

    return $crumbs;
}
