<?php echo $this->view('mcp/_header'); ?>

<ul class="breadcrumb">
	<li><a href="<?=$base_url?>&method=settings"><?=lang('form:settings')?></a> <span class="divider">/</span></li>
</ul>


<div class="cbody FormsForm" id="SettingsForm">
<?=form_open($base_url_short.AMP.'method=update_settings')?>

	<fieldset>
		<legend><?=lang('form:recaptcha_settings')?></legend>
		<div class="elem">
			<label><?=lang('form:recaptcha_public')?></label>
			<div><?=form_input('settings[recaptcha][public]', $config['recaptcha']['public'])?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:recaptcha_private')?></label>
			<div><?=form_input('settings[recaptcha][private]', $config['recaptcha']['private'])?></div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?=lang('form:mailchimp_settings')?></legend>
		<div class="elem">
			<label><?=lang('form:api_key')?></label>
			<div><?=form_input('settings[mailchimp][api_key]', $config['mailchimp']['api_key'])?></div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?=lang('form:createsend_settings')?></legend>
		<div class="elem">
			<label><?=lang('form:api_key')?></label>
			<div><?=form_input('settings[createsend][api_key]', $config['createsend']['api_key'])?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:client_api_key')?></label>
			<div><?=form_input('settings[createsend][client_api_key]', $config['createsend']['client_api_key'])?></div>
		</div>
	</fieldset>


	<button class="btn SaveBtn"><?=lang('form:save')?></button>

<?=form_close()?>
</div>



<?php echo $this->view('mcp/_footer'); ?>