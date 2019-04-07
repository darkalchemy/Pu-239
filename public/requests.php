<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_comments.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_bbcode.php';
check_user_status();
global $CURUSER, $site_config, $user_stuffs, $fluent, $mysqli, $commentid;

$lang = array_merge(load_language('global'), load_language('comment'), load_language('bitbucket'), load_language('upload'));

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('request_js'),
        get_file_name('sceditor_js'),
    ],
];
$HTMLOUT = $count2 = '';
if ($CURUSER['class'] < (UC_MIN + 1)) {
    stderr('Error!', 'Sorry, you need to rank up!');
}
$id = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
$comment_id = (isset($_GET['cid']) ? intval($_GET['cid']) : (isset($_POST['cid']) ? intval($_POST['cid']) : 0));
if (isset($_GET['comment_id']) && $comment_id === 0) {
    $comment_id = $_GET['comment_id'];
} elseif (isset($_POST['comment_id']) && $comment_id === 0) {
    $comment_id = $_POST['comment_id'];
}
$category = (isset($_GET['category']) ? intval($_GET['category']) : (isset($_POST['category']) ? intval($_POST['category']) : 0));
$requested_by_id = isset($_GET['requested_by_id']) ? intval($_GET['requested_by_id']) : 0;
$vote = isset($_POST['vote']) ? intval($_POST['vote']) : 0;
$posted_action = strip_tags((isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : '')));

$valid_actions = [
    'add_new_request',
    'delete_request',
    'edit_request',
    'update_request',
    'request_details',
    'vote',
    'add_comment',
    'edit',
    'delete',
    'vieworiginal',
    'edit_comment',
    'delete_comment',
    'add_bounty',
    'pay_bounty',
];
$action = in_array($posted_action, $valid_actions) ? $posted_action : 'default';
$bounty_note1 = 'Bounties are paid automatically 48 hours after uploaded, if not paid by the requestor before then.';
$bounty_note2 = '1) You are responsible for ensuring that the torrent uploaded matches this request. If not, notify staff.<br>
2) You are responsible for paying the bounties or challenging them as not metting your request.<br>
3) If you do not pay the bounty within 48 hours, or challenge them, the system will force them paid.<br>
4) After the bounties have been paid, they are not reversable.';

$top_menu = '
    <div>
        <ul class="level-center bg-06 bottom20">
            <li class="altlink margin10">
                <a href="' . $site_config['paths']['baseurl'] . '/requests.php">View Requests</a>
            </li>
            <li class="altlink margin10">
                <a href="' . $site_config['paths']['baseurl'] . '/requests.php?action=add_new_request">New Request</a>
            </li>
        </ul>
    </div>';
