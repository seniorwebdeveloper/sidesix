<?php 
	if ($selected == "true"){
		$s = " selected='selected'";	
	} else {
		$s = "";
	}
?>
<option value='<?= $navee_id ?>'<?= $s ?>><?= $spaces ?><?= $text ?></option>