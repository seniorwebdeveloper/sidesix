<div class="FormSettings" id="FormSettings<?=$field_id?>">
	<div class="cbody FormsForm">
		<fieldset>
			<legend><?=lang('form:general')?></legend>

			<?php if ($field_id == 0):?>
			<div class="elem">
				<label><?=lang('form:form_name')?></label>
				<div><?=form_input($field_name.'[settings][form_title]', ($form['form_title'] ? $form['form_title'] : 'Untitled'))?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:form_url_title')?></label>
				<div><?=form_input($field_name.'[settings][form_url_title]', ($form['form_url_title'] ? $form['form_url_title'] : 'untitled'))?></div>
			</div>
			<!--
			<div class="elem">
				<label><?=lang('form:type')?></label>
				<div>
					<?php array_shift($config['form_types']);?>
					<?=form_dropdown($field_name.'[settings][form_type]', $config['form_types'], $form['form_type'])?>
				</div>
			</div>
			-->
			<?php endif;?>

			<div class="elem">
				<label><?=lang('form:label_placement')?> <span class="ToolTip" rel="label_placement" title="<?=lang('form:label_placement')?>"></span></label>
				<div>
					<?=form_radio($field_name.'[settings][label_placement]', 'top', (($form['settings']['label_placement'] == 'top') ? TRUE : FALSE))?> <?=lang('form:place:top')?>
					<?=form_radio($field_name.'[settings][label_placement]', 'left_align', (($form['settings']['label_placement'] == 'left_align') ? TRUE : FALSE))?> <?=lang('form:place:left_align')?>
					<?=form_radio($field_name.'[settings][label_placement]', 'right_align', (($form['settings']['label_placement'] == 'right_align') ? TRUE : FALSE))?> <?=lang('form:place:right_align')?>
					<?=form_radio($field_name.'[settings][label_placement]', 'bottom', (($form['settings']['label_placement'] == 'bottom') ? TRUE : FALSE))?> <?=lang('form:place:bottom')?>
					<?=form_radio($field_name.'[settings][label_placement]', 'none', (($form['settings']['label_placement'] == 'none') ? TRUE : FALSE))?> <?=lang('form:place:none')?>
				</div>
			</div>
			<div class="elem">
				<label><?=lang('form:desc_placement')?> <span class="ToolTip" rel="desc_placement" title="<?=lang('form:desc_placement')?>"></span></label>
				<div>
					<?=form_radio($field_name.'[settings][desc_placement]', 'bottom', (($form['settings']['desc_placement'] == 'bottom') ? TRUE : FALSE))?> <?=lang('form:place:bottom')?>
					<?=form_radio($field_name.'[settings][desc_placement]', 'top', (($form['settings']['desc_placement'] == 'top') ? TRUE : FALSE))?> <?=lang('form:place:top')?>
				</div>
			</div>
			
			<!--
			<div class="elem">
				<label><?=lang('form:limit_entries')?></label>
				<div>
					<?=form_input($field_name.'[settings][limit_entries][number]', $form['settings']['limit_entries']['number'], 'style="width:20%"')?>
					<?=form_dropdown($field_name.'[settings][limit_entries][type]', $config['limit_types'], $form['settings']['limit_entries']['type'])?>
				</div>
			</div>-->
			<div class="elem">
				<label><?=lang('form:submit_button')?></label>
				<div>
					<?=form_radio($field_name.'[settings][submit_button][type]', 'default', (($form['settings']['submit_button']['type'] == 'default') ? TRUE : FALSE), ' class="ShowHideSubmitBtn" rel="default" ')?> <?=lang('form:button:default')?>
					<?=form_radio($field_name.'[settings][submit_button][type]', 'image', (($form['settings']['submit_button']['type'] == 'image') ? TRUE : FALSE), ' class="ShowHideSubmitBtn" rel="image" ')?> <?=lang('form:button:image')?>
					<br />
					<p class="btn_default"><?=lang('form:button:btext')?> <?=form_input($field_name.'[settings][submit_button][text]', $form['settings']['submit_button']['text'], 'style="width:50%"')?></p>
					<p class="btn_default"><?=lang('form:button:btext_next')?> <?=form_input($field_name.'[settings][submit_button][text_next_page]', $form['settings']['submit_button']['text_next_page'], 'style="width:50%"')?></p>
					<p class="btn_image"><?=lang('form:button:bimg')?> <?=form_input($field_name.'[settings][submit_button][img_url]', $form['settings']['submit_button']['img_url'], 'style="width:50%"')?></p>
					<p class="btn_image"><?=lang('form:button:bimg_next')?> <?=form_input($field_name.'[settings][submit_button][img_url_next_page]', $form['settings']['submit_button']['img_url_next_page'], 'style="width:50%"')?></p>
				</div>
			</div>
		</fieldset>

		<fieldset>
			<legend><?=lang('form:restrictions')?></legend>
			<div class="elem">
				<label><?=lang('form:form_enabled')?></label>
				<div>
					<?=form_dropdown($field_name.'[settings][form_enabled]', array_reverse($config['yes_no'], TRUE), $form['settings']['form_enabled'])?>
				</div>
			</div>
			<div class="elem">
				<label><?=lang('form:open_fromto')?></label>
				<div>
					<?=lang('form:from')?> <?=form_input($field_name.'[settings][open_fromto][from]', $form['settings']['open_fromto']['from'], 'style="width:150px" class="datepicker" ')?> &nbsp;&nbsp;&nbsp;
					<?=lang('form:to')?> <?=form_input($field_name.'[settings][open_fromto][to]', $form['settings']['open_fromto']['to'], 'style="width:150px" class="datepicker" ')?>
				</div>
			</div>
			<div class="elem">
				<label><?=lang('form:allow_mgroups')?></label>
				<div>
					<?=form_multiselect($field_name.'[settings][member_groups][]', $member_groups, $form['settings']['member_groups'], ' class="chosen" style="width:300px; height:50px;" ')?>
					<br />
				</div>
			</div>
			<!--
			<div class="elem">
				<label><?=lang('form:multiple_entries')?></label>
				<div>
					<?=form_dropdown($field_name.'[settings][multiple_entries]', array_reverse($config['yes_no'], TRUE), $form['settings']['multiple_entries'])?>
				</div>
			</div>
			-->
		</fieldset>

		<fieldset>
			<legend><?=lang('form:post_submission')?></legend>
			<div class="elem">
				<label><?=lang('form:return_url')?> <span class="ToolTip" rel="return_url" title="<?=lang('form:return_url')?>"></span></label>
				<div><?=form_input($field_name.'[settings][return_url]', ($form['settings']['return_url'] ? $form['settings']['return_url'] : ''))?></div>
			</div>
			<div class="elem">
				<label><?=lang('form:success_msg_when')?> </label>
				<div>
					<?=form_radio($field_name.'[settings][confirmation][when]', 'before_redirect', (($form['settings']['confirmation']['when'] == 'before_redirect') ? TRUE : FALSE), '  ')?> <?=lang('form:success:before_redirect')?> &nbsp;
					<?=form_radio($field_name.'[settings][confirmation][when]', 'after_redirect', (($form['settings']['confirmation']['when'] == 'after_redirect') ? TRUE : FALSE), ' ')?> <?=lang('form:success:after_redirect')?> &nbsp;
					<?=form_radio($field_name.'[settings][confirmation][when]', 'show_only', (($form['settings']['confirmation']['when'] == 'show_only') ? TRUE : FALSE), ' ')?> <?=lang('form:success:show_only')?> &nbsp;
					<?=form_radio($field_name.'[settings][confirmation][when]', 'disabled', (($form['settings']['confirmation']['when'] == 'disabled') ? TRUE : FALSE), ' ')?> <?=lang('form:success:disabled')?> &nbsp;
				</div>
			</div>
			<div class="elem">
				<label><?=lang('form:success_msg')?> <span class="ToolTip" rel="success_msg" title="<?=lang('form:success_msg')?>"></span></label>
				<div><?=form_textarea($field_name.'[settings][confirmation][text]', ($form['settings']['confirmation']['text'] ? $form['settings']['confirmation']['text'] : 'Thanks for contacting us! We will get in touch with you shortly.'))?></div>
			</div>
		</fieldset>

		<fieldset>
			<legend><?=lang('form:security')?></legend>
			<div class="elem">
				<label><?=lang('form:snaptcha')?> <span class="ToolTip" rel="snaptcha" title="<?=lang('form:snaptcha')?>"></span></label>
				<div>
					<?=form_radio($field_name.'[settings][snaptcha]', 'no', (($form['settings']['snaptcha'] == 'no') ? TRUE : FALSE), '  ')?> <?=lang('form:no')?>
					<?=form_radio($field_name.'[settings][snaptcha]', 'yes', (($form['settings']['snaptcha'] == 'yes') ? TRUE : FALSE), ' ')?> <?=lang('form:yes')?>
				</div>
			</div>
		</fieldset>
	</div>
</div>