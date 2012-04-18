<?php echo $this->view('mcp/_header'); ?>

<ul class="breadcrumb">
	<li><a href="<?=$base_url?>&method=forms"><?=lang('forms')?></a> <span class="divider">/</span></li>
	<li><?=$form->form_title?></li>
</ul>


<div class="cbody">
<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
	<thead>
		<tr>
			<th style="width:100px"><?=lang('form')?> <?=lang('form:id')?></th>
			<td><?=$form->form_id?></td>
			<th style="width:100px"><?=lang('form:member')?></th>
			<td><?=$form->screen_name?></td>
			<th style="width:100px"><?=lang('form:date_created')?></th>
			<td><?=$this->localize->decode_date('%d-%M-%Y %g:%i %A', $form->date_created)?></td>
		</tr>
		<tr>
			<th><?=lang('form:form_name')?></th>
			<td><?=$form->form_title?></td>
			<th><?=lang('form:type')?></th>
			<td><?php if ($form->entry_id > 0) echo '<strong class="green">' . lang('form:entry_linked') . '</strong>'; else echo '<strong class="blue">' . lang('form:salone') . '</strong>'; ?></td>
			<th><?=lang('form:last_entry')?></th>
			<td><?=($form->date_last_entry != FALSE) ? $this->localize->decode_date('%d-%M-%Y %g:%i %A', $form->date_last_entry) : ''?></td>
		</tr>
	</thead>
</table>

<div class="BoxWrapper FieldWrapper">
	<?php foreach ($standard_fields as $field_name => $field_title):?>
		<button class="btn" rel="<?=$field_name?>"><?=$field_title?></button>
	<?php endforeach;?> |
	<?php foreach ($dbfields as $field):?>
		<button class="btn" rel="field_id_<?=$field->field_id?>"><?=$field->title?></button>
	<?php endforeach;?>
</div>

<div>
	<div class="BoxWrapper FilterWrapper" style="float:left; width:75%; margin:0;">
		<div class="filterbox">
			<div class="elem">
				<input type="text" name="filter[date][from]" placeholder="<?=lang('form:date_from')?>" class="datepicker">
			</div>
			<div class="elem">
				<input type="text" name="filter[date][to]" placeholder="<?=lang('form:date_to')?>" class="datepicker">
			</div>
		</div>

		<div class="filterbox">
			<?php include_once(APPPATH.'config/countries.php');?>
			<?php $countries = array('xx' => lang('form:unknown')) + $countries;?>
			<div class="elem">
				<?=form_multiselect('filter[country][]', $countries, '', ' class="chzn-select" data-placeholder="'.lang('form:filter:country').'" ');?>
			</div>
			<div class="elem">
				<?=form_multiselect('filter[members][]', $members, '', ' class="chzn-select" data-placeholder="'.lang('form:filter:members').'" ');?>
			</div>
		</div>

		<br clear="all">
	</div>

	<div class="BoxWrapper ExportWrapper" style="float:right; width:20%;">
		<div class="export_options">
			<strong><?=lang('form:export')?></strong>
			<button class="btn csv" rel="csv"><span>CSV</span></button>
			<button class="btn xls" rel="xls"><span>XLS</span></button>
		</div>
		<div class="DisplaySelect">
			Display
				<select>
					<option value="10">10</option>
					<option value="15">15</option>
					<option value="20" selected="selected">20</option>
					<option value="25">25</option>
					<option value="50">50</option>
					<option value="75">75</option>
					<option value="100">100</option>
					<option value="-1">All</option>
				</select> records
		</div>
	</div>

	<br clear="all">
</div>

<div id="EntriesDT">
	<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
        <thead>
          <tr>
            <th style="width:30px"><?=lang('form:id')?></th>
            <?php foreach ($standard_fields as $field_name => $field_title):?>
				<th><?=$field_title?></th>
			<?php endforeach;?>
			<?php foreach ($dbfields as $field):?>
				<th><?=$field->title?></th>
			<?php endforeach;?>
          </tr>
        </thead>
        <tbody>

        </tbody>
        <tfoot>
        </tfoot>
      </table>
      <div id="LoadingDT"><p><?=lang('form:loading_dt')?></p></div>
</div>








</div>

<script type="text/javascript">
var FormsDTCols = [];
FormsDTCols.push({mDataProp:'fentry_id', sName:'fentry_id', bSortable: true});

<?php foreach ($standard_fields as $field_name => $field_title):?>
FormsDTCols.push({mDataProp:'<?=$field_name?>', sName:'<?=$field_name?>'});
<?php endforeach;?>

<?php foreach ($dbfields as $field):?>
FormsDTCols.push({mDataProp:'field_id_<?=$field->field_id?>', sName:'field_id_<?=$field->field_id?>', bVisible:false});
<?php endforeach;?>

var FormsDTData = {form_id: <?=$form->form_id?>};

</script>

