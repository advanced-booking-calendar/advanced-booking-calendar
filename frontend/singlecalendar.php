<?php
function abc_booking_showSingleCalendar( $atts ) {
	if(!isset($atts['calendar'])) { return '<p>Calendar ID not set. Please check the shortcode.</p>';}
	else {
		global $abcUrl;
		wp_enqueue_style( 'styles-css', $abcUrl.'frontend/css/styles.css' );
		wp_enqueue_style( 'font-awesome', $abcUrl.'frontend/css/font-awesome.min.css' );
		global $wpdb;
		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars WHERE id = '.intval($atts['calendar']), ARRAY_A);
		if(isset($er[0])) {
			$divId = uniqid();
			$atts['uniqid'] = $divId;
			wp_enqueue_script('abc-ajax', $abcUrl.'frontend/js/abc-ajax.js', array('jquery'));
			wp_localize_script( 'abc-ajax', 'ajax_abc_booking_SingleCalendar', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'abc_nonce' => wp_create_nonce('abc-nonce'), 'abc_calendar' =>  $atts['calendar'] ));
			wp_enqueue_script('jquery-ui-button');
			wp_enqueue_style('abc-datepicker', $abcUrl.'/frontend/css/jquery-ui.min.css');
			if(getAbcSetting('firstdayofweek') == 0) {
				$weekdayRow = '<div class="abc-box abc-col-day abc-dayname">'.__('Su', 'advanced-booking-calendar').'</div>
						<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Mo', 'advanced-booking-calendar').'</div>
						<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Tu', 'advanced-booking-calendar').'</div>
						<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('We', 'advanced-booking-calendar').'</div>
						<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Th', 'advanced-booking-calendar').'</div>
						<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Fr', 'advanced-booking-calendar').'</div>
						<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Sa', 'advanced-booking-calendar').'</div>';
			} else {
				$weekdayRow = '<div class="abc-box abc-col-day abc-dayname">'.__('Mo', 'advanced-booking-calendar').'</div>
					<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Tu', 'advanced-booking-calendar').'</div>
					<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('We', 'advanced-booking-calendar').'</div>
					<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Th', 'advanced-booking-calendar').'</div>
					<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Fr', 'advanced-booking-calendar').'</div>
					<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Sa', 'advanced-booking-calendar').'</div>
					<div class="abc-box abc-col-day abc-dayname abc-dotted">'.__('Su', 'advanced-booking-calendar').'</div>';
			}	
			$calSingleOutput = abcEnqueueCustomCss().'
				<div class="abc-singlecalendar" data-checkin-'.$divId.'="0" data-offset-'.$divId.'="0" data-month-'.$divId.'="0" id="abc_singlecalendar_'.$divId.'">
					<div class="abc-box abc-single-row">
						<div data-calendar="'.sanitize_text_field($atts['calendar']).'" data-id="'.$divId.'" class="abc-box abc-button abc-single-button-left">
							<button class="fa fa-chevron-left abc-button-rl"></button>
						</div>
						<div class="abc-box abc-month">
							<img alt="'.__('Loading...', 'advanced-booking-calendar').'" src="'.admin_url('/images/wpspin_light.gif').'" class="waiting" id="abc_single_loading-'.$divId.'" style="display:none" /><span id="singlecalendar-month-'.$divId.'">'.date_i18n('F').' '.date_i18n('Y').'</span></div>
							<div data-calendar="'.sanitize_text_field($atts['calendar']).'" data-id="'.$divId.'" class="abc-box abc-button abc-single-button-right">
								<button class="fa fa-chevron-right abc-button-rl"></button>
						</div>
					</div>
					<div class="abc-box abc-single-row">
					'.$weekdayRow.'
					</div>
					<div id="abc-calendar-days-'.$divId.'">
						'.abc_booking_getSingleCalendar($atts).'
					</div>';
				if(isset($atts['legend']) && intval($atts['legend']) == 1){	
					$calSingleOutput .= '<div class="abc-single-legend">
											<span class="fa fa-square-o abc-single-legend-available"></span>
											'.__('Available', 'advanced-booking-calendar');
					if($er[0]["maxAvailabilities"] > 1){						
						$calSingleOutput .= '<span class="fa fa-square abc-single-legend-partly"></span>
											'.__('Partly booked', 'advanced-booking-calendar');
					}							
					$calSingleOutput .= '<span class="fa fa-square abc-single-legend-fully"></span>
										'.__('Fully booked', 'advanced-booking-calendar').'
										</div>';
					}
				$calSingleOutput .= '<div id="abc-booking-'.$divId.'" class="abc-booking-selection">
					</div>
				</div>';
				return $calSingleOutput;
			
		} else { return ' ID unknown.';}	
	}		
}

