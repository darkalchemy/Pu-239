## disable checking foreign keys
SET FOREIGN_KEY_CHECKS = 0;

## achievements
ALTER TABLE achievements
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE achievements
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## ajax_chat_bans
ALTER TABLE ajax_chat_bans
    MODIFY `userID` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ajax_chat_bans
    ADD FOREIGN KEY (userID) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## ajax_chat_invitations
ALTER TABLE ajax_chat_invitations
    MODIFY `userID` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ajax_chat_invitations
    ADD FOREIGN KEY (userID) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## ajax_chat_messages
ALTER TABLE ajax_chat_messages
    MODIFY `userID` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ajax_chat_messages
    MODIFY `userName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE ajax_chat_messages
    ADD FOREIGN KEY (userID) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE ajax_chat_messages
    ADD FOREIGN KEY (userName) REFERENCES users (username)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## ajax_chat_online
ALTER TABLE ajax_chat_online
    MODIFY `userID` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ajax_chat_online
    MODIFY `userName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE ajax_chat_online
    ADD FOREIGN KEY (userID) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## announcement_main
ALTER TABLE announcement_main
    ADD FOREIGN KEY (owner_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## announcement_process
ALTER TABLE announcement_process
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## attachments
ALTER TABLE attachments
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## attachments
ALTER TABLE auth_tokens
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## blackjack tables
ALTER TABLE blackjack
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE blackjack
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE blackjack_history
    MODIFY `player1_userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE blackjack_history
    MODIFY `player2_userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE blackjack_history
    ADD FOREIGN KEY (player1_userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE blackjack_history
    ADD FOREIGN KEY (player2_userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## blocks table
ALTER TABLE blocks
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## bookmarks
ALTER TABLE bookmarks
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## bugs
ALTER TABLE bugs
    MODIFY `sender` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE bugs
    ADD FOREIGN KEY (sender) REFERENCES users (id);

## casino
ALTER TABLE casino
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE casino
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## casino_bets
ALTER TABLE casino_bets
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE casino_bets
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## cheaters
ALTER TABLE cheaters
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE cheaters
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## coins
ALTER TABLE coins
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE bookmarks
    ADD FOREIGN KEY (torrentid) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## comments
ALTER TABLE comments
    MODIFY `user` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE comments
    ADD FOREIGN KEY (user) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE comments
    ADD FOREIGN KEY (torrent) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## events
ALTER TABLE events
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE events
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## files
ALTER TABLE files
    MODIFY `torrent` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE files
    ADD FOREIGN KEY (torrent) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## flashscores
ALTER TABLE flashscores
    MODIFY `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE flashscores
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## friends
ALTER TABLE friends
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE friends
    ADD FOREIGN KEY (friendid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## forum_poll
ALTER TABLE forum_poll
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## forum_poll_votes
ALTER TABLE forum_poll_votes
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## freeslots
ALTER TABLE freeslots
    MODIFY `torrentid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE freeslots
    ADD FOREIGN KEY (torrentid) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE freeslots
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE freeslots
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## funds
ALTER TABLE funds
    ADD FOREIGN KEY (user) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## happyhour
ALTER TABLE happyhour
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE happyhour
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE happyhour
    MODIFY `torrentid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE happyhour
    ADD FOREIGN KEY (torrentid) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## happylog
ALTER TABLE happylog
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE happylog
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE happylog
    MODIFY `torrentid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE happylog
    ADD FOREIGN KEY (torrentid) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## highscores
ALTER TABLE highscores
    MODIFY `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE highscores
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## invite_codes
ALTER TABLE invite_codes
    ADD FOREIGN KEY (sender) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## ips
ALTER TABLE ips
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ips
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## likes
ALTER TABLE likes
    MODIFY `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE likes
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## messages
ALTER TABLE messages
    ADD FOREIGN KEY (receiver) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## news
ALTER TABLE news
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE news
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## notconnectablepmlog
ALTER TABLE notconnectablepmlog
    ADD FOREIGN KEY (user) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## now_viewing
ALTER TABLE now_viewing
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## offer_votes
ALTER TABLE offer_votes
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## offers
ALTER TABLE offers
    ADD FOREIGN KEY (offered_by_user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## peers
ALTER TABLE peers
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE peers
    ADD FOREIGN KEY (torrent) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## pmboxes
ALTER TABLE pmboxes
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE pmboxes
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## poll_voters
ALTER TABLE poll_voters
    MODIFY `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE poll_voters
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## posts
ALTER TABLE posts
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## rating
ALTER TABLE rating
    MODIFY `user` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE rating
    ADD FOREIGN KEY (user) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE rating
    MODIFY `torrent` int(10) UNSIGNED NOT NULL DEFAULT 0;

## read_posts
ALTER TABLE read_posts
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## reports
ALTER TABLE reports
    ADD FOREIGN KEY (reported_by) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## reputation
ALTER TABLE reputation
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE reputation
    MODIFY `whoadded` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE reputation
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE reputation
    ADD FOREIGN KEY (whoadded) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## request_votes
ALTER TABLE request_votes
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## requests
ALTER TABLE requests
    ADD FOREIGN KEY (requested_by_user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## shit_list
ALTER TABLE shit_list
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## snatched
ALTER TABLE snatched
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE snatched
    ADD FOREIGN KEY (torrentid) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## staffmessages
ALTER TABLE staffmessages
    ADD FOREIGN KEY (sender) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## subscriptions
ALTER TABLE subscriptions
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## subtitles
ALTER TABLE subtitles
    MODIFY `owner` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE subtitles
    ADD FOREIGN KEY (owner) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## thanks
ALTER TABLE thanks
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thanks
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE thanks
    MODIFY `torrentid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thanks
    ADD FOREIGN KEY (torrentid) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## thankyou
ALTER TABLE thankyou
    MODIFY `uid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thankyou
    ADD FOREIGN KEY (uid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE thankyou
    MODIFY `torid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE thankyou
    ADD FOREIGN KEY (torid) REFERENCES torrents (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## tickets
ALTER TABLE tickets
    MODIFY `user` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE tickets
    ADD FOREIGN KEY (user) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## topics
ALTER TABLE topics
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## triviausers
ALTER TABLE triviausers
    MODIFY `gamenum` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE triviausers
    MODIFY `qid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE triviaq
    MODIFY `qid` int unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE triviasettings
    MODIFY `gamenum` int unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE triviausers
    MODIFY `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE triviausers
    ADD FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE triviausers
    ADD FOREIGN KEY (qid) REFERENCES triviaq (qid)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
ALTER TABLE triviausers
    ADD FOREIGN KEY (gamenum) REFERENCES triviasettings (gamenum)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## uploadapp
ALTER TABLE uploadapp
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE uploadapp
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## user_blocks
ALTER TABLE user_blocks
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## userhits
ALTER TABLE userhits
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## ustatus
ALTER TABLE ustatus
    MODIFY `userid` int(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE ustatus
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## usercomments
ALTER TABLE usercomments
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## usersachiev
ALTER TABLE usersachiev
    ADD FOREIGN KEY (userid) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

## wiki
ALTER TABLE wiki
    ADD FOREIGN KEY (userid) REFERENCES users (id);

## enable checking foreign keys
SET FOREIGN_KEY_CHECKS = 1;
