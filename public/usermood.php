<?php

declare(strict_types = 1);

use Pu239\Cache;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$lang = array_merge(load_language('global'), load_language('usermood'));
global $container, $site_config;
$HTMLOUT = '';

if (!isset($user['id'])) {
    die($lang['user_mood_log']);
}
$more = $user['perms'] & UNLOCK_MORE_MOODS ? 2 : 1;
if (isset($_GET['id'])) {
    $moodid = (isset($_GET['id']) ? (int) $_GET['id'] : 1);
    $res_moods = sql_query('SELECT * FROM moods WHERE bonus < ' . sqlesc($more) . ' AND id=' . sqlesc($moodid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res_moods)) {
        $rmood = mysqli_fetch_assoc($res_moods);
        sql_query('UPDATE users SET mood = ' . sqlesc($moodid) . ' WHERE id=' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
        $cache = $container->get(Cache::class);
        $cache->update_row('user_' . $user['id'], [
            'mood' => $moodid,
        ], $site_config['expires']['user_cache']);
        $cache->delete('topmoods');
        write_log('<b>' . $lang['user_mood_change'] . '</b> ' . $user['username'] . ' ' . htmlsafechars($rmood['name']) . '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($rmood['image']) . '" alt="">');
        $HTMLOUT = doc_head() . '
        <meta property="og:title" content=' . $lang['user_mood_title'] . '>
        <title>' . $lang['user_mood_title'] . "</title>
        <link rel='stylesheet' href='" . get_file_name('vendor_css') . "'>
        <link rel='stylesheet' href='" . get_file_name('css') . "'>
        <link rel='stylesheet' href='" . get_file_name('main_css') . "'>
      <script>
      <!--
      opener.location.reload(true);
      self.close();
      // -->
      </script>";
    } else {
        die($lang['user_mood_hmm']);
    }
}
$body_class = 'background-16 skin-2';
$HTMLOUT .= doc_head() . '
    <meta property="og:title" content=' . $lang['user_mood_title'] . '>
    <title>' . $lang['user_mood_title'] . "</title>
    <link rel='stylesheet' href='" . get_file_name('vendor_css') . "'>
    <link rel='stylesheet' href='" . get_file_name('css') . "'>
    <link rel='stylesheet' href='" . get_file_name('main_css') . "'>
</head>";

$body = '
<body class="' . $body_class . '">
    <script>
        var theme = localStorage.getItem("theme");
        if (theme) {
            document.body.className = theme;
        }
    </script>';

$div = '
    <h3 class="has-text-centered has-text-primary top20">' . $user['username'] . '\'' . $lang['user_mood_s'] . '</h3>
    <div class="level-center bottom20">';
$res = sql_query('SELECT * FROM moods WHERE bonus < ' . sqlesc($more) . ' ORDER BY id') or sqlerr(__FILE__, __LINE__);
$count = 0;
while ($arr = mysqli_fetch_assoc($res)) {
    $div .= '
        <span class="margin10 bordered has-text-centered bg-04">
            <a href="?id=' . (int) $arr['id'] . '">
                <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($arr['image']) . '" alt="" class="bottom10">
                <br>' . htmlsafechars($arr['name']) . '
            </a>
        </span>';
}
$div .= '
    </div>';

$body .= main_div($div) . '
    <div class="w-100 has-text-centered margin20">
        <a href="javascript:self.close();">
            <span class="button bottom20">' . $lang['user_mood_close'] . '</span>
        </a>
    </div>
    <noscript>
        <a href="' . $site_config['paths']['baseurl'] . '/index.php">' . $lang['user_mood_back'] . '</a>
    </noscript>
</body>';
$HTMLOUT .= wrapper($body) . '
</html>';
echo $HTMLOUT;
