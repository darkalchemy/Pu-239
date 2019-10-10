<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$HTMLOUT = main_div("
<div class='bg-03 padding20 round10'>
    " . _("There seems to be a lot of confusion about how the statistics updates work. The following is a capture of a full session to see what's going on behind the scenes. The client communicates with the tracker via simple http GET commands. The very first in this case was") . ":
</div>
<div class='bg-03 padding20 round10 top20'>
    GET /announce.php?<span class='has-text-lightblue'>info_hash=c9791C5jG951BEC7MF9BFa03F22CEDEE0F</span>&amp;<span class='has-text-orange'>peer_id=S588-----gqQ8TqDeqaY</span>&amp;
    <span class='has-text-success'>port=6882</span>&amp;<span class='has-text-warning'>uploaded=0</span>&amp;<span class='has-text-lightgreen'>downloaded=0</span>&amp;<span class='has-text-jade'>left=753690875</span>&amp;
    <span class='has-text-turquoise'>event=started</span>
</div>
<div class='bg-03 padding20 round10 top20'>
    " . _("Let's dissect this") . ":
    <ul class='disc left20'>
        <li class='has-text-lightblue'>
            <b>info_hash:</b> " . _('is just the hash identifying the torrent in question') . "
        </li>
        <li class='has-text-orange'>
            <b>peer_id:</b> " . _("as the name suggests, identifies the client (the s588 part identifies Shad0w's 5.8.8, the rest is random)") . "
        </li>
        <li class='has-text-success'>
            <b>port:</b> " . _('just tells the tracker which port the client will listen to for incoming connections') . "
        </li>
        <li class='has-text-warning'>
            <b>uploaded=0;</b> " . _('(this and the following are the relevant ones, and are self-explanatory)') . "
        </li>
        <li class='has-text-lightgreen'>
            <b>downloaded=0</b>
        </li>
        <li class='has-text-jade'>
            <b>left=753690875</b> " . _('(how much left)') . "
        </li>
        <li class='has-text-turquoise'>
            <b>event=started</b> " . _('(telling the tracker that the client has just started).') . "
        </li>
    </ul>
</div>
<div class='bg-03 padding20 round10 top20'>
    <div>
        " . _("Notice that the client IP doesn't show up here (although it can be sent by the client if it configured to do so). It's up to the tracker to see it and associate it with the user_id. (Server replies will be omitted, they're just lists of peer ips and respective ports.) At this stage the user's profile will be listing this torrent as being leeched.") . '
    </div>
    <div>
        ' . _('From now on the client will keep send GETs to the tracker. We show only the first one as an example') . ":
    </div>
    <div>
        GET /announce.php?info_hash=c9791C5jG951BEC7MF9BFa03F22CEDEE0F&amp;peer_id=S588-----gqQ8TqDeqaY&amp;port=6882&amp;
        <span class='has-text-warning'>uploaded=67960832</span>&amp;<span class='has-text-success'>downloaded=40828928</span>&amp;left=715417851&amp;<span class='has-text-turquoise'>numwant=0</span>
    </div>
</div>
<div class='bg-03 padding20 round10 top20'>
    <ul class='disc left20'>
        <li class='has-text-turquoise'>
            numwant=0: " . _('is how the client tells the tracker how many new peers it wants, in this case 0.') . "
        </li>
        <li class='has-text-warning'>
            uploaded=67960832: " . _('approximately 68MB uploaded') . "
        </li>
        <li class='has-text-lightgreen'>
            downloaded=40828928: " . _('approximately 40MB downloaded') . " 
        </li>
    </ul>
</div>
<div class='bg-03 padding20 round10 top20'>
    <div>
        " . _("Whenever the tracker receives these GETs it updates both the stats relative to the 'currently leeching/seeding' boxes and the total user upload/download stats. These intermediate GETs will be sent either periodically (every 15 min or so, depends on the client and tracker) or when you force a manual announce in the client.") . '
    </div>
    <div>
        ' . _('Finally, when the client was closed it sent') . ":
    </div>
    <div>
        GET /announce.php?info_hash=c9791C5jG951BEC7MF9BFa03F22CEDEE0F&amp;peer_id=S588-----gqQ8TqDeqaY&amp;port=6882&amp;uploaded=754384896&amp;downloaded=754215163&amp;<span class='has-text-jade'>left=0&amp;numwant=0&amp;
        <span class='has-text-turquoise'>event=completed</span>
    </span>
    <div>
        " . _fe("Notice the all-important {0}. It is at this stage that the torrent will be removed from the user's profile. If for some reason (tracker down, lost connection, bad client, crash, ...) this last GET doesn't reach the tracker and this torrent will still be seen in the user profile until some tracker timeout occurs. It should be stressed that this message will be sent only when closing the client properly, not when the download is finished. (The tracker will start listing a torrent as 'currently seeding' after it receives a GET with {1}).", "<span class='has-text-turquoise'>'event=completed'</span>", "<span class='has-text-jade'>left=0</span>") . '
    </div>
    <div>
        ' . _fe("There's a further message that causes the torrent to be removed from the user's profile, namely {0}. This is usually sent when stopping in the middle of a download, e.g. by pressing 'Cancel'.", "<span class='has-text-turquoise'>'event=stopped'</span>") . '
    </div>
    <div>
        ' . _("One last note: some clients have a pause/resume option. This will not send any message to the server. Do not use it as a way of updating stats more often, it just doesn't work.") . '
    </div>
</div>', '', 'padding20');

$HTMLOUT = wrapper($HTMLOUT, 'has-text-left');
$title = _('Anatomy of a Torrent');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
