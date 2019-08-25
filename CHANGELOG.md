### 25 Aug, 2019
add: check is user has been verified recently  
update: check if user agent is set begore using it  
update: get $moods before using them  
update: Adminer 4.7.2  
fix: missing breadcrumbs after verifying identity  
update: verify password for staff panel  
update: int to float member_ratio()  
fix: arcade highscores  
add: freeleech status to torrent autoshout  

### 23 Aug, 2019
add: get_fullname_from_id() function_categories.php  
add: more tv/movie genres  
fix: typo upload.php  
update: set default location to US for ebook lookup  
update: ebook lookup  
add: clean title for ebook lookup  

### 21 Aug, 2019
remove: duplicate form items  
update: README to include npx requirement  
fix: unknown user User.php  
add: check for composer, npm and npx during install  
replace: htmlsafechars format_comment when for display  
update: style quick_reply.php  
update: add ip to usersdata  
update: forum style on mobile  
fix: avatar not showing correctly  
update: view forum link to 'View Forum' function_breadcrumbs.php  
fix: provide only integers to mksize  

### 20 Aug, 2019
update: README  
update: group by in Snatched.php karma_update.php  
fix: allow edit class_promo.php  
update: show empty block when no results trivia_results.php  
remove: redundant switch block browse.php, mybonus.php, mytorrents.php  
update: phpDoc blocks  
update: missing third param warn.php  
update: incorrect function used comments.php  
update: group by in query trivia_results.php  
fix: typo Torrent.php  

### 19 Aug, 2019
update: show error message on invalid or expired promo  
add: view forum breadcrumb  
fix: missing closing div usercp.php  
update: set user status and verified install.php  
fix: incorrect datetime used ip_update.php  

### 18 Aug, 2019
update: clear caches for latest tv/movie when deleting torrent  
fix: check for empty isbn13 before use  
fix: check for empty publishedDate before use  
fix: check for empty isbn10 before use  
remove: tags.php from navbar  
update: include required php-readline README  
fix: set proper descr takeupload.php  
update: ebook lookup  
fix: update isbn takeedit.php  
update: delete torrent delete.php  
update: use 1:int for odds  
update: only show updates/inserts on submit site_settings.php  
add: site settings gift_odds to set user odds for getting free gift from chat bot  
fix: typo  
add: missing function  
fix: not showing all replies  
update: delete bot_replies_ cache on insert, update or delete trigger or replies  
add: bot_triggers.php to navbar  
add: random gifts to AJAX Chat  
add: bot triggers and responses to AJAX Chat  

### 17 Aug, 2019
replace: htmlsafechars with format_comment when used for display  
fix: submit button not working when using invite or promo signup.php  
remove: passing userids are url parameter promo.php  
update: breadcrumbs for delete.php  
fix: format content bugs.php  

### 15 Aug, 2019
fix: code bbcode block not displayed correctly function_bbcode.php  
fix: Undefined index: size Torrent.php  
fix: Undefined index: children_ids forums.php  

### 14 Aug, 2019
update: clean site name for use with sessions, cookies and cache install.php  
fix: announce event stopped set seeder = no  
fix: mksize return 0 B instead of ''  

### 12 Aug, 2019
fix: typo ImageProxy.php  
remove: duplicate function tvmaze_shows_update.php  
fix: typo jobby.php  
update: check if parent is writable before setting permissions ImageProxy.php  
fix: image proxy not setting permissions on manipulated files  
update: funds cleanup log jobby.php  
update: check if empty first mksize()  

### 11 Aug, 2019
remove: check for autoshout when sending announce to irc takeupload.php  

### 10 Aug, 2019
fix: bash function to get logname functions.php  
update: add user ids to torrent blocks snatched_staff.php, torrents_block.php  
fix: remove trim for bash function functions.php  
fix: staff_actions.php  
fix: topic/post count on main forum page not matching topic/post count on view_forum.php  
update: uploadapp.php  

### 09 Aug, 2019
fix: duplicated results for messages when user has friends/blocks  
fix: and update arcade  

### 07 Aug, 2019
fix: multiple count queries snatched_torrents.php  
fix: show users ip:port to everyone with id > UC_STAFF catalog.php  
fix: limit number of leeches/seeds announce.php  
fix: uploader not setting complete_date announce.php  

### 06 Aug, 2019
remove: paranoia from index page blocks  
fix: undefined $self announce.php  
update: add Port Check to navbar  
update: extend timeout optimize_resize_images.php  
fix: check ports  
fix: user stats not updating announce.php  
fix: uploader not setting complete_date announce.php  
update: imdb id column to accept 7 or 8 digits tvmaze  
update: truncate tables when deleting all torrents remove_torrents.php  
add: cleanup tvmaze show update  
update: get correct type tvmaze_update.php  
fix: setting correct image tvmaze_schedule_update.php  

### 05 Aug, 2019
fix: min announce interval announce.php  
fix: setting incorrect snatched start_date, uploaded, downloaded, seedtime, leechtime announce.php  
fix: Downloaded Daily average userdetails.php  
remove: keys  

### 04 Aug, 2019
fix: get images function_fanart.php  
fix: type function_torrenttable.php  
update: allow users to set paranoia levels  
fix: incorrect time format used delete_torrents_update.php  
fix: class_promo.php  
fix: url format to irc  
update: incorrect constant used  
remove: Pu-239\Rolls from php-di  
fix: data_reset.php  
fix: add, rename, delete user mailboxes  edit_mailboxes.php  
fix: message count view_mailbox.php  
update: irc scripts  
replace: UC_UPLOADER with UC_SUPERUSER  
add: roles CODER, UPLOADER  
add: each parent key includes ability to add new child  
update: min column widths site_settings.php  
fix: column not found watched_users.php  
add: has_access() to allow checking for user roles, currently only if user is site coder, site coder would have full site access without the need to promote to UC_MAX  
update: Mysqli to mysqli  
update: 1000 vs 1024 blackjack.php, casino.php  
add: tags.php to navbar  
add: missing BBcode tags tags.php  

### 30 Jul, 2019
fix: saving BBcode() items as html instead of bbcode  
fix: overwriting snatched['start_date'] with each update  

### 29 Jul, 2019
update: html for youtube iframe function_bbcode.php, chat.js  
fix: breadcrumbs not showing when checking user password and using staffpanel  
update: check banned ip status  
fix: add banned ip  
fix: update confirmed acpmanage.php  
fix: isbn lookup  
add: xbt_files_users, xbt_snatched and xbt_peers_history tables  
update: allow letters with punctuation valid_username()  
update: bot triggers  
update: icons  
fix: index not found mytorrents.php, function_torrenttable.php  
fix: column not found postcounter.php, hnrs.php  

### 24 Jul, 2019
add: xbt_files_users, xbt_snatched and xbt_peers_history tables  
fix: undefined index showfriends.php  
update: all tables have a primary key  
add: user_slots table  

### 24 Jul, 2019
add: xbt_client_blacklist users_freeleeches tables  
fix: function undefined  
update: db files  
add: torrent['balance'] not sure its purpose  
add: torrent['snatched'] i think this will be same as times_completed  
update: all tables['anonymous'] to enum('1', '2')  
add: torrent['time'] - not sure its needed, may remove later if not  
update: restrict announces to $site_config['tracker']['min_interval'] and empty event  
add: torrent['doubletorrent'] - not currently used  
update: torrent['freetorrent'] to enum - not currently used  
update: torrent['last_action'] from timestamp to date time  
update: limit leechers to 1 active peer, limit seeders to 3 active peers  
update: restrict announces to $site_config['tracker']['min_interval'] unless client event is completed and previous announce was seeder = no  
remove: auto_enter_abnormal_upload() with todays speeds this check is pointless  
update: better handling of unicode chars, likely more is needed  

### 23 Jul, 2019
update: forms add enctype='multipart/form-data'  
update: better handling of unicode chars, likely more is needed  
update: limit ips to login in usersearch.php  
update: replace htmlsafechars with format_comment when var is for display  
update: set last access for anonymous users  
fix: reply to messages  
fix: special characters formating messages  
update: margins around code  
fix: create user does not adhere to naming requirements  

### 22 Jul, 2019
add: ignore, lang columns to images table  
fix: snatched_torrents_staff torrents_lookup.php  
add: function to update dead seeders Snatched.php  
revert: remove session from announce.php  
add: missing index  
update: remove session from announce.php  
update: slow fpm log_viewer.php  
update: remove empty backup folders  
update: fanart.tv to only get unidentified images if 'empty' is in the fanart site settings  

### 21 Jul, 2019
fix: delete all users  
update: bugs schema  
merge: user['perms'] user['anonymous_until'] user['paranoia'] into 1 function check_anonymous()  
update: allow user to reset torrent_pass, auth, api_key  
add: anonymous users to site stats  
update: anonymous_until to 28 days mybonus.php  
remove: anonymous from users table  
update: hide anonymous users from all user lists  
update: style nfo viewer  
fix: index page comments block  
add: missing path from config_example.php  
add: linedraw.ttf font  
update: db files  
add: search by audio  
add: site setting to view nfo's as images  
update: ensure path is writable before writing  goaccess_update.php  
update: check if empty before using function_newsrss.php  
update: add try/cache function_newsrss.php  
remove: get_parked()  
update: default to allow comments  
update: user 'status' to track enabled, parked, normal user  
update: catch statements not reached verify_email.php  
update: messages.php  
update: view_mailbox.php  
update: log out current browser only when checking user status  
update: set smtp_enable default to false  
update: links in AJAX Chat reload current window instead of new window  

### 17 Jul, 2019
fix: check if file is readable before reading log_viewer.php  
update: clean url without breaking code blocks  
add: post id to forum posts  
fix: log viewer, large files are still not fixed  
fix: bad '&' used  
fix: missing method  
fix: missing index 'isbn' takeedit.php  
add: line-height for input, select, textarea  

### 16 Jul, 2019
revert: installer to use mysql to import db files  
update: remove date info from db files  

### 15 Jul, 2019
fix: user torrent blocks userdetailes.php  
fix: drag and drop image upload  
fix: cast to int AJAXChat.php  
fix: typo takeupload.php  
fix: add try catch ImageProxy.php  
fix: check if empty view_topic.php  
fix: typo  
fix: display php code  

### 14 Jul, 2019
fix: typo blocks cache  
update: genreal cleanup  

### 13 Jul, 2019
fix: missing index  
update: torrent info browse.php  
add: include audio languages on tv/movie uploads  
fix: update personal info without setting birthday  
fix: db insert for doubleup token used  
update: times_completed during torrent_update cleanup  
fix: wrong query string dunction_torrenttable.php  
update: remove useless code from cron_controller.php  
update: remove torrent_pass from peers table  
update: change initial sort order to descending when click column to sort browse.php  
update: generate users torrent_pass/auth/apikey if not already set  

### 12 Jul, 2019
fix: download torrent as zip  
update: allow tv cats to edit subtitles  
fix: snatches getting mark_of_cain set  
fix: user offset  
fix: sort order top donors function_bonus.php  
remove: need for session in function_common.php  
update: disable check for cookie header announce.php(allows utorrent 2.2.1)  
fix: default user categories browse.php  
update: user message when unseeded new torrent is deleted  
remove: cache torrent hover  
update: allow comments by default, torrent upload  
update: torrent name width index torrent blocks  
add: torrent hover cache  

### 11 Jul, 2019
fix: get user time offset  
fix: display server time for < UC_STAFF  
update: limit torrent title to 450px details.php  
fix: explicit set $temp  
fix: showing multiple duplicate torrents browse.php  
update: duplicate posts view_unread_posts.php  
update: set parked = no when user logs in  
update: database files  
update: send bug report details with bug response  
fix: read forum posts  
fix: default user categories categories.php  
add: site setting to show/hide users torrent pass upload.php  

### 10 Jul, 2019
update: styling  
update: database files  
update: staff panel add user  
fix: forum poll votes    
fix: cast to int function_bbcode.php  
update: disable AJAX Chat if site is disabled  
fix: incorrect global used friends.php  
update: pass user instead of global $CURUSER torrentable_functions.php  
remove: hidden input ssl login.php  
fix: check for imdb before using it  
update: remove package from doc block  
update: serialize/unserialize to json_encode/json_decode for readability, you will need to delete files in dir_list  
update: README  
update: index page torrent blocks  

### 07 Jul, 2019
update: README  
update: validate input rss.php  
update: force user logout login.php  
update: proper validation recover.php login.php signup.php  
update: rules.php faq.php useragreement.php  
add: rakit/validation class  
fix: signup.php  
update: remove cooker/upcoming  

