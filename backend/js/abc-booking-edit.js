jQuery( "#end").on('change', function () {
	abc_checkDates();
});
jQuery( "#start").on('change', function () {
	abc_checkDates();
});

jQuery(document).on('click', '#postAbcBooking', function(){
	
	jQuery('#abc-booking-form').validate({ 
	    rules: {
	        email: {
	            required: {
			        depends: function(element) {
			          return jQuery("#radio-yes").is(":checked");
			        }
			    },
	            email: {
			        depends: function(element) {
			          return jQuery("#radio-yes").is(":checked");
			        }
			    }
	        }
	    },
		submitHandler: function (form) {
			form.submit();
			}
	});	
    
});

function abc_checkDates() {
	var extrasList = jQuery("input[name=abc-extras-checkbox]:checked").map(function () {return this.value;}).get().join(",");
	if( jQuery("#start").val() && jQuery("#end").val() ){
		jQuery('#abc_dateStatus').html('<span class="uk-text-muted"><i><br/>Loading...</i></span>');
		var from = jQuery( "#start").val();
		var to = jQuery( "#end").val();
		var persons = jQuery( "#persons").val();
		dataAvailability = {
			action: 'abc_booking_checkDates',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			from: from,
			to: to,
			calId: ajax_abc_bookings.calendar_id,
			bookingId: ajax_abc_bookings.booking_id,
			persons: persons
		};
		jQuery.post(ajax_abc_bookings.ajaxurl, dataAvailability, function (response){
				jQuery('#abc_dateStatus').html("<br/>"+response);
			});
		return false;
	}	
}