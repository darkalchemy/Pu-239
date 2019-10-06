<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Roles;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
global $container, $site_config;

$auth = $container->get(Auth::class);
if (!$auth->isLoggedIn()) {
    get_template();
    $user['class'] = 0;
} else {
    $user = check_user_status();
}
$HTMLOUT = "
        <fieldset id='rules'>
            <legend class='level-center-center padding20 size_7'>
                <img src='{$site_config['paths']['images_baseurl']}info.png' alt='' class='tooltipper right5' title='" . _('Guidelines') . "' width='25'>" . _('Guidelines') . '
            </legend>';

$main_div = "
                    <div id='accordion'>
                        <p class='accordion-toggle has-text-black round5-top'>
                            " . _('General rules') . " - <span class='is-blue'>" . _('Breaking these rules can and will get you banned!') . "</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul class='left20 disc'>
                                <li>" . _('Do not defy the moderators expressed wishes!') . '</li>
                                <li>' . _fe('Do not upload our torrents to other trackers! (See the {0}FAQ{1} for details.)', "<a href='http://Pu239.silly/faq.php#up3' class='is-link'>") . "</li>
                                <li><a id='warning'></a>" . _fe("Disruptive behaviour in the forums or on the site will result in a warning ({0}).<br>You will only get <b>one</b> warning! After that it's bye bye Kansas!", "<img src='./images/warned.gif' alt=''>") . "</li>
                            </ul>
                        </div>
                        <p class='accordion-toggle has-text-black'>
                            " . _('Downloading rules -') . "<span class='is-blue'>" . _(' By not following these rules you will lose download privileges!') . "</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul class='left20 disc'>
                                <li>" . _fe('Access to the newest torrents is conditional on a good ratio! (See the {0}FAQ{1} for details.', "<a href='http://Pu239.silly/faq.php#dl8' class='is-link'>", '</a>') . '</li>
                                <li>' . _('Low ratios may result in severe consequences, including banning in extreme cases.') . "</li>
                            </ul>
                        </div>
                        <p class='accordion-toggle has-text-black'>
                            " . _('General Forum Guidelines -') . "<span class='is-blue'>" . _(' Please follow these guidelines or else you might end up with a warning!') . "</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul class='left20 disc'>
                                <li>" . _('No aggressive behaviour or flaming in the forums.') . '</li>
                                <li>' . _('No trashing of other peoples topics (i.e. SPAM).') . '</li>
                                <li>' . _('No language other than English in the forums.') . '</li>
                                <li>' . _('No systematic foul language (and none at all on  titles).') . '</li>
                                <li>' . _('No links to warez or crack sites in the forums.') . '</li>
                                <li>' . _('No requesting or posting of serials, CD keys, passwords or cracks in the forums.') . '</li>
                                <li>' . _fe('No requesting if there has been no {0}scene{1} release in the last 7 days.', "<a href='" . url_proxy('https://www.nforce.nl/', false) . "' target='_blank'>", '</a>') . '</li>
                                <li>' . _('No bumping... (All bumped threads will be deleted.)') . '</li>
                                <li>' . _('No images larger than 800x600, and preferably web-optimised.') . '</li>
                                <li>' . _('No double posting. If you wish to post again, and yours is the last post in the thread please use the EDIT function, instead of posting a double.') . '</li>
                                <li>' . _('Please ensure all questions are posted in the correct section!<br>(Game questions in the Games section, Apps questions in the Apps section, etc.)') . '</li>
                                <li>' . _fe('Last, please read the {0}FAQ{1} before asking any questions!', "<a href='http://Pu239.silly/faq.php' class='is-link'>", '</a>') . "</li>
                            </ul>
                        </div>
                        <p class='accordion-toggle has-text-black" . (!$auth->hasRole(Roles::UPLOADER) ? ' round5-bottom' : '') . "'>
                            " . _('Avatar Guidelines -') . "<span class='is-blue'>" . _(' Please try to follow these guidelines') . "</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul class='left20 disc'>
                                <li>" . _('The allowed formats are .gif, .jpg and .png.') . '</li>
                                <li>' . _('Be considerate. Resize your images to a width of 150 px and a size of no more than 150 KB. (Browsers will rescale them anyway: smaller images will be expanded and will not look good; larger images will just waste bandwidth and CPU cycles.) For now this is just a guideline but it will be automatically enforced in the near future.') . '</li>
                                <li>' . _('Do not use potentially offensive material involving porn, religious material, animal / human cruelty or ideologically charged images. Mods have wide discretion on what is acceptable. If in doubt PM one.') . '</li>
                            </ul>
                        </div>';