### 06 Jul, 2019
update: cleanup  
update: check if user input is valid and not an array  
update: faq.php rules.php  
update: check if user creds are vaild  
update: check if path exists before removing  
update: check email during signup  
fix: check if empty user first like.php  
fix: undefined function  
fix: undefined var announce.php  
fix: cast to int hnrs.php  
fix: undefined var movies.php  
fix: undexfined index takeedit.php  
fix: undefined var takeupload.php  
fix: undefined var trivia_results.php  
revert: adminer.php - should not be edited  
update: view_peers.php  
add: missing strict  
add: missing docblock  
remove: duplicate auth check verify_email.php  
fix: incorrect usage $user/$CURUSER userdetails.php  
update: begin replace htmlsafechars with format_comment when string is for output  
fix: return noposter.png instead of false  

### 05 Jul, 2019
fix: add missing require_once INCL_DIR . 'function_password.php' modtask.php  
fix: check for empty index function_torrenttable.php  
remove: cacheing user snatched history - no need  

### 03 Jul, 2019
update: simple search uses LIKE(), advanced search uses fuzzy browse.php  
update: allow get or post request to search.php  
update: all publicly accessible pages, with exceptions, must authenticate user status  

### 02 Jul, 2019
remove: site settings mysqldump_path and gzip_path  
fix: security issue using extract()  
fix: deleting torrent comments  
fix: search cloud  

### 01 Jul, 2019
fix: missing argument view_message.php  
fix: cast to int view_message.php  
update: move config functions to functions.php  
add: missing class files  
fix: trim on null functions.php  
update: require authentication for staff pages if user is remembered  
update: remove $CURUSER from public pages, the rest to follow  
fix: read last post update  
fix: freeleech, doubleupload, halfdownload not updating correctly mybonus.php  

### 29 Jun, 2019
update: style bugs.php  
fix: clear fllslot_ when adding  
fix: show free/double slots correctly on browse page  

### 28 Jun, 2019
fix: cast to int bugs.php hnrs.php  
fix: id is ambiguous search.php  
update: promo users style promo.php  
fix: variable not found bugs.php  

### 27 Jun, 2019
update: allow send message to user without closing bug bugs.php  
update: bugs.php  
update: remove "{}" when not needed  
update: add comments to fix/ignore bugs.php  
fix: must be of the type int move_or_delete_multi.php  
fix: must be of the type int achievementlist.php  
fix: Undefined variable: session postcounter.php  
fix: must be of the type int reputation.php  
fix: Undefined index ajaxchat.php in function_breadcrumbs.php  
fix: Undefined index userid in achievement_up_update.php  
fix: check if empty before setting takeedit.php  
fix: undefined index HTTP_REFERER takeupload.php  

### 26 Jun, 2019
fix: undefined functions() verify_email.php  
update: favicon  
fix: parameter placement dubug_pdo()  
add: unset unsed item toggle.js  
remove: unused js file template.php  
update: check if empty class_check.php  
update: add text shadow to torrent names when stickied browse.php  
fix: pager tmovies.php  
update: check for empty selector or token verfiry_email.php  
fix: str_replace string not array mybonus.php  

### 25 Jun, 2019
fix: user credit mybonus.php  
fix: use correct user['id'] $id $CURUSER['id'] userdetails.php  
add: biblys/isbn to validate isbn  
update: function_bt_client.php  
remove: duplicate items sceditor.js  
update: userdetails.php  
fix: no default value uploadapp.php  
update: page style login.php recover.php signup.php  
fix: check if empty function_tvmaze.php  
fix: cast to int hnrs.php  
fix: new bugs count bugs.php  
fix: extraneous spaces function_books.php  
fix: div by 0 hit_and_run.php  
update: font color blocks/global/uploadapp.php  
fix: incorrent user index achievement_seedtime_update.php  
update: quick reply for everyone view_topic.php  

### 24 Jun, 2019
update: select only invited users  
fix: signup.php  
update: add default achpoints  
update: add ignore() Userblock.php Usersachiev.php  
fix: typo invite.php  
add: script to fix usersachiev userblocks usersfix.php  

### 23 Jun, 2019
fix: typo torrents_lookup.php  
fix: extra spaces User.php  
update: Too few arguments to function get_torrent_count() Peer.php  
fix: typo  
fix: typo announce.php  
update: error messages verify_email.php  
fix: clear user cache where user forced logout  
fix: clear user cache when user logs in/out  
fix: cast to int hit_and_run.php  
update: use $site_config['require_credit'] to determine if user allowed to download torrent  
fix: typo backup_update.php  
fix: undefined $torrent image_update.php  
fix: undefined $session  topiccounter.php  
fix: cast to int poll.php   
fix: achievementhistory.php  
fix: can't delete folder  
fix: incorrect index name used  
add: staff option to set initial upload credit to new users  
add: staff option to enable/disable upload credit to download  
remove: redundant updates images_update.php  
add: latest tv torrents block index page  
add: latest movie torrents block index page  
add: missing id's for labels  
fix: remove extra spaces requests.php offers.php  
fix: cast to int invite.php  
update: bug message formatting bugs.php  

### 22 Jun, 2019
update: explode string to array categories.php  
update: cast to int flash.php  
update: tvmaze genres clickable  
add: link to staff messages navbar.php  
update: popups max width to 500px  
fix: add/edit category without image  
fix: add item to array site_settings.php  
fix: float seedbonus  
add: resend invide codes  
update: seedbonus column to decimal  
update: mybonus.php  
add: torrent['descr'] and tvmaze caching images_update.php  
add: seedbonus to user for filling request  
fix: add bot to user_blocks and usersachiev install.php  
update: return users achievement counts with user, clear cache  
update: move reset password and force logout to sysop  
fix: user logout logout.php  
fix: typo userdetails.php  
fix: minor bugs  
add: force logout button userdetails.php  
update: error message of too many IPs  
add: site_config settings to disable users that have exceeded a set number of ips  
update: separate db backups into type/date folders  

### 21 Jun, 2019
fix: incorrect height hide_navbar.js  
fix: toggle status functions.php  
update: a bit of code cleanup phpstorm/phpcs-fixer  
update: simple search click to advanced search browse.php  
add: one click site offline/online  
add: auto put site offline/online during update  
fix: limit torrents to 1 per user announce.php
fix: change username  
fix: email notification of watched cats takeupload.php  
add: cleanup funds table, runs monthly through jobby  
update: karma_update.php  
update: db files  
fix: check targetid is set before assigning friends.php  
fix: user set country takeeditcp.php  
update: wrong id name CasinoBets.php  
update: deletion notice bugs.php  
fix: sender can't be 0  
fix: user class names when not for css  
add: hide/show navbar on scroll  

### 20 Jun, 2019
fix: forum polls  
update: formatting bugs.php  
fix: get_user_class_name() use class_config.name instead of class_config.classname  
fix: mybonus.php  
fix: use class['name'] not class['classname'] class_config  
fix: Undefined variable: signup_vars adduser.php  

### 19 Jun, 2019
fix: floodlimits  
fix: Data too long for column searchcloud  
fix: open parent cat when single child cat selected categories.php  
fix: insert_update_ip()  
fix: disable ip logging  
fix: chown: missing operand functions.php  
update: allow single item run cron_controller.php  
fix: clear_cache.php  
fix: user class promotions/demotions  
update: add set_hnr() remove_hnr() to Snatched Class  
update: check if empty function_tvmaze.php  
update: temp fix for friends.php  
update: config_example.php  
update: force remove DI_CACHE_DIR when running set_perms.php clear_cache.php uglify.php  
update: uploadapps.php  
update: insert IMDb when inserting images from TvMaze  

### 18 Jun, 2019
remove: empty array categories.php  
remove: sender from messages when sent as system  
update: db files  
update: README  
add: clickable categories browse.php  
add: manage images staffpanel.php  
remove: jpg, png, gif requirement on poster takeedit.php  
update: news.php  
relocate: make_dir() to function_users.php  
relocate: get_scheme() to config.php  
fix: check if cast is empty function_imdb.php  
fix: cast to int view_unread_posts.php  
update: check if empty function_breadcrumbs.php  

### 16 Jun, 2019
remove: funds_update from cron_controller.php  
update: breadcrumbs  
fix: User::delete_users()  
fix: typos  
update: general cleanup  
rewrite: hitrun_update.php-untested  
fix: pu_demote_update.php  
fix: peerlist.php  
update: viewnfo.php  
fix: userdetails after promo deleted  
fix: class config for AJAX Chat when class['name'] differs from class['className']  
fix: usersearch.php  
fix: mega_search.php  
update: add min width to brwose torrents and view mailbox  
update: tvmaze to show more cast info on hover  
update: adduser.php use php-auth to create user  
update: reset.php use php-auth to reset password  
add: jobby to handle cron jobs  

### 14 Jun, 2019
remove: explicit ip address when instantiating Auth()  
revert: rss.php  

### 13 Jun, 2019
fix: typos  
fix: strict issues  
update: casino.php  
fix: cleaning null view_topic.php  
fix: missing $session takereseed.php  
fix: missing lang entry torrents_lookup.php  
fix: missing () movies.php  

### 12 Jun, 2019
fix: strict issues  
update: default time relativity  
fix: member_input.php  
remove: retry on ajax lookups details.php  
fix: autoshout new user with correct classname  
fix: more strict issues  
fix: tvmaze lookup  
fix: many strict issues  

### 09 Jun, 2019
fix: get_user_class_name() usage  
update: disable button when not enough seedbonus mybonus.php  
update: return query results DESC  
fix: say_thanks()  
fix: clear torrent cache when leaving quick thank you  
fix: strict issues  
fix: forum post history  
update: make forum posts and comments same format  
fix: reports.php  
fix: remove dd() usage  
fix: imdb block format details.php  
fix: userhistory.php  
update: database files  
fix: floodlimit.php  
fix: class_config.php  
update: allow import multiple tables import_tables.php  
update: uglify.php  
fix: proper perms set_perms.php  

### 07 Jun, 2019
update: button margin on small screens  
fix: returnto login.php  

### 06 Jun, 2019
update: uglify.php  
fix: signup.php  
update: auto run uglify when changing class config  
update: allow UC_SYSOP to run uglify from staffpanel  
fix: upgrade_database.php  
fix: download.php  
fix: getrss.php  
fix: subtitles.php  
fix: missing $session  

### 05 Jun, 2019
update: css  
fix: populate changes everywhere when change variables.css  
fix: set color to defaul border icarousel  
add: missing autoshout on new user  
move: browserslist from ackage.json to .browserslistrc 
fix: strict issues  

### 04 Jun, 2019
fix: inactive.php  
fix: modtask.php  
fix: freestuffs.php  
fix: blackjack.php  
add: missing minus.gif  
fix: topten.php  
fix: userdetails.php  
fix: get_avatar() not proxying external images  
fix: snatches.php  
fix: send_message.php  
fix: browse.php  
fix: contactstaff.php  
fix: peerlist.php  
fix: staffbox.php  
fix: subtitles.php  

### 03 Jun, 2019
update: global messages  
fix: incorrect index name  
update: uglify.php to change $primary to default text color  
update: uglify.php to change icarousel timer color to default border color  
move: staff_columns array to staffpanel->site_config  
fix: subtitles.php  
fix: over_forums.php  
fix: userdetails.pho  
update: mybonus.php  
update: mass_bonus_for_members.php  
fix: casino.php  
update: breadcrumbs  

### 02 Jun, 2019
fix: mass_bonus_for_members.php polls_manager.php invincible.php  
fix: bugs.php friends.php mybonus.php recover.php snatches.php  
fix: staffbox.php userdetails.php  
update: breadcrumbs  
fix: polls  
fix: not showing forums when no child board exists  
fix: missing $h1_thingie messages  
fix: recover.php  
replace: explicit url with php self in header()  
update: mybonus.php  

### 01 Jun, 2019
fix: hnrwarn.php  
fix: topten.php  
fix: offers.php requests.php autoshout  
fix: lottery  
update: allow index page blocks to be shown as staff only blocks  
add: tooltip for show background  
fix: site_settings.php bg color  
update: make default text to one css variable  
update: iCarousel to use default border color  
update: images_update.php  
fix: thanks  
fix: comments  
fix: report  
remove: set cache type as memory update_db.php  
update: allowed attachments file types  
disable: search by ip  
fix: bitbuckup upload  
update: database files  
fix: various strict issues  
fix: set image permissions to user:group  
update: userdetails.php  
remove: template 2  
remove: google fonts  
remove: themeChanger  

### 31 May, 2019
fix: snatched.php  
fix: peerlist.php  
add: has-default-text-color  
update: default text color  
remove: dd() usage  
remove: echo php-di error  
revert: default PRODUCTION status  
update: move sentry api key to config.php  
fix: userdetails.php  
fix: BBcode null issues  
update: scrape.php  
fix: delete/recycle forum post  
fix: class_config.php  
fix: post_reply.php  
remove: postcss-font-family-system-ui  
add: forum quick reply  
fix: strict issues  

