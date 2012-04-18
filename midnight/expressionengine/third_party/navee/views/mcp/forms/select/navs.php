<?php if (sizeof($navs)>0){ ?>
	<select name="naveeNav" class="navee_nav_select">
		<option value="0"><?= lang("ft_select_a_nav") ?></option>
		<?php foreach ($navs as $nav){ ?>
			<option value="<?= $nav["navigation_id"] ?>"><?= $nav["nav_name"] ?></option>
		<?php } ?>
	</select>
<?php } ?>
