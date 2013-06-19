<?php

/**
 * DataGrab Matrix fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_matrix extends Datagrab_fieldtype {

	/**
	 * Register a setting so it can be saved
	 *
	 * @param string $field_name 
	 * @return void
	 */
	function register_setting( $field_name ) {
		return array( 
			$field_name . "_columns", 
			$field_name . "_unique" 
		);
	}

	/**
	 * Create the form elements to map matrix fields
	 *
	 * @param string $field_name 
	 * @param string $field_label 
	 * @param string $field_type 
	 * @param string $data 
	 * @return void
	 * @author Andrew Weaver
	 */
	function display_configuration( $field_name, $field_label, $field_type, $data ) {
		$config = array();
		$config["label"] = form_label($field_label)
			. BR . anchor("http://brandnewbox.co.uk/support/details/datagrab_and_matrix_fields", "(&#946;eta notes)", 'class="help"');

		$this->EE->db->select( "col_id, col_label" );
		$query = $this->EE->db->get( "exp_matrix_cols" );
		$matrix_columns = array();
		foreach( $query->result_array() as $row ) {
			$matrix_columns[ $row["col_id"] ] = $row["col_label"];
		}

		$cells = form_hidden( $field_name, "1" );
		foreach( $data["field_settings"][ $field_name ][ "col_ids"] as $col_id ) {
			if( isset($data["default_settings"]["cf"][ $field_name . "_columns" ]) ) {
				$default_cells = $data["default_settings"]["cf"][ $field_name . "_columns" ];
			} else {
				$default_cells = array();
			}
			$cells .= "<p>" . 
				$matrix_columns[ $col_id ] . NBS . ":" . NBS . 
				form_dropdown( 
					$field_name . "_columns[" . $col_id . "]", 
					$data["data_fields"],
					isset($default_cells[$col_id]) ? $default_cells[$col_id] : ''
				) . /* NBS .
				form_radio( 
					$field_name . "_unique", 
					$col_id,
					isset($data["default_settings"]["cf"][ $field_name . "_unique" ]) && 
						$data["default_settings"]["cf"][ $field_name . "_unique" ] == $col_id ? 
						$data["default_settings"]["cf"][ $field_name . "_unique" ] : ''
				) . */
				"</p>";
		}

		$column_options = array();
		$column_options["-1"] = "Delete all existing rows";
		$column_options["0"] = "Keep existing rows and append new";
		$sub_options = array();
		foreach( $data["field_settings"][ $field_name ][ "col_ids"] as $col_id ) {
			$sub_options[ $col_id ] = $matrix_columns[ $col_id ];
		}
		$column_options["Update the row if this column matches:"] = $sub_options;
		
		$cells .= "<p>" . 
			"Action to take when an entry is updated: " .
			form_dropdown( 
				$field_name . "_unique", 
				$column_options,
				(isset($data["default_settings"]["cf"][$field_name . "_unique"]) ? 
					$data["default_settings"]["cf"][$field_name . "_unique" ]: '' )
			) .
			"</p>";

		$config["value"] = $cells;
		return $config;
	}
	
	/**
	 * Build matrix data structure for a new set of rows
	 *
	 * @param string $DG 
	 * @param string $item 
	 * @param string $field_id 
	 * @param string $field 
	 * @param string $data 
	 * @param string $update 
	 * @return void
	 * @author Andrew Weaver
	 */
	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
	}

	function final_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		
		// Fetch fieldtype settings
		// We need to know which matrix columns are used in this matrix
		$DG->_get_channel_fields_settings( $field_id );
		$fs = $this->EE->api_channel_fields->settings[ $field_id ]["field_settings"];
		$field_settings = (unserialize(base64_decode($fs)));

		// Get matrix column details (eg, the column type - playa, text, etc)
		$this->EE->db->select( "*" );
		$this->EE->db->where_in( "col_id", $field_settings["col_ids"] );
		$query = $this->EE->db->get( "exp_matrix_cols" );
		$columns = array();
		foreach( $query->result_array() as $row ) {
			$columns[ $row["col_id"] ] = $row;
		}
		
		// $fields contains a list of matrix columns mapped to data elements
		// eg, $fields[3] => 5 means map data element 5 to matrix column 3
		$fields = $DG->settings["cf"][ $field . "_columns" ];
		
		// Initialise empty matrix array
		$matrix = array();
		$col_num = 0;
		$empty = TRUE;
		
		// Loop over columns in matrix
		foreach( $field_settings["col_ids"] as $col_id ) {
		
			// Loop over data items
			if( $DG->datatype->initialise_sub_item( 
				$item, $fields[ $col_id ], $DG->settings, $field ) ) {

					$row_num = 0;

					$subitem = $DG->datatype->get_sub_item( 
						$item, $fields[ $col_id ], $DG->settings, $field );

						while( $subitem != FALSE ) {

							// Pre-fill row matrix with empty values
							if( ! isset( $matrix[ $row_num ] ) ) {
								$matrix[ $row_num ] = array();
								foreach( $field_settings["col_ids"] as $c_id ) {
									$matrix[ $row_num ][ "col_id_".$c_id ] = "";
								}
							}

							// Add data to row matrix
							switch( $columns[ $col_id ]["col_type"] ) {
								case "playa" : {
									$matrix[ $row_num ][ "col_id_".$col_id ] = array();
									$this->EE->db->select( "entry_id" );
									$this->EE->db->where( "title", $subitem );
									$query = $this->EE->db->get( "exp_channel_titles" );
									if( $query->num_rows() > 0 ) {
										$row = $query->row_array();
										$matrix[ $row_num ][ "col_id_".$col_id ] = array(
											"selections" => array(
												"", $row["entry_id"]
											)
										);
										$empty = FALSE;
									}
									break;
								}
								case "file" : {
									$matrix[ $row_num ][ "col_id_".$col_id ] = array(
										"filedir" => "",
										"filename" => ""
									);
									if( preg_match('/{filedir_([0-9]+)}/', $subitem, $matches) ) {
										$matrix[ $row_num ][ "col_id_".$col_id ] = array(
											"filedir" => $matches[1],
											"filename" => str_replace($matches[0], '', $subitem )
										);
										$empty = FALSE;
									}
									break;
								}
								case "date": {
									$timestamp = $DG->_parse_date( $subitem );
									$date = date("Y-m-d g:i A",  $timestamp);// 2011-07-01 1:02 PM
									$matrix[ $row_num ][ "col_id_".$col_id ] = $date;
									break;
								}
								default: {
									$matrix[ $row_num ][ "col_id_".$col_id ] = $subitem;
									$empty = FALSE;
								} 

							}

						$subitem = $DG->datatype->get_sub_item( 
							$item, $fields[ $col_id ], $DG->settings, $field );

						$row_num++;
					}
		
			}

			$col_num++;		
		}
		
		// Is this updating an existing entry?
		if( $update ) {
			
			// Fetch existing data
			$old_matrix = $this->_rebuild_matrix_data( $update, $DG, $field_id );

			// Find out what to do with existing data (delete or keep?)
			$unique = 0;
			if( isset($DG->settings["cf"][ $field . "_unique" ]) ) {
				$unique = $DG->settings["cf"][ $field . "_unique" ];
			}

			// Is this the first update in this import?
			if( ! in_array( $update, $DG->entries ) ) {
				if( $unique == -1 ) {
					// Delete existing matrix rows
					$data[ "field_id_" . $field_id ]["deleted_rows"] = array();
					foreach( $old_matrix as $key => $value ) {
						if( substr( $key, 0, 7 ) == "row_id_" ) {
							$data[ "field_id_" . $field_id ]["deleted_rows"][] = $key;
						}
					}
				}
			}

		}
		
		// Create data array for generating the matrix field
		if( ! $empty ) {

			$data[ "field_id_" . $field_id ][ "row_order" ] = array();
			
			if( $update ) {
				if( $unique != -1 ) {
					foreach( $old_matrix as $key => $mrow ) {
						if( substr( $key, 0, 7 ) == "row_id_" ) {
							$data[ "field_id_" . $field_id ][ $key ] = $mrow;
							$data[ "field_id_" . $field_id ][ "row_order" ][] = $key;
						}
					}
				}
			}
			
			foreach( $matrix as $row_num => $mrow ) {
				
				$found = FALSE;
				if( $update ) {
					if( $unique > 0 ) {
						// Check whether this is a new row or an update
						foreach( $old_matrix as $key => $row ) {
							if( substr( $key, 0, 7 ) == "row_id_" ) {
								if( $row[ "col_id_" . $unique ] == $mrow[ "col_id_" . $unique ] ) {
									$data[ "field_id_" . $field_id ][ $key ] = $mrow;
									$found = TRUE;
								}
							}
						}
					}
				}
				
				if( ! $found ) {
					$data[ "field_id_" . $field_id ][ "row_new_".$row_num ] = $mrow;
					$data[ "field_id_" . $field_id ][ "row_order" ][] = "row_new_".$row_num;
				}
				
			}
					
		}
		
		if( !isset( $data[ "field_id_" . $field_id ] ) ) {
			$data[ "field_id_" . $field_id ] = array();
			$data[ "field_id_" . $field_id ][ "row_order" ] = array();
		}
		
		// print_r( $data[ "field_id_" . $field_id ] ); exit;
		
		/*
		[field_id_63] => Array (
			[row_order] => Array (
				[0] => row_new_0
				[1] => row_id_5571
			)
			[row_new_0] => Array (
				[col_id_8] => Array (
					[filedir] => 1
					[filename] => bnb4.png
				)
				[col_id_7] => fdsfsdfsd
			)
			[row_id_5571] => Array (
				[col_id_8] => Array (
					[filedir] => 1
					[filename] => bnb3.png
				)
				[col_id_7] => Label 1
			)
			[deleted_rows] => Array (
				[0] => row_id_5568
			)
		)
		*/
		
	}

	// Rebuild array (format playa, dates and files)
	function _rebuild_matrix_data( $entry_id, $DG, $field_id ) {
		// Find columns for this field
		// Fetch fieldtype settings
		$DG->_get_channel_fields_settings( $field_id );
		$fs = $this->EE->api_channel_fields->settings[ $field_id ]["field_settings"];
		$field_settings = (unserialize(base64_decode($fs)));

		$col_ids = $field_settings["col_ids"];
		
		// Get matrix column details
		$this->EE->db->select( "*" );
		$this->EE->db->where_in( "col_id", $field_settings["col_ids"] );
		$query = $this->EE->db->get( "exp_matrix_cols" );
		$columns = array();
		foreach( $query->result_array() as $row ) {
			$columns[ $row["col_id"] ] = $row;
		}
		
		// Get existing matrix entries
		$this->EE->db->select( "*" );
		$this->EE->db->where( "entry_id", $entry_id );
		$this->EE->db->order_by( "row_order" );
		$query = $this->EE->db->get( "exp_matrix_data" );

		$data = array();
		$data["row_order"] = array();
		foreach( $query->result_array() as $row ) {
			$matrix_row = array();
			foreach( $col_ids as $col_id ) {
				switch ( $columns[ $col_id ][ "col_type" ] ) {
					case "playa": {
						$playa = $row[ "col_id_" . $col_id ];
						$playa = substr( $playa, 1, strpos( $playa, ']' )-1 );
						$matrix_row["col_id_" . $col_id ]["selections"] = array(
							"0" => "",
							"1" => $playa
						);
						break;
					}
					case "date": {
						$matrix_row["col_id_" . $col_id ] = $this->EE->localize->set_human_time( $row[ "col_id_" . $col_id ] );
						break;
					}
					case "file": {
						$filename = $row[ "col_id_" . $col_id ];
						$matrix_row[ "col_id_" . $col_id ] = "";
						if( preg_match('/{filedir_([0-9]+)}/', $filename, $matches) ) {
							$matrix_row[ "col_id_" . $col_id ] = array(
								"filedir" => $matches[1],
								"filename" => str_replace($matches[0], '', $filename )
							);
						}
						break;
					}
					default: {
						$matrix_row["col_id_" . $col_id ] = $row[ "col_id_" . $col_id ];
					}
				}
			}

			$data[ "row_id_" . $row["row_id"] ] = $matrix_row;
			$data[ "row_order" ][] = "row_id_" . $row["row_id"];
		}
		//print_r( $data );
		return $data;
	}

}

?>