### 28 May, 2019
fix: allsmiles.php  
fix: strict issues  
fix: forum attachments  

### 27 May, 2019
add: missing LOGS_DIR  
fix: offers and requests  
fix: forums  
fix: editlog  
update: uncomment sceditor in forums  
remove: production from $env  

### 26 May, 2019
update: production status  
fix: cache php-di  
fix: mysql socket connection  
update: replace home spun user auth with php-auth  
update: all Pu239 namespaced classes now use strict types  
update: use php-di, i am not sure that this is the correct way to do it, but it is working  
update: use memory cache form cli scripts  

### 07 Apr, 2019
fix: return null fetch()  
update: more code cleanup  
remove: explicit type, since often may be null  
move: remaining unused files to unused  
cleanup: code  
update: release hover movies.php  
update: chmod 0775 images on save ImageProxy.php  
update: catalog.php  

### 06 Apr, 2019
cleanup: adminer plugins  
fix: adminer  
update: format_bbcode $images == false hides image instead of leaving bbcode  
fix: online  
fix: incorrect site_config key  
fix: missing global bittorrent.php  
update: initial database missing site_config data  
remove: unused functions  
fix: Sentry.io config  
fix: unchanged site_config keys chatBotName  
add: SlashTrace - Awesome error handler  
add: Sentry.io for reporting SlashTrace recorded errors - production only, api key required  
fix: key not exists on update site_settings.php  

### 05 Apr, 2019
update: database files  
update: move log viewer paths to site_config  
fix: adding empty array element site_settings.php  
remove: duplicate hnr config  
fix: install.php  
fix: update arrays when field starts empty site_settings.php  
fix: update arrays site_settings.php  
cleanup: classes  
replace: PostKey/CheckPostKey with csrf  
update: dependencies  
refactor: site config to database  
add: trivia config to add/edit/delete questions  

### 01 Apr, 2019
update: remove some erroneous spacing  
fix: display [code] correctly  
update: make similar [code] and [quote]  

### 31 Mar, 2019
move: all used files out of root folder  
move: unused file to unused folder  
add: missing files clear_announcement.php credits.php formats.php new_announcement.php tags.php videoformats.php  
fix: BBcode quote  
remove: site_cofig['char_set']  
update: all code set to use UTF-8  
add: accept-charset='utf-8' to all html forms  

### 30 Mar, 2019
fix: convert text to UTF-8 in torrent description before passing to js  
fix: typo takeupload.php  

### 29 Mar, 2019
fix: missing global $lang take_url_upload.php  
refactor: requests.php  
fix: cache imdb_person db query  
fix: insert image url into requests link  
update: allow add/edit caticons without image  
update: allow caticons image filename to match [A-Za-z0-9_\-]  

### 28 Mar, 2019
fix: removal of curuser class - was incomplete  
remove: ustatus - never set/updated/used  
fix: Block.php  
fix: typo takesignup.php  
fix: typo take_invite_signup.php  

### 27 Mar, 2019
fix: missing 'from' in fluent queries  

### 26 Mar, 2019
fix: missing user_blocks during install  
fix: missing $this  
update: static max failed login count  
update: dependencies  
update: refactor all mysqli queries in bittorent.php  
update: move flood control to session  
update: navbar  

### 24 Mar, 2019
fix: userdetails torrent blocks  
fix: global $free  
fix: announce.php  
remove: event_stuffs  
update: polls  
remove: OMDb API  
update: merge freeleech cache with events cache  
rename: some cache items  
remove: unused cache items  
update: run 'npm audit fix'  
update: add HnRs to navbar.php  
update: dependencies  
update: hnrs.php  

### 21 Mar, 2019
add: mising $formatted var  

### 20 Mar, 2019
fix: typo  

### 19 Mar, 2019
update: cache names  
fix: delete torrent block caches  
update: torrent block caches when updating torrents  
update: more text to language files  
fix: clear caches torrents_update.php  
update: database files  
update: cache names  
update: show only visible torrents in torrent blocks  
remove: create torrent_pass download.php  
update: breadcrumbs  
fix: missing query item rating browse.php  
fix: missing stylesheets  
fix: incomplete styles  
fix: missing query item mytorrents.php  

### 17 Mar, 2019
update: dependencies  
add: select torrents by snatched or not snatched  
add: percentage snatch completed browse.php  

### 16 Mar, 2019
update: remove user from AJAX Chat Online when logout or leave index page  
fix: free leech stat AJAX Chat  
update: youtube bbcode  
remove: html5.js  
update: youtube video to 1920x1080  
fix: usersearch.php  
fix: AJAX Chat user stas timestamp  
update: page format  
update: main.php  
update: cleanup interval 'delete torrents' 'backup clean'  
update: remove never seeded torrents 2 hours after uploaded  
fix: remove torrent comments when deleting torrent  
fix: forum view topic format  
fix: minimum table column widths for head and body  
fix: maintain anonymous when adding rep to anonymous  
fix: page format forums.php  

### 15 Mar, 2019
update: remove dev options when site is in_production uglify.php  
update: README  
fix: modify user security settings without requiring password change  

### 14 Mar, 2019
update: order staff picks block by timestamp  
update: italicize new upload title  
fix: categories not displaying browse.php  
update: README  
update: imdbphp  

### 12 Mar, 2019
fix: typo  
update: restrict check user upload credit is>= torrent size to user class  

### 11 Mar, 2019
update: compare uploaded-downloaded to torrent size  
fix: incorrect cache name used staff_settings_ should be is_staff_  
fix: delete staff cache when changing user class  
update: add check if seeder when checking if $user['uploaded'] < $torrent['size']  

### 10 Mar, 2019
update: check user upload credit is>= torrent size before allow torrent download, download.php, announce.php    
update: restrict user class to 3 active downloads at one time  

### 09 Mar, 2019
update: hide AJAX Chat until loaded  
update: dependencies  
remove: "dirname(__FILE__, 2)" not correctly parsed bu IDE  

### 03 Feb, 2019
bump: version to .6  
update: README  
update: dependencies  
update: check log file being viewed, for easy deletion log_viewer.php  
fix: empty $cats partials/categories.php  

### 28 Jan, 2019
update: composer.json  
remove: DarkAlchemy from namespace  
fix: hitrun_update.php  

### 27 Jan, 2019
fix: array_reverse log_viewer.php  
update: cleanup time backup_update.php  
fix: output not showing ImageProxy.php  
update: add text-shadow to logo.description  
fix: inserting incorrect var into array function_fanartp.php  
fix: Undefined index trivia_update.php  

### 26 Jan, 2019
update: Adminer 4.7.1  
update: add some transparent background to logo  
fix: display info_hash details.php  

### 23 Jan, 2019
fix: template not showing clouck when use_12_hour is false  
fix: disable auto_lotto  
fix: gzip path  

### 22 Jan, 2019
fix: search bar text color  
update: dependencies  
update: margin stats.php  

### 21 Jan, 2019
fix: style stats.php (i hope)  
fix: bin/install.php  
fix: database/mysql_drop_fks.php  
update: gzip all db files  
update: bin/import_tables.php bin/install.php  
update: all backup files to same filename scheme  
revert: backups to gzip for speed  

### 20 Jan, 2019
fix: trivia not resetting during cleanup  
update: staff picks icon  
add: icon-star-empty  
update: bookmarks.php sharemarks.php  
fix: permissions cache/imdb  
fix: trivia questions not resetting  

### 19 Jan, 2019
NOTES: you will need to 'mv include/config.php config/site.php'  
NOTES: php-cs-fixer is working with PHP7.3  
add: bookmark icon  
update: center search bar  
update: border color on search bar  
fix: ajaxchat.php style  
add: search bar  
update: replace color red/#f00 with #ff3860  
update: hide php/mysql version from non staff  
update: database files  
fix: delete posts  
fix: announce.php  
fix: install.php  
rename: many include'd files  
fix: ann_config.php  
moved: several define'd items to site_config  
update: flush entire cache or specific cache from cli bin/clear_cache.php  
update: remove auto download admin/backup.php  
update: use bzip2 admin/backup.php  
remove: site_config table  
update: move site settings to 1 file  
update: navbar  
add: php-cs-fixer  
fix: forums.php  
update: move many hard coded min staff class to site_config  

### 18 Jan, 2019
fix: bitbucket  
fix: delete templates cache on update/add/delete  
fix: get templates  
update: tidy up a few scripts  
update: incorrect css file  
add: cli database updater bin/update_db.php  
update: dependencies  
update: template creation message  
update: min class to view use template  
fix: achievements incrementing wrong users  

### 17 Jan, 2019
update: database files  
update: add poster to start of descr upload.php  
fix: missing lougout icon  
update: do not check all subcats on parent selection  

### 16 Jan, 2019
update: require bitbucket for poster requests.php offers.php  
update: require bitbucket for poster upload.php  

### 15 Jan, 2019
update: dependencies  
fix: upload.php  

### 14 Jan, 2019
update: changes for Percona 8.0  
update: tmdb and fanart languages set in site_config.php  
update: some forms to maxlength, lots to go  
fix: missing imdb_in function_imdb.php  
update: breadcrumbs  
update: page style tenpercent.php  

### 13 Jan, 2019
fix: last_reset reset  
update: run php-cs-fixer  
fix: incorrect lang vars used requests.php

### 12 Jan, 2019
update: torrent_hover.php torrenttable_functions.php  
update: move font awesome icons  
update: database files  
fix: typo failedlogin_update.php  
update: dependencies  
fix: setting perms on .cache files  
update: make tagline site_config setting  
fix: clear_ajaxchat  
fix: upload name with single quote  
fix: ebook lookup  
add: torrent count to categories  
add: categoryids.php  
fix: shuffle on empty array  
fix: margin takelogin.php  

### 11 Jan, 2019
add: genre to torrent hover  
fix: install.php  
update: database files  
update: failed login cleanup to run hourly  
update: failed login count display  
fix: missing code to display failed login count  
remove: reference to avatar creator  

### 08 Jan, 2019
fix: bitbucket not displaying uploaded images  

### 07 Jan, 2019
remove: file_put_contents logging  
add: cli installer  
remove: web installer  
remove: super_user from data.sql  
remove: images, person, imdb_info sql files  
fix: quoted subject and message lotteryclean.php  
update: check if var is empty not false torrents_lookup.php  
fix: increasing Re: on replied to messages  
fix: no spacing between divs lottery.php  
fix: no option to move to inbox when its in deleted box  
fix: reply to message with delete message checked not saving in deleted mailbox  
fix: not setting correct scheme download.php  

### 06 Jan, 2019
update: is var empty announce.php  
update: handle empty array comments.php  
update: depenencies  
remove: increment hits from get_all* Torrent.php  
fix: AJAX Chat not removing stale users from online users  
fix: AJAX Chat only show current user instead of all users  
update: remove duplicated seeders/leechers update announce.php  
update: add mysql log files, files need 655 to read  
update: check if path is_readable  
update: download_multi.php  
update: database files 
add: index on rating.topic  
add: index on over_forums.min_class_view  
add: index on forum_config.id  
add: index on users.perms  
add: index on forums.parent_forum  
add: index on forums.min_class_read  
add: index on posts.added  
add: index on topics.added  
add: add index on ajax_chat_online.dateTime  
add: add index on images.type  
add: add index on auth_tokens.expires  
update: rewrite query to use index  
update: check $_SERVER['HTTPS'] status if $_SERVER['REQUEST_SCHEME'] not set  
fix: not show image if no image is set comments.php  
fix: optimize image ImageProxy.php  

### 05 Jan, 2019
add: exception handling  ImageProxy.php  
remove: define XBT_TRACKER  
update: increase timeout ImageProxy.php  
fix: incorrect var images_update.php  
revert: IMDb cache back to cache folder  
fix: search by rating addparams browse.php  
update: navbar items to lang file  
add: search movies poster view tmovies.php  
fix: not show image when caticon is empty  
removed: category icons, not have proper permission from the creator  

### 04 Jan, 2019
fix: year in slider  
fix: cursor:pointer icon  
update: dependencies  
add: person type  
update: shuffle images for slider and scroller  
add: show unread pm's in navbar when mobile  
fix: show rating on hover  
fix: search by rating  

### 03 Jan, 2019
add: patrons to README  

### 02 Jan, 2019
update: nfo column to blob to allow for not utf8 characters  
fix: resized images overwriting other resized images images_update.php  
fix: png images not getting resized images_update.php  

