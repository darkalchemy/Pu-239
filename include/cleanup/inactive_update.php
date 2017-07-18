<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
/*
 * account_delete function
 * 
 * Copyright 2014 Luis "Laffin" Espinoza <laffintoo@gmail.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This function is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */
function cleanup_log($data)
{
    $text = sqlesc($data['clean_title']);
    $added = TIME_NOW;
    $ip = sqlesc($_SERVER['REMOTE_ADDR']);
    $desc = sqlesc($data['clean_desc']);
    sql_query("INSERT INTO cleanup_log (clog_event, clog_time, clog_ip, clog_desc) VALUES ($text, $added, $ip, {$desc})") or sqlerr(__FILE__, __LINE__);
}
/*function inactive_account_delete($userid)
{
        $secs = 350 * 86400;
        $dt = (TIME_NOW - $secs);
        $maxclass = UC_STAFF;
	$references = array(
		"id" => array("users"), // Do Not move this line
		"userid" => array("blackjack","blocks","bookmarks","happyhour","happylog","user_blocks","ustatus","userhits","usercomments"
			),
                "uid" => array("xbt_files_users","thankyou"),
                "user_id" => array("posts","topics","poll_voters"),
		"friendid" => array(
			"friends"
			),
		);
	$ctr = 1;
	foreach($references as $field=>$tablelist)
	{
		foreach($tablelist as $table)
		{
			$tables[] = $tc = "t{$ctr}";
			$joins[] = ($ctr == 1) ? "users as {$tc}":"INNER JOIN {$table} as {$tc} on t0.id={$tc}.{$field}";
			$ctr++;
		}
	}
	return 'DELETE '. implode(', ',$tables) . " FROM " . implode(' ',$joins) . " WHERE t0.id='{$userid}'AND class < '{$maxclass}' AND last_access < '{$dt}';";
}*/
function account_delete($where,$report=FALSE)
{
	$fields='id:users userid:blackjack,blocks,bookmarks,casino,coins,freeslots,friends,happyhour,happylog,ips,peers,pmboxes,reputation,shoutbox,snatched,thanks,thumbsup,uploadapp,user_blocks,userhits,ustatus friendid:friends offered_by_user_id:offers relation_with:relations sender:messages,invite_codes receiver:messages uid:thankyou whoadded:reputation user:flashscores,rating,relations,tickets user_id:now_viewing,offer_votes,poll_voters,read_posts,subscriptions';
	$type=(is_string($where)?1:(is_int($where)?2:FALSE));
	if(!$type)
		return FALSE;
	if($type==2)
		$where="users.id='{$where}'";
	$fields=explode(' ',$fields);
	$ctr=0;
	foreach($fields as $field)
	{
		$field=explode(':',$field);
		$field[1]=explode(',',$field[1]);
		foreach($field[1] as $table)
		{
			if(!$ctr)
				$primary="{$table}.{$field[0]}";
			$tables[]=$table;
			$joins[]=($ctr++==0)?"{$table}":"{$table} ON {$primary}={$table}.{$field[0]}";
		}
	}
	$tables=' '. implode(',',$tables);
	$from=implode(' INNER JOIN ',$joins);
	if($report)
	{
		$report="SELECT count(*) FROM {$from} WHERE {$where};".PHP_EOL; //put mysql routines here, and place array of accounts in report
	}
	echo "DELETE {$tables} FROM {$from} WHERE {$where};".PHP_EOL; // put mysql routines here
	return $report;
}
function docleanup($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(1);
    //== Delete inactive user accounts
    $secs = 350 * 86400;
    $dt = (TIME_NOW - $secs);
    $maxclass = UC_STAFF;
    sql_query("SELECT FROM users WHERE parked='no' AND status='confirmed' AND class < $maxclass AND last_access < $dt");
    //== Delete parked user accounts
    $secs = 675 * 86400; // change the time to fit your needs
    $dt = (TIME_NOW - $secs);
    $maxclass = UC_STAFF;
    sql_query("SELECT FROM users WHERE parked='yes' AND status='confirmed' AND class < $maxclass AND last_access < $dt");
    if ($queries > 0) write_log("Inactive Clean -------------------- Inactive Clean Complete using $queries queries--------------------");
    if (false !== mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS["___mysqli_ston"]) . " items deleted/updated";
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
?>
