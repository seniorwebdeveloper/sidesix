<tr>
	<td><?=lang('form:limit_cat_groups')?></td>
	<td>
	<div style="width:300px">
		<?=form_multiselect($form_name_settings.'[cat_groups][]', $category_groups, $cat_groups, "class='multiselect' ")?>
		</div>
	</td>
</tr>
<tr>
	<td><?=lang('form:group_by_cat_group')?></td>
	<td>
		<?=form_dropdown($form_name_settings.'[grouped]', array('no' => lang('form:no'), 'yes' => lang('form:yes')), $grouped)?>
	</td>
</tr>
<tr>
	<td><?=lang('form:what2store')?></td>
	<td>
		<?=form_dropdown($form_name_settings.'[store]', array('cat_name' => lang('form:cat_name'), 'cat_id' => lang('form:cat_id')), $store)?>
	</td>
</tr>