### 01 Jan, 2019
update: increment hits when downloading download_multi.php  
update: remove limit from query latest_torrents_slider.php  
fix: many issues images_update.php  
add: several indexes to improve query speed  
add: link to remove all AJAX Chat messages staffpanel.php  
update: enable 3 character word search in fulltext indexes README  
fix: info_updated status not getting set images_update.php  
fix: sort order browse.php, latest_torrents.php  
add: indexes on imdb_id  

### 31 Dec, 2018
update: move UC_UPLOADER class check to $site_config for easier changing of allowed uploaders  
fix: typo view_forums.php  

### 30 Dec, 2018
fix: get_imdb_info()  
update: add backgroung color to text over background images  
update: add background image to torrent hover, if available  
update: GeoIP files  
fix: empty var cleanup/torrents_normalize.php  
add: goaccess access.log analyzer  
remove: extraneous cache file  
update: remove cached inside of cached newsrss  
update: reduce query string length browse.php  
update: reverse order log files log_viewer.php  
update: cron jobs log output  
update: database files  
update: import_tables.php allow cli args  
update: reduce cached time to 5 min newsrss.php  
update: update images limit each item to 50  
move: imdb language config to site_config  
add: updatad/checked columns  
add: delete user peers cache during cleanup  
remove: unneeded cached items  
add: bin/remove_altered_images.php  

### 29 Dec, 2018
update: missing cache items  
update: cheange PDO query warning message to info message  
add: bin/clear_cache.php bin/remove_torrents.php  
fix: cache stats when using file  

### 28 Dec, 2018
update: expiry time for cast to 30 days, up from 7 days  
remove: extra databases tables from initial site data table  

### 27 Dec, 2018
update: bzip2 instead of gzip addditional databases  
update: show the correct number of replies view_forums.php  
update: torrents.year to smallint  
update: limit cast images to first 15  
add: get imdb top movies, anime and tv shows  
update: add image optimize to validate_images.php  
add: bin/import_tables.php to easily import gzipped tables  
update: gzip additional database tables  
update: uncomment if statement  
update: hide category icons without an image  
update: cast birthay to readable month day, year  
update: cache plot IMDb plot lookup  
update: database files  

### 26 Dec, 2018
fix: incorrect primary key  
fix: try/catch optimizer  
fix: Christmas gifts only on Christmas  
update: don't cache anime titles, too big  
update: restrict image optimizer to 15 seconds per optimizer  
update: imdb info  
update: breadcrumbs  
remove: unneeded line breaks  
update: tooltipster  
move: IMDb cache to /dev/shm  
fix: ignore send mail error  
update: IMDb PHP  
remove: original blocks folder  
fix: check is server can send email before attemptinf to send email  

### 25 Dec, 2018
fix: gift cleanup  
fix: seedtime cleanup  
fix: anime_title_update.php  

### 24 Dec, 2018
fix: check for empty results before evaluating them  
fix: multiple image issue in AJAX Chat  
fix: missing closing div  
update: member_post_history.php  

### 23 Dec, 2018
update: delay time for tooltips  
fix: bbcode editor youtube  
fix: group by error view_forums.php  
replace: AniDb Titles with Anime Titles  
fix: initial user blocks setting for userdetails  
fix: typos  
remove: php-cs-fixer until they uppdate to PHP7.3  

### 22 Dec, 2018
fix: count stats_extra.php  
update: page formatting lottery/tickets.php  
update: add pager to stats_extra.php  
fix: stats_extra.php  
update: breadcrumbs for forum search page  

### 21 Dec, 2018
update: allow left column to be empty and hidden index.php  
update: more ways to get sudo user set_perms.php  
fix: admin/usersearch.php  
update: make search term required search.php  
fix: forums search  

### 20 Dec, 2018
add: missing forum quote style  

### 19 Dec, 2018
update: cleanup template formatting  

### 18 Dec, 2018
remove: radio link from navbar  
update: page formatting  
update: display last seen time when hover username  
fix: group by queries  
fix: invalid count active_irc.php, active_users.php, active_birthday_users.php  

### 16 Dec, 2018
fix: typo template.php  
replace: logname broken in Ubuntu 16.04, set_perms.php  
add: anidb titles update, runs daily  
add: anidb titles table  
update: set min/max rating in torrent search  
update: database files  
fix: not setting permissions for folders set_perms.php  

### 15 Dec, 2018
fix: missing update browse.php  
update: level align rows details.php  
update: set min/max release year in torrent search  
update: format cron controller logging  
update: make allow bbcode default for new forumn posts  

### 14 Dec, 2018
update: include search by imdb and isbn in cloud search  
add: search by IMDb and ISBN  
update: add cache to trivia block  
update: clear last post cache when deleting topic or post  
update: restrict search cloud terms to descr and name  
update: display last update time as static, not relative imdb block  
update: README to reflect the addition of Pu-239 Installer script  
update: 0777 to 0775 in install notes  
update: add ARGON2ID as default, when it becomes available to the server  

### 12 Dec, 2018
fix: child boards not showing properly  
fix: child forums not included in stats on main forum page  

### 11 Dec, 2018
update: page template bitbucket.php  
update: concatenate parent name with child name in index page torrent blocks  
remove: last usages of mg.gif  
fix: check items exist before validating takesignup.php  

### 10 Dec, 2018
add: upload image from url bitbucket.php  
update: optimize new images  
update: categories add parent name to child name for display  

### 09 Dec, 2018
fix: categories.js not working in firefox  
remove: mkint()  
update: update IMDb during images update  
fix: IMDb rating not showing on browse page  
fix: IMDb block not showing genres  
fix: find_images() not caching  
remove: star.gif, looks terrible  
fix: formatting rules.php  
fix: tabs users.php  

### 05 Dec, 2018
update: enforce password restrictions resetpw.php  

### 03 Dec, 2018
update: stats block formatting  
update: move reCAPTCHA logging to Sysop log  
fix: get votes count before user votes  
add: ajax toggle for 'show in navbar' staffpanel  
update: check for empty csrf token before trying to validate it  
update: increase cast image size  
fix: check for proper format of supplied date, birthdate  

### 02 Dec, 2018
fix: unneeded margins in index blocks  
fix: banner size  
fix: uglify.php  
cleanup: missing globals, parenthesis, quotes, etc  
update: PHPDoc elements  
remove: AJAXChat native database classes  
update: increase image sizes  
fix: IMDb rating, year, genre not updating if already cached  

### 01 Dec, 2018
update: database files  
add: caching to rss, default 5 minutes ttl  
remove: radio  
add: subtitles  
fix: parallax scrolling  
fix: gift.php  
update: optimize tables only if free data>10 MB  
add: admin/mysql_overview.php  
merge: human_filesize() and mksize() both provided same functionality  
replace: deprecated ereg() with preg_match()  

### 29 Nov, 2018
fix: rss.php  
fix: missing data-private torrenttable_functions.php  
fix: check for empty $CURUSER['language'], $CURUSER['dst_in_use'] before use bittorrent.php  

### 28 Nov, 2018
fix: correct owner set_perms.php  
fix: insert duplicate ip, announce.php  

### 27 Nov, 2018
fix: IMDb caching during images update  

### 25 Nov, 2018
fix: imdb poster not showing when imdb is from cache details.php  
fix: create empty arrays before use torrents_normalize.php  
fix: another attempt to fix birthday_update.php  
fix: torrent upload  
update: require isbn on ebook upload  
update: require imdb on movie/tv upload  

### 24 Nov, 2018
update: Adminer  
fix: missing key  
remove: ip2long  
update: rewrite as notify owner of pending deletion deathrow.php  
update: add several plugins to adminer  
update: add leading 0 on minutes for trivia game time remaining  
add: log all access to the staff panel  
add: user option to set/change font scaling usercp->personal  
fix: uglify.php not updating sceditor.js in production mode  
update: make site class code same as chat class code  
remove: last remnants of klappe_news  
remove: unused images  
update: adduser.php force same username restrictions and signup.php  
fix: user setting use_12_hour not taking affect  

### 20 Nov, 2018
fix: allow characters other than english as username  
update: force create dirs in bin uglify.php  
fix: AJAX Chat not picking up temp user class  
fix: show bbcode not getting set properly in edit_topic.php  

### 19 Nov, 2018
fix: incorrect css path sceditor.js  
update: add check_install_dir() for staff and above  
remove: lang files other that english, garbage/incomplete  
fix: not getting user selected lang when getting lang files  
update: include links to composer and node.js README  
remove: h1 tags in flash messages  
fix: loading reCAPTCHA when empty reCAPTCHA keys signup.php, invite_signup.php  
fix: incorrect logout fontello class name  

### 18 Nov, 2018  
update: reduce font size for tfreak  
fix: determine longest column parallax scrolling  
update: initial page font size  
update: star rating size  
fix: image path for rating  
fix: missing gif files  
add: staff_picks.png to browse.php  
update: sort torrents by staff_picks  

### 17 Nov, 2018  
replace: font-awesome icon-pencil with icon-edit  
update: sass, imdbphp  

### 16 Nov, 2018  
fix: reply to: subject/message not populating send_message.php  
fix: typo send_message.php  
update: allow '-' in category names  
add: font-awesome icon-key icon-users  
update: bookmarks.php and sharemarks.php use ajax  
remove: bookmark.php, aff_cross.gif, key.gif, public.gif   

### 15 Nov, 2018  
update: check user class staff_picks.php  
update: bookmarks add/delete to ajax  
update: reduce font size, increase line height for legend  
fix: add/remove bookmark not updating torrents page correctly  
update: remove fieldset/legend from index page blocks  

### 14 Nov, 2018  
update: columns in tablet stats.php  
update: missimg bookmark.php breadcrumb  
update: proper columns gap  
update: parallax scrolling only on desktop or larger  
update: proper column stacking order on mobile  

### 13 Nov, 2018  
fix: column position after scroll  
update: trivia display  
add: basic parallax scrolling to index, should be able to improve on it  
add: third column to index page  
fix: king update incorrect gloval variable  
fix: trivia update when questions array less than 100  
fix: trivia fail trivia_lookup.php  
remove: console.log trivia.js  
fix: insert query Referer.php  
fix: comments block not showing comment text  
add: runtime to cleanup scripts  
refactor: trivia to ajax  

### 12 Nov, 2018  
fix: typo in method call Snatched.php  
fix: don't give space to empty or disabled block index.php  

### 11 Nox, 2018  
fix: installer to reflect removal of ann_config.php  
add: prime caches cleanup  
add: some caching to queries in announce.php  
remove: padding around banner  

### 10 Nox, 2018  
cleanup: fomatting  
update: expand page to full width of screen  
update: regex for date formats log viewer  
update: easier to rearrange index page block  
update: 2 column index page  
fix: incorrect url in index page comments block  

### 09 Nov, 2018  
update: IMDb rating to stars  
refactor: common code to template  
update: store upload form user input and repopulate fields on return to page  
fix: bbcode images with width or height  
update: enable poll questions to use bbcode  
update: additional checks on ajax torrents  

### 08 Nov, 2018  
update: make torrent blocks initial state hidden  
update: make torrent blocks ajax userdetails.php  
fix: userdetails torrent blocks, incorrect values  

### 07 Nov, 2018  
fix: clear torrent cache details.php  
fix: subtitles on open edit.php not getting set  
add: autoshout for lottery start and tickets purchased  
fix: forums section view  

### 06 Nov, 2018  
update: drop unused column  
fix: incorrect user defaults in db schema  

### 05 Nov, 2018  
fix: some incorrect user defaults in db schema  
add: auto lottery, if enabled, the lottery will auto restart  

### 04 Nov, 2018  
fix: polls  
update: README to account for greater number of users  
fix: cleanup users achievement seedtime  
update: restrict AJAX Chat   @mentions to main channel  
add: delete log files from log viewer  
fix: ajax timeout  
update: improve ajax render times  
update: ajax scripts for uniformity  
update: remove some unused site_config settings  
update: remove some unuses ann_config settings  
remove: public/install/extra/ann_config.phpsample.php as not needed  

### 03 Nov, 2018  
fix: set/delete run cache images_update.php  
remove IE, announcement blocks  
fix: url_proxy trying to proxy 'N/A'   
update: ignored files  
remove: everything paypal  
update: no longer set poster, if empty, poster is strictly for user to set or not  
update: move descr formatting to ajax, if not already cached, improves page load time  
update: wiki breadcrumbs  
update: add previous/next links to torrent details page  
update: set torrents.imdb_id in takeupload.php  
add: left/right angle font awesome  
remove: banner and background from torrents table  
add: get images from images table as needed  
update: show subtitles in details.php  
remove: banner overlay  
fix: use user poster in imdb block  
add: get_torrent_count() Torrent.php  
add: click to IMDb on title imdb block  
fix: remove table backups older than 6 hours  
fix: update tvmaze  
fix: update on/off cleanup item  

