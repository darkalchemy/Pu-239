<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Comment;
use Pu239\Image;
use Pu239\Offer;
use Pu239\Session;
use Pu239\Torrent;
use Rakit\Validation\Validator;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_torrent_hover.php';
require_once INCL_DIR . 'function_categories.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_comments.php';
$user = check_user_status();
global $container, $site_config;

$stdhead = [];
$stdfoot = [
    'js' => [
        has_access($user['class'], UC_STAFF, '') ? get_file_name('offer_js') : '',
    ],
];
$images_class = $container->get(Image::class);
$offer_class = $container->get(Offer::class);
$comment_class = $container->get(Comment::class);
$torrent = $container->get(Torrent::class);
$session = $container->get(Session::class);
$has_access = has_access($user['class'], UC_USER, '');
$actions = [
    'view_all',
    'add_offer',
    'edit_offer',
    'delete_offer',
    'view_offer',
    'delete_comment',
    'edit',
    'edit_comment',
    'add_comment',
    'post_comment',
];
$dt = TIME_NOW;
$session->set('post_offer_data', $_POST);
$data = $_GET;
$view_all = $add = $edit = $delete = $view = $edit_comment = $add_comment = $post_comment = false;
if (isset($data['action'])) {
    switch ($data['action']) {
        case 'delete_comment':
            $cid = isset($data['cid']) ? (int) $data['cid'] : 0;
            $tid = isset($data['tid']) ? (int) $data['tid'] : 0;
            $comment = $comment_class->get_comment_by_id($cid);
            if (!empty($comment) && (has_access($user['class'], UC_STAFF, 'formum_mod') || $user['id'] === $comment['user'])) {
                if ($comment_class->delete($cid)) {
                    $update = [
                        'comments' => new Literal('comments - 1'),
                    ];
                    $offer_class->update($update, $tid);
                    $session->set('is-success', _('Comment Deleted'));
                } else {
                    $session->set('is-warning', _('Comment Not Deleted'));
                }
            } else {
                $session->set('is-danger', _('You do not have access to delete this comment'));
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_offer&id=' . $tid);
            die();
        case 'edit':
            $edit_comment = true;
            $cid = isset($data['cid']) ? (int) $data['cid'] : 0;
            $comment = $comment_class->get_comment_by_id($cid);
            $offer = $offer_class->get($comment['offer'], false);
            $edit_form = "
                <h2 class='has-text-centered'>" . _('Editing a comment for') . ': ' . format_comment($offer['name']) . "</h2>
                <form class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/offers.php?action=edit_comment' accept-charset='utf-8'>
                    <input type='hidden' name='id' value='{$comment['offer']}'>
                    <input type='hidden' name='cid' value='{$comment['id']}'>
                    <div class='columns is-marginless is-paddingless'>
                        <div class='column is-one-quarter has-text-left'>" . _('Comment') . "</div>
                        <div class='column'>" . BBcode($comment['text']) . "</div>
                    </div>
                    <div class='has-text-centered padding20'>
                        <input type='submit' value='" . _('Update') . "' class='button is-small'>
                    </div>
                </form>";
            break;
        case 'edit_comment':
            $edit_comment = true;
            break;
        case 'post_comment':
            $post_comment = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            break;
        case 'add_comment':
            $add_comment = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            $offer = $offer_class->get($id, false);
            $edit_form = "
                <h2 class='has-text-centered'>" . _('Add Comment to') . ': ' . format_comment($offer['name']) . "</h2>
                <form class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/offers.php?action=post_comment' accept-charset='utf-8'>
                    <input type='hidden' name='id' value='{$id}'>
                    <div class='columns is-marginless is-paddingless'>
                        <div class='column is-one-quarter has-text-left'>" . _('Comment') . "</div>
                        <div class='column'>" . BBcode() . "</div>
                    </div>
                    <div class='has-text-centered padding20'>
                        <input type='submit' value='" . _('Add Comment') . "' class='button is-small'>
                    </div>
                </form>";
            break;
        case 'view_offer':
            $view = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            $post_data = $offer_class->get($id, has_access($user['class'], UC_STAFF, ''));
            break;
        case 'view_all':
            $view_all = true;
            break;
        case 'add_offer':
            $add = true;
            $post_data = $session->get('post_offer_data');
            break;
        case 'edit_offer':
            $edit = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            $post_data = $offer_class->get($id, false);
            break;
        case 'delete_offer':
            $delete = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            break;
    }
}
if ($add || $edit || $edit_comment || $add_comment) {
    $stdhead = [
        'css' => [
            get_file_name('sceditor_css'),
        ],
    ];
    $stdfoot = [
        'js' => [
            get_file_name('imdb_js'),
            get_file_name('dragndrop_js'),
            get_file_name('sceditor_js'),
        ],
    ];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = $container->get(Validator::class);
    if ($post_comment) {
        $validation = $validator->validate($_POST, [
            'id' => 'required|numeric',
            'body' => '',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            stderr(_('Error'), $errors->firstOfAll()['name']);
            die();
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $values = [
            'text' => htmlsafechars($_POST['body']),
            'offer' => $id,
            'user' => $user['id'],
            'added' => $dt,
        ];
        if ($comment_class->add($values)) {
            $update = [
                'comments' => new Literal('comments + 1'),
            ];
            $offer_class->update($update, $id);
            $session->set('is-success', _('Comment Added'));
        } else {
            $session->set('is-warning', _('Comment Not Added'));
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_offer&id=' . $id);
        die();
    } elseif ($edit_comment) {
        $validation = $validator->validate($_POST, [
            'id' => 'required|numeric',
            'cid' => 'required|numeric',
            'body' => '',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            stderr(_('Error'), $errors->firstOfAll()['name']);
            die();
        }
        $cid = isset($_POST['cid']) ? (int) $_POST['cid'] : 0;
        $comment = $comment_class->get_comment_by_id($cid);
        $values = [
            'text' => htmlsafechars($_POST['body']),
        ];
        if (!empty($comment) && (has_access($user['class'], UC_STAFF, 'formum_mod') || $user['id'] === $comment['user'])) {
            if ($comment_class->update($values, $cid)) {
                $session->set('is-success', _('Comment Updated'));
            } else {
                $session->set('is-warning', _('Comment Not Updated'));
            }
        } else {
            $session->set('is-danger', _('You do not have access to update this comment'));
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_offer&id=' . $id);
        die();
    } else {
        $validation = $validator->validate($_POST, [
            'type' => 'required|numeric',
            'name' => 'required|regex:/[A-Za-z0-9\:_\-\s]/',
            'poster' => 'required|url:http,https',
            'url' => 'required|url:http,https',
            'id' => 'numeric',
            'body' => '',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            stderr(_('Error'), $errors->firstOfAll()['name']);
            die();
        }
        $values = [
            'category' => (int) $_POST['type'],
            'name' => htmlsafechars($_POST['name']),
            'poster' => htmlsafechars($_POST['poster']),
            'url' => htmlsafechars($_POST['url']),
            'added' => $dt,
            'userid' => $user['id'],
            'description' => htmlsafechars($_POST['body']),
        ];
        if ($add) {
            if ($offer_class->insert($values)) {
                $session->unset('post_offer_data');
                $session->set('is-success', _('Offer: %s Added', format_comment($_POST['name'])));
                header('Location: ' . $_SERVER['PHP_SELF']);
                die();
            }
        } elseif ($edit) {
            $values['updated'] = $dt;
            unset($values['added']);
            if ($offer_class->update($values, (int) $_POST['id'])) {
                $session->set('is-success', _('Offer: %s Updated', format_comment($_POST['name'])));
                header('Location: ' . $_SERVER['PHP_SELF']);
                die();
            }
        }
    }
}
$HTMLOUT = $add_new = $update = '';
$form = "
                <div class='columns is-marginless is-paddingless'>
                    <div class='column is-one-quarter has-text-left'>" . _('Category') . "</div>
                    <div class='column'>
                        " . category_dropdown($site_config['categories']['movie']) . "
                    </div>
                </div>
                <div class='columns is-marginless is-paddingless'>
                    <div class='column is-one-quarter has-text-left'>" . _('Offer') . "</div>
                    <div class='column'>
                        <input type='text' class='w-100' name='name' autocomplete='on' value='" . (!empty($post_data['name']) ? htmlsafechars($post_data['name']) : '') . "' required>
                    </div>
                </div>
                <div class='columns is-marginless is-paddingless'>
                    <div class='column is-one-quarter has-text-left'>" . _('Poster') . "</div>
                    <div class='column'>
                        <input type='url' id='image_url' placeholder='" . _('External Image URL') . "' class='w-100' onchange=\"return grab_url(event)\" value='" . (!empty($post_data['poster']) ? htmlsafechars($post_data['poster']) : '') . "'>
                        <input type='url' id='poster' maxlength='255' name='poster' class='w-100 is-hidden' " . (!empty($post_data['poster']) ? "value='" . htmlsafechars($post_data['poster']) . "'" : '') . ">
                        <div class='poster_container has-text-centered'></div>
                        <div id='droppable' class='droppable bg-03 top20'>
                            <span id='comment'>" . _('Drop images or click here to select images.') . "</span>
                            <div id='loader' class='is-hidden'>
                                <img src='{$site_config['paths']['images_baseurl']}/forums/updating.svg' alt='Loading...'>
                            </div>
                        </div>
                        <div class='output-wrapper output'></div>
                    </div>
                </div>
                <div class='columns is-marginless is-paddingless'>
                    <div class='column is-one-quarter has-text-left'>" . _('IMDb Link') . "</div>
                    <div class='column'>
                        <input type='url' class='w-100' id='url' name='url' autocomplete='on' value='" . (!empty($post_data['url']) ? htmlsafechars($post_data['url']) : '') . "' required>
                        <div id='imdb_outer'></div>
                    </div>
                </div>
                <div class='columns is-marginless is-paddingless'>
                    <div class='column is-one-quarter has-text-left'>" . _('Description') . "</div>
                    <div class='column'>" . BBcode(!empty($post_data['description']) ? htmlsafechars($post_data['description']) : '') . '</div>
                </div>';
if ($has_access) {
    if ($add) {
        $add_new = "
            <h2 class='has-text-centered'>" . _('Add Offer') . "</h2>
            <form class='form-inline table-wrapper' method='post' action='{$_SERVER['PHP_SELF']}?action=add_offer' enctype='multipart/form-data' accept-charset='utf-8'>$form
                <div class='has-text-centered'>
                    <input type='submit' value='Add' class='button is-small'>
                </div>
            </form>";
        $add_new = main_div($add_new, 'has-text-centered w-75 min-350', 'padding20');
    } elseif ($edit && is_valid_id($id)) {
        $update = "
            <h2 class='has-text-centered'>" . _('Edit Offer') . "</h2>
            <form class='form-inline table-wrapper' method='post' action='{$_SERVER['PHP_SELF']}?action=edit_offer' enctype='multipart/form-data' accept-charset='utf-8'>$form
                <div class='has-text-centered padding20'>
                    <input type='hidden' name='id' value='{$id}'>
                    <input type='submit' value='" . _('Update') . "' class='button is-small'>
                </div>
            </form>";
        $update = main_div($update, 'has-text-centered w-75 min-350', 'padding20');
    } elseif ($delete && is_valid_id($id)) {
        if ($offer_class->delete($id, $user['class'] >= UC_STAFF, $user['id']) === 1) {
            $session->set('is-success', _('Offer Deleted'));
        } else {
            $session->set('is-warning', _('Offer was NOT Deleted'));
        }
    }
}
$view_offer = $has_votes = '';
if ($view && is_valid_id($id)) {
    preg_match('/(tt[\d]{7,8})/i', $post_data['url'], $match);
    if (!empty($match[1])) {
        $imdb_id = $match[1];
        $imdb_info = get_imdb_info($match[1], true, false, null, $post_data['poster']);
        if (isset($imdb_info[0])) {
            $imdb_info = "
                <div class='columns has-text-left bg-03 top20 round10'>
                    <div class='column is-one-quarter'>" . _('IMDb Info') . "</div>
                    <div class='column'>{$imdb_info[0]}</div>
                </div>";
        }
    }
    if (isset($post_data['vote_yes']) || isset($post_data['vote_no'])) {
        $has_votes = "
                <div class='columns has-text-left bg-03 top20 round10'>
                    <div class='column is-one-quarter'>" . _('User Votes') . "</div>
                    <div class='column is-1 tooltipper' title='{$post_data['vote_yes']} " . _('Users voting for this Offer.') . "'><i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>{$post_data['vote_yes']}</div>
                    <div class='column is-1 tooltipper' title='{$post_data['vote_no']} " . _('Users voting against this Offer.') . "'><i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>{$post_data['vote_no']}</div>
                </div>";
    }
    $view_offer .= "
                <div class='columns has-text-left bg-03 round10'>
                    <div class='column is-one-quarter'>" . _('Category') . "</div>
                    <div class='column'>{$post_data['fullcat']}</div>
                </div>
                <div class='columns bg-03 top20 round10'>
                    <div class='column is-one-quarter has-text-left'>" . _('Description') . "</div>
                    <div class='column'>" . (!empty($post_data['description']) ? format_comment($post_data['description']) : '') . "</div>
                </div>{$imdb_info}{$has_votes}
                <div class='columns bg-03 top20 round10'>
                    <div class='has-text-centered padding20'>
                        <a class='button is-small' href='{$site_config['paths']['baseurl']}/offers.php?action=add_comment&amp;id={$id}'>Add a comment</a>
                    </div>
                </div>";
    $comments = $comment_class->get_comment_by_column('offer', $id);
    $view_offer .= commenttable($comments, 'offer');
    $view_offer = main_div($view_offer, 'has-text-left', 'padding20');
}

$HTMLOUT .= "
    <ul class='level-center bg-06 padding10'>
        <li><a href='{$_SERVER['PHP_SELF']}?action=add_offer'>" . _('Add Offer') . '</a></li>' . ($view_all ? "
        <li><a href='{$_SERVER['PHP_SELF']}'>" . _('View Incomplete Offers') . '</a></li>' : "
        <li><a href='{$_SERVER['PHP_SELF']}?action=view_all'>" . _('View All Offers') . '</a></li>') . "
    </ul>
    <h1 class='has-text-centered'>{$site_config['site']['name']}'s " . _('Offers') . '</h1>';

if (!empty($edit_form)) {
    $HTMLOUT .= $edit_form;
} elseif (!empty($add_new)) {
    $HTMLOUT .= $add_new;
} elseif (!empty($view_offer)) {
    $HTMLOUT .= $view_offer;
} elseif (!empty($update)) {
    $HTMLOUT .= $update;
} else {
    $count = $offer_class->get_count((isset($data['action']) && $data['action'] === 'view_all' ? true : false), (bool) $user['hidden']);
    $perpage = 25;
    $pager = pager($perpage, (int) $count, $_SERVER['PHP_SELF'] . '?');
    $menu_top = $count > $perpage ? $pager['pagertop'] : '';
    $menu_bottom = $count > $perpage ? $pager['pagerbottom'] : '';
    $offers = $offer_class->get_all($pager['pdo']['limit'], $pager['pdo']['offset'], 'added', true, $view_all, (bool) $user['hidden']);
    $heading = "
                    <tr>
                        <th class='has-text-centered'>" . _('Category') . "</th>
                        <th class='has-text-centered min-250'>" . _('Offer') . "</th>
                        <th class='has-text-centered'>" . _('Offered By') . "</th>
                        <th class='has-text-centered'><i class='icon-commenting-o icon' aria-hidden='true'></i></th>
                        <th class='has-text-centered'>" . _('Status') . "</th>
                        <th class='has-text-centered'><i class='icon-user-plus icon' aria-hidden='true'></i></th>" . ($has_access ? "
                        <th class='has-text-centered'><i class='icon-tools icon' aria-hidden='true'></i></th>" : '') . '
                    </tr>';
    $body = '';
    if (!empty($offers)) {
        foreach ($offers as $offer) {
            $has_full_access = $user['id'] === $offer['userid'] || has_access($user['class'], UC_STAFF, '') && $has_access;
            $caticon = !empty($offer['image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . format_comment($offer['image']) . "' class='tooltipper' alt='" . format_comment($offer['cat']) . "' title='" . format_comment($offer['cat']) . "' height='20px' width='auto'>" : format_comment($offer['cat']);
            $poster = !empty($offer['poster']) ? "<div class='has-text-centered'><img src='" . url_proxy($offer['poster'], true, 250) . "' alt='image' class='img-polaroid'></div>" : '';
            $background = $imdb_id = '';
            preg_match('#(tt\d{7,8})#', $offer['url'], $match);
            if (!empty($match[1])) {
                $imdb_id = $match[1];
                $background = $images_class->find_images($imdb_id, $type = 'background');
                $background = !empty($background) ? "style='background-image: url({$background});'" : '';
                $poster = !empty($offer['poster']) ? $offer['poster'] : $images_class->find_images($imdb_id, $type = 'poster');
                $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' alt='" . _('Poster') . "' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' alt='" . _('Poster') . "' class='tooltip-poster'>";
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
            $hover = upcoming_hover($site_config['paths']['baseurl'] . '/offers.php?action=view_offer&amp;id=' . $offer['id'], 'upcoming_' . $offer['id'], $offer['name'], $background, $poster, get_date($offer['added'], 'MYSQL'), get_date($offer['added'], 'MYSQL'), $chef, $plot);
            $body .= "
                    <tr>
                        <td class='has-text-centered'>{$caticon}</td>
                        <td>$hover</td>
                        <td class='has-text-centered'>{$chef}</td>
                        <td class='has-text-centered'><span class='tooltipper' title='" . _('Comments') . "'>" . number_format($offer['comments']) . "</span></td>
                        <td class='has-text-centered'>
                            <div data-id='{$offer['id']}' data-status='{$offer['status']}' class='offer_status tooltipper' title='" . ($offer['status'] === 'pending' ? _('This offer is still pending.') : ($offer['status'] === 'approved' ? _('This offer has been approved.') : _('This offer has been denied.'))) . "'>
                                <span id='status_{$offer['id']}'>" . ($offer['status'] === 'approved' ? "<i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>" : ($offer['status'] === 'denied' ? "<i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>" : "<i class='icon-thumbs-down icon is-marginless' aria-hidden='true'></i>")) . "</span>
                            </div>
                        </td>
                        <td class='has-text-centered w-10'>
                            <div class='level-center'>
                                <div data-id='{$offer['id']}' data-voted='{$offer['voted']}' class='offer_vote tooltipper' title='" . ($offer['voted'] === 'yes' ? _('You support this request.') : ($offer['voted'] === 'no' ? _('You oppose this request.') : _('You have not voted for or against this request.'))) . "'>
                                    <span id='vote_{$offer['id']}'>" . ($offer['voted'] === 'yes' ? "<i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>" : ($offer['voted'] === 'no' ? "<i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>" : "<i class='icon-thumbs-up icon is-marginless' aria-hidden='true'></i>")) . "</span>
                                </div>
                                <div data-id='{$offer['id']}' data-notified='{$offer['notify']}' class='offer_notify tooltipper' title='" . ($offer['notify'] === 1 ? _('You will be notified when this has been uploaded.') : _('You will NOT be notified when this has been uploaded.')) . "'>
                                    <span id='notify_{$offer['id']}'>" . ($offer['notify'] === 1 ? "<i class='icon-mail icon has-text-success is-marginless' aria-hidden='true'></i>" : "<i class='icon-envelope-open-o icon has-text-info is-marginless' aria-hidden='true'></i>") . '</span>
                                </div>
                            </div>
                        </td>' . ($has_access ? "
                        <td class='has-text-centered'>" . ($has_full_access ? "
                            <a href='{$_SERVER['PHP_SELF']}?action=edit_offer&amp;id={$offer['id']}'><i class='icon-edit icon has-text-info' aria-hidden='true'></i></a>
                            <a href='{$_SERVER['PHP_SELF']}?action=delete_offer&amp;id={$offer['id']}'><i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i></a>" : '') . '
                        </td>' : '') . '
                    </tr>';
        }
    } else {
        $cols = $has_access ? 7 : 6;
        $body = "
                    <tr>
                        <td colspan='{$cols}' class='has-text-centered'>" . _('No Offers') . '</td>
                    </tr>';
    }
    $HTMLOUT .= $menu_top . main_table($body, $heading) . $menu_bottom;
}

$title = _('Offers');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
