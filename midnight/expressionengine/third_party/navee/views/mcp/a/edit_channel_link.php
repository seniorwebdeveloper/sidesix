<?php if (strlen($base) > 0 && $entry_id > 0){ ?>
	<a class='edit_entry' href='<?= $base ?>C=content_publish&M=entry_form&entry_id=<?= $entry_id ?><?php if ($channel_id > 0){ ?>&channel_id=<?= $channel_id ?><?php } ?>'><?= lang('cp_mn_edit_channel_entry') ?></a>
<?php } ?>