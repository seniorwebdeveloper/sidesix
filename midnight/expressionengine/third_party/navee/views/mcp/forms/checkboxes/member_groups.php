<?php if (sizeof($groups)>0){ ?>
	<ul class="memberGroups">
		<?php
			foreach ($groups as $k=>$v){
				$checked = "";
				if (in_array($v['group_id'], $selected)){
					$checked = " checked='checked'";
				}
		?>
			<li><input type="checkbox" name="memGroup_<?= $v['group_id'] ?>"<?= $checked ?> /><?= $v["group_title"] ?></li>
		<?php } ?>
	</ul>
<?php } ?>