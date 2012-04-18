<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
	<thead>
		<tr>
			<th><?=lang('form:date')?></th>
			<td><?=$this->localize->decode_date('%Y-%m-%d %g:%i %A', $fentry['date'])?></td>
			<th><?=lang('form:country')?></th>
			<td><?=strtoupper($fentry['country'])?></td>
		</tr>
		<tr>
			<th><?=lang('form:member')?></th>
			<td><?=$fentry['screen_name']?> (<?=$fentry['email']?>)</td>
			<th><?=lang('form:ip')?></th>
			<td><?=long2ip($fentry['ip_address'])?></td>
		</tr>
	</thead>
</table>

<br />

<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
	<thead>
	<?php foreach($dbfields[0] as $key => $field):?>
		<tr>
			<th style="width:175px"><?=$field['title']?></th>
			<td><?=$this->formsfields[ $field['field_type'] ]->output_data($field, $fentry['fid_'.$field['field_id']], 'html')?></td>
		<?php if (isset($dbfields[1][$key]) != FALSE):?>
			<th style="width:175px"><?=$dbfields[1][$key]['title']?></th>
			<td><?=$this->formsfields[ $dbfields[1][$key]['field_type'] ]->output_data($dbfields[1][$key], $fentry['fid_'.$dbfields[1][$key]['field_id']], 'html')?></td>
		<?php else:?>
			<th style="width:175px"></th>
			<td></td>
		<?php endif;?>
		</tr>
	<?php endforeach;?>
	</thead>
</table>


