<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '../include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $modes   = ['torrents', 'forums'];
    $htmlout = $att = '';
    $i       = 1;
    if (isset($_POST['load'], $_POST['load'])   && in_array($_POST['load'], $modes)) {
        if ('torrents' == $_POST['load']) {
            $query = sql_query('SELECT id, name FROM torrents ORDER BY seeders + leechers DESC LIMIT 5') or sqlerr(__FILE__, __LINE__);
            while ($res = mysqli_fetch_assoc($query)) {
                $att .= "<div class='tr'>
                                <div class='td'>$i</div>
                                <div class='td one'><a href='details.php?id=" . (int) $res['id'] . "'>" . htmlsafechars($res['name']) . "</a></div>
                                <div class='tdclear'></div>
                            </div>";
                ++$i;
            }
        } elseif ('forums' == $_POST['load']) {
            $query = sql_query('SELECT forum.*,topic.*,topic.id AS tid FROM topics AS topic INNER JOIN forums AS forum ON topic.forum_id = forum.id AND forum.min_class_read >= 0 ORDER BY tid DESC LIMIT 5') or sqlerr(__FILE__, __LINE__);
            while ($res = mysqli_fetch_assoc($query)) {
                $att .= "<div class='tr'>
                                <div class='td'>$i</div>
                                <div class='td'><a href='forums.php?action=view_topic&topic_id=" . (int) $res['tid'] . "'>" . htmlsafechars($res['topic_name']) . "</a></div>
                                <div class='tdclear'></div>
                            </div>";
                ++$i;
            }
        }
        $htmlout .= "
                        <style>
                        .t {display: table; }
                        .tr {display: table-row;}
                        .tdclear{height:5px; content: '';}
                        .td {display:table-cell; vertical-align:top;padding-right:5px;}
                        </style>
                        <div class='t'>$att</div>";
        echo $htmlout;
    }
}
