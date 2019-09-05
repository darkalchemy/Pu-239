<?php

declare(strict_types = 1);

/**
 * @return string
 */
function torrent_table()
{
    global $lang;

    return "
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-1 min-100 has-no-border-right'>{$lang['index_mow_type']}</th>
                            <th class='w-50 min-350 has-no-border-right has-no-border-left'>{$lang['last5torrents_movie_title']}</th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-right has-no-border-left' title='{$lang['index_download']}'><i class='icon-download icon' aria-hidden='true'></i></th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-right has-no-border-left' title='{$lang['index_comments']}'><i class='icon-commenting-o icon has-text-info' aria-hidden='true'></i></th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-right has-no-border-left' title='{$lang['index_mow_snatched']}'><i class='icon-ok-circled2 icon has-text-success' aria-hidden='true'></i></th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-right has-no-border-left' title='{$lang['index_mow_seeder']}'><i class='icon-up-big icon has-text-success' aria-hidden='true'></i></th>
                            <th class='has-text-centered tooltipper w-1 has-no-border-left' title='{$lang['index_mow_leecher']}'><i class='icon-down-big icon has-text-danger' aria-hidden='true'></i></th>
                        </tr>
                    </thead>
                    <tbody>";
}
