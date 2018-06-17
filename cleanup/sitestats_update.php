<?php
/**
 * @param $data
 */
function sitestats_update($data)
{
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $query   = sql_query('SELECT IFNULL(sum(seeders), 0) AS seeders FROM torrents') or sqlerr(__FILE__, __LINE__);
    $Seeder  = mysqli_fetch_assoc($query);
    $query   = sql_query('SELECT IFNULL(sum(leechers), 0) AS leechers FROM torrents') or sqlerr(__FILE__, __LINE__);
    $Leecher = mysqli_fetch_assoc($query);

    $registered     = get_row_count('users');
    $unverified     = get_row_count('users', "WHERE status = 'pending'");
    $torrents       = get_row_count('torrents');
    $torrentstoday  = get_row_count('torrents', "WHERE added between UNIX_TIMESTAMP(DATE_FORMAT(NOW() ,'%Y-%m-%d')) AND UNIX_TIMESTAMP(NOW())");
    $donors         = get_row_count('users', "WHERE donor ='yes'");
    $unconnectables = (XBT_TRACKER ? '0' : get_row_count('peers', " WHERE connectable='no'"));
    $forumposts     = get_row_count('posts');
    $forumtopics    = get_row_count('topics');
    $torrentsmonth  = get_row_count('torrents', "WHERE added between UNIX_TIMESTAMP(DATE_FORMAT(NOW() ,'%Y-%m-01')) AND UNIX_TIMESTAMP(NOW())");
    $dt             = TIME_NOW - 300; // Active users last 5 minutes
    $numactive      = get_row_count('users', "WHERE last_access >= $dt");
    $gender_na      = get_row_count('users', "WHERE gender = 'NA'");
    $gender_male    = get_row_count('users', "WHERE gender = 'Male'");
    $gender_female  = get_row_count('users', "WHERE gender = 'Female'");
    $powerusers     = get_row_count('users', "WHERE class = '" . UC_POWER_USER . "'");
    $disabled       = get_row_count('users', "WHERE enabled = 'no'");
    $uploaders      = get_row_count('users', "WHERE class = '" . UC_UPLOADER . "'");
    $moderators     = get_row_count('users', "WHERE class = '" . UC_MODERATOR . "'");
    $administrators = get_row_count('users', "WHERE class = '" . UC_ADMINISTRATOR . "'");
    $sysops         = get_row_count('users', "WHERE class = '" . UC_SYSOP . "'");
    $seeders        = (int) $Seeder['seeders'];
    $leechers       = (int) $Leecher['leechers'];
    $sql            = "UPDATE stats SET regusers = $registered, unconusers = $unverified, torrents = $torrents, seeders = $seeders, leechers = $leechers, unconnectables = $unconnectables,
            torrentstoday = $torrentstoday, donors = $donors, forumposts = $forumposts, forumtopics = $forumtopics, numactive = $numactive, torrentsmonth = $torrentsmonth, gender_na = $gender_na,
            gender_male = $gender_male, gender_female = $gender_female, powerusers = $powerusers, disabled = $disabled, uploaders = $uploaders, moderators = $moderators,
            administrators = $administrators, sysops = $sysops WHERE id = 1";
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Stats Cleanup: Completed using $queries queries");
    }
}
