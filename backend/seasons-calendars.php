<?php 

// Add a Calendar to DB
function abc_booking_addCalendar() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	$roomLimit = '&setting=calAdded';
	if ( isset($_POST["name"]) && isset($_POST["maxAvailabilities"]) && isset($_POST["maxUnits"]) 
			&& isset($_POST["page_id"]) && intval($_POST["minimumStayPreset"]) > 0 ){
		$er = $wpdb->get_row('SELECT SUM(maxAvailabilities) as rooms FROM '.$wpdb->prefix.'abc_calendars', ARRAY_A);
		$rooms = $er["rooms"];
		$maxAvailabilities = intval($_POST["maxAvailabilities"]);
		if( $rooms + $maxAvailabilities <= 15 ){
			$name = sanitize_text_field($_POST["name"]);
			$maxUnits = intval($_POST["maxUnits"]);
			$pricePreset = floatval($_POST["pricePreset"]);
			$minimumStayPreset = intval($_POST["minimumStayPreset"]);
			$partlyBooked = 1;
			if(isset($_POST['partlyBooked']) && intval($_POST['partlyBooked']) > 0 && intval($_POST['partlyBooked']) < $maxAvailabilities){
				$partlyBooked = intval($_POST['partlyBooked']);
			}
			$infoPage = intval($_POST["page_id"]);
			$infoText = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST["infotext"]) ) );
			$wpdb->insert( $wpdb->prefix.'abc_calendars', array('name' 			  => $name,
																					  'maxUnits' 		  => $maxUnits,
																					  'maxAvailabilities' => $maxAvailabilities,
																					  'pricePreset'		  => $pricePreset,
																					  'minimumStayPreset' => $minimumStayPreset,
																					  'partlyBooked'	  => $partlyBooked,
																					  'infoPage'		  => $infoPage,
																					  'infoText'		  => $infoText
			));
			$calendarId = $wpdb->insert_id;
			for ($i =1; $i <= $maxAvailabilities; $i++) {
				$wpdb->insert( $wpdb->prefix.'abc_rooms', 
					array(	'calendar_id'	=>	$calendarId,
							'name' 			  => $name.'-'.$i
				));
			}
			abc_booking_setPersonCount();
		} else {
			$roomLimit = "&setting=roomLimit";
		}	
	}else {
		$roomLimit = '&setting=ERROR';
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars".$roomLimit ) );
	exit;
} //==>addCalendar()
add_action( 'admin_post_abc_booking_addCalendar', 'abc_booking_addCalendar' );

// Edit Calendar after update via form
function abc_booking_editCalendar() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	$roomLimit = '&setting=changeSaved';
	if ( isset($_POST["id"]) && isset($_POST["name"]) && isset($_POST["maxAvailabilities"]) 
		&& isset($_POST["maxUnits"]) && isset($_POST["page_id"]) && intval($_POST["minimumStayPreset"]) > 0) {
		$er = $wpdb->get_row('SELECT SUM(maxAvailabilities) as rooms FROM '.$wpdb->prefix.'abc_calendars', ARRAY_A);
		$rooms = $er["rooms"];
		$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_calendars WHERE id = '.$_POST["id"], ARRAY_A);
		$rooms -= $er["maxAvailabilities"];
		$maxAvailabilities = intval($_POST["maxAvailabilities"]);
		$name = sanitize_text_field($_POST["name"]);
		$maxUnits = intval($_POST["maxUnits"]);
		$pricePreset = floatval($_POST["pricePreset"]);
		$minimumStayPreset = intval($_POST["minimumStayPreset"]);
		$partlyBooked = 1;
		if(isset($_POST['partlyBooked']) && intval($_POST['partlyBooked']) > 0 && intval($_POST['partlyBooked']) < $maxAvailabilities){
			$partlyBooked = intval($_POST['partlyBooked']);
		}
		$infoPage = intval($_POST["page_id"]);
		$infoText = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST["infotext"]) ) );
		if($rooms + $maxAvailabilities <= 15 && $er['maxAvailabilities'] <= intval($_POST["maxAvailabilities"])){
			$wpdb->update($wpdb->prefix.'abc_calendars', array('name'               => $name,
																				  'maxUnits' 		  => $maxUnits,
																				  'maxAvailabilities' => $maxAvailabilities,
																				  'pricePreset'		  => $pricePreset,
																				  'minimumStayPreset' => $minimumStayPreset,
																				  'partlyBooked'	  => $partlyBooked,
																				  'infoPage'		  => $infoPage,
																				  'infoText'		  => $infoText
			), 
																			array('id' => intval($_POST["id"])));
		} else {
			$roomLimit = "&setting=roomLimit";
		}
		if ($er['maxAvailabilities'] < $maxAvailabilities) { // Adding new rooms when room number is raised
			for($i=$er["maxAvailabilities"]; $i< $maxAvailabilities; $i++){
				$wpdb->insert( $wpdb->prefix.'abc_rooms', 
					array('calendar_id'  => $er['id'],
						  'name' 		 => $er['name'].'-'.($i+1)	));	
			}
		}
		
		if ($er['maxAvailabilities'] > $maxAvailabilities) {// Deleting unused rooms when room number is reduced
			$roomCounter = intval($er["maxAvailabilities"]);
			for($i=$maxAvailabilities; $i< $er['maxAvailabilities']; $i++){
				$subEr = $wpdb->get_row('SELECT max(id) as roomId
								FROM '.$wpdb->prefix.'abc_rooms
								WHERE calendar_id = '.intval($_POST["id"]).'
								AND id not in (
									SELECT DISTINCT room_id 
									FROM '.$wpdb->prefix.'abc_bookings
									WHERE calendar_id = '.intval($_POST["id"]).')', ARRAY_A);
				if(isset($subEr['roomId'])){
					$wpdb->delete( $wpdb->prefix.'abc_rooms', array('id' => $subEr['roomId']));						
					$roomCounter--;
				}
			}
			$wpdb->update($wpdb->prefix.'abc_calendars', array('name'               => $name,
																				  'maxUnits' 		  => $maxUnits,
																				  'maxAvailabilities' => $roomCounter,
																				  'pricePreset'		  => $pricePreset,
																				  'minimumStayPreset' => $minimumStayPreset,
																				  'partlyBooked'	  => $partlyBooked,
																				  'infoPage'		  => $infoPage,
																				  'infoText'		  => $infoText
			), 
																			array('id' => intval($_POST["id"])));
			if($roomCounter == intval($er["maxAvailabilities"])){
				$roomCounter = 0;
			}
			abc_booking_setPersonCount();
			$roomLimit = '&setting=changeSaved&room='.$roomCounter;
		}																
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars".$roomLimit ) );
	exit;
} //==>editCalendar()
add_action( 'admin_post_abc_booking_editCalendar', 'abc_booking_editCalendar' );

