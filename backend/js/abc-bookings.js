// Availability table
function abcFormatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}

function abcTriggerButtons(trigger){
	jQuery('.abc-availability-table-button-right').attr('disabled',trigger);
	jQuery('.abc-availability-table-button-left').attr('disabled',trigger);
	jQuery('.abcMonthWrapper').attr('disabled',trigger);
	jQuery('.abcYearWrapper').attr('disabled',trigger);
}

jQuery('.abcAvailabilityTable').on('click', '.abc-availability-table-button-right', function(){
	var startdate = new Date(+new Date(jQuery(this).data('startdate')) + 12096e5); // + 14 days
	abcTriggerButtons(true);
	data = {
			action: 'abc_booking_getAvailabilityTable',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			startdate: abcFormatDate(startdate)
		};
		jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
			jQuery('#abc_AvailabilityTable').html(response);
			abcTriggerButtons(false);
		});
		return false;
});

jQuery('.abcAvailabilityTable').on('click', '.abc-availability-table-button-left', function(){
	var startdate = new Date(+new Date(jQuery(this).data('startdate')) - 12096e5); // - 14 days
	abcTriggerButtons(true);
	data = {
			action: 'abc_booking_getAvailabilityTable',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			startdate: abcFormatDate(startdate)
		};
		jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
			jQuery('#abc_AvailabilityTable').html(response);
			abcTriggerButtons(false);
		});
		return false;
});

jQuery('.abcAvailabilityTable').on('click', '.abcMonthSelector', function(){
	var startdate = jQuery(this).data('startdate');
	abcTriggerButtons(true);
	data = {
			action: 'abc_booking_getAvailabilityTable',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			startdate: startdate
		};
		jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
			jQuery('#abc_AvailabilityTable').html(response);
			abcTriggerButtons(false);
		});
		return false;
});

jQuery('.abcAvailabilityTable').on('click', '.abcYearSelector', function(){
	var startdate = jQuery(this).data('startdate');
	abcTriggerButtons(true);
	data = {
			action: 'abc_booking_getAvailabilityTable',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			startdate: startdate
		};
		jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
			jQuery('#abc_AvailabilityTable').html(response);
			abcTriggerButtons(false);
		});
		return false;
});

// Search function

jQuery('#tab-content').on('click', '#abcBookingSearchButton', function(){
	jQuery('#abcBookingSearchText').attr('disabled',true);
	jQuery('#abcBookingSearchButton').attr('disabled',true);
	var search = jQuery( "#abcBookingSearchText").val();
	data = {
			action: 'abc_booking_getSearchResults',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			search: search
		};
		jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
			jQuery('#abcBookingSearchResult').html(response);
			jQuery('#abcBookingSearchText').attr('disabled',false);
			jQuery('#abcBookingSearchButton').attr('disabled',false);
		});
	return false;
});

// Booking form
jQuery( "#calendar_id").on('change', function () {
	abc_checkDates();
});
jQuery( "#end").on('change', function () {
	abc_checkDates();
});
jQuery( "#start").on('change', function () {
	abc_checkDates();
});
jQuery( "#persons").on('change', function () {
	abc_checkDates();
});
jQuery( '#abc-booking-form' ).on('click', "input[name=abc-extras-checkbox]", function () {
	if(jQuery(this).is(":checked")) {
        jQuery(this).addClass("abc-priceindicator");
    } else {
        jQuery(this).removeClass("abc-priceindicator");
    }
	abc_calculatePrice();
});
jQuery( "select[name='calendar_id']").on('change', function () {
	jQuery('#persons').attr('disabled',true);
	var calId = jQuery( "select[name='calendar_id']").val();
	data = {
		action: 'abc_booking_getPersonList',
		abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
		calId: calId
	};
	jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
		jQuery('#persons').html(jQuery(response));
		jQuery('#persons').attr('disabled',false);
	});
	return false;
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
jQuery(document).on('click', '#postAbcCoupon', function(){
	abc_validateCoupon();
});

