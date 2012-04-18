<tr>
	<td><?=lang('form:subscribe_text')?></td>
	<td>
		<?=form_input($form_name_settings.'[subscribe_text]', ((isset($subscribe_text) == TRUE) ? $subscribe_text : lang('form:subrtext')))?>
	</td>
</tr>

<tr>
	<td><?=lang('form:mailinglist_type')?></td>
	<td class="ShowHideSubmitBtn">
		<?php if (empty($ee['lists']) == FALSE):?>

		<?=form_radio($form_name_settings.'[type]', 'ee', ((isset($type) == TRUE && $type == 'ee') ? TRUE : FALSE), ' class="ShowHideSubmitBtn" rel="ee" ')?> <?=lang('form:mailinglist:ee')?>

		<p class="btn_ee">
			<br />
			<label><?=lang('form:mailinglist:list')?></label> <br />
			<?=form_dropdown($form_name_settings.'[ee][list]', $ee['lists'], ((isset($ee['list']) != FALSE) ? $ee['list'] : ''))?>
		</p>
		<?php endif;?>

		<?php if (empty($mailchimp['lists']) == FALSE):?>

		<?=form_radio($form_name_settings.'[type]', 'mailchimp', ((isset($type) == TRUE && $type == 'mailchimp') ? TRUE : FALSE), ' class="ShowHideSubmitBtn" rel="mailchimp" ')?> <?=lang('form:mailinglist:mailchimp')?>
		<p class="btn_mailchimp">
			<br />
			<label><?=lang('form:mailinglist:list')?></label> <br />
			<?=form_dropdown($form_name_settings.'[mailchimp][list]', $mailchimp['lists'], ((isset($mailchimp['list']) != FALSE) ? $mailchimp['list'] : ''))?>
		</p>

		<?php endif;?>

		<?php if (empty($createsend['lists']) == FALSE):?>

		<?=form_radio($form_name_settings.'[type]', 'createsend', ((isset($type) == TRUE && $type == 'createsend') ? TRUE : FALSE), ' class="ShowHideSubmitBtn" rel="createsend" ')?> <?=lang('form:mailinglist:createsend')?>

		<p class="btn_createsend">
			<br />
			<label><?=lang('form:mailinglist:list')?></label> <br />
			<?=form_dropdown($form_name_settings.'[createsend][list]', $createsend['lists'], ((isset($createsend['list']) != FALSE) ? $createsend['list'] : ''))?>
		</p>

		<?php endif;?>


	</td>
</tr>