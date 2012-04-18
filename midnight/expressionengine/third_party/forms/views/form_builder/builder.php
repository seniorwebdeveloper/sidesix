<div class="FormBuilder">
	<ul class="Tabs">
		<li><a href="#FormStageWrapper<?=$field_id?>"><?=lang('form:builder')?></a></li>
		<li><a href="#FormAlerts<?=$field_id?>"><?=lang('form:alerts')?></a></li>
		<li><a href="#FormSettings<?=$field_id?>"><?=lang('form:adv_settings')?></a></li>
	</ul>

	<div class="TabsHolder">
		<?=$this->load->view('form_builder/form_stage.php');?>
		<?=$this->load->view('form_builder/form_alerts.php');?>
		<?=$this->load->view('form_builder/form_settings.php');?>
	</div>
</div>