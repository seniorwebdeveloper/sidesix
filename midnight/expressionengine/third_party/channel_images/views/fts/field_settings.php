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
			<td><?=lang('ci:hybrid_upload')?></td>
			<td>
				<?=form_dropdown('channel_images[hybrid_upload]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $hybrid_upload)?>
				<small><?=lang('ci:hybrid_upload_exp')?></small>
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