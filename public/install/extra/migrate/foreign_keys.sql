## disable checking foreign keys
SET FOREIGN_KEY_CHECKS = 0;

## achievements
ALTER TABLE achievements MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE achievements ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## ajax_chat_bans
ALTER TABLE ajax_chat_bans MODIFY `userID` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ajax_chat_bans ADD FOREIGN KEY (userID) REFERENCES users(id) ON DELETE CASCADE;

## ajax_chat_invitations
ALTER TABLE ajax_chat_invitations MODIFY `userID` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ajax_chat_invitations ADD FOREIGN KEY (userID) REFERENCES users(id) ON DELETE CASCADE;

## ajax_chat_messages
ALTER TABLE ajax_chat_messages MODIFY `userID` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ajax_chat_messages MODIFY `userName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE ajax_chat_messages ADD FOREIGN KEY (userID) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE ajax_chat_messages ADD FOREIGN KEY (userName) REFERENCES users(username) ON DELETE CASCADE;

## ajax_chat_online
ALTER TABLE ajax_chat_online MODIFY `userID` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ajax_chat_online MODIFY `userName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE ajax_chat_online ADD FOREIGN KEY (userID) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE ajax_chat_online ADD FOREIGN KEY (userName) REFERENCES users(username) ON DELETE CASCADE;

## announcement_main
ALTER TABLE announcement_main ADD FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE;

## announcement_process
ALTER TABLE announcement_process ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## attachments
ALTER TABLE attachments ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## bannedemails
ALTER TABLE bannedemails ADD FOREIGN KEY (addedby) REFERENCES users(id);

## bans
## do you want to remove bans from staff that are not here
ALTER TABLE bans ADD FOREIGN KEY (addedby) REFERENCES users(id);

