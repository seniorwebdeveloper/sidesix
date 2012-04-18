<div class="ChannelImagesField cfix">

<ul class="ChannelImagesTabs">
	<li><a href="#CIActions"><?=lang('ci:upload_actions')?></a></li>
	<li><a href="#CILocSettings"><?=lang('ci:loc_settings')?></a></li>
	<li><a href="#CIFieldUI"><?=lang('ci:fieldtype_settings')?></a></li>
</ul>

<div class="ChannelImagesTabsHolder">
<div class="CIActions cfix" id="CIActions">

<?php foreach($action_groups as $group_name => $group):?>
	<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable ActionGroup">
	<thead>
		<tr class="group_name">
			<th colspan="4">
				<h4><?=ucfirst($group['group_name'])?></h4> <small><?=lang('ci:hover2edit')?></small>
				<input type="hidden" class="gname" name="channel_images[action_groups][][group_name]" value="<?=$group['group_name']?>" />
				<span class="imageprev">
					<input type="checkbox" name="channel_images[action_groups][][wysiwyg]" value="yes" class="wysiwyg" <?php if ($group['wysiwyg'] == 'yes') echo 'checked'?> > <?=lang('ci:wysiwyg')?> &nbsp;&nbsp;
					<input type="radio" name="channel_images[small_preview]" value="" class="small_preview" <?php if ($small_preview == $group['group_name']) echo 'checked'?> > <?=lang('ci:small_prev')?> &nbsp;&nbsp;
					<input type="radio" name="channel_images[big_preview]" value="" class="big_preview" <?php if ($big_preview == $group['group_name']) echo 'checked'?> > <?=lang('ci:big_prev')?>
					<a href="#" class="DelActionGroup">&nbsp;</a>
				</span>
			</th>
		</tr>
		<tr class="action_cols">
			<th style="width:30px"><?=lang('ci:step')?></th>
			<th style="width:150px"><?=lang('ci:action')?></th>
			<th><?=lang('ci:settings')?></th>
			<th style="width:40px"></th>
		</tr>
	</thead>
	<tbody>
	<?php $count = 1;?>
	<?php foreach($group['actions'] AS $action_name => $settings):?>
	<?php if (isset($actions[$action_name]) == FALSE) continue;?>
		<tr>
			<td><?=$count?></td>
			<td><?=$actions[$action_name]->info['title']?></td>
			<td>
				<?=$actions[$action_name]->display_settings($settings)?>
				<input type="hidden" class="action_step" name="channel_images[action_groups][][actions][<?=$action_name?>][step]" value="<?=$count?>">
			</td>
			<td><a href="#" class="MoveAction">&nbsp;</a><a href="#" class="DelAction">&nbsp;</a></td>
		</tr>
	<?php $count++;?>
	<?php unset($action_name, $settings); endforeach;?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4">
				<select>
				<option value=""><?=lang('ci:add_action')?></option>
				<?php foreach($actions as $action_name => &$actionobj):?>
					<option value="<?=$actionobj->info['name']?>"><?=$actionobj->info['title']?></option>
				<?php endforeach;?>
				</select>
			</td>
		</tr>
	</tfoot>
	</table>
