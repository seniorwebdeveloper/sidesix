<?php 
if (isset($addPagesOption)){
	$includePages = true;
} else {
	$includePages = false;
}

if (sizeof($templates)>0){ ?>
	<select name="navee_templates" id="naveeTemplates">
		<?php foreach ($templates as $k=>$v){ ?>
			<option value="<?= $v[0]["id"] ?>"><?= $k ?></option>
			<?php foreach ($v as $k=>$v){ ?>
				<?php
					$selected="";
					if ($template_id == $v["id"]){
						$selected = " selected='selected'";
					}
				?>
				<option value="<?= $v["id"] ?>"<?= $selected ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $v["name"] ?></option>
			<?php } ?>
		<?php } ?>
		<?php if ($includePages){ ?>
			<option value="0">---------------</option>
			<option value="pages"><?= lang("ft_use_pages") ?></option>
		<?php } ?>
	</select>
<?php } ?>