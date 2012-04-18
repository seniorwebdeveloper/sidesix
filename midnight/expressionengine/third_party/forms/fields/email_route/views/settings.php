<tr>
	<td><?=lang('form:choices')?> <span class="ToolTip" rel="choices" title="<?=lang('form:choices')?>"></span></td>
	<td>
		<table cellspacing="0" cellpadding="0" border="0" style="width:100%">
			<thead>
				<th style="width:15px;"></th>
				<th><?=lang('form:label')?></th>
				<th><?=lang('email')?></th>
				<th style="width:40px;"></th>
			</thead>
			<tbody>
				<?php foreach($emails['labels'] as $key => $val): ?>
				<tr>
					<td><?=form_radio($form_name_settings.'[emails][default]', $emails['values'][$key], ((isset($emails['default']) != FALSE && $emails['default'] == $emails['values'][$key]) ? TRUE : FALSE))?></td>
					<td><?=form_input($form_name_settings.'[emails][labels][]', $val);?></td>
					<td><?=form_input($form_name_settings.'[emails][values][]', $emails['values'][$key]);?></td>
					<td><a href="#" class="AddChoice"></a> <a href="#" class="RemoveChoice"></a></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</td>
</tr>