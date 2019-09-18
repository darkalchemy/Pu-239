<?php

declare(strict_types = 1);

use Pu239\Request;

require_once INCL_DIR . 'function_torrent_hover.php';
$user = check_user_status();
global $container, $site_config, $lang;

$lang = array_merge($lang, load_language('requests'));
$request_class = $container->get(Request::class);
$requested = $request_class->get_all($site_config['latest']['requests_limit'], 0, 'added', false, false, (bool) $user['hidden'], $user['id']);
$requests .= "
    <a id='requests-hash'></a>
    <div id='requests' class='box'>
        <div class='grid-wrapper'>
        <div class='table-wrapper has-text-centered'>
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th class='has-text-centered has-no-border-right'>{$lang['upcoming_type']}</th>
                        <th class='min-250 has-no-border-right has-no-border-left'>{$lang['request_title']}</th>
                        <th class='has-text-centered has-no-border-right has-no-border-left'><i class='icon-commenting-o icon' aria-hidden='true'></i></th>
                        <th class='has-text-centered has-no-border-right has-no-border-left'><i class='icon-dollar icon has-text-success' aria-hidden='true'></i></th>
                        <th class='has-text-centered has-no-border-left'><i class='icon-user-plus icon' aria-hidden='true'></i></th>
                    </tr>
                </thead>
                <tbody>";
if (!empty($requested) && is_array($requested)) {
    foreach ($requested as $request) {
        $class_color = get_user_class_name($request['class'], true);
        $caticon = !empty($request['image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . format_comment($request['image']) . "' class='tooltipper' alt='" . format_comment($request['cat']) . "' title='" . format_comment($request['cat']) . "' height='20px' width='auto'>" : format_comment($request['cat']);
        $poster = !empty($request['poster']) ? "<div class='has-text-centered'><img src='" . url_proxy($request['poster'], true, 250) . "' alt='image' class='img-polaroid'></div>" : '';
        $background = $imdb_id = '';
        preg_match('#(tt\d{7,8})#', $request['url'], $match);
        if (!empty($match[1])) {
            $imdb_id = $match[1];
            $background = $images_class->find_images($imdb_id, $type = 'background');
            $background = !empty($background) ? "style='background-image: url({$background});'" : '';
            $poster = !empty($request['poster']) ? $request['poster'] : $images_class->find_images($imdb_id, $type = 'poster');
            $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' alt='Poster for {$request['name']}' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' alt='Poster for {$request['name']}' class='tooltip-poster'>";
        }
        $chef = "<span class='" . get_user_class_name($request['class'], true) . "'>" . $request['username'] . '</span>';
        $plot = $torrent->get_plot($imdb_id);
        if (!empty($plot)) {
            $stripped = strip_tags($plot);
            $plot = strlen($stripped) > 500 ? substr($plot, 0, 500) . '...' : $stripped;
            $plot = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['upcoming_plot']}:</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$plot}</span>
                                                        </div>";
        } else {
            $plot = '';
        }
        $hover = upcoming_hover($site_config['paths']['baseurl'] . '/requests.php?action=view_request&amp;id=' . $request['id'], 'request_' . $request['id'], $request['name'], $background, $poster, get_date($request['added'], 'MYSQL'), get_date($request['added'], 'MYSQL'), $chef, $plot, $lang);
        $requests .= "
                    <tr>
                        <td class='has-text-centered has-no-border-right'>{$caticon}</td>
                        <td class='has-no-border-right has-no-border-left'>{$hover}</td>
                        <td class='has-text-centered has-no-border-right has-no-border-left'>" . number_format($request['comments']) . "</td>
                        <td class='has-text-centered has-no-border-right has-no-border-left'><span class='tooltipper' title='{$lang['request_bounty']}'>" . number_format($request['bounty']) . ' / ' . number_format($request['bounties']) . "</span></td>
                        <td class='has-text-centered has-no-border-left'>
                            <div class='level-center'>
                                <div data-id='{$request['id']}' data-voted='{$request['voted']}' class='request_vote tooltipper' title='" . ($request['voted'] === 'yes' ? $lang['request_voted_yes'] : ($request['voted'] === 'no' ? $lang['request_voted_no'] : $lang['request_not_voted'])) . "'>
                                    <span id='vote_{$request['id']}'>" . ($request['voted'] === 'yes' ? "<i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>" : ($request['voted'] === 'no' ? "<i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>" : "<i class='icon-thumbs-up icon is-marginless' aria-hidden='true'></i>")) . "</span>
                                </div>
                                <div data-id='{$request['id']}' data-notified='{$request['notify']}' class='request_notify tooltipper' title='" . ($request['notify'] === 1 ? $lang['request_notified'] : $lang['request_not_notified']) . "'>
                                    <span id='notify_{$request['id']}'>" . ($request['notify'] === 1 ? "<i class='icon-mail icon has-text-success is-marginless' aria-hidden='true'></i>" : "<i class='icon-envelope-open-o icon has-text-info is-marginless' aria-hidden='true'></i>") . '</span>
                                </div>
                            </div>
                        </td>
                    </tr>';
    }
    $requests .= '
                </tbody>
            </table>
        </div>
        </div>
    </div>';
} else {
    $requests .= "
                    <tr>
                        <td colspan='5'>{$lang['request_no_requests']}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </div>
    </div>";
}
