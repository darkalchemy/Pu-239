<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn();
loggedinorreturn();

$lang = array_merge(load_language('global'), load_language('blackjack'));
$HTMLOUT = '';
if ($CURUSER['game_access'] == 0 || $CURUSER['game_access'] > 1 || $CURUSER['suspended'] == 'yes') {
    stderr($lang['bj_error'], $lang['bj_gaming_rights_disabled']);
}

$width = 100 / 3;
$color1 = $color2 = $color3 = $color4 = $color5 = $color6 = $color7 = $color8 = $color9 = 'red';

// 1GB
$bj1_count = get_row_count('blackjack1', 'WHERE status = "waiting"');
if ($bj1_count > 0) {
    $color1 = 'green';
}

// 10GB
$bj2_count = get_row_count('blackjack2', 'WHERE status = "waiting"');
if ($bj2_count > 0) {
    $color2 = 'green';
}

// 20GB
$bj3_count = get_row_count('blackjack3', 'WHERE status = "waiting"');
if ($bj3_count > 0) {
    $color3 = 'green';
}

// 50GB
$bj4_count = get_row_count('blackjack4', 'WHERE status = "waiting"');
if ($bj4_count > 0) {
    $color4 = 'green';
}

// 100 GB
$bj5_count = get_row_count('blackjack5', 'WHERE status = "waiting"');
if ($bj5_count > 0) {
    $color5 = 'green';
}

// 250 GB
$bj6_count = get_row_count('blackjack6', 'WHERE status = "waiting"');
if ($bj6_count > 0) {
    $color6 = 'green';
}

// 500 GB
$bj7_count = get_row_count('blackjack7', 'WHERE status = "waiting"');
if ($bj7_count > 0) {
    $color7 = 'green';
}

// 1TB
$bj8_count = get_row_count('blackjack8', 'WHERE status = "waiting"');
if ($bj8_count > 0) {
    $color8 = 'green';
}

// Casino
$casino_count = get_row_count('casino', 'WHERE deposit > 0 AND userid != ' . sqlesc($CURUSER['id']));
if ($casino_count > 0) {
    $color9 = 'green';
}

$HTMLOUT = '';
$HTMLOUT .= "
	<h1>{$INSTALLER09['site_name']} Games!</h1>
	<table align='center' class='table table-bordered centered no-margin narrow'>
		<tr>
			<td colspan='5' align='center' class='colhead2'><font color='#555555'><b>Welcome To The Casino, Please Select A Game Below To Play.</b></font></td>
		</tr>
		<tr>
			<td align='center' width='{$width}%' class='colhead3'>
				<a class='altlink' href='blackjack.php?id=1' style='color: {$color1};'>BlackJack 1GB
					<img width='100%' src='{$INSTALLER09['pic_base_url']}blackjack.jpg' alt='blackjack' title='BlackJack 1GB' class='tooltipper' />
				</a>
			</td>
        	<td align='center' width='{$width}%' class='colhead3'>
            	<a class='altlink' href='blackjack.php?id=10' style='color: {$color2};'>BlackJack 10GB
                	<img width='100%' src='{$INSTALLER09['pic_base_url']}blackjack.jpg' alt='blackjack' title='BlackJack 10GB' class='tooltipper' />
	            </a>
    	    </td>
        	<td align='center' width='{$width}%' class='colhead3'>
            	<a class='altlink' href='blackjack.php?id=20' style='color: {$color3};'>BlackJack 20GB
                	<img width='100%' src='{$INSTALLER09['pic_base_url']}blackjack.jpg' alt='blackjack' title='BlackJack 20GB' class='tooltipper' />
	            </a>
    	    </td>
		</tr>
		<tr>
        	<td align='center' width='{$width}%' class='colhead3'>
            	<a class='altlink' href='blackjack.php?id=50' style='color: {$color4};'>BlackJack 50GB
                	<img width='100%' src='{$INSTALLER09['pic_base_url']}blackjack.jpg' alt='blackjack' title='BlackJack 50GB' class='tooltipper' />
	            </a>
    	    </td>
			<td align='center' width='{$width}%' class='colhead3'>
				<a class='altlink' href='casino.php' style='color: {$color9};'>Casino
					<img width='100%' src='{$INSTALLER09['pic_base_url']}casino.jpg' alt='casino' title='Casino' class='tooltipper' />
				</a>
			</td>
        	<td align='center' width='{$width}%' class='colhead3'>
            	<a class='altlink' href='blackjack.php?id=100' style='color: {$color5};'>BlackJack 100GB
                	<img width='100%' src='{$INSTALLER09['pic_base_url']}blackjack.jpg' alt='blackjack' title='BlackJack 100GB' class='tooltipper' />
	            </a>
    	    </td>
		</tr>
		<tr>
        	<td align='center' width='{$width}%' class='colhead3'>
            	<a class='altlink' href='blackjack.php?id=250' style='color: {$color6};'>BlackJack 250GB
                	<img width='100%' src='{$INSTALLER09['pic_base_url']}blackjack.jpg' alt='blackjack' title='BlackJack 250GB' class='tooltipper' />
	            </a>
    	    </td>
        	<td align='center' width='{$width}%' class='colhead3'>
            	<a class='altlink' href='blackjack.php?id=500' style='color: {$color7};'>BlackJack 500GB
                	<img width='100%' src='{$INSTALLER09['pic_base_url']}blackjack.jpg' alt='blackjack' title='BlackJack 500GB' class='tooltipper' />
	            </a>
    	    </td>
        	<td align='center' width='{$width}%' class='colhead3'>
            	<a class='altlink' href='blackjack.php?id=1024' style='color: {$color8};'>BlackJack 1TB
                	<img width='100%' src='{$INSTALLER09['pic_base_url']}blackjack.jpg' alt='blackjack' title='BlackJack 1TB' class='tooltipper' />
	            </a>
    	    </td>
		</tr>
	</table>";

echo stdhead('Games') . $HTMLOUT . stdfoot();
