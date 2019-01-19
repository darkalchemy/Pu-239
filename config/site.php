<?php

// Cookie setup
$site_config['sessionName'] = 'Crafty'; // A single word that identifies this install.
$site_config['cookie_prefix'] = 'Crafty_'; // This allows you to have multiple trackers, eg for demos, testing etc.
$site_config['cookie_path'] = '/'; // generally '/' is good
$site_config['cookie_domain'] = 'pu239.silly'; // set to eg: somedomain.com or is subdomain set to: sub.somedomain.com, add leading '.' to include your entire (sub)domain
$site_config['cookie_lifetime'] = 15; // length of time cookies will be valid
$site_config['domain'] = 'pu239.silly';
$site_config['sessionKeyPrefix'] = 'Crafty_'; // useful if serving multiple sites
$site_config['session_csrf'] = 'csrf_token'; // Cross-Site Request Forgery token name

// keys
$site_config['site']['salt'] = '7ced4aaf02db543cbc90b02e3a7078c7'; // random generated during install
$site_config['site']['salty'] = '09611b18fc9916a6d4540136503f6397'; // random generated during install
$site_config['site']['skey'] = '5a6408ec57a4b2959064aca3d9fd653d'; // random generated during install
$site_config['tracker_post_key'] = '76301fddad3ba8431dd2d86941595254'; // random generated during install

// Site Bot
$site_config['chatBotName'] = 'CraftyBOT';

$site_config['announce_urls'][] = 'http://pu239.silly/announce.php';
$site_config['announce_urls'][] = 'https://pu239.silly/announce.php';

// Email for sender/return path.
$site_config['site_email'] = 'darkalchemy@jbmatrix.net';
$site_config['site_name'] = 'Crafty';

$site_config['password_memory_cost'] = 2048;
$site_config['password_time_cost'] = 12;
$site_config['password_threads'] = 4;
$site_config['password_cost'] = 12;
