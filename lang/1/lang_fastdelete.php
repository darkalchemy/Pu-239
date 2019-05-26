<?php

declare(strict_types = 1);
global $site_config;

$lang = [
    'fastdelete_error' => 'Error',
    'fastdelete_error_id' => 'Invalid ID',
    'fastdelete_no_acc' => 'Sorry yer no tall enough',
    'fastdelete_sure' => 'Security Check',
    'fastdelete_sure_msg' => "Are you sure you want to delete this torrent?<br>Click <a href='{$site_config['paths']['baseurl']}/fastdelete.php?id={$_GET['id']}&sure=1%s' class='altlink'>here</a> if you are",
    'fastdelete_msg_first' => 'Your upload',
    'fastdelete_msg_last' => 'has been deleted by',
    'fastdelete_log_first' => 'Torrent',
    'fastdelete_log_last' => 'was deleted by',
    'fastdelete_returnto' => 'Go back',
    'fastdelete_index' => 'Back to index',
    'fastdelete_deleted' => 'Torrent deleted',
    'fastdelete_head' => 'Delete Torrent (Fast)',
];