### 02 Nov, 2018  
fix: viewnfo breadcrumbs  

### 01 Nov, 2018  
fix: missing name in form new_topic.php  
fix: breadcrumbs when action is page  
move: ratio_free, crazy_hour and happy_hour to define.php  

### 31 Oct, 2018  
add: view log files with extension '1' log_viewer.php  
fix: error when there are topics but no posts view_forum.php  
update: add try catch to pdo query upgrade_database.php  
update: class_config from cache file to site_config  
update: file output format user_functions.php  
update: get_birthday_users fetch to keep mysql packet size small User.php  
fix: values should be an array of arrays function_autopost.php  
update: set chat styles per template  
update: set class colors per template  
move: chat css classes into template folders  
remove: unused columns from select User.php  

### 29 Oct, 2018  
fix: undefined var admin/upgrade_database.php  
fix: incorrect var name imdb_lookup.php  
fix: admin memcached stats when using sockets  
fix: seedtime cleanup once per userid, not once per torrent  

### 28 Oct, 2018  
update: get poster from images table when torrents.poster is empty catalog.php, browse.php   
fix: missing requires  
update: get poster from images table when torrents.poster is empty index blocks  
add: .well-known dir  
update: breadcrumbs  
update: resize signature and avatar in forums  
update: version to 0.5  
update: database files  
fix: installer updating removed table  
fix: installer remove checks for settings depecrated by MariaDB 10.3  
update: README  
update: global blocks  

### 27 Oct, 2018  
update: wrap sql errors in pre tabs log_viewer.php  
update: remove filesize limits log_viewer.php  
update: function_breadcrumbs.php  
update: pagination to log_viewer.php  
update: viewtickets.php  
fix: flash message using html instead of bbcode tickets.php  
fix: autobahn recording stats  
fix: removed columns snatches.php  
fix: seeders/leechers getting correct count torrents_update.php  
update: update forum_config min_delete_view_class when adding/removing classes  
update: edit topic, post reply to use same html  
fix: forum signatures  
fix: forum attachments  
fix: update forum classes when adding/removing classes  
fix: autoshout for new topics/replies  
fix: memcached stats when using sockets  
fix: polls  
update: README  
update: allow memcached to use sockets  
update: allow mysqli to use sockets  
remove: avatar folder, not used here  
fix: typo ann_config.php  
add: missing HnR settings from ann_config.php  
fix: call incorrect method name announce.php  

### 26 Oct, 2018  
update: version to v0.4  
fix: call to wrong method takelogin.php  
fix: send message to user when login failed takelogin.php  
update: show client and peer id view_peers.php  
remove: unused/duplicate functions scrape.php  
fix: divide by 0 annoounce.php  
fix: non numeric rating function_imdb.php  
fix: display peer_id allagents.php  
update: triviaq questions, removed dupes  
update: many cleanup scripts to FluentPDO, reduce number of queries  
rename: column 12_hour to use_12_hour  
update: FluentPDO, autoprefixer, browserslist  
update: easier to update/add stats stats.php  
update: use FluentPDO bans.php, upgrade_database.php  
update: many timestamp columns to unsigned int  
add: added column to topics table  
change: sort order for cleanup processing cron_controller.php  
update: delete deleted messages also, only update caches if cache already exists Message.php  
update: check if torrent exists before deleting Torrent.php  
remove: stats table, no need to store these stats in db  
remove: readpost_update.php can't see any reason to reset a users read post history every 2 weeks  

### 22 Oct, 2018  
fix: check if REQUEST_URI is empty, mainly for running cron_controller.php from cli  
fix: update user birthday_update.php  
remove: insert method User.php  
fix: don't show pager menu when only 1 page reports.php   
fix: division by 0 pu_update.php  

### 21 Oct, 2018  
fix: get from array happyhour_update.php  
fix: freee_switch not resetting freeuser_update.php  
fix: timesann not updating announce.php  
update: set default userid userdetails.php  
fix: don't show pager menu when only 1 page browse.php  
add: missing torrents/users   
fix: user notification on upload to their defaul categories  
update: fluentpdo, php-cs-fixer, flysystem, node-sass  
fix: peers not always deleting on stop announce.php  
fix: happy hour  
fix: properly clear torrent caches when deleting or adding  

### 20 Oct, 2018  
fix: edit admin pages select current staffpanel.php  
remove: file_put_contents logging  
fix: php_info display system_view.php  
update: a few more of the admin pages  
add: missing file promo.php  
update: most of the admin pages  
fix: bug bugs.php  
update: don't show pager when not more than 1 page staffbox.php, bugs.php  
update: align checkboxes upload.php  
update: SCEditor style  
update: missing tags from breadcrumbs  

### 18 Oct, 2018  
update: FluentPDO, Flysystem, Sass, PostCSS, autoprefixer, node-sas  
update: version number package.json  
fix: news block icon size  
fix: iterate only numeric template folders uglify.php  
fix: call image_proxy with width/height when bbcode image tag includes width/height  
fix: db insert Post.php  
update: missing tags from breadcrumbs  

### 14 Oct, 2018  
add: lighter template WIP - will need to rethink AJAX Chat templates  
rewrite: breadcrumbs, WIP  
replace: get all users with ajax user lookup send_message.php  
replace: markItUp.js with SCEditor.js WYSIWYG Editor  
remove: jquery.mousewheel.js  
move: jquery.flexslider.js to npm  
move: tooltipster.js to npm  
remove .htaccess files as nginx ignores them  
add: new base template  
move: themeChanger into template folder to allow modification per template  
replace: FormManager.js with genres_show_hide.js  
replace: jquery.validator.js with html5 validation  
fix: Adminer link in navbar  
remove: ipToStorageFormat function  

### 11 Oct, 2018  
fix: site_settings.php  

### 10 Oct, 2018  
fix: announce.php  

### 02 Sept, 2018  
update: database files  
remove: image proxy key  
update: remove leading dots in cookie_domain example  
add: tag   @username anywhere  
update: add columns year, rating updated from api calls  
update: api calls to ajax requests for faster initial page load details.php  
update: merge tvmaze title info with episode info  
remove: ssluse, determine by site scheme instead  

### 28 Aug, 2018  
update: full stop when files.php is missing  

### 27 Aug, 2018  
update: add notice if missing files.php  

### 26 Aug, 2018  
update: placeholder_image() allow variable size  
update: move imdb info to top of page details.php  
update: use minimal bulma, only as needed  
refactor: navbar.php  
add: node-sass to compile sass files  

### 24 Aug, 2018  
fix: redecalare function validip() ann_functions.php  
fix: using var before set  
fix: get correct class from similar staffpanel names  
update: insert/select user ip correctly  
remove: check_selected.js  
fix: check if var is empty before using it watched_users.php  
fix: typo forums.php  
update: limit displayed users count active_24h_users.php  

### 23 Aug, 2018  
add: missing flip_box.js uglify.php  
fix: typo stelth  
fix: delete user inactive.php  
fix: display freeleech.php block  
fix: set/unset bonus edit_moods.php  
fix: check if var is empty before using it torrenttable_functions.php  
fix: users class not sending messages grouppm.php  

### 22 Aug, 2018  
fix: display ip view_peers.php  
fix: validate IPV6 address while getting user ip address  
fix: returnto logout.out when logging in after clicking logout  
fix: remember me token expires  
cleanup: class files to do only what class relates to  
update: placeholder text freeleech.php  
fix: typo lang files  
fix: use incorrect userid variable usercomment.php  
add: remember me expires to site_config.php  
update: database files  
update: allow only 1 for each modifier admin/freeleech.php  

### 21 Aug, 2018  
fix: serialize data, multiple values admin/freeleech.php  
fix: initial cookie_lifetime value, during install  
remove: bottom margin staff picks block  
add: missing free_details.php  

### 20 Aug, 2018  
update: reset remember me cookie expires time when remember token is updated in db  
add: cache entry to destroy user session when changing password, disabled, etc  
update: destroy all users auth_tokens when logging out  
update: delete all expired auth_tokens when generating new token  
fix: multiple logins destroying other login remember me tokens  
update: insert/update in 1 query instead of delete/insert set_remember  

### 19 Aug, 2018  
fix: undefined index get_date((int) )  
update: add required fields to signup.php, invite_signup.php, requests.php, offers.php  
fix: null referer when url not wrapped in url tags AJAX Chat  
update: jquery.validate.js  
remove: mods folder, not used  
update: use php date() instead of mysql NOW() for search  
update: use random_bytes() instead of make_password() to generate auth tokens  
add: change remember me tokens once used  
add: additional user whereis locations  
update: explicitly start session when using remember me token  
update: remember me fuction to update expires time while user is active on site  
add: flushDB() to flush only the site specific db when using redis as cache driver  
update: imdbphp class  

### 18 Aug, 2018  
update: tooltip formatting movies.php  
update: imdb_info_short to find images on fanart or omdb, when imdb image not found  
add: create_image() to create dummy src images for lazy loading  
update: additional images lazy loaded  
fix: typo lang_pm.php  
fix: check incorrect var view_message.php  
fix: typo ajax_chat_cleanup.php  
add: missing pirate.png  
fix: AJAX Chat disabled gif  
fix: incorrect lang key for AJAX Chat messages  
update: show users message when banned from AJAX Chat  
update: remove img tags from AJAX Chat messages after 24 hours  
update: hide anonymous users from showing in AJAX Chat Online list  
fix: last post link forums.php  
update: database files  
fix: comments edit/delete  
fix: invalid Cache dir site_config.php  

### 16 Aug, 2018  
fix: check downloaded file is actually an image before optimizing it  
update: composer imported classes  
add: wrapper() to all admin/files  
add:   [br  ] to format_comment()  
fix: take_theme.php incorrect comparison  
fix: staff allowed fail post to correct forum/topic  
remove: pager_new.php  
remove: resize_image()  
update: validemail() to use filter_var  
fix: get_stylesheet()  
fix: allow avatar/signature with '  ?' in the url  
fix: AJAX Chat View Logs  
update: AJAX Chat polling timer to 45 seconds after 10 min inactive  
update: uglify.php to update all templates within templates folder  

### 08 Aug, 2018  
fix: missing image uploader_info.php  
fix: missing require user_unlocks.php  
update: show poster, banner, background images, IMDb info in requests.php, offers.php, upload.php when using valid IMDb url and images are available  
fix: allow anything class UC_UPLOADER and above to upload  
fix: after upload multiple images to bitbucket, you can select any link correctly  

### 07 Aug, 2018  
fix: write_css_class()  
move: all delete torrent functions to Torrents Class  
add: IMDb lookup on upload.php  
add: ISBN lookup on upload.php  
remove: unneeded link to google fonts AJAXChatTemplate.php  
fix: Tooltips  
fix: overwrite key browse_js uglify.php  
update: write_classes() to write chat/js/config bbCodeTags  

### 06 Aug, 2018  
fix: clear index page user blocks when edit any user  
fix: missing staff_picks mytorrents.php  

### 05 Aug, 2018  
fix: countable AJAX Chat  
fix: path to up.gif torrenttable_functions.php  
move: find images in details.php to function_get_images.php  
update: allow : in torrent name  
add: verify file is actually an image ImageProxy.php  
move: background-16 to default.css  
add: attribution to colorpicker.js  
fix: get correct page/post when post count greater than per page  
fix: AJAX Chat stats  
fix: bluray releases  
fix: admin user delete account  
fix: admin account manage delete user account  
fix: promise exception AJAX Chat  
add: animation to flipper  
move: flipper to seaparate file  
move: navbar to separate file  
add: apis block to enable/disable individual apis  

### 03 Aug, 2018  
update: set cache for failed apis to limit retries  
add: Staff Picks  
fix: delete cache when updating bonus  
fix: adminer  

### 02 Aug, 2018  
fix: invalid date birthday.php  
fix: remember token getting deleted when flush cache  
fix: AJAX Chat ban user  
fix: string literals announce.php  
fix: trivia showing icons  
fix: schema for cheaters userip  
update: jquery remove to fade out  

### 01 Aug, 2018  
fix: typo lang_torrenttable_functions.php  
fix: use correct key for userid news.php  

### 31 Jul, 2018  
fix: typo bbcode_functions.php and anatomy.php  
fix: typo takeedit.php  
fix: return false if $image is empty bittorent.php  

