<div id="content">

<?php echo form_open($action_url); ?>

	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th colspan="2">
					<?php echo lang('label_site_settings'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td style="width:50%;"><strong><?php echo lang('enable'); ?></strong></td>
				<td><?php echo form_dropdown('enable', array('y' => lang('yes'), 'n' => lang('no')), (isset($settings['enabled']) ? $settings['enabled'] : 'yes')); ?></td>
			</tr>
		</tbody>
	</table>
	
	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0" style="display:none;">
		<thead>
			<tr>
				<th colspan="2">
					<?php echo lang('label_google_maps'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td style="width:50%;"><strong><?php echo lang('label_google_maps_api_key'); ?></strong></td>
				<td>
					<?php
						echo form_input(array(
							'name'			=> 'google_maps_api_key',
							'id'			=> 'google_maps_api_key',
							'value'			=> (isset($settings['google_maps_api_key']) ? $settings['google_maps_api_key'] : ''),
							'maxlength'		=> '120',
							'size'			=> '200',
							'style'			=> 'width:65%',
							'class'			=> 'input'
							)
						);
					?>
				</td>
			</tr>
		</tbody>
	</table>
	
	<table class="mainTable padTable channelSettings" border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th colspan="2">
					<?php echo lang('label_channel_settings'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
<?php
	foreach($channels as $channel) :
		$channel_id = $channel['channel_id'];
		$has_fields = isset($channel['fields']) ? TRUE : FALSE;

		if ($has_fields)
		{
			$fields_blank = $channel['fields'];
			$fields_blank[''] = lang('label_fm_na');
			$channel_settings = isset($settings['channels'][$channel['channel_id']]) ? $settings['channels'][$channel['channel_id']] : FALSE;
			$display_tab = isset($channel_settings['display_tab']) ? $channel_settings['display_tab'] : 'n';
			$class = (isset($class) && $class == 'even') ? 'odd' : 'even'; 
?>
			<tr class="<?php echo $class; ?>">
				<td style="width:50%;"><strong><?php echo $channel['channel_title']; ?></strong></td>
				<td>
					<table border="0" cellspacing="0" cellpadding="0">
						<tbody>
							<tr>
								<td>
									<small><?php echo lang('label_display_tab'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][display_tab]',
											array( 'y' => lang('yes'), 'n' => lang('no')),
											$display_tab,
											" onchange='toggleFieldMappings(this.value, {$channel_id})'"
										);
									?>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_map_zoom_level'); ?>:</small>
								</td>
								<td>
									<?php echo form_input('channels['.$channel_id.'][zoom_level]', isset($channel_settings['zoom_level']) ? $channel_settings['zoom_level'] : '13'); ?>
								</td>
							</tr>

							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_show_fields_in_geo'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][show_fields_in_geo]',
											array( 'y' => lang('yes'), 'n' => lang('no')),
											(isset($channel_settings['show_fields_in_geo']) ? $channel_settings['show_fields_in_geo'] : 'y')
										);
									?>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td colspan="2">
									<strong><small><?php echo lang('label_fm_heading'); ?></small></strong>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_fm_address'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][address]',
											$channel['fields'],
											isset($channel_settings['address']) ? $channel_settings['address'] : ''
										);
									?>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_fm_city'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][city]',
											$fields_blank,
											isset($channel_settings['city']) ? $channel_settings['city'] : ''
										);
									?>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_fm_state'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][state]',
											$fields_blank,
											isset($channel_settings['state']) ? $channel_settings['state'] : ''
										);
									?>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_fm_zip'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][zip]',
											$fields_blank,
											isset($channel_settings['zip']) ? $channel_settings['zip'] : ''
										);
									?>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_fm_latitude'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][latitude]',
											$channel['fields'],
											isset($channel_settings['latitude']) ? $channel_settings['latitude'] : ''
										);
									?>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_fm_longitude'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][longitude]',
											$channel['fields'],
											isset($channel_settings['longitude']) ? $channel_settings['longitude'] : ''
										);
									?>
								</td>
							</tr>
							<tr class="field_map_<?php echo $channel_id; ?>"<?php if ($display_tab != 'y') : ?> style="display:none"<?php endif; ?>>
								<td>
									<small><?php echo lang('label_fm_zoom'); ?>:</small>
								</td>
								<td>
									<?php
										echo form_dropdown(
											'channels['.$channel_id.'][zoom]',
											$fields_blank,
											isset($channel_settings['zoom']) ? $channel_settings['zoom'] : ''
										);
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		<?php } ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<style type="text/css">
	table.channelSettings td table td, table.channelSettings td table td:last-child {
		border: none !important;
	}
	table.channelSettings td {
		vertical-align: top;
	}
	</style>
	<script type="text/javascript">
	/* <![CDATA[ */
	function toggleFieldMappings(val, channel_id) 
	{
		if (val == 'n') 
		{
			$('.field_map_'+channel_id).hide();
		}else{						
			$('.field_map_'+channel_id).show();
		}					
	}
	/* ]]> */
	</script>
	<?php echo form_submit('submit', lang('submit'), 'class="submit"'); ?>
<?php echo form_close(); ?>
<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/geotagger/views/index.php */
?>