function abc_validateCoupon(){	
	var totalprice = jQuery('#abc_totalPrice').attr('data-sum');
	data = {
		action: 'abc_booking_backend_validateCode',
		totalprice: totalprice,
		code: jQuery('#coupon').val(),
		from: jQuery('#start').val(),
		to: jQuery('#end').val(),
		calendar: jQuery('#calendar_id').val()
	};
	jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response) {
		console.log('Response: ' + response);
        if (response == 0) {
        	jQuery('#abc-coupon').addClass('abc-form-error');
            jQuery('#abc-coupon-error').html(ajax_abc_booking_showBookingForm.coupon_unknown);
        }else if (response == 1) {
            jQuery('#abc-coupon').addClass('abc-form-error');
            jQuery('#abc-coupon-error').html(ajax_abc_booking_showBookingForm.coupon_nightlimit);
        }else {
			jQuery('#abc_totalPrice').html(response);
		    jQuery('#abc_totalPrice').data('sum', response);
		    jQuery('#abc_totalPrice').data('discount', (totalprice-response));
			jQuery('#abc-coupon').removeClass('abc-form-error');
            jQuery('#abc-coupon-error').html('');
		}
	});
	return false;
}

function abc_calculatePrice(){
	var sum = 0;
	jQuery('.abc-priceindicator').each(function() {
        sum += Number(jQuery(this).data('price'));
    });
    jQuery('#abc_totalPrice').html(sum);
    jQuery('#abc_totalPrice').attr('data-sum', sum);
    var extrasList = jQuery("input[name=abc-extras-checkbox]:checked").map(function () {return this.value;}).get().join(",");
    jQuery('#abc-extralist').val(extrasList);
    if(jQuery('#abc_totalPrice').data('discount') > 0){
    	abc_validateCoupon();
    }
}

function abc_checkDates() {
	var extrasList = jQuery("input[name=abc-extras-checkbox]:checked").map(function () {return this.value;}).get().join(",");
	if( jQuery("#start").val() && jQuery("#end").val() && jQuery( "select[name='calendar_id']").val() ){
		jQuery('#abc_dateStatus').html('<span class="uk-text-muted"><i>Loading...</i></span>');
		jQuery('#abc_optionalExtras').html('<span class="uk-text-muted"><i>Loading...</i></span>');
		jQuery('#abc_mandatoryExtras').html('<span class="uk-text-muted"><i>Loading...</i></span>');
		var calId = jQuery( "select[name='calendar_id']").val();
		var from = jQuery( "#start").val();
		var to = jQuery( "#end").val();
		var persons = jQuery( "#persons").val();
		dataAvailability = {
			action: 'abc_booking_checkDates',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			from: from,
			to: to,
			calId: calId,
			persons: persons
		};
		dataOptionalExtras = {
			action: 'abc_booking_getOptionalExtras',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			from: from,
			to: to,
			persons: persons,
			extrasList: extrasList
		};
		dataMandatoryExtras = {
			action: 'abc_booking_getMandatoryExtras',
			abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
			from: from,
			to: to,
			persons: persons
		};
		var optionalExtras;
		var q1 = jQuery.post(ajax_abc_bookings.ajaxurl, dataOptionalExtras, function (optionalExtras){
					jQuery('#abc_optionalExtras').html(optionalExtras);
				});
		var mandatoryExtras;
		var q2 = jQuery.post(ajax_abc_bookings.ajaxurl, dataMandatoryExtras, function (mandatoryExtras){
					jQuery('#abc_mandatoryExtras').html(mandatoryExtras);
				});
		var q3 = jQuery.post(ajax_abc_bookings.ajaxurl, dataAvailability, function (response){
				jQuery('#abc_dateStatus').html(response);
			});
	    jQuery.when( q1, q2, q3 ).done( 
	    	function(){
	    		abc_calculatePrice(); 
	    		jQuery('#postAbcCoupon').attr('disabled',false);
	    		jQuery('#coupon').attr('disabled',false);
	    		}
	    	);
		return false;
	}	
}

