<?php
/*
Plugin Name: Advanced Booking Calendar
Plugin URI: https://booking-calendar-plugin.com/
Description: The Booking System that makes managing your online reservations easy. A great Booking Calendar plugin for Accommodations.
Author: Advanced Booking Calendar
Author URI: https://booking-calendar-plugin.com
Version: 1.5.9
Text Domain: advanced-booking-calendar
Domain Path: /languages/
*/

include('functions.php');
include('widget.php');
include('backend/bookings.php');
include('backend/seasons-calendars.php');
include('frontend/shortcodes.php');
include('backend/analytics.php');
include('backend/settings.php');
include('backend/extras.php');
include('backend/coupons.php');
include('backend/tinymce.php');
include('backend/more-features.php');

global $abcUrl, $abcDir;
$abcUrl = plugin_dir_url(__FILE__);
$abcDir = dirname(__FILE__);

// All blocks located here
if( !defined( 'ABC_BLOCKS_DIR' ) ) {
	define( 'ABC_BLOCKS_DIR', dirname( __FILE__ ) . '/backend/blocks/' );
}

// check if admin
if( is_admin() ) {
	require_once( ABC_BLOCKS_DIR . 'class-abc-blocks.php' );
}

// Loading translations
add_action( 'plugins_loaded', 'abc_booking_load_textdomain' );
function abc_booking_load_textdomain() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'advanced-booking-calendar' );
	load_textdomain( 'advanced-booking-calendar', WP_LANG_DIR . '/advanced-booking-calendar-' . $locale . '.mo' );

	load_plugin_textdomain('advanced-booking-calendar', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}

