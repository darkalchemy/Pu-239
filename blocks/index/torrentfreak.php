<?php
require_once ROOT_DIR . 'tfreak.php';
$HTMLOUT .= "
	<fieldset class='header'>
		<legend>{$INSTALLER09['site_name']}{$lang['index_torr_freak']}</legend>
			<div class='cite text-center'>";
$HTMLOUT .= rsstfreakinfo();
$HTMLOUT .= '
			</div>
	</fieldset>';