// Delete Calendar 
function abc_booking_delCalendar() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	if ( isset($_POST["id"]) ) {
		$er = $wpdb->get_row('SELECT count(id) as bookings FROM '.$wpdb->prefix.'abc_bookings WHERE calendar_id = '.intval($_POST["id"]), ARRAY_A);
		if($er["bookings"] > 0){ // There are still bookings for this calendar. Calendar can not be deleted.
			wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=calNotDeleted" ) );			
		}else{
			$wpdb->delete($wpdb->prefix.'abc_calendars', array('id' => intval($_POST["id"])));
			$wpdb->delete($wpdb->prefix.'abc_rooms', array('calendar_id' => intval($_POST["id"])));
			$wpdb->delete($wpdb->prefix.'abc_seasons_assignment', array('calendar_id' => intval($_POST["id"])));
			abc_booking_setPersonCount();
			wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=calDeleted" ) );
		}
	}
	exit;
} //==>delCalendar()
add_action( 'admin_post_abc_booking_delCalendar', 'abc_booking_delCalendar' );

// Add season to DB
function abc_booking_addSeason() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}

	if ( isset($_POST["title"]) && isset($_POST["price"]) && isset($_POST["minimumStay"]) && intval($_POST["minimumStay"]) >= 1 ) {
		$wpdb->insert( $wpdb->prefix.'abc_seasons', array(
			'title' => sanitize_text_field($_POST["title"]), 
			'price' => floatval($_POST["price"]), 
			'lastminute' => 0, 
			'minimumStay' => intval($_POST["minimumStay"]
			)));
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=seasonAdded" ) );
	exit;
} //==>addSeason()
add_action( 'admin_post_abc_booking_addSeason', 'abc_booking_addSeason' );

// Edit season 
function abc_booking_editSeason() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}

	if ( isset($_POST["id"]) && isset($_POST["title"]) && isset($_POST["price"]) && intval($_POST["minimumStay"]) >= 1) {
		$wpdb->update($wpdb->prefix.'abc_seasons', array(
			'title' => sanitize_text_field($_POST["title"]), 
			'price' => floatval($_POST["price"]),
			'minimumStay' => intval($_POST["minimumStay"])), 
			array('id' => intval($_POST["id"])));
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=changeSaved" ) );
	exit;
} //==>editSeason()
add_action( 'admin_post_abc_booking_editSeason', 'abc_booking_editSeason' );

// Delete season
function abc_booking_delSeason() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}

	if ( isset($_POST["id"]) ) {
		$wpdb->delete($wpdb->prefix.'abc_seasons', array('id' => intval($_POST["id"])));
		$wpdb->delete($wpdb->prefix.'abc_seasons_assignment', array('season_id' => intval($_POST["id"])));
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=seasonDeleted" ) );
	exit;
} //==>delSeasons()
add_action( 'admin_post_abc_booking_delSeason', 'abc_booking_delSeason' );

// Edit room names
function abc_booking_editRoomNames() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	
	if ( isset($_POST["name-0"]) && isset($_POST["calendarId"])) {
		$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_calendars WHERE id = '.intval($_POST["calendarId"]), ARRAY_A);
		$maxAvailabilities = $er["maxAvailabilities"];
		for ($i = 0; $i <= ($maxAvailabilities-1); $i++){
			$wpdb->update($wpdb->prefix.'abc_rooms', 
				array('name' => sanitize_text_field($_POST["name-".$i])), 
				array('id' => sanitize_text_field($_POST["id-".$i] )));
		}
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=roomnamesChanged" ) );
	exit;
} //==>editSeason()
add_action( 'admin_post_abc_booking_editRoomNames', 'abc_booking_editRoomNames' );

// Adding assignment
function abc_booking_addSeasonAssignment() {
	global $wpdb;

	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	if (isset($_POST["calendar"]) && isset($_POST["season"]) && isset($_POST["dateStart"]) && isset($_POST["dateEnd"])) {
		
		// Check if end date later as start date
		$normFromValue = abc_booking_formatDateToDB($_POST["dateStart"]);
		$normToValue = abc_booking_formatDateToDB($_POST["dateEnd"]);
		if($normFromValue > $normToValue) {
			wp_die("End Date > Start Date", "Seasons assignment", array('back_link' => true));
		}
		
		
		//check if dates overlap somehow
		$query = "SELECT COUNT(*) as co FROM ".$wpdb->prefix."abc_seasons_assignment
				 				   WHERE calendar_id = '".sanitize_text_field($_POST["calendar"])."' 
				 				   		AND (start < '".$normToValue."' AND end > '".$normFromValue."')
				 				   		AND season_id IN 
				 				   				(SELECT id FROM ".$wpdb->prefix."abc_seasons)";
		$row = $wpdb->get_results($query, ARRAY_A);
		if($row[0]["co"] > 0) {
			wp_die("Overlap", "Seasons assignment", array('back_link' => true));
		}
		
		$wpdb->insert( $wpdb->prefix.'abc_seasons_assignment', array('calendar_id' => sanitize_text_field($_POST["calendar"]), 
																						   'season_id'   => sanitize_text_field($_POST["season"]),
																						   'start'       => $normFromValue,
																						   'end'         => $normToValue
		));
	}

	wp_redirect(admin_url("admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=seasassAdded"));
	exit;
} //==>addSeasonAssignment()
add_action('admin_post_abc_booking_addSeasonAssignment', 'abc_booking_addSeasonAssignment');

