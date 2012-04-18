<div class="FormStageWrapper cfix" id="FormStageWrapper<?=$field_id?>">

	<div class="RightPanel">
		<div class="FormTools">
			<?php foreach ($form['fields'] as $cat => $catfields):?>
				<h6 class="<?=$cat?>"><?=lang('form:'.$cat)?> <span class="ToolTip" rel="<?=$cat?>" title="<?=lang('form:'.$cat)?>"></span></h6>
				<div class="abody">
				<?php foreach($catfields as $class_name): ?>
					<?php if (isset($this->formsfields[$class_name]) == FALSE) continue;?>
					<a href="#" class="draggable" rel="<?=$class_name?>">
						<img src="<?=FORMS_THEME_URL?>fields/<?=$class_name?>.png" />
						<?=$this->formsfields[$class_name]->info['title']?>
					</a>
				<?php endforeach;?><br clear="all">
				</div>
			<?php endforeach;?>
		</div>
	</div>

	<div class="FormStage">
		<img src="<?=FORMS_THEME_URL?>img/form_begin.png">
		<div class="StageFields">
			<div class="FirstDrop"><?=lang('form:first_drop_exp')?> <span class="move"></span></div>
		</div>
		<img src="<?=FORMS_THEME_URL?>img/form_end.png">
	</div>


<script id="FormsElemTmpl" type="text/x-jquery-tmpl">
<div class="FormElem cfix">
	<div class="topbar">
		<h6>{{{field_type_label}}}</h6>
		<div class="actions">
			<a href="#" class="settings"><?=lang('form:settings')?></a>
			<a href="#" class="del"><?=lang('form:delete')?></a>
		</div>
	</div>
	<div class="inner">
		<div class="FieldContent {{{field_type}}} {{#field_required}}req{{/field_required}}">{{{field_content}}}</div>
	</div>
	<div class="ElemSettings">

		<table cellspacing="0" cellpadding="0" border="0" class="stable">
			<thead>
				<tr>
					<th colspan="10"><?=lang('form:settings')?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?=lang('form:field_label')?> <span class="ToolTip" rel="field_label" title="<?=lang('form:field_label')?>"></span></td>
					<td><input name="{{{form_name}}}[title]" type="text" value="{{{title}}}"/></td>
				</tr>
				<tr>
					<td><?=lang('form:field_short_name')?> <span class="ToolTip" rel="field_short_name" title="<?=lang('form:field_short_name')?>"></span></td>
					<td><input name="{{{form_name}}}[url_title]" type="text" value="{{{url_title}}}"/></td>
				</tr>
				<tr>
					<td><?=lang('form:field_desc')?> <span class="ToolTip" rel="field_desc" title="<?=lang('form:field_desc')?>"></span></td>
					<td><textarea name="{{{form_name}}}[description]">{{{description}}}</textarea></td>
				</tr>
				<tr>
					<td><?=lang('form:rules')?></td>
					<td>
						<input type="checkbox" name="{{{form_name}}}[required]" class="FieldRequired" value="yes" {{#field_required}}checked{{/field_required}}/> <?=lang('form:required')?> <span class="ToolTip" rel="required_field" title="<?=lang('form:required')?>"></span> <br />
						<!-- <input type="checkbox" name="{{{form_name}}}[no_dupes]" class="FieldRequired" value="yes" {{#field_no_dupes}}checked{{/field_no_dupes}}/> <?=lang('form:no_duplicates')?> <span class="ToolTip" rel="no_dupes"></span> -->
					</td>
				</tr>
				{{{field_settings}}}
			</tbody>
		</table>
		<a href="#" class="SaveSettings" rel="{{{field_class}}}"><?=lang('form:save_settings')?></a>
		<p class="SavingSettings hidden"><?=lang('form:saving_settings')?></p>
	</div>
	<div class="HiddenVal">
		<input name="{{{form_name}}}[type]" type="hidden" class="hType" value="{{{field_type}}}"/>
		<input name="{{{form_name}}}[field_id]" type="hidden" value="{{{field_id}}}"/>
	</div>

</div>
</script>

<script type="text/javascript">
var Forms = Forms ? Forms : new Object();
Forms.JSON = Forms.JSON ? Forms.JSON : new Object();
<?php if (isset($fieldjson) == TRUE):?>Forms.JSON.Fields = <?=$fieldjson?>;<?php endif;?>

if (typeof(FormsDBFieldJSON) == 'object') {
	var TempF = <?=$dbfieldjson?>;
	for (var attrname in TempF) { Forms.JSON.DBFields[attrname] = TempF[attrname]; }
	delete TempF;
} else Forms.JSON.DBFields = <?=$dbfieldjson?>;
</script>

</div>

