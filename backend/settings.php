<?php

//Edit  general settings
function abc_booking_editCalendarSettings() {
	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	if (isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && 
			isset($_POST["currency"]) && isset($_POST["dateformat"]) && isset($_POST["priceformat"]) && 
			isset($_POST["cookies"]) && isset($_POST["usage"]) && isset($_POST["deletion"]) && 
			isset($_POST["customCss"]) && isset($_POST["emailcopy"]) && isset($_POST["accessLevel"])) {
		update_option ('abc_email', sanitize_email($_POST["email"]));
		$output = '';
		// Check if the bookingpage-post-id contains the abc-bookingform shortcode
		if(getAbcSetting('bookingpage') != $_POST["bookingpage"] && intval($_POST["bookingpage"]) > 0){
			$content_post = get_post(intval($_POST["bookingpage"]));
			if( strpos($content_post->post_content, 'abc-bookingform') !== false) {
				update_option ('abc_bookingformvalidated', 1);
				$output .= '&bookingform=validated';
			}else{
				update_option ('abc_bookingformvalidated', 0);
				$output .= '&bookingform=error';
			}			
		}		
		update_option ('abc_emailcopy', intval($_POST["emailcopy"]));
		update_option ('abc_bookingpage', intval($_POST["bookingpage"]));
		update_option ('abc_dateformat', sanitize_text_field($_POST["dateformat"]));
		update_option ('abc_priceformat', sanitize_text_field($_POST["priceformat"]));
		update_option ('abc_currency', sanitize_text_field($_POST["currency"]));
		update_option ('abc_customCss', sanitize_text_field($_POST["customCss"]));
		update_option ('abc_currencyPosition', intval($_POST["currencyPosition"]));
		update_option ('abc_unconfirmed', sanitize_text_field($_POST["unconfirmed"]));
		update_option ('abc_cookies', sanitize_text_field($_POST["cookies"]));
		update_option ('abc_googleanalytics', intval($_POST["googleanalytics"]));
		update_option ('abc_poweredby', intval($_POST["poweredby"]));
		update_option ('abc_deletion', intval($_POST["deletion"]));
		update_option ('abc_accessLevel', sanitize_text_field($_POST["accessLevel"]));
		if(getAbcSetting("newsletter") == 0 && intval($_POST["newsletter"]) == 1){
			subscribeAbcNewsletter(sanitize_email($_POST["email"]), 0);
		}elseif(getAbcSetting("newsletter") == 1 && intval($_POST["newsletter"]) == 0){
			subscribeAbcNewsletter(sanitize_email($_POST["email"]), 1);
		}
		update_option ('abc_newsletter', intval($_POST["newsletter"]));
		if(getAbcSetting("usage") == 0 && intval($_POST["usage"]) == 1){
			activate_commitUsage();
		}elseif(getAbcSetting("usage") == 1 && intval($_POST["usage"]) == 0){
			deactivate_commitUsage();		
		}
		update_option ('abc_usage', intval($_POST["usage"]));
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-settings&setting=general".$output ) );
	exit;
} //==>editCalendarSettings()
add_action( 'admin_post_abc_booking_editCalendarSettings', 'abc_booking_editCalendarSettings' );

//Edit booking form settings
function abc_booking_editBookingFormSettings() {
	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	if (isset($_POST["firstname"]) &&  isset($_POST["lastname"]) &&  isset($_POST["phone"]) &&  isset($_POST["street"])
		 &&  isset($_POST["zip"]) &&  isset($_POST["city"]) &&  isset($_POST["county"])  
		 &&  isset($_POST["country"])  &&  isset($_POST["message"])) {
		 	$fieldCounter = 0;
			if(intval($_POST["firstname"]) > 0) {$fieldCounter++;}
			if(intval($_POST["lastname"]) > 0) {$fieldCounter++;}
			if(intval($_POST["phone"]) > 0) {$fieldCounter++;}
			if(intval($_POST["street"]) > 0) {$fieldCounter++;}
			if(intval($_POST["zip"]) > 0) {$fieldCounter++;}
			if(intval($_POST["city"]) > 0) {$fieldCounter++;}
			if(intval($_POST["county"]) > 0) {$fieldCounter++;}
			if(intval($_POST["country"]) > 0) {$fieldCounter++;}
			if(intval($_POST["message"]) > 0) {$fieldCounter++;}
		 	$options = array(
		 		'firstname' => intval($_POST["firstname"]),
		 		'lastname' => intval($_POST["lastname"]),
		 		'phone' => intval($_POST["phone"]),
		 		'street' => intval($_POST["street"]),
		 		'zip' => intval($_POST["zip"]),
		 		'city' => intval($_POST["city"]),
		 		'county' => intval($_POST["county"]),
		 		'country' => intval($_POST["country"]),
		 		'message' => intval($_POST["message"]),
		 		'optincheckbox' => intval($_POST["optincheckbox"]),
		 		'inputs' => $fieldCounter
				);
			update_option('abc_bookingform', $options);
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-settings&setting=bookingform" ) );
	exit;
} //==>f()
add_action( 'admin_post_abc_booking_editBookingFormSettings', 'abc_booking_editBookingFormSettings' );

//Edit email settings
function abc_booking_editEmailSettings() {
	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	if (isset($_POST["subjectunconfirmed"]) && isset($_POST["textunconfirmed"]) 
			&& isset($_POST["subjectconfirmed"]) && isset($_POST["textconfirmed"])
			&& isset($_POST["subjectcanceled"]) && isset($_POST["textcanceled"])
			&& isset($_POST["subjectrejected"]) && isset($_POST["textrejected"])
		) {
			update_option ('abc_subject_unconfirmed', sanitize_text_field($_POST["subjectunconfirmed"]));
			update_option ('abc_text_unconfirmed', implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST["textunconfirmed"] ))));
			update_option ('abc_subject_confirmed', sanitize_text_field($_POST["subjectconfirmed"]));
			update_option ('abc_text_confirmed', implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST["textconfirmed"] ))));
			update_option ('abc_subject_canceled', sanitize_text_field($_POST["subjectcanceled"]));
			update_option ('abc_text_canceled', implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST["textcanceled"] ))));
			update_option ('abc_subject_rejected', sanitize_text_field($_POST["subjectrejected"]));
			update_option ('abc_text_rejected', implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST["textrejected"] ))));
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-settings&setting=email" ) );
	exit;
} //==>editEmailSettings()
add_action( 'admin_post_abc_booking_editEmailSettings', 'abc_booking_editEmailSettings' );


