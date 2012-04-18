<?php
	$types = array(
		'True'		=> '1',
		'False' 	=> '0'
	);
?>

<select name='navee_include'>
	<?php foreach ($types as $k=>$v){
		$selected = "";
		if ($val == $v){
			$selected = " selected='selected' ";
		}
	
	?>
		<option value='<?= $v ?>'<?= $selected ?>><?= $k ?></option>
	<?php } ?>
</select>


		
		

		