### 30 Jul, 2018  
fix: comments avatar details.php  
fix: allow special chars for db password backupdb.php  
move: breadcrumbs() to its own file  
update: several cleanup scripts to fluentPDO  
fix: incorrect lang variable name used modtask.php  
fix: typo delete_torrents_update.php  
update: return query ip ajax chat  
update: delete user caches when set disabled  
update: chat destroy session when user is disabled  
add: missing require takeedit.php  
update: check if var is empty before using it function_books.php  
add: book update to images update cleanup  
fix: ignore query upgrade_database.php  
update: delete session for disabled users  
add: button to ignore query on database update  
update: allow staff site access when site set offline  
remove: bad banner  
fix: get lang before use of lang  
fix: install set correct announce https url  
fix: left join when no topic forum_posts.php  

### 29 Jul, 2018  
add: default banner.jpp  
add: dbconn() to all cleanup scripts  
remove: time_adjust and time_offset from site_config  
update: inner join on torrents comments.php  
fix: when empty array comments.php  
update: site_config  
update: assumed fk inactive_update.php  
update: drop fk contstaint on dbbackup  
update: drop fk constraint on bans  
fix: customsmilie_update.php  
update: drop fk constraint on bannedemails  
update: site_config  
fix: redis flush cache flushing all databases instead of just redis site db  
update: site_config  
fix: torrents_update.php  
update: database files  

### 28 Jul, 2018  
fix: card table background  
fix: nfo display viewnfo.php  
update: clean image caches when poster added to torrent  
remove: XBT Cleanup Items  
update: Adminer 4.6.3  
revert: css image to base64  
fix: postcss css cleanup  
update: text for bad id details.php  
fix: redirect to index page and show flash message when torrent not exist details.php  
fix: upload description removing characters such as   ë,   è,   é, and   ê    
fix: upload stripping ascii from description instead of nfo  

### 27 Jul, 2018  
update: add class color to flash message hnrs.php  
fix: format_username can't be used with flash messages hnrs.php  
fix: direct link to post view_topic.php  
replace: file_get_contents() with fetch() tfreak.php  
update: use global $fluent instead of passing it User.php  
add: delete_by_id() Torrent.php  
replace: MyPeers_ with peers_  
fix: first db connection error takeupload.php  
update: search.php bot use only  
fix: delete torrent not deleting all caches and all likes to comments, only remove bp if torrent newer than 14 days  
fix: tfreak block if no results  
fix: only grab urls that are not empty images_cleanup.php  
update: browse.php search by genre full text search  

### 24 Jul, 2018  
remove: timeout from fetch guzzle config  
fix: save image to path store_image()  
fix: get backgrounds when url is empty function_bluray.php  
fix: unquoted key send_message.php  

### 23 Jul, 2018  
fix: remove dd($url) from function_tmdb.php  
update: add try/catch to fetch()  
fix: typo in newsrss.php  
update: cleanup_manager.php replace edit/delete icons  
update: Image Proxy to use fetch()  
fix: function_bluray.php missing requires  
add: Newsrss cleanup to remove news entries older than 30 days  
update: only post newest nrewrss item when starting for the first time  
fix: newsrss initial cache  
fix: gifs no longer optimized on the fly by image proxy  
fix: display no friends when friends list is empty userdetails.php  
add: missing Avatar toggle in user blocks, currently only used in userdetails.php  
fix: bbcode image class  
fix: typo in dragndrop.js  

### 22 Jul, 2018  
fix: view_mailbox.php breaking template  
fix: log_viewer.php not displaying all files correctly  
fix: incorrect use of $cache- >add  
update: only show block in user_blocks if block is enabled by site  
remove: redundant columns from users table  
fix: bitwise for opt2  
remove: imacss and postcss-font-base64  

### 21 Jul, 2018  
update: bitbucket.php using ajax drag and drop  
remove: incorrect method parameter stdhead()  
remove: avatar path from bitbucket  

### 20 Jul, 2018  
cleanup: formatting, alignment, replace join with implode  

### 19 Jul, 2018  
fix: youtube link on upload  
update: database files  
add: php-cs-fixer and postcss to uglify.php  
add: auto download torrent after upload  
fix: typo in function_tvmaze.php  
fix: trivia results on index page not showing scrollbar  
update: log vierer better messages splitting  
fix: direct link to post button  
fix: latest comments poster not using url_proxy  
fix: removal of background  

### 18 Jul, 2018  
update: database files  
add: latest torrents slider using flexslider and torrent banners  

### 17 Jul, 2018  
fix: background image not 100% width  
fix: bluray releases getting incorrect imdbid  

### 16 Jul, 2018  
add: bluray.com new releases page  
replace: file_get_contents() with fetch()  
add: omdb lookup to images_cleanup.php  
fix: random_int larger int listed before smaller int casino.php  

### 15 Jul, 2018  
fix: missing var  
add: OMDb API  
add: upcoming movies from imdb  
merged: all lists into 1 file  
update: check file/folder exists before processing  
fix: images cleanup  
update: composer require PHP  >= 7.2.0  
update: increase font size slightly  
cleanup: general code cleanup, phpstorm  
update: database files  
fix: all like buttons  
add: additional like buttons  
update: all comments displayed in same manner  
add: imdb info in requests and offers  
add: cleanup script to download anf optimize images if using image proxy  
remove: poster from image background  
add: try/catch ImageProxy  
fix: time sort in tvmaze.php  
update: tmdb image sizes for better quality  
add: min-width to index page blocks titles  
rename: pm_system messages  
update: pager_new.php pager_functions.php to return limits for fluentPDO  
remove: manage_likes table  

### 13 Jul, 2018  
fix: make action 'default' usercp.php  

### 12 Jul, 2018  
fix: check if dir exists before traverse it log_viewer.php  

### 11 Jul, 2018  
fix: background not displaying details.php  
fix: query non existent table  
add: TMDb Top 100 Movies  
add: images table to store image urls  
update: API get images to store urls  
update: get_body_image to get from images table and get poster or background depending on screen orientation  
update: normalize relative urls in js files  
remove: Atlantis from countries signup.php invite_signup.php  
fix: error on valid empty post contents takesignup.php  
remove: optimize/blur gifs ImageProxy.php  
fix: check if empty beofre use details.php  
update: README innodb_autoinc_lock_mode   = 0  
fix: undefined $count admin/bans.php  
fix: search by category browse.php  
fix: missing require thanks.php  
fix: missing require bookmark.php  

### 9 Jul, 2018  
fix: uptime time display  
fix: mybonus.php sort order  
fix: crazyhour global message  

### 8 Jul, 2018  
fix: get empty image get_body_image()  
fix: avatar max-width  
fix: update user birthday when birthday not set  
fix: get random image get_body_image()  
update: delete get_body_image() cache on new upload  
fix: url getting deleted when edit takeedit.php  
update: remove banner and background when torrent poster is changed  
update: clean up get_show_name() for better search results  
fix: emoticons.php  
update: bitbucket.php allow webp images, optimize images after upload  
update: details.php to show which api lookup results are showing  
update: composer  
fix: background art not showing when enabled  
add: user option to use 12 or 24 hour clock everywhere except AJAX Chat(working on it)  
update: database update process  
remove: some extraneous spaces in lang files  

### 7 Jul, 2018  
fix: plural question(s) in trivia points pm  
add: option to use 12 or 24 hour clock everywhere except AJAX Chat(working on it)  

### 5 Jul, 2018  
fix: remove invalid chars from uploaded nfo before db insert  
fix: typo in bittorrent.php  
fit: incorrect method name  

### 1 Jul, 2018  
fix: check if folder exists before setting perms set_perms.php  
update: set new/updated files owner:group according to current owner:group set_perms.php  
fix: check for empty vars before using them signup.php  
fix: install folder check  
fix: incorrect file permissions in 775 folders  
fix: bit bucket link not showing  
update: max image size in AJAX Chat  
fix: images in AJAX Chat  
remove: unused gzip directory  
add: logs folder  
fix: permissions  
update: README to correct install directions  
remove: redundant fetch() functions  
replace: remote image proxy with local image proxy, enable only if you have the space for it  
purged: database_updates, rethinking the process  
updated: README to include required apps and php extensions  
add: option to show random background on every page  
add: lazy load most images  

### 27 Jun, 2018  
update: adjust avatar display size for consistency  
update: use real ip when logging failed logins  
add: TVMaze TV Schedule  
add: php-bz2 requirement for TVMaze TV Schedule  

### 26 Jun, 2018  
update: hide video butto in AJAX Chat on small screen  
add: notice to run uglify.php when changing classes info  

### 25 Jun, 2018  
update: limit video size in AJAX Chat  
add: video player to AJAX Chat  
fix: banner width  
add: static and video banners to top of template  
update: enable background image on details.php  
add: gm.gif and hiya.gif  
remove: bans and bannedemail foreign keys  
fix: missing initial site statitics data  
fix: allowed staff and missing lang reference in backup.php  

### 24 Jun, 2018  
fix: check for empty pm ids before attempting to delete them  
change: anchor tags to id from name for uniformity  
fix: incorrect url for announce  
update: resetpw.php when importing from U-232 allow compare hintanswer with md5  
add: forum_id column to over_forums  
remove: unused css links in forums.php  
remove: direct link to Pu-239.pw/forums.php  
fix: staff forums id  
fix: over_forums.php edit forum with same sort number  
update: tv.php function_tmdb.php to get tv listing by date, pretty limited set  
update: use internal cache flush method  
update: limit AJAX Chat images to 50% height 50% width  

### 23 Jun, 2018  
add: tv.php use TMDb to get a list of tv showings airing today  
add: movies.php use TMDb to get a list of movies released any particular week  
fix: emoticon size  
cleanup: avatar and icon display  
fix: forums scroll to post  

### 22 Jun, 2018  
fix: image proxy when using width/height  
add: image proxy in AJAX Chat  
fix: almost all formating of usernames is format_username((int) )  
remove: preview buttons  
fix: AJAX Chat not using override_class  
replace: log viewer to allow reading gzipped files and sqlerr_log files  

### 18 Jun, 2018  
disable: additional newsfeeds  
remove: binary_operator_spaces from php_cs_fixer  
update: function_imdb.php to handle failure  
update: uglify.php to create all needed files, instead of copy example files  

### 17 Jun, 2018  
remove: unneeded var  
update: forums display, still incomplete but much better  
fix: achievementhistory.php table display  
fix: achievementbonus.php  
fix: userhistory.php  
fix: admin/iphistory.php display  

### 16 Jun, 2018  
fix: several admin pages issues  
change: pre to code to keep bbcode code block from breaking template  

### 14 Jun, 2018  
fix: tvmaze lookup  
add: clear torrent cache button on details page  

### 13 Jun, 2018  
fix: remove deleted columns from query mytorrents.php  
update: guzzle request in function_fanart,php  
update: normalize user classes so that adding/removing most classes will not need any code changes  
fix: typo  
remove: redundant parked() check in forums.php  
fix: faq.php output  
update: ip logging false by default  
update: all requests for ip, except announce and scrape, use the function getip() to facilitate logging ip true/false  
fix: calling format_username((int) ) with array instead of id  

### 12 Jun, 2018  
fix: friends.php  

### 9 Jun, 2018  
fix: typo  
update: details.php youtube to match others  
fix: imdb lookup  
fix: site forcing new users to https  
fix: query using incorrect id  
fix: classcolors.css overwrite on git pull  
fix: classes.js overwrite on git pull  
fix: missing ip class_check.php in query  
fix: typos  
fix: tracker bencode issue, you will need to manually merge this with your include/ann_config.php  
update: ssluse for everything, always if using https  
fix: announce.php  
replace: deprecated function each()  
fix: incorrect lang var  
add: missing sqlerr in many forum queries  
fix: bbcode code block breaking layout  

### 8 Jun, 2018  
add: rounded corners to youtube  
fix: youtube display  
fix: class add/remove not updating AJAX Chat classes  
fix: flush cache when driver is 'file'  
fix: adding/removing user class not updating class colors  
fix: ambiguous 'status' view_forum.php  
add: missing sqlerr in many queries  
fix: missing space in query view_topic.php  
fix: missing global  
add: AJAX Chat command takeover to allow staff to speak as BOT  
add: AJAX Chat command announce to allow staff to speak as BOT and send as a PM to all users  
add: missing help info to AJAX Chat commands  

### 7 Jun, 2018  
fix: missing quotes  
add: bin/set_perms.php script to set file permissions to 0664  
add: bin/uglify.php script to process css/js files  

### 6 Jun, 2018  
fix: missing '/'  
fix: incorrect column name used  
fix: properly display html chars in forum posts  
fix: missing js file  
fix: userdetails.php using CURUSER details instead of id.user details  
fix: missing semicolon in takesignup.php  
fix: missing js file signup.php  
update: missing response var  
fix: reCAPTCHA success check  
update: README.md  

