<?php

declare(strict_types = 1);

use Delight\Auth\Auth;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
global $container, $site_config;

$auth = $container->get(Auth::class);
if (!$auth->isLoggedIn()) {
    get_template();
    $user['id'] = $user['class'] = $user['uploaded'] = $user['downloaded'] = 0;
} else {
    $user = check_user_status();
}
$site_name = "<span class='has-text-weight-bold'>{$site_config['site']['name']}</span>";
$HTMLOUT = "
            <div class='bordered'>
                <div class='alt_bordered bg-00 padding20'>
                    <h1 class='has-text-centered'>" . _fe('Welcome to {0}!', $site_name) . '</h1>
                    <p>' . _fe('Our goal is not to become another IPTorrents or Suprnova (not dissing either of them though). The goal is to provide the absolutely latest stuff. Therefore, only specially authorised users have permission to upload torrents. If you have access to 0-day stuff do not hesitate to {0}contact{1} us!', "<a href='{$site_config['paths']['baseurl']}/staff.php'>", '</a>') . '</p>
                    <p>' . _fe('This is a private tracker, and you have to register before you can get full access to the site. Before you do anything here at <b>{0}</b> we suggest you read the {1}rules{2}! There are only a few rules to abide by, but we do enforce them!', $site_config['site']['name'], "<a href='{$site_config['paths']['baseurl']}/rules.php'>", '</a>') . '</p>
                    <p>' . _fe('Before you go any further you should read the {0} {1}user agreement{2}.', $site_name, " <a href='{$site_config['paths']['baseurl']}/useragreement.php'>", '</a>') . "</p>
                </div>
            </div>
            <fieldset id='rules'>
                <legend class='level-center-center padding20 size_7'>
                    <img src='{$site_config['paths']['images_baseurl']}info.png' alt='' class='tooltipper right10 icon' title='" . _('Guidelines') . "'>" . _('Contents') . "
                </legend>
                <div class='bordered'>
                    <div class='alt_bordered bg-00'>
                        <div id='accordion'>
                            <p class='has-text-black accordion-toggle round5-top'>
                                " . _('Site information') . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#site1'>" . _('What is this bittorrent all about anyway? How do I get the files?') . "</a></li>
                                    <li><a href='#site2'>" . _('Where does the donated money go?') . "</a></li>
                                    <li><a href='#site4'>" . _('Where can I get a copy of the source code?') . "</a></li>
                                </ul>
                            </div>
                            <p class='has-text-black accordion-toggle'>
                                " . _('User information') . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#user1'>" . _('I registered an account but did not receive the confirmation e-mail!') . "</a></li>
                                    <li><a href='#user2'>" . _("I've lost my user name or password! Can you send it to me?") . "</a></li>
                                    <li><a href='#user3'>" . _('Can you rename my account?') . "</a></li>
                                    <li><a href='#user4'>" . _('Can you delete my (confirmed) account?') . "</a></li>
                                    <li><a href='#userb'>" . _("So, what's MY ratio?") . "</a></li>
                                    <li><a href='#user5'>" . _('Why is my IP displayed on my details page?') . "</a></li>
                                    <li><a href='#user6'>" . _('Help! I cannot login!? (a.k.a. Login of Death)') . "</a></li>
                                    <li><a href='#user7'>" . _('My IP address is dynamic. How do I stay logged in?') . "</a></li>
                                    <li><a href='#user8'>" . _('Why am I listed as not connectable? (And why should I care?)') . "</a></li>
                                    <li><a href='#user9'>" . _('What are the different user classes?') . "</a></li>
                                    <li><a href='#usera'>" . _('How does this promotion thing work anyway?') . "</a></li>
                                    <li><a href='#usere'>" . _("Hey! I've seen Power Users with less than 25GB uploaded!") . "</a></li>
                                    <li><a href='#userc'>" . _("Why can't my friend become a member?") . "</a></li>
                                    <li><a href='#userd'>" . _('How do I add an avatar to my profile?') . "</a></li>
                                </ul>
                            </div>
                            <p class='has-text-black accordion-toggle'>
                                " . _('Stats') . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#stats1'>" . _('Most common reasons for stats not updating') . "</a></li>
                                    <li><a href='#stats2'>" . _('Best practices') . "</a></li>
                                    <li><a href='#stats3'>" . _('May I use any bittorrent client?') . "</a></li>
                                    <li><a href='#stats4'>" . _("Why is a torrent I'm leeching/seeding listed several times in my profile?") . "</a></li>
                                    <li><a href='#stats5'>" . _("I've finished or cancelled a torrent. Why is it still listed in my profile?") . "</a></li>
                                    <li><a href='#stats6'>" . _("Why do I sometimes see torrents I'm not leeching in my profile!?") . "</a></li>
                                    <li><a href='#stats7'>" . _('Multiple IPs (Can I login from different computers?)') . "</a></li>
                                    <li><a href='#stats8'>" . _('How does NAT/ICS change the picture?') . "</a></li>
                                    <li><a href='#stats9'>" . _('For those of you who are interested (Anatomy of a torrent session)') . "</a></li>
                                </ul>
                            </div>
                            <p class='has-text-black accordion-toggle'>
                                " . _('Uploading') . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#up1'" . _(">Why can't I upload torrents?") . "</a> </li>
                                    <li><a href='#up2'>" . _('What criteria must I meet before I can join the Uploader team?') . "</a></li>
                                    <li><a href='#up3'>" . _('Can I upload your torrents to other trackers?') . "</a></li>
                                </ul>
                            </div>
                            <p class='has-text-black accordion-toggle'>
                                " . _('Downloading') . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#dl1'>" . _("How do I use the files I've downloaded?") . "</a></li>
                                    <li><a href='#dl2'>" . _("Downloaded a movie and don't know what CAM/TS/TC/SCR means?") . "</a></li>
                                    <li><a href='#dl3'>" . _('Why did an active torrent suddenly disappear?') . "</a></li>
                                    <li><a href='#dl4'>" . _('How do I resume a broken download or reseed something?') . "</a></li>
                                    <li><a href='#dl5'>" . _('Why do my downloads sometimes stall at 99%?') . "</a></li>
                                    <li><a href='#dl6'>" . _("What are these 'a piece has failed an hash check' messages?") . "</a></li>
                                    <li><a href='#dl7'>" . _('The torrent is supposed to be 100MB. How come I downloaded 120MB?') . "</a></li>
                                    <li><a href='#dl8'>" . _("Why do I get a 'Not authorized (xx h) - READ THE FAQ!' error?") . "</a></li>
                                    <li><a href='#dl9'>" . _("Why do I get a 'rejected by tracker - Port xxxx is blacklisted' error?") . "</a></li>
                                    <li><a href='#dla'>" . _("What's this 'IOError - [Errno13] Permission denied' error?") . "</a></li>
                                    <li><a href='#dlb'>" . _("What's this 'TTL' in the browse page?") . "</a></li>
                                </ul>
                            </div>
                            <p class='has-text-black accordion-toggle'>
                                " . _('How can I improve my download speed?') . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#dlsp1'>" . _('Do not immediately jump on new torrents') . "</a></li>
                                    <li><a href='#dlsp2'>" . _('Make yourself connectable') . "</a></li>
                                    <li><a href='#dlsp3'>" . _('Limit your upload speed') . "</a></li>
                                    <li><a href='#dlsp4'>" . _('Limit the number of simultaneous connections') . "</a></li>
                                    <li><a href='#dlsp5'>" . _('Limit the number of simultaneous uploads') . "</a></li>
                                    <li><a href='#dlsp6'>" . _('Just give it some time') . "</a></li>
                                    <li><a href='#dlsp7'>" . _('Why is my browsing so slow while leeching?') . "</a></li>
                                </ul>
                            </div>
                            <p class='has-text-black accordion-toggle'>
                                " . _('My ISP uses a transparent proxy. What should I do?') . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#prox1'>" . _('What is a proxy?') . "</a></li>
                                    <li><a href='#prox2'>" . _("How do I find out if I'm behind a (transparent/anonymous) proxy?") . "</a></li>
                                    <li><a href='#prox3'>" . _("Why am I listed as not connectable even though I'm not NAT/Firewalled?") . "</a></li>
                                    <li><a href='#prox4'>" . _("Can I bypass my ISP's proxy?") . "</a></li>
                                    <li><a href='#prox5'>" . _('How do I make my bittorrent client use a proxy?') . "</a></li>
                                    <li><a href='#prox6'>" . _("Why can't I signup from behind a proxy?") . "</a></li>
                                    <li><a href='#prox7'>" . _('Does this apply to other torrent sites?') . "</a></li>
                                </ul>
                            </div>
                            <p class='has-text-black accordion-toggle'>
                                " . _("Why can't I connect? Is the site blocking me?") . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#conn1'>" . _('Name resolution problems') . "</a></li>
                                    <li><a href='#conn2'>" . _('Maybe my address is blacklisted?') . "</a></li>
                                    <li><a href='#conn3'>" . _("Your ISP blocks the site's address") . "</a></li>
                                    <li><a href='#conn4'>" . _('Alternate port (81)') . "</a></li>
                                </ul>
                            </div>
                            <p class='has-text-black accordion-toggle round5-bottom'>
                                " . _("What if I can't find the answer to my problem here?") . "
                            </p>
                            <div class='accordion-content bg-02 round5 padding10'>
                                <ul class='left20 disc'>
                                    <li><a href='#answer_9'>" . _('Some useful tips.') . '</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>';

