<?php

global $site_config;

$lang = [
    'faq_welcome' => "
    <h1 class='has-text-centered'>Welcome to {$site_config['site_name']}!</h1>
    <p>Our goal is not to become another Bytemonsoon or Suprnova (not dizzying either of them though). The goal is to provide the absolutely latest stuff. Therefore, only specially authorised users have permission to upload torrents. If you have access to 0-day stuff do not hesitate to <a href='{$site_config['baseurl']}/staff.php'>contact</a> us!</p>
    <p>This is a private tracker, and you have to register before you can get full access to the site. Before you do anything here at {$site_config['site_name']} we suggest you read the <a href='{$site_config['baseurl']}/rules.php'>rules</a>! There are only a few rules to abide by, but we do enforce them!</p>
    <p>Before you go any further you should read the {$site_config['site_name']} <a href='{$site_config['baseurl']}/useragreement.php'>user agreement</a>.</p>",
    'faq_contents_header' => 'Contents',
    'faq_siteinfo_header' => 'Site information',
    'faq_siteinfo' => "
    <ul>
        <li><a href='#site1'>What is this bittorrent all about anyway? How do I get the files?</a></li>
        <li><a href='#site2'>Where does the donated money go?</a></li>
        <li><a href='#site4'>Where can I get a copy of the source code?</a></li>
    </ul>",
    'faq_userinfo_header' => 'User information',
    'faq_userinfo' => "
    <ul>
        <li><a href='#user1'>I registered an account but did not receive the confirmation e-mail!</a></li>
        <li><a href='#user2'>I've lost my user name or password! Can you send it to me?</a></li>
        <li><a href='#user3'>Can you rename my account?</a></li>
        <li><a href='#user4'>Can you delete my (confirmed) account?</a></li>
        <li><a href='#userb'>So, what's MY ratio?</a></li>
        <li><a href='#user5'>Why is my IP displayed on my details page?</a></li>
        <li><a href='#user6'>Help! I cannot login!? (a.k.a. Login of Death)</a></li>
        <li><a href='#user7'>My IP address is dynamic. How do I stay logged in?</a></li>
        <li><a href='#user8'>Why am I listed as not connectable? (And why should I care?)</a></li>
        <li><a href='#user9'>What are the different user classes?</a></li>
        <li><a href='#usera'>How does this promotion thing work anyway?</a></li>
        <li><a href='#usere'>Hey! I've seen Power Users with less than 25GB uploaded!</a></li>
        <li><a href='#userc'>Why can't my friend become a member?</a></li>
        <li><a href='#userd'>How do I add an avatar to my profile?</a></li>
    </ul>",
    'faq_stats_header' => 'Stats',
    'faq_stats' => "
    <ul>
        <li><a href='#stats1'>Most common reasons for stats not updating</a></li>
        <li><a href='#stats2'>Best practices</a></li>
        <li><a href='#stats3'>May I use any bittorrent client?</a></li>
        <li><a href='#stats4'>Why is a torrent I'm leeching/seeding listed several times in my profile?</a></li>
        <li><a href='#stats5'>I've finished or cancelled a torrent. Why is it still listed in my profile?</a></li>
        <li><a href='#stats6'>Why do I sometimes see torrents I'm not leeching in my profile!?</a></li>
        <li><a href='#stats7'>Multiple IPs (Can I login from different computers?)</a></li>
        <li><a href='#stats8'>How does NAT/ICS change the picture?</a></li>
        <li><a href='#stats9'>For those of you who are interested (Anatomy of a torrent session)</a></li>
    </ul>",
    'faq_uploading_header' => 'Uploading',
    'faq_uploading' => "
    <ul>
        <li><a href='#up1'>Why can't I upload torrents?</a> </li>
        <li><a href='#up2'>What criteria must I meet before I can join the Uploader team?</a></li>
        <li><a href='#up3'>Can I upload your torrents to other trackers?</a></li>
    </ul>",
    'faq_downloading_header' => 'Downloading',
    'faq_downloading' => "
    <ul>
        <li><a href='#dl1'>How do I use the files I've downloaded?</a></li>
        <li><a href='#dl2'>Downloaded a movie and don't know what CAM/TS/TC/SCR means?</a></li>
        <li><a href='#dl3'>Why did an active torrent suddenly disappear?</a></li>
        <li><a href='#dl4'>How do I resume a broken download or reseed something?</a></li>
        <li><a href='#dl5'>Why do my downloads sometimes stall at 99%?</a></li>
        <li><a href='#dl6'>What are these 'a piece has failed an hash check' messages?</a></li>
        <li><a href='#dl7'>The torrent is supposed to be 100MB. How come I downloaded 120MB?</a></li>
        <li><a href='#dl8'>Why do I get a 'Not authorized (xx h) - READ THE FAQ!' error?</a></li>
        <li><a href='#dl9'>Why do I get a 'rejected by tracker - Port xxxx is blacklisted' error?</a></li>
        <li><a href='#dla'>What's this 'IOError - [Errno13] Permission denied' error?</a></li>
        <li><a href='#dlb'>What's this 'TTL' in the browse page?</a></li>
    </ul>",
    'faq_improve_header' => 'How can I improve my download speed?',
    'faq_improve' => "
    <ul>
        <li><a href='#dlsp1'>Do not immediately jump on new torrents</a></li>
        <li><a href='#dlsp2'>Make yourself connectable</a></li>
        <li><a href='#dlsp3'>Limit your upload speed</a></li>
        <li><a href='#dlsp4'>Limit the number of simultaneous connections</a></li>
        <li><a href='#dlsp5'>Limit the number of simultaneous uploads</a></li>
        <li><a href='#dlsp6'>Just give it some time</a></li>
        <li><a href='#dlsp7'>Why is my browsing so slow while leeching?</a></li>
    </ul>",
    'faq_isp_header' => 'My ISP uses a transparent proxy. What should I do?',
    'faq_isp' => "
    <ul>
        <li><a href='#prox1'>What is a proxy?</a></li>
        <li><a href='#prox2'>How do I find out if I'm behind a (transparent/anonymous) proxy?</a></li>
        <li><a href='#prox3'>Why am I listed as not connectable even though I'm not NAT/Firewalled?</a></li>
        <li><a href='#prox4'>Can I bypass my ISP's proxy?</a></li>
        <li><a href='#prox5'>How do I make my bittorrent client use a proxy?</a></li>
        <li><a href='#prox6'>Why can't I signup from behind a proxy?</a></li>
        <li><a href='#prox7'>Does this apply to other torrent sites?</a></li>
    </ul>",
    'faq_connect_header' => "Why can't I connect? Is the site blocking me?",
    'faq_connect' => "
    <ul>
        <li><a href='#conn1'>Name resolution problems</a></li>
        <li><a href='#conn2'>Maybe my address is blacklisted?</a></li>
        <li><a href='#conn3'>Your ISP blocks the site's address</a></li>
        <li><a href='#conn4'>Alternate port (81)</a></li>
    </ul>",
    'faq_problem_header' => "What if I can't find the answer to my problem here?",
    'faq_problem' => "
        <ul>
            <li><a href='#answer_9'>Some useful tips.</a></li>
        </ul>",

    'faq_siteinfo_body' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='site1'></a>
        <p>What is this bittorrent all about anyway? How do I get the files?</p>
        <p>Check out <a href='" . url_proxy('https://www.btfaq.com/') . "'>Brian's BitTorrent FAQ and Guide.</a></p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='site2'>
        <p>Where does the donated money go?</p>
        <p>{$site_config['site_name']} is situated on a dedicated server in the Hinterlands. For the moment we have monthly running costs of approximately &#36;125.00.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='site4'></a>
        <p>Where can I get a copy of the source code?</p>
        <p>Pu-239 is an active open source project available for download via Github <a href='" . url_proxy('https://github.com/darkalchemy/Pu-239') . "'>Zip download</a>. Please note: We do not give any kind of support on the source code so please don't bug us about it. If it works, great, if not too bad. Use this software at your own risk!</p>
    </div>",

    'faq_userinfo_body' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='user1'></a>
        <p>I registered an account but did not receive the confirmation e-mail!</p>
        <p>You can contact site staff with your request on irc</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='user2'>
        <p>I've lost my user name or password! Can you send it to me?</p>
        <p>Please use <a href='{$site_config['baseurl']}/recover.php'>this form</a> to have the login details emailed to you.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='user3'></a>
        <p>Can you rename my account?</p>
        <p>We can rename accounts, please do not create new one. You can contact site staff with your request.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='user4'></a>
        <p>Can you delete my (confirmed) account?</p>
        <p>You can contact site staff with your request.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
    <a id='userb'></a>
        <p>So, what's MY ratio?</p>
        <p>Click on your <a href='{$site_config['baseurl']}/usercp.php?action=default'>profile</a>, then on your user name (at the top).</p>
        <p>It's important to distinguish between your overall ratio and the individual ratio on each torrent you may be seeding or leeching. The overall ratio takes into account the total uploaded and downloaded from your account since you joined the site. The individual ratio takes into account those values for each torrent.</p>
        <p>You may see two symbols instead of a number: 'Inf.', which is just an abbreviation for Infinity, and means that you have downloaded 0 bytes while uploading a non-zero amount (ul/dl becomes infinity); '---', which should be read as 'non-available', and shows up when you have both downloaded and uploaded 0 bytes (ul/dl = 0/0 which is an indeterminate amount).</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='user5'></a>
        <p>Why is my IP displayed on my details page?</p>
        <p>Only you and the site moderators can view your IP address and email. Regular users do not see that information.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='user6'></a>
        <p>Help! I cannot login!? (a.k.a. Login of Death)</p>
        <p>This problem sometimes occurs with MSIE. Close all Internet Explorer windows and open Internet Options in the control panel. Click the Delete Cookie button. You should now be able to login.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='user7'></a>
        <p>My IP address is dynamic. How do I stay logged in?</p>
        <p>You do not have to anymore. All you have to do is make sure you are logged in with your actual IP when starting a torrent session. After that, even if the IP changes mid-session, the seeding or leeching will continue and the statistics will update without any problem.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='user8'></a>
        <p>Why am I listed as not connectable? (And why should I care?)</p>
        <p>The tracker has determined that you are firewalled or NATed and cannot accept incoming connections.</p>
        <p>This means that other peers in the swarm will be unable to connect to you, only you to them. Even worse, if two peers are both in this state they will not be able to connect at all. This obviously has a detrimental effect on the overall speed.</p>
        <p>The way to solve the problem involves opening the ports used for incoming connections (the same range you defined in your client) on the firewall and/or configuring your NAT server to use a basic form of NAT for that range instead of NAPT (the actual process differs widely between different router models. Check your router documentation and/or support forum. You will also find lots of information on the subject at <a href='" . url_proxy('https://portforward.com/') . "'>PortForward</a>).</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='user9'></a>
        <p>What are the different user classes?</p>" . main_table(
            "
            <tr>
                <td class='rowhead'><span class='user'>User</span></td>
                <td class='rowhead'><p>The default class of new members.</p></td>
            </tr>
            <tr>
              <td class='rowhead'><span class='power_user'>Power User</span></td>
              <td class='rowhead'><p>Can download DOX over 1MB and view NFO files.</p></td>
            </tr>
            <tr>
              <td class='rowhead'><img src='{$site_config['pic_baseurl']}star.gif' alt='Star' /></td>
              <td class='rowhead'><p>Has donated money to {$site_config['site_name']}.</p></td>
            </tr>
            <tr>
                <td class='rowhead'><span class='vip'>VIP</span></td>
                <td class='rowhead'><p>Same privileges as Power User and is considered an Elite Member of {$site_config['site_name']}. Immune to automatic demotion.</p></td>
            </tr>
            <tr>
                <td class='rowhead'>Other</td>
                <td class='rowhead'><p>Customised title.</p></td>
            </tr>
            <tr>
                <td class='rowhead'><span class='uploader'>Uploader</span></td>
                <td class='rowhead'><p>Same as PU except with upload rights and immune to automatic demotion.</p></td>
            </tr>
            <tr>
                <td class='rowhead'><span class='moderator'>Moderator</span></td>
                <td class='rowhead'><p>Can edit and delete any uploaded torrents. Can also moderate user    comments and disable accounts.</p></td>
            </tr>
            <tr>
                <td class='rowhead'><span class='administrator'>Administrator</span></td>
                <td class='rowhead'><p>Can do just about anything.</p></td>
            </tr>
            <tr>
                <td class='rowhead'><span class='sysop'>SysOp</span></td>
                <td class='rowhead'>Runs day to day matters on site</td>
            </tr>"
        ),
    'faq_promotion_header' => "
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='usera'></a>
        <p>How does this promotion thing work anyway?</p>",
    'faq_promotion_body' => main_table(
            "
            <tr>
                <td class='rowhead'><span class='power_user'>Power User</span></td>
                <td class='rowhead'>
                    <p>Must have been be a member for at least 4 weeks, have uploaded at least 25GB and have a ratio at or above 1.05.</p>
                    <p>The promotion is automatic when these conditions are met. Note that you will be automatically demoted from this status if your ratio drops below 0.95 at any time.</p>
                </td>
            </tr>
            <tr>
                <td class='rowhead'><img src='{$site_config['pic_baseurl']}star.gif' alt='Star' /></td>
                <td class='rowhead'><p>Just donate, and send the <a href='{$site_config['baseurl']}/contactstaff.php'>Staff</a> the details.</p></td>
            </tr>
            <tr>
                <td class='rowhead'><span class='vip'>VIP</span></td>
                <td class='rowhead'>
                    <p>Assigned by mods at their discretion to users they feel contribute something special to the site.</p>
                    <p>(Anyone begging for VIP status will be automatically disqualified.)</p>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>Other</td>
                <td class='rowhead'><p>Conferred by mods at their discretion (not available to Users or Power Users).</p></td>
            </tr>
            <tr>
                <td class='rowhead'><span class='uploader'>Uploader</span></td>
                <td class='rowhead'><p>Appointed by Admins/SysOp (see the 'Uploading' section for conditions).</p></td>
            </tr>
            <tr>
                <td class='rowhead'><span class='moderator'>Moderator</span></td>
                <td class='rowhead'><p>You don't ask us, we'll ask you!</p></td>
            </tr>"
        )
        . "
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='usere'></a>
        <p>Hey! I've seen Power Users with less than 25GB uploaded!</p>
        <p>The PU limit used to be 10GB and we didn't demote anyone when we raised it to 25GB.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='userc'></a>
        <p>Why can't my friend become a member?</p>
        <p>There is a {$site_config['maxusers']} users limit. When that number is reached we stop accepting new members. Accounts inactive for more than 42 days are automatically deleted, so keep trying. (There is no reservation or queuing system, don't ask for that.)</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='userd'></a>
        <p>How do I add an avatar to my profile?</p>
        <p>First, find an image that you like, and that is within the <a href='{$site_config['baseurl']}/rules.php'>Rules</a>. Then you will have to find a place to host it, such as our own <a href='{$site_config['baseurl']}/bitbucket.php'>BitBucket</a>. (Other popular choices are <a href='" . url_proxy('https://photobucket.com/') . "'>Photobucket</a>, <a href='" . url_proxy('https://uploadit.org/') . "'>Upload-It!</a> or <a href='" . url_proxy('https://www.imageshack.us/') . "'>ImageShack</a>). All that is left to do is copy the URL you were given when uploading it to the avatar field in your <a href='{$site_config['baseurl']}/usercp.php?action=default'>profile</a>.</p>
        <p>Please do not make a post just to test your avatar. If everything is allright you'll see it in your profile</p>
    </div>",
    'faq_details_page' => 'details page',
    'faq_stats_title' => 'Stats',
    'faq_stats_body' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats1'></a>
        <p>Most common reason for stats not updating</p>
        <ul class='disc left20'>
            <li class='padding10'>The user is cheating. (a.k.a. 'Summary Ban')</li>
            <li class='padding10'>The server is overloaded and unresponsive. Just try to keep the session open until the server responds again. (Flooding the server with consecutive manual updates is not recommended.)</li>
            <li class='padding10'>You are using a faulty client. If you want to use an experimental or CVS version you do it at your own risk.</li>
        </ul>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats2'></a>
        <p>Best practices</p>
        <p>If a torrent you are currently leeching/seeding is not listed on your profile, just wait or force a manual update.</p>
        <p>Make sure you exit your client properly, so that the tracker receives 'event=completed'.</p>
        <p>If the tracker is down, do not stop seeding. As long as the tracker is back up before you exit the client the stats should update properly.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats3'></a>
        <p>May I use any bittorrent client?</p>
        Yes. The tracker now updates the stats correctly for all bittorrent clients. However, we still recommend that you avoid the following clients:
        <ul class='disc left20'>
            <li class='padding10'>
                BitTorrent++
            </li>
            <li class='padding10'>
                Nova Torrent
            </li>
            <li class='padding10'>
                TorrentStorm
            </li>
        </ul>
        <p>These clients do not report correctly to the tracker when canceling/finishing a torrent session. If you use them then a few MB may not be counted towards the stats near the end, and torrents may still be listed in your profile for some time after you have closed the client.</p>
        <p>Also, clients in alpha or beta version should be avoided.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats4'></a>
        <p>Why is a torrent I'm leeching/seeding listed several times in my profile?</p>
        <p>If for some reason (e.g. pc crash, or frozen client) your client exits improperly and you restart it, it will have a new peer_id, so it will show as a new torrent. The old one will never receive a 'event=completed' or 'event=stopped' and will be listed until some tracker timeout. Just ignore it, it will eventually go away.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats5'></a>
        <p>I've finished or cancelled a torrent. Why is it still listed in my profile?</p>
        <p>Some clients, notably TorrentStorm and Nova Torrent, do not report properly to the tracker when canceling or finishing a torrent. In that case the tracker will keep waiting for some message - and thus listing the torrent as seeding or leeching - until some timeout occurs. Just ignore it, it will eventually go away.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats6'></a>
        <p>Why do I sometimes see torrents I'm not leeching in my profile!?</p>
        <p>When a torrent is first started, the tracker uses the IP to identify the user. Therefore the torrent will become associated with the user <i>who last accessed the site</i> from that IP. If you share your IP in some way (you are behind NAT/ICS, or using a proxy), and some of the persons you share it with are also users, you may occasionally see their torrents listed in your profile. (If they start a torrent session from that IP and you were the last one to visit the site the torrent will be associated with you). Note that now torrents listed in your profile will always count towards your total stats.</p>
        <p>To make sure your torrents show up in your profile you should visit the site immediately before starting a session.</p>
        <p>(The only way to completely stop foreign torrents from showing in profiles is to forbid users without an individual IP from accessing the site. Yes, that means you. Complain at your own risk.)</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats7'></a>
        <p>Multiple IPs (Can I login from different computers?)</p>
        <p>Yes, the tracker is now capable of following sessions from different IPs for the same user. A torrent is associated with the user when it starts, and only at that moment is the IP relevant. So if you want to seed/leech from computer A and computer B with the same account you should access the site from computer A, start the torrent there, and then repeat both steps from computer B (not limited to two computers or to a single torrent on each, this is just the simplest example). You do not need to login again when closing the torrent.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats8'></a>
        <p>How does NAT/ICS change the picture?</p>
        <p>This is a very particular case in that all computers in the LAN will appear to the outside world as having the same IP. We must distinguish between two cases:</p>
        <ol class='decimal left20'>
            <li class='padding10'>
                <p><i>You are the single {$site_config['site_name']} user in the LAN</i></p>
                <p>You should use the same {$site_config['site_name']} account in all the computers.</p>
                <p>Note also that in the ICS case it is preferable to run the BT client on the ICS gateway. Clients running on the other computers will be unconnectable (they will be listed as such, as explained elsewhere in the FAQ) unless you specify the appropriate services in your ICS configuration (a good explanation of how to do this for Windows XP can be found <a href='" . url_proxy('https://www.microsoft.com/downloads/details.aspx?FamilyID=1dcff3ce-f50f-4a34-ae67-cac31ccd7bc9&amp;displaylang=en') . "'>here</a>). In the NAT case you should configure different ranges for clients on different computers and create appropriate NAT rules in the router. (Details vary widely from router to router and are outside the scope of this FAQ. Check your router documentation and/or support forum.)</p>
            </li>            
            <li class='padding10'>
                <p><i>There are multiple {$site_config['site_name']} users in the LAN</i></p>
                <p>At present there is no way of making this setup always work properly with {$site_config['site_name']}. Each torrent will be associated with the user who last accessed the site from within the LAN before the torrent was started. Unless there is cooperation between the users mixing of statistics is possible. (User A accesses the site, downloads a .torrent file, but does not start the torrent immediately. Meanwhile, user B accesses the site. User A then starts the torrent. The torrent will count towards user B's statistics, not user A's.)</p>
                <p>It is your LAN, the responsibility is yours. Do not ask us to ban other users with the same IP, we will not do that. (Why should we ban <i>them</i> instead of <i>you</i>?)</p>
            </li>
        </ol>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='stats9'></a>
        <p>For those of you who are interested...</p>
        <p>Some <a href='{$site_config['baseurl']}/anatomy.php'>info</a> about the 'Anatomy of a torrent session'.</p>
    </div>",
    'faq_uploading_body' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='up1'></a>
        <p>Why can't I upload torrents?</p>
        <p>Only specially authorized users (<span style='color:#4040C0;'>Uploaders</span>) have permission to upload torrents.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='up2'></a>
        <p>What criteria must I meet before I can join the <span style='color:#4040C0;'>Uploader</span> team?</p>
        <p>You must be able to provide releases that:</p>
        <ul class='disc left20'>
            <li class='padding10'>
                include a proper NFO
            </li>
            <li class='padding10'>
                are genuine scene releases. If it's not on <a href='" . url_proxy('https://www.nforce.nl') . "'>NFOrce</a> or <a href='" . url_proxy('https://www.grokmusiq.com/') . "'>grokMusiQ</a> then forget it!
            </li>
            <li class='padding10'>
                are not older than seven (7) days
            </li>
            <li class='padding10'>
                have all files in original format (usually 14.3 MB RARs)
            </li>
            <li class='padding10'>
                you'll be able to seed, or make sure are well-seeded, for at least 24 hours.
            </li>
        </ul>
        <p>Also, you should have at least 2MBit upload bandwith.</p>
        <p>If you think you can match these criteria do not hesitate to <a href='{$site_config['baseurl']}/staff.php'>contact</a> one of the administrators.</p>
        <p>Remember! Write your application carefully! Be sure to include your UL speed and what kind of stuff you're planning to upload.</p>
        <p>Only well written letters with serious intent will be considered.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='up3'></a>
        <p>Can I upload your torrents to other trackers?</p>
        <p>No, not the torrent file. We are a closed, limited-membership community. Only registered users can use the TB tracker. Posting our torrents on other trackers is useless, since most people who attempt to download them will be unable to connect with us. This generates a lot of frustration and bad-will against us at {$site_config['site_name']}, and will therefore not be tolerated.</p>
        <p>Complaints from other sites' administrative staff about our torrents being posted on their sites will result in the banning of the users responsible.</p>
        <p>(However, the files you download from us are yours to do as you please. You can always create another torrent, pointing to some other tracker, and upload it to the site of your choice.)</p>
    </div>",

    'faq_downloading_body' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='dl1'></a>
        <p>How do I use the files I've downloaded?</p>
        <p>Check out <a href='{$site_config['baseurl']}/formats.php'>this guide</a>.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dl2'></a>
        <p>Downloaded a movie and don't know what CAM/TS/TC/SCR means?</p>
        <p>Check out <a href='{$site_config['baseurl']}/videoformats.php'>this guide</a>.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dl3'></a>
        <p>Why did an active torrent suddenly disappear?</p>
        <p>There may be three reasons for this:</p>
        <ol class='decimal left20'>
            <li class='padding10'>The torrent may have been out-of-sync with the site <a href='{$site_config['baseurl']}/rules.php'>rules</a>.</li>
            <li class='padding10'>The uploader may have deleted it because it was a bad release. A replacement will probably be uploaded to take its place.</li>
            <li class='padding10'>Torrents are automatically deleted after 28 days.</li>
        </ol>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dl4'></a>
        <p>How do I resume a broken download or reseed something?</p>
        <p>Open the .torrent file. When your client asks you for a location, choose the location of the existing file(s) and it will resume/reseed the torrent.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dl5'></a>
        <p>Why do my downloads sometimes stall at 99%?</p>
        <p>The more pieces you have, the harder it becomes to find peers who have pieces you are missing. That is why downloads sometimes slow down or even stall when there are just a few percent remaining. Just be patient and you will, sooner or later, get the remaining pieces.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dl6'></a>
        <p>What are these 'a piece has failed an hash check' messages?</p>
        <p>Bittorrent clients check the data they receive for integrity. When a piece fails this check it is automatically re-downloaded. Occasional hash fails are a common occurrence, and you shouldn't worry.</p>
        <p>Some clients have an (advanced) option/preference to 'kick/ban clients that send you bad data' or similar. It should be turned on, since it makes sure that if a peer repeatedly sends you pieces that fail the hash check it will be ignored in the future.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dl7'></a>
        <p>The torrent is supposed to be 100MB. How come I downloaded 120MB?</p>
        <p>See the hash fails topic. If your client receives bad data it will have to redownload it, therefore the total downloaded may be larger than the torrent size. Make sure the 'kick/ban' option is turned on to minimize the extra downloads.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dl8'></a>
        <p>Why do I get a 'Not authorized (xx h) - READ THE FAQ!' error?</p>
        <p>From the time that each new torrent is uploaded to the tracker, there is a period of time that some users must wait before they can download it.</p>
        <p>This delay in downloading will only affect users with a low ratio, and users with low upload amounts.</p>
        <p>" . main_table(
            "
            <tr>
                <td class='rowhead'>Ratio below</td>
                <td class='rowhead'><span style='color:#BB0000;'>0.50</span></td>
                <td class='rowhead'>and/or upload below</td>
                <td class='rowhead'><div>5.0GB</div></td>
                <td class='rowhead'>delay of</td>
                <td class='rowhead'><div>48h</div></td>
            </tr>
            <tr>
                <td class='rowhead'>Ratio below</td>
                <td class='rowhead'><span style='color:#A10000;'>0.65</span></td>
                <td class='rowhead'>and/or upload below</td>
                <td class='rowhead'><div>6.5GB</div></td>
                <td class='rowhead'>delay of</td>
                <td class='rowhead'><div>24h</div></td>
            </tr>
            <tr>
                <td class='rowhead'>Ratio below</td>
                <td class='rowhead'><span style='color:#880000;'>0.80</span></td>
                <td class='rowhead'>and/or upload below</td>
                <td class='rowhead'><div>8.0GB</div></td>
                <td class='rowhead'>delay of</td>
                <td class='rowhead'><div>12h</div></td>
            </tr>
            <tr>
                <td class='rowhead'>Ratio below</td>
                <td class='rowhead'><span style='color:#6E0000;>0.95</span></td>
                <td class='rowhead'>and/or upload below</td>
                <td class='rowhead'><div>9.5GB</div></td>
                <td class='rowhead'>delay of</td>
                <td class='rowhead'><div>06h</div></td>
            </tr>"
        ) . "
        <p>'And/or' means any or both. Your delay will be the largest one for which you meet at least one condition.</p>",
    'faq_in' => 'In ',
    'faq_your' => 'your',
    'faq_case' => ' particular case, ',
    'faq_both' => 'both ',
    'faq_ratio' => 'your ratio of ',
    'faq_and' => ' and ',
    'faq_totalup' => 'your total uploaded of ',
    'faq_delay' => ' a delay of ',
    'faq_hours' => ' hours',
    'faq_even' => 'Even if your ',
    'faq_totup' => 'total uploaded is ',
    'faq_ratiois' => 'ratio is ',
    'faq_nodelay' => 'you will experience no delay.',
    'faq_downloading_body1' => "
        <p>This applies to new users as well, so opening a new account will not help. Note also that this works at tracker level, you will be able to grab the .torrent file itself at any time.</p>
        <!--<p>The delay applies only to leeching, not to seeding. If you got the files from any other source and wish to seed them you may do so at any time irrespectively of your ratio or total uploaded.</p>-->
        <p>N.B. Due to some users exploiting the 'no-delay-for-seeders' policy we had to change it. The delay now applies to both seeding and leeching. So if you are subject to a delay and get the files from some other source you will not be able to seed them until the delay has elapsed.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
    <a id='dl9'></a>
        <p>Why do I get a 'rejected by tracker - Port xxxx is blacklisted' error?</p>
        <p>Your client is reporting to the tracker that it uses one of the default bittorrent ports (6881-6889) or any other common p2p port for incoming connections.</p>
        <p>{$site_config['site_name']} does not allow clients to use ports commonly associated with p2p protocols. The reason for this is that it is a common practice for ISPs to throttle those ports (that is, limit the bandwidth, hence the speed).</p>
        <p>The blocked ports list include, but is not neccessarily limited to, the following:</p>" . main_table(
            "
            <tr>
                <td class='rowhead'>Direct Connect</td>
                <td class='rowhead'><div>411 - 413</div></td>
            </tr>
            <tr>
                <td class='rowhead'>Kazaa</td>
                <td class='rowhead'><div>1214</div></td>
            </tr>
            <tr>
                <td class='rowhead'>eDonkey</td>
                <td class='rowhead'><div>4662</div></td>
            </tr>
            <tr>
                <td class='rowhead'>Gnutella</td>
                <td class='rowhead'><div>6346 - 6347</div></td>
            </tr>
            <tr>
              <td class='rowhead'>BitTorrent</td>
              <td class='rowhead'><div>6881 - 6889</div></td>
           </tr>"
        ) . "
        <p>In order to use use our tracker you must configure your client to use any port range that does not contain those ports (a range within the region 49152 through 65535 is preferable, cf. <a href='" . url_proxy('https://www.iana.org/assignments/port-numbers') . "'>IANA</a>). Notice that some clients, like Azureus 2.0.7.0 or higher, use a single port for all torrents, while most others use one port per open torrent. The size of the range you choose should take this into account (typically less than 10 ports wide. There is no benefit whatsoever in choosing a wide range, and there are possible security implications).</p>
        <p>These ports are used for connections between peers, not client to tracker. Therefore this change will not interfere with your ability to use other trackers (in fact it should <i>increase</i> your speed with torrents from any tracker, not just ours). Your client will also still be able to connect to peers that are using the standard ports. If your client does not allow custom ports to be used, you will have to switch to one that does.</p>
        <p>Do not ask us, or in the forums, which ports you should choose. The more random the choice is the harder it will be for ISPs to catch on to us and start limiting speeds on the ports we use. If we simply define another range ISPs will start throttling that range also.</p>
        <p>Finally, remember to forward the chosen ports in your router and/or open them in your firewall, should you have them. See the <i><a href='#user8'>Why am I listed as not connectable?</a></i>  section and links therein for more information on this.</p>
    </div>",
    'faq_downloading_body2' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='dla'></a>
        <p>What's this 'IOError - [Errno13] Permission denied' error?</p>
        <p>If you just want to fix it reboot your computer, it should solve the problem. Otherwise read on.</p>
        <p>IOError means Input-Output Error, and that is a file system error, not a tracker one. It shows up when your client is for some reason unable to open the partially downloaded torrent files. The most common cause is two instances of the client to be running simultaneously: the last time the client was closed it somehow didn't really close but kept running in the background, and is therefore still locking the files, making it impossible for the new instance to open them.</p>
        <p>A more uncommon occurrence is a corrupted FAT. A crash may result in corruption that makes the partially downloaded files unreadable, and the error ensues. Running scandisk should solve the problem. (Note that this may happen only if you're running Windows 9x - which only support FAT - or NT/2000/XP with FAT formatted hard drives. NTFS is much more robust and should never permit this problem.)</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dlb'></a>
        <p>What's this 'TTL' in the browse page?</p>
        <p>The torrent's Time To Live, in hours. It means the torrent will be deleted from the tracker after that many hours have elapsed (yes, even if it is still active). Note that this a maximum value, the torrent may be deleted at any time if it's inactive.</p>
    </div>",
    'faq_improve_speed_title' => 'How can I improve my download speed?',
    'faq_improve_speed_body' => "
    <p>The download speed mostly depends on the seeder-to-leecher ratio (SLR). Poor download speed is mainly a problem with new and very popular torrents where the SLR is low.</p>
    <p>(Proselytising sidenote: make sure you remember that you did not enjoy the low speed. Seed so that others will not endure the same.)</p>
    <p>There are a couple of things that you can try on your end to improve your speed:</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dlsp1'></a>
        <p>Do not immediately jump on new torrents</p>
        <p>In particular, do not do it if you have a slow connection. The best speeds will be found around the half-life of a torrent, when the SLR will be at its highest. (The downside is that you will not be able to seed so much. It's up to you to balance the pros and cons of this.)</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dlsp2'></a>
        <p>Make yourself connectable </p>
        <p>See the <i><a href='#user8'>Why am I listed as not connectable?</a></i> section.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dlsp3'></a>
        <p>Limit your upload speed</p>
        The upload speed affects the download speed in essentially two ways:
        <ul class='disc left20'>
            <li class='padding10'>
                Bittorrent peers tend to favour those other peers that upload to them. This means that if A and B are leeching the same torrent and A is sending data to B at high speed then B will try to reciprocate. So due to this effect high upload speeds lead to high download speeds.
            </li>
            <li class='padding10'>
                Due to the way TCP works, when A is downloading something from B it has to keep telling B that it received the data sent to him. (These are called acknowledgements - ACKs -, a sort of 'got it!' messages). If A fails to do this then B will stop sending data and wait. If A is uploading at full speed there may be no bandwidth left for the ACKs and they will be delayed. So due to this effect excessively high upload speeds lead to low download speeds.
            </li>
        </ul>
        <p>The full effect is a combination of the two. The upload should be kept as high as possible while allowing the ACKs to get through without delay. A good thumb rule is keeping the upload at about 80% of the theoretical upload speed. You will have to fine tune yours to find out what works best for you. (Remember that keeping the upload high has the additional benefit of helping with your ratio.)</p>
        <p>If you are running more than one instance of a client it is the overall upload speed that you must take into account. Some clients (e.g. Azureus) limit global upload speed, others (e.g. Shad0w's) do it on a per torrent basis. Know your client. The same applies if you are using your connection for anything else (e.g. browsing or ftp), always think of the overall upload speed.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dlsp4'></a>
        <p>Limit the number of simultaneous connections</p>
        <p>Some operating systems (like Windows 9x) do not deal well with a large number of connections, and may even crash. Also some home routers (particularly when running NAT and/or firewall with stateful inspection services) tend to become slow or crash when having to deal with too many connections. There are no fixed values for this, you may try 60 or 100 and experiment with the value. Note that these numbers are additive, if you have two instances of a client running the numbers add up.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dlsp5'></a>
        <p>Limit the number of simultaneous uploads</p>
        <p>Isn't this the same as above? No. Connections limit the number of peers your client is talking to and/or downloading from. Uploads limit the number of peers your client is actually uploading to. The ideal number is typically much lower than the number of connections, and highly dependent on your (physical) connection.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dlsp6'></a>
        <p>Just give it some time</p>
        <p>As explained above peers favour other peers that upload to them. When you start leeching a new torrent you have nothing to offer to other peers and they will tend to ignore you. This makes the starts slow, in particular if, by change, the peers you are connected to include few or no seeders. The download speed should increase as soon as you have some pieces to share.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='dlsp7'></a>
        <p>Why is my browsing so slow while leeching?</p>
        <p>Your download speed is always finite. If you are a peer in a fast torrent it will almost certainly saturate your download bandwidth, and your browsing will suffer. At the moment there is no client that allows you to limit the download speed, only the upload. You will have to use a third-party solution, such as <a href='" . url_proxy('https://www.netlimiter.com/') . "'>NetLimiter</a>.</p>
        <p>Browsing was used just as an example, the same would apply to gaming, IMing, etc...</p>
    </div>",
    'faq_proxy_title' => '<h2>My ISP uses a transparent proxy. What should I do?</h2>',
    'faq_proxy_body' => "
    <p><i>Caveat: This is a large and complex topic. It is not possible to cover all variations here.</i></p>
    <p>Short reply: change to an ISP that does not force a proxy upon you. If you cannot or do not want to then read on.</p>
    <div class='top20 bg-02 padding20 round10'>
        <a id='prox1'></a>
        <p>What is a proxy?</p>
        <p>Basically a middleman. When you are browsing a site through a proxy your requests are sent to the proxy and the proxy forwards them to the site instead of you connecting directly to the site. There are several classifications (the terminology is far from standard):</p>
        <p>",
    'faq_proxy_body2' => main_table(
            "
        <tr>
            <td class='rowhead'>Transparent</td>
            <td class='rowhead'><p>A transparent proxy is one that needs no configuration on the clients. It works by automatically redirecting all port 80 traffic to the proxy. (Sometimes used as synonymous for non-anonymous.)</p></td>
        </tr>
        <tr>
            <td class='rowhead'>Explicit/Voluntary</td>
            <td class='rowhead'><p>Clients must configure their browsers to use them.</p></td>
        </tr>
        <tr>
            <td class='rowhead'>Anonymous</td>
            <td class='rowhead'><p>The proxy sends no client identification to the server. (HTTP_X_FORWARDED_FOR header is not sent; the server does not see your IP.)</p></td>
        </tr>
        <tr>
            <td class='rowhead'>Highly Anonymous</td>
            <td class='rowhead'><p>The proxy sends no client nor proxy identification to the server. (HTTP_X_FORWARDED_FOR, HTTP_VIA and HTTP_PROXY_CONNECTION headers are not sent; the server doesn't see your IP and doesn't even know you're using a proxy.)</p></td>
        </tr>
        <tr>
            <td class='rowhead'>Public</td>
            <td class='rowhead'><p>(Self explanatory)</p></td>
        </tr>"
        ) . "
        <p>A transparent proxy may or may not be anonymous, and there are several levels of anonymity.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='prox2'></a>
        <p>How do I find out if I'm behind a (transparent/anonymous) proxy?</p>
        <p>Try <a href='" . url_proxy('https://proxyjudge.org') . "'>ProxyJudge</a>. It lists the HTTP headers that the server where it is running received from you. The relevant ones are HTTP_CLIENT_IP, HTTP_X_FORWARDED_FOR and REMOTE_ADDR.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='prox3'></a>
        <p>Why am I listed as not connectable even though I'm not NAT/Firewalled?</p>
        <p>The {$site_config['site_name']} tracker is quite smart at finding your real IP, but it does need the proxy to send the HTTP header HTTP_X_FORWARDED_FOR. If your ISP's proxy does not then what happens is that the tracker will interpret the proxy's IP address as the client's IP address. So when you login and the tracker tries to connect to your client to see if you are NAT/firewalled it will actually try to connect to the proxy on the port your client reports to be using for incoming connections. Naturally the proxy will not be listening on that port, the connection will fail and the tracker will think you are NAT/firewalled.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='prox4'></a>
        <p>Can I bypass my ISP's proxy?</p>
        <p>If your ISP only allows HTTP traffic through port 80 or blocks the usual proxy ports then you would need to use something like <a href='" . url_proxy('https://www.socks.permeo.com') . "'>socks</a> and that is outside the scope of this FAQ.</p>
        <p>The site accepts connections on port 81 besides the usual 80, and using them may be enough to fool some proxies. So the first thing to try should be connecting to {$site_config['baseurl']}:81. Note that even if this works your bt client will still try to connect to port 80 unless you edit the announce url in the .torrent file.</p>
        <p>Otherwise you may try the following:</p>
        <ul class='disc left20'>
            <li class='padding10'>
                Choose any public non-anonymous proxy that does not use port 80 (e.g. from <a href='" . url_proxy('https://tools.rosinstrument.com/proxy') . "'>this</a>, <a href='" . url_proxy('https://www.proxy4free.com/index.html') . "'>this</a> or <a href='" . url_proxy('https://www.samair.ru/proxy') . "'>this</a> list).
            </li>
            <li class='padding10'>
                Configure your computer to use that proxy. For Windows XP, do <i>Start</i>, <i>Control Panel</i>, <i>Internet Options</i>, <i>Connections</i>, <i>LAN Settings</i>, <i>Use a Proxy server</i>, <i>Advanced</i> and type in the IP and port of your chosen proxy. Or from Internet Explorer use <i>Tools</i>, <i>Internet Options</i>, ...
            </li>
            <li class='padding10'>
                (Facultative) Visit <a href='" . url_proxy('https://proxyjudge.org') . "'>ProxyJudge</a>. If you see an HTTP_X_FORWARDED_FOR in the list followed by your IP then everything should be ok, otherwise choose another proxy and try again.
            </li>
            <li class='padding10'>
                Visit {$site_config['site_name']}. Hopefully the tracker will now pickup your real IP (check your profile to make sure).
            </li>
        </ul>
        <p>Notice that now you will be doing all your browsing through a public proxy, which are typically quite slow. Communications between peers do not use port 80 so their speed will not be affected by this, and should be better than when you were 'unconnectable'.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='prox5'></a>
        <p>How do I make my bittorrent client use a proxy?</p>
        <p>Just configure Windows XP as above. When you configure a proxy for Internet Explorer you're actually configuring a proxy for all HTTP traffic (thank Microsoft and their 'IE as part of the OS policy' ). On the other hand if you use another browser (Opera/Mozilla/Firefox) and configure a proxy there you'll be configuring a proxy just for that browser. We don't know of any BT client that allows a proxy to be specified explicitly.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='prox6'></a>
        <p>Why can't I signup from behind a proxy?</p>
        <p>It <i>is</i> our policy to allow new accounts to be opened from behind a proxy.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='prox7'></a>
        <p>Does this apply to other torrent sites?</p>
        <p>This section was written for {$site_config['site_name']}, a closed, port 80-81 tracker. Other trackers may be open or closed, and many listen on e.g. ports 6868 or 6969. The above does not necessarily apply to other trackers.</p>
    </div>",
    'faq_blocked_title' => "<h2>Why can't I connect? Is the site blocking me?</h2>",
    'faq_blocked_body' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='conn1'></a>
        <p>Your failure to connect may be due to several reasons.</p>
        <p>Maybe my address is blacklisted?
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='conn2'></a>
        <p>The site blocks addresses listed in the (former) <a href='" . url_proxy('https://methlabs.org/') . "'>PeerGuardian</a> database, as well as addresses of banned users. This works at Apache/PHP level, it's just a script that blocks <i>logins</i> from those addresses. It should not stop you from reaching the site. In particular it does not block lower level protocols, you should be able to ping/traceroute the server even if your address is blacklisted. If you cannot then the reason for the problem lies elsewhere.</p>
        <p>If somehow your address is indeed blocked in the PG database do not contact us about it, it is not our policy to open <i>ad hoc</i> exceptions. You should clear your IP with the database maintainers instead.</p>
    </div>
    <div class='top20 bg-02 padding20 round10'>
        <a id='conn3'></a>
        <p>Your ISP blocks the site's address</p>
        <p>(In first place, it's unlikely your ISP is doing so. DNS name resolution and/or network problems are the usual culprits.)</p>
        <p>There's nothing we can do. You should contact your ISP (or get a new one). Note that you can still visit the site via a proxy, follow the instructions in the relevant section. In this case it doesn't matter if the proxy is anonymous or not, or which port it listens to.</p>
        <p>Notice that you will always be listed as an 'unconnectable' client because the tracker will be unable to check that you're capable of accepting incoming connections.</p>
    </div>",
    'faq_alt_port' => '<h2>Alternate port (81)</h2>',
    'faq_alt_port_body' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='conn4'></a>
        <p>Some of our torrents use ports other than the usual HTTP port 80. This may cause problems for some users, for instance those behind some firewall or proxy configurations. You can easily solve this by editing the .torrent file yourself with any torrent editor, e.g. <a href='" . url_proxy('https://sourceforge.net/projects/burst/') . "'>MakeTorrent</a>, and replacing the announce url {$site_config['baseurl']}:81 with {$site_config['site_name']}:80 or just {$site_config['site_name']}.</p>
        <p>Editing the .torrent with Notepad is not recommended. It may look like a text file, but it is in fact a bencoded file. If for some reason you must use a plain text editor, change the announce url to {$site_config['site_name']}:80, not {$site_config['site_name']}. (If you're thinking about changing the number before the announce url instead, you know too much to be reading this.)</p>
    </div>",
    'faq_problem_title' => "<h2>What if I can't find the answer to my problem here?</h2>",
    'faq_problem_body' => "
    <div class='top20 bg-02 padding20 round10'>
        <a id='answer_9'></a>
        <p>Post in the <a href='{$site_config['baseurl']}/forums.php'>Forums</a>, by all means. You'll find they are usually a friendly and helpful place, provided you follow a few basic guidelines:</p>
        <ul class='disc left20'>
            <li class='padding10'>
                Make sure your problem is not really in this FAQ. There's no point in posting just to be sent back here.
            </li>
            <li class='padding10'>
                Before posting read the sticky topics (the ones at the top). Many times new information that still hasn't been incorporated in the FAQ can be found there.
            </li>
            <li class='padding10'>
                Help us in helping you. Do not just say 'it doesn't work!'. Provide details so that we don't have to guess or waste time asking. What client do you use? What's your OS? What's your network setup? What's the exact error message you get, if any? What are the torrents you are having problems with? The more you tell the easiest it will be for us, and the more probable your post will get a reply.
            </li>
            <li class='padding10'>
                And needless to say: be polite. Demanding help rarely works, asking for it usually does the trick.
            </li>
        </ul>
    </div>",
];
