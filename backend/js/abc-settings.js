
jQuery('#tab-content').on('click', '#abc_textCustomizationSubmit', function(){
	var abcLanguage = jQuery( "select[name='abcLanguage']").val();
	var textCheckAvailabilities = jQuery("#textCheckAvailabilities").val();
	var textSelectRoom = jQuery("#textSelectRoom").val();
	var textSelectedRoom = jQuery("#textSelectedRoom").val();
	var textOtherRooms = jQuery("#textOtherRooms").val();
	var textNoRoom = jQuery("#textNoRoom").val();
	var textAvailRooms = jQuery("#textAvailRooms").val();
	var textRoomType = jQuery("#textRoomType").val();
	var textYourStay = jQuery("#textYourStay").val();
	var textCheckin = jQuery("#textCheckin").val();
	var textCheckout = jQuery("#textCheckout").val();
	var textBookNow = jQuery("#textBookNow").val();
	var textThankYou = jQuery("#textThankYou").val();
	var textRoomPrice = jQuery("#textRoomPrice").val();
	var textOptin = jQuery("#textOptin").val();
	jQuery('#abc_textCustomizationSubmit').hide();
	jQuery('#abc_textSavingLoading').show();
	data = {
			action: 'abc_booking_editTextCustomization',
			abc_settings_nonce: ajax_abc_settings.abc_settings_nonce,
			abcLanguage: abcLanguage,
			textCheckAvailabilities: textCheckAvailabilities,
			textSelectRoom: textSelectRoom,
			textSelectedRoom: textSelectedRoom,
			textOtherRooms: textOtherRooms,
			textNoRoom: textNoRoom,
			textAvailRooms: textAvailRooms,
			textRoomType: textRoomType,
			textYourStay: textYourStay,
			textCheckin: textCheckin,
			textCheckout: textCheckout,
			textBookNow: textBookNow,
			textThankYou: textThankYou,
			textRoomPrice: textRoomPrice,
			textOptin: textOptin
		};
		jQuery.post(ajax_abc_settings.ajaxurl, data, function (response){
			jQuery('#abc_textSavingLoading').hide();
			jQuery('#abc_textCustomizationSubmit').show();
			jQuery('#abc_textSavingDone').show();
			jQuery("#abc_textSavingDone").fadeOut(7000);
		});
		return false;
});

jQuery( "select[name='abcLanguage']").on('change', function () {
	var abcLanguage = jQuery( "select[name='abcLanguage']").val();
	jQuery('#abc_textCustomizationSubmit').attr('disabled',true);
	jQuery('#languageDropdown').attr('disabled',true);
	data = {
		action: 'abc_booking_getTextCustomization',
		abc_settings_nonce: ajax_abc_settings.abc_settings_nonce,
		abcLanguage: abcLanguage
	};
	jQuery.post(ajax_abc_settings.ajaxurl, data, function (response){
		var textLabels = jQuery.parseJSON(response);
		jQuery('#textCheckAvailabilities').val(textLabels.checkAvailabilities);
		jQuery('#textSelectRoom').val(textLabels.selectRoom);
		jQuery('#textSelectedRoom').val(textLabels.selectedRoom);
		jQuery('#textOtherRooms').val(textLabels.otherRooms);
		jQuery('#textNoRoom').val(textLabels.noRoom);
		jQuery('#textAvailRooms').val(textLabels.availRooms);
		jQuery('#textRoomType').val(textLabels.roomType);
		jQuery('#textYourStay').val(textLabels.yourStay);
		jQuery('#textCheckin').val(textLabels.checkin);
		jQuery('#textCheckout').val(textLabels.checkout);
		jQuery('#textBookNow').val(textLabels.bookNow);
		jQuery('#textThankYou').val(textLabels.thankYou);
		jQuery('#textRoomPrice').val(textLabels.roomPrice);
		jQuery('#textOptin').val(textLabels.optin);
		jQuery('#abc_textCustomizationSubmit').attr('disabled',false);
		jQuery('#languageDropdown').attr('disabled',false);
	});
	return false;
});