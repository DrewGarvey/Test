<div id="low-events-<?=$field_id?>" class="low-events<?php if ($all_day == 'y'): ?> low-all-day<?php endif; ?>"
	<?php foreach ($data AS $key => $val): ?> data-<?=$key?>="<?=$val?>"<?php endforeach; ?>>
	<input type="text" name="<?=$field_name?>[start_date]" value="<?=$start_date?>" class="date start-date" maxlength="10" />
	<input type="text" name="<?=$field_name?>[start_time]" value="<?=$start_time?>" class="time start-time" />
	&mdash;
	<input type="text" name="<?=$field_name?>[end_time]" value="<?=$end_time?>" class="time end-time" />
	<input type="text" name="<?=$field_name?>[end_date]" value="<?=$end_date?>" class="date end-date" maxlength="10" />
	<label><input type="checkbox" name="<?=$field_name?>[all_day]" value="y"
	<?php if ($all_day == 'y'): ?> checked="checked"<?php endif; ?> /> <?=lang('le_all_day')?></label>
</div>