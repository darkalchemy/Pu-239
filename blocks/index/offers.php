<?php

declare(strict_types = 1);

use Pu239\Image;
use Pu239\Offer;

require_once INCL_DIR . 'function_torrent_hover.php';
$user = check_user_status();
global $container, $site_config;

$offer_class = $container->get(Offer::class);
$offered = $offer_class->get_all($site_config['latest']['offers_limit'], 0, 'added', false, false, (bool) $user['hidden']);
$offers .= "
    <a id='offers-hash'></a>
    <div id='offers' class='box'>
        <div class='grid-wrapper'>
        <div class='table-wrapper has-text-centered'>
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th class='w-1 min-100 has-text-centered has-no-border-right'>" . _('Type') . "</th>
                        <th class='w-50 min-350 has-no-border-right has-no-border-left'>" . _('Offer Title') . "</th>
                        <th class='w-1 has-text-centered has-no-border-right has-no-border-left'>" . _('Status') . "</th>
                        <th class='w-1 has-text-centered has-no-border-right has-no-border-left'><i class='icon-commenting-o icon' aria-hidden='true'></i></th>
                        <th class='w-10 has-text-centered has-no-border-left'><i class='icon-user-plus icon' aria-hidden='true'></i></th>
                    </tr>
                </thead>
                <tbody>";
if (!empty($offered) && is_array($offered)) {
    foreach ($offered as $offer) {
        $class_color = get_user_class_name($offer['class'], true);
        $caticon = !empty($offer['image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . format_comment($offer['image']) . "' class='tooltipper' alt='" . format_comment($offer['cat']) . "' title='" . format_comment($offer['cat']) . "' height='20px' width='auto'>" : format_comment($offer['cat']);
        $poster = !empty($offer['poster']) ? "<div class='has-text-centered'><img src='" . url_proxy($offer['poster'], true, 250) . "' alt='image' class='img-polaroid'></div>" : '';
        $background = $imdb_id = '';
        preg_match('#(tt\d{7,8})#', $offer['url'], $match);
        if (!empty($match[1])) {
            $imdb_id = $match[1];
            $images_class = $container->get(Image::class);
            $background = $images_class->find_images($imdb_id, $type = 'background');
            $background = !empty($background) ? "style='background-image: url({$background});'" : '';
            $poster = !empty($offer['poster']) ? $offer['poster'] : $images_class->find_images($imdb_id, $type = 'poster');
            $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' alt='Poster for {$offer['name']}' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' alt='Poster for {$offer['name']}' class='tooltip-poster'>";
        }
        $chef = "<span class='" . get_user_class_name($offer['class'], true) . "'>" . $offer['username'] . '</span>';
        $plot = $torrent->get_plot($imdb_id);
        if (!empty($plot)) {
            $stripped = strip_tags($plot);
            $plot = strlen($stripped) > 500 ? substr($plot, 0, 500) . '...' : $stripped;
            $plot = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary has-text-weight-bold'>" . _('Plot') . ":</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$plot}</span>
                                                        </div>";
        } else {
            $plot = '';
        }
        $hover = upcoming_hover($site_config['paths']['baseurl'] . '/offers.php?action=view_offer&amp;id=' . $offer['id'], 'offer_' . $offer['id'], $offer['name'], $background, $poster, get_date($offer['added'], 'MYSQL'), get_date($offer['added'], 'MYSQL'), $chef, $plot);
        $offers .= "
                    <tr>
                        <td class='has-text-centered has-no-border-right'>{$caticon}</td>
                        <td class='has-no-border-right has-no-border-left'>{$hover}</td>
                        <td class='has-text-centered has-no-border-right has-no-border-left'>
                            <div data-id='{$offer['id']}' data-status='{$offer['status']}' class='offer_status tooltipper' title='" . ($offer['status'] === 'pending' ? _('Pending') : ($offer['status'] === 'approved' ? _('Approved') : _('Denied'))) . "'>
                                <span id='status_{$offer['id']}'>" . ($offer['status'] === 'approved' ? "<i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>" : ($offer['status'] === 'denied' ? "<i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>" : "<i class='icon-thumbs-down icon is-marginless' aria-hidden='true'></i>")) . "</span>
                            </div>
                        </td>
                        <td class='has-text-centered has-no-border-right has-no-border-left'>" . number_format($offer['comments']) . "</td>
                        <td class='has-text-centered has-no-border-left'>
                            <div class='level-center'>
                                <div data-id='{$offer['id']}' data-voted='{$offer['voted']}' class='offer_vote tooltipper' title='" . ($offer['voted'] === 'yes' ? _('You have voted in favor of this offer.') : ($offer['voted'] === 'no' ? _('You have voted against this offer.') : _('You have not voted.'))) . "'>
                                    <span id='vote_{$offer['id']}'>" . ($offer['voted'] === 'yes' ? "<i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>" : ($offer['voted'] === 'no' ? "<i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>" : "<i class='icon-thumbs-up icon is-marginless' aria-hidden='true'></i>")) . "</span>
                                </div>
                                <div data-id='{$offer['id']}' data-notified='{$offer['notify']}' class='offer_notify tooltipper' title='" . ($offer['notify'] === 1 ? _('You will be notified when this has been uploaded.') : _('You will NOT be notified when this has been uploaded.')) . "'>
                                    <span id='notify_{$offer['id']}'>" . ($offer['notify'] === 1 ? "<i class='icon-mail icon has-text-success is-marginless' aria-hidden='true'></i>" : "<i class='icon-envelope-open-o icon has-text-info is-marginless' aria-hidden='true'></i>") . '</span>
                                </div>
                            </div>
                        </td>
                    </tr>';
    }
    $offers .= '
                </tbody>
            </table>
        </div>
        </div>
    </div>';
} else {
    $offers .= "
                    <tr>
                        <td colspan='5'>" . _('There are no offers.') . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </div>
    </div>';
}
