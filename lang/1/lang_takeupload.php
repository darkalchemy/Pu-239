<?php

declare(strict_types = 1);
global $site_config;

$lang = [
    'takeupload_success' => 'Successfully uploaded!',
    'takeupload_failed' => 'Upload failed!',
    'takeupload_no_formdata' => 'missing form data',
    'takeupload_no_filename' => 'Empty filename!',
    'takeupload_no_nfo' => 'No NFO!',
    'takeupload_0_byte' => '0-byte NFO',
    'takeupload_nfo_big' => 'NFO is too big! Max 65,535 bytes.',
    'takeupload_nfo_failed' => 'NFO upload failed',
    'takeupload_no_descr' => 'You must enter a description or a Nfo!',
    'takeupload_no_cat' => 'You must select a category to put the torrent in!',
    'takeupload_invalid' => 'Invalid filename!',
    'takeupload_not_torrent' => 'Invalid filename (not a .torrent).',
    'takeupload_eek' => 'eek',
    'takeupload_no_file' => 'Empty file!',
    'takeupload_not_benc' => 'What have you uploaded? This is not a bencoded file!',
    'takeupload_not_dict' => 'invalid torrent, info is not a dictionary',
    'takeupload_no_keys' => 'dictionary is missing key(s)',
    'takeupload_empty_dict' => 'invalid torrent, info dictionary does not exist',
    'takeupload_encode_error' => 'Could not properly encode file',
    'takeupload_invalid_entry' => 'invalid entry in dictionary',
    'takeupload_dict_type' => 'invalid dictionary entry type',
    'takeupload_missing_parts' => 'invalid torrent, missing parts of the info dictionary',
    'takeupload_invalid_types' => 'invalid torrent, invalid types in the info dictionary',
    'takeupload_unknown' => 'Unknown',
    'takeupload_pieces' => 'invalid pieces',
    'takeupload_url' => 'invalid announce url! must be <b>%s</b>',
    'takeupload_both' => 'missing both length and files',
    'takeupload_file_list' => 'invalid files, not a list',
    'takeupload_no_files' => 'no files',
    'takeupload_error' => 'filename error',
    'takeupload_already' => 'This torrent has already been uploaded! Please use the search function before uploading.',
    'takeupload_log' => 'Torrent %s (%s) was uploaded by %s',
    'takeupload_img_failed' => 'Image upload failed',
    'takeupload_img_type' => 'Image file is is invalid type.',
    'takeupload_img_big' => 'Image file is too big! Max 512,000 bytes.',
    'takeupload_img_exists' => 'An image already exists. Contact Admin for assistance.',
    'takeupload_img_copyerror' => 'An error occured copy the image to the image storage repository. Contact Admin for assistance.',
    'takeupload_bucket_format' => 'The image you are trying (%s) to upload is not allowed!',
    'takeupload_bucket_size' => 'The image is to big (%s)! max size can be ' . mksize($site_config['bucket']['maxsize']),
    'takeupload_no_youtube' => 'youtube link is not correct or is not present!',
    'takeupload_bucket_noimg' => 'You forgot about the images!',
    'takeupload_what' => 'What did you upload? This is not a bencoded file!',
    'takeupload_piece_size' => 'piece size is not mod(4096), invalid torrent.',
    'takeupload_length' => 'length must be an integer',
    'takeupload_no_info' => 'file info not found, empty filename in torrent file?',
    'takeupload_invalid_info' => 'invalid file info',
    'takeupload_type_error' => 'filename type error',
    'takeupload_no_match' => 'total file size and number of pieces do not match',
    'takeupload_agreement' => "In using this torrent you are bound by the {$site_config['site']['name']} Confidentiality Agreement By Law",
    'takeupload_offer_subject' => 'An offer you voted for has been uploaded!',
    'takeupload_request_subject' => 'A request you were interested in has been uploaded!',
    'takeupload_email_subject' => 'New Torrent Uploaded!',
];

