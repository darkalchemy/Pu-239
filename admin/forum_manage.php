<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Forum;

require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_forum_manage'));
global $container, $site_config, $CURUSER;

$HTMLOUT = $options = $options_2 = $options_3 = $options_4 = $options_5 = $options_6 = $option_7 = $option_8 = $option_9 = $option_10 = $option_11 = $option_12 = $count = $forums_stuff = '';
$row = 0;
$maxclass = $CURUSER['class'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$name = isset($_POST['name']) ? htmlsafechars($_POST['name']) : '';
$desc = isset($_POST['desc']) ? htmlsafechars($_POST['desc']) : '';
$sort = isset($_POST['sort']) ? (int) $_POST['sort'] : 0;
$parent_forum = isset($_POST['parent_forum']) ? (int) $_POST['parent_forum'] : 0;
$over_forums = isset($_POST['over_forums']) ? (int) $_POST['over_forums'] : 0;
$min_class_read = isset($_POST['min_class_read']) ? (int) $_POST['min_class_read'] : 0;
$min_class_write = isset($_POST['min_class_write']) ? (int) $_POST['min_class_write'] : 0;
$min_class_create = isset($_POST['min_class_create']) ? (int) $_POST['min_class_create'] : 0;
$main_links = "
            <div class='bottom20'>
                <ul class='level-center bg-06'>
                    <li class='is-link margin10'>
                        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=over_forums&amp;action=over_forums'>{$lang['fm_overforum']}</a>
                    </li>
                    <li class='is-link margin10'>
                        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=forum_config&amp;action=forum_config'>{$lang['fm_configure']}</a>
                    </li>
                </ul>
            </div>
            <h1 class='has-text-centered'>{$lang['fm_forummanager']}</h1>";

$posted_action = (isset($_GET['action2']) ? htmlsafechars($_GET['action2']) : (isset($_POST['action2']) ? htmlsafechars($_POST['action2']) : ''));
$valid_actions = [
    'delete',
    'edit_forum',
    'add_forum',
    'edit_forum_page',
];
$action = in_array($posted_action, $valid_actions) ? $posted_action : 'no_action';
$fluent = $container->get(Database::class);
$forum_class = $container->get(Forum::class);
switch ($action) {
    case 'delete':
        if (!$id) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=forum_manage&action=forum_manage');
            die();
        }
        $forum_class->delete($id);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=forum_manage&action=forum_manage');
        die();
        break;

    case 'edit_forum':
        if (!$name && !$desc && !$id) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=forum_manage&action=forum_manage');
            die();
        }
        $set = [
            'sort' => $sort,
            'name' => $name,
            'parent_forum' => $parent_forum,
            'description' => $desc,
            'forum_id' => $over_forums,
            'min_class_read' => $min_class_read,
            'min_class_write' => $min_class_write,
            'min_class_create' => $min_class_create,
        ];
        $forum_class->update($set, $id);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=forum_manage&action=forum_manage');
        die();
        break;

    case 'add_forum':
        if (!$name && !$desc) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=forum_manage&action=forum_manage');
            die();
        }
        $values = [
            'sort' => $sort,
            'name' => $name,
            'parent_forum' => $parent_forum,
            'description' => $desc,
            'min_class_read' => $min_class_read,
            'min_class_write' => $min_class_write,
            'min_class_create' => $min_class_create,
            'forum_id' => $over_forums,
        ];
        $forum_class->add($values);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=forum_manage&action=forum_manage');
        die();
        break;

    case 'edit_forum_page':
        $forum = $forum_class->get_forum($id);
        if (!empty($forum)) {
            $HTMLOUT .= $main_links . '
            <form method="post" action="' . $_SERVER['PHP_SELF'] . '?tool=forum_manage&amp;action=forum_manage" accept-charset="utf-8">';
            $body = "
                    <tr>
                        <td colspan='2'>{$lang['fm_efp_edit']} " . htmlsafechars($forum['name']) . "</td>
                    </tr>
                    <tr>
                        <td>{$lang['fm_efp_name']}</td>
                        <td><input name='name' type='text' class='w-100' maxlength='60' value='" . htmlsafechars($forum['name']) . "'></td>
                    </tr>
                    <tr>
                        <td>{$lang['fm_efp_description']}</td>
                        <td><input name='desc' type='text' class='w-100' maxlength='200' value='" . htmlsafechars($forum['description']) . "'></td>
                    </tr>
                    <tr>
                        <td>{$lang['fm_efp_over']}</td>
                        <td>
                            <select name='over_forums'>";
            $query = $fluent->from('over_forums');
            foreach ($query as $arr) {
                $body .= "
                                <option class='body' value='{$arr['id']}' " . ($forum['forum_id'] === $arr['id'] ? 'selected' : '') . '>' . htmlsafechars($arr['name']) . '</option>';
            }
            $body .= "
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['fm_efp_sub']}</td>
                        <td>
                            <select name='parent_forum'>
                                <option class='body' value='0' " . ($parent_forum === 0 ? 'selected' : '') . '>' . $lang['fm_efp_select'] . '</option>';
            $query = $fluent->from('forums')
                            ->select(null)
                            ->select('id')
                            ->select('name');

            foreach ($query as $arr) {
                if (is_valid_id($arr['id'])) {
                    $body .= "
                                <option class='body' value='{$arr['id']}' " . ($parent_forum === $arr['id'] ? 'selected' : '') . '>' . htmlsafechars($arr['name']) . '</option>';
                }
            }
            $body .= "
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['fm_efp_min_r']}</td>
                        <td>
                            <select name='min_class_read'>";
            for ($i = 0; $i <= $maxclass; ++$i) {
                $body .= "
                                <option class='body' value='{$i}' " . ($forum['min_class_read'] === $i ? 'selected' : '') . '>' . get_user_class_name((int) $i) . '</option>';
            }
            $body .= "
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['fm_efp_min_w']}</td>
                        <td>
                            <select name='min_class_write'>";
            for ($i = 0; $i <= $maxclass; ++$i) {
                $body .= "
                                <option class='body' value='{$i}' " . ($forum['min_class_write'] === $i ? 'selected' : '') . '>' . get_user_class_name((int) $i) . '</option>';
            }
            $body .= "
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['fm_efp_min_c']}</td>
                        <td>
                            <select name='min_class_create'>";
            for ($i = 0; $i <= $maxclass; ++$i) {
                $body .= "
                                <option class='body' value='{$i}' " . ($forum['min_class_create'] === $i ? 'selected' : '') . '>' . get_user_class_name((int) $i) . '</option>';
            }
            $body .= "
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['fm_efp_rank']}</td>
                        <td>
                            <select name='sort'>";
            $count = $forum_class->get_count();
            $maxclass = $count++;
            for ($i = 0; $i <= $maxclass; ++$i) {
                $body .= "
                                <option class='body' value='{$i}' " . ($forum['sort'] === $i ? 'selected' : '') . ">$i</option>";
            }
            $body .= '
                            </select>
                        </td>
                    </tr>
                </table>
            <div class="has-text-centered margin20">
                <input type="hidden" name="action2" value="edit_forum">
                <input type="hidden" name="id" value="' . $id . '">
                <input type="submit" name="button" class="button is-small margin20" value="' . $lang['fm_efp_btn'] . '">
            </div>
        </form>';
            $HTMLOUT .= main_table($body);
        }
        break;
}