//Edit Payment Settings

function abc_booking_editTextCustomization() {
	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
		
	if (isset($_POST["textCheckAvailabilities"]) && isset($_POST["abcLanguage"]) && isset($_POST["textSelectRoom"])) {			
		$locale = '';
		if(empty($_POST["abcLanguage"])){
			$locale = 'en_US';
		}else{
			$locale = sanitize_text_field($_POST["abcLanguage"]);
		}
		$previosCustomization = array();
		if(get_option( 'abc_textCustomization') != false){
			$previousCustomization = unserialize(get_option( 'abc_textCustomization'));
		}
		$previousCustomization[$locale] = array(  
							'checkAvailabilities' => sanitize_text_field($_POST["textCheckAvailabilities"]),
							'selectRoom' => sanitize_text_field($_POST["textSelectRoom"]),
							'selectedRoom' => sanitize_text_field($_POST["textSelectedRoom"]),
							'otherRooms' => sanitize_text_field($_POST["textOtherRooms"]),
							'noRoom' => sanitize_text_field($_POST["textNoRoom"]),
							'availRooms' => sanitize_text_field($_POST["textAvailRooms"]),
							'roomType' => sanitize_text_field($_POST["textRoomType"]),
							'yourStay' => sanitize_text_field($_POST["textYourStay"]),
							'checkin' => sanitize_text_field($_POST["textCheckin"]),
							'checkout' => sanitize_text_field($_POST["textCheckout"]),
							'bookNow' => sanitize_text_field($_POST["textBookNow"]),
							'thankYou' => sanitize_text_field($_POST["textThankYou"]),
							'roomPrice' => sanitize_text_field($_POST["textRoomPrice"]),
							'optin' => sanitize_text_field($_POST["textOptin"])
						);
		update_option( 'abc_textCustomization',	serialize($previousCustomization));
	}
	exit;
}//==>abc_booking_editTextCustomization()
//Edit Text Customization

function ajax_abc_booking_editTextCustomization() {
	if(!isset( $_POST['abc_settings_nonce'] ) || !wp_verify_nonce($_POST['abc_settings_nonce'], 'abc-settings-nonce') ){
		die('Permissions check failed!');
	}
	echo abc_booking_editTextCustomization($_POST);
	die();
}
add_action('wp_ajax_abc_booking_editTextCustomization', 'ajax_abc_booking_editTextCustomization');

function ajax_abc_booking_getTextCustomization(){
	if(!isset( $_POST['abc_settings_nonce'] ) || !wp_verify_nonce($_POST['abc_settings_nonce'], 'abc-settings-nonce') ){
		die('Permissions check failed!');
	}
	$locale = '';
	if(empty($_POST['abcLanguage'])){
		$locale = 'en_US';
	}else{
		$locale = sanitize_text_field($_POST['abcLanguage']);
	}
	$textCustomization = array(
			'checkAvailabilities' => '',
			'selectRoom' => '',
			'selectedRoom' => '',
			'otherRooms' => '',
			'noRoom' => '',
			'availRooms' => '',
			'roomType' => '',
			'yourStay' => '',
			'checkin' => '',
			'checkout' => '',
			'bookNow' => '',
			'thankYou' => '',
			'roomPrice' => '',
			'optin' => ''
		);
	if(get_option('abc_textCustomization') != false){
		$textCustomization = unserialize(get_option('abc_textCustomization'));
		if(isset($textCustomization[$locale])){
			$textCustomization = $textCustomization[$locale];
		}
	}
	echo json_encode($textCustomization);
	die();
}
add_action('wp_ajax_abc_booking_getTextCustomization', 'ajax_abc_booking_getTextCustomization');

function abc_booking_editPaymentSettings() {
	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
		
	//cash
	if (isset($_POST["activateCash"])) {
		if(empty($_POST["textCash"])) {
			wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-settings&setting=paymentSettingsError&type=Cash" ) );
			exit;
		}
	}
	
	//on invoice
	if (isset($_POST["activateOnInvoice"])) {
		if(empty($_POST["textOnInvoice"])) {
			wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-settings&setting=paymentSettingsError&type=OnInvoice" ) );
			exit;
		}
	}

	
	$paymentString = '';
		
	if (isset($_POST["activateCash"])) {
		$cash = "true";
		if(strlen($paymentString) > 0){
			$paymentString .= ',';
		}
		$paymentString .= 'Cash';
	} else {
		$cash = "false";
	}
	
	if (isset($_POST["activateOnInvoice"])) {
		$onInvoice = "true";
		if(strlen($paymentString) > 0){
			$paymentString .= ',';
		}
		$paymentString .= 'Invoice';
	} else {
		$onInvoice = "false";
	}
	
	update_option('abc_paymentString', $paymentString);
	
	update_option( 'abc_paymentSettings',
			serialize(
					array(  'cash' => array('activate' => $cash,
					      				   'text' => sanitize_text_field($_POST["textCash"])),
							'onInvoice' => array('activate' => $onInvoice,
									'text' => sanitize_text_field($_POST["textOnInvoice"]))
					)));
	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-settings&setting=paymentSettings" ) );
	exit;
}//==>abc_booking_editPaymentSettings()
add_action( 'admin_post_abc_booking_editPaymentSettings', 'abc_booking_editPaymentSettings' );

// Export database tables to JSON
function abc_booking_exportTables() {
	if(isset( $_GET['abc_booking_exportTables'])){
		if (!current_user_can(abc_booking_admin_capabilities())) {
			wp_die("You don't have access to this page.");
		}
		global $wpdb;
		$response = array();
		$response['settings'] = array(
				'abc_pluginversion' => get_option('abc_pluginversion'),
				'abc_email' => get_option('abc_email'),
				'abc_dateformat' => get_option('abc_dateformat'),
				'abc_priceformat' => get_option('abc_priceformat'),
				'abc_currency' => get_option('abc_currency'),
				'abc_currencyPosition' => get_option('abc_currencyPosition'),
				'blogname' => get_option('blogname')
		);
		$tables = array(
				'bookings',
				'booking_extras',
				'calendars',
				'rooms',
				'seasons',
				'seasons_assignment'
		);
		foreach($tables as $table){
			$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_'.$table.' ORDER BY id', ARRAY_A);
			$results = array();
			foreach($er as $row) {
				$results[] = $row;
			}
			$response[$table] = $results;
		}
		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=ABC_Export.json");
		echo json_encode($response);
		exit;
	}
} //==>exportTable
add_action( 'plugins_loaded', 'abc_booking_exportTables' );


