<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
if (!$session->get('LoggedIn')) {
    get_template();
} else {
    check_user_status();
}

$lang = load_language('global');

$HTMLOUT = main_div("
<p>
    There seems to be a lot of confusion about how the statistics updates work. The following is a capture of a full session to see what's going on behind the scenes. The client communicates with the tracker via simple http GET commands. The very first in this case was:
</p>
<p class='text-black'>
    GET /announce.php?<span class='has-text-lghtblue'>info_hash=c9791C5jG951BEC7MF9BFa03F22CEDEE0F</span>&amp;<span class='has-text-oragne'>peer_id=S588-----gqQ8TqDeqaY</span>&amp;
    <span class='has-text-success'>port=6882</span>&amp;<span class='is-warning'>uploaded=0</span>&amp;<span class='is-lightgreen'>downloaded=0</span>&amp;<span class='is-jade'>left=753690875</span>&amp;
    <span class='is-turquoise'>event=started</span>
</p>

Let's dissect this:
<ul class='disc left20'>
    <li class='has-text-lghtblue'>
        info_hash is just the hash identifying the torrent in question
    </li>
    <li class='has-text-oragne'>
        peer_id, as the name suggests, identifies the client (the s588 part identifies Shad0w's 5.8.8, the rest is random)
    </li>
    <li class='has-text-success'>
        port just tells the tracker which port the client will listen to for incoming connections
    </li>
    <li class='is-warning'>
        uploaded=0; (this and the following are the relevant ones, and are self-explanatory)
    </li>
    <li class='is-lightgreen'>
        downloaded=0
    </li>
    <li class='is-jade'>
        left=753690875 (how much left)
    </li>
    <li class='is-turquoise'>
        event=started (telling the tracker that the client has just started).
    </li>
</ul>
<br>
<p>
    Notice that the client IP doesn't show up here (although it can be sent by the client if it configured to do so). It's up to the tracker to see it and associate it with the user_id. (Server replies will be omitted, they're just lists of peer ips and respective ports.) At this stage the user's profile will be listing this torrent as being leeched.
</p>
<p>
    From now on the client will keep send GETs to the tracker. We show only the first one as an example:
</p>
<p class='text-black'>
    GET /announce.php?info_hash=c9791C5jG951BEC7MF9BFa03F22CEDEE0F&amp;peer_id=S588-----gqQ8TqDeqaY&amp;port=6882&amp;
    <span class='is-warning'>uploaded=67960832</span>&amp;<span class='is-success'>downloaded=40828928</span>&amp;left=715417851&amp;<span class='is-turquoise'>numwant=0</span>
</p>
<ul class='disc left20'>
    <li class='is-turquoise'>
        numwant=0 is how the client tells the tracker how many new peers it wants, in this case 0.
    </li>
    <li class='is-warning'>
        uploaded=67960832 approximately 68MB uploaded
    </li>
    <li class='is-lightgreen'>
        downloaded=40828928 approximately 40MB downloaded 
    </li>
</ul>
<br>
<p>
    Whenever the tracker receives these GETs it updates both the stats relative to the 'currently leeching/seeding' boxes and the total user upload/download stats. These intermediate GETs will be sent either periodically (every 15 min or so, depends on the client and tracker) or when you force a manual announce in the client.
</p>
<p>
    Finally, when the client was closed it sent:
</p>
<p class='text-black'>
    GET /announce.php?info_hash=c9791C5jG951BEC7MF9BFa03F22CEDEE0F&amp;peer_id=S588-----gqQ8TqDeqaY&amp;port=6882&amp;uploaded=754384896&amp;downloaded=754215163&amp;<span class='is-jade'>left=0&amp;numwant=0&amp;
    <span class='is-turquoise'>event=completed</span>
</p>
<p>
    Notice the all-important <span class='is-turquoise'>'event=completed'</span>. It is at this stage that the torrent will be removed from the user's profile. If for some reason (tracker down, lost connection, bad client, crash, ...) this last GET doesn't reach the tracker and this torrent will still be seen in the user profile until some tracker timeout occurs. It should be stressed that this message will be sent only when closing the client properly, not when the download is finished. (The tracker will start listing a torrent as 'currently seeding' after it receives a GET with <span class='is-jade'>left=0</span>).
</p>
<p>
    There's a further message that causes the torrent to be removed from the user's profile, namely <span class='is-turquoise'>'event=stopped'</span>. This is usually sent when stopping in the middle of a download, e.g. by pressing 'Cancel'.
</p>
<p>
    One last note: some clients have a pause/resume option. This will not send any message to the server. Do not use it as a way of updating stats more often, it just doesn't work.
</p>");

$HTMLOUT = wrapper($HTMLOUT, 'has-text-left');

echo stdhead('FAQ') . $HTMLOUT . stdfoot();
