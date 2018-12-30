<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'comment_functions.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();
global $CURUSER, $site_config, $user_stuffs, $fluent, $mysqli, $commentid;

$lang = array_merge(load_language('global'), load_language('comment'));
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
$offered_by_id = isset($_GET['offered_by_id']) ? intval($_GET['offered_by_id']) : 0;
$vote = isset($_POST['vote']) ? intval($_POST['vote']) : 0;
$posted_action = strip_tags((isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : '')));

$valid_actions = [
    'add_new_offer',
    'delete_offer',
    'edit_offer',
    'offer_details',
    'vote',
    'add_comment',
    'edit',
    'delete',
    'vieworiginal',
    'alter_status',
    'edit_comment',
    'delete_comment',
];
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'default');
$top_menu = '
    <div>
        <ul class="level-center bg-06 bottom20">
            <li class="altlink margin10">
                <a href="' . $site_config['baseurl'] . '/offers.php">View Offers</a>
            </li>
            <li class="altlink margin10">
                <a href="' . $site_config['baseurl'] . '/offers.php?action=add_new_offer">New Offer</a>
            </li>
        </ul>
    </div>';

switch ($action) {
    case 'vote':
        if (!isset($id) || !is_valid_id($id) || !isset($vote) || !is_valid_id($vote)) {
            stderr('USER ERROR', 'Bad id / bad vote');
        }
        $res_did_they_vote = sql_query('SELECT vote FROM offer_votes WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND offer_id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $row_did_they_vote = mysqli_fetch_row($res_did_they_vote);
        if ($row_did_they_vote[0] == '') {
            $yes_or_no = ($vote == 1 ? 'yes' : 'no');
            sql_query('INSERT INTO offer_votes (offer_id, user_id, vote) VALUES (' . sqlesc($id) . ', ' . sqlesc($CURUSER['id']) . ', \'' . $yes_or_no . '\')') or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE offers SET ' . ($yes_or_no === 'yes' ? 'vote_yes_count = vote_yes_count + 1' : 'vote_no_count = vote_no_count + 1') . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?action=offer_details&voted=1&id=' . $id);
            die();
        } else {
            stderr('USER ERROR', 'You have voted on this offer before.');
        }
        break;

    case 'default':
        $count_query = sql_query('SELECT COUNT(id) FROM offers') or sqlerr(__FILE__, __LINE__);
        $count_arr = mysqli_fetch_row($count_query);
        $count = $count_arr[0];
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
        $perpage = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 15;
        $link = $site_config['baseurl'] . '/offers.php?' . (isset($_GET['perpage']) ? "perpage={$perpage}&amp;" : '');
        $pager = pager($perpage, $count, $link);
        $menu_top = $pager['pagertop'];
        $menu_bottom = $pager['pagerbottom'];
        $LIMIT = $pager['limit'];

        $main_query_res = sql_query('SELECT o.id AS offer_id, o.offer_name, o.category, o.added, o.offered_by_user_id, o.vote_yes_count, o.vote_no_count, o.comments, o.status,
                                                    u.id, u.username, u.warned, u.suspended, u.enabled, u.donor, u.class,  u.leechwarn, u.chatpost, u.pirate, u.king,
                                                    c.id AS cat_id, c.name AS cat_name, c.image AS cat_image
                                                    FROM offers AS o
                                                    LEFT JOIN categories AS c ON o.category = c.id
                                                    LEFT JOIN users AS u ON o.offered_by_user_id = u.id
                                                    ORDER BY o.added DESC ' . $LIMIT) or sqlerr(__FILE__, __LINE__);
        if ($count === 0) {
            stderr('Error!', 'Sorry, there are no current offers!');
        }
        $HTMLOUT .= (isset($_GET['new']) ? '<h1>Offer Added!</h1>' : '') . (isset($_GET['offer_deleted']) ? '<h1>Offer Deleted!</h1>' : '') . $top_menu . '' . ($count > $perpage ? $menu_top : '');
        $heading = '
        <tr>
            <th>Type</th>
            <th>Name</th>
            <th>Added</th>
            <th>Comm</th>
            <th>Votes</th>
            <th>Offered By</th>
            <th>Status</th>
        </tr>';
        $body = '';
        while ($main_query_arr = mysqli_fetch_assoc($main_query_res)) {
            $status = ($main_query_arr['status'] == 'approved' ? '<span>Approved!</span>' : ($main_query_arr['status'] === 'pending' ? '<span>Pending...</span>' : '<span>denied</span>'));
            $body .= '
        <tr>
            <td><img src="' . $site_config['pic_baseurl'] . 'caticons/' . get_category_icons() . '/' . htmlsafechars($main_query_arr['cat_image'], ENT_QUOTES) . '" alt="' . htmlsafechars($main_query_arr['cat_name'], ENT_QUOTES) . '"></td>
            <td><a class="altlink" href="' . $site_config['baseurl'] . '/offers.php?action=offer_details&amp;id=' . $main_query_arr['offer_id'] . '">' . htmlsafechars($main_query_arr['offer_name'], ENT_QUOTES) . '</a></td>
            <td>' . get_date($main_query_arr['added'], 'LONG') . '</td>
            <td>' . number_format($main_query_arr['comments']) . '</td>
            <td>yes: ' . number_format($main_query_arr['vote_yes_count']) . '<br>
            no: ' . number_format($main_query_arr['vote_no_count']) . '</td>
            <td>' . format_username($main_query_arr['id']) . '</td>
            <td>' . $status . '</td>
        </tr>';
        }
        $HTMLOUT .= !empty($body) ? main_table($body, $heading) : main_div('<div class="padding20 has-text-centered">There are no offers</div>');
        $HTMLOUT .= $count > $perpage ? $menu_bottom : '';

        echo stdhead('Offers', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'offer_details':
        if (!isset($id) || !is_valid_id($id)) {
            stderr('USER ERROR', 'Bad id');
        }
        $arr = $fluent->from('offers AS o')
            ->select('o.id AS offer_id')
            ->select('c.name AS cat_name')
            ->select('c.image AS cat_image')
            ->leftJoin('categories AS c ON o.category = c.id')
            ->where('o.id = ?', $id)
            ->fetch();

        if (!empty($arr['link'])) {
            preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $arr['link'], $imdb);
            $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        }
        $movie_info = get_imdb_info($imdb, false, false, null, null);

        $row_did_they_vote = $fluent->from('offer_votes')
            ->select(null)
            ->select('vote')
            ->where('user_id = ?', $CURUSER['id'])
            ->where('offer_id = ?', $id)
            ->fetch();

        if (!$row_did_they_vote) {
            $vote_yes = '<form method="post" action="' . $site_config['baseurl'] . '/offers.php">
                    <input type="hidden" name="action" value="vote">
                    <input type="hidden" name="id" value="' . $id . '">
                    <input type="hidden" name="vote" value="1">
                    <input type="submit" class="button is-small" value="vote yes!">
                    </form> ~ you will be notified when this offer is filled.';
            $vote_no = '<form method="post" action="' . $site_config['baseurl'] . '/offers.php">
                    <input type="hidden" name="action" value="vote">
                    <input type="hidden" name="id" value="' . $id . '">
                    <input type="hidden" name="vote" value="2">
                    <input type="submit" class="button is-small" value="vote no!">
                    </form> ~ you are being a stick in the mud.';
            $your_vote_was = '';
        } else {
            $vote_yes = '';
            $vote_no = '';
            $your_vote_was = ' your vote: ' . $row_did_they_vote[0] . ' ';
        }
        $status_drop_down = ($CURUSER['class'] < UC_STAFF ? '' : '<br><form method="post" action="' . $site_config['baseurl'] . '/offers.php">
                    <input type="hidden" name="action" value="alter_status">
                    <input type="hidden" name="id" value="' . $id . '">
                    <select name="set_status">
                    <option value="pending"' . ($arr['status'] == 'pending' ? ' selected' : '') . '>Status: pending</option>
                    <option value="approved"' . ($arr['status'] == 'approved' ? ' selected' : '') . '>Status: approved</option>
                    <option value="denied"' . ($arr['status'] == 'denied' ? ' selected' : '') . '>Status: denied</option>
                    </select>
                    <input type="submit" class="button is-small" value="change status!">
                    </form> ');
        $usersdata = $user_stuffs->getUserFromId($arr['offered_by_user_id']);
        $HTMLOUT .= '<div class="has-text-centered">' . (isset($_GET['status_changed']) ? '<h1>Offer Status Updated!</h1>' : '') . (isset($_GET['voted']) ? '<h1>vote added</h1>' : '') . (isset($_GET['comment_deleted']) ? '<h1>comment deleted</h1>' : '') . $top_menu . ($arr['status'] === 'approved' ? '<span>status: approved!</span>' : ($arr['status'] === 'pending' ? '<span>status: pending...</span>' : '<span>status: denied</span>')) . $status_drop_down . '</div><br><br>
    <table class="table table-bordered table-striped">
    <tr>
    <td colspan="2"><h1>' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . ($CURUSER['class'] < UC_STAFF ? '' : ' [ <a href="offers.php?action=edit_offer&amp;id=' . $id . '">edit</a> ]
    [ <a href="offers.php?action=delete_offer&amp;id=' . $id . '">delete</a> ]') . '</h1></td>
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
    <td><img src="' . $site_config['pic_baseurl'] . 'caticons/' . get_category_icons() . '/' . htmlsafechars($arr['cat_image'], ENT_QUOTES) . '" alt="' . htmlsafechars($arr['cat_name'], ENT_QUOTES) . '"></td>
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
    <td>offered by:</td>
    <td>' . format_username($usersdata['id']) . ' [ ' . get_user_class_name($usersdata['class']) . ' ]
    ratio: ' . member_ratio($usersdata['uploaded'], RATIO_FREE ? '0' : $usersdata['downloaded']) . get_user_ratio_image((RATIO_FREE ? 1 : $usersdata['uploaded'] / ($usersdata['downloaded'] == 0 ? 1 : $usersdata['downloaded']))) . '</td>
    </tr>
    <tr>
    <td>Report Offer</td>
    <td><form action="' . $site_config['baseurl'] . '/report.php?type=Offer&amp;id=' . $id . '" method="post">
    <input type="submit" class="button is-small" value="Report This Offer">
    For breaking the <a class="altlink" href="rules.php">rules</a></form></td>
    </tr>
    </table>';
        $HTMLOUT .= '
            <h1 class="has-text-centered">Comments for ' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '</h1>
            <a id="startcomments"></a>
            <div class="has-text-centered">
                <a class="button is-small" href="offers.php?action=add_comment&amp;id=' . $id . '">Add a comment</a>
            </div>';
        $count = (int) $arr['comments'];
        if (!$count) {
            $HTMLOUT .= main_div('<h2>No comments yet</h2>', 'top20 has-text-centered');
        } else {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
            $perpage = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 15;
            $link = $site_config['baseurl'] . "/offers.php?action=offer_details&amp;id=$id" . (isset($_GET['perpage']) ? "perpage={$perpage}&amp;" : '');
            $pager = pager($perpage, $count, $link);
            $menu_top = $pager['pagertop'];
            $menu_bottom = $pager['pagerbottom'];

            $allrows = $fluent->from('comments')
                ->select('id AS comment_id')
                ->where('offer = ?', $id)
                ->orderBy('id DESC')
                ->limit("{$pager['pdo']}")
                ->fetchAll();
            $HTMLOUT .= '<a id="comments"></a>';
            $HTMLOUT .= ($count > $perpage ? $menu_top : '') . '<br>';
            $HTMLOUT .= commenttable($allrows, 'offer');
            $HTMLOUT .= ($count > $perpage ? $menu_bottom : '');
        }
        echo stdhead('Offer details for: ' . htmlsafechars($arr['offer_name'], ENT_QUOTES), $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'add_new_offer':
        $offer_name = strip_tags(isset($_POST['offer_name']) ? trim($_POST['offer_name']) : '');
        $image = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : '');
        $body = (isset($_POST['body']) ? trim($_POST['body']) : '');
        $link = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : '');
        $category_drop_down = '
                <select name="category" required><option value="">Select Offer Category</option>';
        $cats = genrelist(true);
        foreach ($cats as $cat) {
            foreach ($cat['children'] as $row) {
                $category_drop_down .= "
                    <option value='{$row['id']}'" . ($category == $row['id'] ? ' selected' : '') . '>' . htmlsafechars($cat['name']) . '::' . htmlsafechars($row['name']) . '</option>';
            }
        }
        $category_drop_down .= '
                </select>';
        if (isset($_POST['category'])) {
            $cat_res = sql_query('SELECT id AS cat_id, name AS cat_name, image AS cat_image FROM categories WHERE id = ' . sqlesc($category)) or sqlerr(__FILE__, __LINE__);
            $cat_arr = mysqli_fetch_assoc($cat_res);
            $cat_image = htmlsafechars($cat_arr['cat_image'], ENT_QUOTES);
            $cat_name = htmlsafechars($cat_arr['cat_name'], ENT_QUOTES);
        }
        if (isset($_POST['button']) && $_POST['button'] === 'Submit') {
            sql_query('INSERT INTO offers (offer_name, image, description, category, added, offered_by_user_id, link) VALUES (' . sqlesc($offer_name) . ', ' . sqlesc($image) . ', ' . sqlesc($body) . ', ' . sqlesc($category) . ', ' . TIME_NOW . ', ' . sqlesc($CURUSER['id']) . ',  ' . sqlesc($link) . ');') or sqlerr(__FILE__, __LINE__);
            $new_offer_id = ((is_null($___mysqli_res = mysqli_insert_id($mysqli))) ? false : $___mysqli_res);
            header('Location: offers.php?action=offer_details&new=1&id=' . $new_offer_id);
            die();
        }
        $HTMLOUT .= $top_menu . '
    <h1 class="has-text-centered">New Offer</h1>
    <div class="banner_container has-text-centered w-100"></div>
    <form method="post" action="' . $site_config['baseurl'] . '/offers.php?action=add_new_offer">
    <table class="table table-bordered table-striped">
    <tbody>
    <tr>
    <td colspan="2">Before you make an offer, <a class="altlink" href="search.php">Search</a>
    to be sure it has not yet been requested, offered, or uploaded!<br><br>
    Be sure to fill in all fields!
    <div class="has-text-centered error size_6 margin20"><span></span></div>
    </td>
    </tr>
    <tr>
    <td>name:</td>
    <td><input type="text" name="offer_name" value="' . htmlsafechars($offer_name, ENT_QUOTES) . '" class="w-100" required></td>
    </tr>
    <tr>
    <td>link:</td>
    <td>
        <input type="url" id="url" name="link" class="w-100" data-csrf="' . $session->get('csrf_token') . '" value="' . htmlsafechars($link, ENT_QUOTES) . '" required>
        <div class="imdb_outer">
            <div class="imdb_inner">
            </div>
        </div>
    </td>
    </tr>
    <tr>
    <td>image:</td>
    <td>
        <input type="url" id="poster" name="image" value="' . htmlsafechars($image, ENT_QUOTES) . '" class="w-100" require>
        <div class="poster_container has-text-centered"></div>
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
    <td colspan="2" class="has-text-centered">
    <input type="submit" name="button" class="button is-small" value="Submit"></td>
    </tr>
    </tbody>
    </table></form>
     </td></tr></table>';
        echo stdhead('Add new offer.', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'delete_offer':
        if (!isset($id) || !is_valid_id($id)) {
            stderr('Error', 'Bad ID.');
        }
        $res = sql_query('SELECT offer_name, offered_by_user_id FROM offers WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (!$arr) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['offered_by_user_id'] !== $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        if (!isset($_GET['do_it'])) {
            stderr('Sanity check...', 'are you sure you would like to delete the offer <b>"' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '"</b>? If so click
        <a class="altlink" href="offers.php?action=delete_offer&id=' . $id . '&amp;do_it=666" >HERE</a>.');
        } else {
            sql_query('DELETE FROM offers WHERE id = ' . $id) or sqlerr(__FILE__, __LINE__);
            sql_query('DELETE FROM offer_votes WHERE offer_id = ' . $id) or sqlerr(__FILE__, __LINE__);
            sql_query('DELETE FROM comments WHERE offer = ' . $id) or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?offer_deleted=1');
            die();
        }
        echo stdhead('Delete Offer.', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'edit_offer':
        require_once INCL_DIR . 'bbcode_functions.php';
        if (!isset($id) || !is_valid_id($id)) {
            stderr('Error', 'Bad ID.');
        }
        $edit_res = sql_query('SELECT offer_name, image, description, category, offered_by_user_id, link FROM offers WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $edit_arr = mysqli_fetch_assoc($edit_res);
        if ($CURUSER['class'] < UC_STAFF && $CURUSER['id'] !== $edit_arr['offered_by_user_id']) {
            stderr('Error!', 'This is not your offer to edit!');
        }
        $offer_name = strip_tags(isset($_POST['offer_name']) ? trim($_POST['offer_name']) : $edit_arr['offer_name']);
        $image = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : $edit_arr['image']);
        $body = (isset($_POST['body']) ? trim($_POST['body']) : $edit_arr['description']);
        $link = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : $edit_arr['link']);
        $category = (isset($_POST['category']) ? intval($_POST['category']) : $edit_arr['category']);
        $category_drop_down = '
                <select name="category" required><option value="">Select Offer Category</option>';
        $cats = genrelist(true);
        foreach ($cats as $cat) {
            foreach ($cat['children'] as $row) {
                $category_drop_down .= "
                    <option value='{$row['id']}'" . ($category == $row['id'] ? ' selected' : '') . '>' . htmlsafechars($cat['name']) . '::' . htmlsafechars($row['name']) . '</option>';
            }
        }
        $category_drop_down .= '
                </select>';
        $cat_res = sql_query('SELECT id AS cat_id, name AS cat_name, image AS cat_image FROM categories WHERE id = ' . sqlesc($category)) or sqlerr(__FILE__, __LINE__);
        $cat_arr = mysqli_fetch_assoc($cat_res);
        $cat_image = htmlsafechars($cat_arr['cat_image'], ENT_QUOTES);
        $cat_name = htmlsafechars($cat_arr['cat_name'], ENT_QUOTES);
        if (isset($_POST['button']) && $_POST['button'] === 'Edit') {
            sql_query('UPDATE offers SET offer_name = ' . sqlesc($offer_name) . ', image = ' . sqlesc($image) . ', description = ' . sqlesc($body) . ', category = ' . sqlesc($category) . ', link = ' . sqlesc($link) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            header('Location: offers.php?action=offer_details&edited=1&id=' . $id);
            die();
        }
        $HTMLOUT .= '<table class="table table-bordered table-striped">
    <tr>
    <td class="embedded">
    <h1 class="has-text-centered">Edit Offer</h1>' . $top_menu . '
    <form method="post" action="' . $site_config['baseurl'] . '/offers.php?action=edit_offer" name="offer_form" id="offer_form">
    <input type="hidden" name="id" value="' . $id . '">
    <table class="table table-bordered table-striped">
    <tr>
    <td colspan="2">Be sure to fill in all fields!</td>
    </tr>
    <tr>
    <td>name:</td>
    <td><input type="text" name="offer_name" value="' . htmlsafechars($offer_name, ENT_QUOTES) . '" class="w-100" required></td>
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
    </tr>
    <tr>
    <td colspan="2" class="has-text-centered">
    <input type="submit" name="button" class="button is-small" value="Edit"></td>
    </tr>
    </table></form>
     </td></tr></table><br>';
        echo stdhead('Edit Offer.', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'add_comment':
        if (!isset($id) || !is_valid_id($id)) {
            stderr('USER ERROR', 'Bad id');
        }
        $arr = $fluent->from('offers')
            ->select(null)
            ->select('offer_name')
            ->where('id = ?', $id)
            ->fetch();

        if (!$arr) {
            stderr('Error', 'No offer with that ID.');
        }
        if (isset($_POST['button']) && $_POST['button'] === 'Save') {
            $body = trim($_POST['body']);
            if (!$body) {
                stderr('Error', 'Comment body cannot be empty!');
            }
            sql_query('INSERT INTO comments (user, offer, added, text, ori_text) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($id) . ', ' . TIME_NOW . ', ' . sqlesc($body) . ',' . sqlesc($body) . ')') or sqlerr(__FILE__, __LINE__);
            $newid = ((is_null($___mysqli_res = mysqli_insert_id($mysqli))) ? false : $___mysqli_res);
            sql_query('UPDATE offers SET comments = comments + 1 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?action=offer_details&id=' . $id . '&viewcomm=' . $newid . '#comm' . $newid);
            die();
        }
        $body = htmlsafechars((isset($_POST['body']) ? $_POST['body'] : ''));
        $HTMLOUT .= $top_menu . '
    <form method="post" action="' . $site_config['baseurl'] . '/offers.php?action=add_comment">
        <input type="hidden" name="id" value="' . $id . '">
        <table class="table table-bordered table-striped">
            <tr>
                <td colspan="2"><h1>Add a comment to "' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '"</h1></td>
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
            ->where('offer = ?', $id)
            ->orderBy('id DESC')
            ->limit(5)
            ->fetchAll();

        if ($allrows) {
            $HTMLOUT .= '<h2>Most recent comments, in reverse order</h2>';
            $HTMLOUT .= commenttable($allrows, 'offer');
        }
        echo stdhead('Add a comment to "' . htmlsafechars($arr['offer_name']) . '"', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'edit':
        require_once INCL_DIR . 'bbcode_functions.php';
        if (!isset($comment_id) || !is_valid_id($comment_id)) {
            stderr('Error', 'Bad ID.');
        }
        $res = sql_query('SELECT c.*, o.offer_name FROM comments AS c LEFT JOIN offers AS o ON c.offer = o.id WHERE c.id = ' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
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
            sql_query('UPDATE comments SET text = ' . sqlesc($body) . ', editedat = ' . TIME_NOW . ', editedby = ' . sqlesc($CURUSER['id']) . ' WHERE id = ' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?action=offer_details&id=' . $id . '&viewcomm=' . $comment_id . '#comm' . $comment_id);
            die();
        }
        if ($CURUSER['id'] == $arr['user']) {
            $avatar = get_avatar($CURUSER);
        } else {
            $arr_user = $user_stuffs->getUserFromId($arr['user']);
            $avatar = get_avatar($arr_user);
        }

        $HTMLOUT .= $top_menu . '<form method="post" action="' . $site_config['baseurl'] . '/offers.php?action=edit_comment">
    <input type="hidden" name="id" value="' . $arr['offer'] . '">
    <input type="hidden" name="comment_id" value="' . $comment_id . '">
    <table class="table table-bordered table-striped">
     <tr>
    <td colspan="2"><h1>Edit comment to "' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '"</h1></td>
    </tr>
     <tr>
    <td><b>Comment:</b></td>
    <td class="is-paddingless">' . BBcode($body) . '</td>
    </tr>
     <tr>
    <td colspan="2">
            <div class="has-text-centered margin20">
                <input name="button" type="submit" class="button is-small" value="Edit">
            </div>
        </td>
    </tr>
     </table></form>';
        echo stdhead('Edit comment to "' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '"', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;

    case 'edit_comment':
        if (!isset($comment_id) || !is_valid_id($comment_id)) {
            stderr('Error', 'Bad ID.');
        }
        $res = sql_query('SELECT user, offer FROM comments WHERE id = ' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (!$arr) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        sql_query('UPDATE comments set editedby = ' . sqlesc($CURUSER['id']) . ', editedat = ' . sqlesc(TIME_NOW) . ', ori_text = text, text = ' . sqlesc($_POST['body']) . ' WHERE id = ' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
        $session->set('is-success', 'Comment Edited Successfully.');
        header('Location: /offers.php?action=offer_details&id=' . $id . '#comm' . $comment_id);
        die();
        break;

    case 'delete_comment':
        if (!isset($comment_id) || !is_valid_id($comment_id)) {
            stderr('Error', 'Bad ID.');
        }
        $res = sql_query('SELECT user, offer FROM comments WHERE id = ' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (!$arr) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        if (!isset($_GET['do_it'])) {
            stderr('Sanity check...', 'are you sure you would like to delete this comment? If so click
        <a class="altlink" href="offers.php?action=delete_comment&amp;id=' . (int) $arr['offer'] . '&amp;comment_id=' . $comment_id . '&amp;do_it=666" >HERE</a>.');
        } else {
            sql_query('DELETE FROM comments WHERE id = ' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE offers SET comments = comments - 1 WHERE id = ' . sqlesc($arr['offer'])) or sqlerr(__FILE__, __LINE__);
            $session->set('is-success', 'Comment Deleted');
            header('Location: /offers.php?action=offer_details&id=' . $id . '&comment_deleted=1');
            die();
        }
        break;

    case 'alter_status':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        $set_status = strip_tags(isset($_POST['set_status']) ? $_POST['set_status'] : '');
        $ok_stuff = [
            'approved',
            'pending',
            'denied',
        ];
        $change_it = (in_array($set_status, $ok_stuff) ? $set_status : 'poop');
        if ($change_it === 'poop') {
            stderr('Error', 'Nice try Mr. Fancy Pants!');
        }
        $res_name = sql_query('SELECT offer_name, offered_by_user_id FROM offers WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr_name = mysqli_fetch_assoc($res_name);
        if ($change_it === 'approved') {
            $subject = sqlesc('Your Offer has been approved!');
            $message = sqlesc("Hi, \n An offer you made has been approved!!! \n\n Please  [url=" . $site_config['baseurl'] . '/upload.php]Upload ' . htmlsafechars($arr_name['offer_name'], ENT_QUOTES) . "[/url] as soon as possible! \n Members who voted on it will be notified as soon as you do! \n\n [url=" . $site_config['baseurl'] . '/offers.php?action=offer_details&id=' . $id . ']HERE[/url] is your offer.');
            sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location) VALUES (0, ' . sqlesc($arr_name['offered_by_user_id']) . ', ' . TIME_NOW . ', ' . $message . ', ' . $subject . ', \'yes\', 1)') or sqlerr(__FILE__, __LINE__);
        }
        if ($change_it === 'denied') {
            $subject = sqlesc('Your Offer has been denied!');
            $message = sqlesc("Hi, \n An offer you made has been denied. \n\n  [url=" . $site_config['baseurl'] . '/offers.php?action=offer_details&id=' . $id . ']' . htmlsafechars($arr_name['offer_name'], ENT_QUOTES) . '[/url] was denied by ' . $CURUSER['username'] . '. Please contact them to find out why.');
            sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location) VALUES (0, ' . sqlesc($arr_name['offered_by_user_id']) . ', ' . TIME_NOW . ', ' . $message . ', ' . $subject . ', \'yes\', 1)') or sqlerr(__FILE__, __LINE__);
        }
        sql_query('UPDATE offers SET status = ' . sqlesc($change_it) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        header('Location: /offers.php?action=offer_details&status_changed=1&id=' . $id);
        die();
        break;

    case 'vieworiginal':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");
        }
        if (!is_valid_id($comment_id)) {
            stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");
        }
        $arr = $fluent->from('comments')
            ->where('id = ?', $comment_id)
            ->fetch();

        if (!$arr) {
            stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']} $commentid.");
        }
        $HTMLOUT = "
            <h1 class='has-text-centered'>{$lang['comment_original_content']}#$comment_id</h1>" . main_div("<div class='margin10 bg-02 round10 column'>" . format_comment(htmlsafechars($arr['ori_text'])) . '</div>');

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
