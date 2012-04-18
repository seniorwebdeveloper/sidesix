<div id="navEE">
	<p class="instructions"><?= lang('cp_all_fields_required') ?></p>
	<?php if (strlen($helper)>0){ ?>
		<div class="navee_helper"><?= $helper ?></div>
	<?php } ?>
	<div id="add-nav-group">
	<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=add_navigation_handler')?>
		<ol class="required" class="create_nav">
			<li class="left">
				<label for="navee_name">Name:</label>
				<input type="input" name="navee_name" value="<?= $navee_name ?>" tabindex="1" />
			</li>
			<li class="right">
				<label for="navee_description">Description:</label>
				<textarea name="navee_description" tabindex="3"><?= $navee_description ?></textarea>
			</li>
			
			<li class="left">
				<label for="navee_title">Title:</label>
				<input type="input" name="navee_title" value="<?= $navee_title ?>" tabindex="2" />
				<span>single word, no spaces</span>
			</li>
			
			
		</ol>
		<input type="submit" class="create-nav-group btn action" value="<?= lang('cp_an_add_nav_group') ?>" name="navee_submit" tabindex="4" />
	<?=form_close()?>
	</div>
</div>