$HTMLOUT .= main_div("
                        <h2 class='has-text-centered padtop10' id='answer_1'>" . _('Site information') . "</h2>
                        <div id='answer_1_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='site1'></a>
                                <p>" . _('What is this bittorrent all about anyway? How do I get the files?') . '</p>
                                <p>' . _fe("Check out {0}Brian's BitTorrent FAQ and Guide.{1}", " <a href='" . url_proxy('https://www.btfaq.com/', false) . '>', '</a>') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='site2'></a>
                                <p>" . _('Where does the donated money go?') . '</p>
                                <p>' . _fe('{0} is situated on a dedicated server in the Hinterlands. For the moment we have monthly running costs of approximately &#36;{1}.', $site_config['site']['name'], 125.00) . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='site4'></a>
                                <p>" . _('Where can I get a copy of the source code?') . '</p>
                                <p>' . _fe("Pu-239 is an active open source project available for download via Github {0}Zip download{1}. Please note: We do not give any kind of support on the source code so please don't bug us about it. If it works, great, if not too bad. Use this software at your own risk!", "<a href='" . url_proxy('https://github.com/darkalchemy/Pu-239/archive/master.zip', false) . "'>", '</a>') . '</p>
                            </div>
                        </div>', 'top20');

$HTMLOUT .= main_div("
                        <h2 class='has-text-centered padtop10' id='answer_2'>" . _('User information') . "</h2>
                        <div id='answer_2_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user1'></a>
                                <p>" . _('I registered an account but did not receive the confirmation e-mail!') . '</p>
                                <p>' . _('You can contact site staff with your request on irc') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user2'></a>
                                <p>" . _("I've lost my user name or password! Can you send it to me?") . '</p>
                                <p>' . _fe('Please use {0}this form{1} to have the reset details emailed to you.', "<a href='{$site_config['paths']['baseurl']}/recover.php'>", '</a>') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user3'></a>
                                <p>" . _('Can you rename my account?') . '</p>
                                <p>' . _('We can rename accounts, please do not create new one. You can contact site staff with your request.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user4'></a>
                                <p>" . _('Can you delete my (confirmed) account?') . '</p>
                                <p>' . _('You can contact site staff with your request.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='userb'></a>
                                <p>" . _("So, what's MY ratio?") . '</p>
                                <p>' . _fe('Click on your {0}profile{1}, then on your user name (at the top).', "<a href='{$site_config['paths']['baseurl']}/usercp.php?action=default'>", '</a>') . '</p>
                                <p>' . _("It's important to distinguish between your overall ratio and the individual ratio on each torrent you may be seeding or leeching. The overall ratio takes into account the total uploaded and downloaded from your account since you joined the site. The individual ratio takes into account those values for each torrent.") . '</p>
                                <p>' . _("You may see two symbols instead of a number: 'Inf.', which is just an abbreviation for Infinity, and means that you have downloaded 0 bytes while uploading a non-zero amount (ul/dl becomes infinity); '---', which should be read as 'non-available', and shows up when you have both downloaded and uploaded 0 bytes (ul/dl = 0/0 which is an indeterminate amount).") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user5'></a>
                                <p>" . _('Why is my IP displayed on my details page?') . '</p>
                                <p>' . _('Only you and the site moderators can view your IP address and email. Regular users do not see that information.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user6'></a>
                                <p>" . _('Help! I cannot login!? (a.k.a. Login of Death)') . '</p>
                                <p>' . _('This problem sometimes occurs with MSIE. Close all Internet Explorer windows and open Internet Options in the control panel. Click the Delete Cookie button. You should now be able to login.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user7'></a>
                                <p>" . _('My IP address is dynamic. How do I stay logged in?') . '</p>
                                <p>' . _('You do not have to anymore. All you have to do is make sure you are logged in with your actual IP when starting a torrent session. After that, even if the IP changes mid-session, the seeding or leeching will continue and the statistics will update without any problem.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user8'></a>
                                <p>" . _('Why am I listed as not connectable? (And why should I care?)') . '</p>
                                <p>' . _('The tracker has determined that you are firewalled or NATed and cannot accept incoming connections.') . '</p>
                                <p>' . _('This means that other peers in the swarm will be unable to connect to you, only you to them. Even worse, if two peers are both in this state they will not be able to connect at all. This obviously has a detrimental effect on the overall speed.') . '</p>
                                <p>' . _fe('The way to solve the problem involves opening the ports used for incoming connections (the same range you defined in your client) on the firewall and/or configuring your NAT server to use a basic form of NAT for that range instead of NAPT (the actual process differs widely between different router models. Check your router documentation and/or support forum. You will also find lots of information on the subject at {0}PortForward{1}).', "<a href='" . url_proxy('https://portforward.com/') . "'>", '</a>') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='user9'></a>
                                <p>" . _('What are the different user classes?') . "</p>
                                <div class='table-wrapper'>
                                    <table class='table table-bordered table-striped top20'>
                                        <tbody>
                                            <tr>
                                                <td class='rowhead'><span class='user'>" . _('User') . "</span></td>
                                                <td class='rowhead'>" . _('The default class of new members.') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='power_user'>" . _('Power User') . "</span></td>
                                                <td class='rowhead'>" . _('Can view NFO files.') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='super_user'>" . _('Super User') . "</span></td>
                                                <td class='rowhead'>" . _('Same as PU, but has been around longer.') . "</td>
                                            </tr>
                                            <tr>
                                              <td class='rowhead'><img src='./images/star.png' alt='" . _('Star') . "'></td>
                                              <td class='rowhead'>" . _fe('Has donated money to {0}.', $site_name) . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='vip'>" . _('VIP') . "</span></td>
                                                <td class='rowhead'>" . _fe('Same privileges as Power User and is considered an Elite Member of {0}. Immune to automatic demotion.', $site_name) . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'>" . _('Other') . "</td>
                                                <td class='rowhead'>" . _('Customised title.') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='moderator'>" . _('Moderator') . "</span></td>
                                                <td class='rowhead'>" . _('Can edit and delete any uploaded torrents. Can also moderate user comments and disable accounts.') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='administrator'>" . _('Administrator') . "</span></td>
                                                <td class='rowhead'>" . _('Can do just about anything.') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='sysop'>" . _('SysOp') . "</span></td>
                                                <td class='rowhead'>" . _('Runs day to day matters on site') . "</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='usera'></a>
                                <p>" . _('How does this promotion thing work anyway?') . "</p>
                                <div class='table-wrapper'>
                                    <table class='table table-bordered table-striped top20'>
                                        <tbody>
                                            <tr>
                                                <td class='rowhead'><span class='power_user'>" . _('Power User') . "</span></td>
                                                <td class='rowhead'>
                                                    <p>" . _('Must have been be a member for at least 20 days, have uploaded at least 50GB and have a ratio at or above 1.20.') . '</p>
                                                    <p>' . _('The promotion is automatic when these conditions are met. Note that you will be automatically demoted from this status if your ratio drops below 0.85 at any time.') . "</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='super_user'>" . _('Super User') . "</span></td>
                                                <td class='rowhead'>
                                                    <p>" . _('Must have been be a member for at least 90 days, have uploaded at least 250GB and have a ratio at or above 1.35.') . '</p>
                                                    <p>' . _('The promotion is automatic when these conditions are met. Note that you will be automatically demoted from this status if your ratio drops below 1.20 at any time.') . "</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><img src='./images/star.png' alt='" . _('Star') . "'></td>
                                                <td class='rowhead'>" . _fe('Just donate, and send the {0}Staff{1} the details.', "<a href='{$site_config['paths']['baseurl']}/contactstaff.php'>", '</a>') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='vip'>" . _('VIP') . "</span></td>
                                                <td class='rowhead'>
                                                    <p>" . _('Assigned by mods at their discretion to users they feel contribute something special to the site.') . '</p>
                                                    <p>' . _('(Anyone begging for VIP status will be automatically disqualified.)') . "</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'>" . _('Other') . "</td>
                                                <td class='rowhead'>" . _('Conferred by mods at their discretion (not available to Users or Power Users).') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'><span class='moderator'>" . _('Moderator') . "</span></td>
                                                <td class='rowhead'>" . _("You don't ask us, we'll ask you!") . "</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class='top20 bg-02 padding20 round10'>
                                    <a id='usere'></a>
                                    <p>" . _("Hey! I've seen Power Users with less than 50B uploaded!") . '</p>
                                    <p>' . _("The PU limit used to be 25GB and we didn't demote anyone when we raised it to 50GB.") . "</p>
                                </div>
                                <div class='top20 bg-02 padding20 round10'>
                                    <a id='userc'></a>
                                    <p>" . _("Why can't my friend become a member?") . '</p>
                                    <p>' . _fe("There is a {0} users limit. When that number is reached we stop accepting new members. Accounts inactive for more than 42 days are automatically deleted, so keep trying. (There is no reservation or queuing system, don't ask for that.)", $site_config['site']['maxusers']) . "</p>
                                </div>
                                <div class='top20 bg-02 padding20 round10'>
                                    <a id='userd'></a>
                                    <p>" . _('How do I add an avatar to my profile?') . '</p>
                                    <p>' . _fe('First, find an image that you like, and that is within the {0}Rules{1}. Then you will have to find a place to host it, such as our own {2}BitBucket{3}. (Other popular choices are {4}Photobucket{5}, {6}Upload-It!{7} or {8}ImageShack{9}). All that is left to do is copy the URL you were given when uploading it to the avatar field in your {10}profile{11}', "<a href='{$site_config['paths']['baseurl']}/rules.php'>", '</a>', "<a href='{$site_config['paths']['baseurl']}/bitbucket.php'>", '</a>', "<a href='" . url_proxy('https://photobucket.com/', false) . "'>", '</a>', "<a href='" . url_proxy('https://uploadit.org/', false) . "'>", '</a>', "<a href='" . url_proxy('https://www.imageshack.us/', false) . "'>", '</a>', "<a href='{$site_config['paths']['baseurl']}/usercp.php?action=default'>", '</a>') . '</a>.</p>
                                    <p>' . _fe("Please do not make a post just to test your avatar. If everything is allright you'll see it in your profile {0}details page{1}.", "<a class='is-link' href='userdetails.php?id={$user['id']}'>", '</a>') . '</p>
                                </div>
                            </div>
                        </div>', 'top20');

$HTMLOUT .= main_div("
                        <h2 class='has-text-centered padtop10' id='answer_3'>" . _('Stats') . "</h2>
                        <div id='answer_3_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats1'></a>
                                <p>" . _('Most common reason for stats not updating') . "</p>
                                <ul class='disc left20'>
                                    <li class='padding10'>" . _("The user is cheating. (a.k.a. 'Summary Ban')") . "</li>
                                    <li class='padding10'>" . _('The server is overloaded and unresponsive. Just try to keep the session open until the server responds again. (Flooding the server with consecutive manual updates is not recommended.)') . "</li>
                                    <li class='padding10'>" . _('You are using a faulty client. If you want to use an experimental or CVS version you do it at your own risk.') . "</li>
                                </ul>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats2'></a>
                                <p>" . _('Best practices') . '</p>
                                <p>' . _('If a torrent you are currently leeching/seeding is not listed on your profile, just wait or force a manual update.') . '</p>
                                <p>' . _("Make sure you exit your client properly, so that the tracker receives 'event=completed'.") . '</p>
                                <p>' . _('If the tracker is down, do not stop seeding. As long as the tracker is back up before you exit the client the stats should update properly.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats3'></a>
                                <p>" . _('May I use any bittorrent client?') . '</p>
                                ' . _('Yes. The tracker now updates the stats correctly for all bittorrent clients. However, we still recommend that you avoid the following clients:') . "
                                <ul class='disc left20'>
                                    <li class='padding10'>
                                        " . _('BitTorrent++') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _('Nova Torrent') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _('TorrentStorm') . '
                                    </li>
                                </ul>
                                <p>' . _('These clients do not report correctly to the tracker when canceling/finishing a torrent session. If you use them then a few MB may not be counted towards the stats near the end, and torrents may still be listed in your profile for some time after you have closed the client.') . '</p>
                                <p>' . _('Also, clients in alpha or beta version should be avoided.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats4'></a>
                                <p>" . _("Why is a torrent I'm leeching/seeding listed several times in my profile?") . '</p>
                                <p>' . _("If for some reason (e.g. pc crash, or frozen client) your client exits improperly and you restart it, it will have a new peer_id, so it will show as a new torrent. The old one will never receive a 'event=completed' or 'event=stopped' and will be listed until some tracker timeout. Just ignore it, it will eventually go away.") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats5'></a>
                                <p>" . _("I've finished or cancelled a torrent. Why is it still listed in my profile?") . '</p>
                                <p>' . _('Some clients, notably TorrentStorm and Nova Torrent, do not report properly to the tracker when canceling or finishing a torrent. In that case the tracker will keep waiting for some message - and thus listing the torrent as seeding or leeching - until some timeout occurs. Just ignore it, it will eventually go away.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats6'></a>
                                <p>" . _("Why do I sometimes see torrents I'm not leeching in my profile!?") . '</p>
                                <p>' . _('When a torrent is first started, the tracker uses the IP to identify the user. Therefore the torrent will become associated with the user <i>who last accessed the site</i> from that IP. If you share your IP in some way (you are behind NAT/ICS, or using a proxy), and some of the persons you share it with are also users, you may occasionally see their torrents listed in your profile. (If they start a torrent session from that IP and you were the last one to visit the site the torrent will be associated with you). Note that now torrents listed in your profile will always count towards your total stats.') . '</p>
                                <p>' . _('To make sure your torrents show up in your profile you should visit the site immediately before starting a session.') . '</p>
                                <p>' . _('(The only way to completely stop foreign torrents from showing in profiles is to forbid users without an individual IP from accessing the site. Yes, that means you. Complain at your own risk.)') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats7'></a>
                                <p>" . _('Multiple IPs (Can I login from different computers?)') . '</p>
                                <p>' . _('Yes, the tracker is now capable of following sessions from different IPs for the same user. A torrent is associated with the user when it starts, and only at that moment is the IP relevant. So if you want to seed/leech from computer A and computer B with the same account you should access the site from computer A, start the torrent there, and then repeat both steps from computer B (not limited to two computers or to a single torrent on each, this is just the simplest example). You do not need to login again when closing the torrent.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats8'></a>
                                <p>" . _('How does NAT/ICS change the picture?') . '</p>
                                <p>' . _('This is a very particular case in that all computers in the LAN will appear to the outside world as having the same IP. We must distinguish between two cases:') . "</p>
                                <ol class='decimal left20'>
                                    <li class='padding10'>
                                        <p><i>" . _fe('You are the single {0} user in the LAN', $site_name) . '</i></p>
                                        <p>' . _fe('You should use the same {0} account in all the computers.', $site_name) . '</p>
                                        <p>' . _fe('Note also that in the ICS case it is preferable to run the BT client on the ICS gateway. Clients running on the other computers will be unconnectable (they will be listed as such, as explained elsewhere in the FAQ) unless you specify the appropriate services in your ICS configuration (a good explanation of how to do this for Windows XP can be found {0}here{1}). In the NAT case you should configure different ranges for clients on different computers and create appropriate NAT rules in the router. (Details vary widely from router to router and are outside the scope of this FAQ. Check your router documentation and/or support forum.)', "<a href='" . url_proxy('https://www.microsoft.com/downloads/details.aspx?FamilyID=1dcff3ce-f50f-4a34-ae67-cac31ccd7bc9&amp;displaylang=en', false) . "'>", '</a>') . "</p>
                                    </li>
                                    <li class='padding10'>
                                        <p><i>" . _fe('There are multiple {0} users in the LAN', $site_name) . '</i></p>
                                        <p>' . _fe("At present there is no way of making this setup always work properly with {0}. Each torrent will be associated with the user who last accessed the site from within the LAN before the torrent was started. Unless there is cooperation between the users mixing of statistics is possible. (User A accesses the site, downloads a .torrent file, but does not start the torrent immediately. Meanwhile, user B accesses the site. User A then starts the torrent. The torrent will count towards user B's statistics, not user A's.)", $site_name) . '</p>
                                        <p>' . _("It's your LAN, the responsibility is yours. Do not ask us to ban other users with the same IP, we will not do that. (Why should we ban <i>them</i> instead of <i>you</i>?)") . "</p>
                                    </li>
                                </ol>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='stats9'></a>
                                <p>" . _('For those of you who are interested...') . '</p>
                                <p>' . _fe("Some {0}info{1} about the 'Anatomy of a torrent session'.", "<a href='<a href='{$site_config['paths']['baseurl']}/anatomy.php'>", '</a>') . '</p>
                            </div>
                        </div>', 'top20');

$HTMLOUT .= main_div("
                        <h2 class='has-text-centered padtop10' id='answer_4'>" . _('Uploading') . "</h2>
                        <div id='answer_4_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='up1'></a>
                                <p>" . _("Why can't I upload torrents?") . '</p>
                                <p>' . _fe('Only specially authorized users ({0}Uploaders{1}) have permission to upload torrents.', "<span style='color:#4040C0;'>", '</span>') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='up2'></a>
                                <p>" . _fe('What criteria must I meet before I can join the {0}Uploader{1} team?', "<span style='color:#4040C0;'>", '</span>') . '</p>
                                <p>' . _('You must be able to provide releases that:') . "</p>
                                <ul class='disc left20'>
                                    <li class='padding10'>
                                        " . _('include a proper NFO') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _fe("are genuine scene releases. If it's not on {0}NFOrce{1} or {2}grokMusiQ{3} then forget it!", "<a href='" . url_proxy('https://www.nforce.nl', false) . "'>", '</a>', "<a href='" . url_proxy('https://www.grokmusiq.com/', false) . "'>", '</a>') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _('are not older than seven (7) days') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _('have all files in original format (usually 14.3 MB RARs)') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _("you'll be able to seed, or make sure are well-seeded, for at least 24 hours.") . '
                                    </li>
                                </ul>
                                <p>' . _('Also, you should have at least 2MBit upload bandwith.') . '</p>
                                <p>' . _fe('If you think you can match these criteria do not hesitate to {0}contact{1} one of the administrators.', "<a href='{$site_config['paths']['baseurl']}/staff.php'>", '</a>') . '</p>
                                <p>' . _("Remember! Write your application carefully! Be sure to include your UL speed and what kind of stuff you're planning to upload.") . '</p>
                                <p>' . _('Only well written letters with serious intent will be considered.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='up3'></a>
                                <p>" . _('Can I upload your torrents to other trackers?') . '</p>
                                <p>' . _fe('No, not the torrent file. We are a closed, limited-membership community. Only registered users can use the {0} tracker. Posting our torrents on other trackers is useless, since most people who attempt to download them will be unable to connect with us. This generates a lot of frustration and bad-will against us at {1}, and will therefore not be tolerated.', $site_name, $site_name) . '</p>
                                <p>' . _("Complaints from other sites' staff about our torrents being posted on their sites will result in the banning of the users responsible.") . '</p>
                                <p>' . _('(However, the files you download from us are yours to do as you please. You can always create another torrent, pointing to some other tracker, and upload it to the site of your choice.)') . '</p>
                            </div>
                        </div>', 'top20');

$next_para = "
                        <h2 class='has-text-centered padtop10' id='answer_5'>" . _('Downloading') . "</h2>
                        <div id='answer_5_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dl1'></a>
                                <p>" . _("How do I use the files I've downloaded?") . '</p>
                                <p>' . _('Almost any modern video file player will play these files.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dl2'></a>
                                <p>" . _("Downloaded a movie and don't know what CAM/TS/TC/SCR means?") . '</p>
                                <p>' . _('Google It.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dl3'></a>
                                <p>" . _('Why did an active torrent suddenly disappear?') . '</p>
                                <p>' . _('There may be three reasons for this:') . "</p>
                                <ol class='decimal left20'>
                                    <li class='padding10'>" . _fe('The torrent may have been out-of-sync with the site {0}rules{1}.', "<a href='{$site_config['paths']['baseurl']}/rules.php'>", '</a>') . "</li>
                                    <li class='padding10'>" . _('The uploader may have deleted it because it was a bad release. A replacement will probably be uploaded to take its place.') . "</li>
                                    <li class='padding10'>" . _('Torrents are automatically deleted after 28 days.') . "</li>
                                </ol>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dl4'></a>
                                <p>" . _('How do I resume a broken download or reseed something?') . '</p>
                                <p>' . _('Open the .torrent file. When your client asks you for a location, choose the location of the existing file(s) and it will resume/reseed the torrent.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dl5'></a>
                                <p>" . _('Why do my downloads sometimes stall at 99%?') . '</p>
                                <p>' . _('The more pieces you have, the harder it becomes to find peers who have pieces you are missing. That is why downloads sometimes slow down or even stall when there are just a few percent remaining. Just be patient and you will, sooner or later, get the remaining pieces.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dl6'></a>
                                <p>" . _("What are these 'a piece has failed an hash check' messages?") . '</p>
                                <p>' . _("Bittorrent clients check the data they receive for integrity. When a piece fails this check it is automatically re-downloaded. Occasional hash fails are a common occurrence, and you shouldn't worry.") . '</p>
                                <p>' . _("Some clients have an (advanced) option/preference to 'kick/ban clients that send you bad data' or similar. It should be turned on, since it makes sure that if a peer repeatedly sends you pieces that fail the hash check it will be ignored in the future.") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dl7'></a>
                                <p>" . _('The torrent is supposed to be 100MB. How come I downloaded 120MB?') . '</p>
                                <p>' . _("See the hash fails topic. If your client receives bad data it will have to redownload it, therefore the total downloaded may be larger than the torrent size. Make sure the 'kick/ban' option is turned on to minimize the extra downloads.") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dl8'></a>
                                <p>" . _("Why do I get a 'Not authorized (xx h) - READ THE FAQ!' error?") . '</p>
                                <p>' . _('From the time that each new torrent is uploaded to the tracker, there is a period of time that some users must wait before they can download it.') . '</p>
                                <p>' . _('This delay in downloading will only affect users with a low ratio, and users with low upload amounts.') . "</p>
                                <div class='bordered bg-02'>
                                    <div class='alt_bordered bg-00'>
                                        <div class='padding10'>
                                            " . _fe('Ratio below {0}0.50{1} and/or upload below 5.0GB then a delay of 48h.', "<span style='color:#bb0000;'>", '</span>') . "
                                        </div>
                                        <div class='padding10'>
                                            " . _fe('Ratio below {0}0.65{1} and/or upload below 6.5GB then a delay of 24h.', "<span style='color:#a10000;'>", '</span>') . "
                                        </div>
                                        <div class='padding10'>
                                            " . _fe('Ratio below {0}0.80{1} and/or upload below 8.0GB then a delay of 12h.', "<span style='color:#880000;'>", '</span>') . "
                                        </div>
                                        <div class='padding10'>
                                            " . _fe('Ratio below {0}0.95{1} and/or upload below 9.5GB then a delay of 6h.', "<span style='color:#6e0000;'>", '</span>') . "
                                        </div>
                                    </div>
                                </div>
                                <p class='top20'>" . _("'And/or' means any or both. Your delay will be the largest one for which you meet at least one condition.") . '</p>
                            </div>';

$byratio = 0;
$byul = 0;

/**
 * @param int  $up
 * @param int  $down
 * @param bool $color
 *
 * @return string
 */
function format_ratio(int $up, int $down, $color = true)
{
    if ($down > 0) {
        $r = $up / $down;
        if ($color) {
            $r = "<span style='color: " . get_ratio_color($r) . ";'>" . number_format($r, 3) . '</span>';
        }
    } elseif ($up > 0) {
        $r = "'INF'";
    } else {
        $r = "'---'";
    }

    return $r;
}

if ($user['class'] < UC_VIP) {
    $gigs = $user['uploaded'] / (1024 * 1024 * 1024);
    $ratio = (($user['downloaded'] > 0) ? ($user['uploaded'] / $user['downloaded']) : 0);
    if (($ratio > 0 && $ratio < 0.5) || $gigs < 5) {
        $wait = 48;
        if ($ratio > 0 && $ratio < 0.5) {
            $byratio = 1;
        }
        if ($gigs < 5) {
            $byul = 1;
        }
    } elseif (($ratio > 0 && $ratio < 0.65) || $gigs < 6.5) {
        $wait = 24;
        if ($ratio > 0 && $ratio < 0.65) {
            $byratio = 1;
        }
        if ($gigs < 6.5) {
            $byul = 1;
        }
    } elseif (($ratio > 0 && $ratio < 0.8) || $gigs < 8) {
        $wait = 12;
        if ($ratio > 0 && $ratio < 0.8) {
            $byratio = 1;
        }
        if ($gigs < 8) {
            $byul = 1;
        }
    } elseif (($ratio > 0 && $ratio < 0.95) || $gigs < 9.5) {
        $wait = 6;
        if ($ratio > 0 && $ratio < 0.95) {
            $byratio = 1;
        }
        if ($gigs < 9.5) {
            $byul = 1;
        }
    } else {
        $wait = 0;
    }
}
$next_para .= "<div class='top20 bg-02 padding20 round10'>" . _fe('In {0}your{1} particular case, ', "<a class='is-link' href='{$site_config['paths']['baseurl']}/userdetails.php?id={$user['id']}'>", '</a>');
if (isset($wait)) {
    $byboth = $byratio && $byul;
    if ($byboth) {
        $next_para .= _fe('both your ratio of {0} and your total uploaded of {1}GB implies a delay of {2} hours. Even if your total uploaded is {3}', format_ratio($user['uploaded'], $user['downloaded']), round($gigs, 2), $wait, format_ratio($user['uploaded'], $user['downloaded']));
    } elseif ($byratio) {
        $next_para .= _fe('your ratio of {0} implies a delay of {1} hours. Even if your ratio is {2}GB', format_ratio($user['uploaded'], $user['downloaded']), $wait, round($gigs, 2));
    } else {
        $next_para .= _fe('your total uploaded of {0}GB implies a delay of {1} hours. Even if your total uploaded in {2}GB', round($gigs, 2), $wait, round($gigs, 2));
    }
} else {
    $next_para .= _(' you will experience no delay.');
}

$next_para .= '</div></div>';
$HTMLOUT .= main_div($next_para, 'top20');

$HTMLOUT .= main_div("
                        <h2 class='has-text-centered padtop10' id='answer_6'>" . _('How can I improve my download speed?') . "</h2>
                        <div id='answer_6_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <p>" . _('The download speed mostly depends on the seeder-to-leecher ratio (SLR). Poor download speed is mainly a problem with new and very popular torrents where the SLR is low.') . '</p>
                                <p>' . _('(Proselytising sidenote: make sure you remember that you did not enjoy the low speed. Seed so that others will not endure the same.)') . '</p>
                                <p>' . _('There are a couple of things that you can try on your end to improve your speed:') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dlsp1'></a>
                                <p>" . _('Do not immediately jump on new torrents') . '</p>
                                <p>' . _("In particular, do not do it if you have a slow connection. The best speeds will be found around the half-life of a torrent, when the SLR will be at its highest. (The downside is that you will not be able to seed so much. It's up to you to balance the pros and cons of this.)") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dlsp2'></a>
                                <p>" . _('Make yourself connectable') . '</p>
                                <p>' . _fe('See the <i>{0}Why am I listed as not connectable?{1}</i> section.', "<a href='#user8'>", '</a>') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dlsp3'></a>
                                <p>" . _('Limit your upload speed') . '</p>
                                ' . _('The upload speed affects the download speed in essentially two ways:') . "
                                <ul class='disc left20'>
                                    <li class='padding10'>
                                        " . _('Bittorrent peers tend to favour those other peers that upload to them. This means that if A and B are leeching the same torrent and A is sending data to B at high speed then B will try to reciprocate. So due to this effect high upload speeds lead to high download speeds.') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _("Due to the way TCP works, when A is downloading something from B it has to keep telling B that it received the data sent to him. (These are called acknowledgements - ACKs -, a sort of 'got it!' messages). If A fails to do this then B will stop sending data and wait. If A is uploading at full speed there may be no bandwidth left for the ACKs and they will be delayed. So due to this effect excessively high upload speeds lead to low download speeds.") . '
                                    </li>
                                </ul>
                                <p>' . _('The full effect is a combination of the two. The upload should be kept as high as possible while allowing the ACKs to get through without delay. A good thumb rule is keeping the upload at about 80 percent of the theoretical upload speed. You will have to fine tune yours to find out what works best for you. (Remember that keeping the upload high has the additional benefit of helping with your ratio.)') . '</p>
                                <p>' . _("If you are running more than one instance of a client it is the overall upload speed that you must take into account. Some clients (e.g. Azureus) limit global upload speed, others (e.g. Shad0w's) do it on a per torrent basis. Know your client. The same applies if you are using your connection for anything else (e.g. browsing or ftp), always think of the overall upload speed.") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dlsp4'></a>
                                <p>" . _('Limit the number of simultaneous connections') . '</p>
                                <p>' . _('Some operating systems (like Windows 9x) do not deal well with a large number of connections, and may even crash. Also some home routers (particularly when running NAT and/or firewall with stateful inspection services) tend to become slow or crash when having to deal with too many connections. There are no fixed values for this, you may try 60 or 100 and experiment with the value. Note that these numbers are additive, if you have two instances of a client running the numbers add up.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dlsp5'></a>
                                <p>" . _('Limit the number of simultaneous uploads') . '</p>
                                <p>' . _("Isn't this the same as above? No. Connections limit the number of peers your client is talking to and/or downloading from. Uploads limit the number of peers your client is actually uploading to. The ideal number is typically much lower than the number of connections, and highly dependent on your (physical) connection.") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dlsp6'></a>
                                <p>" . _('Just give it some time') . '</p>
                                <p>' . _('As explained above peers favour other peers that upload to them. When you start leeching a new torrent you have nothing to offer to other peers and they will tend to ignore you. This makes the starts slow, in particular if, by change, the peers you are connected to include few or no seeders. The download speed should increase as soon as you have some pieces to share.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='dlsp7'></a>
                                <p>" . _('Why is my browsing so slow while leeching?') . '</p>
                                <p>' . _fe('Your download speed is always finite. If you are a peer in a fast torrent it will almost certainly saturate your download bandwidth, and your browsing will suffer. At the moment there is no client that allows you to limit the download speed, only the upload. You will have to use a third-party solution, such as {0}NetLimiter{1}', "<a href='" . url_proxy('https://www.netlimiter.com/', false) . "'>", '</a>') . '.</p>
                                <p>' . _('Browsing was used just as an example, the same would apply to gaming, IMing, etc...') . '</p>
                            </div>
                        </div>', 'top20');

$HTMLOUT .= main_div("
                        <h2 class='has-text-centered padtop10' id='answer_7'>" . _('My ISP uses a transparent proxy. What should I do?') . "</h2>
                        <div id='answer_7_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <p><i>" . _('Caveat: This is a large and complex topic. It is not possible to cover all variations here.') . '</i></p>
                                <span>' . _('Short reply: change to an ISP that does not force a proxy upon you. If you cannot or do not want to then read on.') . "</span>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='prox1'></a>
                                <p>" . _('What is a proxy?') . '</p>
                                <p>' . _('Basically a middleman. When you are browsing a site through a proxy your requests are sent to the proxy and the proxy forwards them to the site instead of you connecting directly to the site. There are several classifications (the terminology is far from standard):') . "</p>
                                <div class='table-wrapper'>
                                    <table class='table table-bordered table-striped bottom20'>
                                        <tbody>
                                            <tr>
                                                <td class='rowhead'>" . _('Transparent') . "</td>
                                                <td class='rowhead'>" . _('A transparent proxy is one that needs no configuration on the clients. It works by automatically redirecting all port 80 traffic to the proxy. (Sometimes used as synonymous for non-anonymous.)') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'>" . _('Explicit/Voluntary') . "</td>
                                                <td class='rowhead'>" . _('Clients must configure their browsers to use them.') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'>" . _('Anonymous') . "</td>
                                                <td class='rowhead'>" . _('The proxy sends no client identification to the server. (HTTP_X_FORWARDED_FOR header is not sent; the server does not see your IP.)') . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'>" . _('Highly Anonymous') . "</td>
                                                <td class='rowhead'>" . _("The proxy sends no client nor proxy identification to the server. (HTTP_X_FORWARDED_FOR, HTTP_VIA and HTTP_PROXY_CONNECTION headers are not sent; the server doesn't see your IP and doesn't even know you're using a proxy.)") . "</td>
                                            </tr>
                                            <tr>
                                                <td class='rowhead'>" . _('Public') . "</td>
                                                <td class='rowhead'>" . _('(Self explanatory)') . '</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p>' . _('A transparent proxy may or may not be anonymous, and there are several levels of anonymity.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='prox2'></a>
                                <p>" . _("How do I find out if I'm behind a (transparent/anonymous) proxy?") . '</p>
                                <p>' . _fe('Try {0}ProxyJudge{1}. It lists the HTTP headers that the server where it is running received from you. The relevant ones are HTTP_CLIENT_IP, HTTP_X_FORWARDED_FOR and REMOTE_ADDR.', "<a href='" . url_proxy('https://proxyjudge.org', false) . "'>", '</a>') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='prox3'></a>
                                <p>" . _("Why am I listed as not connectable even though I'm not NAT/Firewalled?") . '</p>
                                <p>' . _fe("The {0} tracker is quite smart at finding your real IP, but it does need the proxy to send the HTTP header HTTP_X_FORWARDED_FOR. If your ISP's proxy does not then what happens is that the tracker will interpret the proxy's IP address as the client's IP address. So when you login and the tracker tries to connect to your client to see if you are NAT/firewalled it will actually try to connect to the proxy on the port your client reports to be using for incoming connections. Naturally the proxy will not be listening on that port, the connection will fail and the tracker will think you are NAT/firewalled.", $site_name) . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='prox4'></a>
                                <p>" . _("Can I bypass my ISP's proxy?") . '</p>
                                <p>' . _fe('If your ISP only allows HTTP traffic through port 80 or blocks the usual proxy ports then you would need to use something like {0}socks{1} and that is outside the scope of this FAQ.', "<a href='" . url_proxy('https://www.socks.permeo.com', false) . "'>", '</a') . '</p>
                                <p>' . _fe('The site accepts connections on port 81 besides the usual 80, and using them may be enough to fool some proxies. So the first thing to try should be connecting to {0}. Note that even if this works your bt client will still try to connect to port 80 unless you edit the announce url in the .torrent file.', "{$site_config['paths']['baseurl']}:81") . '</p>
                                <p>' . _('Otherwise you may try the following:') . "</p>
                                <ul class='disc left20'>
                                    <li class='padding10'>
                                        " . _fe('Choose any public non-anonymous proxy that does not use port 80 (e.g. from {0}this{1}, {2}this{3} or {4}this{5}</a> list).', "<a href='" . url_proxy('https://tools.rosinstrument.com/proxy', false) . "'>", '</a>', "<a href='" . url_proxy('https://www.proxy4free.com/index.html', false) . "'>", '</a>', "<a href='" . url_proxy('https://www.samair.ru/proxy', false) . "'>", '</a>') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _('Configure your computer to use that proxy. For Windows XP, do <i>Start</i>, <i>Control Panel</i>, <i>Internet Options</i>, <i>Connections</i>, <i>LAN Settings</i>, <i>Use a Proxy server</i>, <i>Advanced</i> and type in the IP and port of your chosen proxy. Or from Internet Explorer use <i>Tools</i>, <i>Internet Options</i>, ...') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _fe('(Facultative) Visit {0}ProxyJudge{1}. If you see an HTTP_X_FORWARDED_FOR in the list followed by your IP then everything should be ok, otherwise choose another proxy and try again.', "<a href='" . url_proxy('https://proxyjudge.org', false) . "'>", '</a>') . "
                                    </li>
                                    <li class='padding10'>
                                        " . _fe('Visit {0}. Hopefully the tracker will now pickup your real IP (check your profile to make sure).', $site_name) . '
                                    </li>
                                </ul>
                                <p>' . _("Notice that now you will be doing all your browsing through a public proxy, which are typically quite slow. Communications between peers do not use port 80 so their speed will not be affected by this, and should be better than when you were 'unconnectable'.") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='prox5'></a>
                                <p>" . _('How do I make my bittorrent client use a proxy?') . '</p>
                                <p>' . _("Just configure Windows XP as above. When you configure a proxy for Internet Explorer you're actually configuring a proxy for all HTTP traffic (thank Microsoft and their 'IE as part of the OS policy' ). On the other hand if you use another browser (Opera/Mozilla/Firefox) and configure a proxy there you'll be configuring a proxy just for that browser. We don't know of any BT client that allows a proxy to be specified explicitly.") . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='prox6'></a>
                                <p>" . _("Why can't I signup from behind a proxy?") . '</p>
                                <p>' . _('It <i>is</i> our policy to allow new accounts to be opened from behind a proxy.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='prox7'></a>
                                <p>" . _('Does this apply to other torrent sites?') . '</p>
                                <p>' . _fe('This section was written for {0}, a closed, port 80-81 tracker. Other trackers may be open or closed, and many listen on e.g. ports 6868 or 6969. The above does not necessarily apply to other trackers.', $site_name) . '</p>
                            </div>
                        </div>', 'top20');

$HTMLOUT .= main_div("
                        <h2 class='has-text-centered padtop10' id='answer_8'>" . _("Why can't I connect? Is the site blocking me?") . "</h2>
                        <div id='answer_8_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='conn1'></a>
                                <p>" . _('Your failure to connect may be due to several reasons.') . '</p>
                                <p>' . _('Maybe my address is blacklisted?') . "
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='conn2'></a>
                                <p>" . _fe("The site blocks addresses listed in the (former) {0}PeerGuardian{1} database, as well as addresses of banned users. This works at Apache/PHP level, it's just a script that blocks <i>logins</i> from those addresses. It should not stop you from reaching the site. In particular it does not block lower level protocols, you should be able to ping/traceroute the server even if your address is blacklisted. If you cannot then the reason for the problem lies elsewhere.", "<a href='" . url_proxy('https://methlabs.org/', false) . "'>", '</a>') . '</p>
                                <p>' . _('If somehow your address is indeed blocked in the PG database do not contact us about it, it is not our policy to open <i>ad hoc</i> exceptions. You should clear your IP with the database maintainers instead.') . "</p>
                            </div>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='conn3'></a>
                                <p>" . _("Your ISP blocks the site's address") . '</p>
                                <p>' . _("(In first place, it's unlikely your ISP is doing so. DNS name resolution and/or network problems are the usual culprits.)") . '</p>
                                <p>' . _("There's nothing we can do. You should contact your ISP (or get a new one). Note that you can still visit the site via a proxy, follow the instructions in the relevant section. In this case it doesn't matter if the proxy is anonymous or not, or which port it listens to.") . '</p>
                                <p>' . _("Notice that you will always be listed as an 'unconnectable' client because the tracker will be unable to check that you're capable of accepting incoming connections.") . "</p>
                            </div>
                            <h2 class='has-text-centered'>" . _('Alternate port (81)') . "</h2>
                            <div class='top20 bg-02 padding20 round10'>
                                <a id='conn4'></a>
                                <p>" . _fe('Some of our torrents use ports other than the usual HTTP port 80. This may cause problems for some users, for instance those behind some firewall or proxy configurations. You can easily solve this by editing the .torrent file yourself with any torrent editor, e.g. {0}MakeTorrent{1}, and replacing the announce url {2} with {3} or just {4}.', "<a href='" . url_proxy('https://sourceforge.net/projects/burst/', false) . "'>", '</a>', $site_config['paths']['baseurl'] . ':81', $site_config['paths']['baseurl'] . ':80', $site_config['paths']['baseurl']) . '</p>
                                <p>' . _fe("Editing the .torrent with Notepad is not recommended. It may look like a text file, but it is in fact a bencoded file. If for some reason you must use a plain text editor, change the announce url to {0}, not {1}. (If you're thinking about changing the number before the announce url instead, you know too much to be reading this.)", $site_config['paths']['baseurl'] . ':80', $site_config['paths']['baseurl']) . '</p>
                            </div>
                        </div>', 'top20');

$HTMLOUT .= main_div("
                        <h2 class='has-text-centered padtop10' id='answer_9'>" . _("What if I can't find the answer to my problem here?") . "</h2>
                        <div id='answer_9_text'>
                            <div class='top20 bg-02 padding20 round10'>
                                <p>" . _fe("Post in the {0}Forums{1}, by all means. You'll find they are usually a friendly and helpful place, provided you follow a few basic guidelines:", "<a href='{$site_config['paths']['baseurl']}/forums.php'>", '</a>') . "</p>
                                <ul class='disc left20'>
                                    <li class='padding10'>
                                        " . _("Make sure your problem is not really in this FAQ. There's no point in posting just to be sent back here.") . "
                                    </li>
                                    <li class='padding10'>
                                        " . _("Before posting read the sticky topics (the ones at the top). Many times new information that still hasn't been incorporated in the FAQ can be found there.") . "
                                    </li>
                                    <li class='padding10'>
                                        " . _("Help us in helping you. Do not just say 'it doesn't work!'. Provide details so that we don't have to guess or waste time asking. What client do you use? What's your OS? What's your network setup? What's the exact error message you get, if any? What are the torrents you are having problems with? The more you tell the easiest it will be for us, and the more probable your post will get a reply.") . "
                                    </li>
                                    <li class='padding10'>
                                        " . _('And needless to say: be polite. Demanding help rarely works, asking for it usually does the trick.') . '
                                    </li>
                                </ul>
                            </div>
                        </div>', 'top20');

$title = _('FAQ');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
