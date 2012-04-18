<div id="FormsChoices">
	<div class="top"><?=lang('form:bulkchoice:exp')?></div>

	<div class="wrapper">
		<div class="left">
		<?php foreach($lists as $list_label => $list): ?>
			<a href="#"><?=$list_label?> <span><?=$list?></span></a>
		<?php endforeach; ?>
		</div>
		<div class="middle">&nbsp;</div>
		<div class="right">
			<textarea id="FormsChoicesText"></textarea>
			<small><?=lang('form:option_setting_ex')?></small>
		</div>
		<br clear="all"><br>
		<button class="FormsBtn"><?=lang('form:insert_choices')?></button>
	</div>
</div>