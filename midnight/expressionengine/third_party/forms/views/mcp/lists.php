<?php echo $this->view('mcp/_header'); ?>

<ul class="breadcrumb">
	<li><a href="<?=$base_url?>&method=lists"><?=lang('form:lists')?></a> <span class="divider">/</span></li>
    <li class="btnwrapper"><a href="<?=$base_url?>&method=create_list" class="btn"><?=lang('form:list_new')?></a></li>
</ul>

<div class="cbody" id="ListsDT">
	<table cellpadding="0" cellspacing="0" border="0" class="FormsTable">
        <thead>
          <tr>
            <th style="width:30px"><?=lang('form:id')?></th>
            <th><?=lang('form:list_label')?></th>
            <th style="width:100px"><?=lang('form:actions')?></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($lists as $list): ?>
        <tr>
        	<td><?=$list->list_id?></td>
        	<td><?=$list->list_label?></td>
        	<td>
        		<a href="<?=$base_url?>&method=create_list&list_id=<?=$list->list_id?>" class="gEdit">&nbsp;</a>
        		<a href="<?=$base_url?>&method=update_list&list_id=<?=$list->list_id?>&delete=yes" class="gDel">&nbsp;</a>
        	</td>
        </tr>
        <?php endforeach;?>
        </tbody>
      </table>
</div>

<?php echo $this->view('mcp/_footer'); ?>