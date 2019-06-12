<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Database;
use Pu239\User;

global $container;

$fluent = $container->get(Database::class);
$cache = $container->get(CACHE::class);
$users_class = $container->get(User::class);
$auth = $container->get(Auth::class);
$user = $users_class->getUserFromId($auth->getUserId());
$ratio = 1;
if ($user['uploaded'] !== 0 && $user['downloaded'] !== 0) {
    $ratio = $user['uploaded'] / $user['downloaded'];
}

$traffic1 = [
    1,
    2,
    3,
    24,
    25,
    26,
    27,
    28,
    29,
];

$traffic2 = [
    14,
    15,
    16,
    37,
    38,
];

$donations = [
    11,
    12,
    13,
];

$freeleech_enabled = $double_upload_enabled = $half_down_enabled = false;
$free = get_event(false);
if (!empty($free) && $free['modifier'] != 0) {
    $begin = $free['begin'];
    $expires = $free['expires'];
    if ($free['modifier'] === 1) {
        $freeleech_start_time = $free['begin'];
        $freeleech_end_time = $free['expires'];
        $freeleech_enabled = true;
    } elseif ($free['modifier'] === 2) {
        $double_upload_start_time = $free['begin'];
        $double_upload_end_time = $free['expires'];
        $double_upload_enabled = true;
    } elseif ($free['modifier'] === 3) {
        $freeleech_start_time = $free['begin'];
        $freeleech_end_time = $free['expires'];
        $freeleech_enabled = true;
        $double_upload_start_time = $free['begin'];
        $double_upload_end_time = $free['expires'];
        $double_upload_enabled = true;
    } elseif ($free['modifier'] === 4) {
        $half_down_start_time = $free['begin'];
        $half_down_end_time = $free['expires'];
        $half_down_enabled = true;
    }
}

$total_fl = $fluent->from('bonus')
                   ->select(null)
                   ->select('SUM(pointspool) AS pointspool')
                   ->select('points')
                   ->select('enabled')
                   ->where('id = 11')
                   ->fetch();
$font_color_fl = $font_color_du = $font_color_hd = '';
$percent_fl = $total_fl['pointspool'] / $total_fl['points'] * 100;
if ($total_fl['enabled'] === 'yes') {
    switch ($percent_fl) {
        case $percent_fl >= 90:
            $font_color_fl = '<span style="color: green">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 80:
            $font_color_fl = '<span style="color: lightgreen">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 70:
            $font_color_fl = '<span style="color: #00a86b">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 50:
            $font_color_fl = '<span style="color: turquoise">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 40:
            $font_color_fl = '<span style="color: lightblue">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 30:
            $font_color_fl = '<span style="color: yellow">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 20:
            $font_color_fl = '<span style="color: orange">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl < 20:
            $font_color_fl = '<span style="color: red">' . number_format($percent_fl) . ' %</span>';
            break;
    }
}
$total_du = $fluent->from('bonus')
                   ->select(null)
                   ->select('SUM(pointspool) AS pointspool')
                   ->select('points')
                   ->select('enabled')
                   ->where('id = 12')
                   ->fetch();
$percent_du = $total_du['pointspool'] / $total_du['points'] * 100;
if ($total_du['enabled'] === 'yes') {
    switch ($percent_du) {
        case $percent_du >= 90:
            $font_color_du = '<span style="color: #0f0">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 80:
            $font_color_du = '<span style="color: lightgreen">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 70:
            $font_color_du = '<span style="color: #00a86b">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 50:
            $font_color_du = '<span style="color: turquoise">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 40:
            $font_color_du = '<span style="color: lightblue">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 30:
            $font_color_du = '<span style="color: yellow">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 20:
            $font_color_du = '<span style="color: orange">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du < 20:
            $font_color_du = '<span style="color: red">' . number_format($percent_du) . ' %</span>';
            break;
    }
}

$total_hd = $fluent->from('bonus')
                   ->select(null)
                   ->select('SUM(pointspool) AS pointspool')
                   ->select('points')
                   ->select('enabled')
                   ->where('id=13')
                   ->fetch();
