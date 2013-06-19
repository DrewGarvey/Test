<div id="expresso_global_settings">

	<div id="expresso_export_settings">
		<h3><?=lang('export_settings')?></h3>
		<textarea readonly="readonly"><?=$export_settings?></textarea>
		<a href="#" class="expresso_close_settings" />Close</a>
	</div>

	<div id="expresso_import_settings">
		<h3><?=lang('import_settings')?></h3>
		<textarea name="import_settings"></textarea>
		<input type="submit" name="import" class="submit" value="<?=lang('import')?>" />
		<?=lang('or')?> <a href="#" class="expresso_close_settings" /><?=lang('close')?></a>	
	</div>


<?php
$docs = array(
	'custom_toolbar' => 'http://docs.cksource.com/CKEditor_3.x/Developers_Guide/Toolbar',
	'styles' => 'http://docs.cksource.com/CKEditor_3.x/Developers_Guide/Styles'
);

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('global_settings'), 'width' => '15%'),
    '',
    array('width' => '15%')
);

foreach ($settings as $key => $val)
{
	switch ($key) 
	{
		case 'license_number':
			$license = ($license == 'invalid_license') ? '<i class="notice">'.lang($license).'</i>' : '<i>'.lang($license).'</i>';
			$this->table->add_row('<label>'.lang($key).'&nbsp;<strong class="notice">*</strong></label>', $val, $license);
			break;
		
		case 'uiColor':
			$this->table->add_row('<label>'.lang($key).'</label>', $val, '<i>'.lang('enter_for_transparent').'</i>');
			break;
		
		case 'contentsCss':
		case 'custom_toolbar':
		case 'styles':
			$this->table->add_row('<label>'.lang($key).'</label>'.(isset($docs[$key]) ? ' (<i><a href="'.$docs[$key].'" target="_blank">docs</a></i>)' : ''), $val, '<i><a href="#" class="add_sample_code" id="'.$key.'">'.lang('add_sample_code').'</a></i>');
			break;
			
		default:	
			$this->table->add_row('<label>'.lang($key).'</label>', $val, '');
			break;
	}
}

echo $this->table->generate(); 
?>

</div>