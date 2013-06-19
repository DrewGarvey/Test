<?php

/**
 * DataGrab Assets fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_assets extends Datagrab_fieldtype {

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
		
		$data[ "field_id_" . $field_id ] = array();
		
		// Can the current datatype handle sub-loops (eg, XML)?
		if( $DG->datatype->datatype_info["allow_subloop"] ) {
		
			// Check this field can be a sub-loop
			if( $DG->datatype->initialise_sub_item( 
				$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
		
				// Loop over sub items
				while( $subitem = $DG->datatype->get_sub_item( 
					$item, $DG->settings["cf"][ $field ], $DG->settings, $field ) ) {
		
					$data[ "field_id_" . $field_id ][] = $subitem;
		
				}
			}
		}	
		
		/*
		[field_id_40] => Array
		        (
		            [0] => {filedir_1}bnb1.png
		            [1] => {filedir_1}bnb2.png
		            [2] => {filedir_1}bnb3.png
		            [3] => {filedir_1}bnb4.png
		            [4] => {filedir_1}bnb5.png
		        )
		*/		
	}

	function rebuild_post_data( $DG, $field_id, &$data, $existing_data ) {

		$data[ "field_id_" . $field_id ] = array();

		$this->EE->db->select( "exp_assets.file_path" );
		$this->EE->db->from( "exp_assets" );
		$this->EE->db->join( "exp_assets_entries", "exp_assets.asset_id = exp_assets_entries.asset_id" );
		$this->EE->db->where( "entry_id", $existing_data["entry_id"] );
		$this->EE->db->order_by( "asset_order" );
		$query = $this->EE->db->get();
		
		foreach( $query->result_array() as $row ) {
			$data[ "field_id_" . $field_id ][] = $row["file_path"];
		}

	}

}

?>