$percent_hd = $total_hd['pointspool'] / $total_hd['points'] * 100;
if ($total_hd['enabled'] === 'yes') {
    switch ($percent_hd) {
        case $percent_hd >= 90:
            $font_color_hd = '<span style="color: green">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 80:
            $font_color_hd = '<span style="color: lightgreen">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 70:
            $font_color_hd = '<span style="color: #00a86b">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 50:
            $font_color_hd = '<span style="color: turquoise">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 40:
            $font_color_hd = '<span style="color: lightblue">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 30:
            $font_color_hd = '<span style="color: yellow">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 20:
            $font_color_hd = '<span style="color: orange">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd < 20:
            $font_color_hd = '<span style="color: red">' . number_format($percent_hd) . ' %</span>';
            break;
    }
}

if ($freeleech_enabled) {
    $fstatus = "<span style='color: green;'> ON </span>";
} else {
    $fstatus = $font_color_fl . '';
}
if ($double_upload_enabled) {
    $dstatus = "<span style='color: green;'> ON </span>";
} else {
    $dstatus = $font_color_du . '';
}
if ($half_down_enabled) {
    $hstatus = "<span style='color: green;'> ON </span>";
} else {
    $hstatus = $font_color_hd . '';
}

$top_donators = $cache->get('top_donators1_');
if ($top_donators === false || is_null($top_donators)) {
    $top_donators = $fluent->from('bonuslog')
                           ->select(null)
                           ->select('user_id')
                           ->select('SUM(donation) AS total')
                           ->where('type = "freeleech"')
                           ->groupBy('user_id')
                           ->orderBy('total')
                           ->limit(10)
                           ->fetchAll();

    $cache->set('top_donators1_', $top_donators, 0);
}

$top_donators2 = $cache->get('top_donators2_');
if ($top_donators2 === false || is_null($top_donators2)) {
    $top_donators2 = $fluent->from('bonuslog')
                            ->select(null)
                            ->select('user_id')
                            ->select('SUM(donation) AS total')
                            ->where('type = "doubleupload"')
                            ->groupBy('user_id')
                            ->orderBy('total')
                            ->limit(10)
                            ->fetchAll();
    $cache->set('top_donators2_', $top_donators2, 0);
}

$top_donators3 = $cache->get('top_donators3_');
if ($top_donators3 === false || is_null($top_donators3)) {
    $top_donators3 = $fluent->from('bonuslog')
                            ->select(null)
                            ->select('user_id')
                            ->select('SUM(donation) AS total')
                            ->where('type = "halfdownload"')
                            ->groupBy('user_id')
                            ->orderBy('total')
                            ->limit(10)
                            ->fetchAll();
    $cache->set('top_donators3_', $top_donators3, 0);
}

$top_donator1 = "<h4 class='top10 has-text-weight-bold'>Top 10 Contributors </h4>\n";
if (!empty($top_donators) && count($top_donators) > 0) {
    if ($top_donators) {
        foreach ($top_donators as $a) {
            $top_donator1 .= format_username($a['user_id']) . ': ' . number_format((int) $a['total']) . '<br>';
        }
    } else {
        $top_donator1 .= 'Nobodys contibuted yet!!';
    }
}

$top_donator2 = "<h4 class='top10 has-text-weight-bold'>Top 10 Contributors </h4>\n";
if (!empty($top_donators2) && count($top_donators2) > 0) {
    if ($top_donators2) {
        foreach ($top_donators2 as $b) {
            $top_donator2 .= format_username($b['user_id']) . ': ' . number_format((int) $b['total']) . '<br>';
        }
    } else {
        $top_donator2 .= 'Nobodys contibuted yet!!';
    }
}

$top_donator3 = "<h4 class='top10 has-text-weight-bold'>Top 10 Contributors </h4>\n";
if (!empty($top_donators3) && count($top_donators3) > 0) {
    if ($top_donators3) {
        foreach ($top_donators3 as $c) {
            $top_donator3 .= format_username($c['user_id']) . ': ' . number_format((int) $c['total']) . '<br>';
        }
    } else {
        $top_donator3 .= 'Nobodys contibuted yet!';
    }
}

