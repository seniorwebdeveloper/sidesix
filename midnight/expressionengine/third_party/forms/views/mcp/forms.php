<?php echo $this->view('mcp/_header'); ?>

<ul class="breadcrumb">
	<li><a href="<?=$base_url?>&method=forms"><?=lang('form')?></a> <span class="divider">/</span></li>
	<li class="btnwrapper"><a href="<?=$base_url?>&method=create_form" class="btn"><?=lang('form:form_new')?></a></li>
</ul>

<div class="cbody" id="FormsDT">
	<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
        <thead>
          <tr>
            <th style="width:30px"><?=lang('form:id')?></th>
            <th><?=lang('form')?></th>
            <th><?=lang('form:form_url_title')?></th>
            <th><?=lang('form:member')?></th>
            <th style="width:100px"><?=lang('form:type')?></th>
            <th style="width:70px"><?=lang('form:submissions')?></th>
            <th style="width:100px"><?=lang('form:date_created')?></th>
            <th style="width:100px"><?=lang('form:last_entry')?></th>
            <th style="width:100px"><?=lang('form:actions')?></th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
</div>

<br clear="all">
<?php echo $this->view('mcp/_footer'); ?>