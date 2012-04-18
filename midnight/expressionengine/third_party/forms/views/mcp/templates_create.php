<?php echo $this->view('mcp/_header'); ?>

<ul class="breadcrumb">
	<li><a href="<?=$base_url?>&method=templates"><?=lang('form:templates')?></a> <span class="divider">/</span></li>
	<?php if ($template_label != FALSE):?><li><?=$template_label?></li>
	<?php else:?><li><?=lang('form:tmpl_new')?></li><?php endif;?>
</ul>


<div class="cbody FormsForm" id="TemplatesForm">
<?=form_open($base_url_short.AMP.'method=update_template')?>

	<?=form_hidden('template_id', $template_id);?>

	<fieldset>
		<legend><?=lang('form:tmpl_gen_info')?></legend>
		<div class="elem">
			<label><?=lang('form:tmpl_label')?></label>
			<div><?=form_input('template_label', $template_label)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl_name')?></label>
			<div><?=form_input('template_name', $template_name)?></div>
		</div>
		<div class="elem template_type">
			<label><?=lang('form:type')?></label>
			<div><?=form_dropdown('template_type', $template_types, $template_type)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:desc')?></label>
			<div><?=form_input('template_desc', $template_desc)?></div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?=lang('form:tmpl_email_info')?></legend>
		<div class="elem">
			<label><?=lang('form:tmpl:email:type')?></label>
			<div><?=form_dropdown('email_type', $email_types, $email_type)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:wordwrap')?></label>
			<div><?=form_dropdown('email_wordwrap', $yes_no, $email_wordwrap)?></div>
		</div>
		<div class="elem admin_only">
			<label><?=lang('form:tmpl:email:to')?></label>
			<div><?=form_input('email_to', $email_to)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:from')?></label>
			<div><?=form_input('email_from', $email_from)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:from_email')?></label>
			<div><?=form_input('email_from_email', $email_from_email)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:reply_to')?></label>
			<div><?=form_input('email_reply_to', $email_reply_to)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:reply_to_email')?></label>
			<div><?=form_input('email_reply_to_email', $email_reply_to_email)?></div>
		</div>
		<div class="elem admin_only">
			<label><?=lang('form:tmpl:email:reply_to_author')?></label>
			<div><?=form_dropdown('reply_to_author', array_reverse($yes_no, TRUE), $reply_to_author)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:subject')?></label>
			<div><?=form_input('email_subject', $email_subject)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:cc')?></label>
			<div><?=form_input('email_cc', $email_cc)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:bcc')?></label>
			<div><?=form_input('email_bcc', $email_bcc)?></div>
		</div>
		<div class="elem">
			<label><?=lang('form:tmpl:email:send_attach')?></label>
			<div><?=form_dropdown('email_attachments', $yes_no, $email_attachments)?></div>
		</div>
		<div class="elem" style="height:auto; min-height:250px">
			<label><?=lang('form:tmpl:email:template')?></label>
			<div>
				<textarea name="template" rows="15"><?=$template?></textarea>
				<?=lang('form:email_template_exp')?>
			</div>
		</div>
	</fieldset>
	<br />
	<button class="btn SaveBtn"><?=lang('form:save')?></button>
<?=form_close()?>
</div>





<?php echo $this->view('mcp/_footer'); ?>