### 5 Jun, 2018  
replace: jquery.simpleCaptcha-0.2.js with reCAPTCHA V2  
fix: userdetails.php tabs not working  
fix: >UC_UPLOADER should not be demoted to UC_VIP when donating  
fix: remove Cache class instantiation  

### 4 Jun, 2018  
update: when cleanup item is missed, next cleanup time is set to time later than now  
fix: cleanup manager not updating or creating cleanup items  
update: timeout for all index page blocks  
fix: set timeout on cron_controller cache, in the event any cleanup failed cleanup would not run again  

### 3 Jun, 2018  
remove: mcrypt check during install  
change: some non-expiring cache items to 86400  
move: 404.jpg, off.gif, on.gih to public/images/  
fix: admin/events.php format_username  
fix: admin/editlog.php remove check_user_status  
remove: mcrypt dependency for bitbucket  
add: bucket folder for bitbucket  
update: bitbucket.php  

### 2 Jun, 2018  
change: persistent db connections to false  
fix: typo in ajax_tooltips.php  
remove: show_staffshout from database  

### 30 May, 2018  
remove: tfreak.php has no need for check_user_status since not loaded directly by user  
fix: typo in cleanup_manager.php  
add: ability to reset cleanup time to yesterday midnight from cleanup manager  
remove: radio.php has no need for check_user_status since not loaded directly by user  
remove: need to hash check, salty function for logout.php  
revert: reversed conditionals  
fix: a couple of var set that should have been a comparison  
replace: sizeof with count for consistency  
replace: __DIR__ with dirname(__FILE__) for consistency  
update: README to include required MariaDB settings  
add: check for innodb_file_format , innodb_large_prefix, innodb_file_per_table in mysql during install  

### 27 May, 2018  
remove: cleanup from user thread, remove autoclean and register_shutdown_function  
add: cronjob to contol cleanup on separate thread  
update: manually set $_SERVER  ['HTTP_HOST'  ]   = $site_config  ['domain'  ] when $_SERVER  ['HTTP_HOST'  ] and $_SERVER  ['SERVER_NAME'  ] are empty  
update: login.php move 'remember me' above login button  
update: welcome message to new users  
update: cleanup info for hourly backup  
update: rewrite Peer Class to remove need for ANY_VALUE, MariaDB does not have it   

### 24 May, 2018  
add: check for mysql variable innodb_large_prefix   = 1 during install  
update: schema tokens table to dynamic  
add: password_needs_rehash to login  
update: check to assure PASSWORD_ARGON2I is available  
fix: errors in log_viewer.php  
update: use PASSWORD_BCRYPT for php versions prior to 7.2.0  

### 23 May, 2018  
add: limit to number of cleanup scripts processed each run  
update: increase karma achievement to start at 250, users start with 200  
fix: undefinded lang in cleanup  
update: clear default cache during install  
fix: dissallow cleanup scripts to run before installation completed  
fix: typo in adduser.php  
fix: count in forum_posts.php  

### 16 May, 2018  
fix: topics view increment  
update: dependencies  
update: replace password_hash algorithm bcrypt with argon2i, this only effects new or updated passwords,  
      all previous password will continue to use bcrypt until password is updated  

### 13 May, 2018  
update: move check for width/height to after if proxy url exists  
remove: image proxy default url  
update: enable latest forum posts on index page  

### 12 May, 2018  
request: allow any alphanumeric character in username #4  
update: require all new users or change username to use function valid_username  

### 11 May, 2018  
update: cleanup process runs in perpetuity once started  
fix: incorrect implementation of autoclean perpetuity  

### 10 May, 2018  
update: cleanup process select all scripts that need processing instead of just 1  

### 9 May, 2018  
fix: format_username in pm_system  

### 19 Apr, 2018  
fix: fully revert navvbar changes  
fix: README typo #3  
fix: newest user block shows user id instead of formatted user name #1  
fix: site stats not populating  
fix: call getUserFromId() with array instead of id  

### 18 Apr, 2018  
update: README  
fix: incorrect var for users id  
update: cookie lifetime to 1 as default  
fix: site not working without redis installed and used as php session handler  

### 17 Apr, 2018  
update: README  
### 4 Mar, 2018  
update: composer.json require php extensions  

### 3 Mar, 2018  
update: README, removed test sites  
update: db files  
add: stock forums(mostly done, but not happy with it)  
add: cache insert/update AJAX Chat user online, only hit db once per 60 sec per user  
fix: php-cs-fixer   @Symfony reversing if comparisons(yoda style)  

### 25 Feb, 2018  
fix: global user expecting users data, instead of class instance  
fix: announce update not updating peers cache  
add: stock forums  

