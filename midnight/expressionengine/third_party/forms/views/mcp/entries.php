<?php echo $this->view('mcp/_header'); ?>

<ul class="breadcrumb">
	<li><a href="<?=$base_url?>&method=entries"><?=lang('form:submissions')?></a> <span class="divider">/</span></li>
</ul>

<div class="leftmenu">
	<div class="section formfilter">
		<span class="filtersection"><?=lang('form:filter:form')?></span>
		<div class="elem">
			<?=form_multiselect('filter[forms][]', $forms, '', ' class="chzn-select" ');?>
		</div>
		<br />
	</div>

	<div class="section">
		<span class="filtersection"><?=lang('form:filter:date')?></span>
		<div class="elem">
			<input type="text" name="filter[date][from]" placeholder="<?=lang('form:from')?>" class="datepicker">
		</div>
		<div class="elem">
			<input type="text" name="filter[date][to]" placeholder="<?=lang('form:to')?>" class="datepicker">
		</div>
	</div>

	<!--
	<div class="section">
		<span class="filtersection"><?=lang('form:filter:keywords')?></span>
		<div class="elem">
			<input type="text" name="filter[keywords]" placeholder="<?=lang('form:keywords')?>">
		</div>
	</div>
	-->

	<div class="section countryfilter">
		<?php include_once(APPPATH.'config/countries.php');?>
		<?php $countries = array('xx' => lang('form:unknown')) + $countries;?>
		<span class="filtersection"><?=lang('form:filter:country')?></span>
		<div class="elem">
			<?=form_multiselect('filter[country][]', $countries, '', ' id="filter[country]" ');?>
		</div>
	</div>
</div>

<div class="rightbody" id="SubmissionsDT">
	<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
        <thead>
          <tr>
            <th style="width:30px"><?=lang('form:id')?></th>
            <th><?=lang('form:member')?></th>
            <th style="width:110px"><?=lang('form:date')?></th>
            <th style="width:50px"><?=lang('form:country')?></th>
            <th style="width:70px"><?=lang('form:ip')?></th>
            <th><?=lang('form')?></th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
</div>

<br clear="all">
<?php echo $this->view('mcp/_footer'); ?>