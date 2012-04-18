<?php
	$types = array(
		'None'		=> '',
		'_blank' 	=> '_blank',
		'_parent' 	=> '_parent',
		'_self' 	=> '_self',
		'_top' 		=> '_top'
	);
?>
<select name='navee_target'>
	<?php
		foreach ($types as $k=>$v){
			$selected = "";
			if ($val == $v){
				$selected .= " selected='selected' ";
			}
	?>
			<option value='<?= $v ?>'<?= $selected ?>><?= $k ?></option>
	<?php } ?>
</select>




		

		
		
		