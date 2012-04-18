<div class="Forms" rel="<?=$field_id?>">
<?php if ($missing_settings == TRUE) exit('<strong style="color:red">'.lang('form:missing_settings').'</strong>');?>

<?=$this->load->view('form_builder/builder.php');?>

<script type="text/javascript">
var Forms = Forms ? Forms : new Object();
Forms.JSON = Forms.JSON ? Forms.JSON : new Object();
<?php if (isset($helpjson) == TRUE):?>Forms.JSON.Help = <?=$helpjson?>;<?php endif;?>
<?php if (isset($alertjson) == TRUE):?>Forms.JSON.Alerts = <?=$alertjson?>;<?php endif;?>
</script>

</div>