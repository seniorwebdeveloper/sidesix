<?php if ($e->num_rows() > 0){ ?>
		<?php foreach ($e->result() as $r){
			$selected = "";
			if ($entry_id == $r->entry_id){
				$selected = " selected='selected'";
			}
		?>
			<option value="<?= $r->entry_id ?>"<?= $selected ?>><?= $r->title ?></option>
		<?php } ?>
<?php } ?>