// Edit Assignment
function abc_booking_editSeasonAssignment() {
	global $wpdb;

	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	
	if ( isset($_POST["id"]) && isset($_POST["calendar"]) && isset($_POST["season"]) && isset($_POST["dateStart"]) && isset($_POST["dateEnd"])) {
		$normFromValue = abc_booking_formatDateToDB($_POST["dateStart"]);
		$normToValue = abc_booking_formatDateToDB($_POST["dateEnd"]);
		// Check if start end is larger than end date
		if($normFromValue > $normToValue) {
			wp_die("End Date < Start Date", "Seasons assignment", array('back_link' => true));
		}
		// Check if dates overlap
		$row = $wpdb->get_results("SELECT COUNT(*) as co FROM ".$wpdb->prefix."abc_seasons_assignment
				 				   WHERE calendar_id = '".sanitize_text_field($_POST["calendar"])."' AND
				 				   		id != '".intval($_POST["id"])."' 
										AND (start < '".$normToValue."' AND end > '".$normFromValue."')
				 				   		AND season_id IN 
				 				   				(SELECT id FROM ".$wpdb->prefix."abc_seasons)", ARRAY_A);
		if($row[0]["co"] > 0) {
			wp_die("Overlap", "Seasons assignment", array('back_link' => true));
		}
		
		$wpdb->update($wpdb->prefix.'abc_seasons_assignment', array('calendar_id'  => sanitize_text_field($_POST["calendar"]), 
																						   'season_id'   => intval($_POST["season"]),
																						   'start'       => $normFromValue,
																						   'end'         => $normToValue
		), array('id' => intval($_POST["id"])));
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=changeSaved" ) );
	exit;
} //==>editSeason()
add_action( 'admin_post_abc_booking_editSeasonAssignment', 'abc_booking_editSeasonAssignment' );

// Delete Assignment
function abc_booking_delSeasonAssignment() {
	global $wpdb;

	if (!current_user_can( abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}

	if (isset($_POST["id"])) {
		$wpdb->delete($wpdb->prefix.'abc_seasons_assignment', array('id' => intval($_POST["id"])));
	}

	wp_redirect(admin_url("admin.php?page=advanced-booking-calendar-show-seasons-calendars&setting=seasassDeleted"));
	exit;
} //==>delSeasons()
add_action('admin_post_abc_booking_delSeasonAssignment', 'abc_booking_delSeasonAssignment');



// Output to backend
function advanced_booking_calendar_show_seasons_calendars() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	
	global $wpdb;
	global $abcUrl;
	wp_enqueue_script('uikit-js', $abcUrl.'backend/js/uikit.min.js', array('jquery'));
	wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');
	wp_enqueue_style('abc-datepicker', $abcUrl.'/frontend/css/jquery-ui.min.css');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('abc-functions', plugin_dir_url(__FILE__) . 'js/abc-functions.js', array('jquery'));
	wp_localize_script( 'abc-functions', 'abc_functions_vars', array( 'dateformat' => abc_booking_dateFormatToJS(getAbcSetting('dateformat')), 'firstday' => getAbcSetting('firstdayofweek')));
	
	$datepickerLang = array('af','ar-DZ','ar','az','be','bg','bs','ca','cs','cy-GB','da','de','el','en-AU','en-GB','en-NZ',
		'eo','es','et','eu','fa','fi','fo','fr-CA','fr-CH','fr','gl','he','hi','hr','hu','hy','id','is',
		'it-CH','it','ja','ka','kk','km','ko','ky','lb','lt','lv','mk','ml','ms','nb','nl-BE','nl','nn',
		'no','pl','pt-BR','pt','rm','ro','ru','sk','sl','sq','sr-SR','sr','sv','ta','th','tj','tr','uk',
		'vi','zh-CN','zh-HK','zh-TW');
	if(substr(get_locale(), 0,2) != 'en' && in_array(get_locale(), $datepickerLang)){
		wp_enqueue_script('jquery-datepicker-lang', $abcUrl.'frontend/js/datepicker_lang/datepicker-'.get_locale().'.js', array('jquery'));
	}elseif(substr(get_locale(), 0,2) != 'en' && in_array(substr(get_locale(), 0,2), $datepickerLang)){
		wp_enqueue_script('jquery-datepicker-lang', $abcUrl.'frontend/js/datepicker_lang/datepicker-'.substr(get_locale(), 0,2).'.js', array('jquery'));
	}

	if(isset($_GET["action"])) {
		$getAction = $_GET["action"];
	} else {
		$getAction = "";
	}
	$calendars = "";
	$output = "";
	$seasons = "";
	$calendarsAs = "";
	$seasonsAs = "";
	$assignments = "";
	$priceformat = getAbcSetting('priceformat');
	$currency = getAbcSetting('currency');
	$notices = "";
	if(isset($_GET["setting"]) ){
		switch($_GET["setting"]){
			case 'roomLimit':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Number of rooms is limited to 15 rooms! If you need more rooms, please download our <a href="https://booking-calendar-plugin.com/pro-download/?cmp=RoomLimit" target="_blank">Pro-Version</a>.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'licenseError':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('This is the Pro-Version of the <a href="https://booking-calendar-plugin.com/" target="_blank">Advanced Booking Calendar Plugin</a>, but it has never been activated. Please enter a valid license first.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'changeSaved':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Change has been saved.', 'advanced-booking-calendar' ).' ';
				
				if(isset($_GET["room"])){
					if($_GET["room"] == 0){
						$notices .= __('Rooms could not be deleted. There are still active bookings.', 'advanced-booking-calendar' );
					}else{
						$notices .= sprintf( __('Rooms reduced to %s rooms', 'abc-bookings'), $_GET["room"]);
					}
				}		
				$notices .='</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'seasonAdded':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Season has been added.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'calDeleted':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Calendar has been deleted.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'calNotDeleted':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Calendar was not deleted. There still exist bookings in this calendar.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'calAdded':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Calendar has been added.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'seasonDeleted':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Season has been deleted.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'roomnamesChanged':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Room names have been saved.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'seasassAdded':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Season assignment has been added.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'seasassDeleted':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Season assignment has been deleted.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
		}
	}	
	//Show standard page or action page
	switch($getAction){
		case "abc_booking_editCalendar":
			// Does the ID exist?
			$row = $wpdb->get_row("SELECT COUNT(*) as co FROM ".$wpdb->prefix."abc_calendars WHERE id = '".intval($_GET["id"])."'", ARRAY_A);
			if($row["co"] == 0) {
				// ID doesn't exist
				wp_die("Error! Unknown id<br />Please go <a href='admin.php?page=advanced-booking-calendar-show-seasons-calendars'>back</a>");
			} else {
				//ID exists
				$row = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_calendars WHERE id = '".intval($_GET["id"])."'", ARRAY_A);
				$output = '<div class="wrap">
					  <h3>'.__('Edit existing Calendar', 'advanced-booking-calendar').'</h3>
					  <div class="wrap">
						<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="abc_booking_editCalendar" />
						<input type="hidden" name="id" value="'.intval($_GET["id"]).'" />
						<table class="form-table">
						  <tr>
							<td><label for="name">'.__('Name', 'advanced-booking-calendar').'</label></td>
							<td align="left"><input placeholder="'.__('Room Type A', 'advanced-booking-calendar').'" name="name" id="name" type="text" value="'.$row[0]["name"].'" class="regular-text code" required />
											 <p class="description">'.__('Please choose a meaningful title.', 'advanced-booking-calendar').'</p></td>
						  </tr>
						<tr>
						<td><label for="maxAvailabilities">'.__('Number of rooms', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="10" name="maxAvailabilities" id="maxAvailabilities" type="number" value="'.$row[0]["maxAvailabilities"].'" class="regular-text code" min="1" required />
											 <p class="description">'.__('eg. max. 10 Rooms available', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="maxUnits">'.__('Max. Persons', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="5" name="maxUnits" id="maxUnits" type="number" value="'.$row[0]["maxUnits"].'" class="regular-text code" min="1" required />
											 <p class="description">'.__('eg. max. 5 persons in one room', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="pricePreset">'.__('Price Preset', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="90,00" name="pricePreset" id="pricePreset" type="number" value="'.$row[0]["pricePreset"].'" class="regular-text code" min="1" step="0.01" required />
											 <p class="description">'.__('If no season is active, the price preset will be used for price calculations', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="minimumStayPreset">'.__('Minimum Stay Preset', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="1" name="minimumStayPreset" id="minimumStayPreset" type="number" value="'.$row[0]["minimumStayPreset"].'" class="regular-text code" min="1" required />
											 <p class="description">'.__('Select the minimum number of nights a guest has to stay. You can also set different values for different seasons.', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="partlyBooked">'.__('Threshold for "partly booked"', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="1" name="partlyBooked" id="partlyBooked" type="number" value="'.$row[0]["partlyBooked"].'" class="regular-text code" min="1" required />
											 <p class="description">'.__('The number of rooms after which days in the calendars will be marked as "partly booked". Example: 3 rooms, partly booked is set to 2. Partly booked will be shown when two rooms are booked.', 'advanced-booking-calendar').'</p></td>
					  </tr>
					   <tr>
						<td><label for="page_id">'.__('Information Page', 'advanced-booking-calendar').'</label></td>
						<td>'.wp_dropdown_pages(array('echo' => 0, 'show_option_none' => "Don't link", 'option_none_value' => "0", 'selected' => $row[0]["infoPage"])).' <a target="_blank" href="edit.php?post_type=page">'.__('Manage Pages', 'advanced-booking-calendar').'</a><br />
							<p class="description">'.__('You can link to an existing Page for further information.', 'advanced-booking-calendar').'</p></td>
					  </tr>
					 <tr>
					   <td><label for="infotext">'.__('Short information text', 'advanced-booking-calendar').'<br /><em>'.__('(optional)', 'advanced-booking-calendar').'</em></label></td>
					   <td><textarea placeholder="'.__('Nice Apartment with sea view...', 'advanced-booking-calendar').'" rows="5" cols="50" maxlength="200" name="infotext" id="infotext">'.$row[0]["infoText"].'</textarea><br /><p class="description">'.__('Maximum 200 chars', 'advanced-booking-calendar').'</p></td>
					  </tr>
						</table>
						<br />
						<input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
						<a href="admin.php?page=advanced-booking-calendar-show-seasons-calendars"><input class="button button-secondary" type="button" value="'._x('Cancel', 'a change', 'advanced-booking-calendar').'" /></a>
						</form>
					  </div>
				  </div>';
			}	  
			break;
		case "abc_booking_editSeason":
			//Does the ID exist?
			$row = $wpdb->get_results("SELECT COUNT(*) as co FROM ".$wpdb->prefix."abc_seasons WHERE id = '".intval($_GET["id"])."'", ARRAY_A);
			if($row[0]["co"] == 0) {
				// ID doesn't exist
				wp_die("Error! Unknown id<br />Please go <a href='admin.php?page=advanced-booking-calendar-show-seasons-calendars'>back</a>");
			} else {
				//ID exists
				$row = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_seasons WHERE id = '".intval($_GET["id"])."'", ARRAY_A);

				$output = '<br />
					  <h3>'.__('Edit existing Season', 'advanced-booking-calendar').'</h3>
					  <div class="wrap">
						<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="abc_booking_editSeason" />
						<input type="hidden" name="id" value="'.intval($_GET["id"]).'" />
						<table class="form-table">
						  <tr>
							<td><label for="title">'.__('Name', 'advanced-booking-calendar').'</label></td>
							<td align="left"><input placeholder="'.__('Room Type A', 'advanced-booking-calendar').'" name="title" id="title" type="text" value="'.$row[0]["title"].'" class="regular-text code" required />
											 <p class="description">'.__('Please choose a meaningful title.', 'advanced-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="price">'.__('Price', 'advanced-booking-calendar').'</label></td>
							<td align="left"><input name="price" placeholder="50" id="price" value="'.$row[0]["price"].'" type="number" min="1" step="0.01" class="regular-text code" required />
											 <p class="description">'.__('The currency code will be attached automatically.', 'advanced-booking-calendar').'</p></td>
						  </tr>	
						  <tr>
							<td><label for="minimumStay">'.__('Minimum Stay', 'advanced-booking-calendar').'</label></td>
							<td align="left"><input name="minimumStay" placeholder="1" id="minimumStay" value="'.$row[0]["minimumStay"].'" type="number" min="1" class="regular-text code" required />
											 <p class="description">'.__('Enter the number of nights a guest has to stay. Bookings below this number of nights will not be accepted.', 'advanced-booking-calendar').'</p></td>
						  </tr>		
						</table>
						<br />
						<input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
						<a href="admin.php?page=advanced-booking-calendar-show-seasons-calendars"><input class="button button-secondary" type="button" value="'._x('Cancel', 'a change', 'advanced-booking-calendar').'" /></a>
						</form>
					  </div>';
			}		  
			break;
		case "abc_booking_editSeasonAssignment":
			//Does the ID exist?
			$row = $wpdb->get_results("SELECT COUNT(*) as co FROM ".$wpdb->prefix."abc_seasons_assignment WHERE id = '".intval($_GET["id"])."'", ARRAY_A);
			if($row[0]["co"] == 0) {
				// ID doesn't exist
				wp_die("Error! Unknown id<br />Please go <a href='admin.php?page=advanced-booking-calendar-show-seasons-calendars'>back</a>");
			} else {
				$row = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_seasons_assignment WHERE id = '".intval($_GET["id"])."'", ARRAY_A);
				$row = $row[0];
				
				// Getting calendars
				$calendarRows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_calendars", ARRAY_A);
				foreach($calendarRows as $crow) {
					if($crow["id"] == $row["calendar_id"]) {
						$selected = 'selected';
					} else {
						$selected = "";
					}
					$calendarsAs .= '<option value="'.$crow["id"].'" '.$selected.'>'.esc_html($crow["name"]).'</option>';
						
				}
				
				// Getting seasons
				$seasonRows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_seasons", ARRAY_A);
				foreach($seasonRows as $srow) {
					if($srow["id"] == $row["season_id"]) {
						$selected = 'selected';
					} else {
						$selected = "";
					}
					$minimumStay = intval($srow["minimumStay"]);
					$seasonsAs .= '<option value="'.$srow["id"].'" '.$selected.'>'.
						esc_html($srow["title"]).' - '.
						abc_booking_formatPrice(number_format($srow["price"], 2, $priceformat, '')).' - '.
						sprintf( _n('%d night minimum stay', '%d nights minimum stay', $minimumStay, 'advanced-booking-calendar'), $minimumStay ).'
						</option>';
				
				}
				
				
				//ID exists
				$output .= '<br />
					  <h3>'.__('Edit existing Assignment', 'advanced-booking-calendar').'</h3>
					  <div class="wrap">
						<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="abc_booking_editSeasonAssignment" />
						<input type="hidden" name="id" value="'.intval($_GET["id"]).'" />
						<table class="form-table">
						  <tr>
							<td><label for="calendar">'.__('Calendar', 'advanced-booking-calendar').'</label></td>
							<td align="left"><select name="calendar" id="calendar">'.$calendarsAs.'</select></td>
						  </tr>
						  <tr>
							<td><label for="season">'.__('Season', 'advanced-booking-calendar').'</label></td>
							<td align="left"><select name="season" id="season">'.$seasonsAs.'</select></td>
						  </tr>
						  <tr>
							<td><label for="dateStart">'.__('Start Date', 'advanced-booking-calendar').'</label></td>
							<td><input type="text" name="dateStart" id="start" value="'.abc_booking_formatDate($row["start"]).'" requiered /></td>
						  </tr>
						  <tr>
							<td><label for="dateEnd">'.__('End Date', 'advanced-booking-calendar').'</label></td>
							<td><input type="text" name="dateEnd" id="end" value="'.abc_booking_formatDate($row["end"]).'" requiered /></td>
						  </tr>
					  </table>
					
					  <br />
					  <input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
					  <a href="admin.php?page=advanced-booking-calendar-show-seasons-calendars"><input class="button button-secondary" type="button" value="'._x('Cancel', 'a change', 'advanced-booking-calendar').'" /></a>
					  </form>
					</div>';
			}	
			break;
		case "abc_booking_editRoomNames":
			//Does the ID exist?
			$row = $wpdb->get_results("SELECT COUNT(*) as co FROM ".$wpdb->prefix."abc_rooms WHERE calendar_id = '".intval($_GET["id"])."'", ARRAY_A);
			if($row[0]["co"] == 0) {
				// ID doesn't exist
				wp_die("Error! Unknown id<br />Please go <a href='admin.php?page=advanced-booking-calendar-show-seasons-calendars'>back</a>");
			} else {
				$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_calendars WHERE id = '.intval($_GET["id"]), ARRAY_A);
				$calendarName = esc_html($er["name"]);
				$maxAvailabilities = esc_html($er["maxAvailabilities"]);
				$row = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_rooms WHERE calendar_id = '".intval($_GET["id"])."'", ARRAY_A);
				
				$output = '<div class="wrap">
					  <h3>'.sprintf( __('Edit room names for calendar "%s"', 'abc-bookings'), $calendarName ).'</h3>
					  <div class="wrap">
						<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="abc_booking_editRoomNames" />
						<input type="hidden" name="calendarId" value="'.intval($_GET["id"]).'" />
						<table class="form-table">';
				for ($i = 0; $i <= ($maxAvailabilities-1); $i++){
					$output .= '<tr>
							<td>
								<label for="name-'.$i.'">'.sprintf( __('Name for room %d', 'abc-bookings'), ($i+1) ).'</label>
								<input type="hidden" name="id-'.$i.'" value="'.$row[$i]["id"].'" />
							</td>
							<td align="left">
								<input name="name-'.$i.'" id="name-'.$i.'" type="text" value="'.$row[$i]["name"].'" class="regular-text code" required />
							</td>
						  </tr>';
				}
				$output .= '</table>
						<br />
						<input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
						<a href="admin.php?page=advanced-booking-calendar-show-seasons-calendars"><input class="button button-secondary" type="button" value="'._x('Cancel', 'a change', 'advanced-booking-calendar').'" /></a>
						</form>
					  </div>
				  </div>';
			}	
			break;
		default:
			$output .= '<div class="wrap">
					<h1>'.__('Seasons & Calendars', 'advanced-booking-calendar').'</h1>
					'.$notices.'
					<div id="abctabs2">
						<ul class="uk-tab" data-uk-tab="{connect:\'#tab-content\'}">
							<li><a href="#">'.__('Calendars', 'advanced-booking-calendar').'</a></li>
							<li><a href="#">'.__('Seasons', 'advanced-booking-calendar').'</a></li>
							<li><a href="#">'.__('Season - Calendar - Assignments', 'advanced-booking-calendar').'</a></li>
						</ul>
						<ul id="tab-content" class="uk-switcher uk-margin">';
			// ----- CALENDAR SETTINGS -----
			$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars ORDER BY name', ARRAY_A);

			//Count, for adding a <tr> at every second row
			$foreachcount = 1;
			foreach($er as $row) {
				if ($foreachcount%2 == 1) {
					$class = 'class="alternate"';
				} else {
					$class = '';
				}
				
				//Max 50 char for Info Text
				if(strlen($row["infoText"]) > 50) {
					$infoText = substr(esc_textarea($row["infoText"]),0,strpos(esc_textarea($row["infoText"]), ' ', 50))."..."; 
				} else {
					$infoText = esc_textarea($row["infoText"]);
				}
				
				//Page Link
				if($row["infoPage"] == 0 || $row["infoPage"] == "") {
					$infopage = '<em>'.__('No Page selected', 'advanced-booking-calendar').'</em>';
				} else {
					$infopage = '<a href="'.get_the_permalink(esc_html($row["infoPage"])).'" target="_blank">'.get_post(esc_html($row["infoPage"]))->post_title.'</a>';
				}
				
				$calendars .= '<tr '.$class.'>
								 <td>'.esc_html($row["name"]).'</td>
								 <td>'.esc_html($row["maxAvailabilities"]).'</td>
								 <td>'.intval($row["maxUnits"]).'</td>
								 <td>'.abc_booking_formatPrice(esc_html($row["pricePreset"])).'</td>
								 <td>'.intval($row["minimumStayPreset"]).'</td>
								 <td>'.$infopage.'</td>
								 <td>'.$infoText.'</td>
								 <td>[abc-single calendar='.$row["id"].']</td>
								 <td align="left"><form style="display: inline;" action="admin.php" method="get">
													<input type="hidden" name="page" value="advanced-booking-calendar-show-seasons-calendars" />
													<input type="hidden" name="action" value="abc_booking_editCalendar" />
													<input type="hidden" name="id" value="'.$row["id"].'" />
													<input class="button button-primary" type="submit" value="'.__('Edit', 'advanced-booking-calendar').'" />
												  </form>
												  <form style="display: inline;" action="admin-post.php?action=abc_booking_delCalendar" method ="post">
													<input type="hidden" name="id" value="'.$row["id"].'" />
													<input class="button button-primary" type="submit" value="'.__('Delete', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this calendar?', 'advanced-booking-calendar').'\')" />
												  </form>
												  <form style="display: inline;" action="admin.php" method="get">
													<input type="hidden" name="page" value="advanced-booking-calendar-show-seasons-calendars" />
													<input type="hidden" name="action" value="abc_booking_editRoomNames" />
													<input type="hidden" name="id" value="'.$row["id"].'" />
													<input class="button button-primary" type="submit" value="'.__('Room Names', 'advanced-booking-calendar').'" />
												  </form></td>
							   </tr>';
				$foreachcount++;
			}

			$output .= '<li>
				  <h3>'.__('Existing Calendars', 'advanced-booking-calendar').'</h3>
				  <table class="wp-list-table widefat">
					<tr>
					  <td>'.__('Name', 'advanced-booking-calendar').'</td>
					  <td>'.__('Number of rooms', 'advanced-booking-calendar').'</td>
					  <td>'.__('Max. Persons', 'advanced-booking-calendar').'</td>
					  <td>'.__('Price Preset', 'advanced-booking-calendar').'</td>
					  <td>'.__('Min. Stay Preset', 'advanced-booking-calendar').'</td>
					  <td>'.__('Info Page', 'advanced-booking-calendar').'</td>
					  <td>'.__('Info Text', 'advanced-booking-calendar').'</td>
					  <td>'.__('Shortcode', 'advanced-booking-calendar').'</td>
					  <td align="left"></td>
					</tr>
				  '.$calendars.'
				  </table>
				  <hr/>
				  <h3>'.__('Add new Calendar', 'advanced-booking-calendar').'</h3>
				  <div class="wrap">
					<form method="post" action="admin-post.php">
					<input type="hidden" name="action" value="abc_booking_addCalendar" />
					<table class="form-table">
					  <tr>
						<td><label for="name">'.__('Name', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input name="name" placeholder="'.__('Room Type A', 'advanced-booking-calendar').'" id="name" type="text" class="regular-text code" required />
										 <p class="description">'.__('Please choose a meaningful title.', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="maxAvailabilities">'.__('Number of rooms', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="10" name="maxAvailabilities" id="maxAvailabilities" type="number" class="regular-text code" min="1" required />
											 <p class="description">'.__('eg. max. 10 Rooms available', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="maxUnits">'.__('Max. Persons', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="5" name="maxUnits" id="maxUnits" type="number" class="regular-text code" min="1" required />
											 <p class="description">'.__('eg. max. 5 persons in one room', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="pricePreset">'.__('Price Preset', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="90,00" name="pricePreset" id="pricePreset" type="number" class="regular-text code" min="1" step="0.01" required />
											 <p class="description">'.__('If no season is active, the price preset will be used for price calculations', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="minimumStayPreset">'.__('Minimum Stay Preset', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="1" name="minimumStayPreset" id="minimumStayPreset" type="number" class="regular-text code" min="1" required />
											 <p class="description">'.__('Select the minimum number of nights a guest has to stay. You can also set different values for different seasons.', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="partlyBooked">'.__('Threshold for "partly booked"', 'advanced-booking-calendar').'</label></td>
						<td align="left"><input placeholder="1" name="partlyBooked" id="partlyBooked" type="number" class="regular-text code" min="1" required />
											 <p class="description">'.__('The number of rooms after which days in the calendars will be marked as "partly booked". Example: 3 rooms, partly booked is set to 2. Partly booked will be shown when two rooms are booked.', 'advanced-booking-calendar').'</p></td>
					  </tr>
					  <tr>
						<td><label for="page_id">'.__('Information Page', 'advanced-booking-calendar').'</label></td>
						<td>'.wp_dropdown_pages(array('echo' => 0, 'show_option_none' => __("Don't link", "advanced-booking-calendar"), 'option_none_value' => "0")).' <a target="_blank" href="edit.php?post_type=page">'.__('Manage Pages', 'advanced-booking-calendar').'</a><br />
							<p class="description">'.__('You can link to an existing Page for further information.', 'advanced-booking-calendar').'</p></td>
					  </tr>
					 <tr>
					   <td><label for="infotext">'.__('Short information text', 'advanced-booking-calendar').'<br /><em>'.__('(optional)', 'advanced-booking-calendar').'</em></label></td>
					   <td><textarea placeholder="'.__('Nice Apartment with sea view...', 'advanced-booking-calendar').'" rows="5" cols="50" maxlength="200" name="infotext" id="infotext"></textarea><br /><p class="description">'.__('Maximum 200 chars', 'advanced-booking-calendar').'</p></td>
					  </tr>
					</table>
					<br />
					<input class="button button-primary" type="submit" value="'.__('Add Calendar', 'advanced-booking-calendar').'" />
					</form>
				  </div>
				  </li>';
				  
				// ----- SEASONS SETTINGS -----
				$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_seasons WHERE lastminute = 0 ORDER BY title', ARRAY_A);
				//Count, for adding a <tr> at every second row
				$foreachcount = 1;
				foreach($er as $row) {
					if ($foreachcount%2 == 1) {
						$class = 'class="alternate"';
					} else {
						$class = '';
					}
					$seasons .= '<tr '.$class.'>
										 <td>'.esc_html($row["title"]).'</td>
										 <td>'.abc_booking_formatPrice(number_format($row["price"], 2, $priceformat, '')).'</td>
										 <td>'.intval($row["minimumStay"]).'</td>
										 <td align="left"><form style="display: inline;" action="admin.php" method="get">
															<input type="hidden" name="page" value="advanced-booking-calendar-show-seasons-calendars" />
															<input type="hidden" name="action" value="abc_booking_editSeason" />
															<input type="hidden" name="id" value="'.$row["id"].'" />
															<input class="button button-primary" type="submit" value="'.__('Edit', 'advanced-booking-calendar').'" />
														  </form>
														  <form style="display: inline;" action="admin-post.php?action=abc_booking_delSeason" method ="post">
															<input type="hidden" name="id" value="'.$row["id"].'" />
															<input class="button button-primary" type="submit" value="'.__('Delete', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this Season?', 'advanced-booking-calendar').'\')" />
														  </form></td>
									   </tr>';
					$foreachcount++;
				}
				
				$output .= '<li>
					  <h3>'.__('Existing Seasons', 'advanced-booking-calendar').'</h3>
					  <table class="wp-list-table widefat">
						<tr>
						  <td>'.__('Name', 'advanced-booking-calendar').'</td>
						  <td>'.__('Price', 'advanced-booking-calendar').'</td>
						  <td>'.__('Minimum Stay', 'advanced-booking-calendar').'</td>
						  <td align="left"></td>
						</tr>
					  '.$seasons.'
					  </table>
					  <hr />
					  <h3>'.__('Add new Season', 'advanced-booking-calendar').'</h3>
					  <div class="wrap">
						<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="abc_booking_addSeason" />
						<table class="form-table">
						  <tr>
							<td><label for="title">'.__('Name', 'advanced-booking-calendar').'</label></td>
							<td align="left"><input name="title" placeholder="'.__('Summer High Price', 'advanced-booking-calendar').'" id="title" type="text" class="regular-text code" required />
											 <p class="description">'.__('Please choose a meaningful title.', 'advanced-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="price">'.__('Price', 'advanced-booking-calendar').'</label></td>
							<td align="left"><input name="price" placeholder="50" id="price" type="number" min="1" step="0.01" class="regular-text code" required />
											 <p class="description">'.__('The currency code will be attached automatically.', 'advanced-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="minimumStay">'.__('Minimum Stay', 'advanced-booking-calendar').'</label></td>
							<td align="left"><input name="minimumStay" placeholder="1" id="minimumStay" type="number" min="1" class="regular-text code" required />
											 <p class="description">'.__('Enter the number of nights a guest has to stay. Bookings below this number of nights will not be accepted.', 'advanced-booking-calendar').'</p></td>
						  </tr>
						</table>
						<br />
						<input class="button button-primary" type="submit" value="'.__('Add Season', 'advanced-booking-calendar').'" />
						</form>
					  </div>
					  </li>';
					  
				// ----- SEASON ASSIGNMENTS SETTINGS ------
				// Calendars
				$calendarRows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_calendars", ARRAY_A);
				foreach($calendarRows as $row) {
					$calendarsAs .= '<option value="'.$row["id"].'">'.esc_html($row["name"]).'</option>';
					
				}
				
				// Seasons
				$seasonRows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_seasons", ARRAY_A);
				foreach($seasonRows as $row) {
					$minimumStay = intval($row["minimumStay"]);
					$seasonsAs .= '<option value="'.$row["id"].'">'.
						esc_html($row["title"]).' - '.
						abc_booking_formatPrice(number_format($row["price"], 2, $priceformat, '')).' - '.
						sprintf( _n('%d night minimum stay', '%d nights minimum stay', $minimumStay, 'advanced-booking-calendar'), $minimumStay ).'
						</option>';
				
				}
				
				$er = $wpdb->get_results('SELECT assignments.id as ID,
												 calendars.name as calendar,
												 seasons.title as season,
												 seasons.price as price,
												 seasons.minimumStay as minimumStay,
												 assignments.start as start,
												 assignments.end as end
										  FROM '.$wpdb->prefix.'abc_seasons_assignment as assignments 
										  INNER JOIN '.$wpdb->prefix.'abc_seasons as seasons ON seasons.id = assignments.season_id
										  INNER JOIN '.$wpdb->prefix.'abc_calendars as calendars ON calendars.id = assignments.calendar_id
										  WHERE seasons.lastminute = 0
						ORDER BY calendars.name, assignments.start', ARRAY_A);
				
				//Count, for adding a <tr> at every second row
				$foreachcount = 1;
				foreach($er as $row) {
					if ($foreachcount%2 == 1) {
						$class = 'class="alternate"';
					} else {
						$class = '';
					}
					$minimumStay = intval($row["minimumStay"]);
					$assignments .= '<tr '.$class.'>
										 <td>'.esc_html($row["calendar"]).'</td>
										 <td>'.esc_html($row["season"]).' - '.abc_booking_formatPrice(number_format($row["price"], 2, $priceformat, '')).' - '.sprintf( _n('%d night minimum stay', '%d nights minimum stay', $minimumStay, 'advanced-booking-calendar'), $minimumStay ).'</td>
										 <td>'.abc_booking_formatDate($row["start"]).' - '.abc_booking_formatDate($row["end"]).'</td>
										 <td align="left"><form style="display: inline;" action="admin.php" method="get">
															<input type="hidden" name="page" value="advanced-booking-calendar-show-seasons-calendars" />
															<input type="hidden" name="action" value="abc_booking_editSeasonAssignment" />
															<input type="hidden" name="id" value="'.$row["ID"].'" />
															<input class="button button-primary" type="submit" value="'.__('Edit', 'advanced-booking-calendar').'" />
														  </form>
														  <form style="display: inline;" action="admin-post.php?action=abc_booking_delSeasonAssignment" method ="post">
															<input type="hidden" name="id" value="'.$row["ID"].'" />
															<input class="button button-primary" type="submit" value="'.__('Delete', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this Season Assignment?', 'advanced-booking-calendar').'\')" />
														  </form></td>
									   </tr>';
					$foreachcount++;
				}
				
				$output .= '<li>
						  <h3>'.__('Existing Assignments', 'advanced-booking-calendar').'</h3>
						  <table class="wp-list-table widefat">
							<tr>
							  <td>'.__('Calendar', 'advanced-booking-calendar').'</td>
							  <td>'.__('Season', 'advanced-booking-calendar').'</td>
							  <td>'.__('Date', 'advanced-booking-calendar').'</td>
							  <td align="left"></td>
							</tr>
						  '.$assignments.'
						  </table>
						  <hr />
						  <h3>'.__('Add new Assignment', 'advanced-booking-calendar').'</h3>
						  <div class="wrap">
							<form method="post" action="admin-post.php">
							<input type="hidden" name="action" value="abc_booking_addSeasonAssignment" />
							<table class="form-table">
							  <tr>
								<td><label for="calendar">'.__('Calendar', 'advanced-booking-calendar').'</label></td>
								<td align="left"><select name="calendar" id="calendar">'.$calendarsAs.'</select></td>
							  </tr>
							  <tr>
								<td><label for="season">'.__('Season', 'advanced-booking-calendar').'</label></td>
								<td align="left"><select name="season" id="season">'.$seasonsAs.'</select></td>
							  </tr>
							  <tr>
								<td><label for="dateStart">'.__('Start Date', 'advanced-booking-calendar').'</label></td>
								<td><input type="text" name="dateStart" id="start" required /></td>
							  </tr>
							  <tr>
								<td><label for="dateEnd">'.__('End Date', 'advanced-booking-calendar').'</label></td>
								<td><input type="text" name="dateEnd" id="end" required /></td>
							  </tr>
							</table>
										
							<br />
							<input class="button button-primary" type="submit" value="'.__('Add Assignment', 'advanced-booking-calendar').'" />
							</form>
						  </div>
						  </li>';
						  
				// ----- CLOSING TABS -----
				$output .= '</ul>
						</div>	
					</div>';
	}
	echo $output;
}//==>advanced_booking_calendar_show_calendars()



?>