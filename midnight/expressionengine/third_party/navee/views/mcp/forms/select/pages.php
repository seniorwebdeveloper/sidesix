<?php 
	if (sizeof($pages)>0){
		asort($pages); 
?>
	<select name='pages' class='pagesDropdown'>
		<?php
			foreach ($pages as $k=>$v){
				
				$selected = "";
				$link = "";
				
				if ($k==$entry_id){
					$selected = " selected='selected'";
				}
				
				// install directory
				if (strlen($ee_install_directory)>0){
					$link .= "/".$ee_install_directory;
				}
				
				// include index
				if ($include_index == "true"){
					$link .= "/".$index_page;
				}
				
				// pages content
				$link .= $v;
		?>
			<option value='<?= $k ?>'<?= $selected ?>><?= $link ?></option>
		<?php } ?>
	</select>
<?php } ?>	