## blackjack tables
ALTER TABLE blackjack MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE blackjack ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE blackjack_history MODIFY `player1_userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE blackjack_history MODIFY `player2_userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE blackjack_history ADD FOREIGN KEY (player1_userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE blackjack_history ADD FOREIGN KEY (player2_userid) REFERENCES users(id) ON DELETE CASCADE;

## blocks table
ALTER TABLE blocks ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## bookmarks
ALTER TABLE bookmarks ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE bookmarks ADD FOREIGN KEY (torrentid) REFERENCES torrents(id) ON DELETE CASCADE;

## bugs
ALTER TABLE bugs MODIFY `sender` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE bugs ADD FOREIGN KEY (sender) REFERENCES users(id);

## casino
ALTER TABLE casino MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE casino ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## casino_bets
ALTER TABLE casino_bets MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE casino_bets ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## cheaters
ALTER TABLE cheaters MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE cheaters ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## coins
ALTER TABLE coins ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE bookmarks ADD FOREIGN KEY (torrentid) REFERENCES torrents(id) ON DELETE CASCADE;

## comments
ALTER TABLE comments MODIFY `user` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE comments ADD FOREIGN KEY (user) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE comments ADD FOREIGN KEY (torrent) REFERENCES torrents(id) ON DELETE CASCADE;

## dbbackup
ALTER TABLE dbbackup ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## events
ALTER TABLE events MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE events ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## files
ALTER TABLE files MODIFY `torrent` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE files ADD FOREIGN KEY (torrent) REFERENCES torrents(id) ON DELETE CASCADE;

## flashscores
ALTER TABLE flashscores MODIFY `user_id` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE flashscores ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## friends
ALTER TABLE friends ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE friends ADD FOREIGN KEY (friendid) REFERENCES users(id) ON DELETE CASCADE;

## forum_poll
ALTER TABLE forum_poll ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## forum_poll_votes
ALTER TABLE forum_poll_votes ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## freeslots
ALTER TABLE freeslots MODIFY `torrentid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE freeslots ADD FOREIGN KEY (torrentid) REFERENCES torrents(id) ON DELETE CASCADE;
ALTER TABLE freeslots MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE freeslots ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## funds
ALTER TABLE funds ADD FOREIGN KEY (user) REFERENCES users(id) ON DELETE CASCADE;

## happyhour
ALTER TABLE happyhour MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE happyhour ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE happyhour MODIFY `torrentid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE happyhour ADD FOREIGN KEY (torrentid) REFERENCES torrents(id) ON DELETE CASCADE;

## happylog
ALTER TABLE happylog MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE happylog ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE happylog MODIFY `torrentid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE happylog ADD FOREIGN KEY (torrentid) REFERENCES torrents(id) ON DELETE CASCADE;

## highscores
ALTER TABLE highscores MODIFY `user_id` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE highscores ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## invite_codes
ALTER TABLE invite_codes ADD FOREIGN KEY (sender) REFERENCES users(id) ON DELETE CASCADE;

## ips
ALTER TABLE ips MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ips ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## likes
ALTER TABLE likes MODIFY `user_id` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE likes ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## manage_likes
ALTER TABLE manage_likes MODIFY `user_id` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE manage_likes ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## news
ALTER TABLE news MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE news ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## notconnectablepmlog
ALTER TABLE notconnectablepmlog ADD FOREIGN KEY (user) REFERENCES users(id) ON DELETE CASCADE;

## now_viewing
ALTER TABLE now_viewing ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## offer_votes
ALTER TABLE offer_votes ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## offers
ALTER TABLE offers ADD FOREIGN KEY (offered_by_user_id) REFERENCES users(id) ON DELETE CASCADE;

## peers
ALTER TABLE peers ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE peers ADD FOREIGN KEY (torrent) REFERENCES torrents(id) ON DELETE CASCADE;

## pmboxes
ALTER TABLE pmboxes MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE pmboxes ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## poll_voters
ALTER TABLE poll_voters MODIFY `user_id` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE poll_voters ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## posts
ALTER TABLE posts ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## rating
ALTER TABLE rating MODIFY `user` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE rating ADD FOREIGN KEY (user) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE rating MODIFY `torrent` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE rating ADD FOREIGN KEY (torrent) REFERENCES torrents(id) ON DELETE CASCADE;

## read_posts
ALTER TABLE read_posts ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## reports
ALTER TABLE reports ADD FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE;

## reputation
ALTER TABLE reputation MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE reputation MODIFY `whoadded` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE reputation ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE reputation ADD FOREIGN KEY (whoadded) REFERENCES users(id) ON DELETE CASCADE;

## request_votes
ALTER TABLE request_votes ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## requests
ALTER TABLE requests ADD FOREIGN KEY (requested_by_user_id) REFERENCES users(id) ON DELETE CASCADE;

## shit_list
ALTER TABLE shit_list ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## snatched
ALTER TABLE snatched ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE snatched ADD FOREIGN KEY (torrentid) REFERENCES torrents(id) ON DELETE CASCADE;

## staffmessages
ALTER TABLE staffmessages ADD FOREIGN KEY (sender) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE staffmessages ADD FOREIGN KEY (answeredby) REFERENCES users(id) ON DELETE CASCADE;

## subscriptions
ALTER TABLE subscriptions ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## subtitles
ALTER TABLE subtitles MODIFY `owner` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE subtitles ADD FOREIGN KEY (owner) REFERENCES users(id) ON DELETE CASCADE;

## thanks
ALTER TABLE thanks MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thanks ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE thanks MODIFY `torrentid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thanks ADD FOREIGN KEY (torrentid) REFERENCES torrents(id) ON DELETE CASCADE;

## thankyou
ALTER TABLE thankyou MODIFY `uid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thankyou ADD FOREIGN KEY (uid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE thankyou MODIFY `torid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thankyou ADD FOREIGN KEY (torid) REFERENCES torrents(id) ON DELETE CASCADE;

## thumbsup
ALTER TABLE thumbsup MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thumbsup ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE thumbsup MODIFY `torrentid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thumbsup ADD FOREIGN KEY (torrentid) REFERENCES torrents(id) ON DELETE CASCADE;

## tickets
ALTER TABLE tickets MODIFY `user` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE tickets ADD FOREIGN KEY (user) REFERENCES users(id) ON DELETE CASCADE;

## topics
ALTER TABLE topics ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

## triviausers
ALTER TABLE triviausers MODIFY `gamenum` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE triviausers MODIFY `qid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE triviaq MODIFY `qid` int unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE triviasettings MODIFY `gamenum` int unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE triviausers MODIFY `user_id` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE triviausers ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE triviausers ADD FOREIGN KEY (qid) REFERENCES triviaq(qid) ON DELETE CASCADE;
ALTER TABLE triviausers ADD FOREIGN KEY (gamenum) REFERENCES triviasettings(gamenum) ON DELETE CASCADE;

## uploadapp
ALTER TABLE uploadapp MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE uploadapp ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## user_blocks
ALTER TABLE user_blocks ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## userhits
ALTER TABLE userhits ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## ustatus
ALTER TABLE ustatus MODIFY `userid` int UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ustatus ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## usercomments
ALTER TABLE usercomments ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## usersachiev
ALTER TABLE usersachiev ADD FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE;

## wiki
ALTER TABLE wiki ADD FOREIGN KEY (userid) REFERENCES users(id);

## enable checking foreign keys
SET FOREIGN_KEY_CHECKS = 1;
