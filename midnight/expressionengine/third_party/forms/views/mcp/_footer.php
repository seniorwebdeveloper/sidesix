<?php if ($this->input->get('method') != 'create_form'):?>
</div> <!-- </fcontents> -->
</div> <!-- </FBODY> -->
<?php endif;?>

<script type="text/javascript">
var Forms = Forms ? Forms : new Object();
Forms.JSON = Forms.JSON ? Forms.JSON : new Object();
<?php if (isset($helpjson) == TRUE):?>Forms.JSON.Help = <?=$helpjson?>;<?php endif;?>
<?php if (isset($alertjson) == TRUE):?>Forms.JSON.Alerts = <?=$alertjson?>;<?php endif;?>
</script>