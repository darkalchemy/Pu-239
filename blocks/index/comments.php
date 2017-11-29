<?php
require_once INCL_DIR . 'html_functions.php';
global $cache, $lang, $site_config;

$comments = $cache->get('latest_comments_');
if ($comments === false || is_null($comments)) {
    $sql = sql_query("SELECT c.id, c.user AS user_id, c.torrent, c.added, c.text, c.anonymous, c.user_likes, t.name, t.category, cat.name AS cat, cat.image,
                            t.seeders, t.poster, t.leechers, t.times_completed, t.added AS toradd, t.size
                            FROM comments AS c 
                            LEFT JOIN torrents AS t ON t.id = c.torrent
                            LEFT JOIN categories AS cat ON t.category = cat.id 
                            ORDER BY c.id 
                            DESC LIMIT 5") or sqlerr(__FILE__, __LINE__);
    while ($comment = mysqli_fetch_assoc($sql)) {
        $comments[] = $comment;
    }
    $cache->set('latest_comments_', $comments, 3600);
}
$header = "
                        <tr>
                            <th class='has-text-centered w-10'>Type</th>
                            <th class='w-50'>Last 5 Comments</th>
                            <th class='has-text-centered'>User</th>
                            <th class='has-text-centered'>When</th>
                            <th class='has-text-centered'>Likes</th>
                        </tr>";

$body = '';
foreach ($comments as $comment) {
    extract($comment);
    $user = $anonymous === 'yes' ? 'Anonymous' : format_username($user_id);
    $poster = empty($poster) ? "<img src='{$site_config['pic_base_url']}noposter.png' class='tooltip-poster' />" : "<img src='" . htmlsafechars($poster) . "' class='tooltip-poster' />";

    $body .= "
                        <tr>
                            <td class='has-text-centered'>
                                <img src='{$site_config['pic_base_url']}caticons/" . get_categorie_icons() . "/$image' class='tooltipper' alt='$cat' title='$cat' />
                            </td>
                            <td>
                                <a href='{$site_config['baseurl']}/details.php?id=$torrent&amp;hit=1'>
                                    <span class='dt-tooltipper-large' data-tooltip-content='#comment_id_{$id}_tooltip'>
                                        " . format_comment($text) . "
                                        <div class='tooltip_templates'>
                                            <span id='comment_id_{$id}_tooltip'>
                                                <div class='is-flex tooltip-torrent'>
                                                    <span class='margin10'>
                                                        $poster
                                                    </span>
                                                    <span class='margin10'>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($name) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($toradd, 'DATE', 0, 1) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($size)) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>" . (int)$seeders . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>" . (int)$leechers . "<br>
                                                    </span>
                                                </div>
                                            </span>
                                        </div>
                                    </span>
                                </a>
                            </td>
                            <td class='has-text-centered'>$user</td>
                            <td class='has-text-centered'>" . get_date($added, 'LONG') . "</td>
                            <td class='has-text-centered'>" . number_format($user_likes) . "</td>
                        </tr>";
}

$text = main_table($body, $header);

$HTMLOUT .= "
    <a id='latest_comment-hash'></a>
    <fieldset id='latest_comment' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_latest_comments']}</legend>
        <div class='table-wrapper has-text-centered'>
            $text
        </div>
    </fieldset>";
