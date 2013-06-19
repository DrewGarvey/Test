<?php

/**
 * DataGrab VMG Chosen Member fieldtype class
 * see: https://github.com/vector/VMG-Chosen-Member
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_vmg_chosen_member extends Datagrab_fieldtype {

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {

		$field_data = $DG->datatype->get_item( $item, $DG->settings["cf"][ $field ] );		
		$data[ "field_id_" . $field_id ] = explode( ",", $field_data );

	}

}

?>