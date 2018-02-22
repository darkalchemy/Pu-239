<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config;

$session = new DarkAlchemy\Pu239\Session();
$lang = load_language('global');
$nick = ($CURUSER ? $CURUSER['username'] : ('Guest' . random_int(1000, 9999)));
$form_template = <<<PAYPAL
<form action='https://www.{$site_config['paypal_config']['sandbox']}paypal.com/cgi-bin/webscr' method='post'>
<input type='hidden' name='business' value='{$site_config['paypal_config']['email']}' />
<input type='hidden' name='cmd' value='_xclick' />
<input type='hidden' name='amount' value='#amount' />
<input type='hidden' name='item_name' value='#item_name' />
<input type='hidden' name='item_number' value='#item_number' />
<input type='hidden' name='currency_code' value='{$site_config['paypal_config']['currency']}' />
<input type='hidden' name='no_shipping' value='1' />
<input type='hidden' name='notify_url' value='{$site_config['baseurl']}/donatecheck.php' />
<input type='hidden' name='rm' value='2' />
<input type='hidden' name='custom' value='#id' />
<input type='hidden' name='return' value='{$site_config['baseurl']}/donate.php?done=1' />
<input type='submit' class='button is-small' value='Donate $#amount {$site_config['paypal_config']['currency']}' />
</form>
PAYPAL;
//this shows what they get
$donate = [
    $site_config['paypal_config']['gb_donated_1'] => [
        'ViP ' . $site_config['paypal_config']['vip_dur_1'] . ' week',
        'Donor ' . $site_config['paypal_config']['donor_dur_1'] . ' week',
        'Freeleech ' . $site_config['paypal_config']['free_dur_1'] . ' wk',
        '' . $site_config['paypal_config']['up_amt_1'] . 'G upload',
        '' . $site_config['paypal_config']['kp_amt_1'] . ' bonus points',
        '' . $site_config['paypal_config']['inv_amt_1'] . ' invite',
        'Donor star ' . $site_config['paypal_config']['duntil_dur_1'] . ' week',
        'Imunnity ' . $site_config['paypal_config']['imm_dur_1'] . ' week',
    ],
    $site_config['paypal_config']['gb_donated_2'] => [
        'ViP ' . $site_config['paypal_config']['vip_dur_2'] . ' weeks',
        'Donor ' . $site_config['paypal_config']['donor_dur_2'] . ' weeks',
        'Freeleech ' . $site_config['paypal_config']['free_dur_2'] . ' wks',
        '' . $site_config['paypal_config']['up_amt_2'] . 'G upload',
        '' . $site_config['paypal_config']['kp_amt_2'] . ' bonus points',
        '' . $site_config['paypal_config']['inv_amt_2'] . ' invites',
        'Donor star ' . $site_config['paypal_config']['duntil_dur_2'] . ' weeks',
        'Imunnity ' . $site_config['paypal_config']['imm_dur_2'] . ' weeks',
    ],
    $site_config['paypal_config']['gb_donated_3'] => [
        'ViP ' . $site_config['paypal_config']['vip_dur_3'] . ' weeks',
        'Donor ' . $site_config['paypal_config']['donor_dur_3'] . ' weeks',
        'Freeleech ' . $site_config['paypal_config']['free_dur_3'] . ' wks',
        '' . $site_config['paypal_config']['up_amt_3'] . 'G upload',
        '' . $site_config['paypal_config']['kp_amt_3'] . ' bonus points',
        '' . $site_config['paypal_config']['inv_amt_3'] . ' invites',
        'Donor star ' . $site_config['paypal_config']['duntil_dur_3'] . ' weeks',
        'Imunnity ' . $site_config['paypal_config']['imm_dur_3'] . ' weeks',
    ],
    $site_config['paypal_config']['gb_donated_4'] => [
        'ViP ' . $site_config['paypal_config']['vip_dur_4'] . ' weeks',
        'Donor ' . $site_config['paypal_config']['donor_dur_4'] . ' weeks',
        'Freeleech ' . $site_config['paypal_config']['free_dur_4'] . ' wks',
        '' . $site_config['paypal_config']['up_amt_4'] . 'G upload',
        '' . $site_config['paypal_config']['kp_amt_4'] . ' bonus points',
        '' . $site_config['paypal_config']['inv_amt_4'] . ' invites',
        'Donor star ' . $site_config['paypal_config']['duntil_dur_4'] . ' weeks',
        'Imunnity ' . $site_config['paypal_config']['imm_dur_4'] . ' weeks',
    ],
    $site_config['paypal_config']['gb_donated_5'] => [
        'ViP ' . $site_config['paypal_config']['vip_dur_5'] . ' weeks',
        'Donor ' . $site_config['paypal_config']['donor_dur_5'] . ' weeks',
        'Freeleech ' . $site_config['paypal_config']['free_dur_5'] . ' wks',
        '' . $site_config['paypal_config']['up_amt_5'] . 'G upload',
        '' . $site_config['paypal_config']['kp_amt_5'] . ' bonus points',
        '' . $site_config['paypal_config']['inv_amt_5'] . ' invites',
        'Donor star ' . $site_config['paypal_config']['duntil_dur_5'] . ' weeks',
        'Imunnity ' . $site_config['paypal_config']['imm_dur_5'] . ' weeks',
    ],
    $site_config['paypal_config']['gb_donated_6'] => [
        'ViP ' . $site_config['paypal_config']['vip_dur_6'] . ' weeks',
        'Donor ' . $site_config['paypal_config']['donor_dur_6'] . ' weeks',
        'Freeleech ' . $site_config['paypal_config']['free_dur_6'] . ' wks',
        '' . $site_config['paypal_config']['up_amt_6'] . 'G upload',
        '' . $site_config['paypal_config']['kp_amt_6'] . ' bonus points',
        '' . $site_config['paypal_config']['inv_amt_6'] . ' invites',
        'Donor star ' . $site_config['paypal_config']['duntil_dur_6'] . ' weeks',
        'Imunnity ' . $site_config['paypal_config']['imm_dur_6'] . ' weeks',
    ],
];
$done = isset($_GET['done']) && $_GET['done'] == 1 ? true : false;
if ($site_config['paypal_config']['enable'] == 0) {
    $out = stdmsg('Sorry', 'Donation system is currently offline.');
} else {
    $out = '';
    if ($done) {
        $session->set('is-success', 'Your donations was sent to paypal wait for processing, this should be immediately! If any errors appear youll be contacted by someone from staff');
    }
    $out .= '
            <h1>Donate</h1>
            <div class="level">';
    foreach ($donate as $amount => $ops) {
        $out .= '
            <div class="w-15">';
        $header = '
                <tr>
                    <th class="has-text-centered">Donate $' . $amount . ' ' . $site_config['paypal_config']['currency'] . '</th>
                </tr>';
        $body = '
                <tr>
                    <td>
                        <ul>';
        foreach ($ops as $op) {
            $body .= '
                            <li>' . $op . '</li>';
        }
        $body .= '
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td class="has-text-centered">' . str_replace([
                                                                                       '#amount',
                                                                                       '#item_name',
                                                                                       '#item_number',
                                                                                       '#id',
                                                                                   ], [
                                                                                       $amount,
                                                                                       $nick,
                                                                                       $amount,
                                                                                       $CURUSER['id'],
                                                                                   ], $form_template);
        $body .= '
                    </td>
                </tr>';
        $out .= main_table($body, $header);
        $out .= '        
            </div>';
    }
    $out .= '</div>' . stdmsg('Note', '<p>If you want to say something to ' . $site_config['site_name'] . ' staff, click on <b>Add special instructions to seller</b> link as soon as you are on paypal.com page.</p><p>Please note donating will reset Hit and Runs, any warnings and download bans.</p>');
}
echo stdhead('Donate') . wrapper($out) . stdfoot();
