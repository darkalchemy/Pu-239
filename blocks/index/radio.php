<?php
require_once ROOT_DIR . 'radio.php';
$HTMLOUT .= "
	<fieldset class='header'>
		<legend>{$INSTALLER09['site_name']} Radio</legend>
			<div class='cite text-center'>";
$HTMLOUT .= radioinfo($radio);
$HTMLOUT .= '
			</div>
	</fieldset>';
