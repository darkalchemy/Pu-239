<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Notify;
use Pu239\Upcoming;

$user = check_user_status();
global $container, $site_config;

$valid_actions = [
    'notify',
    'unnotify',
    'default',
];
$action = 'default';
$data = array_merge($_GET, $_POST);
if (isset($data['action']) && in_array($data['action'], $valid_actions)) {
    $action = htmlsafechars($data['action']);
}
$upcoming = $container->get(Upcoming::class);
$notify_class = $container->get(Notify::class);
$cache = $container->get(Cache::class);
$id = isset($date['id']) ? (int) $data['id'] : 0;
$notify = isset($data['unnotify']) ? (int) $data['unnotify'] : 0;
$dt = TIME_NOW;
switch ($action) {
    case 'unnotify':
        if (empty($id) || !is_valid_id($id) || empty($notify) || !is_valid_id($notify)) {
            stderr('USER ERROR', 'Bad id / bad cooker recipe');
        }
        if ($notify_class->delete($id, $user['id'])) {
            header("Location: {$site_config['paths']['baseurl']}index.php#cooking");
            exit();
        } else {
            stderr('USER ERROR', 'Something went wrong!');
        }
        break;

    case 'notify':
        if (empty($id) || !is_valid_id($id) || empty($notify) || !is_valid_id($notify)) {
            stderr('USER ERROR', 'Bad id / bad cooker recipe');
        }
        if ($notify_class->add($id, $user['id'])) {
            header("Location: {$site_config['paths']['baseurl']}/index.php#cooking");
            exit();
        } else {
            stderr('USER ERROR', 'Something went wrong!');
        }
        break;

    case 'default':
        $works = $upcoming->get_all(5, 0, 'added', true, false);
        $cooker = "
		<a id='cooker-hash'></a>
        <div id='cooker' class='box'>
            <div class='has-text-centered'>";
        $i = 0;
        if (count($works) > 0) {
            $heading = "
						<tr>
							<th class='has-text-centered w-10'>Category</th>
							<th class='w-50 min-150'>Recipe Name</th>
							<th class='has-text-centered'>Expected</th>
							<th class='has-text-centered'>Chef</th>
							<th class='has-text-centered'>Status</th>
							<th class='has-text-centered'>Notify</th>
						</tr>";
            $body = '';
            $work = [];
            foreach ($works as $work) {
                if (!empty($work['poster'])) {
                    $name = "<a href='{$site_config['paths']['baseurl']}/cooker.php?action=details&amp;id=" . $work['id'] . "&amp;hit=1' title='Dood' class='tooltipper'>" . format_comment($work['name']) . '</a>';
                } else {
                    $name = format_comment($work['name']);
                }
                $notify_class->get($work['id']);
                $user_notifys = [];
                //$res = sql_query('SELECT recipeid FROM notify WHERE userid = ' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
                //while ($user_notify = mysqli_fetch_row($res)) {
                //   $user_notifys[] = $user_notify[0];
                //}
            }
            if (!in_array($work['id'], $user_notifys) && $work['status'] != 'uploaded') {
                $notify = "<span id='activenotify" . $work['id'] . "'><input type='button' class='button is-small' value='Notify Me!' onclick=\"notify('notify', '{$work['id']}', '{$user['id']}')\"></span>";
            } elseif ($work['status'] != 'uploaded') {
                $notify = "<span id='activenotify" . $work['id'] . "'><input type='button' class='button is-small' value='UnNotify Me!' onclick=\"notify('unnotify', '{$work['id']}', '{$user['id']}')\"></span>";
            } else {
                $notify = '';
            }

            $caticon = !empty($image) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . format_comment($work['image']) . "' class='tooltipper' alt='" . format_comment($work['cat']) . "' title='" . format_comment($work['cat']) . "' height='20px' width='auto'>" : format_comment($work['cat']);
            $added = get_date(strtotime($work['added']), 'LONG');
            $username = format_username($work['userid']);
            $status = $work['status'];
            $views = $work['views'];
            if (isset($work['expected']) && strtotime($work['expected']) > 0 && strtotime($work['expected']) < $dt) {
                $expected = '<span class="has-text-danger">' . get_date(strtotime($work['expected']), 'LONG') . '</span>';
            } elseif (isset($work['expected']) && $work['expected'] > 0 && strtotime($work['expected']) > $dt) {
                $expected = mkprettytime(strtotime($work['expected']) - $dt);
            } else {
                $expected = mkprettytime(86400);
            }

            $request = $notify_class->get($work['id']);
            $requests = $request > 0 ? "<span id='activenotifyreq_{$work['id']}' class='has-text-green'>$request</span>" : "<span id='activenotifyreq_{$work['id']}' class='has-text-yellow'>$request</span>";
            ++$i;
            $body .= "
						<tr>
							<td class='has-text-centered'>$caticon</td>
							<td class='has-text-left'>$name</td>
							<td class='has-text-centered'>$expected</td>
							<td class='has-text-centered'>$username</td>
							<td class='has-text-centered'>$status</td>
							<td class='has-text-centered'>$notify</td>
						</tr>";
        } else {
            $body .= "
					<tr>
						<td class='has-text-centered'>Nothing happening here</td>
					</tr>";
        }
        $cooker .= main_table($body, $heading) . '
         </div>
    </div>';
}

return $cooker;
