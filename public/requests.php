<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Bounty;
use Pu239\Comment;
use Pu239\Image;
use Pu239\Request;
use Pu239\Session;
use Pu239\Torrent;
use Pu239\User;
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
        get_file_name('imdb_js'),
    ],
];
$images_class = $container->get(Image::class);
$request_class = $container->get(Request::class);
$comment_class = $container->get(Comment::class);
$torrent = $container->get(Torrent::class);
$session = $container->get(Session::class);
$bounty_class = $container->get(Bounty::class);
$has_access = has_access($user['class'], UC_USER, '');
$actions = [
    'view_all',
    'add_request',
    'edit_request',
    'delete_request',
    'view_request',
    'delete_comment',
    'edit',
    'edit_comment',
    'add_comment',
    'post_comment',
    'add_bounty',
    'pay_bounty',
];
$dt = TIME_NOW;
$session->set('post_request_data', $_POST);
$data = $_GET;
$view_all = $add = $edit = $delete = $view = $edit_comment = $add_comment = $post_comment = $add_bounty = $pay_bounty = false;
$post_data = [];
if (isset($data['action'])) {
    switch ($data['action']) {
        case 'pay_bounty':
            $pay_bounty = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            $post_data = $request_class->get($id, false, $user['id']);
            break;
        case 'delete_comment':
            $cid = isset($data['cid']) ? (int) $data['cid'] : 0;
            $tid = isset($data['tid']) ? (int) $data['tid'] : 0;
            $comment = $comment_class->get_comment_by_id($cid);
            if (!empty($comment) && (has_access($user['class'], UC_STAFF, 'forum_mod') || $user['id'] === $comment['user'])) {
                if ($comment_class->delete($cid)) {
                    $update = [
                        'comments' => new Literal('comments - 1'),
                    ];
                    $request_class->update($update, $tid);
                    $session->set('is-success', _('Comment Deleted'));
                } else {
                    $session->set('is-warning', _('Comment Not Deleted'));
                }
            } else {
                $session->set('is-danger', _('You do not have access to delete this comment'));
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_request&id=' . $tid);
            die();
        case 'edit':
            $edit_comment = true;
            $cid = isset($data['cid']) ? (int) $data['cid'] : 0;
            $comment = $comment_class->get_comment_by_id($cid);
            $request = $request_class->get($comment['request'], false, $user['id']);
            $edit_form = "
                <h2 class='has-text-centered'>" . _('Editing a comment for :') . '' . htmlsafechars($request['name']) . "</h2>
                <form class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/requests.php?action=edit_comment' accept-charset='utf-8'>
                    <input type='hidden' name='id' value='{$comment['request']}'>
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
            $request = $request_class->get($id, false, $user['id']);
            $edit_form = "
                <h2 class='has-text-centered'>" . _('Add Comment') . htmlsafechars($request['name']) . "</h2>
                <form class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/requests.php?action=post_comment' accept-charset='utf-8'>
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
        case 'view_request':
            $view = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            $post_data = $request_class->get($id, has_access($user['class'], UC_STAFF, ''), $user['id']);
            break;
        case 'view_all':
            $view_all = true;
            break;
        case 'add_request':
            $add = true;
            $post_data = $session->get('post_request_data');
            break;
        case 'edit_request':
            $edit = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            $post_data = $request_class->get($id, false, $user['id']);
            break;
        case 'delete_request':
            $delete = true;
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            break;
        case 'add_bounty':
            $add_bounty = true;
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
    if ($pay_bounty) {
        $validation = $validator->validate($_POST, [
            'id' => 'required|numeric',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            stderr(_('Error'), $errors->firstOfAll()['name']);
            die();
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $bounties = $bounty_class->get_sum($id);
        $user_class = $container->get(User::class);
        $owner = $user_class->getUserFromId($post_data['owner']);
        if ($post_data['torrentid'] !== 0 && $post_data['paid'] === 'no' && $post_data['owner'] != 0 && $user['status'] === 0 && $owner['id'] === $post_data['owner'] && (has_access($user['class'], UC_STAFF, '') || $user['id'] === $post_data['id'])) {
            $update = [
                'paid' => 'yes',
            ];
            $bounty_class->pay($update, $id);
            $update = [
                'seedbonus' => $owner['seedbonus'] + $bounties,
            ];
            $user_class->update($update, $owner['id']);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_request&id=' . $id);
        die();
    } elseif ($add_bounty) {
        $validation = $validator->validate($_POST, [
            'id' => 'required|numeric',
            'bounty' => 'required|numeric',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            stderr(_('Error'), $errors->firstOfAll()['name']);
            die();
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $bounty = isset($_POST['bounty']) ? (int) $_POST['bounty'] : 0;
        if ($bounty > 0 && $user['seedbonus'] >= $bounty) {
            $values = [
                'userid' => $user['id'],
                'requestid' => $id,
                'amount' => $bounty,
            ];
            $bounty_id = $bounty_class->add($values);
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_request&id=' . $id);
            die();
        }
    } elseif ($post_comment) {
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
            'request' => $id,
            'user' => $user['id'],
            'added' => $dt,
        ];
        if ($comment_class->add($values)) {
            $update = [
                'comments' => new Literal('comments + 1'),
            ];
            $request_class->update($update, $id);
            $session->set('is-success', _('Comment Added'));
        } else {
            $session->set('is-warning', _('Comment Not Added'));
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_request&id=' . $id);
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
        if (!empty($comment) && (has_access($user['class'], UC_STAFF, 'forum_mod') || $user['id'] === $comment['user'])) {
            if ($comment_class->update($values, $cid)) {
                $session->set('is-success', _('Comment Updated'));
            } else {
                $session->set('is-warning', _('Comment Not Updated'));
            }
        } else {
            $session->set('is-danger', _('You do not have access to update this comment'));
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_request&id=' . $id);
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
            if ($request_class->insert($values)) {
                $session->unset('post_request_data');
                $session->set('is-success', _fe('Request: {0} Added', format_comment($_POST['name'])));
                header('Location: ' . $_SERVER['PHP_SELF']);
                die();
            }
        } elseif ($edit) {
            $values['updated'] = $dt;
            unset($values['added']);
            if ($request_class->update($values, (int) $_POST['id'])) {
                $session->set('is-success', _fe('Request: {0} Updated', format_comment($_POST['name'])));
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
                    <div class='column is-one-quarter has-text-left'>" . _('Request') . "</div>
                    <div class='column'>
                        <input type='text' class='w-100' name='name' autocomplete='on' value='" . (!empty($post_data['name']) ? format_comment($post_data['name']) : '') . "' required>
                    </div>
                </div>
                <div class='columns is-marginless is-paddingless'>
                    <div class='column is-one-quarter has-text-left'>" . _('Poster') . "</div>
                    <div class='column'>
                        <input type='url' id='image_url' placeholder='" . _('External Image URL') . "' class='w-100' onchange=\"return grab_url(event)\" value='" . (!empty($post_data['poster']) ? format_comment($post_data['poster']) : '') . "'>
                        <input type='url' id='poster' maxlength='255' name='poster' class='w-100 is-hidden' " . (!empty($post_data['poster']) ? "value='" . format_comment($post_data['poster']) . "'" : '') . ">
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
                        <input type='url' class='w-100' id='url' name='url' autocomplete='on' value='" . (!empty($post_data['url']) ? format_comment($post_data['url']) : '') . "' required>
                        <div id='imdb_outer'></div>
                    </div>
                </div>
                <div class='columns is-marginless is-paddingless'>
                    <div class='column is-one-quarter has-text-left'>" . _('Description') . "</div>
                    <div class='column'>" . BBcode(!empty($post_data['description']) ? format_comment($post_data['description']) : '') . '</div>
                </div>';
if ($has_access) {
    if ($add) {
        $add_new = "
            <h2 class='has-text-centered'>" . _('Add Request') . "</h2>
            <form class='form-inline table-wrapper' method='post' action='{$_SERVER['PHP_SELF']}?action=add_request' enctype='multipart/form-data' accept-charset='utf-8'>$form
                <div class='has-text-centered'>
                    <input type='submit' value='Add' class='button is-small'>
                </div>
            </form>";
        $add_new = main_div($add_new, 'has-text-centered w-75 min-350', 'padding20');
    } elseif ($edit && is_valid_id($id)) {
        $update = "
            <h2 class='has-text-centered'>" . _('Edit Request') . "</h2>
            <form class='form-inline table-wrapper' method='post' action='{$_SERVER['PHP_SELF']}?action=edit_request' enctype='multipart/form-data' accept-charset='utf-8'>$form
                <div class='has-text-centered padding20'>
                    <input type='hidden' name='id' value='{$id}'>
                    <input type='submit' value='" . _('Update') . "' class='button is-small'>
                </div>
            </form>";
        $update = main_div($update, 'has-text-centered w-75 min-350', 'padding20');
    } elseif ($delete && is_valid_id($id)) {
        if ($request_class->delete($id, $user['class'] >= UC_STAFF, $user['id']) === 1) {
            $session->set('is-success', _('Request Deleted'));
        } else {
            $session->set('is-warning', _('Request was NOT Deleted'));
        }
    }
}
$view_request = $has_votes = '';
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
                    <div class='column is-1 tooltipper' title='{$post_data['vote_yes']} " . _('Users voting for this Request.') . "'><i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>{$post_data['vote_yes']}</div>
                    <div class='column is-1 tooltipper' title='{$post_data['vote_no']} " . _('Users voting against this Request.') . "'><i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>{$post_data['vote_no']}</div>
                </div>";
    }
    $view_request .= "
                <div class='columns has-text-left bg-03 round10'>
                    <div class='column is-one-quarter'>" . _('Category') . "</div>
                    <div class='column'>{$post_data['fullcat']}</div>
                </div>
                <div class='columns bg-03 top20 round10'>
                    <div class='column is-one-quarter has-text-left'>" . _('Description') . "</div>
                    <div class='column'>" . (!empty($post_data['description']) ? format_comment($post_data['description']) : '') . "</div>
                </div>{$imdb_info}{$has_votes}";
    $bounties = $bounty_class->get_bounties($post_data['id']);
    $show_bounties = '';
    if (!empty($bounties)) {
        $show_bounties .= "
            <div class='has-text-centered w-10 min-250 bottom20'>";
        foreach ($bounties as $bounty) {
            $show_bounties .= "
                <div class='level-wide'>
                    <div>" . format_username($bounty['userid']) . '</div>
                    <div>' . number_format((float) $bounty['amount']) . '</div>
                </div>';
        }
        $show_bounties .= '
            </div>';
    }
    if ($post_data['torrentid'] !== 0 && $post_data['paid'] === 'no' && (has_access($user['class'], UC_STAFF, '') || $user['id'] === $post_data['id'])) {
        $view_request .= "
                <div class='columns bg-03 top20 round10'>
                    <div class='has-text-centered padding20'>
                        <h2 class='has-text-centered'>" . _('Accept the torrent and pay the bounty') . "</h2>
                        <form class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/requests.php?action=pay_bounty&amp;id={$id}' accept-charset='utf-8'>
                            <input type='hidden' name='id' value='{$id}'>
                            <div class='level-center-center'>
                                <input type='submit' value='" . _fe('Pay {0} bounty', number_format($post_data['bounties'])) . "' class='button is-small'>
                            </div>
                        </form>
                        <div class='bg-03 padding20 top20 round10'>" . _('Bounties are automatically paid when the requestor accepts the uploaded torrent or 48 hours after the requested torrent has been uploaded.') . '</div>
                    </div>
                </div>';
    } elseif ($post_data['torrentid'] === 0) {
        $view_request .= "
                <div class='columns bg-03 top20 round10'>
                    <div class='has-text-centered padding20'>
                        <h2 class='has-text-centered'>" . _fe('Add a Bounty to: {0}', format_comment($post_data['name'])) . "</h2>
                        <h4 class='has-text-centered bottom20'><span class='tooltipper' title='" . _fe('You have contributed {0} / {1} of the total bounty offered', $post_data['bounty'], $post_data['bounties']) . "'>" . number_format($post_data['bounty']) . ' / ' . number_format($post_data['bounties']) . "</span></h4>
                        {$show_bounties}
                        <form class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/requests.php?action=add_bounty' accept-charset='utf-8'>
                            <input type='hidden' name='id' value='{$id}'>
                            <div class='level-center-center'>
                                <input type='number' name='bounty' min='100' max='" . ($user['seedbonus'] > 100000 ? 100000 : $user['seedbonus']) . "' step='100' class='left10 right10' required>
                                <input type='submit' value='" . _('Add Bounty') . "' class='button is-small left10 right10'>
                            </div>
                        </form>
                        <div class='bg-03 padding20 top20 round10'>" . _('Bounties are automatically paid when the requestor accepts the uploaded torrent or 48 hours after the requested torrent has been uploaded.') . '</div>
                    </div>
                </div>';
    }
    $view_request .= "
                <div class='columns bg-03 top20 round10'>
                    <div class='has-text-centered padding20'>
                        <h2 class='has-text-centered'>" . _fe('Add a comment to: {0}', format_comment($post_data['name'])) . "</h2>
                        <a class='button is-small' href='{$site_config['paths']['baseurl']}/requests.php?action=add_comment&amp;id={$id}'>" . _('Add Comment') . '</a>
                    </div>
                </div>';
    $comments = $comment_class->get_comment_by_column('request', $id);
    $view_request .= commenttable($comments, 'request');
    $view_request = main_div($view_request, 'has-text-left', 'padding20');
}

$HTMLOUT .= "
    <ul class='level-center bg-06 padding10'>
        <li><a href='{$_SERVER['PHP_SELF']}?action=add_request'>" . _('Add Request') . '</a></li>' . ($view_all ? "
        <li><a href='{$_SERVER['PHP_SELF']}'>" . _('View Incomplete Requests') . '</a></li>' : "
        <li><a href='{$_SERVER['PHP_SELF']}?action=view_all'>" . _('View All Requests') . '</a></li>') . "
    </ul>
    <h1 class='has-text-centered'>{$site_config['site']['name']}'s " . _('Requests') . '</h1>';

if (!empty($edit_form)) {
    $HTMLOUT .= $edit_form;
} elseif (!empty($add_new)) {
    $HTMLOUT .= $add_new;
} elseif (!empty($view_request)) {
    $HTMLOUT .= $view_request;
} elseif (!empty($update)) {
    $HTMLOUT .= $update;
} else {
    $count = $request_class->get_count((isset($data['action']) && $data['action'] === 'view_all' ? true : false), (bool) $user['hidden']);
    $perpage = 25;
    $pager = pager($perpage, (int) $count, $_SERVER['PHP_SELF'] . '?');
    $menu_top = $count > $perpage ? $pager['pagertop'] : '';
    $menu_bottom = $count > $perpage ? $pager['pagerbottom'] : '';
    $requests = $request_class->get_all($pager['pdo']['limit'], $pager['pdo']['offset'], 'added', true, $view_all, (bool) $user['hidden'], $user['id']);
    $heading = "
                    <tr>
                        <th class='has-text-centered'>" . _('Category') . "</th>
                        <th class='has-text-centered min-250'>" . _('Request') . "</th>
                        <th class='has-text-centered'>" . _('Requested By') . "</th>
                        <th class='has-text-centered'><i class='icon-commenting-o icon' aria-hidden='true'></i></th>
                        <th class='has-text-centered'><i class='icon-dollar icon has-text-success' aria-hidden='true'></i></th>
                        <th class='has-text-centered'><i class='icon-user-plus icon' aria-hidden='true'></i></th>" . ($has_access ? "
                        <th class='has-text-centered'><i class='icon-tools icon' aria-hidden='true'></i></th>" : '') . '
                    </tr>';
    $body = '';
    if (!empty($requests)) {
        foreach ($requests as $request) {
            $has_full_access = $user['id'] === $request['userid'] || has_access($user['class'], UC_STAFF, '') && $has_access;
            $caticon = !empty($request['image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . format_comment($request['image']) . "' class='tooltipper' alt='" . format_comment($request['cat']) . "' title='" . format_comment($request['cat']) . "' height='20px' width='auto'>" : format_comment($request['cat']);
            $poster = !empty($request['poster']) ? "<div class='has-text-centered'><img src='" . url_proxy($request['poster'], true, 250) . "' alt='image' class='img-polaroid'></div>" : '';
            $background = $imdb_id = '';
            preg_match('#(tt\d{7,8})#', $request['url'], $match);
            if (!empty($match[1])) {
                $imdb_id = $match[1];
                $background = $images_class->find_images($imdb_id, $type = 'background');
                $background = !empty($background) ? "style='background-image: url({$background});'" : '';
                $poster = !empty($request['poster']) ? $request['poster'] : $images_class->find_images($imdb_id, $type = 'poster');
                $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' alt='Poster for {$request['name']}' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' alt='" . _('Poster') . "' class='tooltip-poster'>";
            }
            $chef = format_username($request['userid']);
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
            $hover = upcoming_hover($site_config['paths']['baseurl'] . '/requests.php?action=view_request&amp;id=' . $request['id'], 'upcoming_' . $request['id'], $request['name'], $background, $poster, get_date($request['added'], 'MYSQL'), get_date($request['added'], 'MYSQL'), $chef, $plot);
            $body .= "
                    <tr>
                        <td class='has-text-centered'>{$caticon}</td>
                        <td>$hover</td>
                        <td class='has-text-centered'>{$chef}</td>
                        <td class='has-text-centered'><span class='tooltipper' title='" . _('Comments') . "'>" . number_format($request['comments']) . "</span></td>
                        <td class='has-text-centered'><span class='tooltipper' title='" . _('Bounties') . "'>" . number_format($request['bounty']) . ' / ' . number_format($request['bounties']) . "</span></td>
                        <td class='has-text-centered w-10'>
                            <div class='level-center'>
                                <div data-id='{$request['id']}' data-voted='{$request['voted']}' class='request_vote tooltipper' title='" . ($request['voted'] === 'yes' ? _('You support this request.') : ($request['voted'] === 'no' ? _('You oppose this request.') : _('You have not voted for or against this request.'))) . "'>
                                    <span id='vote_{$request['id']}'>" . ($request['voted'] === 'yes' ? "<i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>" : ($request['voted'] === 'no' ? "<i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>" : "<i class='icon-thumbs-up icon is-marginless' aria-hidden='true'></i>")) . "</span>
                                </div>
                                <div data-id='{$request['id']}' data-notified='{$request['notify']}' class='request_notify tooltipper' title='" . ($request['notify'] === 1 ? _('You will be notified when this has been uploaded.') : _('You will NOT be notified when this has been uploaded.')) . "'>
                                    <span id='notify_{$request['id']}'>" . ($request['notify'] === 1 ? "<i class='icon-mail icon has-text-success is-marginless' aria-hidden='true'></i>" : "<i class='icon-envelope-open-o icon has-text-info is-marginless' aria-hidden='true'></i>") . '</span>
                                </div>
                            </div>
                        </td>' . ($has_access ? "
                        <td class='has-text-centered'>" . ($has_full_access ? "
                            <a href='{$_SERVER['PHP_SELF']}?action=edit_request&amp;id={$request['id']}' class='tooltipper' title='" . _('Edit Request') . "'><i class='icon-edit icon has-text-info' aria-hidden='true'></i></a>
                            <a href='{$_SERVER['PHP_SELF']}?action=delete_request&amp;id={$request['id']}' class='tooltipper' title='" . _('Delete Request') . "'><i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i></a>" : '') . '
                        </td>' : '') . '
                    </tr>';
        }
    } else {
        $cols = $has_access ? 7 : 6;
        $body = "
                    <tr>
                        <td colspan='{$cols}' class='has-text-centered'>" . _('No Requests') . '</td>
                    </tr>';
    }
    $HTMLOUT .= $menu_top . main_table($body, $heading) . $menu_bottom;
}

$title = _('Requests');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