// Sorting
jQuery(document).on('click', '.abc-sortingOption', function(){
	var itemsOnPage = 10;
	var offset = 0;
	var divid = '#' + jQuery(this).data('abc-divid');
	var buttonid = '#button' + jQuery(this).data('abc-divid');
	jQuery(buttonid).html(jQuery(this).html());
	var sorting = jQuery(this).data('abc-sorting');
	jQuery(divid).fadeOut('medium');
	data = {
		action: 'abc_booking_getBookingContent',
		abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
		state: jQuery(this).data('abc-state'),
		offset: offset,
		itemsOnPage: itemsOnPage,
		sorting: sorting
	};
	jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
		var pagination = UIkit.pagination('.uk-pagination', { currentPage: 1});
		pagination.init();
		jQuery('.uk-pagination').data('abc-sorting', sorting);
		jQuery(divid).html(jQuery(response));
		jQuery(divid).fadeIn('fast');
	});
	return false;
});

// Pagination for overview
jQuery('[data-uk-pagination]').on('select.uk.pagination', function(e, pageIndex){
	var itemsOnPage = jQuery(this).data('abc-itemsonpage');
	var offset = (pageIndex)*itemsOnPage;
	var divid = '#' + jQuery(this).data('abc-divid');
	var sorting = jQuery(this).data('abc-sorting');
	jQuery(divid).fadeOut('medium');
	data = {
		action: 'abc_booking_getBookingContent',
		abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
		state: jQuery(this).data('abc-state'),
		offset: offset,
		itemsOnPage: itemsOnPage,
		sorting: sorting
	};
	jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
		jQuery(divid).html(jQuery(response));
		jQuery(divid).fadeIn('fast');
	});
	return false;
});

// Feedback & Usage Forms
jQuery(document).on('click', '#feedbackLike', function(){
	jQuery('.feedbackQuestion').hide();
	jQuery('#likeForm').show();
});

jQuery(document).on('click', '#feedbackDislike', function(){
	jQuery('.feedbackQuestion').hide();
	jQuery('#dislikeForm').show();
});

jQuery(document).on('click', '#sendFeedback', function(){
	var feedbackMessage = jQuery( "#feedbackMessage").val();
	data = {
		action: 'abc_booking_sendFeedbackModal',
		abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
		feedbackMessage: feedbackMessage
	};
	jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
		jQuery('#dislikeForm').html(response);
		setTimeout(function() {
		  var modal = new UIkit.modal("#feedbackModal");
		  modal.hide();
		}, 5000);
	});
	return false;
});

jQuery(document).on('click', '#activateCommitUsage', function(){
	data = {
		action: 'abc_booking_activateCommitUsage',
		abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
		commitusage: 1
	};
	jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
		jQuery('#usageModalContent').html(response);
		setTimeout(function() {
		  var modal = new UIkit.modal("#feedbackModal");
		  modal.hide();
		}, 5000);
	});
	return false;
});

jQuery(document).on('click', '#activatePoweredby', function(){
	data = {
		action: 'abc_booking_activatePoweredby',
		abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
		poweredby: 1
	};
	jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){
		jQuery('#likeForm').html(response);
		setTimeout(function() {
		  var modal = new UIkit.modal("#feedbackModal");
		  modal.hide();
		}, 5000);
	});
	return false;
});

jQuery( document ).ready(function() {
	if(ajax_abc_bookings.nlAsked){
		UIkit.modal.prompt(
				ajax_abc_bookings.nlText, 
				ajax_abc_bookings.nlEmail, 
				function(val){
					data = {
							action: 'abc_setAbcNewsletter',
							abc_bookings_nonce: ajax_abc_bookings.abc_bookings_nonce,
							email: val
					};
					jQuery.post(ajax_abc_bookings.ajaxurl, data, function (response){});
				});
	}
	var modal = new UIkit.modal("#feedbackModal");

    if ( modal.isActive() ) {
        modal.hide();
    } else {
        modal.show();
    }	
});