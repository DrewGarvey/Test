<?php

/**
 * DataGrab exp-resso Store fieldtype class
 *
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Datagrab_store extends Datagrab_fieldtype {

	function register_setting( $field_name ) {
		return array( 
			$field_name . "_store_sku", 
			$field_name . "_store_sale_price",
			$field_name . "_store_sale_price_enabled",
			$field_name . "_store_weight",
			$field_name . "_store_free_shipping"
		);
	}

	function display_configuration( $field_name, $field_label, $field_type, $data ) {
		$config = array();
		$config["label"] = "<p>" .
		form_label($field_label);
		/*  . NBS .
		anchor("http://brandnewbox.co.uk/support/details/importing_into_playa_fields_with_datagrab", "(?)", 'class="help"');
		*/
		$config["value"] = "Price: " . NBS . form_dropdown( 
			$field_name, $data["data_fields"], 
			isset( $data["default_settings"]["cf"][$field_name] ) ? 
				$data["default_settings"]["cf"][$field_name] : '' 
			) . 
			"</p><p>" . "SKU: " . NBS .
			form_dropdown( 
				$field_name . "_store_sku", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_store_sku"]) ? 
					$data["default_settings"]["cf"][$field_name . "_store_sku" ]: '' )
			) .
			"</p><p>" . "Sale Price: " . NBS .
			form_dropdown( 
				$field_name . "_store_sale_price", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_store_sale_price"]) ? 
					$data["default_settings"]["cf"][$field_name . "_store_sale_price" ]: '' )
			) .
			"</p><p>" . "Sale Price Enabled?: " . NBS .
			form_dropdown( 
				$field_name . "_store_sale_price_enabled", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_store_sale_price_enabled"]) ? 
					$data["default_settings"]["cf"][$field_name . "_store_sale_price_enabled" ]: '' )
			) .
			"</p><p>" . "Weight: " . NBS .
			form_dropdown( 
				$field_name . "_store_weight", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_store_weight"]) ? 
					$data["default_settings"]["cf"][$field_name . "_store_weight" ]: '' )
			) .
			"</p><p>" . "Free shipping: " . NBS .
			form_dropdown( 
				$field_name . "_store_free_shipping", 
				$data["data_fields"], 
				(isset($data["default_settings"]["cf"][$field_name . "_store_free_shipping"]) ? 
					$data["default_settings"]["cf"][$field_name . "_store_free_shipping" ]: '' )
			) .
			"</p>";
		return $config;
	}


	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {

		/*
		[field_id_2] => store
		[store_product_field] => Array
		        (
		            [regular_price] => 10.99
		            [sale_price] => 
		            [sale_price_enabled] => 
		            [sale_start_date] => 
		            [sale_end_date] => 
		            [stock] => Array
		                (
		                    [0] => Array
		                        (
		                            [sku] => SKU123
		                            [min_order_qty] => 
		                        )

		                )

		            [weight] => 
		            [dimension_l] => 
		            [dimension_w] => 
		            [dimension_h] => 
		            [handling] => 
		            [free_shipping] => 
		            [tax_exempt] => 
		        )
		*/

		$data[ "field_id_" . $field_id ]= "store";
		$_POST[ "store_product_field" ] = array(
			"regular_price" => $DG->datatype->get_item( $item, $DG->settings["cf"][ $field ] ),
			"sale_price" => $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_store_sale_price" ] ),
			"sale_price_enabled" => $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_store_sale_price_enabled" ] ),
			"sale_start_date" => "",
			"sale_end_date" => "",
			"stock" => array(
				array(
					"sku" => $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_store_sku" ] ),
					"min_order_qty" => ""
					)
			),
			"weight" => $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_store_weight" ] ),
			"dimension_l" => "",
			"dimension_w" => "",
			"dimension_h" => "",
			"handling" => "",
			"free_shipping" => $DG->datatype->get_item( $item, $DG->settings["cf"][ $field."_store_free_shipping" ] ),
			"tax_exempt" =>	""
			);
		
	}
	
	function rebuild_post_data( $DG, $field_id, &$data, $existing_data ) {
	
		// Rebuild selections array
		$data[ "field_id_".$field_id ] = "store";
		$_POST[ "store_product_field" ] = array(
			"regular_price" => "",
			"sale_price" => "",
			"sale_price_enabled" => "",
			"sale_start_date" => "",
			"sale_end_date" => "",
			"stock" => array(
				array(
					"sku" => "",
					"min_order_qty" => ""
					)
			),
			"weight" => "",
			"dimension_l" => "",
			"dimension_w" => "",
			"dimension_h" => "",
			"handling" => "",
			"free_shipping" => "",
			"tax_exempt" =>	""
		);

		$this->EE->db->select( "sku, regular_price, sale_price, sale_price_enabled, weight, free_shipping" );
		$this->EE->db->from( "exp_store_products" );
		$this->EE->db->join( "exp_store_stock", "exp_store_products.entry_id = exp_store_stock.entry_id" );
		$this->EE->db->where( "exp_store_products.entry_id", $existing_data["entry_id"] );
		$query = $this->EE->db->get();

		if( $query->num_rows() > 0 ) {
			$row = $query->row_array();
			$_POST[ "store_product_field" ] = array(
				"regular_price" => $row["regular_price"],
				"sale_price" => $row["sale_price"],
				"sale_price_enabled" => $row["sale_price_enabled"],
				"sale_start_date" => "",
				"sale_end_date" => "",
				"stock" => array(
					array(
						"sku" => $row["sku"],
						"min_order_qty" => ""
						)
				),
				"weight" => $row["weight"],
				"dimension_l" => "",
				"dimension_w" => "",
				"dimension_h" => "",
				"handling" => "",
				"free_shipping" => $row["free_shipping"],
				"tax_exempt" =>	""
			);
		}
	}
}

?>