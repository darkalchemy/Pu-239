<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('usermood'));
if (!isset($CURUSER['id'])) {
    die($lang['user_mood_log']);
}
$more = (($CURUSER['perms'] & bt_options::UNLOCK_MORE_MOODS) ? 2 : 1);
if (isset($_GET['id'])) {
    $moodid = (isset($_GET['id']) ? (int) $_GET['id'] : 1);
    $res_moods = sql_query('SELECT * FROM moods WHERE bonus < ' . sqlesc($more) . ' AND id = ' . sqlesc($moodid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res_moods)) {
        $rmood = mysqli_fetch_assoc($res_moods);
        sql_query('UPDATE users SET mood = ' . sqlesc($moodid) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user' . $CURUSER['id'], [
            'mood' => $moodid,
        ], $site_config['expires']['user_cache']);
        $cache->delete('topmoods');
        write_log('<b>' . $lang['user_mood_change'] . '</b> ' . $CURUSER['username'] . ' ' . htmlsafechars($rmood['name']) . '<img src="' . $site_config['pic_baseurl'] . 'smilies/' . htmlsafechars($rmood['image']) . '" alt="">');
        $HTMLOUT = doc_head() . '
        <meta property="og:title" content=' . $lang['user_mood_title'] . '>
        <title>' . $lang['user_mood_title'] . '</title>
      <script>
      <!--
      opener.location.reload(true);
      self.close();
      // -->
      </script>';
    } else {
        die($lang['user_mood_hmm']);
    }
}
$body_class = 'background-16 h-style-9 text-9 skin-2';
$HTMLOUT .= doc_head() . '
    <meta property="og:title" content=' . $lang['user_mood_title'] . '>
    <title>' . $lang['user_mood_title'] . '</title>
    <link rel="stylesheet" href="' . get_file_name('css') . '">
</head>';

$body = '
<body class="' . $body_class . '">
    <script>
        var theme = localStorage.getItem("theme");
        if (theme) {
            document.body.className = theme;
        }
    </script>';

$div = '
    <h3 class="has-text-centered has-text-white top20">' . $CURUSER['username'] . '\'' . $lang['user_mood_s'] . '</h3>
    <div class="level-center bottom20">';
$res = sql_query('SELECT * FROM moods WHERE bonus < ' . sqlesc($more) . ' ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
$count = 0;
while ($arr = mysqli_fetch_assoc($res)) {
    $div .= '
        <span class="margin10 bordered has-text-centered bg-04">
            <a href="?id=' . (int) $arr['id'] . '">
                <img src="' . $site_config['pic_baseurl'] . 'smilies/' . htmlsafechars($arr['image']) . '" alt="" class="bottom10">
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
        <a href="' . $site_config['baseurl'] . '/index.php">' . $lang['user_mood_back'] . '</a>
    </noscript>
</body>';
$HTMLOUT .= wrapper($body) . '
</html>';
echo $HTMLOUT;