### 23 Feb, 2018  
cleanup: add additional rules to php-cs-fixer(will run before any new commits)  
revert: use globals in functions, until all moved to classes(i'm still learning)  
remove: class destructors  
remove: persistent connections to pdo  
add:   @Symfony to php-cs-fixer default rules  

### 22 Feb, 2018  
add: destructor to classes  
add: persistent connections to pdo  

### 21 Feb, 2018  
move: project classes to src  
add: namespace to project classes  
move: manage jquery with npm  
fix: usercp.php password change  
fix: AJAX Chat not login user when not in online list  
replace: some globals with new Class()  
move: begin moving functions to class methods  

### 18 Feb, 2018  
remove: remember me cookies when user logs out  
move: all cookie functions to cookie class  
move: foreign key scripts to database folder for use after install folder removed  
add: Torrent Class  
add: User Class  

### 17 Feb, 2018  
fix: stylesheets for firefox, should now appear the same in all browsers  
fix: unauthenticated user redirected to login  
move: all session related tasks to Session class  
add: encrypted remember me cookie, set at 1 year  
move: AJAX Chat cookies to localstorage, no need to pass them back and forth with each request  

### 10 Feb, 2018  
fix: delete cookie at logout  
fix: admin failedlogins.php  
add: secure cookie store for userID  
remove: userID from sessions  
remove: call to undefined var arcade_top_scores.php  
update: AJAX Chat polling timer  
update:image_proxy function to match updated simple image proxy  
update: prefer ogg over mp3 audio  
add: missing ogg/wav sound files  
add: anonymize url in advertise block  

### 9 Feb, 2018  
fix: wrong user var used in recover.php  

### 7 Feb, 2018  
update: a few minor fixes  

### 5 Feb, 2018  
update: google books api, track api usage and disable if exceeded. cache only book data, not formatting  
fix: birthday cleanup  
update: books function check if empty  
remove: duplicate trivia questions  

### 4 Feb, 2018  
update: download torrent  
update: delete torrent  
update: edit torrent  
update: add flush cache for UC_MAX  
update: redis cache to choose database  
update: trivia always autrefresh, hide days when lees than 1 day  
update: rating when torrent not downloaded  
update: queries block  
fix: cache variable  
add: cache stats for memcache, redis, apcu and files  
hide: display of stats from all but staff  

### 3 Feb, 2018  
fix: freeuser_update.php  
add: redirect to login on no session  
update: sql files  

### 2 Feb, 2018  
fix: typo in site_config hnr  
remove: duplicate, unused images  
fix: rating system, using ajax  
add: torrent search suggestions  
move: create session variable salt to login function  
fix: 350 millisecond page load delay on page refresh  

### 1 Feb, 2018  
update: sql files  
fix: code block bbcode on ajax chat  
fix: users cache for AJAX Chat when staff add user  
fix: session canary and regenerate session id  
add: encrypted session data, may require delete current sessions  
update: emails to html  
add: mail class to send emails, adds headers and sennds both plain text and html versions or email  
update: torrents scoller to force image dimensions, images all face-up  

### 28 Jan, 2018  
update: wiki item formatting  
update: catalog.php formatting remove bbcode from description  
update: catalog.php formatting when no peers  
add: catalog.php  
update: alphabatize nav menu links  
update: wiki highlight active tab  
update: make AJAX Chat default audio html5, flash is not working in current ubuntu chrome  
update: get_poll return false when no poll  
update: update poll_data cache when user votes  
update: scroll to poll if user not voted  
update: use fluentpdo in tvmaze functions  
update: tvmaze and imdb api to limit cast to first 30  
update: tvmaze and imdb api to only cache the data, not the html  
add: tvmaze table with all tvmaze ids for quicker lookup  
add: tvmaze cleanup to get new tvmaze id's  
update: README to show tvmaze insert/update  
update: sql dumps to allow update  
update: tvmaze api to include episode info  
update: fanart api to include tv   

### 27 Jan, 2018  
remove: check for imdb dir during install  

### 26 Jan, 2018  
fix: polls  
remove: thumbsup, torrent rating is sufficient  
update: set overlay height correctly  

### 25 Jan, 2018  
add: fanart api banner, poster, background  
fix: validator.php  
update: staff change user class to allow UC_MAX to set any user to any class  

### 24 Jan, 2018  
fix: left margin on details poster  
fix: incorrect array being passed to get_reputation  
add: ability to use a simple, encrypted image proxy for hotlinked images  
update: blackjack better appearance for mobile  

### 23 Jan, 2018  
add: isbn field to edit.php  
update: fix get correct isbn 10/13  
update: minified js/css files  
update: add anonymizer_url to imdb cast and trailer links  
update: update categories  
update: apis will set poster to api poster, if poster unset, without page refresh  
update: book uniformity  
update: tvmaze uniformity  
update: details.php youtube display highest quality video available  
update: imdb info  
remove: unused beep.mp3 and beep.ogg  
fix: incorrect var name for newsrss enable/disable  
replace: imdb class with imdbphp class via composer  
update: resetpw to use token auth  

### 22 Jan, 2018  
update: comment.php  

### 21 Jan, 2018  
update: trivia questions  
fix: undefined vars in staffpanel.php  
remove: log queries from staff panel  
fix: anonymous username in popups  
remove: log queries  
update: adminer.php 4.4.0  
update: check if vaiable is set before counting  
update: missing expire time in site_config  
update: details.php  
merge: userstatus_ and user_status_ caches  
update: snatched_staff.php remove duplicated text  
fix: show/hide userdetails torrent blocks, should be closed at start  
remove: redundant cache user_stats_  
remove: redundant cache userstats_  
fix: undefined var in news block  
fix: incorrect sql erro log path  
fix: countables  
update: signup errors redirect, previous form data intact  
update: install info  
add: apikey for future api access  
update: torrent_pass, auth to 64 characters  
update: tables.css to work same with table inside table  
fix: userdetails table blocks  

### 20 Jan, 2018  
update: install tooltips  

### 19 Jan, 2018  
update: set file permissions of .env, config.php and ann_config.php during install  
update: installer check writeable root  
update: user_unlocks.php to same style as the other block pages  
add: option to disable/enable newsrss feeds  
add: missing admin 'Show Friends' block  

### 18 Jan, 2018  
fix: edit wiki article  
fix: logout salt  
fix: blackjack  
update: index news block  
update: wiki.php  

### 17 Jan, 2018  
remove: double page load details.php just to increment hits  
update: set poster in db from book cache  
add: set ebook poster(in cache) to google book poster, if poster not set  
add: isbn column to torrents for better search results from google  
update: admin categories  
add: book info from google books api, with or without api key  
remove: unused columns from categories table  
update: database files  
add: list of anonymous names  
replace: 'Anonymous' with anonymous name  
add: auth key  
update: BOT upload to use auth key  

### 16 Jan, 2018  
update: site config: auto confirm   = yes, confirm email   = no  
add: missing trivia questions  

### 15 Jan, 2018  
update: install database  
update: karma cleanup  
move: cleanup  
update: site log  
update: coders log  
add: gzipped adminer.css  
update: browse.php not building links for seeders, leechers, snatches  
update: peerlist.php  
remove: unneeded ann_config var  
update: ann_config vars  
fix: AJAX Chat admin/sysop delete own posts  
update: install  

### 14 Jan, 2018  
update: add bot login/upload  
update: scrape.php  
update: tabs to spaces  
fix: hnr config settings  
fix: missing variable in AJAX Chat  
update: missing global  

### 13 Jan, 2018  
update: installer not write config files if empty values  
update: explicitly set variable  
update: announce.php  
update: template to show current PHP version  
update: PHP7.2  
update: install not continue if fields are blank  
fix: install back button and bad write config  
update: README.md  

### 12 Jan, 2018  
add: default .php_cs - rules used  
update: run php-cs-fixer --rules  =@PSR2  

### 11 Jan, 2018  
fix: delete torrent xbt cleanup name   
fix: admin+ delete bot messages in AJAX Chat  
update: hide latest forum posts  
update: hide torrent scroller when less than 10 torrent posters  
add: version number when site not in production  
fix: logout using the salt  
update: use of salts  

### 10 Jan, 2018  
fix: missing lang vars  
fix: queries affected by 'only_full_group_by'  
update: README.md add utf8 to mysqld.cnf  
add: searchcloud to initial database data  
update: begin update queries for sql_mode  =only_full_group_by(stoc  ​k mysql default), i have been using stock Percona without issue  
add: explicit globals  

### 9 Jan, 2018  
fix: trivia_update when 0 questions in db  
fix: uptime not showing when 0 queries  
update: lastest_user cache  
move: adminer into iframe  
remove: unnecessary css  

### 8 Jan, 2018  
add: adminer database manager  
remove: king icon showing for all  
fix: icon size in news  
update: minified css/js files  
move: fontawesome fonts to local  
move: google fonts to local  

### 7 Jan, 2018  
update: database  
update: config.php  
remove: forums link  
remove: forums  
update: use of dirname  
update: announce.php  
revert: changes to imdb class  
fix: typo in lang files  

### 6 Jan, 2018  
fix: global block uploadapp.php  
fix: uploadapp.php  
move: jquery to local file  
replace: many relative urls with full url  

5 Jan 2108  
fix: login bug  

### 4 Jan, 2018  
remove: ipToStorageFormat function  
fix: admin/bans.php  
fix: first user status pending  
add: error message for pending users attempting to login  

### 2 Jan, 2018  
update: README  
remove: trivia questions to separate sql install  
update: trivia to not refresh when game closed  
replace: getting server's webroot with getting root from script  
add: set the 3 passwords to random generated password in config during install  
remove: news delete   \n  
fix: margin on index page last news item  
remove: unused js files  
fix: AJAX Chat anonimize url without bbcode  

### 1 Jan, 2018  
add: missing month in AJAX Chat  
fix: function call Christmas  
add: checkdate for Christmas Gift  
fix: lang not loaded in achievements cleanup  

### 31 Dec, 2017  
fix: AJAX Chat dateTime insert  
add: datetime for mysql  
update: re-order sql updates  
update: database updates  
update: CACHE class  
update: database updates  
update: install scripts  
fix: invite signup  
fix: signup confirm  
update: many additional edits  
update: sql files  

### 16 Dec, 2017  
add: read from .env file  
update: installer to check composer and npm  
update: split install.php.sql into schema.php.sql and data.php.sql  
update: pStrength.jquery.js  
fix: autologin on successful account creation  

### 10 Dec, 2017  
fix: index trivia block display slightly different than all the others  
add: add bbcode for   [p  ]
removed: unneeded css/js/gz files  

### 9 Dec, 2017  
add: missing required file  
update: general code cleanup  
update: allow notifications to use bbcode  
fix: wiki.php  
fix: users.php  
update: forums sort links to tabs  
fix: navbar username class color  
add: mini navbar for links in usercp.php, userdetails.php forums.php  
remove: text shadow from user classes  
remove: webpack dependancy  
fix: extra space between username and comma  
add: package.json  
add: bulma css requirement  
add: notification of which cache adapter is in use  
fix: pm search delete  
add: cache all_users search for send message  

### 8 Dec, 2017  
change: most buttons smaller  
fix: pm search  
add: missing full text indexes  
add: select/send message to any user  
update: pm pages  

### 6 Dec, 2017  
fix: remove author from newsrss.php  
fix: check for defined  
run: "INSERT INTO staffpanel VALUES (75, 'Upgrade Database', 'staffpanel.php  ?tool  =upgrade_database', 'upgrade database', 'other', 6, 1, 1512555394, 0)  ;"  
add: admin/database_updates.php will show all queries that need run or things that need to be added to config.php  

### 2 Dec, 2017  
replace: more mysqli queries with pdo  
general code cleanup  

### 1 Dec, 2017  
remove: duplicate caches Myuser_ and user  

### 26 Nov, 2017  
remove: hardcoded user:pass  
add: pdo class, just beginning to move from mysqli to pdo  
fix: tooltipster position  
update: browse.php search function  
update: details.php comments  
update: cache- >get  
update: details.php  
remove: new up redis  

### 25 Nov, 2017  
fix: index page poll  
fix: index page latest torrents spacing  
fix: index page torrents scroller when empty  
fix: index page news block spacing  
fix: global messages positioning  
add: caching environment 'Scrapbook'  
remove: redis requirement  
remove: memcached and php-memcache requirement  

### 14 Nov, 2017  
update: GeoIP files  
fix: iphistory.php view ip  
fix: userdetails.php view ip  
add: transition to notification buttons  
fix: themeChanger active border  
fix: lottery config  
fix: userdetails.php tabs and anchors  
update: faq  
fix: scroll to anchor  
merge: torrents-today.php into browse.php  
fix: userdetails.php tabcontrol  

### 12 Nov, 2017  
add: user stats and gifts in AJAX Chat  
add: torrent search by uploader  
add: port_check.php to assist in opening ports  
add: hnrs.php so users can keep up with them  
update: AJAX Chat channels, restrict messages origins  
update: newsrss.php can accept arrays for feeds  

### 11 Nov, 2017  
update: page uniformity  
update: snatches.php, peerlist.php, viewnfo.php, report.php  
update: global messages  
add: doc blocks(a start)  
update: filelist.php  
update: torrents-today.php  
update: notifications can accept arrays  
update: ip storage(probably missed something)  
update: lightbox v2.9.0, group every image on page, using lightbox, to create slideshow  
update: raphael.js 2.2.7  

### 6 Nov, 2017  
replace: jquery-ui accordion with smaller alternative  
add: missing markItUp css  
optimize: gif images using gifsicle  

### 5 Nov, 2017  
add: index page latest comments block(request)  

### 4 Nov, 2017  
update: installer set bot name in config.php  
fix: breadcrumbs  
add: missing lnag index  
remove: check for array index after changing to string  
fix: compare array when array does not exist  
fix: unset blocks not getting cached  
fix: compare array where array is string  
fix: error with newsfeeds before bot user created  
fix: error with autoshout before bot user created  
move: iframe resize to template  
remove: checkbox for XBT install  
add: missing function  
update: config files  
remove: unused javascript files  
update: admin/block.settings.php  
update: user_blocks.php  
remove: jquery-ui, can't find any place its being used  
replace: iframeResizer.js with one-liner  
replace: css/js files are now served pre-gzipped  
add: admin view queries page  
fix: tooltips on index page last/top/mow torrents  
add: style to scrollbar  

### 31 Oct, 2017  
fix: various typos  
add: button to remove alerts  
replace: more error/success redirects with session variable and alerts  
update: iCarousel 1.2.2  
remove: bootstrap from default.css  
add: bulma.css  
replace: bootstrap classes with bulma classes  
update: and validated rss.php  
update: getrss.php  
remove: 'LOW_PRIORITY' from sql statements, is ignored when using INNODB tables  
update: contactstaff.php  

### 31 Oct, 2017  
add: php extensions check during install  
add: missing js files  
remove: incorrect references  
add: missing functions  
fix: ini_get method  
fix: installer  

### 22 Oct, 2017  
update: rules.php  
update: faq.php  
update: useragreement.php  
fix: rules.php, faq.php, useragreement.php accessible when not logged in  
update: add anonymizer url to lang files  
replace: $#163  ; with $#36  ;
update: admin/mega_search.php  
add: lottery site alert  
add: more staff links to navbar  
update: blackjack.php  
fix: admin/backup.php checkboxes  

### 21 Oct, 2017  
fix: browse.php checkbox selection  
change: vip to include, not include and only in search results  
fix: missing $subs in torrenttable.php  
fix: browse.php  

### 20 Oct, 2017  
add: auto remove alerts that use session variables after 15 seconds  
update: trivia pages  
fix: removed lines from backup.php  
add: breadcrumbs to all pages except index page  
update: staffpanel.php  
update: view_mailbox.php  

### 18 Oct, 2017  
remove: empty extra tools button from bbcode editor  
update: tfreak news block format to match news block  
replace: fast delete and fast edit images with font awesome icons  

### 17 Oct, 2017  
update: database schema  
add: missing images  
fix: BBcode editor using 100% of available space  
update: index news block  
fix: admin news add/edit  
update: captcha pages  
remove: print_user_stuff function  
remove: javascript to change button class on hover  

### 14 Oct, 2017  
update: admin cleanup log, moved disabled to last, instead of first  
fix: lottery  
update: Arcade and related files  
update: index page forum posts, remove unneeded joins  
fix: AJAX Chat kick/ban user  
remove: destroy session from AJAX Chat so not logged out of site  
fix: AJAX Chat endless loop when user is banned or disabled  
remove: AJAX Chat channels from mysql  
move: AJAX Chat online table to redis  
move: AJAX Chat bans table to redis  
begin: move AJAX-Chat from mysql to redis  

### 9 Oct, 2017  
remove: unneeded check for direct file access in files not in webroot  
remove: geshi syntax highlighter  
update: coders log  
add: bbcode code for   [code  ]

### 8 Oct, 2017  
update: minified css and js files  
remove: margin and padding on collapsed divs  
remove: cleanup log  
move: DB Backup config settings to db and site_config  
remove: torrent.type from database  
remove: torrent.username from database  
update: pushed AJAX Chat to the borders  
update: AJAX Chat to use same mysqli object as the rest of the site  
add: anonymizer url to site settings and all links use this  
add: simple logs viewer  
remove: LogViewer  
add: option to log all queries to the database  
update: admin/site_config  
remove: many inline style  
update: index blocks ->torrents to uniform style  
remove: excess borders on table-bordered  

### 14 Sept, 2017  
fix: cleanup manual run  
fix: MOW  
add: missing jquery-ui in merged and minified js files  
fix: iCarousel centering  
remove: navbar border radius along top  

### 13 Sept, 2017  
fix: iCarousel 3d  

### 12 Sept, 2017  
update: jquery.mousewheel.js  
fix: scroller mouse scroll not scroll entire page  
fix: themeChanger font color in iframes  
update: css/js minified files  
update: AJAX Chat links to behave same as the rest of the site  
fix: blackjack cards incorrect info in sql table  
fix: AJAX Chat log viewer  
add: link to AJAX Chat log viewer  
fix: overflow-y on index page  

### 11 Sept, 2017  
fix: back to top z-index  
hide index page poll block when no poll  
hide index page ie alert block if not using ie  
index page more mobile friendly  

### 11 Sept, 2017  
replace js navbar with css navbar  
remove: or replaced all document.write  
fix: install issues with foreign key issues  

### 6 Sept, 2017  
fix: all index blocks to display correctly  
fix: returnto in fastdelete.php and edit.php  
update: favicon  
update: install scripts  

### 5 Sept, 2017  
remove: php preview, replaced with inline bbcode editor preview  
fix: bbcode parser adding extra newlines when displaying tables  
moved: home button to sticky navbar  

### 4 Sept, 2017  
modify: bbcode editor, add preview  
moved almost everything out of the webroot, incomplete  
merge: default.css, bootstrap.css and bootstart-responsive.css  
update: jquery 3.2.1  
update: jquery ui 1.12.1  
add: more subtitle options for upload  
update: upload  
moved: bucket inside root  
update: bitbucket  
begin removing: unneeded tables  
begin replacing: tooltips and hover popups with tooltipster  
begin modify: all toggle/slide elements to operate uniformly  

### 21 Aug, 2017  
modify: themeController to localstorage instead of cookies  
modify: themeController add additional fonts and removed MS fonts  
modify: themeController now also controls the fonts in AJAX Caht and trivia  
modify: cleanup filelist.php  

### 20 Aug, 2017  
modify: change sitelog message to be more consistant and easier to read  
fix: install setting incorrect clean_time  
replace: Xmas with Christmas  
add: function to return a random color  
fix: incorrect minified js file being requested  
add: cleanup log viewer in admin panel  
fix: cleanup getting access to $queries count  
fix: rename cleanup function names for concurrency  

### 19 Aug, 2017  
fix: installer  
update: README  
fix: update/reorder ajax chat online list when users count changes  
fix: inactive_update.php fail on empty set  
fix: move js to just before closing body tag  
fix: combine and minify most js files on index page  
fix: themecontroller to use cookie_prefix: when getting/setting cookie  
update: jQuery Cookie Plugin to v1.4.1  
remove: duplicated function from cleanup scripts  

### 18 Aug, 2017  
add: foreign_keys.sql to create some foreign keys, nowhere near complete  
add: mysql_drop_fks.php to remove foreign keys from database  
fix: inactive_update.php to properly delete users  

### 17 Aug, 2017  
fix: make more pages 'table' appear similar to the other pages  
fix: make invite_signup.php similar to signup.php  
update: make blakckjack use 1 table, use single image sprite for cards  
fix: admin adduser.php adds user to usersachiev table and autoshout  
fix: add color class 'user' to autoshout of new users  

### 16 Aug, 2017  
rename: project to Pu-239  
