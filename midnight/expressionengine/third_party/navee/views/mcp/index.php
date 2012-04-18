<div id="navEE">
	<?php
		if ($instructions){
	?>
		<div class="first-time">
			<div id="support"><h3><?= lang('cp_pre_beta'); ?></h3> <p><?= lang('cp_beta_text'); ?> <a href="http://booyant.com/navee">booyant.com/navee</a></p></div>
			
			<div id="welcome-brief">
				<h3><?= lang('cp_start') ?></h3>
				<p><?= lang('cp_click_button') ?><a class="lg btn action" href="<?= $add_nav_link ?>"><?= lang('cp_click_me') ?></a></p>
				
			</div>
		</div>
	<?php	
		} else {
	?>
			<div class="navee_alert hidden"></div>
			<table class="mainTable" border="0" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th class="navee_th_name">Name</th>
						<th class="navee_th_title">Title</th>
						<th class="navee_th_desc">Description</th>
						<th class="navee_th_del"></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($navs AS $nav){ ?>
					<?php if (!in_array($memberGroupId, $blockedMemberGroups[$nav["navigation_id"]])){ ?>
					<tr>
						<td><div>
							<span><a href='<?= $manage_nav_link ?>&id=<?= $nav["navigation_id"] ?>'><?= $nav["nav_name"] ?></a></span>
							<?php if (!$hideAddDelete){ ?>
								<a class='navee_edit icn' title="<?= lang('cp_edit') ?>" id='navee_edit_name_<?= $nav["navigation_id"] ?>' href='javascript:;'>Edit</a>
								<a class='navee_accept icn' title="<?= lang('cp_save') ?>" id='navee_accept_name_<?= $nav["navigation_id"] ?>' href='javascript:;'>Save</a>
							<?php } ?>
						</div></td>
						<td><div>
							<span><?= $nav["nav_title"] ?></span>
							<?php if (!$hideAddDelete){ ?>
								<a class='navee_edit icn' title="<?= lang('cp_edit') ?>" id='navee_edit_title_<?= $nav["navigation_id"] ?>' href='javascript:;'>Edit</a>
								<a class='navee_accept icn' title="<?= lang('cp_save') ?>" id='navee_accept_title_<?= $nav["navigation_id"] ?>' href='javascript:;'>Save</a>
							<?php } ?>
						</div></td>
						<td><div>
							<span><?= $nav["nav_description"] ?></span>
							<?php if (!$hideAddDelete){ ?>
							<a class='navee_edit icn' title="<?= lang('cp_edit') ?>" id='navee_edit_description_<?= $nav["navigation_id"] ?>' href='javascript:;'>Edit</a>
							<a class='navee_accept icn' title="<?= lang('cp_save') ?>" id='navee_accept_description_<?= $nav["navigation_id"] ?>' href='javascript:;'>Save</a>
							<?php } ?>
						</div></td>
						
						<td>
							<?php if (!$hideAddDelete){ ?>
							<a class='navee_delete icn' title="<?= lang('cp_delete') ?>" id='navDelete_<?= $nav["navigation_id"] ?>' href='javascript:;'><img src='<?= $theme_folder_url ?>navee/img/trash-dark.png' alt='' /></a>
							<?php } ?>
						</td>
						
					</tr>
					<?php } ?>
				<?php } ?>
				</tbody>
			</table>
			<?php if (!$hideAddDelete){ ?>
			<a class="btn action manage" href="<?= $add_nav_link ?>"><?= lang('cp_add_more') ?></a>
			<?php } ?>
			<?php if (!in_array($memberGroupId, $blockedMemberGroups[0])){ ?>
				<a class="btn options manage" href="<?= $config_link ?>"><?= lang('cp_conf_configure') ?></a>
			<?php } ?>			
	<?php } ?>

</div>