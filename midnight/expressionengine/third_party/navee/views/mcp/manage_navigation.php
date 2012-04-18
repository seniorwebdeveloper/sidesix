<div id="navEE" class="tree-view">
	<div id="navEE-Nav-Select">
		<select>
			<?php foreach ($navee_navs as $navee_nav){ ?>
				<option value="<?= $navee_nav["navigation_id"] ?>"<?php if ($navee_nav["navigation_id"] == $navigation_id) { ?> selected="selected"<?php } ?>><?= $navee_nav["nav_name"] ?></option>
			<?php } ?>
			<option value="-">------------</option>
			<option value="-1"><?= lang('cp_mn_new_tree') ?></option>
			<option value="0"><?= lang('cp_mn_manage_trees') ?></option>
			
		</select>
		<p><?= lang('cp_mn_change_tree') ?></p>
		<?php if (!$nav_empty){ ?><a href="javascript:;" id="navEE-Form-Add"><?= lang('cp_mn_add_item') ?></a><?php } ?>
	</div>
	<div id="navEE-Form">
		<div id="navEE-Form-Header">
			<h3><?= lang('cp_mn_add_item') ?></h3>
			<p><?= lang('cp_mn_add_item_desc') ?></p>
		</div>
		<div id="navEE-Form-Content">
			<?= $navItemForm ?>
		</div>
	</div>
	<div class="navee_alert<?php if (strlen($alert) == 0){ ?> hidden<?php } ?>"><?= $alert ?><a href='javascript:;' class='x'>X</a></div>	
	<div id="add_navItem" class="hidden"></div>
	<div id="navee_cp_nav">
		<?php if ($nav_empty){ ?>
			<div id="new_tree">
				<h2><?= lang('new_tree_h2') ?></h2>
				<h3><?= lang('new_tree_fi_h3') ?></h3>
				<p><?= lang('new_tree_fi_p') ?></p>
				
				<h3><?= lang('new_tree_qs_h3') ?></h3>
				<ol>
					<li><?= lang('new_tree_qs_step1') ?></li>
					<li><?= lang('new_tree_qs_step2') ?></li>
					<li><?= lang('new_tree_qs_step3') ?></li>
					<li><?= lang('new_tree_qs_step4') ?></li>
				</ol>
			</div>
		<?php } ?>
		<?= $nav ?>
	</div>
</div>
