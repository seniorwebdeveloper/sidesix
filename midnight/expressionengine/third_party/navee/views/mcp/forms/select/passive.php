<?php
	$types = array(
		'False' 	=> '0',
		'True'		=> '1'
	);
?>

<select name='navee_passive'>
	<?php foreach ($types as $k=>$v){
		$selected = "";
		if ($val == $v){
			$selected = " selected='selected' ";
		}
	
	?>
		<option value='<?= $v ?>'<?= $selected ?>><?= $k ?></option>
	<?php } ?>
</select>


		
		

		