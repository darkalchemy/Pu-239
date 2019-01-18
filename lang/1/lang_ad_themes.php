<?php

$lang = [
    //Headers
    'stdhead_templates' => 'Templates',
    //Main table
    'themes_id' => 'ID',
    'themes_name' => 'Name',
    'themes_uri' => 'Uri',
    'themes_is_folder' => 'Folder Exists?',
    'themes_min_class' => 'Min Class To View',
    'themes_e_d' => 'Edit/Delete',
    'themes_edit' => 'Edit',
    'themes_delete' => 'Delete',
    'themes_file_exists' => "<span class='has-text-success'> Yes </span>",
    'themes_not_exists' => "<span class='has-text-danger'> No </span>",
    //Other Stuff
    'themes_use_temp' => 'Use this template',
    'themes_addnew' => 'Add a template',
    'themes_edit_tem' => 'Edit Template',
    //---' <Template Name>' added in source
    'themes_edit_uri' => 'Edit Uri',
    'themes_save' => 'Save',
    'themes_add' => 'Add',
    'themes_some_wrong' => 'Something Went Wrong',
    'themes_delete_sure_q' => 'Are you sure you want to delete this template?',
    'themes_delete_sure_q2' => "Click <span class='has-text-success'>here</span>",
    'themes_delete_sure_q3' => 'if you are sure',
    'themes_delete_q' => 'Delete Template',
    'themes_takenids' => "Taken ID's: ",
    //Messages
    'themes_msg' => 'Succesfully Edited',
    'themes_msg1' => 'Succesfully Saved',
    'themes_msg2' => 'Succesfully Deleted',
    'themes_msg3' => 'Succesfully Added',
    //Guide/Explains
    'themes_guide' => '
<ul class="left20">
    <li class="bullet">Make a folder in the Templates dir: ' .  TEMPLATE_DIR . ' and create files:
        <ul>
            <li class="bullet">default.css</li>
            <li class="bullet">custom.css</li>
            <li class="bullet">template.php</li>
        </ul>
    </li><br>
    <li class="bullet">In template.php there shall be minimum 4 functions
        <ul>
            <li class="bullet">stdhead</li>
            <li class="bullet">stdfoot</li>
            <li class="bullet">stdmsg</li>
            <li class="bullet">StatusBar</li>
        </ul>
    </li><br>
    <li class="bullet">Make a folder in the AJAX Chat dir: ' .  AJAX_CHAT_PATH . 'css/ and copy these files from ' .  AJAX_CHAT_PATH . 'css/1/:
        <ul>
            <li class="bullet">global.css</li>
            <li class="bullet">fonts.css</li>
            <li class="bullet">custom.css</li>
            <li class="bullet">default.css</li>
        </ul>
    </li><br>
</ul>',
    'themes_explain_id' => 'This shall be the same as the folder name',
    //Errors
    'themes_error' => 'Error',
    'themes_inv_act' => 'Invalid Action',
    'themes_inv_id' => 'Invalid ID',
    'themes_inv_uri' => 'Invalid Uri',
    'themes_inv_name' => 'Invalid Name',
    'themes_inv_class' => 'Invalid Class',
    'themes_nofile' => 'Template file does not exist',
    'themes_inv_file' => 'Continue?',
    //Credits
    'themes_credits' => 'Credits to AronTh for making this template mananger and the template system',
];