$HTMLOUT .= $main_links;
$heading = ' 
        <tr>
            <th>' . $lang['fm_mp_name'] . '</th>
            <th>' . $lang['fm_mp_sub'] . '</th>
            <th>' . $lang['fm_mp_over'] . '</th>
            <th>' . $lang['fm_mp_read'] . '</th>
            <th>' . $lang['fm_mp_write'] . '</th>
            <th>' . $lang['fm_mp_create'] . '</th>
            <th>' . $lang['fm_mp_modify'] . '</th>
        </tr>';
$forums = $fluent->from('forums AS f')
                 ->select('o.name AS parent_name')
                 ->select('s.name AS subforum_name')
                 ->leftJoin('over_forums AS o ON f.forum_id = o.id')
                 ->leftJoin('forums AS s ON f.parent_forum = s.id')
                 ->orderBy('f.forum_id')
                 ->fetchAll();
$body = '';

foreach ($forums as $row) {
    $forum_id = $row['forum_id'];
    $name = !empty($row['parent_name']) ? htmlsafechars($row['parent_name']) : '';
    $subforum = $row['parent_forum'];
    $subforum_name = !empty($row['subforum_name']) ? htmlsafechars($row['subforum_name']) : '';
    $body .= '
        <tr>
            <td><a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . (int) $row['id'] . '">
                <span>' . htmlsafechars($row['name']) . '</span></a><br>
                    ' . htmlsafechars($row['description']) . '
            </td>
            <td><span>' . $subforum_name . '</span></td>
            <td>' . $name . '</td>
            <td>' . get_user_class_name((int) $row['min_class_read']) . '</td>
            <td>' . get_user_class_name((int) $row['min_class_write']) . '</td>
            <td>' . get_user_class_name($row['min_class_create']) . '</td>
            <td class="has-text-centered">
                <span class="level-center">
                    <span class="left10 tooltipper" title="Edit">
                        <a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=forum_manage&amp;action=forum_manage&amp;action2=edit_forum_page&amp;id=' . (int) $row['id'] . '">
                            <i class="icon-edit icon has-text-info" aria-hidden="true"></i>
                        </a>
                    </span>
                    <span class="tooltipper" title="Delete">
                        <a href="javascript:confirm_delete(\'' . $row['id'] . '\');">
                            <i class="icon-cancel icon has-text-danger" aria-hidden="true"></i>
                        </a>
                    </span>
                </span>
            </td>
        </tr>';
}