//Install
function advanced_booking_calendar_install() {
	global $wpdb;
	$bookingTable = $wpdb->prefix . "abc_bookings";

	if(get_option('abc_pluginversion') === false && $wpdb->get_var("show tables like '$bookingTable'") != $bookingTable) 
	{
	$bookings = "CREATE TABLE `".$wpdb->prefix."abc_bookings` (
                 `id` int(255) NOT NULL AUTO_INCREMENT,
                 `start` date NOT NULL,
                 `end` date NOT NULL,
                 `calendar_id` int(255) NOT NULL,
                 `persons` int(32) NOT NULL,
                 `first_name` varchar(255) NOT NULL,
                 `last_name` varchar(255) NOT NULL,
                 `email` varchar(255) NOT NULL,
                 `phone` varchar(255) NOT NULL,
                 `address` varchar(255) NOT NULL,
                 `zip` varchar(255) NOT NULL,
                 `city` varchar(255) NOT NULL,
                 `county` varchar(255) NOT NULL,
                 `country` varchar(255) NOT NULL,
                 `message` text NOT NULL,
                 `price` float NOT NULL,
                 `state` varchar(32) NOT NULL,
                 `room_id` int(11) NOT NULL,
                 `payment` varchar(255) NOT NULL,
                 `payment_reference` varchar(255) NOT NULL,
                 `created` date NOT NULL,
                 PRIMARY KEY (`id`)
                ) CHARSET=utf8";

	$requests = "CREATE TABLE `".$wpdb->prefix."abc_requests` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `current_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
         `date_from` date NOT NULL,
         `date_to` date NOT NULL,
         `persons` int(10) NOT NULL,
         `successful` tinyint(1) NOT NULL,
         PRIMARY KEY (`id`)
        ) CHARSET=utf8";

	$rooms = "CREATE TABLE `".$wpdb->prefix."abc_rooms` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `calendar_id` int(11) NOT NULL,
         `name` varchar(255) NOT NULL,
         UNIQUE KEY `id_2` (`id`),
         KEY `id` (`id`)
        ) CHARSET=utf8";

	$calendars = "CREATE TABLE `".$wpdb->prefix."abc_calendars` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `name` varchar(255) NOT NULL,
         `maxUnits` int(11) NOT NULL,
         `maxAvailabilities` int(11) NOT NULL,
         `pricePreset` double NOT NULL,
         `minimumStayPreset` int(16) NOT NULL,
         `partlyBooked` int(16) NOT NULL,
         `infoPage` int(11) NOT NULL,
         `infoText` text NOT NULL,
         PRIMARY KEY (`id`)
        ) CHARSET=utf8";

	$seasons = "CREATE TABLE `".$wpdb->prefix."abc_seasons` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `title` varchar(255) NOT NULL,
         `price` double NOT NULL,
 		 `lastminute` int(11) NOT NULL,
         `minimumStay` int(16) NOT NULL,
         PRIMARY KEY (`id`)
        ) CHARSET=utf8";

	$seasonsAssignment = "CREATE TABLE `".$wpdb->prefix."abc_seasons_assignment` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `calendar_id` int(11) NOT NULL,
         `season_id` int(11) NOT NULL,
         `start` date NOT NULL,
         `end` date NOT NULL,
         PRIMARY KEY (`id`)
        ) CHARSET=utf8";
	
	$extras ="CREATE TABLE `".$wpdb->prefix."abc_extras` ( 
		`id` INT(16) NOT NULL AUTO_INCREMENT , 
		`name` TEXT NOT NULL , 
		`explanation` TEXT NOT NULL , 
		`calculation` TEXT NOT NULL ,
		`mandatory` TEXT NOT NULL , 
		`price` FLOAT(32) NOT NULL ,
		`persons` int(32) NOT NULL ,
		`order` BIGINT NOT NULL ,
		 PRIMARY KEY (`id`) ) charset=utf8";
		 
	$bookingExtras ="CREATE TABLE `".$wpdb->prefix."abc_booking_extras` ( 
		`id` INT(32) NOT NULL AUTO_INCREMENT , 
		`booking_id` INT(32) NOT NULL , 
		`extra_id` INT(32) NOT NULL , 
		 PRIMARY KEY (`id`) ) charset=utf8";
       	 
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($extras);
	dbDelta($bookingExtras);
	dbDelta($bookings);
	dbDelta($requests);
	dbDelta($rooms);
	dbDelta($calendars);
	dbDelta($seasons);
	dbDelta($seasonsAssignment);
	}
	add_option('abc_pluginversion', '159');
	add_option ('abc_email', get_option( 'admin_email' ));
	add_option ('abc_bookingpage', 0);
	add_option ('abc_dateformat', "Y-m-d");
	add_option ('abc_priceformat', ".");
	add_option ('abc_cookies', 0);
	add_option ('abc_googleanalytics', 0);
	$locale = localeconv();
	add_option ('abc_currency', $locale['currency_symbol']);
	add_option ('abc_newsletter_10th_asked', 0);
	add_option ('abc_newsletter_100th_asked', 0);
	add_option ('abc_newsletter_20000revenue_asked', 0);
	$abc_text_details = __("Your details", "abc-booking").":\n";
	$abc_greeting = __("Hi [abc_first_name]!", "abc-booking")."\n";
	$abc_goodbye = sprintf(__('Your %s-Team', 'advanced-booking-calendar'), get_option( 'blogname' ));
	$abc_text_details .= __("Room type", "abc-booking").": [abc_calendar_name]\n";
	$abc_text_details .= __("Room price", "abc-booking").": [abc_room_price]\n";
	$abc_text_details .= __("Selected extras", "abc-booking").": [abc_optional_extras]\n";
	$abc_text_details .= __("Additional costs", "abc-booking").": [abc_mandatory_extras]\n";
	$abc_text_details .= __("Discount", "abc-booking").": [abc_discount]\n";
	$abc_text_details .= __("Total price", "abc-booking").": [abc_total_price]\n";
	$abc_text_details .= __("Checkin - Checkout", "abc-booking").": [abc_checkin_date] - [abc_checkout_date]\n";
	$abc_text_details .= __("Number of guests", "abc-booking").": [abc_person_count]\n";
	$abc_text_details .= __("Full Name", "abc-booking").": [abc_first_name] - [abc_last_name]\n";
	$abc_text_details .= __("Email", "abc-booking").": [abc_email]\n";
	$abc_text_details .= __("Phone", "abc-booking").": [abc_phone]\n";
	$abc_text_details .= __("Address", "abc-booking").": [abc_address], [abc_zip] [abc_city], [abc_county], [abc_country] \n";
	$abc_text_details .= __("Your message to us", "abc-booking").": [abc_message]\n\n";
	$abc_subject_unconfirmed = sprintf(__('Your booking at %s', 'advanced-booking-calendar'),get_option( 'blogname' )).' - [abc_checkin_date] - [abc_checkout_date]';
	add_option ('abc_subject_unconfirmed', $abc_subject_unconfirmed);
	$abc_text_unconfirmed =  $abc_greeting; 
	$abc_text_unconfirmed .=  sprintf(__("Thank you for booking at %s. Your booking has not yet been confirmed. Please wait for an additional confirmation email.", "abc-booking"), get_option( 'blogname' ))."\n\n";
	$abc_text_unconfirmed .= $abc_text_details;
	$abc_text_unconfirmed .= $abc_goodbye;
	add_option ('abc_text_unconfirmed', $abc_text_unconfirmed);
	$abc_subject_confirmed = sprintf(__('Confirming your booking at %s', 'advanced-booking-calendar'),get_option( 'blogname' )).' - [abc_checkin_date] - [abc_checkout_date]';
	add_option ('abc_subject_confirmed', $abc_subject_confirmed);
	$abc_text_confirmed = $abc_greeting;
	$abc_text_confirmed .= __("We are happy to confirm your booking!", "abc-booking")."\n\n";
	$abc_text_confirmed .= $abc_text_details;
	$abc_text_confirmed .= __("If you have any questions regard your stay, feel free to contact us.", "abc-booking")."\n\n";
	$abc_text_confirmed .= $abc_goodbye;
	add_option ('abc_text_confirmed', $abc_text_confirmed);
	$abc_subject_canceled = sprintf(__('Canceling your booking at %s', 'advanced-booking-calendar'),get_option( 'blogname' ));
	add_option ('abc_subject_canceled', $abc_subject_canceled);
	$abc_text_canceled = $abc_greeting;
	$abc_text_canceled .= __("We are very sorry to cancel your booking! We already had another reservation for your requested travel period.", "abc-booking")."\n";
	$abc_text_canceled .= sprintf(__("Please check our website at %s for an alternative. We would be very happy to welcome you any time soon.", "abc-booking"), get_site_url())."\n\n";
	$abc_text_canceled .= $abc_goodbye;
	add_option ('abc_text_canceled', $abc_text_canceled);
	$abc_subject_rejected = sprintf(__('Rejecting your booking at %s', 'advanced-booking-calendar'),get_option( 'blogname' ));
	add_option ('abc_subject_rejected', $abc_subject_rejected);
	$abc_text_rejected = $abc_greeting;
	$abc_text_rejected .= __("We are very sorry to reject your booking! We already had another reservation for your requested travel period.", "abc-booking")."\n";
	$abc_text_rejected .= sprintf(__("Please check our website at %s for an alternative. We would be very happy to welcome you any time soon.", "abc-booking"), get_site_url())."\n\n";
	$abc_text_rejected .= $abc_goodbye;
	add_option ('abc_text_rejected', $abc_text_rejected);
	add_option('abc_installdate', date('Y-m-d'));
	add_option('abc_poweredby', 0);
	add_option('abc_feedbackModal01', 0);
	add_option('abc_currencyPosition', 1);
	add_option ('abc_customCss', '');
	add_option('abc_personcount', 2);
	add_option('abc_bookingform', array(
		 		'firstname' => '2',
		 		'lastname' => '2',
		 		'phone' => '2',
		 		'street' => '2',
		 		'zip' => '2',
		 		'city' => '2',
		 		'county' => '0',
		 		'country' => '2',
		 		'message' => '1',
		 		'inputs' => '8',
				'optincheckbox' => '0'
				));
	add_option('abc_bookingformvalidated', 0);
	add_option('abc_deletion', 0);
	add_option('abc_accessLevel','Administrator');
	add_option ('abc_emailcopy', 0);
	if (! wp_next_scheduled ( 'clearWaitingBookings_event' )) {
		wp_schedule_event(time(), 'daily', 'clearWaitingBookings_event');
    }
    //Insert default values for textCustomization
    $textCustomization = array();
    $textCustomization[get_locale()] = array(
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
    add_option( 'abc_textCustomization',
    		serialize($textCustomization));
    
    //Insert default values for payment Settings Array
    add_option( 'abc_paymentSettings',
    		serialize(
    				array(	'cash' => array('activate' => 'false',
    								'text' => ''),
    						'onInvoice' => array('activate' => 'false',
    								'text' => '')
    				)));
	
} //==>advanced_booking_calendar_install()
register_activation_hook( __FILE__, 'advanced_booking_calendar_install');


