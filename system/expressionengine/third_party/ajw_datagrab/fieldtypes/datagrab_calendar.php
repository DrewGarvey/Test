<?php

/**
 * DataGrab Calendar fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_calendar extends Datagrab_fieldtype {

	function register_setting( $field_name ) {
		return array( 
			$field_name . "_calendar_start_time",
			$field_name . "_calendar_end_time", 
			$field_name . "_calendar_field" 
		);
	}

	function display_configuration( $field_name, $field_label, $field_type, $data ) {
		$config = array();

		$this->EE->db->select( "calendar_id, title" );
		$this->EE->db->from( "exp_channel_titles" );
		$this->EE->db->join( "exp_calendar_calendars", "exp_channel_titles.entry_id = exp_calendar_calendars.calendar_id" );
		$query = $this->EE->db->get();
		$calendars = array();
		foreach( $query->result_array() as $row ) {
			$calendars[ $row["calendar_id"] ] = $row[ "title" ];
		}

		$config["label"] = form_label($field_label)  . NBS .
		anchor("http://brandnewbox.co.uk/support/details/importing_into_calendar_fields_with_datagrab", "(?)", 'class="help"');

		$config["value"] = 
			"<p>Start time: " . NBS . 
			form_dropdown( 
			$field_name . "_calendar_start_time", 
			$data["data_fields"], 
			isset( $data["default_settings"]["cf"][$field_name . "_calendar_start_time"] ) ? 
				$data["default_settings"]["cf"][$field_name . "_calendar_start_time"] : '' 
			) . NBS . "</p>"
			. "<p>End time: " . NBS 
			. form_dropdown( 
			$field_name . "_calendar_end_time",
			$data["data_fields"], 
			isset( $data["default_settings"]["cf"][$field_name . "_calendar_end_time"] ) ? 
				$data["default_settings"]["cf"][$field_name . "_calendar_end_time"] : '' 
			) 
			. "</p><p>Add to calendar: " . NBS .
			form_dropdown( 
				$field_name, 
				$calendars, 
				(isset($data["default_settings"]["cf"][$field_name]) ? 
					$data["default_settings"]["cf"][$field_name]: '' )
			) . "</p>"
			;

		return $config;
	}

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {

		$data[ "field_id_" . $field_id ] = $DG->settings["cf"][ $field ];
		
		$data["interval"] = "select_dates";
		$data["calendar_id"] = $data[ "field_id_" . $field_id ];
		$data["calendar_calendar_id"] = $data[ "field_id_" . $field_id ];
		$data["type"] = "+";

		$data["ampm"] = "pm";
		$data["rule_id"] = array( "0" );
		$data["type"] = "+";
		$data["start_time"] = array();
		$data["end_time"] = array();
		$data["all_day"] = array( "" );
		$data["rule_type"] = array( "+" );

		$data["occurrences"] = array(
			"date" => array(),
			"start_time" => array(),
			"end_time" => array(),
			"all_day" => array(),
			"rule_type" => array(),
		);
		
		$start_field = $DG->settings["cf"][$field . "_calendar_start_time"];
		$first = TRUE;
		if( $DG->datatype->initialise_sub_item( 
			$item, $start_field, $DG->settings, $field ) ) {

			while( $subitem = $DG->datatype->get_sub_item( 
				$item, $start_field, $DG->settings, $field ) ) {
			
					$timestamp = $DG->_parse_date( $subitem );
					$start_time = date("Hi", $timestamp);
					$start_date = date("Ymd", $timestamp);

					if( $first ) {
						
						$data["ampm"] = "pm";
						$data["start_time"] = array( $start_time );
						$data["start_date"] = array( $start_date );
						$data["all_day"] = array( "" );
						$data["rule_type"] = array( "+" );
						
						$first = FALSE;
					}

					$data["occurrences"]["date"][] = $start_date;
					$data["occurrences"]["start_time"][] = $start_time;
					$data["occurrences"]["all_day"][] = "";
					$data["occurrences"]["rule_type"][] = "+";
					

			}
		}

		$end_field = $DG->settings["cf"][$field . "_calendar_end_time"];
		
		if( $DG->datatype->initialise_sub_item( 
			$item, $end_field, $DG->settings, $field ) ) {

			$first = TRUE;
			
			while( $subitem = $DG->datatype->get_sub_item( 
				$item, $end_field, $DG->settings, $field ) ) {
			
					$timestamp = $DG->_parse_date( $subitem );
					$end_time = date("Hi", $timestamp);
					$end_date = date("Ymd", $timestamp);

					if( $first ) {
						
						$data["end_time"] = array( $end_time );
						$data["end_date"] = array( $end_date );
						
						$first = FALSE;
					}

					$data["occurrences"]["end_time"][] = $end_time;
	
			}
		}

		//print_r( $data ); exit;

	}

}

?>