if ($auth->hasRole(Roles::UPLOADER)) {
    $main_div .= "
                        <p class='accordion-toggle has-text-black" . ($user['class'] < UC_STAFF ? ' round5-bottom' : '') . "'>
                            " . _('Uploading rules -') . "<span class='is-blue'>" . _(' Torrents violating these rules may be deleted without notice') . "</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul class='left20 disc'>
                                <li>" . _('All uploads must include a proper NFO.') . '</li>
                                <li>' . _fe("Only scene releases. If it's not on {0}NFOrce{1} or {2}grokMusiQ{3} then forget it!", "<a href='" . url_proxy('https://www.nforce.nl', false) . "' target='_blank' class='is-link'>", '</a>', "<a href='" . url_proxy('https://www.grokmusiq.com/', false) . "' target='_blank' class='is-link'>", '</a>') . '</li>
                                <li>' . _('The stuff must not be older than seven (7) days.') . '</li>
                                <li>' . _('All files must be in original format (usually 14.3 MB RARs).') . '</li>
                                <li>' . _('Pre-release stuff should be labeled with an *ALPHA* or *BETA* tag.') . '</li>
                                <li>' . _('Make sure not to include any serial numbers, CD keys or similar in the description (you do <b>not</b> need to edit the NFO!).') . '</li>
                                <li>' . _('Make sure your torrents are well-seeded for at least 24 hours.') . '</li>
                                <li>' . _('Do not include the release date in the torrent name.') . '</li>
                                <li>' . _('Stay active! You risk being demoted if you have no active torrents.') . '</li>
                                <li>' . _('If you have something interesting that somehow violate these rules (e.g. not ISO format), ask a mod and we might make an exception.') . '</li>
                            </ul>
                        </div>';
}
if ($user['class'] >= UC_STAFF) {
    $main_div .= "
                        <p class='accordion-toggle has-text-black'>
                            " . _('Moderating rules -') . "<span class='is-blue'>" . _(' Whom to promote and why') . "</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <table class='table table-bordered table-striped'>
                                <tr>
                                    <td class='w-15'>
                                        <span class='power_user'>" . _('Power User') . '</span>
                                    </td>
                                    <td>' . _('Automatically given to (and revoked from) users who have been members for at least 20 days, have uploaded at least 50 GB and have a share ratio above 1.25. Moderator changes of status last only until the next execution of the script.') . "</td>
                                </tr>
                                <tr>
                                    <td class='w-15'>
                                        <span class='super_user'>" . _('Super User') . '</span>
                                    </td>
                                    <td>' . _('Automatically given to (and revoked from) users who have been members for at least 90 days, have uploaded at least 250 GB and have a share ratio above 1.35. Moderator changes of status last only until the next execution of the script.') . "</td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src='{$site_config['paths']['images_baseurl']}star.png' alt='Donor' class='tooltipper' title='Donor'>
                                    </td>
                                    <td>" . _('This status is given ONLY by the Sysops since only they can verify that they actually donated something.') . "</td>
                                </tr>
                                <tr>
                                    <td><span class='vip'>" . _('VIP') . '</span></td>
                                    <td>' . _('Conferred to users you feel contribute something special to the site. (Anyone begging for VIP status will be automatically disqualified)') . '</td>
                                </tr>
                                <tr>
                                    <td>' . _('Other') . '</td>
                                    <td>' . _('Customised title given to special users only (Not available to Users or Power Users).') . "</td>
                                </tr>
                                <tr>
                                    <td><span class='moderator'>" . _('Moderator') . '</span></td>
                                    <td>' . _fe("Appointed by Sysop only. If you think you've got a good candidate, send him a {0}PM{1}", "<a class='is-link' href='http://Pu239.silly/messages.php?action=send_message&amp;receiver=1'>", '</a>') . "</td>
                                </tr>
                            </table>
                        </div>
                        <p class='accordion-toggle has-text-black'>
                            " . _('Moderating Rules -') . "<span class='is-blue'> " . _('Use your better judgement!') . "</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul class='left20 disc'>
                                <li>" . _('The most important rule: Use your better judgment!') . '</li>
                                <li>' . _("Don't be afraid to say <b>NO</b>! (a.k.a. 'Helshad's rule'.)") . '</li>
                                <li>' . _("Don't defy another mod in public, instead send a PM or through IM.") . '</li>
                                <li>' . _('Be tolerant! Give the user(s) a chance to reform.') . '</li>
                                <li>' . _("Don't act prematurely, let the users make their mistakes and THEN correct them.") . '</li>
                                <li>' . _("Try correcting any 'off topics' rather then closing a thread.") . '</li>
                                <li>' . _('Move topics rather than locking them.') . '</li>
                                <li>' . _('Be tolerant when moderating the Chit-chat section (give them some slack).') . '</li>
                                <li>' . _("If you lock a topic, give a brief explanation as to why you're locking it.") . '</li>
                                <li>' . _('Before you disable a user account, send him/her a PM and if they reply, put them on a 2 week trial.') . '</li>
                                <li>' . _("Don't disable a user account until he or she has been a member for at least 4 weeks.") . '</li>
                                <li>' . _('<b>Always</b> state a reason (in the user comment box) as to why the user is being banned / warned.') . "</li>
                            </ul>
                        </div>
                        <p class='accordion-toggle has-text-black round5-bottom'>
                            " . _('Moderating options -') . "<span class='is-blue'> " . _('What are my privileges as a mod?') . "</span>
                        </p>
                        <div class='accordion-content padding20'>
                            <ul class='left20 disc'>
                                <li>" . _('You can delete and edit forum posts.') . '</li>
                                <li>' . _('You can delete and edit torrents.') . '</li>
                                <li>' . _('You can delete and change users avatars.') . '</li>
                                <li>' . _('You can disable user accounts.') . '</li>
                                <li>' . _("You can edit the title of VIP's.") . '</li>
                                <li>' . _('You can see the complete info of all users.') . '</li>
                                <li>' . _('You can add comments to users (for other mods and admins to read).') . '</li>
                                <li>' . _('You can stop reading now because you already knew about these options. ;)') . '</li>
                                <li>' . _fe('Lastly, check out the {0}Staff{1} page.', "<a href='http://Pu239.silly/staff.php' class='is-link'>", '</a>') . '</li>
                            </ul>
                        </div>';
}

$main_div .= '
                    </div>';
$HTMLOUT .= main_div($main_div) . '
    </fieldset>';

$title = _('Rules');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
