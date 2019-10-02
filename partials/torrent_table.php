<?php

declare(strict_types = 1);

/**
 * @param string $title
 *
 * @return string
 */
function torrent_table(string $title)
{
    return "
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-1 min-100 has-no-border-right'>" . _('Type') . "</th>
                            <th class='w-50 min-350 has-no-border-right has-no-border-left'>$title</th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-right has-no-border-left' title='" . _('Download') . "'><i class='icon-download icon' aria-hidden='true'></i></th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-right has-no-border-left' title='" . _('Comments') . "'><i class='icon-commenting-o icon has-text-info' aria-hidden='true'></i></th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-right has-no-border-left' title='" . _('Completed') . "'><i class='icon-ok-circled2 icon has-text-success' aria-hidden='true'></i></th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-right has-no-border-left' title='" . _('Seeders') . "'><i class='icon-up-big icon has-text-success' aria-hidden='true'></i></th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-left' title='" . _('Leechers') . "'><i class='icon-down-big icon has-text-danger' aria-hidden='true'></i></th>
                        </tr>
                    </thead>
                    <tbody>";
}
