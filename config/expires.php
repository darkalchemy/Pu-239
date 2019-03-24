<?php

// Cache Expires
// 0 = permanent (doesn't expires),
// 1 - 2591999 (30 days) = relative time, in seconds from now,
// 2592000 and over = absolute time, unix timestamp
$site_config['expires'] = [
  'activeircusers' => 300,
  'activeusers' => 300,
  'alerts' => 86400,
  'announcement' => 600,
  'birthdayusers' => 43200,
  'book_info' => 604800,
  'browse_where' => 60,
  'browser_user_agent' => 86400,
  'checked_by' => 0,
  'child_boards' => 900,
  'completed_torrents' => 300,
  'contribution' => 259200,
  'curuser' => 2591999,
  'forum_insertJumpTo' => 3600,
  'forum_posts' => 86400,
  'forum_users' => 60,
  'genrelist' => 2591999,
  'get_all_boxes' => 2591999,
  'hnr_data' => 300,
  'insertJumpTo' => 2591999,
  'invited_by' => 900,
  'ip_data' => 900,
  'iphistory' => 900,
  'ismoddin' => 0,
  'last24' => 300,
  'last5_torrents' => 3600,
  'last_post' => 86400,
  'last_read_post' => 86400,
  'latest_news' => 3600,
  'latestcomments' => 300,
  'latestposts' => 300,
  'latesttorrents' => 86400,
  'latestuser' => 3600,
  'motw' => 3600,
  'movieofweek' => 604800,
  'newpoll' => 0,
  'news_users' => 3600,
  'peers_' => 1800,
  'poll_data' => 900,
  'port_data' => 900,
  'port_data_xbt' => 900,
  'radio' => 0,
  'remember_me' => 14,
  'sanity' => 0,
  'scroll_torrents' => 3600,
  'searchcloud' => 86400,
  'section_view' => 60,
  'share_ratio' => 900,
  'share_ratio_xbt' => 900,
  'shit_list' => 900,
  'site_stats' => 300,
  'slider_torrents' => 3600,
  'snatch_data' => 300,
  'staff_picks' => 3600,
  'staff_snatches_data' => 300,
  'sv_child_boards' => 900,
  'sv_last_post' => 86400,
  'sv_last_read_post' => 86400,
  'top5_torrents' => 3600,
  'torrent_comments' => 900,
  'torrent_details' => 2591999,
  'total_funds' => 3600,
  'u_status' => 2591999,
  'unread' => 86400,
  'user_blocks' => 900,
  'user_cache' => 2591999,
  'user_flag' => 2419200,
  'user_friends' => 900,
  'user_hash' => 900,
  'user_invitees' => 900,
  'user_peers' => 900,
  'user_seedleech' => 900,
  'user_snatches_complete' => 300,
  'user_snatches_data' => 300,
  'user_status' => 2591999,
  'user_torrents' => 900,
];
