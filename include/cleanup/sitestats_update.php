<?php
/**
 * @param $data
 */
function sitestats_update($data)
{
    global $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    $XBT_Seeder = mysqli_fetch_assoc(sql_query('SELECT sum(seeders) AS seeders FROM torrents')) or sqlerr(__FILE__, __LINE__);
    $XBT_Leecher = mysqli_fetch_assoc(sql_query('SELECT sum(leechers) AS leechers FROM torrents')) or sqlerr(__FILE__, __LINE__);
    $registered = get_row_count('users');
    $unverified = get_row_count('users', "WHERE status='pending'");
    $torrents = get_row_count('torrents');
    $torrentstoday = get_row_count('torrents', "WHERE added between unix_timestamp(DATE_FORMAT(NOW() ,'%Y-%m-%d')) AND unix_timestamp(NOW())");    $donors = get_row_count('users', "WHERE donor ='yes'");
    $unconnectables = (XBT_TRACKER == true ? '0' : get_row_count('peers', " WHERE connectable='no'"));
    $forumposts = get_row_count('posts');
    $forumtopics = get_row_count('topics');
    $torrentsmonth = get_row_count('torrents', "WHERE added between unix_timestamp(DATE_FORMAT(NOW() ,'%Y-%m-01')) AND unix_timestamp(NOW())");
    $dt = TIME_NOW - 300; // Active users last 5 minutes
    $numactive = get_row_count('users', "WHERE last_access >= $dt");
    $gender_na = get_row_count('users', "WHERE gender = 'NA'");
    $gender_male = get_row_count('users', "WHERE gender = 'Male'");
    $gender_female = get_row_count('users', "WHERE gender = 'Female'");
    $powerusers = get_row_count('users', "WHERE class = '" . UC_POWER_USER . "'");
    $disabled = get_row_count('users', "WHERE enabled = 'no'");
    $uploaders = get_row_count('users', "WHERE class = '" . UC_UPLOADER . "'");
    $moderators = get_row_count('users', "WHERE class = '" . UC_MODERATOR . "'");
    $administrators = get_row_count('users', "WHERE class = '" . UC_ADMINISTRATOR . "'");
    $sysops = get_row_count('users', "WHERE class = '" . UC_SYSOP . "'");
    $seeders = (int)$XBT_Seeder['seeders'];
    $leechers = (int)$XBT_Leecher['leechers'];
    sql_query("UPDATE stats SET regusers = '$registered', unconusers = '$unverified', torrents = '$torrents', seeders = '$seeders', leechers = '$leechers', unconnectables = '$unconnectables', torrentstoday = '$torrentstoday', donors = '$donors', forumposts = '$forumposts', forumtopics = '$forumtopics', numactive = '$numactive', torrentsmonth = '$torrentsmonth', gender_na = '$gender_na', gender_male = '$gender_male', gender_female = '$gender_female', powerusers = '$powerusers', disabled = '$disabled', uploaders = '$uploaders', moderators = '$moderators', administrators = '$administrators', sysops = '$sysops' WHERE id = '1' LIMIT 1");
    if ($data['clean_log'] && $queries > 0) {
        write_log("Stats Cleanup: Completed using $queries queries");
    }
}
