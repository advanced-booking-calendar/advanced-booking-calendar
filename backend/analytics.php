<?php

function advanced_booking_calendar_show_analytics() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	global $wpdb;
	global $abcUrl;
	wp_enqueue_script('abc-analytics', $abcUrl.'backend/js/abc-analytics.js', array('jquery'));
	wp_enqueue_script('chartist-js', $abcUrl.'backend/js/chartist.min.js', array('jquery'));
	wp_enqueue_style( 'chartist-css', $abcUrl.'backend/css/chartist.css' );
	wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');
	wp_enqueue_style( 'analytics-css', $abcUrl.'backend/css/analytics.css' );
	$output = '<h1>'.__('Analytics', 'advanced-booking-calendar').'</h1><div class="uk-grid uk-grid-small">';
	$numberOfDays = 60; // Time range of the analysis in days
	$startDate = date_i18n('Y-m-d');
	$endDate = date_i18n('Y-m-d', strtotime("+".$numberOfDays." days"));
	$localizedVars = array();
	
	// Start: Overall Availability line chart
	$availabilityData = array();
	$availabilityLabels = array();
	$totalAvailData = array();
	
	$time = time();
	$enddate = $time + 86400*($numberOfDays-1);
	$maxAvailabilites = array();
	$maxAvailabilites['total'] = 0;
	$bookings = array();
	
	// Getting max Availabilities
	$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars ORDER BY name ASC', ARRAY_A);
	$foreachcount = 0;
	foreach($er as $row) {
		$maxAvailabilites[$foreachcount]['maxAvail'] = $row["maxAvailabilities"];
		$maxAvailabilites[$foreachcount]['id'] = $row["id"];
		$maxAvailabilites['total'] += $row["maxAvailabilities"];
		$foreachcount++;
	}
	if($foreachcount == 0){ // Check if there are calendars
		$output .= "<p>".__('There are currently no calendars.', 'advanced-booking-calendar')."
					<a href=\"admin.php?page=advanced-booking-calendar-show-seasons-calendars\">
					".__('Please create a calendar first.', 'advanced-booking-calendar')."</a></p>";
	}else {
		// Getting Bookings and calculating availability
		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_bookings 
			WHERE end >= \''.date_i18n('Y-m-d', $time).'\' AND start <= \''.date_i18n('Y-m-d', $enddate).'\'
			ORDER BY start ASC', ARRAY_A);
		
		for( $i = 0; $i < $numberOfDays; $i++) {
		    $totalCurAvail = 0;
			if($i%7 == 0){
				$availabilityLabels[] = date_i18n(getAbcSetting("dateformat"), $time); // Showing current date on the x axis only every 7 days
			} else {
				$availabilityLabels[] = '';
			}
			for( $j = 0; $j < $foreachcount; $j++) { // Setting max Availabilities for the current day
			    $currentAvailability[$j] = $maxAvailabilites[$j]['maxAvail']; 
			    $totalCurAvail += $maxAvailabilites[$j]['maxAvail'];
			}
			foreach($er as $row) { // Iterating all bookings and checking if a booking is valid for the current date 
	    		$bookings[$row["calendar_id"]][] = $row; 
	    		for( $j = 0; $j < $foreachcount; $j++) {
	    		    if($time >= strtotime($row["start"]) && $time < strtotime($row["end"]) && $row["calendar_id"] == $maxAvailabilites[$j]['id']){
	    		        $currentAvailability[$j] -= 1;
	    		        $totalCurAvail -= 1;
	    		    }
	    		}
	    	}
	    	for( $j = 0; $j < $foreachcount; $j++) { // Iterating all calendars and calculating avilability
	        	if($currentAvailability[$j] == 0){
	    			$availabilityData[$j][] = 100;
	    		} else {	
	    			$availabilityData[$j][] = round(abs(($currentAvailability[$j]/$maxAvailabilites[$j]['maxAvail'])-1)*100, 2);
	    		}  
	    	} 
			$totalAvailData[0][] = round(abs(($totalCurAvail/$maxAvailabilites['total'])-1)*100, 2);
			$time += 86400;
		}
		$localizedVars['availabilityLabels'] = json_encode($availabilityLabels);
		$localizedVars['availabilityData'] = json_encode($availabilityData);	
		$localizedVars['totalAvailData'] = json_encode($totalAvailData);
		$output .= '
			<div class="uk-width-1-1">
				<div class="abc-analytics-wrapper ">
					<span class="abc-analytics-title"><h2>'.sprintf( __('Overall Occupation Rate for the next %d days', 'advanced-booking-calendar'), $numberOfDays ).'</h2></span>
					<div class="abc-analytics-content abc-overallAvailability"></div>
				</div>	
			</div>';
		// End: Overall Availability line chart
		
		// Start: Output Availability per Calendar line chart
		$output .= '
			<div class="uk-width-1-1">
				<div class="abc-analytics-wrapper ">
					<span class="abc-analytics-title"><h2>'.sprintf( __('Occupation Rate of each calendar for the next %d days', 'advanced-booking-calendar'), $numberOfDays ).'</h2></span>
					<div class="abc-analytics-content abc-availability"></div>
				</div>
			</div>';
		// End: Availability per Calendar line chart
		
		// Start: Number of requests line chart
		$requestRows = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_requests 
						WHERE date_from <= "'.$endDate.'" 
						AND date_to >= "'.$startDate.'" 
						', ARRAY_A);
						
		$time = time();
		$dateData = array();
		$dateTicksArray = array();
		for( $i = 0; $i < $numberOfDays; $i++) {
			$requestCount = '0';
			foreach($requestRows as $row) {
				if(date_i18n('Y-m-d', $time) >= $row["date_from"] && date_i18n('Y-m-d', $time) <= $row["date_to"]){
					$requestCount++;
				}
			}
			$dateData[0][] = $requestCount;
			if($i%7 ==0){
				$dateTicksArray[] = date_i18n(getAbcSetting("dateformat"), $time);
			} else {
				$dateTicksArray[] = '';
			}	
			$time = strtotime('+1 days', $time);
		}
		$localizedVars['dateGraphData'] = json_encode($dateData);
		$localizedVars['dateGraphTicks'] = json_encode($dateTicksArray);
		
		$output .= '
			<div class="uk-width-1-1">
				<div class="abc-analytics-wrapper ">
					<span class="abc-analytics-title"><h2>'.sprintf( __('Number of requests for the next %d days', 'advanced-booking-calendar'), $numberOfDays ).'</h2></span>
					<div class="abc-analytics-content abc-requests"></div>
				</div>
			</div>';
		// End: Number of requests line chart
		
		// Start: Percentage per person pie chart 
		$personPieData = array();
		$personPieLabels = array();
		$requestRows = $wpdb->get_results('SELECT persons, count(persons) as personCount FROM '.$wpdb->prefix.'abc_requests 
					WHERE date_from <= "'.$endDate.'" 
						AND date_to >= "'.$startDate.'" 
					GROUP BY persons', ARRAY_A);
		$i = 0;
		$personCountSum = 0;
		foreach($requestRows as $row) {
			$personCountSum += $row["personCount"];
		}
		foreach($requestRows as $row) {
			$i++;
			$personsDesc = ' persons';
			if ($row["persons"] == 1){
				$personsDesc = ' person';
			}
			$percantage = round($row["personCount"]/$personCountSum*100, 2);
			$personPieLabels[] = $row["persons"].$personsDesc.', abs. '.$row["personCount"].', '.$percantage.' %';
			$personPieData[] = $row["personCount"];
		}
		$localizedVars['personPieLabels'] = json_encode($personPieLabels);
		$localizedVars['personPieData'] = json_encode($personPieData);
		$localizedVars['personCountSum'] = json_encode($personCountSum);	
		
		$output .= '
			<div class="uk-width-1-1 uk-width-medium-1-2">
				<div class="abc-analytics-wrapper ">
					<span class="abc-analytics-title"><h2>'.sprintf( __('Percentage per person for the next %d days', 'advanced-booking-calendar'), $numberOfDays ).'</h2></span>
					<div class="abc-analytics-title abc-personpie"></div>
				</div>
			</div>';
		// End: Percentage per person pie chart
		
		// Start: Revenue by Calendar
		$mainEr = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars ORDER BY name ASC', ARRAY_A);
		$outputBar = '';
		$revenueLabels = array();
		$revenueData = array();
		foreach($mainEr as $mainRow) {
			$revenueLabels[] = $mainRow["name"];
			$er = $wpdb->get_results('SELECT start, end, calendar_id, price 
										FROM '.$wpdb->prefix.'abc_bookings
										WHERE start <= "'.$endDate.'" 
										AND end >= "'.$startDate.'"
										AND price != 0
										AND state = "confirmed"
										AND calendar_id = "'.$mainRow["id"].'"
										ORDER BY start', ARRAY_A);
			$revenue = 0;								
			foreach($er as $row) { // Calculating average revenue (if Booking is starts earlier or ends later than configured time period)
				if($startDate <= $row["start"] AND $endDate >= $row["end"]) { // Booking is between start- and enddate
					$revenue += $row["price"];
				} elseif($startDate >= $row["start"] AND $endDate >= $row["end"]){ // Startdate of Booking is earlier
					$revenue += round(($row["price"]/abc_booking_dateDiffInDays(strtotime($row["end"]), strtotime($row["start"]))*abc_booking_dateDiffInDays(strtotime($row["end"]), strtotime($startDate))),2);
				} elseif($startDate <= $row["start"] AND $endDate <= $row["end"]){ // Enddate of Booking is later
					$revenue += round(($row["price"]/abc_booking_dateDiffInDays(strtotime($row["end"]), strtotime($row["start"]))*abc_booking_dateDiffInDays(strtotime($endDate), strtotime($row["start"]))),2);
				} elseif($startDate >= $row["start"] AND $endDate <= $row["end"]){ // Enddate of Booking is later
					$revenue += round(($row["price"]/abc_booking_dateDiffInDays(strtotime($row["end"]), strtotime($row["start"]))*abc_booking_dateDiffInDays(strtotime($endDate), strtotime($startDate))),2);
				}
			}
			$revenueData[0][] = $revenue;
		}
		$localizedVars['revenueLabels'] = json_encode($revenueLabels);
		$localizedVars['revenueData'] = json_encode($revenueData);
		$output .= '
			<div class="uk-width-1-1 uk-width-medium-1-2">
				<div class="abc-analytics-wrapper ">
					<span class="abc-analytics-title"><h2>'.sprintf( __('Average revenue per calendar for the next %d days', 'advanced-booking-calendar'), $numberOfDays ).'</h2></span>
					<div class="abc-analytics-title abc-revenueBar"></div>
				</div>
			</div>';
		// End: Revenue by Calendar
		$output .= '</div>'; //Closing <div class="uk-grid">
		wp_localize_script( 'abc-analytics', 'abc_graphData', $localizedVars);
	}
	echo $output;
}//==>advanced_booking_calendar_show_analytics()