//Uninstall
function advanced_booking_calendar_uninstall() {
	deactivate_commitUsage();
	if(get_option('abc_deletion') == 1){
		global $wpdb;
		wp_clear_scheduled_hook('clearWaitingBookings_event');
		$wpdb->query("DROP TABLE IF EXISTS 
			`".$wpdb->prefix."abc_bookings`,
			`".$wpdb->prefix."abc_booking_extras`,
			`".$wpdb->prefix."abc_calendars`,
			`".$wpdb->prefix."abc_extras`,
			`".$wpdb->prefix."abc_requests`,
			`".$wpdb->prefix."abc_rooms`,
			`".$wpdb->prefix."abc_seasons`,
			`".$wpdb->prefix."abc_seasons_assignment`
			");
		delete_option ('abc_email');
		delete_option ('abc_bookingpage');
		delete_option ('abc_dateformat');
		delete_option ('abc_priceformat');
		delete_option ('abc_cookies');
		delete_option ('abc_googleanalytics');
		delete_option ('abc_currency');
		delete_option ('abc_newsletter_10th_asked');
		delete_option ('abc_newsletter_100th_asked');
		delete_option ('abc_newsletter_20000revenue_asked');
		delete_option ('abc_subject_unconfirmed');
		delete_option ('abc_text_unconfirmed');
		delete_option ('abc_subject_confirmed');
		delete_option ('abc_text_confirmed');
		delete_option ('abc_subject_canceled');
		delete_option ('abc_text_canceled');
		delete_option ('abc_subject_rejected');
		delete_option ('abc_text_rejected');
		delete_option('abc_pluginversion');
		delete_option('abc_installdate');
		delete_option('abc_poweredby');
		delete_option('abc_feedbackModal01');
		delete_option('abc_currencyPosition');
		delete_option('abc_bookingform');
		delete_option('abc_bookingformvalidated');
		delete_option('abc_usage');
		delete_option('abc_deletion');
		delete_option('abc_newsletter');
		delete_option('abc_paymentSettings');
		delete_option('abc_paymentString');
		delete_option('abc_personcount');
		delete_option('abc_textCustomization');
		delete_option('abc_accessLevel');
		delete_option('abc_emailcopy');
	} else {
		$adminEmail = getAbcSetting('email');
   		$headers = 'From: '.get_option('blogname').' <'.$adminEmail.'>'."\r\n";
    	$subject = __('Plugin uninstalled', 'advanced-booking-calendar').' '.get_option('blogname');
		$body = __("Hi!", "advanced-booking-calendar").' <br/>';
		$body .= __("you just uninstalled the plugin Advanced Booking Calendar on your WordPress website, but for safety reasons the data in the database created by the plugin was not deleted.",  "advanced-booking-calendar").'<br/>';
		$body .= __("If this was a mistake, please install the plugin again, go the plugins settings, tick the box for plugin deletion and uninstall it again.",  "advanced-booking-calendar").'<br/><br/>';
		$body .= __("Kindly,",  "advanced-booking-calendar").'<br/>';
		$body .= 'Team of Advanced Booking Calendar <br/> https://booking-calendar-plugin.com';
		wp_mail($adminEmail, $subject, $body, $headers);
	}	

} //==>advanced_booking_calendar_uninstall()
register_uninstall_hook( __FILE__, 'advanced_booking_calendar_uninstall');

//Backend Actions:
function advanced_booking_calendar_admin_actions() {
	$capability = abc_booking_admin_capabilities();
	//Backend Menu
	add_menu_page('Advanced Booking Calendar', 
			'Advanced Booking Calendar', 
			$capability, 
			'advanced_booking_calendar', 
			'advanced_booking_calendar_show_bookings', 
			'dashicons-calendar-alt',
			30
			);
			
	//Submenu "Bookings"
	add_submenu_page('advanced_booking_calendar',
			'Advanced Booking Calendar - '.__('Bookings', 'advanced-booking-calendar'),
			__('Bookings', 'advanced-booking-calendar'),
			$capability,
			'advanced_booking_calendar',
			'advanced_booking_calendar_show_bookings'
	);
	//Submenu "Seasons & Calendars"
	add_submenu_page('advanced_booking_calendar',
			'Advanced Booking Calendar - '.__('Seasons & Calendars', 'advanced-booking-calendar'),
			__('Seasons & Calendars', 'advanced-booking-calendar'),
			$capability,
			'advanced-booking-calendar-show-seasons-calendars',
			'advanced_booking_calendar_show_seasons_calendars'
	);
	//Submenu "Extras"
	add_submenu_page('advanced_booking_calendar',
			'Advanced Booking Calendar - '.__('Extras', 'advanced-booking-calendar'),
			__('Extras', 'advanced-booking-calendar'),
			$capability,
			'advanced-booking-calendar-show-extras',
			'advanced_booking_calendar_show_extras'
	);
	//Submenu "Analytics"
	add_submenu_page('advanced_booking_calendar',
			'Advanced Booking Calendar - '.__('Analytics', 'advanced-booking-calendar'),
			__('Analytics', 'advanced-booking-calendar'),
			$capability,
			'advanced-booking-calendar-show-analytics',
			'advanced_booking_calendar_show_analytics'
	);
	//Submenu "Settings"
	add_submenu_page('advanced_booking_calendar',
			'Advanced Booking Calendar - '.__('Settings', 'advanced-booking-calendar'),
			__('Settings', 'advanced-booking-calendar'),
			$capability,
			'advanced-booking-calendar-show-settings',
			'advanced_booking_calendar_show_settings', 'dashicons-chart-pie'
	);
	//Submenu "More Features"
	add_submenu_page('advanced_booking_calendar',
			'Advanced Booking Calendar - '.__('More Features', 'advanced-booking-calendar'),
			'<span class="dashicons dashicons-lock" style="width: 17px;height: 17px; margin-right: 4px; color: #ec4b35;font-size: 17px;vertical-align: -3px;"></span>'.__('More Features', 'advanced-booking-calendar'),
			$capability,
			'advanced-booking-calendar-more-features',
			'advanced_booking_calendar_more_features'
	);

} //==>advanced_booking_calendar_admin_actions()
add_action('admin_menu', 'advanced_booking_calendar_admin_actions');

// Links on Plugin-Page
add_filter( 'plugin_row_meta', 'abc_plugin_row_meta', 10, 2 );

function abc_plugin_row_meta( $links, $file ) {

	if ( strpos( $file, 'advanced-booking-calendar.php' ) !== false ) {
		$new_links = array(
					'<a href="https://twitter.com/BookingCal" target="_blank">Twitter</a>',
					'<a href="https://booking-calendar-plugin.com/setup-guide" target="_blank">Setup Guide</a>'
				);
		
		$links = array_merge( $links, $new_links );
	}
	
	return $links;
}

// Update Check
function advanced_booking_update_check(){
	if ( intval(get_option( 'abc_pluginversion' )) < '110' || intval(get_option( 'abc_pluginversion' )) == 0) {
		update_option('abc_pluginversion', '110');
		add_option('abc_installdate', date('Y-m-d'));
		add_option('abc_poweredby', 0);	
		add_option('abc_feedbackModal01', 0);
		add_option('abc_currencyPosition', 1);
    }
    if(intval(get_option( 'abc_pluginversion' )) < '117'){
		abc_booking_setPersonCount();
	}
    if(intval(get_option( 'abc_pluginversion' )) < '118'){
		update_option('abc_pluginversion', '118');
		global $wpdb;
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."advanced_booking_calendar_calendars` ADD `minimumStayPreset` INT(16) NOT NULL AFTER `pricePreset`;");
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."advanced_booking_calendar_seasons` ADD `minimumStay` INT(16) NOT NULL AFTER `lastminute`;");
		$wpdb->query("UPDATE `".$wpdb->prefix."advanced_booking_calendar_calendars` SET `minimumStayPreset` = 1;");
		$wpdb->query("UPDATE `".$wpdb->prefix."advanced_booking_calendar_seasons` SET `minimumStay` = 1;");
	}
	if(intval(get_option( 'abc_pluginversion' )) < '119'){
		update_option('abc_pluginversion', '119');
		global $wpdb;
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."advanced_booking_calendar_calendars` ADD `partlyBooked` INT(16) NOT NULL AFTER `minimumStayPreset`;");
		$wpdb->query("UPDATE `".$wpdb->prefix."advanced_booking_calendar_calendars` SET `partlyBooked` = 1;");
	}
	
	if(intval(get_option( 'abc_pluginversion' )) < '120'){
		update_option('abc_pluginversion', '120');
		global $wpdb;
		$extras ="CREATE TABLE `".$wpdb->prefix."advanced_booking_calendar_extras` ( 
			`id` INT(16) NOT NULL AUTO_INCREMENT , 
			`name` TEXT NOT NULL , 
			`explanation` TEXT NOT NULL , 
			`calculation` TEXT NOT NULL ,
			`mandatory` TEXT NOT NULL , 
			`price` FLOAT(32) NOT NULL ,
			 PRIMARY KEY (`id`) ) charset=utf8";
		$bookingExtras ="CREATE TABLE `".$wpdb->prefix."advanced_booking_calendar_booking_extras` ( 
			`id` INT(32) NOT NULL AUTO_INCREMENT , 
			`booking_id` INT(32) NOT NULL , 
			`extra_id` INT(32) NOT NULL , 
			 PRIMARY KEY (`id`) ) charset=utf8";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($extras);
		dbDelta($bookingExtras);
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."advanced_booking_calendar_bookings` ADD `county` TEXT NOT NULL AFTER `city`;");
		update_option('abc_bookingform', array(
		 		'firstname' => '2',
		 		'lastname' => '2',
		 		'phone' => '2',
		 		'street' => '2',
		 		'zip' => '2',
		 		'city' => '2',
		 		'county' => '0',
		 		'country' => '2',
		 		'message' => '1',
		 		'inputs' => '8'
				));
	}
	if(get_option( 'abc_pluginversion' ) < '130'){
		update_option( 'abc_pluginversion', '130');
		update_option ('abc_usage', '0');
		update_option ('abc_deletion', '0');
		if(getAbcSetting('bookingpage') > 0){
			$content_post = get_post(getAbcSetting('bookingpage'));
			if( strpos($content_post->post_content, 'abc-bookingform') !== false) {
				update_option ('abc_bookingformvalidated', 1);
			}else{
				update_option ('abc_bookingformvalidated', 0);
			}			
		}
		global $wpdb;
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."advanced_booking_calendar_bookings` ADD `payment` TEXT NOT NULL AFTER `room_id`;");
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."advanced_booking_calendar_bookings` ADD `payment_reference` TEXT NOT NULL AFTER `payment`;");
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."advanced_booking_calendar_bookings` ADD `created` date NOT NULL AFTER `payment_reference`;");
	}
	if(get_option( 'abc_pluginversion' ) < '137'){
		update_option( 'abc_pluginversion', '137');
	    //Insert default values for textCustomization
	    add_option( 'abc_textCustomization',
	    		serialize(
	    				array(
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
	    						'roomPrice' => ''
	    				)));
	    
	    //Insert default values for payment Settings Array
	    add_option( 'abc_paymentSettings',
	    		serialize(
	    				array(	'cash' => array('activate' => 'false',
	    								'text' => ''),
	    						'onInvoice' => array('activate' => 'false',
	    								'text' => '')
	    				)));
	}
	if(get_option( 'abc_pluginversion' ) < '138'){
		if(get_option( 'abc_textCustomization') != false) {
			$textCustomization = array();
			$textCustomization[get_locale()] = unserialize(get_option( 'abc_textCustomization') );
			update_option( 'abc_textCustomization', serialize($textCustomization));
		}
		update_option( 'abc_pluginversion', '138');
	}	
	if(get_option( 'abc_pluginversion' ) < '140'){
		global $wpdb;
		$extraPersonsAlter = "ALTER TABLE `".$wpdb->prefix."advanced_booking_calendar_extras` ADD `persons` INT(32) NOT NULL AFTER `price`;";
		$wpdb->query($extraPersonsAlter);
		$wpdb->query("UPDATE `".$wpdb->prefix."advanced_booking_calendar_extras` SET `persons` = 1;");
		update_option( 'abc_pluginversion', '140');
	}
	if(get_option( 'abc_pluginversion' ) < '142'){
		add_option ('abc_customCss', '');
		update_option ('abc_unconfirmed', '0');
		update_option( 'abc_pluginversion', '142');
	}	
	if(get_option( 'abc_pluginversion' ) < '143'){
		if(getAbcSetting('installdate') <= date("Y-m-d", strtotime('-1 month'))){
			update_option('abc_feedbackModal01', 0);
		}
		update_option( 'abc_pluginversion', '143');
	}	
	if(get_option( 'abc_pluginversion' ) < '144'){
		add_option('abc_accessLevel','Administrator');
		update_option( 'abc_pluginversion', '144');
	}
	if(get_option( 'abc_pluginversion' ) < '147'){
		add_option ('abc_emailcopy', 0);
		update_option( 'abc_pluginversion', '147');
	}
	if(get_option( 'abc_pluginversion' ) < '148'){
		global $wpdb;
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'advanced_booking_calendar_bookings` TO `'.$wpdb->prefix.'abc_bookings`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'advanced_booking_calendar_requests` TO `'.$wpdb->prefix.'abc_requests`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'advanced_booking_calendar_rooms` TO `'.$wpdb->prefix.'abc_rooms`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'advanced_booking_calendar_calendars` TO `'.$wpdb->prefix.'abc_calendars`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'advanced_booking_calendar_seasons` TO `'.$wpdb->prefix.'abc_seasons`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'advanced_booking_calendar_seasons_assignment` TO `'.$wpdb->prefix.'abc_seasons_assignment`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'advanced_booking_calendar_extras` TO `'.$wpdb->prefix.'abc_extras`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'advanced_booking_calendar_booking_extras` TO `'.$wpdb->prefix.'abc_booking_extras`';
		$wpdb->query($rename_table);
		update_option( 'abc_pluginversion', '148');
	}
	if(get_option( 'abc_pluginversion' ) < '152') {

		global $wpdb;

		$query = "SELECT id FROM ".$wpdb->prefix."abc_extras ORDER BY `id` DESC;";
		$extras = $wpdb->get_results( $query, ARRAY_A );
		
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."abc_extras` ADD `order` BIGINT NOT NULL AFTER `persons`;");

		foreach( $extras as $key => $extra ) {
			$wpdb->update($wpdb->prefix.'abc_extras', array(
				'order'	=> intval( $key + 1 )
			), array( 'id' => intval($extra['id'])) );
		}

		update_option( 'abc_pluginversion', '152');
	}
	if(get_option( 'abc_pluginversion' ) < '154') {
		$newOptions = array_merge(get_option('abc_bookingform'), array('optincheckbox' => '0'));
		update_option('abc_bookingform', $newOptions);
		$newTexts = array();
		if(get_option('abc_textCustomization') != false){
			$textCustomization = unserialize(get_option('abc_textCustomization'));
			foreach ($textCustomization as $locale=>$texts){
				if(is_array($texts)){
					$newTexts[$locale] = array_merge($texts, array('optin' => ''));
				}
			}
			update_option('abc_textCustomization', serialize($newTexts));
		}
		update_option( 'abc_pluginversion', '154');
	}
	if(get_option( 'abc_pluginversion' ) < '159') {
		update_option( 'abc_pluginversion', '159');
	}	
}
add_action( 'plugins_loaded', 'advanced_booking_update_check' );