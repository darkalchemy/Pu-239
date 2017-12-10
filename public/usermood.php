<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('usermood'));
if (!isset($CURUSER['id'])) {
    die($lang['user_mood_log']);
}
$more = (($CURUSER['perms'] & bt_options::UNLOCK_MORE_MOODS) ? 2 : 1);
if (isset($_GET['id'])) {
    $moodid = (isset($_GET['id']) ? (int)$_GET['id'] : 1);
    $res_moods = sql_query('SELECT * FROM moods WHERE bonus < ' . sqlesc($more) . ' AND id = ' . sqlesc($moodid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res_moods)) {
        $rmood = mysqli_fetch_assoc($res_moods);
        sql_query('UPDATE users SET mood = ' . sqlesc($moodid) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user' . $CURUSER['id'], [
            'mood' => $moodid,
        ], $site_config['expires']['user_cache']);
        $cache->delete('topmoods');
        write_log('<b>' . $lang['user_mood_change'] . '</b> ' . $CURUSER['username'] . ' ' . htmlsafechars($rmood['name']) . '<img src="' . $site_config['pic_base_url'] . 'smilies/' . htmlsafechars($rmood['image']) . '" alt="" />');
        $HTMLOUT .= '<!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
         <meta http-equiv="Content-Language" content="en-us" />
         <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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
$HTMLOUT .= '<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . $lang['user_mood_title'] . '</title>
    <link rel="stylesheet" href="' . get_file('css') . '" />
</head>
<body class="$body_class">
    <script>
        var theme = localStorage.getItem("theme");
        if (theme) {
            document.body.className = theme;
        }
    </script>
    <h3 class="has-text-centered has-text-white top20">' . $CURUSER['username'] . '\'' . $lang['user_mood_s'] . '</h3>
    <div class="level-center bottom20">';
$res = sql_query('SELECT * FROM moods WHERE bonus < ' . sqlesc($more) . ' ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
$count = 0;
while ($arr = mysqli_fetch_assoc($res)) {
    $HTMLOUT .= '
        <span class="margin10 bordered w-25 has-text-centered">
            <a href="?id=' . (int)$arr['id'] . '">
                <img src="' . $site_config['pic_base_url'] . 'smilies/' . htmlsafechars($arr['image']) . '" alt="" class="bottom10" />
                <br>' . htmlsafechars($arr['name']) . '
            </a>
        </span>';
}
$HTMLOUT .= '
    </div>
    <div class="w-100 has-text-centered margin20">
        <a href="javascript:self.close();">
            <span class="button bottom20">' . $lang['user_mood_close'] . '</span>
        </a>
    </div>
    <noscript>
        <a href="./index.php">' . $lang['user_mood_back'] . '</a>
    </noscript>
</body>
</html>';
echo $HTMLOUT;
