<?php 
	echo form_open( $form_action ); 
	echo form_hidden( "datagrab_step", "settings" ); 
?>

<h3>Import Settings</h3>

<?php 

$this->table->set_template($cp_table_template);
$this->table->set_heading('Setting', 'Value');

$import = array(
	array( 
		form_label('Channel', 'channel')  .
		'<div class="subtext">Select the channel to import the data into</div>', 
		form_dropdown('channel', $channels, $channel, ' id="channel"')
		)
);

echo $this->table->generate($import);

$this->table->clear()

?>

<h3>Datatype settings</h3>

<?php 

$this->table->set_template($cp_table_template);
$this->table->set_heading('Setting', 'Value');
echo $this->table->generate($settings);

?>

<input type="submit" value="Check settings" class="submit" />

<?php echo form_close(); ?>
