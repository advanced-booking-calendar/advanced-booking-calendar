function getLastDayDate(someDate){ // Returns the last day of the month for someDate
	var y = someDate.getFullYear();
	var m = someDate.getMonth();
	var d = new Date(y, m + 1, 0 );
	return d;
}

function getDateYYYYMMDD(someDate){
	var dd = someDate.getDate();
	if (dd < 10){
		dd = '0' + dd;
	}
	var mm = someDate.getMonth() + 1;
	if (mm < 10){
		mm = '0' + mm;
	}
	var yyyy = someDate.getFullYear();
	return yyyy + '-'+ mm + '-'+ dd;
}

function setDateYYYYMMDD(someDate){
	var tempDate = someDate.split("-");
	var newDate = new Date(tempDate[0], tempDate[1] - 1, tempDate[2]);
	return newDate;
}

// Single Calendar

jQuery('.abc-singlecalendar').on('click', '.abc-single-button-right', function(){
	var uniqid = jQuery(this).data('id');
	var calendar = jQuery(this).data('calendar');
	var abcSingleCheckin = jQuery('#abc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var abcSingleCheckout = jQuery('#abc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	var month = jQuery('#abc_singlecalendar_' + uniqid).data('month-' + uniqid);
	month = month + 1;
	jQuery('#singlecalendar-month-' + uniqid).hide();
	jQuery('#abc-calendar-days-' + uniqid).hide();
	jQuery('.abc-single-button-right').attr('disabled',true);
	jQuery('#abc_single_loading-' + uniqid).show();
	dateData = {
			action: 'abc_booking_getMonth',
			abc_nonce: ajax_abc_booking_SingleCalendar.abc_nonce,
			month: month
	}
	jQuery.post(ajax_abc_booking_SingleCalendar.ajaxurl, dateData, function (response){
		jQuery('#singlecalendar-month-' + uniqid).html(response);
	});
	data = {
		action: 'abc_booking_getSingleCalendar',
		abc_nonce: ajax_abc_booking_SingleCalendar.abc_nonce,
		month: month, 
		uniqid: uniqid, 
		calendar: calendar,
		start: abcSingleCheckin,
		end: abcSingleCheckout
	};
	jQuery.post(ajax_abc_booking_SingleCalendar.ajaxurl, data, function (response){
		jQuery('#abc-calendar-days-' + uniqid).html(response);
		jQuery('#abc-calendar-days-' + uniqid).show();
		jQuery('#singlecalendar-month-' + uniqid).show();
		jQuery('.abc-single-button-right').attr('disabled',false);
		jQuery('#abc_single_loading-' + uniqid).hide();
	});
	jQuery('#abc_singlecalendar_' + uniqid).data('month-' + uniqid, month);
	return false;	
});

jQuery('.abc-singlecalendar').on('click', '.abc-single-button-left', function(){
	var uniqid = jQuery(this).data('id');
	var calendar = jQuery(this).data('calendar');
	var abcSingleCheckin = jQuery('#abc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var abcSingleCheckout = jQuery('#abc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	var month = jQuery('#abc_singlecalendar_' + uniqid).data('month-' + uniqid);
	month = month - 1;
	jQuery('#singlecalendar-month-' + uniqid).hide();
	jQuery('#abc-calendar-days-' + uniqid).hide();
	jQuery('.abc-single-button-left').attr('disabled',true);
	jQuery('#abc_single_loading-' + uniqid).show();
	dateData = {
			action: 'abc_booking_getMonth',
			abc_nonce: ajax_abc_booking_SingleCalendar.abc_nonce,
			month: month
	}
	jQuery.post(ajax_abc_booking_SingleCalendar.ajaxurl, dateData, function (response){
		jQuery('#singlecalendar-month-' + uniqid).html(response);
	});
	data = {
		action: 'abc_booking_getSingleCalendar',
		abc_nonce: ajax_abc_booking_SingleCalendar.abc_nonce,
		month: month, 
		uniqid: uniqid, 
		calendar: calendar,
		start: abcSingleCheckin,
		end: abcSingleCheckout
	};
	jQuery.post(ajax_abc_booking_SingleCalendar.ajaxurl, data, function (response){
		jQuery('#abc-calendar-days-' + uniqid).html(response);
		jQuery('#abc-calendar-days-' + uniqid).show();
		jQuery('#singlecalendar-month-' + uniqid).show();
		jQuery('.abc-single-button-left').attr('disabled',false);
		jQuery('#abc_single_loading-' + uniqid).hide();	
	});
	jQuery('#abc_singlecalendar_' + uniqid).data('month-' + uniqid, month);
	return false;	
});	

jQuery('.abc-singlecalendar').on('click', '.abc-date-selector', function(){
	var uniqid = jQuery(this).data('id');
	var calendar = jQuery(this).data('calendar');
	var date = jQuery(this).data('date');
	var tempDate = setDateYYYYMMDD(date);
	var abcSingleCheckin = jQuery('#abc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var abcSingleCheckout = jQuery('#abc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	var lastDay = getDateYYYYMMDD(getLastDayDate(tempDate));
	if(abcSingleCheckin == 0){
		abcSingleCheckin = date;
		abcSingleCheckout = 0;
		jQuery(this).addClass('abc-date-selected');
	} else if (abcSingleCheckin != 0 && abcSingleCheckout == 0 && date > abcSingleCheckin){
		var tempDate = setDateYYYYMMDD(abcSingleCheckin);
		while(getDateYYYYMMDD(tempDate) <= date){
			if(jQuery('#abc-day-' + uniqid + getDateYYYYMMDD(tempDate)).hasClass('abc-booked')){
				break;
			}
			jQuery('#abc-day-' + uniqid + getDateYYYYMMDD(tempDate)).addClass('abc-date-selected');
			tempDate.setDate(tempDate.getDate() + 1);
		}
		tempDate.setDate(tempDate.getDate() - 1);
		abcSingleCheckout = getDateYYYYMMDD(tempDate);
	} else if (abcSingleCheckin > date 
			|| (abcSingleCheckin != 0 && abcSingleCheckout != 0 && date >= abcSingleCheckout)
			|| (abcSingleCheckin != 0 && abcSingleCheckout != 0 && date >= abcSingleCheckin)
			){
		var tempDate = setDateYYYYMMDD(abcSingleCheckin);
		jQuery('.abc-date-selector').removeClass('abc-date-selected');
		jQuery(this).addClass('abc-date-selected');
		abcSingleCheckin = date;
		abcSingleCheckout = 0;
	}	
	data = {
		action: 'abc_booking_setDataRange',
		abc_nonce: ajax_abc_booking_SingleCalendar.abc_nonce,
		start: abcSingleCheckin, 
		end: abcSingleCheckout, 
		uniqid: uniqid, 
		calendar: calendar
	};
	jQuery.post(ajax_abc_booking_SingleCalendar.ajaxurl, data, function (response){
		jQuery('#abc-booking-' + uniqid).html(response);
	});
	jQuery('#abc_singlecalendar_' + uniqid).data('checkin-' + uniqid, abcSingleCheckin);
	jQuery('#abc_singlecalendar_' + uniqid).data('checkout-' + uniqid, abcSingleCheckout);
	return false;	
});

jQuery('.abc-singlecalendar').on('mouseenter', '.abc-date-selector', function(){
	var uniqid = jQuery(this).data('id');
	var date = jQuery(this).data('date');
	var dateDate = setDateYYYYMMDD(date);
	var abcSingleCheckin = jQuery('#abc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var abcSingleCheckout = jQuery('#abc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	if(date > abcSingleCheckin && abcSingleCheckin != 0 && abcSingleCheckout == 0){
		var tempDate = setDateYYYYMMDD(abcSingleCheckin);
		while(tempDate <= dateDate){
			if(jQuery('#abc-day-'+ uniqid + getDateYYYYMMDD(tempDate)).hasClass('abc-booked')){
				break;
			}
			jQuery('#abc-day-'+ uniqid + getDateYYYYMMDD(tempDate)).addClass('abc-date-selected');
			tempDate.setDate(tempDate.getDate() + 1);
		}
	}
});

jQuery('.abc-singlecalendar').on('mouseleave', '.abc-date-selector', function(){
	var uniqid = jQuery(this).data('id');
	var date = jQuery(this).data('date');
	var abcSingleCheckin = jQuery('#abc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var checkinDate = setDateYYYYMMDD(abcSingleCheckin);
	var abcSingleCheckout = jQuery('#abc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	if(date > abcSingleCheckin && abcSingleCheckin != 0 && abcSingleCheckout == 0){
		var tempDate = setDateYYYYMMDD(date);
		while(tempDate > checkinDate){
			jQuery('#abc-day-'+ uniqid + getDateYYYYMMDD(tempDate)).removeClass('abc-date-selected');
			tempDate.setDate(tempDate.getDate() - 1);
		}
	}
});	

// Calendar overview
jQuery('.abc-calendar-overview').on('click', '.abc-overview-button', function(){
	var uniqid = jQuery(this).data('id');
	var overviewMonth = jQuery(this).data('month');
	var overviewYear = jQuery(this).data('year');
	jQuery('.abc-overview-button').attr('disabled',true);
	jQuery('.abcMonth').attr('disabled',true);
	jQuery('.abcYear').attr('disabled',true);
	data = {
		action: 'abc_booking_getCalOverview',
		abc_nonce: ajax_abc_booking_calOverview.abc_nonce,
		month: overviewMonth,
		year: overviewYear,
		uniqid: uniqid
	};
	
	jQuery.post(ajax_abc_booking_calOverview.ajaxurl, data, function (response){
		jQuery('#abc-calendaroverview-' + uniqid).html(response);
		jQuery('.abc-overview-button').attr('disabled',false);
		jQuery('.abcMonth').attr('disabled',false);
		jQuery('.abcYear').attr('disabled',false);
	});
	
	return false;	
});	

jQuery( '.abc-calendar-overview' ).on('change', "select[name='abcMonth']", function () {
	var uniqid = jQuery(this).data('id');
	var overviewMonth = jQuery( "select[name='abcMonth']").val();
	var overviewYear = jQuery( "select[name='abcYear']").val();
	jQuery('.abcMonth').attr('disabled',true);
	jQuery('.abcYear').attr('disabled',true);
	jQuery('.abc-button-rl').attr('disabled',true);
	data = {
		action: 'abc_booking_getCalOverview',
		abc_nonce: ajax_abc_booking_calOverview.abc_nonce,
		month: overviewMonth,
		year: overviewYear,
		uniqid: uniqid
	};
	jQuery.post(ajax_abc_booking_calOverview.ajaxurl, data, function (response){
		jQuery('#abc-calendaroverview-' + uniqid).html(response);
		jQuery('.abc-button-rl').attr('disabled',false);
		jQuery('.abcMonth').attr('disabled',false);
		jQuery('.abcYear').attr('disabled',false);
	});
	return false;
});
jQuery( '.abc-calendar-overview' ).on('change', "select[name='abcYear']", function () {
	var uniqid = jQuery(this).data('id');
	var overviewMonth = jQuery( "select[name='abcMonth']").val();
	var overviewYear = jQuery( "select[name='abcYear']").val();
	jQuery('.abcMonth').attr('disabled',true);
	jQuery('.abcYear').attr('disabled',true);
	jQuery('.abc-button-rl').attr('disabled',true);
	data = {
		action: 'abc_booking_getCalOverview',
		abc_nonce: ajax_abc_booking_calOverview.abc_nonce,
		month: overviewMonth,
		year: overviewYear,
		uniqid: uniqid
	};
	jQuery.post(ajax_abc_booking_calOverview.ajaxurl, data, function (response){
		jQuery('#abc-calendaroverview-' + uniqid).html(response);
		jQuery('.abc-button-rl').attr('disabled',false);
		jQuery('.abcMonth').attr('disabled',false);
		jQuery('.abcYear').attr('disabled',false);
	});
	return false;
});

// Booking form
function getAbcAvailabilities(calendarId){
	data = {
		action: 'abc_booking_getBookingResult',			
		from: jQuery("#abc-from").val(),
		to: jQuery("#abc-to").val(),
		persons: jQuery("#abc-persons").val(),
		hide_other: ajax_abc_booking_showBookingForm.hide_other,
		hide_tooshort: ajax_abc_booking_showBookingForm.hide_tooshort,
		calendarId: calendarId
	};
	jQuery('#abc-submit-button').hide();
	jQuery('#abc-bookingresults').hide();
	jQuery('.abc-submit-loading').show();
	jQuery.post(ajax_abc_booking_showBookingForm.ajaxurl, data, function (response){
		jQuery('#abc-submit-button').show();
		jQuery('.abc-submit-loading').hide();
		jQuery('#abc-bookingresults').html(response);
		jQuery("#abc-bookingresults").slideDown("slow");
		jQuery('.abc-submit').attr('disabled',false);
	});	
	return false;	
}

jQuery('#abc-form-content').on('click', '#abc-check-availabilities', function() {
	getAbcAvailabilities(jQuery("#abcPostCalendarId").val());
});

jQuery.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return results[1] || 0;
    }
}

jQuery(document).ready(function() {
	if(jQuery.urlParam('abc-paypal') !== null && jQuery.urlParam('token') !== null){
		jQuery('#abc-form-content').hide();
		jQuery('#abc_bookinform_loading').show();
		var payerId;
		payerId = 'null';
		if(jQuery.urlParam('PayerID') !== null){
			payerId = jQuery.urlParam('PayerID');
		}
		data = {
			action: 'abc_booking_getPayPalResponse',
			paypal: jQuery.urlParam('abc-paypal'),
			token: jQuery.urlParam('token'),
			payerId: payerId
		};
		jQuery.post(ajax_abc_booking_showBookingForm.ajaxurl, data, function (response){
			jQuery('#abc_bookinform_loading').hide();
			jQuery('#abc-form-content').html(response);
			jQuery('#abc-form-content').fadeIn('medium');
		});	
	}
    if (jQuery("#abcPostTrigger").length && jQuery("#abcPostTrigger").val() > 0 ) {
    	getAbcAvailabilities(jQuery("#abcPostCalendarId").val());
		jQuery('html, body').animate({
                    scrollTop: jQuery("#abc-form-content").offset().top
                }, 2000);
	}
});

jQuery('#abc-back-to-availabilities').click(function(){
	jQuery('#abc-from').attr('disabled',false);
	jQuery('#abc-from').removeClass('abc-deactivated');
	jQuery('#abc-to').attr('disabled',false);
	jQuery('#abc-to').removeClass('abc-deactivated');
	jQuery('#abc-persons').attr('disabled',false);
	jQuery('#abc-persons').removeClass('abc-deactivated');
	jQuery('#abc-back-to-availabilities').hide();
	jQuery('#abc-check-availabilities').show();
	
	return false;	
});
jQuery(document).on('click', '.abc-bookingform-book', function(){
	jQuery('#abc-bookingresults').fadeOut('medium');
	jQuery('#abc-check-availabilities').hide();
	jQuery('#abc-from').attr('disabled',true);
	jQuery('#abc-from').addClass('abc-deactivated');
	jQuery('#abc-to').attr('disabled',true);
	jQuery('#abc-to').addClass('abc-deactivated');
	jQuery('#abc-persons').attr('disabled',true);
	jQuery('#abc-persons').addClass('abc-deactivated');
	jQuery('html, body').animate({
        scrollTop: jQuery("#abc-bookingresults").offset().top - 150
    	}, 1000);
	data = {
		action: 'abc_booking_getBookingFormStep2',
		from: jQuery(this).data('from'),
		to: jQuery(this).data('to'),
		persons: jQuery(this).data('persons'),
		calendar: jQuery(this).data('calendar')
	};
	jQuery.post(ajax_abc_booking_showBookingForm.ajaxurl, data, function (response){
		jQuery('#abc-bookingresults').html(response);
		jQuery('#abc-bookingresults').fadeIn('medium');
		jQuery('#abc-back-to-availabilities').show();
	});	
	return false;	
});


jQuery(document).on('click', '#abc-bookingform-coupon-submit', function(){
	jQuery('#abc-coupon').attr('disabled',true);
	jQuery('#abc-coupon').addClass('abc-deactivated');
	data = {
		action: 'abc_booking_validateCode',
		totalprice: jQuery('#abc-bookingform-totalprice').data('totalprice'),
		code: jQuery('#abc-coupon').val(),
		from: jQuery(this).data('from'),
		to: jQuery(this).data('to'),
		calendar: jQuery(this).data('calendar')
	};
	jQuery.post(ajax_abc_booking_showBookingForm.ajaxurl, data, function (response) {
        if (response == 0) {
            jQuery('#abc-coupon').addClass('abc-form-error');
            jQuery('#abc-coupon-error').html(ajax_abc_booking_showBookingForm.coupon_unknown);
        }else if (response == 1) {
            jQuery('#abc-coupon').addClass('abc-form-error');
            jQuery('#abc-coupon-error').html(ajax_abc_booking_showBookingForm.coupon_nightlimit);
        }else {
			jQuery('#abc-bookingform-totalprice').html(response);
			jQuery('#abc-coupon').removeClass('abc-form-error');
            jQuery('#abc-coupon-error').html('');
		}
	});
	jQuery('#abc-coupon').attr('disabled',false);
	jQuery('#abc-coupon').removeClass('abc-deactivated');
	return false;
});

jQuery(document).on('click', '#abc-bookingform-extras-submit', function(){
	jQuery('#abc-bookingresults').fadeOut('medium');
	jQuery('html, body').animate({
        scrollTop: jQuery("#abc-bookingresults").offset().top - 150
    	}, 1000);
	data = {
		action: 'abc_booking_getBookingFormStep2',
		extrasList: jQuery("input[name=abc-extras-checkbox]:checked").map(function () {return this.value;}).get().join(","),
		from: jQuery(this).data('from'),
		to: jQuery(this).data('to'),
		persons: jQuery(this).data('persons'),
		calendar: jQuery(this).data('calendar')
	};
	jQuery.post(ajax_abc_booking_showBookingForm.ajaxurl, data, function (response){
		jQuery('#abc-bookingresults').html(response);
		jQuery('#abc-bookingresults').fadeIn('medium');
		jQuery('#abc-back-to-availabilities').show();
	});	
	return false;
});

jQuery(document).on('click', '#abc-bookingform-back', function(){
	jQuery('#abc-form-content').fadeOut('medium');
	data = {
		action: 'abc_booking_getBackToBookingResult',
		from: jQuery(this).data('from'),
		to: jQuery(this).data('to'),
		persons: jQuery(this).data('persons')
	};
	jQuery.post(ajax_abc_booking_showBookingForm.ajaxurl, data, function (response){
		jQuery('#abc-form-content').html(response);
		jQuery('#abc-form-content').fadeIn('medium');
	});	
	return false;	
});

jQuery(document).on('click', '#abc-bookingform-book-submit', function(){
		data = {
			action: 'abc_booking_getBookingFormBook',
			from: jQuery(this).data('from'),
			to: jQuery(this).data('to'),
			persons: jQuery(this).data('persons'),
			calendar: jQuery(this).data('calendar'),
			extraslist: jQuery(this).data('extraslist'),
			firstname: jQuery('#first_name').val(),
			lastname: jQuery('#last_name').val(),
			email: jQuery('#email').val(),
			phone: jQuery('#phone').val(),
			address: jQuery('#address').val(),
			zip: jQuery('#zip').val(),
			city: jQuery('#city').val(),
			county: jQuery('#county').val(),
			country: jQuery('#country').val(),
			coupon: jQuery('#abc-coupon').val(),
			payment: jQuery("input[name='payment']:checked").val(),
			message: jQuery('#message').val(),
			optincheckbox: jQuery("input[name='optincheckbox']:checked").val()
		};
	jQuery('.abc-booking-form').validate({ // initialize the plugin
        errorClass:'abc-form-error',
		rules: ajax_abc_booking_showBookingForm.rules,
		submitHandler: function (form) { 
		jQuery('#abc-form-content').fadeOut('medium');
		jQuery('#abc_bookinform_loading').show();
		jQuery('html, body').animate({ scrollTop: (jQuery('#abc-form-wrapper').offset().top - 150)}, 'slow');
		jQuery.post(ajax_abc_booking_showBookingForm.ajaxurl, data, function (response){
			jQuery('#abc_bookinform_loading').hide();
			jQuery('#abc-form-content').html(response);
			jQuery('#abc-form-content').fadeIn('medium');
		});	
		return false;
        }
    });	
});
	