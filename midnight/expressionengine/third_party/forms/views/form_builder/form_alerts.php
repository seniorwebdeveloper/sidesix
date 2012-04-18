<div class="FormAlerts" id="FormAlerts<?=$field_id?>">

	<div class="formbar cfix">
		<h3><?=lang('form:tmpl:admin')?></h3>
		<ul>
			<li><?=form_radio($field_name.'[templates][admin][which]', 'predefined', (($form['admin_template'] > 0) ? TRUE : FALSE))?> <?=lang('form:tmpl:predefined')?></li>
			<li><?=form_radio($field_name.'[templates][admin][which]', 'custom', (($form['admin_template'] == -1) ? TRUE : FALSE))?> <?=lang('form:tmpl:custom')?></li>
			<li><?=form_radio($field_name.'[templates][admin][which]', 'none', (($form['admin_template'] == 0) ? TRUE : FALSE))?> <?=lang('form:tmpl:none')?></li>
		</ul>
	</div>
	<div class="cbody FormsForm">
		<fieldset class="predefined" <?php if ($form['admin_template'] < 1) echo "style=display:none"?>>
			<legend><?=lang('form:tmpl_predefined')?></legend>
			<div class="elem">
				<label><?=lang('form:templates')?></label>
				<div><?=form_dropdown($field_name.'[templates][admin][predefined]', $email_templates['admin'], $form['admin_template'])?></div>
			</div>
		</fieldset>
		<fieldset class="custom" <?php if ($form['admin_template'] > -1) echo "style=display:none"?>>
			<legend><?=lang('form:tmpl_email_info')?></legend>
			<div class="elem">
				<label><?=lang('form:tmpl:email:type')?></label>
				<div><?=form_dropdown($field_name.'[templates][admin][custom][email_type]', $config['email_types'], $form['templates']['admin']['email_type'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:wordwrap')?></label>
				<div><?=form_dropdown($field_name.'[templates][admin][custom][email_wordwrap]', $config['yes_no'], $form['templates']['admin']['email_wordwrap'])?></div>
			</div>
			<div class="elem email_to">
				<label><?=lang('form:tmpl:email:to')?></label>
				<div><?=form_input($field_name.'[templates][admin][custom][email_to]', $form['templates']['admin']['email_to'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:from')?></label>
				<div><?=form_input($field_name.'[templates][admin][custom][email_from]', $form['templates']['admin']['email_from'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:from_email')?></label>
				<div><?=form_input($field_name.'[templates][admin][custom][email_from_email]', $form['templates']['admin']['email_from_email'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:reply_to')?></label>
				<div><?=form_input($field_name.'[templates][admin][custom][email_reply_to]', $form['templates']['admin']['email_reply_to'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:reply_to_email')?></label>
				<div><?=form_input($field_name.'[templates][admin][custom][email_reply_to_email]', $form['templates']['admin']['email_reply_to_email'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:reply_to_author')?></label>
				<div><?=form_dropdown($field_name.'[templates][admin][custom][reply_to_author]', array_reverse($config['yes_no'], TRUE), $form['templates']['admin']['reply_to_author'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:subject')?></label>
				<div><?=form_input($field_name.'[templates][admin][custom][email_subject]', $form['templates']['admin']['email_subject'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:cc')?></label>
				<div><?=form_input($field_name.'[templates][admin][custom][email_cc]', $form['templates']['admin']['email_cc'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:bcc')?></label>
				<div><?=form_input($field_name.'[templates][admin][custom][email_bcc]', $form['templates']['admin']['email_bcc'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:send_attach')?></label>
				<div><?=form_dropdown($field_name.'[templates][admin][custom][email_attachments]', $config['yes_no'], $form['templates']['admin']['email_attachments'])?></div>
			</div>
			<div class="elem" style="height:auto; min-height:250px">
				<label><?=lang('form:tmpl:email:template')?></label>
				<div>
					<?=form_textarea($field_name.'[templates][admin][custom][template]', $form['templates']['admin']['template'], 'rows="15"')?>
					<?=lang('form:email_template_exp')?>
				</div>
			</div>
		</fieldset>
	</div>

	<br />
	<div class="formbar cfix">
		<h3><?=lang('form:tmpl:user')?></h3>
		<ul>
			<li><?=form_radio($field_name.'[templates][user][which]', 'predefined', (($form['user_template'] > 0) ? TRUE : FALSE))?> <?=lang('form:tmpl:predefined')?></li>
			<li><?=form_radio($field_name.'[templates][user][which]', 'custom', (($form['user_template'] == -1) ? TRUE : FALSE))?> <?=lang('form:tmpl:custom')?></li>
			<li><?=form_radio($field_name.'[templates][user][which]', 'none', (($form['user_template'] == 0) ? TRUE : FALSE))?> <?=lang('form:tmpl:none')?></li>
		</ul>
	</div>
	<div class="cbody FormsForm">
		<fieldset class="predefined" <?php if ($form['user_template'] < 1) echo "style=display:none"?>>
			<legend><?=lang('form:tmpl_predefined')?></legend>
			<div class="elem">
				<label><?=lang('form:templates')?></label>
				<div><?=form_dropdown($field_name.'[templates][user][predefined]', $email_templates['user'], $form['user_template'])?></div>
			</div>
		</fieldset>
		<fieldset class="custom" <?php if ($form['user_template'] > -1) echo "style=display:none"?>>
			<legend><?=lang('form:tmpl_email_info')?></legend>
			<div class="elem">
				<label><?=lang('form:tmpl:email:type')?></label>
				<div><?=form_dropdown($field_name.'[templates][user][custom][email_type]', $config['email_types'], $form['templates']['user']['email_type'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:wordwrap')?></label>
				<div><?=form_dropdown($field_name.'[templates][user][custom][email_wordwrap]', $config['yes_no'], $form['templates']['user']['email_wordwrap'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:from')?></label>
				<div><?=form_input($field_name.'[templates][user][custom][email_from]', $form['templates']['user']['email_from'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:from_email')?></label>
				<div><?=form_input($field_name.'[templates][user][custom][email_from_email]', $form['templates']['user']['email_from_email'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:reply_to')?></label>
				<div><?=form_input($field_name.'[templates][user][custom][email_reply_to]', $form['templates']['user']['email_reply_to'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:reply_to_email')?></label>
				<div><?=form_input($field_name.'[templates][user][custom][email_reply_to_email]', $form['templates']['user']['email_reply_to_email'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:subject')?></label>
				<div><?=form_input($field_name.'[templates][user][custom][email_subject]', $form['templates']['user']['email_subject'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:cc')?></label>
				<div><?=form_input($field_name.'[templates][user][custom][email_cc]', $form['templates']['user']['email_cc'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:bcc')?></label>
				<div><?=form_input($field_name.'[templates][user][custom][email_bcc]', $form['templates']['user']['email_bcc'])?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:tmpl:email:send_attach')?></label>
				<div><?=form_dropdown($field_name.'[templates][user][custom][email_attachments]', $config['yes_no'], $form['templates']['user']['email_attachments'])?></div>
			</div>
			<div class="elem" style="height:auto; min-height:250px">
				<label><?=lang('form:tmpl:email:template')?></label>
				<div>
					<?=form_textarea($field_name.'[templates][user][custom][template]', $form['templates']['user']['template'], 'rows="15"')?>
					<?=lang('form:email_template_exp')?>
				</div>
			</div>
		</fieldset>
	</div>
</div>