$HTMLOUT .= main_table($body, $heading) . '<br><br>
            <form method="post" action="' . $_SERVER['PHP_SELF'] . '?tool=forum_manage&amp;action=forum_manage" accept-charset="utf-8">';
$body = '
            <tr>
                <td colspan="2">' . $lang['fm_mp_make'] . '</td>
            </tr>
            <tr>
                <td>' . $lang['fm_mp_fname'] . '</td>
                <td><input name="name" type="text" class="w-100" maxlength="60"></td>
            </tr>
            <tr>
                <td>' . $lang['fm_mp_description'] . '</td>
                <td><input name="desc" type="text" class="w-100" maxlength="200"></td>
            </tr>
            <tr>
                <td>' . $lang['fm_mp_over2'] . '</td>
                <td>
                    <select name="over_forums">';

$query = $fluent->from('over_forums');
foreach ($query as $arr) {
    $body .= "
                        <option class='body' value='{$arr['id']}'>" . htmlsafechars($arr['name']) . '</option>';
}
$body .= '
                    </select>
                </td>
            </tr>
            <tr>
                <td>' . $lang['fm_mp_sub2'] . '</td>
                <td>
                    <select name="parent_forum">
                        <option class="body" value="0">' . $lang['fm_mp_none'] . '</option>';
$query = $fluent->from('forums');
foreach ($query as $arr) {
    $body .= '
                        <option class="body" value="' . $arr['id'] . '">' . htmlsafechars($arr['name']) . '</option>';
}
$body .= '
                    </select>
                </td>
            </tr>
            <tr>
                <td>' . $lang['fm_mp_min_r'] . '</td>
                <td>
                    <select name="min_class_read">';
for ($i = 0; $i <= $maxclass; ++$i) {
    $body .= '
                        <option class="body" value="' . $i . '">' . get_user_class_name($i) . '</option>';
}
$body .= '
                    </select>
                </td>
            </tr>
            <tr>
                <td>' . $lang['fm_mp_min_w'] . '</td>
                <td>
                    <select name="min_class_write">';
for ($i = 0; $i <= $maxclass; ++$i) {
    $body .= '
                        <option class="body" value="' . $i . '">' . get_user_class_name($i) . '</option>';
}
$body .= '
                    </select>
                </td>
            </tr>
            <tr>
                <td>' . $lang['fm_mp_min_c'] . '</td>
                <td>
                    <select name="min_class_create">';
for ($i = 0; $i <= $maxclass; ++$i) {
    $body .= '
                        <option class="body" value="' . $i . '">' . get_user_class_name($i) . '</option>';
}
$body .= '
                    </select>
                </td>
            </tr>
            <tr>
                <td>' . $lang['fm_mp_rank'] . '</td>
                <td>
                    <select name="sort">';
$count = $fluent->from('forums')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->fetch('count');
$maxclass = $count + 1;
for ($i = 0; $i <= $maxclass; ++$i) {
    $body .= '
                        <option class="body" value="' . $i . '" selected>' . $i . '</option>';
}
$body .= '
                    </select>
                </td>
            </tr>';
$HTMLOUT .= main_table($body) . '
    <div class="has-text-centered margin20">
        <input type="hidden" name="action2" value="add_forum">
        <input type="submit" name="button" class="button is-small margin20" value="' . $lang['fm_mp_btn'] . '">
    </div>
    </form>
          <script>
            /*<![CDATA[*/
            function confirm_delete(id)
            {
               if (confirm(\'' . $lang['fm_mp_remove'] . '\'))
               {
                  self.location.href=\'staffpanel.php?tool=forum_manage&amp;action=forum_manage&action2=delete&id=\'+id;
               }
            }
        /*]]>*/
    </script>';
echo stdhead($lang['fm_stdhead']) . wrapper($HTMLOUT) . stdfoot();