switch ($action) {
    case 'update_request':
        if (!isset($id) || !is_valid_id($id)) {
            stderr('Error', 'Bad ID.');
        }
        $exists = $fluent->from('requests')
                         ->select(null)
                         ->select('requested_by_user_id')
                         ->where('id=?', $id)
                         ->fetch();
        if (empty($exists)) {
            stderr('Error', 'Invalid ID.');
        }
        if ($exists['requested_by_user_id'] !== $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        $set = [
            'id' => $_POST['id'],
            'request_name' => $_POST['request_name'],
            'image' => $_POST['image'],
            'link' => $_POST['link'],
            'category' => $_POST['category'],
            'description' => $_POST['body'],
            'updated' => TIME_NOW,
        ];
        $fluent->update('requests')
               ->set($set)
               ->where('id=?', $id)
               ->execute();

        header('Location: ' . $site_config['paths']['baseurl'] . '/requests.php?action=request_details&id=' . sqlesc($id));
        die();
        break;

    case 'vote':
        if (!isset($id) || !is_valid_id($id) || !isset($vote) || !is_valid_id($vote)) {
            stderr('USER ERROR', 'Bad id / bad vote');
        }
        $voted = $fluent->from('request_votes')
                        ->select(null)
                        ->select('vote')
                        ->where('user_id=?', $CURUSER['id'])
                        ->where('request_id=?', $id)
                        ->fetch('vote');

        if (!empty($voted)) {
            stderr('USER ERROR', 'You have voted on this request before.');
        } else {
            $yes_or_no = $vote === 1 ? 'yes' : 'no';
            $values = [
                'request_id' => $id,
                'user_id' => $CURUSER['id'],
                'vote' => $yes_or_no,
            ];
            $fluent->insertInto('request_votes')
                   ->values($values)
                   ->execute();
            if ($vote === 1) {
                $set = [
                    'vote_yes_count' => new Envms\FluentPDO\Literal('vote_yes_count + 1'),
                ];
            } else {
                $set = [
                    'vote_no_count' => new Envms\FluentPDO\Literal('vote_no_count + 1'),
                ];
            }
            $fluent->update('requests')
                   ->set($set)
                   ->where('id=?', $id)
                   ->execute();
            header('Location: ' . $site_config['paths']['baseurl'] . '/requests.php?action=request_details&voted=1&id=' . sqlesc($id));
            die();
        }
        break;

    case 'default':
        $count = $fluent->from('requests')
                        ->select(null)
                        ->select('COUNT(*) AS count')
                        ->fetch('count');
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
        $perpage = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 15;
        $link = $site_config['paths']['baseurl'] . '/requests.php?' . (isset($_GET['perpage']) ? "perpage={$perpage}&amp;" : '');
        $pager = pager($perpage, $count, $link);
        $menu_top = $pager['pagertop'];
        $menu_bottom = $pager['pagerbottom'];

        $requests = $fluent->from('requests AS r')
                           ->select('c.name AS cat_name')
                           ->select('c.image AS cat_image')
                           ->select('p.name AS parent_name')
                           ->leftJoin('categories AS c ON r.category = c.id')
                           ->leftJoin('categories AS p ON c.parent_id=p.id')
                           ->orderBy('r.added DESC')
                           ->limit($pager['pdo'])
                           ->fetchAll();

        if (empty($requests)) {
            stderr('Error!', 'Sorry, there are no current requests!');
        }
        $HTMLOUT .= (isset($_GET['new']) ? '<h1>Request Added!</h1>' : '') . (isset($_GET['request_deleted']) ? '<h1>Request Deleted!</h1>' : '') . $top_menu . '' . ($count > $perpage ? $menu_top : '');
        $heading = '
        <tr>
            <th>Type</th>
            <th>Name</th>
            <th>Added</th>
            <th>Comm</th>
            <th>Votes</th>
            <th>Requested By</th>
            <th>Filled</th>
        </tr>';
        $body = '';
        foreach ($requests as $request) {
            $request['cat'] = $request['parent_name'] . '::' . $request['cat_name'];
            $caticon = !empty($request['cat_image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($request['cat_image']) . "' class='tooltipper' alt='" . htmlsafechars($request['cat']) . "' title='" . htmlsafechars($request['cat']) . "' height='20px' width='auto'>" : htmlsafechars($request['cat']);
            $body .= '
        <tr>
            <td>' . $caticon . '</td>
            <td><a class="altlink" href="' . $site_config['paths']['baseurl'] . '/requests.php?action=request_details&amp;id=' . $request['id'] . '">' . htmlsafechars($request['request_name'], ENT_QUOTES) . '</a></td>
            <td>' . get_date($request['added'], 'LONG') . '</td>
            <td>' . number_format($request['comments']) . '</td>
            <td>yes: ' . number_format($request['vote_yes_count']) . '<br>
            no: ' . number_format($request['vote_no_count']) . '</td>
            <td>' . format_username($request['requested_by_user_id']) . '</td>
            <td>' . ($request['filled_by_user_id'] > 0 ? '<a href="details.php?id=' . (int) $request['filled_torrent_id'] . '" title="go to torrent page!!!"><span>yes!</span></a>' : '<span>no</span>') . '</td>
        </tr>';
        }
        $HTMLOUT .= !empty($body) ? main_table($body, $heading) : main_div('<div class="padding20 has-text-centered">There are no requests</div>');
        $HTMLOUT .= $count > $perpage ? $menu_bottom : '';

        echo stdhead('Requests', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'request_details':
        if (!isset($id) || !is_valid_id($id)) {
            stderr('USER ERROR', 'Bad id');
        }
        $arr = $fluent->from('requests AS r')
                      ->select('r.id AS request_id')
                      ->select('c.name AS cat_name')
                      ->select('c.image AS cat_image')
                      ->select('p.name AS parent_name')
                      ->leftJoin('categories AS c ON r.category = c.id')
                      ->leftJoin('categories AS p ON c.parent_id=p.id')
                      ->where('r.id=?', $id)
                      ->fetch();

        $arr['cat'] = $arr['parent_name'] . '::' . $arr['cat_name'];
        $caticon = !empty($arr['cat_image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($arr['cat_image']) . "' class='tooltipper' alt='" . htmlsafechars($arr['cat']) . "' title='" . htmlsafechars($arr['cat']) . "' height='20px' width='auto'>" : htmlsafechars($arr['cat']);

        if (!empty($arr['link'])) {
            preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $arr['link'], $imdb);
            $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        }
        $movie_info = get_imdb_info($imdb, false, false, null, null);

        $voted = $fluent->from('request_votes')
                        ->select(null)
                        ->select('vote')
                        ->where('user_id=?', $CURUSER['id'])
                        ->where('request_id=?', $id)
                        ->fetch('vote');

        if (!$voted) {
            $vote_yes = '<form method="post" action="' . $site_config['paths']['baseurl'] . '/requests.php" accept-charset="utf-8">
                    <input type="hidden" name="action" value="vote">
                    <input type="hidden" name="id" value="' . $id . '">
                    <input type="hidden" name="vote" value="1">
                    <input type="submit" class="button is-small" value="vote yes!">
                    </form> ~ you will be notified when this request is filled.';
            $vote_no = '<form method="post" action="' . $site_config['paths']['baseurl'] . ' / requests.php" accept-charset="utf-8">
                    <input type="hidden" name="action" value="vote">
                    <input type="hidden" name="id" value="' . $id . '">
                    <input type="hidden" name="vote" value="2">
                    <input type="submit" class="button is-small" value="vote no!">
                    </form> ~ you are being a stick in the mud.';
            $your_vote_was = '';
        } else {
            $vote_yes = '';
            $vote_no = '';
            $your_vote_was = ' your vote: ' . $voted;
        }
        $usersdata = $user_stuffs->getUserFromId($arr['requested_by_user_id']);
        $HTMLOUT .= (isset($_GET['voted']) ? '<h1>vote added</h1>' : '') . (isset($_GET['comment_deleted']) ? '<h1>comment deleted</h1>' : '') . $top_menu . '
  <table class="table table-bordered table-striped">
  <tr>
  <td colspan="2"><h1>' . htmlsafechars($arr['request_name'], ENT_QUOTES) . ($CURUSER['class'] < UC_STAFF ? '' : ' [ <a href="' . $site_config['paths']['baseurl'] . '/requests.php?action=edit_request&amp;id=' . $id . '">edit</a> ]
  [ <a href="' . $site_config['paths']['baseurl'] . '/requests.php?action=delete_request&amp;id=' . $id . '">delete</a> ]') . '</h1></td>
  </tr>
  <tr>
  <td>image:</td>
  <td><img src="' . strip_tags(url_proxy($arr['image'], true, 500)) . '" alt="image"></td>
  </tr>
  <tr>
  <td>description:</td>
  <td>' . format_comment($arr['description']) . '</td>
  </tr>
  <tr>
  <td>category:</td>
  <td>' . $caticon . '</td>
  </tr>
  <tr>
  <td>link:</td>
  <td><a class="altlink" href="' . htmlsafechars($arr['link'], ENT_QUOTES) . '"  target="_blank">' . htmlsafechars($arr['link'], ENT_QUOTES) . '</a></td>
  </tr>
    <tr>
        <td>IMDb</td>
        <td>' . $movie_info[0] . '</td>
    </tr>
  <tr>
  <td>votes:</td>
  <td>
  <span>yes: ' . number_format($arr['vote_yes_count']) . '</span> ' . $vote_yes . '<br>
  <span>no: ' . number_format($arr['vote_no_count']) . '</span> ' . $vote_no . '<br> ' . $your_vote_was . '</td>
  </tr>
  <tr>
  <td>requested by:</td>
  <td>' . format_username($usersdata['id']) . ' [ ' . get_user_class_name($usersdata['class']) . ' ]
  ratio: ' . member_ratio($usersdata['uploaded'], $site_config['site']['ratio_free'] ? '0' : $usersdata['downloaded']) . get_user_ratio_image(($site_config['site']['ratio_free'] ? 1 : $usersdata['uploaded'] / ($usersdata['downloaded'] == 0 ? 1 : $usersdata['downloaded']))) . '</td>
  </tr>' . ($arr['filled_torrent_id'] > 0 ? '<tr>
  <td>filled:</td>
  <td><a class="altlink" href="details.php?id=' . $arr['filled_torrent_id'] . '">yes, click to view torrent!</a></td>
  </tr>' : '') . '
  <tr>
  <td>Report Request</td>
  <td>
    <form action="' . $site_config['paths']['baseurl'] . '/report.php?type=Request&amp;id=' . $id . '" method="post" accept-charset="utf-8">
        <div class="has-text-centered margin20">
            <input type="submit" class="button is-small" value="Report This Request">
        </div>
        For breaking the <a class="altlink" href="rules . php">rules</a>
    </form>
    </td>
  </tr>
  </table>';
        $HTMLOUT .= '
            <h1 class="has-text-centered">Comments for ' . htmlsafechars($arr['request_name'], ENT_QUOTES) . '</h1>
            <a id="startcomments"></a>
            <div class="has-text-centered margin20">
                <a class="button is-small" href="' . $site_config['paths']['baseurl'] . '/requests.php?action=add_comment&amp;id=' . $id . '">Add a comment</a>
            </div>';
        $count = (int) $arr['comments'];
        if (!$count) {
            $HTMLOUT .= main_div('<h2>No comments yet</h2>', 'top20 has-text-centered');
        } else {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
            $perpage = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 15;
            $link = $site_config['paths']['baseurl'] . "/requests.php?action=request_details&amp;id=$id" . (isset($_GET['perpage']) ? "perpage ={$perpage}&amp;" : '');
            $pager = pager($perpage, $count, $link);
            $menu_top = $pager['pagertop'];
            $menu_bottom = $pager['pagerbottom'];

            $allrows = $fluent->from('comments')
                              ->select('id AS comment_id')
                              ->where('request = ?', $id)
                              ->orderBy('id DESC')
                              ->limit($pager['pdo'])
                              ->fetchAll();

            $HTMLOUT .= '<a id="comments"></a>';
            $HTMLOUT .= ($count > $perpage ? $menu_top : '') . '<br>';
            $HTMLOUT .= commenttable($allrows, 'request');
            $HTMLOUT .= ($count > $perpage ? $menu_bottom : '');
        }
        echo stdhead('Request details for: ' . htmlsafechars($arr['request_name'], ENT_QUOTES), $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'add_new_request':
        $request_name = strip_tags(isset($_POST['request_name']) ? trim($_POST['request_name']) : '');
        $image = strip_tags(isset($_POST['poster']) ? trim($_POST['poster']) : '');
        $body = isset($_POST['body']) ? trim($_POST['body']) : '';
        $link = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : '');
        $category_drop_down = '
                <select name="category" required>
                    <option value="">Select Request Category</option>';
        $cats = genrelist(true);
        foreach ($cats as $cat) {
            foreach ($cat['children'] as $row) {
                $category_drop_down .= " < option value = '{$row['id']}'" . ($category == $row['id'] ? ' selected' : '') . '>' . htmlsafechars($cat['name']) . '::' . htmlsafechars($row['name']) . '</option>';
            }
        }
        $category_drop_down .= '
                </select>';
        if (isset($_POST['button']) && $_POST['button'] == 'Submit') {
            $values = [
                'request_name' => $request_name,
                'image' => $image,
                'description' => $body,
                'category' => $category,
                'added' => TIME_NOW,
                'requested_by_user_id' => $CURUSER['id'],
                'link' => $link,
            ];
            $new_request_id = $fluent->insertInto('requests')
                                     ->values($values)
                                     ->execute();

            $color = get_user_class_name($CURUSER['class'], true);
            $msg = "[{
        $color}]{$CURUSER['username']}[/{$color}] posted a new request: [url ={$site_config['paths']['baseurl']}/requests.php?action=request_details & id ={$new_request_id}]{$request_name}[/url]";
            autoshout($msg);
            header('Location: ' . $site_config['paths']['baseurl'] . '/requests.php?action=request_details&new=1&id=' . $new_request_id);
            die();
        }
        $stdfoot['js'] = array_merge($stdfoot['js'], [
            get_file_name('dragndrop_js'),
        ]);
        $HTMLOUT .= $top_menu . '
    <h1 class="has-text-centered">New Request</h1>
    <div class="banner_container has-text-centered w-100"></div>
    <form method="post" action="' . $site_config['paths']['baseurl'] . ' / requests.php?action=add_new_request" accept-charset="utf-8">
    <table class="table table-bordered table-striped">
    <tbody>
    <tr>
    <td colspan="2"><h1>Making a Request</h1></td>
    </tr>
    <tr>
    <td colspan="2">Before you make an request, <a class="altlink" href="browse.php">Search</a>
    to be sure it has not yet been requested, offered, or uploaded!<br><br>Be sure to fill in all fields!
    <div class="has-text-centered error size_6 margin20"><span></span></div>
    </td>
    </tr>
    <tr>
    <td>name:</td>
    <td><input type="text" name="request_name" value="' . htmlsafechars($request_name, ENT_QUOTES) . '" class="w-100" required></td>
    </tr>
    <tr>
    <td>link:</td>
    <td>
        <input type="url" id="url" name="link" class="w-100" data-csrf="' . $session->get('csrf_token') . '" value="' . htmlsafechars($link, ENT_QUOTES) . '" required>
        <div id="imdb_outer">
        </div>
    </td>
    </tr>
    <tr>
    <td>image:</td>
    <td>
        <input type="url" id="image_url" data-csrf="' . $session->get('csrf_token') . '" placeholder="External Image URL" class="w-100" onchange=\'return grab_url(event)\'>
        <input type="url" id="poster" maxlength="255" name="poster" class="w-100 is-hidden">
        <div class="poster_container has-text-centered"></div>
    </td>
    </tr>
    <tr>
    <td class="rowhead"><b>' . $lang['upload_bitbucket'] . '</b></td>
    <td class="has-text-centered">
        <div id="droppable" class="droppable bg-03">
            <span id="comment">' . $lang['bitbucket_dragndrop'] . '</span>
            <div id="loader" class="is-hidden">
                <img src="' . $site_config['paths']['images_baseurl'] . 'forums/updating.svg" alt="Loading...">
            </div>
        </div>
        <div class="output-wrapper output"></div>
    </td>
    </tr>
    <tr>
    <td>category:</td>
    <td>' . $category_drop_down . '</td>
    </tr>
    <tr>
    <td>description:</td>
    <td class="is-paddingless">' . BBcode($body) . '</td>
    </tr>
    <tr>
    <td colspan="2">
    <div class="has-text-centered margin20">
        <input type="submit" name="button" class="button is-small" value="Submit">
    </div>
    </td>
    </tr>
    </tbody>
    </table></form>';
        echo stdhead('Add new request.', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'delete_request':
        if (!isset($id) || !is_valid_id($id)) {
            stderr('Error', 'Bad ID.');
        }
        $exists = $fluent->from('requests')
                         ->select(null)
                         ->select('request_name')
                         ->select('requested_by_user_id')
                         ->where('id=?', $id)
                         ->fetch();
        if (empty($exists)) {
            stderr('Error', 'Invalid ID.');
        }
        if ($exists['requested_by_user_id'] !== $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        if (!isset($_GET['do_it'])) {
            stderr('Sanity check...', 'Are you sure you would like to delete the request <b>"' . htmlsafechars($exists['request_name'], ENT_QUOTES) . '"</b>? If so click
        <a class="altlink" href="' . $site_config['paths']['baseurl'] . '/requests.php?action=delete_request&amp;id=' . $id . '&amp;do_it=666">HERE</a>.');
        } else {
            $fluent->deleteFrom('requests')
                   ->where('id=?', $id)
                   ->execute();
            $fluent->deleteFrom('comments')
                   ->where('request = ?', $id)
                   ->execute();

            header('Location: ' . $site_config['paths']['baseurl'] . '/requests.php?request_deleted=1');
            die();
        }
        echo stdhead('Delete Request.', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'edit_request':
        require_once INCL_DIR . 'function_bbcode.php';
        if (!isset($id) || !is_valid_id($id)) {
            stderr('Error', 'Bad ID.');
        }
        $edit_arr = $fluent->from('requests AS r')
                           ->select('r.id AS request_id')
                           ->select('c.name AS cat_name')
                           ->select('c.image AS cat_image')
                           ->select('p.name AS parent_name')
                           ->leftJoin('categories AS c ON r.category = c.id')
                           ->leftJoin('categories AS p ON c.parent_id=p.id')
                           ->where('r.id=?', $id)
                           ->fetch();

        $edit_arr['cat'] = $edit_arr['parent_name'] . '::' . $edit_arr['cat_name'];
        $caticon = !empty($edit_arr['cat_image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($edit_arr['cat_image']) . "' class='tooltipper' alt='" . htmlsafechars($edit_arr['cat']) . "' title='" . htmlsafechars($edit_arr['cat']) . "' height='20px' width='auto'>" : htmlsafechars($edit_arr['cat']);

        if ($CURUSER['class'] < UC_STAFF && $CURUSER['id'] !== $edit_arr['requested_by_user_id']) {
            stderr('Error!', 'This is not your request to edit!');
        }
        $filled_by = '';
        if ($edit_arr['filled_by_user_id'] > 0) {
            $filled_by = 'this request was filled by ' . format_username($edit_arr['filled_by_user_id']);
        }
        $request_name = strip_tags(isset($_POST['request_name']) ? trim($_POST['request_name']) : $edit_arr['request_name']);
        $image = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : $edit_arr['image']);
        $body = (isset($_POST['body']) ? trim($_POST['body']) : $edit_arr['description']);
        $link = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : $edit_arr['link']);
        $category = (isset($_POST['category']) ? intval($_POST['category']) : $edit_arr['category']);
        $category_drop_down = '
                <select name="category" required><option value="">Select Request Category</option>';
        $cats = genrelist(true);
        foreach ($cats as $cat) {
            foreach ($cat['children'] as $row) {
                $category_drop_down .= "
                    <option value='{$row['id']}'" . ($category == $row['id'] ? ' selected' : '') . '>' . htmlsafechars($cat['name'], ENT_QUOTES) . '::' . htmlsafechars($row['name'], ENT_QUOTES) . '</option>';
            }
        }
        $category_drop_down .= '
                </select>';
        $HTMLOUT .= '<table class="table table-bordered table-striped">
   <tr>
   <td class="embedded">
   <h1 class="has-text-centered">Edit Request</h1>' . $top_menu . '
   <form method="post" action="' . $site_config['paths']['baseurl'] . '/requests.php?action=update_request" accept-charset="utf-8">
   <input type="hidden" name="id" value="' . $id . '">
   <table class="table table-bordered table-striped">
   <tr>
   <td colspan="2">Be sure to fill in all fields!</td>
   </tr>
   <tr>
   <td>name:</td>
   <td><input type="text" name="request_name" value="' . htmlsafechars($request_name, ENT_QUOTES) . '" class="w-100" required></td>
   </tr>
   <tr>
   <td>image:</td>
   <td><input type="url" name="image" value="' . htmlsafechars($image, ENT_QUOTES) . '" class="w-100" required></td>
   </tr>
   <tr>
   <td>link:</td>
   <td><input type="url" name="link" value="' . htmlsafechars($link, ENT_QUOTES) . '" class="w-100" required></td>
   </tr>
   <tr>
   <td>category:</td>
   <td>' . $category_drop_down . '</td>
   </tr>
   <tr>
   <td>description:</td>
   <td class="is-paddingless">' . BBcode($body) . '</td>
   </tr>' . ($edit_arr['filled_by_user_id'] == 0 ? '' : '
   <tr>
   <td>filled:</td>
   <td>' . $filled_by . ' <input type="checkbox" name="filled_by" value="1"' . (isset($_POST['filled_by']) ? ' "checked"' : '') . '> check this box to re-set this request. [ removes filled by ]  </td>
   </tr>') . '
   <tr>
   <td colspan="2">
    <div class="has-text-centered margin20">
        <input type="submit" name="button" class="button is-small" value="Edit">
    </div>
    </td>
   </tr>
   </table></form>
    </td></tr></table><br>';
        echo stdhead('Edit Request.', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'add_comment':
        if (!isset($id) || !is_valid_id($id)) {
            stderr('USER ERROR', 'Bad id');
        }
        $arr = $fluent->from('requests')
                      ->select(null)
                      ->select('request_name')
                      ->where('id=?', $id)
                      ->fetch();

        if (!$arr) {
            stderr('Error', 'No request with that ID.');
        }
        if (isset($_POST['button']) && $_POST['button'] === 'Save') {
            $body = trim($_POST['body']);
            if (!$body) {
                stderr('Error', 'Comment body cannot be empty!');
            }
            $values = [
                'user' => $CURUSER['id'],
                'request' => $id,
                'added' => TIME_NOW,
                'text' => $body,
                'ori_text' => $body,
            ];
            $newid = $fluent->insertInto('comments')
                            ->values($values)
                            ->execute();
            $set = [
                'comments' => new Envms\FluentPDO\Literal('comments + 1'),
            ];
            $fluent->update('requests')
                   ->set($set)
                   ->execute();
            header('Location: ' . $site_config['paths']['baseurl'] . '/requests.php?action=request_details&id=' . $id . '&viewcomm=' . $newid . '#comm' . $newid);
            die();
        }
        $body = htmlsafechars((isset($_POST['body']) ? $_POST['body'] : ''));
        $HTMLOUT .= $top_menu . '
    <form method="post" action="' . $site_config['paths']['baseurl'] . ' / requests.php?action=add_comment" accept-charset="utf-8">
        <input type="hidden" name="id" value="' . $id . '">
        <table class="table table-bordered table-striped">
            <tr>
                <td class="colhead" colspan="2"><h1>Add a comment to "' . htmlsafechars($arr['request_name'], ENT_QUOTES) . '"</h1></td>
            </tr>
            <tr>
                <td><b>Comment:</b></td>
                <td class="is-paddingless">' . BBcode($body) . '   </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="has-text-centered margin20">
                        <input name="button" type="submit" class="button is-small" value="Save">
                    </div>
                </td>
            </tr>
        </table>
    </form>';

        $allrows = $fluent->from('comments')
                          ->select('id AS comment_id')
                          ->where('request = ?', $id)
                          ->orderBy('id DESC')
                          ->limit(5)
                          ->fetchAll();

        if ($allrows) {
            $HTMLOUT .= '<h2>Most recent comments, in reverse order</h2>';
            $HTMLOUT .= commenttable($allrows, 'request');
        }
        echo stdhead('Add a comment to "' . $arr['request_name'] . '"', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'edit':
        require_once INCL_DIR . 'function_bbcode.php';
        if (!isset($comment_id) || !is_valid_id($comment_id)) {
            stderr('Error', 'Bad ID.');
        }
        $arr = $fluent->from('comments AS c')
                      ->select('r.request_name')
                      ->leftJoin('requests AS r ON c.request = r.id')
                      ->where('c.id=?', $comment_id)
                      ->fetch();

        if (!$arr) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        $body = htmlsafechars((isset($_POST['body']) ? $_POST['body'] : $arr['text']));
        if (isset($_POST['button']) && $_POST['button'] === 'Edit') {
            if ($body == '') {
                stderr('Error', 'Comment body cannot be empty!');
            }
            $set = [
                'text' => $body,
                'editedat' => TIME_NOW,
                'editedby' => $CURUSER['id'],
            ];
            $fluent->update('comments')
                   ->set($set)
                   ->where('id=?', $comment_id)
                   ->execute();
            header('Location: ' . $site_config['paths']['baseurl'] . '/requests.php?action=request_details&id=' . $id . '&viewcomm=' . $comment_id . '#comm' . $comment_id);
            die();
        }
        if ($CURUSER['id'] == $arr['user']) {
            $avatar = get_avatar($CURUSER);
        } else {
            $arr_user = $user_stuffs->getUserFromId($arr['user']);
            $avatar = get_avatar($arr_user);
        }
        $HTMLOUT .= $top_menu . '<form method="post" action="' . $site_config['paths']['baseurl'] . '/requests.php?action=edit" accept-charset="utf-8">
    <input type="hidden" name="id" value="' . $arr['request'] . '">
    <input type="hidden" name="cid" value="' . $comment_id . '">
    <table class="table table-bordered table-striped">
     <tr>
    <td colspan="2"><h1>Edit comment to "' . htmlsafechars($arr['request_name'], ENT_QUOTES) . '"</h1></td>
    </tr>
     <tr>
    <td><b>Comment:</b></td><td class="is-paddingless">' . BBcode($body) . '</td>
    </tr>
     <tr>
        <td colspan="2">
            <div class="has-text-centered margin20">
                <input name="button" type="submit" class="button is-small" value="Edit">
            </div>
        </td>
    </tr>
     </table></form>';
        echo stdhead('Edit comment to "' . $arr['request_name'] . '"', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'edit_comment':
        if (!isset($comment_id) || !is_valid_id($comment_id)) {
            stderr('Error', 'Bad ID.');
        }
        $arr = $fluent->from('requests')
                      ->select(null)
                      ->select('user')
                      ->select('request')
                      ->select('text')
                      ->where('id =?', $comment_id)
                      ->fetch();
        if (emtpy($arr)) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        $set = [
            'editedby' => $CURUSER['id'],
            'editedat' => TIME_NOW,
            'ori_text' => $arr['text'],
            'text' => $_POST['body'],
        ];
        $fluent->update('comments')
               ->set($set)
               ->where('id=?', $comment_id)
               ->execute();

        $session->set('is-success', 'Comment Edited Successfully.');
        header('Location: ' . $site_config['paths']['baseurl'] . '/requests.php?action=request_details&id=' . $id . '#comm' . $comment_id);
        die();
        break;

    case 'delete_comment':
        if (!isset($comment_id) || !is_valid_id($comment_id)) {
            stderr('Error', 'Bad ID.');
        }
        $arr = $fluent->from('comments')
                      ->select('user')
                      ->select('request')
                      ->where('id=?', $comment_id)
                      ->fetch();
        if (emtpy($arr)) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        if (!isset($_GET['do_it'])) {
            stderr('Sanity check...', 'are you sure you would like to delete this comment? If so click <a class="altlink" href="' . $site_config['paths']['baseurl'] . '/requests.php?action=delete_comment&amp;id=' . (int) $arr['request'] . ' &amp;comment_id=' . $comment_id . '&amp;do_it=666">HERE</a>.');
        } else {
            $fluent->deleteFrom('comments')
                   ->where('id=?', $comment_id)
                   ->execute();
            $set = [
                'comments' => new Envms\FluentPDO\Literal('comments - 1'),
            ];
            $fluent->update('requests')
                   ->set($set)
                   ->where('id=?', $arr['request'])
                   ->execute();

            header('Location: ' . $site_config['paths']['baseurl'] . '/requests.php?action=request_details&id=' . $id . '&comment_deleted=1');
            die();
        }
        break;

    case 'vieworiginal':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");
        }
        if (!is_valid_id($comment_id)) {
            stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");
        }
        $arr = $fluent->from('comments')
                      ->where('id=?', $comment_id)
                      ->fetch();

        if (!$arr) {
            stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']} $commentid . ");
        }
        $HTMLOUT = " < h1 class='has-text-centered'>{$lang['comment_original_content']}#$comment_id</h1>" . main_div("<div class='margin10 bg-02 round10 column'>" . format_comment(htmlsafechars($arr['ori_text'])) . '</div>');

        $returnto = (isset($_SERVER['HTTP_REFERER']) ? htmlsafechars($_SERVER['HTTP_REFERER']) : 0);
        if ($returnto) {
            $HTMLOUT .= "
                <div class='has-text-centered margin20'>
                    <a href='$returnto' class='button is-small has-text-black'>back</a>
                </div>";
        }
        echo stdhead("{$lang['comment_original']}", $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        die();
        break;
}
