<div class='naveeFieldtype'>
	<div class="navee_alert">Make sure to save your data first. Are you ready to edit this nav item? <a href="#" class="x navee_trash_dump">Yes</a><a href="javascript:;" class="x navee_trash_no_dump">No</a></div>
	<?php if (sizeof($navItems)>0){ ?>
		<div class="naveeExistingElements">
			<h4>Existing NavEE elements for this entry:</h4>
			<?php foreach ($navItems as $k=>$v){ ?>
				<dl>
					<dt><?= $k ?>:</dt>
					<?php foreach ($navItems[$k] as $k1=>$v1){ ?>
						<dd><a href="<?= $manageNavLink ?>&id=<?= $v1["navigation_id"] ?>&navee_id=<?= $v1["navee_id"] ?>"><?= $v1["text"] ?></a></dd>
					<?php } ?>
				</dl>
			<?php } ?>
		</div>
	<?php } ?>
	<input type="hidden" name="naveeBase" id="naveeBase" value="<?= html_entity_decode(BASE) ?>" />
	<h4>Add NavEE elements for this entry:</h4>
	<ol>
		<li>
			<label><?= lang("ft_navigation") ?></label>
			<?= $navsSelect ?>
		</li>
		
		<li>
			<label><?= lang("ft_text") ?></label>
			<input type="text" name="naveeText" value="<?= $text ?>" />
		</li>
		
		<li>
			<label><?= lang("ft_template") ?></label>
			<?= $templateSelect ?>
		</li>
		
		<li id="naveeFTParent" class="last">
			<label><?= lang("ft_parent") ?></label>
			<select name="naveeParent">
				<option value='0'><?= lang("ft_select_navigation") ?></option>
			</select>
		</li>
	</ol>
</div>