function abc_booking_getMonth($atts){
	if(!isset($atts['month'])) {
		$cMonth = date("n");
	} else {
		$cMonth = date("n") + intval(sanitize_text_field($atts['month']));
	}
	
	$cYear = date("Y");
	 
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	 
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
	$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
	return date_i18n('F', $timestamp).' '.date('Y', $timestamp);
}

function ajax_abc_booking_getMonth() {
	
	if(!isset( $_POST['abc_nonce'] ) || !wp_verify_nonce($_POST['abc_nonce'], 'abc-nonce') )
		die('Permissions check failed!');
		
	if(!isset($_POST['month'])){
		echo 'Month not set.';
	} else {	
		echo abc_booking_getMonth($_POST);
	}
	die();
}
add_action('wp_ajax_abc_booking_getMonth', 'ajax_abc_booking_getMonth');
add_action( 'wp_ajax_nopriv_abc_booking_getMonth', 'ajax_abc_booking_getMonth');

function abc_booking_getCssForSingleCalendar($currentAvailability, $previousAvailability, $newCurrentTime){
	$cssClass=' ';
	$cssClass = 'abc-booked ';
	$cssClass = 'abc-partly-avail ';
	$cssClass = 'abc-avail abc-date-selector ';
	$switcher = '';
	if(date('Y-m-d', $newCurrentTime)== date('Y-m-d') ){ // Fixing partly booked on today
		$previousAvailability = $currentAvailability;
	}
	switch ($previousAvailability){
		case -1:
			$switcher = 'available';
			break;
		case 0:
			$switcher = 'booked';
			break;
		default:
			$switcher = 'partly';
			break;	
	}
	switch ($currentAvailability){
		case -1:
			$switcher .= '-available';
			break;
		case 0:
			$switcher .= '-booked';
			break;
		default:
			$switcher .= '-partly';
			break;	
	}
	switch ($switcher){
		case 'available-available':
			$cssClass = 'abc-avail abc-date-selector ';
			break;
		case 'available-partly':
			$cssClass = 'abc-avail-partly-avail abc-date-selector ';
			break;
		case 'available-booked':
			$cssClass = 'abc-avail-booked abc-date-selector ';
			break;
		case 'partly-available':
			$cssClass = 'abc-partly-avail-avail abc-date-selector ';
			break;
		case 'partly-partly':
			$cssClass = 'abc-partly-avail abc-date-selector ';
			break;
		case 'partly-booked':
			$cssClass = 'abc-partly-booked abc-date-selector ';
			break;
		case 'booked-available':
			$cssClass = 'abc-booked-avail abc-date-selector ';
			break;
		case 'booked-partly':
			$cssClass = 'abc-booked-partly abc-date-selector ';
			break;
		case 'booked-booked':
			$cssClass = 'abc-booked ';
			break;
	}
	return $cssClass;
}