<?php endforeach;?>

	<a href="#" class="AddActionGroup"><img src="<?=CHANNELIMAGES_THEME_URL?>img/add.png" /><?=lang('ci:add_action_group')?></a>

	<div class="default_actions">
	<?php foreach($actions as $action_name => &$actionobj):?>
		<div class="<?=$actionobj->info['name']?>">
			<?=base64_encode('
			<tr>
				<td></td>
				<td>'.$actions[$action_name]->info['title'].'</td>
				<td>
				'.$actions[$action_name]->display_settings().'
				<input type="hidden" class="action_step" name="channel_images[action_groups][][actions]['.$action_name.'][step]" value="">
			</td>
			<td><a href="#" class="MoveAction">&nbsp;</a><a href="#" class="DelAction">&nbsp;</a></td>
			</tr>
			');?>
		</div>
	<?php endforeach;?>
	</div>
</div>


<div class="CILocSettings" id="CILocSettings">
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable" style="width:80%">
	<thead>
		<tr>
			<th style="width:180px"><?=lang('ci:pref')?></th>
			<th><?=lang('ci:value')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:keep_original')?></td>
			<td>
				<?=form_dropdown('channel_images[keep_original]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $keep_original)?>
				<small><?=lang('ci:keep_original_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:upload_location')?></td>
			<td><?=form_dropdown('channel_images[upload_location]', $upload_locations, $upload_location, ' class="ci_upload_type" ')?></td>
		</tr>
		<tr>
			<td colspan="2"><a href="#" class="TestLocation"><?=lang('ci:test_location')?></a></td>
		</tr>
	</tbody>
</table>


<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CIUpload_local" style="width:80%">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:local')?>
					<small><?=lang('ci:specify_pref_cred')?></small>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:upload_location')?></td>
			<td><?=form_dropdown('channel_images[locations][local][location]', $local['locations'], $locations['local']['location'] ); ?></td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CIUpload_s3" style="width:80%">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:s3')?>
					<small><?=lang('ci:specify_pref_cred')?></small>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:s3:key')?> <small><?=lang('ci:s3:key_exp')?></small></td>
			<td><?=form_input('channel_images[locations][s3][key]', $locations['s3']['key'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:secret_key')?> <small><?=lang('ci:s3:secret_key_exp')?></small></td>
			<td><?=form_input('channel_images[locations][s3][secret_key]', $locations['s3']['secret_key'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:bucket')?> <small><?=lang('ci:s3:bucket_exp')?></small></td>
			<td><?=form_input('channel_images[locations][s3][bucket]', $locations['s3']['bucket'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:region')?></td>
			<td><?=form_dropdown('channel_images[locations][s3][region]', $s3['regions'], $locations['s3']['region']); ?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:acl')?> <small><?=lang('ci:s3:acl_exp')?></small></td>
			<td><?=form_dropdown('channel_images[locations][s3][acl]', $s3['acl'], $locations['s3']['acl']); ?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:storage')?></td>
			<td><?=form_dropdown('channel_images[locations][s3][storage]', $s3['storage'], $locations['s3']['storage']); ?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:directory')?></td>
			<td><?=form_input('channel_images[locations][s3][directory]', $locations['s3']['directory'])?></td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CIUpload_cloudfiles" style="width:80%">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:cloudfiles')?>
					<small><?=lang('ci:specify_pref_cred')?></small>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:cloudfiles:username')?></td>
			<td><?=form_input('channel_images[locations][cloudfiles][username]', $locations['cloudfiles']['username'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:api')?></td>
			<td><?=form_input('channel_images[locations][cloudfiles][api]', $locations['cloudfiles']['api'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:container')?></td>
			<td><?=form_input('channel_images[locations][cloudfiles][container]', $locations['cloudfiles']['container'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:region')?></td>
			<td><?=form_dropdown('channel_images[locations][cloudfiles][region]', $cloudfiles['regions'], $locations['cloudfiles']['region']); ?></td>
		</tr>
	</tbody>
</table>

</div>

<div class="CIFieldUI" id="CIFieldUI">

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable" style="width:80%">
	<thead>
		<tr>
			<th style="width:180px"><?=lang('ci:pref')?></th>
			<th><?=lang('ci:value')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:categories')?></td>
			<td>
				<?=form_input('channel_images[categories]', implode(',', $categories), 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:categories_explain')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_stored_images')?></td>
			<td><?=form_dropdown('channel_images[show_stored_images]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $show_stored_images)?></td>
		</tr>
		<tr>
			<td><?=lang('ci:limt_stored_images_author')?></td>
			<td>
				<?=form_dropdown('channel_images[stored_images_by_author]', array('no' => lang('ci:no'), 'yes' => lang('ci:yes')), $stored_images_by_author)?>
				<small><?=lang('ci:limt_stored_images_author_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:stored_images_search_type')?></td>
			<td>
				<?=form_dropdown('channel_images[stored_images_search_type]', array('entry' => lang('ci:entry_based'), 'image' => lang('ci:image_based')), $stored_images_search_type)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:allow_per_image_action')?></td>
			<td>
				<?=form_dropdown('channel_images[allow_per_image_action]', array('no' => lang('ci:no'), 'yes' => lang('ci:yes')), $allow_per_image_action)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:jeditable_event')?></td>
			<td>
				<?=form_dropdown('channel_images[jeditable_event]', array('click' => lang('ci:click'), 'mouseenter' => lang('ci:hover')), $jeditable_event)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:image_limit')?></td>
			<td>
				<?=form_input('channel_images[image_limit]', $image_limit, 'style="border:1px solid #ccc; width:50px;"')?>
				<small><?=lang('ci:image_limit_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:act_url')?></td>
			<td>
				<strong><a href="<?=$act_url?>" target="_blank"><?=$act_url?></a></strong>
				<small><?=lang('ci:act_url:exp')?></small>
			</td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable" style="width:80%">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:field_columns')?>
					<small><?=lang('ci:field_columns_exp')?></small>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($columns as $name => $val):?>
		<tr>
			<td><?=lang('ci:'.$name)?></td>
			<td><?=form_input('channel_images[columns]['.$name.']', $val)?></td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
</div>

</div>
</div>