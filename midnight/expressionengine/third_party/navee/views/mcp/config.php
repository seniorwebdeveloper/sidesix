<div id="navEE">
	<div id="add-nav-group" class="config">

	<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=config_handler')?>
		<ol class="required group">
			<li class="left">
				<label for="stylesheet"><?= lang('cp_conf_stylesheet') ?>:</label>
				<select name="stylesheet">
					<?php foreach ($stylesheets as $s){ ?>
							<option value="<?= $s["file"] ?>"<?php if ($stylesheet == $s["file"]){ ?> selected="selected"<?php } ?>><?= $s["name"] ?></option>
					<?php } ?>
				</select>
			</li>
			<li class="right explanation"><?= lang('cp_conf_sub_instr') ?></li>
			<li class="left">
				<label for="include_index"><?= lang('cp_conf_include_index') ?>:</label>
				<select name="include_index">
					<option value="false"><?= lang('cp_false') ?></option>
					<option value="true"<?php if ($include_index == "true") { ?> selected="selected"<?php } ?>><?= lang('cp_true') ?></option>
				</select>
				<span><?= lang('cp_conf_include_index_inst') ?></span>
			</li>
			<li class="right">
				<label for="install_directory"><?= lang('cp_conf_install_directory') ?>:</label>
				<input type="input" name="install_directory" value="<?= $install_directory ?>" />
				<span><?= lang('cp_conf_install_dir_inst') ?></span>
			</li>

			<li class="right">
				<label for="remove_deleted_entries"><?= lang('cp_conf_remove_deleted_entries') ?>:</label>
				<select name="remove_deleted_entries">
					<option value="false"><?= lang('cp_false') ?></option>
					<option value="true"<?php if ($remove_deleted_entries == "true") { ?> selected="selected"<?php } ?>><?= lang('cp_true') ?></option>
				</select>
				<span><?= lang('cp_conf_remove_deleted_inst') ?></span>
			</li>
			<li class="left">
				<label for="clear_cache"><?= lang('cp_conf_clear_cache') ?>:</label>
				<select name="clear_cache">
					<option value="false"><?= lang('cp_false') ?></option>
					<option value="true"><?= lang('cp_true') ?></option>
				</select>
				<span><?= lang('cp_conf_clear_on_update') ?></span>
			</li>
			<li class="left">
				<label for="entify_ee_tags"><?= lang('cp_conf_entify_ee_tags') ?>:</label>
				<select name="entify_ee_tags">
					<option value="false"><?= lang('cp_false') ?></option>
					<option value="true"<?php if ($entify_ee_tags == "true") { ?> selected="selected"<?php } ?>><?= lang('cp_true') ?></option>
				</select>
				<span><?= lang('cp_conf_added_security') ?></span>
			</li>

			
			<li class="right">
				<label for="only_superadmins_can_admin_navs"><?= lang('cp_only_superadmins_can_admin_navs') ?>:</label>
				<select name="only_superadmins_can_admin_navs">
					<option value="false"><?= lang('cp_false') ?></option>
					<option value="true"<?php if ($only_superadmins_can_admin_navs == "true") { ?> selected="selected"<?php } ?>><?= lang('cp_true') ?></option>
				</select>
			</li>
			
		</ol>
		
		<?php if (sizeof($navs) > 0){ ?>
			<div id="blocked-member-groups">
			<h2><?= lang('cp_conf_member_access') ?></h2>
			<?php if (sizeof($member_groups) == 0){ ?>
				<p><?= lang('cp_conf_no_member_groups') ?></p>
			<?php } else { ?>
				<p><?= lang('cp_conf_member_access_inst') ?></p>
				<dl>
				<?php foreach ($navs as $nav){ ?>
					<dt><?= $nav["nav_name"] ?></dt>
					<?php foreach ($member_groups as $mb){ 
						$checked = "";
						if (isset($blockedMemberGroups[$nav["navigation_id"]])){
							if (in_array($mb["group_id"],$blockedMemberGroups[$nav["navigation_id"]])){
								$checked = " checked='checked'";
							}
						}
					?>
						<dd><input type="checkbox" name="mg_<?= $nav["navigation_id"] ?>_<?= $mb["group_id"] ?>"<?= $checked ?> /> <?= $mb["group_title"] ?></dd>
					<?php } ?>
					
				<?php } ?>
					<dt>Hide "Configure NavEE" Button from any checked Member Groups</dt>
					<?php foreach ($member_groups as $mb){ 
						$checked = "";
						if (isset($blockedMemberGroups[0])){
							if (in_array($mb["group_id"],$blockedMemberGroups[0])){
								$checked = " checked='checked'";
							}
						}
					?>
					<dd><input type="checkbox" name="mg_0_<?= $mb["group_id"] ?>"<?= $checked ?> /> <?= $mb["group_title"] ?></dd>
					<?php } ?>
				</dl>
			<?php } ?>
			</div>
		<?php } ?>
		
		<div id="blocked-member-groups">
			<h2><?= lang('cp_conf_hide_templates') ?></h2>
			<p><?= lang('cp_conf_hide_templates_inst') ?></p>
				<dl>
				<?php foreach ($templates as $k=>$v){ ?>
					<dt><?= $k ?></dt>
					<?php foreach ($v as $kk=>$vv){ ?>
						<?php
							$checked = "";
							if (in_array($vv["id"], $blockedTemplates)){
								$checked = " checked='checked'";
							}
						?>
						<dd><input type="checkbox" name="tp_<?= $vv["id"] ?>"<?= $checked ?> /> <?= $vv["name"] ?></dd>
					<?php } ?>
					
				<?php } ?>	
				</dl>
			</div>
		
		
		
		<input type="submit" class="create-nav-group btn action" value="<?= lang('cp_conf_update') ?>" name="navee_submit" />
	<?=form_close()?>
	
	
	
	</div>
</div>