$fl_header = '';
if ($total_fl['enabled'] === 'yes' || $total_du['enabled'] === 'yes' || $total_hd['enabled'] === 'yes') {
    $fl_header .= "<div class='has-text-centered size_5'>";

    if ($total_fl['enabled'] === 'yes') {
        $fl_header .= ' FreeLeech [ ';
        if ($freeleech_enabled) {
            $fl_header .= '<span style="color: green;"> ON</span>';
        } else {
            $fl_header .= $fstatus;
        }
        $fl_header .= ' ]';
    }
    if ($total_du['enabled'] === 'yes') {
        $fl_header .= ' DoubleUpload [ ';
        if ($double_upload_enabled) {
            $fl_header .= '<span style="color: green"> ON</span>';
        } else {
            $fl_header .= $dstatus;
        }
        $fl_header .= ' ]';
    }

    if ($total_hd['enabled'] === 'yes') {
        $fl_header .= ' Half Download [ ';
        if ($half_down_enabled) {
            $fl_header .= '<span style="color: green"> ON</span>';
        } else {
            $fl_header .= $hstatus;
        }
        $fl_header .= ' ]';
    }
    $fl_header .= '</div>';
}

$bpt = $site_config['bonus']['per_duration'];
$bmt = $site_config['bonus']['max_torrents'];
$bonus_per_comment = $site_config['bonus']['per_comment'];
$bonus_per_rating = $site_config['bonus']['per_rating'];
$bonus_per_post = $site_config['bonus']['per_post'];
$bonus_per_topic = $site_config['bonus']['per_topic'];

$at = $fluent->from('peers')
             ->select(null)
             ->select('COUNT(id) AS count')
             ->where('seeder = ?', 'yes')
             ->where('connectable = ?', 'yes')
             ->where('userid = ?', $user['id'])
             ->fetch('count');

$at = $at >= $bmt ? $bmt : $at;

$atform = number_format($at);
$activet = number_format($at * $bpt * 2, 2);

$user_point = "
    <div class='portlet'>
        <h1 class='top20 has-text-centered'>What the hell are these Karma Bonus points, and how do I get them?</h1>
        <div class='bordered bottom20'>
            <div class='alt_bordered bg-00 padding20'>
                <h2>
                    For every hour that you seed a torrent, you are awarded with " . number_format($bpt * 2, 2) . " Karma Bonus Point...
                </h2>
                <p>
                    If you save up enough of them, you can trade them in for goodies like bonus GB(s) to increase your upload stats, also to get more invites, or doing the real Karma booster... give them to another user!<br>
                    This is awarded on a per torrent basis (max of $bmt) even if there are no leechers on the Torrent you are seeding! <br>
                    Seeding Torrents Based on Connectable Status = <span>
                        <span class='tooltipper' title='Seeding $atform torrents'> $atform </span>*
                        <span class='tooltipper' title='$bpt per announce period'> $bpt </span>*
                        <span class='tooltipper' title='2 announce periods per hour'> 2 </span>= $activet
                    </span>
                    karma per hour
                </p>
            </div>
        </div>

        <div class='bordered bottom20'>
            <div class='alt_bordered bg-00 padding20'>
                <h2>Other things that will get you karma points:</h2>
                <p>
                    Uploading a new torrent = 15 points<br>
                    Filling a request = 10 points<br>
                    Comment on torrent = 3 points<br>
                    Saying thanks = 2 points<br>
                    Rating a torrent = 2 points<br>
                    Making a post = 1 point<br>
                    Starting a topic = 2 points
                </p>
            </div>
        </div>

        <div class='bordered'>
            <div class='alt_bordered bg-00 padding20'>
                <h2>Some things that will cost you karma points:</h2>
                <p>
                    Upload credit<br>
                    Custom title<br>
                    One month VIP status<br>
                    A 1:1 ratio on a torrent<br>
                    Buying off your warning<br>
                    One month custom smilies for the forums and comments<br>
                    Getting extra invites<br>
                    Getting extra freeslots<br>
                    Giving.gift of karma points to another user<br>
                    Asking for a re-seed<br>
                    Making a request<br>
                    Freeleech, Doubleupload, Halfdownload contribution<br>
                    Anonymous profile<br>
                    Download reduction<br>
                    Freeleech for a year<br>
                    Pirate or King status<br>
                    Unlocking parked option<br>
                    Pirates bounty<br>
                    Reputation points<br>
                    Userblocks<br>
                    Bump a torrent<br>
                    User immuntiy<br>
                    User unlocks<br>
                </p>
                <p>
                    But keep in mind that everything that can get you karma can also be lost...<br>
                </p>
                <p>
                    ie: If you up a torrent then delete it, you will gain and then lose 15 points, making a post and having it deleted will do the same... and there are other hidden bonus karma points all over the site which is another way to help out your ratio!
                </p>
                <span>
                    *Please note, staff can give or take away points for breaking the rules, or doing good for the community.
                </span>
            </div>
        </div>
    </div>";
