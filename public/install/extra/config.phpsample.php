<?php

// Cookie setup
$site_config['sessionName']      = '#sessionName'; // A single word that identifies this install.
$site_config['cookie_prefix']    = '#cookie_prefix_'; // This allows you to have multiple trackers, eg for demos, testing etc.
$site_config['cookie_path']      = '#cookie_path'; // generally '/' is good
$site_config['cookie_domain']    = '#cookie_domain'; // set to eg: .somedomain.com or is subdomain set to: .sub.somedomain.com
$site_config['cookie_lifetime']  = #cookie_lifetime; // length of time cookies will be valid
$site_config['domain']           = '#domain';
$site_config['sessionKeyPrefix'] = '#cookie_prefix_'; // useful if serving multiple sites
$site_config['session_csrf']     = 'csrf_token'; // useful if serving multiple sites

// keys
$site_config['site']['salt']       = '#pass1'; // random generated during install
$site_config['site']['salty']      = '#pass2'; // random generated during install
$site_config['site']['skey']       = '#pass3'; // random generated during install
$site_config['staff']['staff_pin'] = '#pass4'; // random generated during install
$site_config['staff']['owner_pin'] = '#pass5'; // random generated during install
$site_config['tracker_post_key']   = '#pass6'; // random generated during install
$site_config['image_proxy_key']    = [
    'uid' => 'key',
]; // assigned by the image proxy host

// Site Bot
$site_config['chatBotName'] = '#bot_username';

// XBT or PHP announce
if (XBT_TRACKER) {
    $site_config['xbt_prefix']      = '#announce_urls:2710/';
    $site_config['xbt_suffix']      = '/announce';
    $site_config['announce_urls'][] = '#announce_urls:2710/announce';
} else {
    $site_config['announce_urls']   = [];
    $site_config['announce_urls'][] = '#announce_urls';
    $site_config['announce_urls'][] = '#announce_https';
}
// Email for sender/return path.
$site_config['site_email'] = '#site_email';
$site_config['site_name']  = '#site_name';

$site_config['password_memory_cost'] = 2048;
$site_config['password_time_cost'] = 12;
$site_config['password_threads'] = 4;
$site_config['password_cost'] = 12;

