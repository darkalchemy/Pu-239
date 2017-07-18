<?php
require_once ROOT_DIR . 'radio.php';
$HTMLOUT.= "
	<fieldset class='header'>
		<legend>{$INSTALLER09['site_name']} Radio</legend>
			<div class='container-fluid'>";
			$HTMLOUT.= radioinfo($radio);
			$HTMLOUT.= "
			</div>
	</fieldset>
<hr />";
//==
// End Class
// End File
