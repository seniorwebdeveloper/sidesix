<?php echo $this->view('mcp/_header'); ?>

<ul class="breadcrumb">
	<li><a href="<?=$base_url?>&method=templates"><?=lang('form:templates')?></a> <span class="divider">/</span></li>
	<li class="btnwrapper"><a href="<?=$base_url?>&method=create_template" class="btn"><?=lang('form:tmpl_new')?></a></li>
</ul>


<div class="cbody" id="TemplatesDT">
	<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
        <thead>
          <tr>
            <th style="width:30px"><?=lang('form:id')?></th>
            <th><?=lang('form:tmpl_label')?></th>
            <th><?=lang('form:tmpl_name')?></th>
            <th><?=lang('form:type')?></th>
            <th style="width:100px"><?=lang('form:actions')?></th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
</div>





<?php echo $this->view('mcp/_footer'); ?>