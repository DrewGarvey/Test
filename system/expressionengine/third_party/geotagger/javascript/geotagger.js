jQuery(function($) {
	
	// build our html
	var geo_wrap = $(document.createElement('div')).attr('id', 'geo_wrap');
	var geo_details = $(document.createElement('div')).attr('id', 'geo_details');
	var geo_paragraph = $(document.createElement('p'));
	var geo_form = $(document.createElement('div')).attr('id', 'geo_form');
	var geo_submit = $(document.createElement('a')).attr('id', 'geo_tag').addClass('btn').attr('href','#').attr('title', NLGEO.label_btn_geo).html(NLGEO.label_btn_geo);
	var geo_message = $(document.createElement('p')).attr('id', 'geo_messages');
	geo_message.html(NLGEO.label_msg_geo);
	// geo_details.append('<p>' +  + '</p>');
	geo_details.append(geo_message);
	geo_details.append(geo_form);
	geo_details.append(geo_paragraph);
	geo_paragraph.append(geo_submit);
	geo_wrap.append(geo_details);
	geo_wrap.append($(document.createElement('div')).attr('id', 'geo_map_canvas'));
	$('#hold_field_geotagger__geotagger_field_ids input').replaceWith(geo_wrap);
	
	if (NLGEO.settings_exist == 'y')
	{

		// pull fields into geotagger tab 
		if (NLGEO.inline_fields == 1)
		{
			geo_form.append($("#hold_field_"+NLGEO.address_field));
			geo_form.append($("#hold_field_"+NLGEO.city_field));
			geo_form.append($("#hold_field_"+NLGEO.state_field));
			geo_form.append($("#hold_field_"+NLGEO.zip_field));
			geo_form.append($("#hold_field_"+NLGEO.lat_field));
			geo_form.append($("#hold_field_"+NLGEO.lng_field));
			geo_form.append($("#hold_field_"+NLGEO.zoom_field));
		}
	
		$("#geo_tag").bind("click", function(e) {
			e.preventDefault();
			nl_map(false);
		});
	
		if (NLGEO.existing_entry == 1 && NLGEO.lat_field != 0 && $("#field_id_"+NLGEO.lat_field).val().length > 0)
		{
			$('#menu_geotagger a').bind("click", function(e) {
				nl_map(true);
			});		
		}
	}else
	{
		geo_wrap.html(NLGEO.msg_no_settings);
	}
	
	var nl_map = function(existing) {

		// get the inputs
		var address, city, state, zip, zoom, default_zoom;
		var field_prefix = "field_id_";
		
		if (NLGEO.address_field != 0)
		{
			address = $("#"+field_prefix+NLGEO.address_field);	
		}

		if (NLGEO.city_field != 0)
		{
			city = $("#"+field_prefix+NLGEO.city_field);	
		}		

		if (NLGEO.state_field != 0)
		{
			state = $("#"+field_prefix+NLGEO.state_field);	
		}
		
		if (NLGEO.zip_field != 0)
		{
			zip = $("#"+field_prefix+NLGEO.zip_field);	
		}
		
		if (NLGEO.zoom_field != 0)
		{
			zoom = $("#"+field_prefix+NLGEO.zoom_field);	
		}
		
		// set default zoom
		if (NLGEO.zoom_field != 0 && zoom.val().length > 0)
		{
			default_zoom = parseInt(zoom.val());
		}else
		{
			default_zoom = NLGEO.default_zoom;
		}
						
		// map options
	    var map_options = {
	      zoom: default_zoom,
	      mapTypeId: google.maps.MapTypeId.ROADMAP,
		  scrollwheel: false
	    }

		// geocoder
		var geocoder = new google.maps.Geocoder();
		
		// google map
		var map = new google.maps.Map(document.getElementById("geo_map_canvas"), map_options);
		
		// fix for showing google map from initial hidden element
		google.maps.event.trigger(map, 'resize');
		
	    geocoder.geocode( { 'address': nl_geo_parse_address()}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var current_point;
				// get lat and lng fields
				var current_lat = $("#"+field_prefix+NLGEO.lat_field);
				var current_lng = $("#"+field_prefix+NLGEO.lng_field);
				
				// get messages element
				var geo_messages = $("#geo_messages");
				
				// get the point
				if (current_lat.val().length > 0 && current_lng.val().length > 0)
				{
					current_point = new google.maps.LatLng(current_lat.val(), current_lng.val());
				}else
				{
					current_point = results[0].geometry.location;				
					// set lat/lng values
					current_lat.val(current_point.lat().toString());
					current_lng.val(current_point.lng().toString());
				}
								
				// set center of map
				map.setCenter(current_point);
				
				// update message
				if (! existing)
				{
					geo_messages.removeClass("geo_error").addClass("geo_success").html(NLGEO.msg_lat + current_point.lat().toString() + "<br/>" + NLGEO.msg_lng + current_point.lng().toString());
				}		
				
				// add marker
				var marker = new google.maps.Marker({
				    map: map, 
				    position: current_point,
					draggable: true
				});
				
				// add marker dragend listener
		        google.maps.event.addListener(marker, 'dragend', function() {
					var point = marker.getPosition();
					$("#"+field_prefix+NLGEO.lat_field).val(point.lat().toFixed(5));
					$("#"+field_prefix+NLGEO.lng_field).val(point.lng().toFixed(5));
					geo_messages.removeClass("geo_error").addClass("geo_success").html(NLGEO.msg_lat + point.lat().toFixed(5) + "<br/>" + NLGEO.msg_lng + point.lng().toFixed(5));
		        });
			
				// add zoom changed listener
				if (NLGEO.zoom_field != 0)
				{				
					// set zoom field value
					$("#"+field_prefix+NLGEO.zoom_field).val(map.getZoom());
					
					google.maps.event.addListener(map, 'zoom_changed', function() {
					    $("#"+field_prefix+NLGEO.zoom_field).val(map.getZoom());
					});				
				}

			} else {
			  $("#geo_messages").removeClass("geo_success").addClass("geo_error").html(NLGEO.msg_geo_error + status);
			}
	    });
		
	}
});

function nl_geo_parse_address()
{
	var field_values = new Array();
	var field_prefix = "field_id_";

	// get the field values
	var addr_field = (NLGEO.address_field != 0) ? $("#"+field_prefix+NLGEO.address_field).val() : false;
	if ( ! addr_field) {
		addr_field =  $("select[name='"+field_prefix+NLGEO.address_field+"']").val();
		if (addr_field === undefined) {
			addr_field = false;
		}
	}				

	var city_field = (NLGEO.city_field != 0) ? $("#"+field_prefix+NLGEO.city_field).val() : false;					
	if ( ! city_field) {
		city_field =  $("select[name='"+field_prefix+NLGEO.city_field+"']").val();					
		if (city_field === undefined) {
			city_field = false;
		}
	}

	var st_field = (NLGEO.state_field != 0) ? $("#"+field_prefix+NLGEO.state_field).val() : false;				
	if ( ! st_field) {
		st_field =  $("select[name='"+field_prefix+NLGEO.state_field+"']").val();
		if (st_field === undefined) {
			st_field = false;
		}
	}

	var zip_field = (NLGEO.zip_field != 0) ?  $("#"+field_prefix+NLGEO.zip_field).val() : false;
		
	field_values.push(addr_field);
	field_values.push(city_field);
	field_values.push(st_field);
	field_values.push(zip_field);
	
	var address = "";
	
	for(var i=0;i<field_values.length;i++)
	{
		val = field_values[i];
		if (val)
		{
			address += val;
			address += (i+1 != field_values.length) ? ", " : "";				
		}
	}
	return address;
}