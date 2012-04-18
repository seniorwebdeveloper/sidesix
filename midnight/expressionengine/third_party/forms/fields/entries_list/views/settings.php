<tr>
	<td><?=lang('form:limit_channel')?></td>
	<td>
	<div style="width:300px">
		<?=form_multiselect($form_name_settings.'[channels][]', $dbchannels, $channels, "class='multiselect' ")?>
		</div>
	</td>
</tr>
<tr>
	<td><?=lang('form:group_by_channel')?></td>
	<td>
		<?=form_dropdown($form_name_settings.'[grouped]', array('no' => lang('form:no'), 'yes' => lang('form:yes')), $grouped)?>
	</td>
</tr>
<tr>
	<td><?=lang('form:what2store')?></td>
	<td>
		<?=form_dropdown($form_name_settings.'[store]', array('entry_title' => lang('form:entry_title'), 'entry_url_title' => lang('form:entry_urltitle'), 'entry_id' =>  lang('form:entry_id')), $store)?>
	</td>
</tr>