function abc_booking_getSingleCalendar($atts){
	$dateformat = getAbcSetting('dateformat');
	$firstdayofweek = getAbcSetting('firstdayofweek');
	$calSingleOutput ='';
	$divId = sanitize_text_field($atts['uniqid']);
	if(!isset($atts['month'])) {
		$cMonth = date("n");
	} else {
		$cMonth = date("n") + sanitize_text_field($atts['month']);
	}
	$cYear = date("Y");
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	 
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
	$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
	$maxday = date("t",$timestamp);
	$thismonth = getdate ($timestamp);
	
	// Getting confirmed Bookings for the current month
	global $wpdb;
	$normFromValue = date("Y-m-", $timestamp).'01';
	$normToValue = date("Y-m-", $timestamp).$maxday;
	$unconfirmedBookings = 'state = \'confirmed\'';
	if(get_option ('abc_unconfirmed') == 1){
		$unconfirmedBookings = '(state = \'confirmed\' OR state = \'open\')';
	}
	$query = 'SELECT * FROM '.$wpdb->prefix.'abc_bookings 
			WHERE calendar_id = '.$atts['calendar'].'
			AND '.$unconfirmedBookings.'
			AND ( (start <= \''.$normFromValue.'\' AND end >=\''.$normToValue.'\') 
				OR (start >= \''.$normFromValue.'\' AND end <= \''.$normToValue.'\') 
				OR (start >= \''.$normFromValue.'\' AND start <= \''.$normToValue.'\') 
				OR (start <= \''.$normFromValue.'\' AND end >= \''.$normToValue.'\') 
				OR (end <= \''.$normFromValue.'\' AND end >= \''.$normToValue.'\') 
				OR (end >= \''.$normFromValue.'\' AND end <= \''.$normToValue.'\') 
			)';
	$bookings = $wpdb->get_results($query, ARRAY_A);
	
	$priceDates = array();
	$lastminutePriceDates = array();
	
	 // Getting last minute offers
	$queryLastminute = 'SELECT * FROM `'.$wpdb->prefix.'abc_seasons_assignment` a
		INNER JOIN `'.$wpdb->prefix.'abc_seasons` s
		ON a.season_id = s.id
		WHERE a.calendar_id = '.intval(sanitize_text_field($atts['calendar'])).'
		AND a.end >= \''.date("Y-m-d", $timestamp).'\'
		AND s.lastminute != 0
		ORDER BY a.start';
	$er = $wpdb->get_results($queryLastminute, ARRAY_A);
	foreach($er as $row) {
		$time = strtotime(date_i18n("Y-m-d", $timestamp));
		for( $i = 0; $i < $maxday; $i++) {
			if(strtotime($row["start"]) <= $time && strtotime($row["end"]) >= $time) {
				$lastminutePriceDates[date_i18n("Y-m-d", $time)] = $row["price"];
			}
				$time += 86400;
		}
	} 
	
	// Getting Prices for the current month for the standard seasons
	$query = 'SELECT * FROM `'.$wpdb->prefix.'abc_seasons_assignment` a 
		INNER JOIN `'.$wpdb->prefix.'abc_seasons` s 
		ON a.season_id = s.id 
		WHERE a.calendar_id = '.intval(sanitize_text_field($atts['calendar'])).'
		AND a.end >= \''.date("Y-m-d", $timestamp).'\'
		AND s.lastminute = 0
		ORDER BY a.start DESC';
	$er = $wpdb->get_results($query, ARRAY_A);
	foreach($er as $row) {
		$time = strtotime(date("Y-m-d", $timestamp));
		for( $i = 0; $i < $maxday; $i++) {
			if(!isset($priceDates[date("Y-m-d", $time)]) && $row["lastminute"] == 0){
				if(strtotime($row["start"]) <= $time && strtotime($row["end"]) >= $time) {
					$priceDates[date("Y-m-d", $time)] = $row["price"];
				}
				$time += 86400;
			}
		} 
	}
	
	$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_calendars WHERE id = '.intval(sanitize_text_field($atts['calendar'])), ARRAY_A);
	$maxAvailability = $er["maxAvailabilities"];
	$pricePreset = esc_html($er["pricePreset"]);
	$calendarName = esc_html($er["name"]);
	$partlyBooked = intval($er["partlyBooked"]);
	$startday = $thismonth['wday'];
	if ($firstdayofweek == 1 ){// If first day of the week is a monday
		if($startday == 0){
			$startday = 7;
		}
	} else {
		$startday += 1;
	}	
	$cTime = $timestamp;
	$emptyDays = 0;
	$availDates = array();
	$prevAvailability = -100;
	for( $i=1; $i<($maxday+$startday); $i++ ) {

		if( $i == 1 ) {
			$prevAvailability = 0;
			$past_month_day = date( 'Y-m-d', strtotime('-1 day', $cTime) );
			$pastAvailability = getAbcAvailability( $atts['calendar'], $past_month_day, $past_month_day );

			if( $pastAvailability ) {
				$prevAvailability = -1;
			}
		}

		$cAvailability = '';
		$availDates[date('Y-m-d', $cTime)] = $maxAvailability;
		$cssClass = 'abc-box abc-col-day ';

		foreach($bookings as $br) {
			if ($cTime >= strtotime($br["start"]) && $cTime < strtotime($br["end"])){
				$availDates[date('Y-m-d', $cTime)] -= 1;
			}
		}

		if($i % 7 > 1 || $i % 7 == 0 ) {
			$cssClass .= 'abc-dotted ';
		}
		if($i % 7 == 1) {
			$calSingleOutput .= '<div class="abc-box abc-single-row">';
		}
		if($i < $startday){
			$calSingleOutput .='<div class="'.$cssClass.'">&nbsp;</div>';
			$emptyDays++;
		} else {
			$newCurrentTime = $cTime-86400*$emptyDays; // Getting a new current time, due to "empty days"
			$cPrice = 0;
			if(isset($priceDates[date('Y-m-d', $newCurrentTime)])) {
				$cPrice = abc_booking_formatPrice($priceDates[date('Y-m-d', $newCurrentTime)]);
			} else {
				$cPrice = abc_booking_formatPrice($pricePreset);
			}
			$priceOutput = '<br /><span class="abc-single-price">';				
			$title = '';
			if(isset($atts["start"]) && isset($atts["end"])){
				// Check if date has been selected by user
				if(date('Y-m-d', $newCurrentTime) >= sanitize_text_field($atts["start"]) && date('Y-m-d', $newCurrentTime) <= sanitize_text_field($atts["end"])){ 
					$cssClass .= 'abc-date-selected ';
				}
			}
			if(isset($availDates[date('Y-m-d', $newCurrentTime)]) && date('Y-m-d', $newCurrentTime)>= date('Y-m-d') ) {
				$cAvailability = $availDates[date('Y-m-d', $newCurrentTime)];
				if($cAvailability == 0){ 
					$cAvailability = '0';
					$cssClass .= abc_booking_getCssForSingleCalendar($cAvailability, $prevAvailability, $newCurrentTime);
					$priceOutput .= '&nbsp;';
					$title = ($prevAvailability <= -1 ? abc_booking_getCustomText('checkout') : __('Fully booked', 'advanced-booking-calendar') );
					$prevAvailability = $cAvailability;
				}elseif($cAvailability > 0 && ($maxAvailability-$cAvailability) >= $partlyBooked){
					$cssClass .= abc_booking_getCssForSingleCalendar($cAvailability, $prevAvailability, $newCurrentTime);
					$priceOutput .= $cPrice;
					$title = __('Partly available', 'advanced-booking-calendar')."\n".date($dateformat, $newCurrentTime).": ".$cPrice;
					$prevAvailability = $cAvailability;
				}else{
					$cAvailability = -1;
					$cssClass .= abc_booking_getCssForSingleCalendar($cAvailability, $prevAvailability, $newCurrentTime);
					$prevAvailability = -1;
					$priceOutput .= $cPrice;
					$title = __('Available', 'advanced-booking-calendar').":\n".date($dateformat, $newCurrentTime).": ".$cPrice;
				}
				switch ($cAvailability) {
					case $maxAvailability:
						break;
					case 0:
						break;
					default:
						break;
				}
			} elseif(date('Y-m-d', $newCurrentTime)>= date('Y-m-d')) {
				$cAvailability = -1;
				$cssClass .= abc_booking_getCssForSingleCalendar($cAvailability, $prevAvailability, $newCurrentTime);
				$prevAvailability = -1;
				$priceOutput .= $cPrice;
				$title = __('Available', 'advanced-booking-calendar').":\n".date($dateformat, $newCurrentTime).": ".$cPrice;
			} else {
				$cssClass .= 'abc-past ';
			}
			$priceOutput .= '</span>';
			$cssClass .= 'abc-date-item ';
			$calSingleOutput .='<div title="'.$title.'" data-calendar="'.intval(sanitize_text_field($atts['calendar'])).'" data-id="'.$divId.'" data-date="'.date('Y-m-d', $newCurrentTime).'" class="'.$cssClass.'" id="abc-day-'.$divId.date('Y-m-d', $newCurrentTime).'">'.date('j', ($newCurrentTime)).$priceOutput.'</div>';
		}
		if($i % 7 == 0 OR $i == ($maxday+$startday-1)) { // Closing row if week is over or last day of month has been reached.
			$calSingleOutput .= '</div>';
		}
		$cTime += 86400;
	}
	$calSingleOutput .= abc_booking_setPageview('single-calendar/'.sanitize_title_with_dashes($calendarName).'/'.date_i18n('Y-m', $timestamp)); // Google Analytics Tracking
	return $calSingleOutput;
}

function ajax_abc_booking_getSingleCalendar() {
	
	if(!isset( $_POST['abc_nonce'] ) || !wp_verify_nonce($_POST['abc_nonce'], 'abc-nonce') )
		die('Permissions check failed!');
		
	if(!isset($_POST['month'])){
		echo 'Month not set.';
	} else {	
		echo abc_booking_getSingleCalendar($_POST);
	}
	die();
}
add_action('wp_ajax_abc_booking_getSingleCalendar', 'ajax_abc_booking_getSingleCalendar');
add_action( 'wp_ajax_nopriv_abc_booking_getSingleCalendar', 'ajax_abc_booking_getSingleCalendar');

// Called by jQuery, when user clicks on available dates.
function ajax_abc_booking_setDataRange() {
	$output = '';
	$success = false; // Triggers Google Analytics Tracking, if user selected a date range
	if(!isset( $_POST['abc_nonce'] ) || !wp_verify_nonce($_POST['abc_nonce'], 'abc-nonce') )
		die('Permissions check failed!');
		
	if(!isset($_POST['start']) OR !isset($_POST['end'])){
		$output = 'Dates not set.';
	} else {
		$start = strtotime(sanitize_text_field($_POST['start']));
		$end = strtotime(sanitize_text_field($_POST['end']));
		$calendarId = sanitize_text_field($_POST['calendar']);
		$dateformat = getAbcSetting('dateformat');
		$currency = getAbcSetting('currency');
		if($start != 0){
			$output .= '<div class="abc-column"><b>'.abc_booking_getCustomText('checkin').':</b> '.date($dateformat, $start).'<br/>
				<b>'.abc_booking_getCustomText('checkout').':</b> ';
			if($end != 0 && $end > $start){
				$success = true;
				$output .= date($dateformat, $end);
				$numberOfDays = abc_booking_dateDiffInDays($end, $start);
				$output .= '<br/><b>'.abc_booking_getCustomText('roomPrice').': </b>
						'.abc_booking_formatPrice(abc_booking_getTotalPrice($calendarId, date("Y-m-d", $start), $numberOfDays));
				$minimumStay = abc_booking_checkMinimumStay($calendarId, sanitize_text_field($_POST['start']), sanitize_text_field($_POST['end']));		
				if($minimumStay > 0){ // Checking if the minimum number of nights to stay is reached
					$output .= '</div>
						<div class="abc-column"><b>'.sprintf( __('Your stay is too short. Minimum stay for those dates is %d nights.', 'advanced-booking-calendar'), $minimumStay ).'</b>';
				}elseif(getAbcSetting("bookingpage") > 0 && get_option('abc_bookingformvalidated') == 1){ // Checking if bookingpage in the settings has been defined
					$output .='</div>
						<div class="abc-column">
							<form action="'.get_permalink(getAbcSetting("bookingpage")).'" method="post">
							<button class="abc-submit">
								<span class="abc-submit-text">'.abc_booking_getCustomText('bookNow').'</span>
							</button>
							';
				}
				if(getAbcSetting("cookies") == 1) { // Storing selected dates in cookie, if activated
					$domain = str_replace('www', '', str_replace('https://','',str_replace('http://','',get_site_url()))); // Getting domain-name for creating cookies
					setcookie('abc-from', date($dateformat, $start), time()+3600*24*30*6, '/',  $domain);
					setcookie('abc-to', date($dateformat, $end), time()+3600*24*30*6, '/', $domain );
					setcookie('abc-calendar', $calendarId, time()+3600*24*30*6, '/', $domain );
				} 
					$output .= '<input type="hidden" name="abc-from" value="'.date($dateformat, $start).'">';
					$output .= '<input type="hidden" name="abc-to" value="'.date($dateformat, $end).'">';
					$output .= '<input type="hidden" name="abc-calendarId" value="'.$calendarId.'">';
					$output .= '<input type="hidden" name="abc-trigger" value="'.$calendarId.'">';
				$output .= '</form>';	
			} else {
				$output .= '-';
			}
			$output .= '</div><div style="clear:both"></div>';
		}
	}
	if($success){
		global $wpdb;
		$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_calendars WHERE id = '.$calendarId, ARRAY_A);
		$calendarName = esc_html($er["name"]);
		$output .= abc_booking_setPageview('single-calendar/'.sanitize_title_with_dashes($calendarName).'/date-selected'); // Google Analytics Tracking
	}
	echo $output;
	die();
}
add_action('wp_ajax_abc_booking_setDataRange', 'ajax_abc_booking_setDataRange');
add_action( 'wp_ajax_nopriv_abc_booking_setDataRange', 'ajax_abc_booking_setDataRange');
?>