<div id="FormsExportDialogWrapper" style="display:none">
<div class="FormsExportDialog">
	<div class="sectionwrapper csv">
		<form method="POST">
		<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
			<thead>
				<tr>
					<th style="width:100px"><?=lang('form:export:fields')?></th>
					<td>
						<input name="export[fields]" type="radio" value="current" checked> <?=lang('form:export:current_fields')?> <br />
						<input name="export[fields]" type="radio" value="all"> <?=lang('form:export:all_fields')?>
					</td>
					<th style="width:100px"><?=lang('form:export:entries')?></th>
					<td>
						<input name="export[entries]" type="radio" value="current" checked> <?=lang('form:export:current_entries')?> <br />
						<input name="export[entries]" type="radio" value="all"> <?=lang('form:export:all_entries')?>
					</td>
				</tr>
				<tr>
					<th style="width:100px"><?=lang('form:export:delimiter')?></th>
					<td>
						<input name="export[delimiter]" type="radio" value="comma" checked> <?=lang('form:export:comma')?> <br />
						<input name="export[delimiter]" type="radio" value="tab"> <?=lang('form:export:tabs')?><br />
						<input name="export[delimiter]" type="radio" value="semicolon"> <?=lang('form:export:scolons')?><br />
						<input name="export[delimiter]" type="radio" value="pipe"> <?=lang('form:export:pipes')?>
					</td>
					<th style="width:100px"><?=lang('form:export:enclosure')?></th>
					<td>
						<input name="export[enclosure]" type="radio" value="double_quote" checked> <?=lang('form:export:dblquote')?><br />
						<input name="export[enclosure]" type="radio" value="quote"> <?=lang('form:export:quote')?>

					</td>
				</tr>
				<tr>
					<th style="width:100px"><?=lang('form:export:include_header')?></th>
					<td>
						<input name="export[include_header]" type="radio" value="yes" checked> <?=lang('form:yes')?> <br />
						<input name="export[include_header]" type="radio" value="no"> <?=lang('form:no')?><br />
					</td>
					<th style="width:100px"><?=lang('form:export:member_info')?></th>
					<td>
						<input name="export[member_info]" type="radio" value="screen_name" checked> <?=lang('form:export:screen_name')?> <br />
						<input name="export[member_info]" type="radio" value="username"> <?=lang('form:export:username')?><br />
						<input name="export[member_info]" type="radio" value="email"> <?=lang('form:export:email')?><br />
						<input name="export[member_info]" type="radio" value="member_id"> <?=lang('form:export:member_id')?>
					</td>
				</tr>
			</thead>
		</table>

		<input name="export[type]" type="hidden" value="csv">
		<input name="export[form_id]" type="hidden" value="<?=$form->form_id?>">
		<button class="btn ExportButton"><?=lang('form:export')?> CSV</button>
		<div class="hidden_fields"></div>
		</form>
	</div>

	<div class="sectionwrapper xls">
		<form method="POST">
		<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
			<thead>
				<tr>
					<th style="width:100px"><?=lang('form:export:fields')?></th>
					<td>
						<input name="export[fields]" type="radio" value="current" checked> <?=lang('form:export:current_fields')?> <br />
						<input name="export[fields]" type="radio" value="all"> <?=lang('form:export:all_fields')?>
					</td>
					<th style="width:100px"><?=lang('form:export:entries')?></th>
					<td>
						<input name="export[entries]" type="radio" value="current" checked> <?=lang('form:export:current_entries')?> <br />
						<input name="export[entries]" type="radio" value="all"> <?=lang('form:export:all_entries')?>
					</td>
				</tr>
				<tr>
					<th style="width:100px"><?=lang('form:export:include_header')?></th>
					<td>
						<input name="export[include_header]" type="radio" value="yes" checked> <?=lang('form:yes')?> <br />
						<input name="export[include_header]" type="radio" value="no"> <?=lang('form:no')?><br />
					</td>
					<th style="width:100px"><?=lang('form:export:member_info')?></th>
					<td>
						<input name="export[member_info]" type="radio" value="screen_name" checked> <?=lang('form:export:screen_name')?> <br />
						<input name="export[member_info]" type="radio" value="username"> <?=lang('form:export:username')?><br />
						<input name="export[member_info]" type="radio" value="email"> <?=lang('form:export:email')?><br />
						<input name="export[member_info]" type="radio" value="member_id"> <?=lang('form:export:member_id')?>
					</td>
				</tr>
			</thead>
		</table>

		<input name="export[type]" type="hidden" value="xls">
		<input name="export[form_id]" type="hidden" value="<?=$form->form_id?>">
		<button class="btn ExportButton"><?=lang('form:export')?> XLS</button>
		<div class="hidden_fields"></div>
		</form>
	</div>

	<p class="LoadingExport"><?=lang('form:export:loading')?></p>
</div>
</div>

<?php echo $this->view('mcp/_footer'); ?>

