<?php

/**
 * DataGrab MX Google Map fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_mx_google_map extends Datagrab_fieldtype {

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {

		$field_data = $DG->datatype->get_item( $item, $DG->settings["cf"][ $field ] );
		
		$coords = explode( "|", $field_data );
		
		$data[ "field_id_" . $field_id ] = array(
			"field_data" => $field_data,
			"order" => array(
				"0" => 564
			),
			"564" => array(
				"address" => "",
				"city" => "",
				"zipcode" => "",
				"state" => "",
				"long" => $coords[1],
				"icon" => "",
				"lat" => $coords[0]
			)
		);

	}

}

?>