<?php if (sizeof($channels)>0){ ?>
	<select name="navee_channels" class="navee_channels_select">
		<option value="0">Select a Channel</option>
		<?php foreach ($channels as $c){ 
			$selected = "";
			if ($c["id"] == $channel_id){
				$selected = ' selected="selected"';
			}
		?>
			<option value="<?= $c["id"] ?>"<?= $selected ?>><?= $c["title"] ?></option>
		<?php } ?>
	</select>
<?php } ?>