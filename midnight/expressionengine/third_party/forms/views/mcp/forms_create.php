<?php echo $this->view('mcp/_header'); ?>

<div style="padding:10px 10px 150px 10px">


<div class="cbody FormsForm" id="NewForm">
<?=form_open($base_url_short.AMP.'method=update_form')?>

	<?=form_hidden('form_id', $form['form_id']);?>

	<div class="Forms" rel="0">
		<?=$this->load->view('form_builder/builder.php');?>
		<br />
		<button class="btn SaveBtn"><?=lang('form:save')?></button>
	</div>

<?=form_close()?>
</div>


</div>

<?php echo $this->view('mcp/_footer'); ?>