//Backend output:
function advanced_booking_calendar_show_settings() {
	global $abcUrl;
	wp_enqueue_script('uikit-js', $abcUrl.'backend/js/uikit.min.js', array('jquery'));
	wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');
	
	//Preparing Date vars (saved format is selected)
	$date1 = "";
	$date2 = "";
	$date3 = "";
	$date4 = "";
	if(getAbcSetting("dateformat") == "Y-m-d") {
		$date1 = 'selected';
	} elseif(getAbcSetting("dateformat") == "d.m.Y") {
		$date2 = 'selected';
	} elseif(getAbcSetting("dateformat") == "d/m/Y") {
		$date3 = 'selected';
	} elseif(getAbcSetting("dateformat") == "m/d/Y") {
		$date4 = 'selected';
	}
	
	//Price Format
	$emailcopytrue = "";
	$emailcopyfalse = "";
	$priceComma = "";
	$priceDot = "";
	$currencyPositionBefore = "";
	$currencyPositionAfter = "";
	$newslettertrue = "";
	$newsletterfalse = "";
	$unconfirmedtrue = "";
	$unconfirmedfalse = "";
	$cookiestrue = "";
	$cookiesfalse = "";
	$gatrue = "";
	$gafalse = "";
	$poweredbytrue = "";
	$poweredbyfalse = "";
	$deletiontrue = "";
	$deletionfalse = "";
	$usagetrue = "";
	$usagefalse = "";
	$firstdayofweekSunday = "";
	$firstdayofweekMonday = "";
	if(getAbcSetting("emailcopy") == "0") {
		$emailcopyfalse= 'checked';
	} elseif(getAbcSetting("emailcopy") == "1") {
		$emailcopytrue= 'checked';
	}
	if(getAbcSetting("priceformat") == ",") {
		$priceComma = 'selected';
	} elseif(getAbcSetting("priceformat") == ".") {
		$priceDot = 'selected';
	}
	if(getAbcSetting("currencyPosition") == 0) {
		$currencyPositionBefore = 'checked';
	} elseif(getAbcSetting("currencyPosition") == 1) {
		$currencyPositionAfter = 'checked';
	}
	if(getAbcSetting("newsletter") == "1") {
		$newslettertrue = 'checked';
	} elseif(getAbcSetting("newsletter") == "0") {
		$newsletterfalse = 'checked';
	}
	if(getAbcSetting("cookies") == "1") {
		$cookiestrue = 'checked';
	} elseif(getAbcSetting("cookies") == "0") {
		$cookiesfalse = 'checked';
	}
	if(getAbcSetting("unconfirmed") == "1") {
		$unconfirmedtrue = 'checked';
	} elseif(getAbcSetting("unconfirmed") == "0") {
		$unconfirmedfalse = 'checked';
	}
	if(getAbcSetting("googleanalytics") == "1") {
		$gatrue = 'checked';
	} elseif(getAbcSetting("googleanalytics") == "0") {
		$gafalse = 'checked';
	}
	if(getAbcSetting("poweredby") == "1") {
		$poweredbytrue = 'checked';
	} elseif(getAbcSetting("poweredby") == "0") {
		$poweredbyfalse = 'checked';
	}
	if(getAbcSetting("deletion") == "1") {
		$deletiontrue = 'checked';
	} elseif(getAbcSetting("deletion") == "0") {
		$deletionfalse = 'checked';
	}
	if(getAbcSetting("usage") == "1") {
		$usagetrue = 'checked';
	} elseif(getAbcSetting("usage") == "0") {
		$usagefalse = 'checked';
	}
	if(getAbcSetting("firstdayofweek") == "0") {
		$firstdayofweekSunday = 'checked';
	} elseif(getAbcSetting("firstdayofweek") == "1") {
		$firstdayofweekMonday = 'checked';
	}
	$accessLevel = getAbcSetting("accessLevel");
	$accessLevelHtml = '';
	foreach( abc_booking_admin_capabilities(true) as $k => $v){
		$accessLevelHtml .= sprintf('<option value="%s" %s>%s</option>',$k,(($accessLevel===$k) ? 'selected="selected"':''),translate_user_role($k));
	}
	$bookingVarArray = abc_booking_getBookingVars();
	$placeholderList = '';
	$numItems = count($bookingVarArray);
	$i = 0;
	foreach ($bookingVarArray as $bookingVars){
		$placeholderList .= '['.$bookingVars.']';
		if(++$i === $numItems) {
			$placeholderList .= '.';
		} else {
			$placeholderList .= ', ';			
		}
	}
	$bookingForm = getAbcSetting("bookingform");	
	$firstname = array('', '', '');
	$lastname = array('', '', '');
	$phone = array('', '', '');
	$street = array('', '', '');
	$zip = array('', '', '');
	$county = array('', '', '');
	$city = array('', '', '');
	$country = array('', '', '');
	$message = array('', '', '');
	$optincheckbox = array('', '', '');
	switch ($bookingForm["firstname"]) {
		case '0':$firstname[0] = ' checked';break;
		case '1':$firstname[1] = ' checked';break;
		case '2':$firstname[2] = ' checked';break;
	}
	switch ($bookingForm["lastname"]) {
		case '0':$lastname[0] = ' checked';break;
		case '1':$lastname[1] = ' checked';break;
		case '2':$lastname[2] = ' checked';break;
	}
	switch ($bookingForm["phone"]) {
		case '0':$phone[0] = ' checked';break;
		case '1':$phone[1] = ' checked';break;
		case '2':$phone[2] = ' checked';break;
	}
	switch ($bookingForm["street"]) {
		case '0':$street[0] = ' checked';break;
		case '1':$street[1] = ' checked';break;
		case '2':$street[2] = ' checked';break;
	}
	switch ($bookingForm["zip"]) {
		case '0':$zip[0] = ' checked';break;
		case '1':$zip[1] = ' checked';break;
		case '2':$zip[2] = ' checked';break;
	}
	switch ($bookingForm["county"]) {
		case '0':$county[0] = ' checked';break;
		case '1':$county[1] = ' checked';break;
		case '2':$county[2] = ' checked';break;
	}
	switch ($bookingForm["city"]) {
		case '0':$city[0] = ' checked';break;
		case '1':$city[1] = ' checked';break;
		case '2':$city[2] = ' checked';break;
	}
	switch ($bookingForm["country"]) {
		case '0':$country[0] = ' checked';break;
		case '1':$country[1] = ' checked';break;
		case '2':$country[2] = ' checked';break;
	}
	switch ($bookingForm["message"]) {
		case '0':$message[0] = ' checked';break;
		case '1':$message[1] = ' checked';break;
		case '2':$message[2] = ' checked';break;
	}
	switch ($bookingForm["optincheckbox"]) {
		case '0':$optincheckbox[0] = ' checked';break;
		case '1':$optincheckbox[1] = ' checked';break;
		case '2':$optincheckbox[2] = ' checked';break;
	}
	$settingsMessage = '';
	if ( isset($_GET["setting"]) ) {
		switch ($_GET["setting"]) {
			case 'email':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Email settings have been saved.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'general':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('General settings have been saved.', 'advanced-booking-calendar').'</strong></p>';
					if(isset($_GET["bookingform"]) && $_GET["bookingform"] == "error" ){
						$settingsMessage .= '<p><strong>'.__('Warning! The page you selected does not contain the booking form shortcode. Please add the shortcode [abc-bookingform] or some functions may not work properly.', 'advanced-booking-calendar').'</strong></p>';
					}elseif(isset($_GET["bookingform"]) && $_GET["bookingform"] == "validated" ){
						$settingsMessage .= '<p><strong>'.__('Great! The page you selected contains the booking form shortcode.', 'advanced-booking-calendar').'</strong></p>';
					}					
					$settingsMessage .=	'<button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'bookingform':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking form settings have been saved. Please make sure to update the email templates as well.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'textCustomization':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('New text labels have been saved.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'paymentSettings':
				$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
								<p><strong>'.__('Payment Settings have been saved.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
								<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'paymentSettingsError':
				if($_GET["type"] != "bookingForm") {
					if($_GET["type"] == "PP") {
						$type = "PayPal";
					} elseif($_GET["type"] == "PPSandbox") {
						$type = "PayPal Sandbox";
					} elseif($_GET["type"] == "Cash") {
						$type = "Cash onsite";
					} elseif($_GET["type"] == "OnInvoice") {
						$type = "On Invoice";
					}
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error error is-dismissible">
								<p><strong>'.__('Error while saving Payment Settings. Please fill out all required fields for', 'advanced-booking-calendar').' '.$type.'.</strong></p><button type="button" class="notice-dismiss">
								<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				} else {
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error error is-dismissible">
								<p><strong>'.__('Unable to activate the selected Payment Gateway. The selected Booking Form Page is invalid.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
								<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				}
				break;
				
				case 'licenseKey':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
								<p><strong>'.__('License was successfully activated.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
								<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
					
				case 'licenseKeyError':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
							<p><strong>'.urldecode($_GET["message"]).'</strong></p><button type="button" class="notice-dismiss">
							<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
		}
	}
	//Regex for email pattern
	$emailPattern = "[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,10}$";
	
	//Payment Gateways
	$paymentArr = get_option('abc_paymentSettings');
	$paymentArr = unserialize($paymentArr);
	$textCustomization = '';
	$customizationValid = false;
	if(get_option('abc_textCustomization') != false){
		$textCustomization = unserialize(get_option('abc_textCustomization'));
		if(isset($textCustomization[get_locale()])){
			$textCustomization = $textCustomization[get_locale()];
			$customizationValid = true;
		}
	}
	if(!$customizationValid){
		$textCustomization = array(
				'checkAvailabilities' => '',
				'selectRoom' => '',
				'selectedRoom' => '',
				'otherRooms' => '',
				'noRoom' => '',
				'availRooms' => '',
				'roomType' => '',
				'yourStay' => '',
				'checkin' => '',
				'checkout' => '',
				'bookNow' => '',
				'thankYou' => '',
				'roomPrice' => '',
				'optin' => ''
		);
	}
	if($paymentArr["cash"]["activate"] == 'true') {
		$activateCash  = 'checked';
	} else {
		$activateCash  = '';
	}
	
	if($paymentArr["onInvoice"]["activate"] == 'true') {
		$activateOnInvoice = 'checked';
	} else {
		$activateOnInvoice = '';
	}
	$languages = get_available_languages();
	
	wp_enqueue_script('abc-settings', $abcUrl.'backend/js/abc-settings.js', array('jquery'));
	wp_localize_script( 'abc-settings', 'ajax_abc_settings', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'abc_settings_nonce' => wp_create_nonce('abc-settings-nonce')
				)
			);
	
	echo '<div class="wrap">
		  <h1>'.__('Settings', 'advanced-booking-calendar').'</h1>
		  '.$settingsMessage.'
		  <div>
		  <ul class="uk-tab" data-uk-tab="{connect:\'#tab-content\'}">
					<li><a href="#">'.__('General Settings', 'advanced-booking-calendar').'</a></li>
					<li><a href="#">'.__('Booking Form Settings', 'advanced-booking-calendar').'</a></li>
					<li><a href="#">'.__('Email Settings', 'advanced-booking-calendar').'</a></li>
					<li><a href="#">'.__('Payment Settings', 'advanced-booking-calendar').'</a></li>
					<li><a href="#">'.__('Text Customization', 'advanced-booking-calendar').'</a></li>
		  </ul>
		  <ul id="tab-content" class="uk-switcher uk-margin">
		   	<li>	
			  <form method="post" class="uk-form uk-form-horizontal" action="admin-post.php">
			  <input type="hidden" name="action" value="abc_booking_editCalendarSettings" />
			  <div class="uk-form-row">
			      <label class="uk-form-label" for="email">'.__('Email', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls"><input pattern="'.$emailPattern.'" name="email" id="email" type="email" value="'.getAbcSetting("email").'" class="regular-text code" placeholder="mail@mail.com" required />
				      <p class="description">'.__('This Email Address will receive all booking requests', 'advanced-booking-calendar').'</p></div>
			    </div>
				<div class="uk-form-row">
			      <label class="uk-form-label" for="cookies">'.__('Send copies of guest mails to the email address above', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      <fieldset>
			      	<input '.$emailcopytrue.' type="radio" name="emailcopy" id="emailcopy-true" value="1"><label for="emailcopy-true"> '.__('Enabled', 'advanced-booking-calendar').'</label><br/>
			      	<input '.$emailcopyfalse.' type="radio" name="emailcopy" id="emailcopy-false" value="0"><label for="emailcopy-false"> '.__('Disabled', 'advanced-booking-calendar').'</label>
			      </fieldset>
			      	</div>
			    </div>
			  <div class="uk-form-row">
			      <label class="uk-form-label" for="newsletter">Advanced Booking Calendar '.__('Newsletter', 'advanced-booking-calendar').':</label>
			       <div class="uk-form-controls">
			      <fieldset>
			      	<input '.$newslettertrue.' type="radio" name="newsletter" id="newsletter-true" value="1"><label for="newsletter-true"> '.__('Yes, I want to get vital informations on how to raise my occupation rate and stay tuned about Advanced Booking Calendar', 'advanced-booking-calendar').'</label><br/>
			      	<input '.$newsletterfalse.' type="radio" name="newsletter" id="newsletter-false" value="0"><label for="newsletter-false"> '.__('No, disable the newsletter', 'advanced-booking-calendar').'</label>
			      </fieldset>
			      	<p class="description">'.__('You will receive regular tips on how to create great Hotel websites and informations about this plugin. We promise to never spam you. You can unsubscribe anytime.', 'advanced-booking-calendar').'</p>
				     </div>
			    </div>
				<div class="uk-form-row">
			      <label class="uk-form-label" for="bookingpage">'.__('Page with booking form', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      	'.wp_dropdown_pages(
			      		array(
			      			'echo' => 0, 
			      			'show_option_none' => __('Not selected', 'advanced-booking-calendar'), 
			      			'option_none_value' => "0", 
			      			'selected' => getAbcSetting("bookingpage"),
			      			'name' => "bookingpage"
			      			)
			      		).' 
			   		<a target="_blank" href="edit.php?post_type=page">'.__('Manage Pages', 'advanced-booking-calendar').'</a><br />
			        <p class="description">'.__('Select the page which uses the booking form shortcode. You have to add the shortcode [abc-bookingform] manually to this page. The page will be linked, if user selects dates on the single calendar shortcode.', 'advanced-booking-calendar').'</p></div>
			    </div>
				<div class="uk-form-row">
			      <label class="uk-form-label" for="currency">'.__('Currency', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls"><input name="currency" id="currency" value="'.getAbcSetting("currency").'" class="regular-text" placeholder="&euro;" required />
				      <p class="description">'.__('Example: &euro; or $', 'advanced-booking-calendar').'</p></div>
			    </div>
				<div class="uk-form-row">
			      <label class="uk-form-label" for="priceformat">'.__('Price format', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      	 <select name="priceformat" id="priceformat">
				        <option value="," '.$priceComma.'>0,00</option>
						<option value="." '.$priceDot.'>0.00</option>
				  	  </select></div>
			    </div>
				<div class="uk-form-row">
			      <label class="uk-form-label" for="dateformat">'.__('Date format', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls"><select name="dateformat" id="dateformat">
				        <option value="Y-m-d" '.$date1.'>2016-12-15</option>
						<option value="d.m.Y" '.$date2.'>15.12.2016</option>
						<option value="d/m/Y" '.$date3.'>15/12/2016</option>
						<option value="m/d/Y" '.$date4.'>12/15/2016</option>
				  	  </select></div>
			    </div>
				<div class="uk-form-row">
			      <label class="uk-form-label" for="currencyPosition">'.__('Position of currency sign', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
				      <fieldset>
				      	<input '.$currencyPositionBefore.' type="radio" name="currencyPosition" id="currencyPositionBefore" value="0">
				      		<label for="currencyPositionBefore"> '.__('Before the amount', 'advanced-booking-calendar').' ($ 50)</label><br/>
				      	<input '.$currencyPositionAfter.' type="radio" name="currencyPosition" id="currencyPositionAfter" value="1">
				      		<label for="currencyPositionAfter"> '.__('After the amount', 'advanced-booking-calendar').' (50 $)</label>
				      </fieldset>
				  </div>
			    </div>
				<div class="uk-form-row">
			      <label class="uk-form-label" for="customCss">'.__('Custom CSS', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      		<textarea name="customCss" id="customCss" class="regular-text" >'.getAbcSetting("customCss").'</textarea>			
				     	<p class="description">'.__('Will be loaded on every page containing the shortcode or the widget.', 'advanced-booking-calendar').'
				     			</p></div>
			    </div>
			    <div class="uk-form-row">
			      <label class="uk-form-label" for="cookies">'.__('Unconfirmed Bookings block dates', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      <fieldset>
			      	<input '.$unconfirmedtrue.' type="radio" name="unconfirmed" id="unconfirmed-true" value="1"><label for="unconfirmed-true"> '.__('Enabled', 'advanced-booking-calendar').'</label><br/>
			      	<input '.$unconfirmedfalse.' type="radio" name="unconfirmed" id="unconfirmed-false" value="0"><label for="unconfirmed-false"> '.__('Disabled', 'advanced-booking-calendar').'</label>
			      </fieldset>
			      	<p class="description">'.__('If enabled, unconfirmed bookings will be handled like confirmed bookings. They will block calendars until they are rejected.', 'advanced-booking-calendar').'</p>
				     </div>
			    </div>
			    <div class="uk-form-row">
			      <label class="uk-form-label" for="cookies">'.__('Cookies', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      <fieldset>
			      	<input '.$cookiestrue.' type="radio" name="cookies" id="cookies-true" value="1"><label for="cookies-true"> '.__('Enabled', 'advanced-booking-calendar').'</label><br/>
			      	<input '.$cookiesfalse.' type="radio" name="cookies" id="cookies-false" value="0"><label for="cookies-false"> '.__('Disabled', 'advanced-booking-calendar').'</label>
			      </fieldset>
			      	<p class="description">'.__('If cookies are enabled, customer date inputs are saved in a cookie (no personal data is stored in the cookie).', 'advanced-booking-calendar').'</p>
				     </div>
			    </div>
			    <div class="uk-form-row">
			      <label class="uk-form-label" for="googleanalytics">Google Analytics:</label>
			      <div class="uk-form-controls">
			      <fieldset>
			      	<input '.$gatrue.' type="radio" name="googleanalytics" id="googleanalytics-true" value="1"><label for="googleanalytics-true"> '.__('Enabled', 'advanced-booking-calendar').'</label><br/>
			      	<input '.$gafalse.' type="radio" name="googleanalytics" id="googleanalytics-false" value="0"><label for="googleanalytics-false"> '.__('Disabled', 'advanced-booking-calendar').'</label>
			      </fieldset>
			      	<p class="description">'.__('If enabled, user interactions with calendars and forms will be tracked in your Google Universal Analytics. Please configure Universal Analytics seperately. We recommend using the following plugin:', 'advanced-booking-calendar').' 
			      			<a href="https://wordpress.org/plugins/google-universal-analytics/" target="_blank">Google Universal Analytics</a>.</p>
				     </div>
			    </div>
			    <div class="uk-form-row">
			      <label class="uk-form-label" for="poweredby">'.__('Powered-by-Link', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      <fieldset>
			      	<input '.$poweredbytrue.' type="radio" name="poweredby" id="poweredby-true" value="1"><label for="poweredby-true"> '.__('Enabled', 'advanced-booking-calendar').'</label><br/>
			      	<input '.$poweredbyfalse.' type="radio" name="poweredby" id="poweredby-false" value="0"><label for="poweredby-false"> '.__('Disabled', 'advanced-booking-calendar').'</label>
			      </fieldset>
			      	<p class="description">'.__('If link is enabled, a tiny "powered by Advanced Booking Calendar"-link will show up below the calendar overview and the booking form.', 'advanced-booking-calendar').'</p>
				     </div>
			    </div>
			    <div class="uk-form-row">
			      <label class="uk-form-label" for="deletion">'.__('Delete Data on Uninstall', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      <fieldset>
			      	<input '.$deletiontrue.' type="radio" name="deletion" id="deletion-true" value="1"><label for="deletion-true"> '.__('Enabled', 'advanced-booking-calendar').'</label><br/>
			      	<input '.$deletionfalse.' type="radio" name="deletion" id="deletion-false" value="0"><label for="deletion-false"> '.__('Disabled', 'advanced-booking-calendar').'</label>
			      </fieldset>
			      	<p class="description">'.__('If enabled, the data created by this plugin will be deleted when uninstalling the plugin.', 'advanced-booking-calendar').'</p>
				     </div>
			    </div>
			    <div class="uk-form-row">
			      <label class="uk-form-label" for="usage">'.__('Commit Usage Data', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
			      <fieldset>
			      	<input '.$usagetrue.' type="radio" name="usage" id="usage-true" value="1"><label for="usage-true"> '.__('Enabled', 'advanced-booking-calendar').'</label><br/>
			      	<input '.$usagefalse.' type="radio" name="usage" id="usage-false" value="0"><label for="usage-false"> '.__('Disabled', 'advanced-booking-calendar').'</label>
			      </fieldset>
			      	<p class="description">'.__('If enabled, you will help us to make this plugin better by committing some usage data.', 'advanced-booking-calendar').' '.__('Your visitors will not be affected. No sensible data will be transmitted.', 'advanced-booking-calendar').'</p>
				     </div>
			    </div>
			    <div class="uk-form-row">
			      <label class="uk-form-label" for="accessLevel">'.__('Access Level', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
				  	<select name="accessLevel" id="accessLevel">'.$accessLevelHtml.'</select>
				  	<p class="description">'.__('Minimum access level required for a WordPress user to access this plugin.', 'advanced-booking-calendar').'</p>
				  </div>
			    </div>
			  <div class="uk-form-row">
			      <label class="uk-form-label" for="exportTables">'.__('Export', 'advanced-booking-calendar').':</label>
			      <div class="uk-form-controls">
				  	<a href="'.admin_url( ).'?abc_booking_exportTables">'.__('Download', 'advanced-booking-calendar').'</a>
				  	<p class="description">'.__('Generate a JSON-file with all database tables and settings.', 'advanced-booking-calendar').'</p>
				</div>
			  </div>
				<div class="uk-form-row">
					<input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
				</div>	
			  </form>
			</li>
			<li>
				<h3>'.__('Booking Form Inputs', 'advanced-booking-calendar').'</h3>
				<form method="post" class="uk-form uk-form-horizontal" action="admin-post.php">
				<input type="hidden" name="action" value="abc_booking_editBookingFormSettings" />
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="firstname">'.__('First Name', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="firstname" id="firstname-required" value="2"'.$firstname[2].'> <label for="firstname-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="firstname" id="firstname-optional" value="1"'.$firstname[1].'> <label for="firstname-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="firstname" id="firstname-disabled" value="0"'.$firstname[0].'> <label for="firstname-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="last">'.__('Last Name', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="lastname" id="lastname-required" value="2"'.$lastname[2].'> <label for="lastname-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="lastname" id="lastname-optional" value="1"'.$lastname[1].'> <label for="lastname-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="lastname" id="lastname-disabled" value="0"'.$lastname[0].'> <label for="lastname-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="phone">'.__('Phone Number', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="phone" id="phone-required" value="2"'.$phone[2].'> <label for="phone-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="phone" id="phone-optional" value="1"'.$phone[1].'> <label for="phone-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="phone" id="phone-disabled" value="0"'.$phone[0].'> <label for="phone-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="street">'.__('Street Address, House no.', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="street" id="street-required" value="2"'.$street[2].'> <label for="street-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="street" id="street-optional" value="1"'.$street[1].'> <label for="street-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="street" id="street-disabled" value="0"'.$street[0].'> <label for="street-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="zip">'.__('ZIP Code', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="zip" id="zip-required" value="2"'.$zip[2].'> <label for="zip-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="zip" id="zip-optional" value="1"'.$zip[1].'> <label for="zip-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="zip" id="zip-disabled" value="0"'.$zip[0].'> <label for="zip-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="county">'.__('State / County', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="county" id="county-required" value="2"'.$county[2].'> <label for="county-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="county" id="county-optional" value="1"'.$county[1].'> <label for="county-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="county" id="county-disabled" value="0"'.$county[0].'> <label for="county-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="city">'.__('City', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="city" id="city-required" value="2"'.$city[2].'> <label for="city-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="city" id="city-optional" value="1"'.$city[1].'> <label for="city-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="city" id="city-disabled" value="0"'.$city[0].'> <label for="city-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="country">'.__('Country', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="country" id="country-required" value="2"'.$country[2].'> <label for="country-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="country" id="country-optional" value="1"'.$country[1].'> <label for="country-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="country" id="country-disabled" value="0"'.$country[0].'> <label for="country-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="message">'.__('message', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="message" id="message-required" value="2"'.$message[2].'> <label for="message-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="message" id="message-optional" value="1"'.$message[1].'> <label for="message-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="message" id="message-disabled" value="0"'.$message[0].'> <label for="message-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label>
				     	</fieldset>
			      	</div>
			    </div>
			    <div class="uk-form-row">
			    	<label class="uk-form-label" for="optincheckbox">'.__('Opt-in-Checkbox', 'advanced-booking-calendar').':</label>
			    	<div class="uk-form-controls">
				    	<fieldset>
				      		<input type="radio" name="optincheckbox" id="optincheckbox-required" value="2"'.$optincheckbox[2].'> <label for="optincheckbox-required"> '.__('Required', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="optincheckbox" id="optincheckbox-optional" value="1"'.$optincheckbox[1].'> <label for="optincheckbox-optional"> '.__('Optional', 'advanced-booking-calendar').'</label><br/>
				      		<input type="radio" name="optincheckbox" id="optincheckbox-disabled" value="0"'.$optincheckbox[0].'> <label for="optincheckbox-disabled"> '.__('Disabled', 'advanced-booking-calendar').'</label></br>
						</fieldset>
			      	</div>
			    </div>
				<div class="uk-form-row">
					<input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
				</div>	
			    </form>
				<hr />			
				<div class="uk-form-row">
					<p>'.__('Want to add a <b>custom input</b> in your booking form? Our <a href="https://booking-calendar-plugin.com/pro-download/?cmp=CustomInputs" target="_blank">Pro-Version</a> lets you add three additional inputs!', 'advanced-booking-calendar').'<br/>
						'.__('Use discount code <b>BASICUPGRADE</b> to save 10€.', 'advanced-booking-calendar').'
					</p>
				</div>	
			</li>
		   	<li>
			  <h3>'.__('Placeholders', 'advanced-booking-calendar').'</h3>
			  <p>'.__('You can use the following placeholder in both subject and text. They will be replaced with the actual content when the email is send to the guest:', 'advanced-booking-calendar').'<br/>
			  <i>'.$placeholderList.'</i></p>
			  <hr>
			  <form method="post" class="uk-form uk-form-horizontal" action="admin-post.php">
				<input type="hidden" name="action" value="abc_booking_editEmailSettings" />
				<h3>'.__('Templates', 'advanced-booking-calendar').'</h3>
				<h4>'.__('Unconfirmed Booking', 'advanced-booking-calendar').'</h4>
				<div class="uk-form-row">
					<label class="uk-form-label" for="subjectunconfirmed">'.__('Subject for an unconfirmed booking mail', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<input class="uk-form-width-large" name="subjectunconfirmed" id="subjectunconfirmed" value="'.esc_attr(stripslashes(get_option('abc_subject_unconfirmed'))).'"/>
					</div>
				</div>	
				<div class="uk-form-row">
					<label class="uk-form-label" for="textunconfirmed">'.__('Text for an unconfirmed booking mail', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<textarea class="uk-form-width-large" rows="10" name="textunconfirmed" id="textunconfirmed">'.esc_textarea(stripslashes(get_option('abc_text_unconfirmed'))).'</textarea>	
					</div>
				</div>
				<h4>'.__('Confirming an open Booking', 'advanced-booking-calendar').'</h4>
				<div class="uk-form-row">
					<label class="uk-form-label" for="subjectconfirmed">'.__('Subject for a booking confirmation mail', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<input class="uk-form-width-large" name="subjectconfirmed" id="subjectconfirmed" value="'.esc_attr(stripslashes(get_option('abc_subject_confirmed'))).'"/>
					</div>
				</div>	
				<div class="uk-form-row">
					<label class="uk-form-label" for="textconfirmed">'.__('Text for a booking confirmation mail', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<textarea class="uk-form-width-large" rows="10" name="textconfirmed" id="textconfirmed">'.esc_textarea(stripslashes(get_option('abc_text_confirmed'))).'</textarea>	
					</div>
				<h4>'.__('Canceling a confirmed Booking', 'advanced-booking-calendar').'</h4>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="subjectcanceled">'.__('Subject for a cancelation mail', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<input class="uk-form-width-large" name="subjectcanceled" id="subjectcanceled" value="'.esc_attr(stripslashes(get_option('abc_subject_canceled'))).'"/>
					</div>
				</div>	
				<div class="uk-form-row">
					<label class="uk-form-label" for="textcanceled">'.__('Text for a cancelation mail', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<textarea class="uk-form-width-large" rows="10" name="textcanceled" id="textcanceled">'.esc_textarea(stripslashes(get_option('abc_text_canceled'))).'</textarea>	
					</div>
				</div>
				<h4>'.__('Rejecting an open Booking', 'advanced-booking-calendar').'</h4>
				<div class="uk-form-row">
					<label class="uk-form-label" for="subjectrejected">'.__('Subject for a rejection mail', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<input class="uk-form-width-large" name="subjectrejected" id="subjectrejected" value="'.esc_attr(stripslashes(get_option('abc_subject_rejected'))).'"/>
					</div>
				</div>	
				<div class="uk-form-row">
					<label class="uk-form-label" for="textrejected">'.__('Text for a rejection mail', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<textarea class="uk-form-width-large" rows="10" name="textrejected" id="textrejected">'.esc_textarea(stripslashes(get_option('abc_text_rejected'))).'</textarea>	
					</div>
				</div>
				<div class="uk-form-row">
					<input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
				</div>	
			  </form>
			</li>
							
			<!--Payment Settings start-->				
			<li>
			  <h3>'.__('Payment Settings', 'advanced-booking-calendar').'</h3>
			  <form method="post" class="uk-form uk-form-horizontal" action="admin-post.php">
				<input type="hidden" name="action" value="abc_booking_editPaymentSettings" />
			  								
				<!--Cash onsite-->
			  		
				<h4>Cash onsite</h4>
				<div class="uk-form-row">
					<label class="uk-form-label" for="activateCash">'.__('Activate Cash onsite', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<input type="checkbox" class="uk-form-width-large" name="activateCash" id="activateCash" '.$activateCash.'/>
					</div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="textCash">'.__('Cash onsite Text (shown in booking form)', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<input type="text" class="uk-form-width-large" name="textCash" id="textCash" placeholder="'.__('e.g. Pay cash onsite', 'advanced-booking-calendar').'" value="'.$paymentArr["cash"]["text"].'"/>
					</div>
				</div>
				<hr />
								
				<!--On invoice-->
			  		
				<h4>On Invoice</h4>
				<div class="uk-form-row">
					<label class="uk-form-label" for="activateOnInvoice">'.__('Activate On Invoice', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<input type="checkbox" class="uk-form-width-large" name="activateOnInvoice" id="activateOnInvoice" '.$activateOnInvoice.'/>
					</div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label" for="textOnInvoice">'.__('On Invoice Payment Text (shown in booking form)', 'advanced-booking-calendar').'</label>
					<div class="uk-form-controls">
						<input type="text" class="uk-form-width-large" name="textOnInvoice" id="textOnInvoice" placeholder="'.__('e.g. Pay on invoice', 'advanced-booking-calendar').'" value="'.$paymentArr["onInvoice"]["text"].'"/>
					</div>
				</div>			
				<div class="uk-form-row">
					<input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
				</div>
				<hr />			
				<div class="uk-form-row">
					<p>'.__('If you are looking for a <strong>PayPal</strong> or <strong>Stripe</strong> integration, please take a look at our <a href="https://booking-calendar-plugin.com/pro-download/?cmp=PayPalSetting" target="_blank">Pro-Version</a>.', 'advanced-booking-calendar').'<br/>
						'.__('Use discount code <b>BASICUPGRADE</b> to save 10€.', 'advanced-booking-calendar').'
					</p>
				</div>		
			</form>
			  
			</li>
		
			<!--Text customization start-->
			<li>
			  <form method="" class="uk-form uk-form-horizontal" action="" onsubmit="return false">
				<input type="hidden" name="action" value="abc_booking_editTextCustomization" />
			  <h3>'.__('Text Customization', 'advanced-booking-calendar').'</h3>
			  <p>'.__('You can change the label of buttons in the booking form. Leave these fields empty to use default labels.', 'advanced-booking-calendar').'</p>
			  <div class="uk-form-row">
				<label class="uk-form-label" >'.__('Select language', 'advanced-booking-calendar').':</label>
				<div class="uk-form-controls">
					'.wp_dropdown_languages(array('id'=>'languageDropdown','name'=>'abcLanguage', 'echo'=>0, 'languages' => $languages, 'selected' => get_locale(), 'show_option_site_default' => false)).'
					<p class="description">'.__('Please save any changes before selecting a new language!', 'advanced-booking-calendar').'</p>
				</div>
			  </div><div class="uk-form-row">
				<label class="uk-form-label" for="textCheckAvailabilities">'.__('Text for:', 'advanced-booking-calendar').' '.__('Check availabilities', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textCheckAvailabilities" id="textCheckAvailabilities" value="'.$textCustomization["checkAvailabilities"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textSelectRoom">'.__('Text for:', 'advanced-booking-calendar').' '.__('Select room', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textSelectRoom" id="textSelectRoom" value="'.$textCustomization["selectRoom"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textSelectedRoom">'.__('Text for:', 'advanced-booking-calendar').' '.__('Selected room', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textSelectedRoom" id="textSelectedRoom" value="'.$textCustomization["selectedRoom"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textOtherRooms">'.__('Text for:', 'advanced-booking-calendar').' '.__('Other available rooms for your stay', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textOtherRooms" id="textOtherRooms" value="'.$textCustomization["otherRooms"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textNoRoom">'.__('Text for:', 'advanced-booking-calendar').' '.__('No rooms available for your search request.', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textNoRoom" id="textNoRoom" value="'.$textCustomization["noRoom"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textAvailRooms">'.__('Text for:', 'advanced-booking-calendar').' '.__('Available rooms for your stay', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textAvailRooms" id="textAvailRooms" value="'.$textCustomization["availRooms"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textRoomType">'.__('Text for:', 'advanced-booking-calendar').' '.__('Room type', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textRoomType" id="textRoomType" value="'.$textCustomization["roomType"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textYourStay">'.__('Text for:', 'advanced-booking-calendar').' '.__('Your stay', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textYourStay" id="textYourStay" value="'.$textCustomization["yourStay"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textCheckin">'.__('Text for:', 'advanced-booking-calendar').' '.__('Checkin', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textCheckin" id="textCheckin" value="'.$textCustomization["checkin"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textCheckout">'.__('Text for:', 'advanced-booking-calendar').' '.__('Checkout', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textCheckout" id="textCheckout" value="'.$textCustomization["checkout"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textBookNow">'.__('Text for:', 'advanced-booking-calendar').' '.__('Book now', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textBookNow" id="textBookNow" value="'.$textCustomization["bookNow"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textThankYou">'.__('Text for:', 'advanced-booking-calendar').' '.__('Thank you for your booking request!', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textThankYou" id="textThankYou" value="'.$textCustomization["thankYou"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textRoomPrice">'.__('Text for:', 'advanced-booking-calendar').' '.__('Price for the room', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textRoomPrice" id="textRoomPrice" value="'.$textCustomization["roomPrice"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
				<label class="uk-form-label" for="textOptin">'.__('Text for:', 'advanced-booking-calendar').' '.__('Please store my data to contact me.', 'advanced-booking-calendar').'</label>
				<div class="uk-form-controls">
					<input type="text" class="uk-form-width-large" name="textOptin" id="textOptin" value="'.$textCustomization["optin"].'"/>
				</div>
			  </div>
			  <div class="uk-form-row">
			    <input class="button button-primary" type="submit" id="abc_textCustomizationSubmit" value="'.__('Save', 'advanced-booking-calendar').'" />
			    <span id="abc_textSavingLoading" style="display:none;">'.__('Saving', 'advanced-booking-calendar').'</span>
			    <span id="abc_textSavingDone" style="display:none;">'.__('Changes have been saved!', 'advanced-booking-calendar').'</span>
			  </div>
			  </form>
			</li>
		</ul>
		</div>
		</div>';
	

}//==>advanced_booking_calendar_show_settings()
	

?>