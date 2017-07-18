<?php
require_once ROOT_DIR . 'tfreak.php';
$HTMLOUT.= "
	<fieldset class='header'>
		<legend>{$INSTALLER09['site_name']}{$lang['index_torr_freak']}</legend>
			<div class='container-fluid'>";
			$HTMLOUT.= rsstfreakinfo();
			$HTMLOUT.= "
			</div>
	</fieldset>
	<hr />";
//==
// End Class
// End File
