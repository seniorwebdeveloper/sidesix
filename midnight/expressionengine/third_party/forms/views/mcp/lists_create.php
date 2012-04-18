<?php echo $this->view('mcp/_header'); ?>

<ul class="breadcrumb">
	<li><a href="<?=$base_url?>&method=lists"><?=lang('form:lists')?></a> <span class="divider">/</span></li>
	<?php if ($list_label != FALSE):?><li><?=$list_label?></li>
	<?php else:?><li><?=lang('form:list_new')?></li><?php endif;?>
</ul>


<div class="cbody FormsForm" id="ListForm">
<?=form_open($base_url_short.AMP.'method=update_list')?>

	<?=form_hidden('list_id', $list_id);?>

	<fieldset>
		<legend><?=lang('form:list_gen_info')?></legend>
		<div class="elem">
			<label><?=lang('form:list_label')?></label>
			<div><?=form_input('list_label', $list_label)?></div>
		</div>
	</fieldset>
<br>
	<fieldset>
		<legend><?=lang('form:list_bulk')?></legend>

		<div class="elem">
			<label>
				<?=lang('form:list:items')?>
				<br><br>
				<?=lang('form:option_setting_ex')?>
			</label>
			<div>
				<textarea name="items" rows="30"><?=$items?></textarea>
			</div>
		</div>

	</fieldset>
	<br />
	<button class="btn SaveBtn"><?=lang('form:save')?></button>
<?=form_close()?>
</div>





<?php echo $this->view('mcp/_footer'); ?>