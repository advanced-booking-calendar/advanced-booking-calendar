<?php

//Confirm a booking by id
function abc_booking_confBooking() {
	global $wpdb;
	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	$rows = '';
	if ( isset($_GET["id"]) ) {	
		$bookingId = intval($_GET["id"]);
		$row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."abc_bookings WHERE id = '".$bookingId ."'", ARRAY_A);
		if(getAbcAvailability($row["calendar_id"], $row["start"], $row["end"], 1, $bookingId)){
			$wpdb->update($wpdb->prefix.'abc_bookings', array('state' => 'confirmed'), array('id' => $bookingId)); // Setting booking-state to confirmed
			if (filter_var($row["email"], FILTER_VALIDATE_EMAIL)) {
				$row["state"] = 'confirmed';
				$row["extras"] = getAbcExtrasForBooking($bookingId);
				sendAbcGuestMail($row);
			}
			wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&setting=confirmed" ) );
		} else {
			wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&setting=error" ) );
		}		 
	}
		
	exit;
} //==>abc_booking_confBooking()
add_action( 'admin_post_abc_booking_confBooking', 'abc_booking_confBooking' );
	
//Deleting a booking by id
function abc_booking_delBooking() {
	global $wpdb;
	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	if ( isset($_GET["id"]) && intval($_GET["id"]) > 0 ) {
		$wpdb->delete($wpdb->prefix.'abc_bookings', array('id' => intval($_GET["id"])));
		wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&setting=deleted" ) );
	}else{
		echo 'Something went wrong.';
	}
	exit;
} //==>abc_booking_delBooking()
add_action( 'admin_post_abc_booking_delBooking', 'abc_booking_delBooking' );

//Redirect confirm- or reject-call when logged out
function abc_booking_loggedOut() {
	if (isset($_GET['action']) && isset($_GET['id']) && intval($_GET['id']) > 0 
		&& ($_GET['action'] == 'abc_booking_confBooking' || $_GET['action'] == 'abc_booking_rejBooking')){
			wp_redirect(  wp_login_url(  admin_url( "admin-post.php?action=".$_GET['action']."&id=".intval($_GET['id']) ) ));
	}
}//==>abc_booking_loggedOut()
add_action( 'admin_post_nopriv_abc_booking_confBooking', 'abc_booking_loggedOut' );
add_action( 'admin_post_nopriv_abc_booking_rejBooking', 'abc_booking_loggedOut' );

//Cancel a confirmed booking by id
function abc_booking_cancBooking() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	if (isset($_GET["id"])) {
		$bookingId = intval($_GET["id"]);
		$wpdb->update($wpdb->prefix.'abc_bookings', array('state' => 'canceled'), array('id' => $bookingId )); // Setting booking-state to confirmed
		$row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."abc_bookings WHERE id = '".$bookingId."'", ARRAY_A);
		if (filter_var($row["email"], FILTER_VALIDATE_EMAIL)) {
			$row["extras"] = getAbcExtrasForBooking($bookingId);
			sendAbcGuestMail($row);
		}
		wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&setting=canceled" ) );
	} else {
		wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&setting=error" ) );
	}	
	exit;
} //==>abc_booking_cancBooking()
add_action( 'admin_post_abc_booking_cancBooking', 'abc_booking_cancBooking' );

//Reject an open booking by id
function abc_booking_rejBooking() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}

	if ( isset($_GET["id"]) ) {
		$bookingId = intval($_GET["id"]);
		$wpdb->update($wpdb->prefix.'abc_bookings', array('state' => 'rejected'), array('id' => $bookingId));
		$row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."abc_bookings WHERE id = '".$bookingId."'", ARRAY_A);
		if (filter_var($row["email"], FILTER_VALIDATE_EMAIL)) {
				$row["extras"] = getAbcExtrasForBooking($bookingId);
				sendAbcGuestMail($row);
		}
	}
	wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&setting=rejected" ) );
	exit;
} //==>abc_booking_rejBooking()
add_action( 'admin_post_abc_booking_rejBooking', 'abc_booking_rejBooking' );

function ajax_abc_booking_getBookingContent(){
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}
	$sorting = '';
	if(isset($_POST["sorting"])){
		$sorting = sanitize_text_field($_POST["sorting"]);
	}
	if(isset($_POST["offset"]) && isset($_POST["state"]) && isset($_POST["itemsOnPage"])){
		$offset = intval($_POST["offset"]);
		$state = sanitize_text_field($_POST["state"]);
		$itemsOnPage = intval($_POST["itemsOnPage"]);
		echo abc_booking_getBookingContent($state, $offset, $itemsOnPage, $sorting);
	}
	die();  
} //==>ajax_abc_booking_getBookingContent()
add_action('wp_ajax_abc_booking_getBookingContent', 'ajax_abc_booking_getBookingContent');	

function abc_booking_getBookingContent($state, $offset = 0, $itemsOnPage = 10, $sorting = 'standard') { // Returns table and accordion with all booking items, depending on the booking-state and the offset (used by ajax_getBookingContent())
	global $wpdb;
	// Getting Calendar Names
	$calendarNames = array();
	$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars ORDER BY id', ARRAY_A);
	foreach($er as $row) {
		$calendarNames[$row["id"]] = $row["name"];
	}
	$roomNames = array();
	$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_rooms ORDER BY id', ARRAY_A);
	foreach($er as $row) {
		$roomNames[$row["id"]] = $row["name"];
	}
	$extras = array();
	$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_extras ORDER BY id', ARRAY_A);
	foreach($er as $row) {
		$extras[$row["id"]] = $row["name"];
	}
	
	$coupons = array();
	if($state == 'canceled'){
		$state = 'canceled", "rejected';
	}
	
	$where = '';
	switch( $sorting ) {
		case 'checkoutDesc':
			$where = 'end DESC';
			break;
		case 'checkoutAsc':
			$where = 'end ASC';
			break;
		case 'lastnameAsc':
			$where = 'last_name ASC';
			break;
		case 'lastnameDesc':
			$where = 'last_name DESC';
			break;
		case 'priceAsc':
			$where = 'price ASC';
			break;
		case 'priceDesc':
			$where = 'price DESC';
			break;
		case 'createdAsc':
			$where = 'id ASC';
			break;
		case 'createdDesc':
			$where = 'id DESC';
			break;
		default:
			$where = 'end DESC';
			break;
			
	}

	$paymentArr = get_option( 'abc_paymentSettings' );
	$paymentArr = unserialize( $paymentArr );

	$paymentActive = false;	$paymentTitle = '';
	if( $paymentArr["cash"]["activate"] == 'true' && $paymentArr["onInvoice"]["activate"] == 'true' ) {
		$paymentTitle = '<th>'.__('Payment', 'advanced-booking-calendar').'</th>';
		$paymentActive = true;
	}

	//Getting actual booking items
	$query = 'SELECT * FROM '.$wpdb->prefix.'abc_bookings WHERE state in ("'.$state.'") ORDER BY '.$where.' LIMIT '.$offset.', '.$itemsOnPage;
	$er = $wpdb->get_results($query, ARRAY_A);
	
	$foreachcount = 1;
	$dateformat = getAbcSetting('dateformat');
	$tables = '<div class="uk-overflow-container abcBookingsTable">
				<table class="uk-table uk-table-condensed uk-table-striped uk-table-hover">
				<thead>
					<tr>
						<th>'.__('ID', 'advanced-booking-calendar').'</th>
						<th>'.__('Checkin', 'advanced-booking-calendar').' - '.__('Checkout', 'advanced-booking-calendar').'</th>
						<th>'.__('Calendar', 'advanced-booking-calendar').', '.__('Room', 'advanced-booking-calendar').'</th>
						<th>'.__('Name', 'advanced-booking-calendar').', '.__('Email', 'advanced-booking-calendar').', '.__('Phone', 'advanced-booking-calendar').'</th>
						<th>'.__('Address', 'advanced-booking-calendar').'</th>
						<th>'.__('Price', 'advanced-booking-calendar').', '.__('Extras', 'advanced-booking-calendar').'</th>
						'.$paymentTitle.'
						<th>'.__('Message', 'advanced-booking-calendar').'</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>';
	$accordion = '<div class="abcBookingsAccordion">';	
	foreach($er as $row) {
		// Cutting message to 20 characters
		if(strlen($row["message"]) > 20) {
			$message = '<span data-uk-tooltip="{pos:\'left\'}" title="'.esc_html($row["message"]).'">'.esc_html(substr($row["message"],0,strpos($row["message"], " ", 20))).'...</span>'; 
		} else {
			$message = esc_textarea($row["message"]);
		}
		$extraOutput = '';
		if(count($extras) > 0){
			$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_booking_extras WHERE booking_id = "'.$row["id"].'"', ARRAY_A);
			foreach($er as $extrarow) {
				if(strlen($extraOutput) > 0){
					$extraOutput .= ', ';
				}
				$extraOutput .= $extras[$extrarow["extra_id"]];
			}
		}
		$extraOutputTable = '';
		if(strlen($extraOutput) > 0){
			$extraOutputTable = '<br>'.$extraOutput;
		}else{
			$extraOutput = __('No extras', 'advanced-booking-calendar');
		}
		$couponOutput = '';
		
		$payment = '';
		$paymentAccordion = __('n/a', 'advanced-booking-calendar');
		if( $paymentActive ) {
			
			/*if($row["payment"] !== 'paypal' && strlen($row["payment"]) > 0){
				$paymentAccordion = $paymentArr['cash']["text"];
			}elseif($row["payment"] == 'paypal'){
				$paymentAccordion = 'PayPal, ('.__('Transaction ID', 'advanced-booking-calendar').': '.$row["payment_reference"].')';	
			}*/

			switch( $row["payment"] ) {
				case 'cash':
					$paymentAccordion = $paymentArr['cash']["text"];
					break;
				case 'onInvoice':
					$paymentAccordion = $paymentArr['onInvoice']["text"];
			}

			$payment .= '<td>'.$paymentAccordion.'</td>';
			$paymentAccordion = __('Payment', 'advanced-booking-calendar').': '.$paymentAccordion.'<br/>';
		}
		
		$tables .= '<tr>
							<td>#'.$row['id'].'</td>

							 <td>'.date_i18n($dateformat, strtotime($row["start"])).' - '.date_i18n($dateformat, strtotime($row["end"])).'</td>
							 <td>'.esc_html($calendarNames[$row["calendar_id"]]).', '.esc_html($roomNames[$row["room_id"]]).'</td>
							 <td style="word-break: break-all; word-wrap: break-word;">';
		$accordion .= '<div class="uk-width-1-1 uk-panel-box">
						<h3>'.esc_html($row["last_name"]).', '.date_i18n($dateformat, strtotime($row["start"])).' - '.date_i18n($dateformat, strtotime($row["end"])).', '.esc_html($calendarNames[$row["calendar_id"]]).', '.$roomNames[$row["room_id"]].'</h3>
		
							<p>';
		$filled = false;
		if(!empty($row["first_name"])){
			$tables .= esc_html($row["first_name"]).' ';
			$accordion .= esc_html($row["first_name"]).' ';
			$filled = true;
		}
		if(!empty($row["last_name"])){
			$tables .= esc_html($row["last_name"]);
			$accordion .= esc_html($row["last_name"]);
			$filled = true;
		}
		if(!empty($row["email"])){
			if($filled){
				$tables .= ', ';
				$accordion .= '<br/>';
			}
			$tables .= '<a href="mailto:'.esc_html($row["email"]).'?subject='.__('Booking Request', 'advanced-booking-calendar').' '.get_option('blogname').'">'.esc_html($row["email"]).'</a>';
			$accordion .= '<a href="mailto:'.esc_html($row["email"]).'?subject='.__('Booking Request', 'advanced-booking-calendar').' '.get_option('blogname').'">'.esc_html($row["email"]).'</a>';
			$filled = true;
		}
		if(!empty($row["phone"])){
			if($filled){
				$tables .= '<br/>';
				$accordion .= '<br/>';
			}
			$tables .= '<a href="tel:'.esc_html($row["phone"]).'">'.esc_html($row["phone"]).'</a>';
			$accordion .= '<a href="tel:'.esc_html($row["phone"]).'">'.esc_html($row["phone"]).'</a><br/>';
		}
		$tables .= '</td><td>';
		$filled = false;
		if(!empty($row["address"])){
			$tables .= esc_html($row["address"]);
			$accordion .= '<br/>'.esc_html($row["address"]);
			$filled = true;
		}
		if(!empty($row["zip"])){
			if($filled){
				$tables .= '<br/>';
				$accordion .= ', ';
			}
			$tables .= esc_html($row["zip"]);
			$accordion .= esc_html($row["zip"]);
			$filled = true;
		}
		if(!empty($row["city"])){
			if($filled){
				$tables .= ' ';
				$accordion .= ' ';
			}
			$tables .= esc_html($row["city"]);
			$accordion .= esc_html($row["city"]);
			$filled = true;
		}	
		if(!empty($row["county"])){
			if($filled){
				$tables .= ',<br/>';
				$accordion .= ', ';
			}
			$tables .= esc_html($row["county"]);
			$accordion .= esc_html($row["county"]);
			$filled = true;
		}	
		if(!empty($row["country"])){
			if($filled){
				$tables .= ', ';
				$accordion .= ', ';
			}
			$tables .= esc_html($row["country"]);
			$accordion .= esc_html($row["country"]);
			$filled = true;
		}	
		$tables .='<td>'.abc_booking_formatPrice(esc_html($row["price"])).$couponOutput.$extraOutputTable.'</td>
							 '.$payment.'
							 <td>'.$message.'</td>
							<td align="left" style="line-height:30px">';
							
		$accordion .= '<br/>'.__('Extras', 'advanced-booking-calendar').': '.$extraOutput.'<br/>
							'.__('Total price', 'advanced-booking-calendar').': '.abc_booking_formatPrice(esc_html($row["price"])).$couponOutput.'<br/>
							'.$paymentAccordion.'

							'.__('Message', 'advanced-booking-calendar').': '.esc_textarea($row["message"]).'</p>';
		$buttons = '';
		switch ($row["state"]) {
			case 'open':
				$buttons .= '<form style="display: inline;" action="admin-post.php?action=abc_booking_confBooking&id='.$row["id"].'" method="post">
								<input class="uk-button uk-button-small uk-button-primary" type="submit" value="'.__('Confirm', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to confirm this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  <form style="display: inline;" action="admin-post.php?action=abc_booking_rejBooking&id='.$row["id"].'" method="post">
								<input class="uk-button uk-button-small uk-button-primary" type="submit" value="'.__('Reject', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to reject this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  ';
				break;
			case 'confirmed':
				$buttons .= '
							<form style="display: inline;" action="admin.php" method="get">
								<input type="hidden" name="page" value="advanced_booking_calendar" />
								<input type="hidden" name="action" value="editBookingRoom" />
								<input type="hidden" name="id" value="'.$row["id"].'" />
								<input class="uk-button uk-button-small uk-button-primary" type="submit" value="'.__('Change room', 'advanced-booking-calendar').'" />
							</form>
							  <form style="display: inline;" action="admin-post.php?action=abc_booking_cancBooking&id='.$row["id"].'" method="post">
								<input class="uk-button uk-button-small uk-button-primary" type="submit" value="'._x('Cancel', 'a booking', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to cancel this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  ';
				break;
			case 'canceled':
				$buttons .= '<form style="display: inline;" action="admin-post.php?action=abc_booking_confBooking&id='.$row["id"].'" method="post">
								<input class="uk-button uk-button-small uk-button-primary" type="submit" value="'.__('Confirm', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to confirm this booking (it has already been canceled)?', 'advanced-booking-calendar').'\')" />
							  </form>
							  <form style="display: inline;" action="admin-post.php?action=abc_booking_delBooking&id='.$row["id"].'" method="post">
								<input class="uk-button uk-button-small uk-button-primary" type="submit" value="'.__('Delete', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  ';
				break;
			case 'rejected':
				$buttons .= '<form style="display: inline;" action="admin-post.php?action=abc_booking_confBooking&id='.$row["id"].'" method="post">
								<input class="uk-button uk-button-small uk-button-primary" type="submit" value="'.__('Confirm', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to confirm this booking (it has already been rejected)?', 'advanced-booking-calendar').'\')" />
							  </form>
							  <form style="display: inline;" action="admin-post.php?action=abc_booking_delBooking&id='.$row["id"].'" method="post">
								<input class="uk-button uk-button-small uk-button-primary" type="submit" value="'.__('Delete', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  ';
				break;
		}					 
		$tables .= $buttons.'<br/><form style="display: inline;" action="admin.php" method="get">
								<input type="hidden" name="page" value="advanced_booking_calendar" />
								<input type="hidden" name="action" value="editBooking" />
								<input type="hidden" name="id" value="'.$row["id"].'" />
								<input class="uk-button uk-button-small" type="submit" value="'.__('Edit', 'advanced-booking-calendar').'" />
					 		 </form>
							  <form style="display: inline;" action="admin.php" method="get">
								<input type="hidden" name="page" value="advanced_booking_calendar" />
								<input type="hidden" name="action" value="customMessage" />
								<input type="hidden" name="id" value="'.$row["id"].'" />
								<input class="uk-button uk-button-small" type="submit" value="'.__('Custom message', 'advanced-booking-calendar').'" />
					 		 </form>
							</div></td></tr>';
	
		$accordion .= '<div class="uk-clearfix">'.$buttons.'
						<form style="display: inline;" action="admin.php" method="get">
								<input type="hidden" name="page" value="advanced_booking_calendar" />
								<input type="hidden" name="action" value="editBooking" />
								<input type="hidden" name="id" value="'.$row["id"].'" />
								<input class="uk-button uk-button-small" type="submit" value="'.__('Edit', 'advanced-booking-calendar').'" />
					 	</form>
						<form style="display: inline;" action="admin.php" method="get">
								<input type="hidden" name="page" value="advanced_booking_calendar" />
								<input type="hidden" name="action" value="customMessage" />
								<input type="hidden" name="id" value="'.$row["id"].'" />
								<input class="uk-button uk-button-small" type="submit" value="'.__('Custom message', 'advanced-booking-calendar').'" />
						</form>


						<div class="uk-float-right"><a href="#top" class="uk-button uk-button-mini" data-uk-smooth-scroll>'.__('Scroll up', 'advanced-booking-calendar').'</a></div>
						</div></div>';
		$accordion .= '<hr class="uk-grid-divider">';
		$foreachcount++;
	}
	$tables .= '</tbody></table></div>';
	$accordion .= '</div>';
	$bookings = $tables.$accordion;
	return $bookings;
} // ==>getBookingContent()

function abc_booking_getBookings($state){
	global $wpdb;
	global $abcUrl;
	$bookings = '';
	$divId = 'abc-'.preg_replace ( '/[^a-z0-9]/i', '', $state ); // Used to identify the div-element in jQuery
	$itemsOnPage = 10; // Defines number of items been shown on one page
	$sorting = '<div class="uk-grid" data-uk-grid-margin="">
						 <div class="uk-width-1-2 uk-row-first">
							<div class="uk-button-dropdown" data-uk-dropdown >
                                    <button class="uk-button"><span id="button'.$divId.'"><i class="uk-icon-sort-alpha-desc"></i> '.__('Checkout', 'advanced-booking-calendar').'</span> <i class="uk-icon-caret-down"></i></button>
                                    <div class="uk-dropdown">
                                        <ul class="uk-nav uk-nav-dropdown">
                                    		<li class="uk-nav-header">'.__('Sorting', 'advanced-booking-calendar').'</li>
                                        	<li>
                                            	<a class="abc-sortingOption" data-abc-divid="'.$divId.'" data-abc-state="'.$state.'" data-abc-sorting="checkoutAsc" href="#">
                                        			<i class="uk-icon-sort-alpha-asc"></i> '.__('Checkout', 'advanced-booking-calendar').'
                                        		</a>
                                        	</li>
                                            <li>
                                    			<a class="abc-sortingOption" data-abc-divid="'.$divId.'" data-abc-state="'.$state.'" data-abc-sorting="checkoutDesc" href="#">
                                            		<i class="uk-icon-sort-alpha-desc"></i> '.__('Checkout', 'advanced-booking-calendar').'
                                            	</a>
                                            </li>
                                        	<li>
                                            	<a class="abc-sortingOption" data-abc-divid="'.$divId.'" data-abc-state="'.$state.'" data-abc-sorting="lastnameAsc" href="#">
                                        			<i class="uk-icon-sort-alpha-asc"></i> '.__('Last Name', 'advanced-booking-calendar').'
                                        		</a>
                                        	</li>
                                        	<li>
                                            	<a class="abc-sortingOption" data-abc-divid="'.$divId.'" data-abc-state="'.$state.'" data-abc-sorting="lastnameDesc" href="#">
                                        			<i class="uk-icon-sort-alpha-desc"></i> '.__('Last Name', 'advanced-booking-calendar').'
                                        		</a>
                                        	</li>
                                        	<li>
                                            	<a class="abc-sortingOption" data-abc-divid="'.$divId.'" data-abc-state="'.$state.'" data-abc-sorting="priceAsc" href="#">
                                        			<i class="uk-icon-sort-numeric-asc"></i> '.__('Price', 'advanced-booking-calendar').'
                                        		</a>
                                        	</li>
                                        	<li>
                                            	<a class="abc-sortingOption" data-abc-divid="'.$divId.'" data-abc-state="'.$state.'" data-abc-sorting="priceDesc" href="#">
                                        			<i class="uk-icon-sort-numeric-desc"></i> '.__('Price', 'advanced-booking-calendar').'
                                        		</a>
                                        	</li>
                                        	<li>
                                            	<a class="abc-sortingOption" data-abc-divid="'.$divId.'" data-abc-state="'.$state.'" data-abc-sorting="createdAsc" href="#">
                                        			<i class="uk-icon-sort-numeric-asc"></i> '.__('Created at', 'advanced-booking-calendar').'
                                        		</a>
                                        	</li>
                                        	<li>
                                            	<a class="abc-sortingOption" data-abc-divid="'.$divId.'" data-abc-state="'.$state.'" data-abc-sorting="createdDesc" href="#">
                                        			<i class="uk-icon-sort-numeric-desc"></i> '.__('Created at', 'advanced-booking-calendar').'
                                        		</a>
                                        	</li>
                                        </ul>
                                    </div>
                                </div>
							</div>
							<div class="uk-width-1-2">';
	// Getting number of Bookings
	$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_bookings WHERE state in ("'.$state.'") ORDER BY id DESC', ARRAY_A);
	if($wpdb->num_rows > $itemsOnPage){ // Checking if pagination is needed
		$sorting .= '<ul class="uk-pagination uk-pagination-right" data-abc-sorting="standard" data-abc-divid="'.$divId.'" data-abc-itemsonpage="'.$itemsOnPage.'" 
					data-abc-state="'.$state.'" data-uk-pagination="{items:'.$wpdb->num_rows.', itemsOnPage:'.$itemsOnPage.'}"></ul>
					 ';
		$sorting .='</div></div>';
		$bookings = abc_booking_getBookingContent($state, 0, $itemsOnPage); 
	} elseif($wpdb->num_rows > 0) {  // Checking if there are any Bookings for $state
		$bookings = abc_booking_getBookingContent($state, 0, $itemsOnPage); // Getting content
		$sorting .='</div></div>';
	}  else {
		$bookings = '<p>'.__('No Bookings found.', 'advanced-booking-calendar').'</p><p>'.__('Take a look at our <a href="https://booking-calendar-plugin.com/pro-download/?cmp=PayPalSetting" target="_blank">Pro-Version</a> to benefit from more features like payment via PayPal or Stripe.', 'advanced-booking-calendar').'</p>';
		$sorting ='';
	}
	return $sorting.'<div id="'.$divId.'">'.$bookings.'</div>';
}

// Returns an option list for the manual booking function
function abc_booking_getCalOptList(){
	global $wpdb;
	$optionList = '<option disabled value="" selected>'.__('Select...', 'advanced-booking-calendar').'</option>';
	$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars ORDER BY id', ARRAY_A);
	foreach($er as $row) {
		$optionList .= '<option value="'.$row["id"].'">'.esc_html($row["name"]).'</option>';
	}
	return $optionList;
}

// Returns an option list for the manual booking function
function ajax_abc_booking_getPersonList(){
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}	
	
	if(isset($_POST["calId"])){
		global $wpdb;
		$optionList = '';
		$row = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."abc_calendars WHERE id = '".intval($_POST["calId"])."'", ARRAY_A);
		for ($i=1; $i <= $row[0]["maxUnits"]; $i++) {
			$optionList .= '<option value="'.$i.'">'.$i.'</option>';
		}
		echo $optionList;
	}
	die();	
} //==>ajax_getPersonList()
add_action('wp_ajax_abc_booking_getPersonList', 'ajax_abc_booking_getPersonList');

// Returns a status for to dates in a calendar
function ajax_abc_booking_checkDates(){
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}	
	
	$output = '';
	
	if(isset($_POST["calId"]) && isset($_POST["from"]) && isset($_POST["to"])){
		global $wpdb;
		$abcFromValue = sanitize_text_field($_POST["from"]); 
		$abcToValue = sanitize_text_field($_POST["to"]);
		$calId = intval($_POST["calId"]);
		$bookingId = 0;
		if(isset($_POST["bookingId"])){
			$bookingId = intval($_POST["bookingId"]);
		}
		$output = '';
		$dateformat = getAbcSetting("dateformat");
		// Normalizing entered dates
		$normFromValue = abc_booking_formatDateToDB($abcFromValue);
		$normToValue = abc_booking_formatDateToDB($abcToValue);
		if(getAbcAvailability($calId, $normFromValue, $normToValue, 1, $bookingId)){ // Checking if calendar is available for the time frame
			$numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
			$totalPrice = abc_booking_getTotalPrice($calId, $normFromValue, $numberOfDays);
			$output = '<span class="uk-text-success">'.__('Calendar is available.', 'advanced-booking-calendar').' ';
			$output .= __('Room price for the stay', 'advanced-booking-calendar').': <span id="abc-room-price"  class="abc-priceindicator" data-price="'.$totalPrice.'">'.abc_booking_formatPrice($totalPrice).'</span></span>';
			if(getAbcRoomId($calId, $normFromValue, $normToValue, 1, $bookingId)<1){
				$output .= '<br/><span class="uk-text-danger">'.__('There is an overlap in the bookings. Check if you can move other bookings to other rooms. The booking can only be saved as "open"', 'advanced-booking-calendar').'.</span>';
			}
		} else {
			$output .= '<span class="uk-text-danger">'.__('No rooms available for this calendar during the selected time period', 'advanced-booking-calendar').'<span>';
		}
	} else { 
		$output = ''.__('Something went wrong.', 'advanced-booking-calendar').'';
	}
	echo $output;	
	die();	
}
add_action('wp_ajax_abc_booking_checkDates', 'ajax_abc_booking_checkDates');

// Returns all optional Extras including the prices for a date range
function ajax_abc_booking_getOptionalExtras(){
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}	
	
	$output = '';
	if(isset($_POST["persons"]) && isset($_POST["from"]) && isset($_POST["to"])){
		$extrasSelected = explode(',', sanitize_text_field($_POST["extrasList"]));
		$normFromValue = abc_booking_formatDateToDB($_POST["from"]);
		$normToValue = abc_booking_formatDateToDB($_POST["to"]);
		$numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
		$abcPersons = intval($_POST["persons"]);
		$extrasOptional = getAbcExtrasList($numberOfDays, $abcPersons, 1);
		foreach($extrasOptional as $extra){
			$checked = '';	
			$priceClass = '';
			if(in_array($extra["id"], $extrasSelected)){
				$checked = ' checked';
				$priceClass = ' abc-priceindicator';
			}	
			$tempText = '<span class="abc-extra-name abc-pointer">'.$extra["name"].', '.abc_booking_formatPrice($extra["priceValue"]).'</span>';
			if(strlen($extra["explanation"]) > 1){
				$tempText .= '<span class="abc-extra-cost abc-pointer"></br>('.$extra["priceText"].')</span>';
			}	
			$output .= '<div class="abc-column">
											<div class="abc-option">
												<div class="abc-optional-column-checkbox">
													<input type="checkbox" id="checkbox'.$extra["id"].'" name="abc-extras-checkbox" class="abc-extra-checkbox'.$priceClass.'" data-price="'.$extra["priceValue"].'" value="'.$extra["id"].'"'.$checked.'>
												</div>
												<div class="abc-optional-column-text"><label for="checkbox'.$extra["id"].'">'.$tempText.'</label></div>
											</div>
										</div>';
		}
		echo $output;	
		die();
	}	
}
add_action('wp_ajax_abc_booking_getOptionalExtras', 'ajax_abc_booking_getOptionalExtras');

// Returns all mandatory Extras including the prices for a date range
function ajax_abc_booking_getMandatoryExtras(){
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}	
	
	$output = '';
	if(isset($_POST["persons"]) && isset($_POST["from"]) && isset($_POST["to"])){
		$normFromValue = abc_booking_formatDateToDB($_POST["from"]);
		$normToValue = abc_booking_formatDateToDB($_POST["to"]);
		$numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
		$abcPersons = intval($_POST["persons"]);
		$extrasOptional = getAbcExtrasList($numberOfDays, $abcPersons, 2);
		foreach($extrasOptional as $extra){
			$tempText = '<span class="abc-extra-name">'.$extra["name"].', '.abc_booking_formatPrice($extra["priceValue"]).'</span>';
			if(strlen($extra["explanation"]) > 1){
				$tempText .= '<span class="abc-extra-cost"></br>('.$extra["priceText"].')</span>';
			}	
			$output .= '<div class="abc-column">
											<div class="abc-option">
												<div class="abc-optional-column-text abc-priceindicator" data-price="'.$extra["priceValue"].'">'.$tempText.'</div>
											</div>
										</div>';
		}
		echo $output;	
		die();
	}	
}
add_action('wp_ajax_abc_booking_getMandatoryExtras', 'ajax_abc_booking_getMandatoryExtras');

function postAbcBooking() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	$_POST['start'] = abc_booking_formatDateToDB($_POST['start']);
	$_POST['end'] = abc_booking_formatDateToDB($_POST['end']);
	if(!isset($_POST["payment"])){
		$_POST["payment"] = 'n/a';
	}

	$booking_id = setAbcBooking( $_POST );
	if( $_POST["emailconfirmation"] == 'yes' && $booking_id ) {
		
		$booking_data = array_merge( $_POST, array('id' => $booking_id) );
		sendAbcGuestMail( $booking_data );
	}
	
	wp_redirect(admin_url("admin.php?page=advanced_booking_calendar&setting=booked-".$_POST["state"]));

	exit;
} //==>postAbcBooking
add_action('admin_post_postAbcBooking', 'postAbcBooking');

function abc_booking_editBookingRoom() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	$success = false;
	
	if(isset($_POST["id"]) && isset($_POST["room_id"])){
		global $wpdb;
		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_rooms ORDER BY id', ARRAY_A);
		$calendarIds = array();
		foreach($er as $row) {
			$calendarIds[$row["id"]] = $row["calendar_id"];
		}
		$wpdb->update($wpdb->prefix.'abc_bookings', 
			array( 
				'room_id' => sanitize_text_field($_POST["room_id"]),
				'calendar_id' => $calendarIds[intval($_POST["room_id"])]),
			array('id' => intval($_POST["id"])));
		$success = true;
	} 
	
	wp_redirect(admin_url("admin.php?page=advanced_booking_calendar&setting=room-changed"));
	exit;
} //==>editBookingRoom
add_action('admin_post_abc_booking_editBookingRoom', 'abc_booking_editBookingRoom');

function abc_booking_editBookingRoomContent() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	if(isset($_GET["id"])){
		$bookingId = intval($_GET["id"]);
		global $wpdb;
		$dateformat = getAbcSetting("dateformat");
		
		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars ORDER BY id', ARRAY_A);
		foreach( $er as $row ) {
			$calendarNames[$row["id"]] = $row["name"];
		}

		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_rooms ORDER BY id', ARRAY_A);
		foreach( $er as $row ) {
			$roomNames[$row["id"]] = $row["name"];
		}

		$er = $wpdb->get_row( 'SELECT * FROM '.$wpdb->prefix.'abc_bookings WHERE id = '.$bookingId, ARRAY_A );
		$query = 'SELECT r.id, r.calendar_id FROM '.$wpdb->prefix.'abc_rooms r
					LEFT JOIN (
						SELECT DISTINCT room_id FROM '.$wpdb->prefix.'abc_bookings
						WHERE state = \'confirmed\'
						AND ( (start <= \''.$er["start"].'\'
						AND end >=\''.$er["end"].'\')
						OR (start >= \''.$er["start"].'\' AND end <= \''.$er["end"].'\')
						OR (start <= \''.$er["start"].'\' AND end >= \''.$er["end"].'\') ) ) b
					ON r.id = b.room_id
					WHERE b.room_id IS NULL
				ORDER BY r.id';
		$availRooms = $wpdb->get_results( $query, ARRAY_A );
		
		$roomSelect = '<select id="room_id" name="room_id" required="">
				<option value="'.$er["room_id"].'" selected=""><b>'.esc_html($calendarNames[$er["calendar_id"]]).', '.esc_html($roomNames[$er["room_id"]]).'</b></option>';
		foreach( $availRooms as $roomRow ) {
			$roomSelect .= '<option value="'.$roomRow["id"].'">'.esc_html($calendarNames[$roomRow["calendar_id"]]).', '.esc_html($roomNames[$roomRow["id"]]).'</option>';
		}
		$roomSelect .= '</select>';

		$output = '<div class="wrap">
					<h3>'.__('Change room for a booking', 'advanced-booking-calendar').'</h3>
			  		'.abc_booking_getAvailabilityTable($er["start"], $bookingId).'			
					<div class="wrap">';

		$output .= '<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="abc_booking_editBookingRoom" />
						<input type="hidden" name="id" value="'.$bookingId.'" />
						<table class="form-table">
						  <tr>
							<td>'.__('Booking', 'advanced-booking-calendar').'</td>
							<td><b>'.esc_html($er["first_name"]).' '.esc_html($er["last_name"]).', '.sprintf( _n('%d person', '%d persons', esc_html($er["persons"]), 'advanced-booking-calendar'), esc_html($er["persons"]) ).'.</b><br/>
								'.date_i18n($dateformat, strtotime($er["start"])).' - '.date_i18n($dateformat, strtotime($er["end"])).'<br/><br/>
								<a href="mailto:'.esc_html($er["email"]).'">'.esc_html($er["email"]).'</a>, <a href="tel:'.esc_html($er["phone"]).'">'.esc_html($er["phone"]).'</a><br/>
								'.esc_html($er["address"]).', '.esc_html($er["zip"]).' '.esc_html($er["city"]).', '.esc_html($er["county"]).', '.esc_html($er["country"]).'<br/>
								'.__('state', 'advanced-booking-calendar').': "'.$er["state"].'"<br/>
								'.__('message', 'advanced-booking-calendar').': '.esc_textarea($er["message"]).'
							</td>
						  <tr>
						  <tr>
							<td><label for="name">'.__('Available rooms', 'advanced-booking-calendar').'</label></td>
							<td align="left">'.$roomSelect.'
								<p class="description">'.__('Room changes will not affect any price changes!', 'advanced-booking-calendar').'</p>
							</td>
						  </tr>
						  </table>
						  <input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />
						  <a href="admin.php?page=advanced_booking_calendar"><input class="button button-secondary" type="button" value="'._x('Cancel', 'a change', 'advanced-booking-calendar').'" /></a>
						 </form>
						</div>
					</div>	 ';
		return $output;
	}
} //==>editBookingRoomContent

function abc_booking_editBookingContent() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	if(isset($_GET["id"]) && intval($_GET["id"]) > 0){
		global $abcUrl;
		global $wpdb;
		$bookingId = intval($_GET["id"]);
		$dateformat = abc_booking_dateFormatToJS(getAbcSetting("dateformat"));
		wp_dequeue_script('abc-bookings');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('uikit-js', $abcUrl.'backend/js/uikit.min.js', array('jquery'));
		wp_enqueue_script('abc-functions', $abcUrl.'backend/js/abc-functions.js', array('jquery'));
		wp_localize_script( 'abc-functions', 'abc_functions_vars', array( 'dateformat' => $dateformat, 'firstday' => getAbcSetting("firstdayofweek")));
		wp_enqueue_style('abc-datepicker', $abcUrl.'/frontend/css/jquery-ui.min.css');
		wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');
		wp_enqueue_style('abc-adminstyle', $abcUrl.'/backend/css/admin.css');
		wp_enqueue_style( 'font-awesome', $abcUrl.'frontend/css/font-awesome.min.css' );
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
		$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_bookings WHERE id = '.$bookingId, ARRAY_A);
		wp_enqueue_script('abc-booking-edit', $abcUrl.'backend/js/abc-booking-edit.js', array('jquery'));
		wp_localize_script( 'abc-booking-edit', 'ajax_abc_bookings', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'abc_bookings_nonce' => wp_create_nonce('abc-bookings-nonce'),
				'calendar_id' => $er['calendar_id'],
				'booking_id' => $bookingId
				));
		$nameQuery = $wpdb->get_row('SELECT r.name as roomName, c.name as calendarName 
					FROM '.$wpdb->prefix.'abc_rooms r
					INNER JOIN '.$wpdb->prefix.'abc_calendars c
					ON r.calendar_id = c.id
					WHERE r.id = '.$er['room_id'], ARRAY_A);
		$roomName = $nameQuery['roomName'];
		$calendarName = $nameQuery['calendarName'];
		$personList = '<select id="persons" name="persons">';
		for ($i = 1; $i <= get_option('abc_personcount'); $i++) {
			$personList .= '<option value="'.$i.'"';
			if($i == $er['persons']){
				$personList .= ' selected';
			}
			$personList .= '>'.$i.'</option>';
		}
		$personList .= '</select>';

		$numberOfDays = floor((strtotime($er['end']) - strtotime($er['start']))/(60*60*24));
		$extrasOptional = getAbcExtrasList($numberOfDays, $er['persons'], 1);
		$extrasResults = $wpdb->get_results('SELECT extra_id FROM '.$wpdb->prefix.'abc_booking_extras WHERE booking_id = '.$bookingId, ARRAY_A);
		$extrasSelected = array();
		foreach($extrasResults as $extra){
			$extrasSelected[] = $extra['extra_id'];
		}
		$extraOptionalOutput = '';
		foreach($extrasOptional as $extra){
			$checked = '';
			$priceClass = '';
			if(in_array($extra["id"], $extrasSelected)){
				$checked = ' checked';
				$priceClass = ' abc-priceindicator';
			}
			$tempText = '<span class="abc-extra-name abc-pointer">'.$extra["name"].', '.abc_booking_formatPrice($extra["priceValue"]).'</span>';
			if(strlen($extra["explanation"]) > 1){
				$tempText .= '<span class="abc-extra-cost abc-pointer"></br>('.$extra["priceText"].')</span>';
			}
			$extraOptionalOutput .= '<div class="abc-column">
											<div class="abc-option">
												<div class="abc-optional-column-checkbox">
													<input type="checkbox" id="checkbox'.$extra["id"].'" name="abc-extras-checkbox[]" class="abc-extra-checkbox'.$priceClass.'" data-price="'.$extra["priceValue"].'" value="'.$extra["id"].'"'.$checked.'>
												</div>
												<div class="abc-optional-column-text"><label for="checkbox'.$extra["id"].'">'.$tempText.'</label></div>
											</div>
										</div>';
		}
		$extrasMandatory = getAbcExtrasList($numberOfDays, $er['persons'], 2);
		$extraMandatoryOutput = '';
		foreach($extrasMandatory as $extra){
			$tempText = '<span class="abc-extra-name">'.$extra["name"].', '.abc_booking_formatPrice($extra["priceValue"]).'</span>';
			if(strlen($extra["explanation"]) > 1){
				$tempText .= '<span class="abc-extra-cost"></br>('.$extra["priceText"].')</span>';
			}	
			$extraMandatoryOutput .= '<div class="abc-column">
											<div class="abc-option">
												<div class="abc-optional-column-text abc-priceindicator" data-price="'.$extra["priceValue"].'">'.$tempText.'</div>
											</div>
										</div>';
		}
		$price = floatval($er['price']);
		$decimals = 0;
		if(intval($price) != $price){
			$decimals = 2;
		}
		$price = number_format($price, $decimals, ".", "");
		if(getAbcSetting('currencyPosition') == 0 ){
			$price = getAbcSetting('currency').' <input type="number" min="0" id="price" name="price" value="'.$price.'" required>';
		} else {
			$price = '<input type="number" min="0" id="price" name="price" value="'.$price.'" required> '.getAbcSetting('currency');
		}
		$state['open'] = '';
		$state['confirmed'] = '';
		$state['rejected'] = '';
		$state['canceled'] = '';
		$state[$er['state']] = 'selected';
		
		$output = '<div class="wrap">
				<h1>'.__('Edit booking', 'advanced-booking-calendar').'</h1>
				<form class="uk-form uk-form-horizontal" id="abc-booking-form" action="admin-post.php?action=abc_booking_editBooking" method="post">
							<input id="abc-booking-edit-nonce" type="hidden" name="abc-booking-edit-nonce" value="'.wp_create_nonce('abc-booking-edit-nonce').'">
							<input id="abc-booking-id" type="hidden" name="abc-booking-id" value="'.$bookingId.'">
							<input id="abc-calendar-id" type="hidden" name="abc-calendar-id" value="'.$er['calendar_id'].'">
							<div class="uk-form-row">
								<label class="uk-form-label" for="calendar_id">'.__('Calendar', 'advanced-booking-calendar').'</label>
								<div class="uk-form-controls">
									'.$calendarName.', '.$roomName.'<br/>
									<a class="button button-primary" href="admin.php?page=advanced_booking_calendar&action=editBookingRoom&id='.$bookingId.'">'.__('Change room', 'advanced-booking-calendar').'</a>
								</div>	
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="persons">'.__('Persons', 'advanced-booking-calendar').'</label>
								<div class="uk-form-controls">
									'.$personList.'
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="start">'.__('Checkin Date', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									 <div class="uk-form-icon">
		                                <i class="uk-icon-calendar"></i>
										<input type="text" id="start" name="start" value="'.abc_booking_formatDate($er['start']).'" required>
									</div>	
								 </div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="end">'.__('Checkout Date', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									 <div class="uk-form-icon">
		                                <i class="uk-icon-calendar"></i>
										<input type="text" id="end" name="end" value="'.abc_booking_formatDate($er['end']).'" required><br/>
									</div>	
									<span id="abc_dateStatus"></span>
								 </div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('Extras', 'advanced-booking-calendar').'</span>
								<div class="uk-form-controls" id="abc_optionalExtras">
										'.$extraOptionalOutput.'
								</div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('Additional costs', 'advanced-booking-calendar').'</span>
								<div class="uk-form-controls" id="abc_mandatoryExtras">
										'.$extraMandatoryOutput.'
								</div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('Total price', 'advanced-booking-calendar').'</span>
								<div class="uk-form-controls">'.$price.'</b>
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="first_name">'.__('First Name', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="first_name" name="first_name" value="'.$er['first_name'].'" >
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="last_name">'.__('Last Name', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="last_name" name="last_name" value="'.$er['last_name'].'">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="email">'.__('Email Address', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="email" id="email" name="email" value="'.$er['email'].'">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="phone">'.__('Phone Number', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="phone" name="phone" value="'.$er['phone'].'">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="address">'.__('Street Address, House no.', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="address" name="address" value="'.$er['address'].'">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="zip">'.__('ZIP Code', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="zip" name="zip" value="'.$er['zip'].'">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="city">'.__('City', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="city" name="city" value="'.$er['city'].'">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="county">'.__('State / County', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="county" name="county" value="'.$er['county'].'">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="country">'.__('Country', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="country" name="country" value="'.$er['country'].'">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="message">'.__('Message, special note for stay', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<textarea id="message" name="message">'.$er['message'].'</textarea>	
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="state">'.__('State', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<select id="state" name="state">
										<option value="open" '.$state['open'].'>'.__('Open', 'advanced-booking-calendar').'</option>
										<option value="confirmed" '.$state['confirmed'].'>'.__('Confirmed', 'advanced-booking-calendar').'</option>
										<option value="rejected" '.$state['rejected'].'>'.__('Rejected', 'advanced-booking-calendar').'</option>
										<option value="canceled" '.$state['canceled'].'>'.__('Canceled', 'advanced-booking-calendar').'</option>
									</select>
									<p><i>'.__('Note: Changing the booking state here will not sent an email to the guest.', 'advanced-booking-calendar').'</i></p>
								</div>
							</div>
							<div class="uk-form-row">
								<input id="postAbcBooking" class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'"/>
								<a class="button " href="admin.php?page=advanced_booking_calendar">'.__('Cancel', 'advanced-booking-calendar').'</a>
							</div>
						</form>
					</div>	';
		return $output;
	}
} //==>editBookingContent

function abc_booking_customMessageContent() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	if(isset($_GET["id"]) && intval($_GET["id"]) > 0){
		global $abcUrl;
		global $wpdb;
		$bookingId = intval($_GET["id"]);
		$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_bookings WHERE id = '.$bookingId, ARRAY_A);
		wp_dequeue_script('abc-bookings');
		wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');
		wp_enqueue_style('abc-adminstyle', $abcUrl.'/backend/css/admin.css');
		wp_enqueue_style( 'font-awesome', $abcUrl.'frontend/css/font-awesome.min.css' );
		$state['open'] = '';
		$state['confirmed'] = '';
		$state['rejected'] = '';
		$state['canceled'] = '';
		$state[$er['state']] = 'selected';
		$name = '';
		if(strlen($er['first_name']) > 0 && strlen($er['last_name']) > 0){
			$name = ' '.sanitize_text_field($er['first_name']).' '.sanitize_text_field($er['last_name']);
		}elseif(strlen($er['first_name']) > 0){
			$name = ' '.sanitize_text_field($er['first_name']);
		}elseif(strlen($er['last_name']) > 0){
			$name = ' '.sanitize_text_field($er['last_name']);
		}
		$settingsMessage = '';
		if(isset($_GET['availabilityError'])){
			$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
										<p><strong>'.__('Could not confirm booking, room is not available.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
		}
		$output = $settingsMessage.'<div class="wrap">
					<h1>'.__('Custom Message', 'advanced-booking-calendar').'</h1>
					<p>'.__('Send a customized email to your guest. You can also change the status of the booking.', 'advanced-booking-calendar').'</p>
					<form class="uk-form uk-form-horizontal" id="abc-booking-form" action="admin-post.php" method="post">
					<input type="hidden" name="action" value="abc_booking_customMessageSend" />
					<input type="hidden" name="id" value="'.$bookingId.'" />
					<div class="uk-form-row">
						<label class="uk-form-label" for="subject">'.__('Subject', 'advanced-booking-calendar').'</label>
						<div class="uk-form-controls">
							<input class="uk-form-width-large" name="subject" id="subject" value="'.sprintf(__('Your booking at %s, from %s to %s', 'advanced-booking-calendar'), get_option( 'blogname' ), abc_booking_formatDate($er['start']), abc_booking_formatDate($er['end'])).'"/>
						</div>
					</div>
					<div class="uk-form-row">
						<label class="uk-form-label" for="message">'.__('Message', 'advanced-booking-calendar').'</label>
						<div class="uk-form-controls">
							<textarea class="uk-form-width-large" rows="10" name="message" id="message">
'.__('Hello', 'advanced-booking-calendar').$name.',
		
'.__('Best regards,', 'advanced-booking-calendar').'
'.sprintf(__('Your %s-Team', 'advanced-booking-calendar'), get_option( 'blogname' )).'</textarea>
						</div>
					</div>
					<div class="uk-form-row">
						<label class="uk-form-label" for="state">'.__('State', 'advanced-booking-calendar').'</label>
						 <div class="uk-form-controls">
							<select id="state" name="state">
								<option value="open" '.$state['open'].'>'.__('Open', 'advanced-booking-calendar').'</option>
								<option value="confirmed" '.$state['confirmed'].'>'.__('Confirmed', 'advanced-booking-calendar').'</option>
								<option value="rejected" '.$state['rejected'].'>'.__('Rejected', 'advanced-booking-calendar').'</option>
								<option value="canceled" '.$state['canceled'].'>'.__('Canceled', 'advanced-booking-calendar').'</option>
							</select>
						</div>
					</div>
					<div class="uk-form-row">
						<input id="sendCustomMessage" class="button button-primary" type="submit" value="'.__('Send custom message', 'advanced-booking-calendar').'"/>
						<a class="button " href="admin.php?page=advanced_booking_calendar">'.__('Cancel', 'advanced-booking-calendar').'</a>
					</div>
					</form>
				   </div>';
		return $output;
	}
} //==>customMessageContent

function abc_booking_customMessageSend() {
	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	if (isset($_POST["id"]) && isset($_POST["subject"])
			&& isset($_POST["message"]) && isset($_POST["state"])
			&& ($_POST["state"] == 'confirmed' || $_POST["state"] == 'open'
					|| $_POST["state"] == 'canceled' || $_POST["state"] == 'rejected' )) {
						global $wpdb;
						$bookingId = intval($_POST["id"]);$adminEmail = getAbcSetting('email');
						if(!filter_var($adminEmail,FILTER_VALIDATE_EMAIL)){
							$adminEmail = get_option('admin_email');
						}
						$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_bookings WHERE id = '.$bookingId, ARRAY_A);
						if($_POST["state"] == 'confirmed' && $er['state'] != 'confirmed'
								&& !getAbcAvailability($er["calendar_id"], $er["start"], $er["end"], 1, $bookingId)){
									wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&action=customMessage&id=".$bookingId."&availabilityError" ) );
						}else {
							if($_POST["state"] != $er["state"]){
								$wpdb->update($wpdb->prefix.'abc_bookings', array('state' => sanitize_text_field($_POST["state"])), array('id' => $bookingId)); // Setting booking-state to confirmed
							}
							if (filter_var($er["email"], FILTER_VALIDATE_EMAIL)) {
								$headers[] = 'MIME-Version: 1.0' . "\r\n";
								$headers[] = 'From: '.htmlspecialchars_decode(get_option('blogname')).' <'.$adminEmail.'>'."\r\n";
								wp_mail($er["email"], stripslashes($_POST["subject"]), stripslashes($_POST["message"]), $headers);
								wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&setting=customMessageSend" ) );
							}else{
								wp_redirect(  admin_url( "admin.php?page=advanced_booking_calendar&setting=customMessageError" ) );
							}
						}
					}else{
						wp_die("Something went wrong.");
					}
					exit;
} //==>customMessageSend()
add_action( 'admin_post_abc_booking_customMessageSend', 'abc_booking_customMessageSend' );

function abc_booking_editBooking() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	if(!isset( $_POST['abc-booking-edit-nonce'] ) || !wp_verify_nonce($_POST['abc-booking-edit-nonce'], 'abc-booking-edit-nonce') ){
		die('Permissions check failed!');
	}
	if(isset($_POST["abc-booking-id"]) && isset($_POST["abc-calendar-id"]) 
		&& isset($_POST['persons']) && intval($_POST['persons']) > 0
		&& isset($_POST['start']) && isset($_POST['end']) 
		&& isset($_POST['price']) && isset($_POST['first_name']) && isset($_POST['last_name']) 
		&& isset($_POST['email']) && isset($_POST['phone'])  && isset($_POST['state'])
		&& isset($_POST['address']) && isset($_POST['zip']) && isset($_POST['city'])
		&& isset($_POST['county']) && isset($_POST['country']) && isset($_POST['message'])){
			$bookingId = intval($_POST["abc-booking-id"]);
			$state = 'open';
			global $wpdb;
			if($_POST['state'] == 'rejected' || $_POST['state'] == 'canceled'){
				$state = $_POST['state'];
			}elseif($_POST['state'] == 'confirmed'){
				$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'abc_bookings WHERE id = '.$bookingId, ARRAY_A);
				if(getAbcAvailability($er["calendar_id"], $_POST["start"], $_POST["end"], 0, $bookingId)){
					$state = 'confirmed';
				}
			}
			
			$changes = array(
							'persons'	=> intval($_POST["persons"]),
							'price'		=> floatval($_POST['price']),
							'email'		=> '',
							'first_name'=> sanitize_text_field($_POST['first_name']),
							'last_name'	=> sanitize_text_field($_POST['last_name']),
							'phone'		=> sanitize_text_field($_POST['phone']),
							'address'	=> sanitize_text_field($_POST['address']),
							'zip'		=> sanitize_text_field($_POST['zip']),
							'city'		=> sanitize_text_field($_POST['city']),
							'county'	=> sanitize_text_field($_POST['county']),
							'country'	=> sanitize_text_field($_POST['country']),
							'message'	=> sanitize_text_field($_POST['message']),
							'state'		=> $state
						);
			if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
				$changes['email'] = sanitize_text_field($_POST['email']);
			}
			$bookingId = intval($_POST["abc-booking-id"]);
			$normFromValue = abc_booking_formatDateToDB($_POST['start']);
			$normToValue = abc_booking_formatDateToDB($_POST['end']);
			$numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
			$dateChanged = '-error';
			if(getAbcAvailability(intval($_POST["abc-calendar-id"]), $normFromValue, $normToValue, 1, $bookingId)){ // Checking if calendar is available for the time frame
				$changes['start'] = $normFromValue;
				$changes['end'] = $normToValue;
				$dateChanged = '';
			}
			global $wpdb;
			$wpdb->update(
					$wpdb->prefix.'abc_bookings',
					$changes,
					array('id' => intval($_POST["abc-booking-id"])));
			$wpdb->delete( 
					$wpdb->prefix.'abc_booking_extras', 
					array( 'booking_id' => $bookingId ) );
			$extrasForBooking = array();
			$extrasMandatory = getAbcExtrasList($numberOfDays, intval($_POST["persons"]), 2);
			foreach ($extrasMandatory as $extra){
				$extrasForBooking[] = $extra['id']; 
			}
			if(!empty($_POST['abc-extras-checkbox'])){
				foreach($_POST['abc-extras-checkbox'] as $extra){
					$extrasForBooking[] = $extra;
				}
			}
			foreach ($extrasForBooking as $extra){
				$wpdb->insert(
						$wpdb->prefix.'abc_booking_extras',
						array(
								'booking_id' => $bookingId,
								'extra_id' => $extra
						)
					);	
			}
		wp_redirect(admin_url("admin.php?page=advanced_booking_calendar&setting=booking-changed".$dateChanged));
	}else{
		wp_redirect(admin_url("admin.php?page=advanced_booking_calendar&setting=error"));
	}
	exit;
} //==>editBookingRoom
add_action('admin_post_abc_booking_editBooking', 'abc_booking_editBooking');

function ajax_setAbcNewsletter(){
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}	
	
	if(isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){		
		subscribeAbcNewsletter($_POST["email"]);
		update_option ('abc_newsletter', 1);
	}
	die();	
} //==>setAbcNewsletter()
add_action('wp_ajax_abc_setAbcNewsletter', 'ajax_setAbcNewsletter');

// Generating an availability table
function abc_booking_getAvailabilityTable($initialDate, $bookingId = 0) {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	global $abcUrl;
	global $wpdb;
	$startDate = strtotime($initialDate);
	$dateformat = getAbcSetting("dateformat");
	if (date_i18n('w', $startDate) < 6) { // Availability always starts on the last saturday
		$dayDiff = date_i18n('w', $startDate)+1; 
		$startDate = strtotime('-'.$dayDiff.' days', $startDate);
	}
	$tempDate = $startDate;
	$tableHead = '';
	for($i=0; $i<=28; $i++){ // Creating dates for table head
		$tableHead .= '<th colspan="2" class="abcCellBorderBottom abcCellBorderLeft abcDayNumber';
		if($i%7 <= 1){
			$tableHead .= ' abcDayWeekend';
		}
		$tableHead .= '">
					<span class="abcMonthName">'.date_i18n('D', $tempDate).'</span><br/>
					'.date_i18n('j', $tempDate).'<br/>
					<span class="abcMonthName">'.date_i18n('M', $tempDate).'</span></th>';
		$tempDate = strtotime('+1 day', $tempDate);
	}
	$endDate = strtotime('-1 day', $tempDate);
	$initialYear = date_i18n("Y", strtotime($initialDate));
	$output = '<div class="uk-overflow-container abcAvailabilityTable" id="abc_AvailabilityTable">
				<table class="abcAvailabilityTable">
				<thead>
					<tr>
						<th>
							<div class="uk-button-group">
								<button data-startdate="'.date("Y-m-d", $startDate).'" data-uk-tooltip="{animation:\'true\'}" class="uk-button abc-availability-table-button-left"><i class="uk-icon-chevron-left"></i></button>
								<button data-startdate="'.date("Y-m-d", $startDate).'" data-uk-tooltip="{animation:\'true\'}" class="uk-button abc-availability-table-button-right"><i class="uk-icon-chevron-right"></i></button>
							</div>
							<div class="abcDateSelector">
								<div class="uk-button-dropdown" data-uk-dropdown>
									<button class="uk-button abcMonthWrapper">'.date_i18n("M", strtotime($initialDate)).'<i class="uk-icon-caret-down"></i></button>
									<div class="uk-dropdown uk-dropdown-small">
										<ul class="uk-nav uk-nav-dropdown">
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-01-01" href="">'.__('January', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-02-01" href="">'.__('February', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-03-01" href="">'.__('March', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-04-01" href="">'.__('April', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-05-01" href="">'.__('May', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-06-01" href="">'.__('June', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-07-01" href="">'.__('July', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-08-01" href="">'.__('August', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-09-01" href="">'.__('September', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-10-01" href="">'.__('October', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-11-01" href="">'.__('November', 'advanced-booking-calendar').'</a></li>
											<li><a class="abcMonthSelector" data-startdate="'.$initialYear.'-12-01" href="">'.__('December', 'advanced-booking-calendar').'</a></li>
										</ul>
									</div>
								</div>
								<div class="uk-button-dropdown" data-uk-dropdown>
									<button class="uk-button abcYearWrapper">'.date_i18n("Y", strtotime($initialDate)).'<i class="uk-icon-caret-down"></i></button>
									<div class="uk-dropdown uk-dropdown-small">
										<ul class="uk-nav uk-nav-dropdown"> ';
	for ($i = -2; $i <3; $i++){
		$currYear = date_i18n("Y", strtotime($initialDate))+$i;
		$currMonth = date_i18n("m", strtotime($initialDate));
		$output .= '<li><a class="abcYearSelector" data-startdate="'.$currYear.'-'.$currMonth.'-01" href="">'.$currYear.'</a></li>';
	} 
	$output .= '						</ul>
									</div>
								</div>
							</div>	
						</th>';
	$output .= $tableHead.'		</tr>
				</thead>
				<tbody>';
	$bookings = array();
	$bookingQuery = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_bookings 
					WHERE end >= "'.date_i18n("Y-m-d", $startDate).'" 
					AND start <= "'.date_i18n("Y-m-d", strtotime('+29 days', $startDate)).'" 
					AND state = "confirmed" 
					ORDER BY end', ARRAY_A); 
	foreach($bookingQuery as $bookingRow){
		$bookings[$bookingRow["calendar_id"]][$bookingRow["room_id"]][] = $bookingRow;  // Getting all confirmed bookings for the current timeframe
	}
	$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars ORDER BY name', ARRAY_A);
	$currCalendar = ''; // Necessary for drawing a bold line at the first room
	foreach($er as $row) { // Creating rows for table
		$calendar = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_rooms WHERE calendar_id='.$row["id"].' ORDER BY name', ARRAY_A);
		if($wpdb->num_rows > 1){
			$output .= '<tr>
						<td class="abcCalendarName">'.esc_html($row["name"]).'</td>
						</tr>';
		}	
		foreach($calendar as $rooms) { // Getting room names and bookings
			if($wpdb->num_rows > 1){
				$output .= '<tr>
						<td class="abcRoomName">'.esc_html($rooms["name"]).'</td>';
			} else {
				$output .= '<tr>
						<td class="abcCalendarName">'.esc_html($row["name"]).'</td>';
			}	
			$roomRowDate = $startDate;
			for($i = 0; $i <= 57; $i++){
				$colSpan = 1;
				if (isset($bookings[$row["id"]][$rooms["id"]])){ // Checking for bookings for the current room
					$success = false;
					for($j = 0; $j < count($bookings[$row["id"]][$rooms["id"]]); $j++){
						// Checking if a booking started before startDate 
						if($bookings[$row["id"]][$rooms["id"]][$j]["start"] < date_i18n("Y-m-d", $startDate) && $i==0){
							$tempEndDate = strtotime($bookings[$row["id"]][$rooms["id"]][$j]["end"]);
							$dayOffset = 0;
							$cssClass = ' abcAvailabilityTableStarting';
							if ($tempEndDate > $endDate){
								$tempEndDate = strtotime('-1 day', $endDate);
								$dayOffset = 1;
								$success = true;
								$cssClass .= ' abcAvailabilityTableStartingEnding';
							}
							$dateDiff = abc_booking_dateDiffInDays($tempEndDate, $startDate);
							$colSpan = ($dateDiff*2)+1;
							$lineCount = 0;
							$title = date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["start"])).' 
										- '.date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["end"]));
							if(strlen($bookings[$row["id"]][$rooms["id"]][$j]["last_name"]) > 0){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["last_name"];
								$lineCount ++;
							}
							if(strlen($bookings[$row["id"]][$rooms["id"]][$j]["first_name"]) > 5){
								if($lineCount == 0){
									$title .= '<br/>';
								}else{
									$title .= ', ';
								}
								$title .= $bookings[$row["id"]][$rooms["id"]][$j]["first_name"];
								$lineCount ++;
							}
						
							// Creating Title- and Inline-Text 
							$lineCount = 0;
							$text = '';
							$title = date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["start"])).' 
										- '.date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["end"]));
							if(strlen($bookings[$row["id"]][$rooms["id"]][$j]["last_name"]) > 0){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["last_name"];
								$text .= $bookings[$row["id"]][$rooms["id"]][$j]["last_name"].',';
								$lineCount ++;
							}
							if(!empty($bookings[$row["id"]][$rooms["id"]][$j]["first_name"])){
								if($lineCount == 0){
									$title .= '<br/>';
								}else{
									$title .= ', ';
								}
								$title .= $bookings[$row["id"]][$rooms["id"]][$j]["first_name"];
								$lineCount ++;
							}
							$title .= '<br/>'.sprintf( _n('%d person', '%d persons', $bookings[$row["id"]][$rooms["id"]][$j]["persons"], 'advanced-booking-calendar'), $bookings[$row["id"]][$rooms["id"]][$j]["persons"] ).' 
										<br/>'.abc_booking_formatPrice($bookings[$row["id"]][$rooms["id"]][$j]["price"]);
							$text .= sprintf( _n('%d person', '%d persons', $bookings[$row["id"]][$rooms["id"]][$j]["persons"], 'advanced-booking-calendar'), $bookings[$row["id"]][$rooms["id"]][$j]["persons"] ).',
										'.abc_booking_formatPrice($bookings[$row["id"]][$rooms["id"]][$j]["price"]).', '.date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["start"])).'
									- '.date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["end"]));
							if(!empty($bookings[$row["id"]][$rooms["id"]][$j]["country"])){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["country"];
								$text .= ', '.$bookings[$row["id"]][$rooms["id"]][$j]["country"];
							}
							if(!empty($bookings[$row["id"]][$rooms["id"]][$j]["phone"])){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["phone"];
							}
							if(!empty($bookings[$row["id"]][$rooms["id"]][$j]["email"])){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["email"];
							}
							
							if(mb_strlen($text, "utf-8") > $colSpan*1.5){
								$text = mb_substr($text, 0, $colSpan*1.5, "utf-8").'...';
							}
							$output .= '<td data-uk-tooltip="{animation:\'true\'}" title="'.$title.'"  class="abcAvailabilityTableFont abcAvailabilityTableColor';
							$output .= $bookings[$row["id"]][$rooms["id"]][$j]["id"]%12;
							$output .= $cssClass.'" colspan="'.$colSpan.'">'.$text.'</td>';
							$i += ((abc_booking_dateDiffInDays($tempEndDate, $startDate))*2)+1+$dayOffset;
							$roomRowDate = strtotime('+'.$dateDiff.' day', $roomRowDate);
						
						}elseif($bookings[$row["id"]][$rooms["id"]][$j]["start"] == date_i18n("Y-m-d", $roomRowDate) && $i%2==1) {
							$tempEndDate = strtotime($bookings[$row["id"]][$rooms["id"]][$j]["end"]);
							$dayOffset = 0;
							$cssClass = '';
							if ($tempEndDate > $endDate){
								$tempEndDate = strtotime('-1 day', $endDate);
								$dayOffset = 3;
								$success = true;
								$cssClass .= ' abcAvailabilityTableEnding';
							}
							if ($bookingId == $bookings[$row["id"]][$rooms["id"]][$j]["id"]){
								$cssClass .= ' abcBookingHighlighted';
							}
							// Creating Title- and Inline-Text 
							$lineCount = 0;
							$text = '';
							$title = date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["start"])).' 
										- '.date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["end"]));
							if(strlen($bookings[$row["id"]][$rooms["id"]][$j]["last_name"]) > 0){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["last_name"];
								$text .= $bookings[$row["id"]][$rooms["id"]][$j]["last_name"].',';
								$lineCount ++;
							}
							if(!empty($bookings[$row["id"]][$rooms["id"]][$j]["first_name"])){
								if($lineCount == 0){
									$title .= '<br/>';
								}else{
									$title .= ', ';
								}
								$title .= $bookings[$row["id"]][$rooms["id"]][$j]["first_name"];
								$lineCount ++;
							}
							$title .= '<br/>'.sprintf( _n('%d person', '%d persons', $bookings[$row["id"]][$rooms["id"]][$j]["persons"], 'advanced-booking-calendar'), $bookings[$row["id"]][$rooms["id"]][$j]["persons"] ).' 
										<br/>'.abc_booking_formatPrice($bookings[$row["id"]][$rooms["id"]][$j]["price"]);
							$text .= sprintf( _n('%d person', '%d persons', $bookings[$row["id"]][$rooms["id"]][$j]["persons"], 'advanced-booking-calendar'), $bookings[$row["id"]][$rooms["id"]][$j]["persons"] ).',
										'.abc_booking_formatPrice($bookings[$row["id"]][$rooms["id"]][$j]["price"]).', '.date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["start"])).'
									- '.date_i18n($dateformat, strtotime($bookings[$row["id"]][$rooms["id"]][$j]["end"]));
							if(!empty($bookings[$row["id"]][$rooms["id"]][$j]["country"])){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["country"];
								$text .= ', '.$bookings[$row["id"]][$rooms["id"]][$j]["country"];
							}
							if(!empty($bookings[$row["id"]][$rooms["id"]][$j]["phone"])){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["phone"];
							}
							if(!empty($bookings[$row["id"]][$rooms["id"]][$j]["email"])){
								$title .= '<br/>'.$bookings[$row["id"]][$rooms["id"]][$j]["email"];
							}
							
							$dateDiff = abs(abc_booking_dateDiffInDays(strtotime($bookings[$row["id"]][$rooms["id"]][$j]["start"]), $tempEndDate));
							$colSpan = ($dateDiff*2)+$dayOffset;
							
							if(mb_strlen($text, "utf-8") > $colSpan*1.5){
								$text = mb_substr($text, 0, $colSpan*1.5, "utf-8").'...';
							}
							$output .= '<td data-uk-tooltip="{animation:\'true\'}" title="'.$title.'" class="abcAvailabilityTableFont abcAvailabilityTableColor';
							$output .= $bookings[$row["id"]][$rooms["id"]][$j]["id"]%12;
							$output .= $cssClass.'" colspan="'.$colSpan.'">'.$text.'</td>';
							$i += ($dateDiff*2)+$dayOffset;
							$roomRowDate = strtotime('+'.$dateDiff.' day', $roomRowDate);
						}
					}
					if(!$success){
						$output .= '<td class="abcCellBorderBottom';
						if($i%2 ==0){
							$output .= ' abcCellBorderLeft';
						}
						if($currCalendar != $row["id"]){
							$output .= ' abcCellBorderTop';
						}
						$output .= '">&nbsp;</td>';
					}
				} else{
					$output .= '<td class="abcCellBorderBottom';
					if($i%2 ==0){
						$output .= ' abcCellBorderLeft';
					}
					if($currCalendar != $row["id"]){
						$output .= ' abcCellBorderTop';
					}
					$output .= '">&nbsp;</td>';
				}
				if($i%2==1 || $i == 1){
					$roomRowDate = strtotime('+1 day', $roomRowDate);
				}	
			}			
			$output .= '</tr>';
			$currCalendar = $row["id"]; 
		}
	}
	$output .='
				</table>
				</div>';
	return $output;
}

function ajax_abc_booking_getAvailabilityTable() {
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}
	if(!isset($_POST['startdate'])){
		echo 'Date not set.';
	} else {	
		echo abc_booking_getAvailabilityTable(sanitize_text_field($_POST['startdate']));
	}
	die();
}
add_action('wp_ajax_abc_booking_getAvailabilityTable', 'ajax_abc_booking_getAvailabilityTable');

function ajax_abc_booking_sendFeedbackModal() {
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}
	if(!isset($_POST['feedbackMessage'])){
		echo 'Message not set.';
	} else {
		$text = "Message: \n".sanitize_text_field($_POST['feedbackMessage']);
		$headers = 'From: '.get_option('blogname').' <'.getAbcSetting('email').'>'."\r\n";
		wp_mail("info@booking-calendar-plugin.com", "Feedback of ".get_option('blogname'), $text, $headers);
		echo "<p>".__('Thank you for your Feedback!', 'advanced-booking-calendar')."</p>";
	}
	die();
}
add_action('wp_ajax_abc_booking_sendFeedbackModal', 'ajax_abc_booking_sendFeedbackModal');

function ajax_abc_booking_activatePoweredby() {
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}
	if(!isset($_POST['poweredby']) && $_POST['poweredby'] == 1){
		echo 'Value missing.';
	} else {
		update_option('abc_poweredby', 1);
		echo "<p>".__('Thank you! The link is now activated.', 'abc-booking')."</p>";
	}
	die();
}
add_action('wp_ajax_abc_booking_activatePoweredby', 'ajax_abc_booking_activatePoweredby');

function ajax_abc_booking_activateCommitUsage() {
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}
	if(isset($_POST['commitusage']) && $_POST['commitusage'] == 1){
		activate_commitUsage();
		update_option ('abc_usage', 1);
		echo "<h1>".__('Thank you!', 'abc-booking')."</h1>";
	} else {
		echo 'Value missing.';
	}
	die();
}
add_action('wp_ajax_abc_booking_activateCommitUsage', 'ajax_abc_booking_activateCommitUsage');

function ajax_abc_booking_getSearchResults() {
	if(!isset( $_POST['abc_bookings_nonce'] ) || !wp_verify_nonce($_POST['abc_bookings_nonce'], 'abc-bookings-nonce') ){
		die('Permissions check failed!');
	}
	if(!isset($_POST['search'])){
		echo _e('Search not set.', 'advanced-booking-calendar');
	} else {
		global $wpdb;
		$search = sanitize_text_field($_POST["search"]);
		// Getting calendar and room names
		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_calendars ORDER BY id', ARRAY_A);
		foreach($er as $row) {
			$calendarNames[$row["id"]] = $row["name"];
		}
		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'abc_rooms ORDER BY id', ARRAY_A);
		foreach($er as $row) {
			$roomNames[$row["id"]] = $row["name"];
		}
		//Getting booking items
		$query = "SELECT * FROM ".$wpdb->prefix."abc_bookings WHERE 1 = 1 ";

		if( is_numeric($search) ) {
			$where = "AND id = ".$search;
		} else {
			$where = 'AND last_name like \'%'.$search.'%\' OR first_name  like \'%'.$search.'%\'
					OR email like \'%'.$search.'%\' OR message like \'%'.$search.'%\'';
		}

		$query .= $where . " ORDER BY end DESC LIMIT 0, 50";

		$er = $wpdb->get_results($query, ARRAY_A);
		$foreachcount = 1;
		$dateformat = getAbcSetting('dateformat');
		$accordion = '<div >';	
		foreach($er as $row) {
			$accordion .= '<div class="uk-width-1-1 uk-panel-box">
						<h3>#'.$row["id"].' '.$row["last_name"].', '.date_i18n($dateformat, strtotime($row["start"])).' - '.date_i18n($dateformat, strtotime($row["end"])).', '.$calendarNames[$row["calendar_id"]].', '.$roomNames[$row["room_id"]].'</h3>
							<h4>'.$row["state"].'</h4
							<p>'.esc_html($row["first_name"]).' '.esc_html($row["last_name"]).'<br/>
							<a href="mailto:'.esc_html($row["email"]).'?subject='.__('Booking Request', 'advanced-booking-calendar').' '.get_option('blogname').'">'.esc_html($row["email"]).'</a><br/>
							<a href="tel:'.esc_html($row["phone"]).'">'.esc_html($row["phone"]).'</a><br/>
							'.esc_html($row["address"]).', '.esc_html($row["zip"]).' '.esc_html($row["city"]).', '.esc_html($row["county"]).', '.esc_html($row["country"]).'<br/>
							'.__('Total price', 'advanced-booking-calendar').': '.abc_booking_formatPrice($row["price"]).'<br/>
							'.__('Message', 'advanced-booking-calendar').': '.esc_textarea($row["message"]).'</p>';
		$buttons = '';
		switch ($row["state"]) {
			case 'open':
				$buttons .= '<form style="display: inline;" action="admin-post.php?action=abc_booking_confBooking&id='.$row["id"].'" method="post">
								<input class="button button-primary" type="submit" value="'.__('Confirm', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to confirm this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  <form style="display: inline;" action="admin-post.php?action=abc_booking_rejBooking&id='.$row["id"].'" method="post">
								<input class="button button-primary" type="submit" value="'.__('Reject', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to reject this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  ';
				break;
			case 'confirmed':
				$buttons .= '<form style="display: inline;" action="admin.php" method="get">
								<input type="hidden" name="page" value="advanced_booking_calendar" />
								<input type="hidden" name="action" value="editBookingRoom" />
								<input type="hidden" name="id" value="'.$row["id"].'" />
								<input class="button button-primary" type="submit" value="'.__('Change room', 'advanced-booking-calendar').'" />
							</form>
							<form style="display: inline;" action="admin-post.php?action=abc_booking_cancBooking&id='.$row["id"].'" method="post">
								<input class="button button-primary" type="submit" value="'._x('Cancel', 'a booking', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to cancel this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  ';
				break;
			case 'canceled':
				$buttons .= '<form style="display: inline;" action="admin-post.php?action=abc_booking_confBooking&id='.$row["id"].'" method="post">
								<input class="button button-primary" type="submit" value="'.__('Confirm', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to confirm this booking (it has already been canceled)?', 'advanced-booking-calendar').'\')" />
							  </form>
							  <form style="display: inline;" action="admin-post.php?action=abc_booking_delBooking&id='.$row["id"].'" method="post">
								<input class="button button-primary" type="submit" value="'.__('Delete', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  ';
				break;
			case 'rejected':
				$buttons .= '<form style="display: inline;" action="admin-post.php?action=abc_booking_confBooking&id='.$row["id"].'" method="post">
								<input class="button button-primary" type="submit" value="'.__('Confirm', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to confirm this booking (it has already been rejected)?', 'advanced-booking-calendar').'\')" />
							  </form>
							  <form style="display: inline;" action="admin-post.php?action=abc_booking_delBooking&id='.$row["id"].'" method="post">
								<input class="button button-primary" type="submit" value="'.__('Delete', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this booking?', 'advanced-booking-calendar').'\')" />
							  </form>
							  ';
				break;
		}	
			
		$accordion .= '<div class="uk-clearfix">'.$buttons.'
						<form style="display: inline;" action="admin.php" method="get">
								<input type="hidden" name="page" value="advanced_booking_calendar" />
								<input type="hidden" name="action" value="editBooking" />
								<input type="hidden" name="id" value="'.$row["id"].'" />
								<input class="button" type="submit" value="'.__('Edit', 'advanced-booking-calendar').'" />
					 	</form>

						<div class="uk-float-right"><a href="#top" class="uk-button uk-button-mini" data-uk-smooth-scroll>'.__('Scroll up', 'advanced-booking-calendar').'</a></div>
						</div></div>';
		$accordion .= '<hr class="uk-grid-divider">';
		$foreachcount++;
	}
	$accordion .= '</div>';
		echo $accordion;
	}
	die();
}
add_action('wp_ajax_abc_booking_getSearchResults', 'ajax_abc_booking_getSearchResults');

// Output to front end
function advanced_booking_calendar_show_bookings() {
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	global $abcUrl;
	global $wpdb;
	$dateformat = abc_booking_dateFormatToJS(getAbcSetting("dateformat"));
	$nlAsked = false;
	$nlText = '';
	$nlEmail = '';
	if(getAbcSetting("newsletter") == 0){
		$er = $wpdb->get_row('SELECT COUNT(id) as bookings, SUM(price) as revenue FROM '.$wpdb->prefix.'abc_bookings WHERE state = \'confirmed\'', ARRAY_A);
		if ($er["bookings"] > 10 && getAbcSetting("newsletter_10th_asked") == 0) {
			$nlAsked = true;
			$nlEmail = getAbcSetting("email");
			update_option ('abc_newsletter_10th_asked', 1);
			$nlText = '<h2>'.__('Congratulations on your 10th confirmed Booking!', 'advanced-booking-calendar').'</h2>
			'.__('We would like to give your more tips on how to raise your occupation rate via our Newsletter. We promise to never spam you. You can unsubscribe anytime.', 'advanced-booking-calendar').'<br/>
					<br/><b>'.__('Click OK to subscribe now!', 'advanced-booking-calendar').'<b>';
		} elseif($er["bookings"] > 100 && getAbcSetting("newsletter_100th_asked") == 0){
			$nlAsked = true;
			$nlEmail = getAbcSetting("email");
			update_option ('abc_newsletter_100th_asked', 1);
			$nlText = '<h2>'.__('Wow! Congratulations on your 100th confirmed Booking!', 'advanced-booking-calendar').'</h2>
			'.__('You are obviously doing a great job! But still, maybe you could learn something by our tips on how to raise your occupation rate via our Newsletter. We promise to never spam you. You can unsubscribe anytime.', 'advanced-booking-calendar').'<br/>
					<br/><b>'.__('Click OK to subscribe now!', 'advanced-booking-calendar').'<b>';
		} elseif($er["revenue"] > 20000 && getAbcSetting("newsletter_20000revenue_asked") == 0){
			$nlAsked = true;
			$nlEmail = getAbcSetting("email");
			update_option ('abc_newsletter_20000revenue_asked', 1);
			$nlText = '<h2>'.sprintf( __('Congratulations! You just surpassed a total revenue of 20000 %s!', 'advanced-booking-calendar'), getAbcSetting('currency')).'</h2>
			'.__('You are obviously doing a great job! But still, maybe you could learn something by our tips on how to raise your occupation rate via our Newsletter. We promise to never spam you. You can unsubscribe anytime.', 'advanced-booking-calendar').'<br/>
					<br/><b>'.__('Click OK to subscribe now!', 'advanced-booking-calendar').'<b>';
		}
	}
	$feedbackModal = '';
	if(!$nlAsked && getAbcSetting('feedbackModal01') == 0  && getAbcSetting('installdate') <= date("Y-m-d", strtotime('-1 week'))){
		update_option('abc_feedbackModal01', 1);
		$feedbackModal = '
		<div id="feedbackModal" class="uk-modal">
	        <div class="uk-modal-dialog">
	            <a href="" class="uk-modal-close uk-close"></a>
	            <h1 class="feedbackQuestion">'.__('Do you like this Plugin?', 'advanced-booking-calendar').'</h1>
	            <p id="feedbackSmileys" class="feedbackQuestion">
	            	<span id="feedbackLike"><i class="fa fa-smile-o"></i></span>
	            	<span id="feedbackDislike"><i class="fa fa-frown-o"></i></span>
	            </p>
	        	<div id="dislikeForm" style="display:none">
	            	<h1>'.__("Oh, we are sorry!", "advanced-booking-calendar").'</h1>
	            	<p>'.__("Would you tell us what you don't like?", "advanced-booking-calendar").'</p>
	            	<div class="uk-form-controls" style="text-align:center;">
						<textarea id="feedbackMessage" name="feedbackMessage" style="width:100%; margin-bottom:20px;"></textarea>	
						<input id="sendFeedback" class="button button-primary" value="'.__("Send Feedback", "advanced-booking-calendar").'" type="submit">
					</div>
	        	</div>
	        	<div id="likeForm" style="display:none">
	            	<h1>'.__("Great!", "advanced-booking-calendar").'</h1>
	            	<p>'.__("We would really appreciate a review:", "advanced-booking-calendar").' <br/>
	            		<a href="https://wordpress.org/support/view/plugin-reviews/advanced-booking-calendar" target="_blank">
	            		https://wordpress.org/support/view/plugin-reviews/advanced-booking-calendar</a>
	            	</p>
	            	<p>'.__('You can also help us by activating a tiny "Powered by Advanced Booking Calendar"-notice below the calendar overview and booking form. It would help us a lot!', 'advanced-booking-calendar').'</p>
	            	<div class="uk-form-controls" style="text-align:center;">	
						<input id="activatePoweredby" class="button button-primary" value="'.__("Activate Powered-By-Link", "advanced-booking-calendar").'" type="submit">
					</div>
	        	</div>
	            
	        </div>
		 </div>';
	} 
	if(!$nlAsked && getAbcSetting('feedbackModal01') == 1 && get_option('abc_usage') == 0 && 
		(
			(getAbcSetting('usageModal01') == 0  && getAbcSetting('installdate') <= date("Y-m-d", strtotime('-2 weeks'))) ||
			(getAbcSetting('usageModal02') == 0  && getAbcSetting('installdate') <= date("Y-m-d", strtotime('-4 weeks')))
		)
		){
		if(getAbcSetting('usageModal01') == 1){
			update_option('abc_usageModal02', 1);
		}
		update_option('abc_usageModal01', 1);
		$feedbackModal = '
		<div id="feedbackModal" class="uk-modal">
	        <div class="uk-modal-dialog">
	            <a href="" class="uk-modal-close uk-close"></a>
	            <div id="usageModalContent">
		            <h1 class="feedbackQuestion">'.__('Please help us!', 'advanced-booking-calendar').'<br/>
		            	'.__('Lets make this plugin better!', 'advanced-booking-calendar').'</h1>
		            <p id="usageQuestion">'.__('Help us to make this plugin better by committing some usage data.', 'advanced-booking-calendar').' '.__('Your visitors will not be affected. No sensible data will be transmitted.', 'advanced-booking-calendar').'</p>
		            <div class="uk-form-controls" style="text-align:center;">	
							<input id="activateCommitUsage" class="button button-primary" value="'.__("Commit Usage", "advanced-booking-calendar").'" type="submit">
					</div>
		        	<div id="dislikeForm" style="display:none">
		            	<h1>'.__("Oh, we are sorry!", "advanced-booking-calendar").'</h1>
		            	<p>'.__("Would you tell us what you don't like?", "advanced-booking-calendar").'</p>
		            	<div class="uk-form-controls" style="text-align:center;">
							<textarea id="feedbackMessage" name="feedbackMessage" style="width:100%; margin-bottom:20px;"></textarea>	
							<input id="sendFeedback" class="button button-primary" value="'.__("Send Feedback", "advanced-booking-calendar").'" type="submit">
						</div>
		        	</div>
		        	<div id="likeForm" style="display:none">
		            	<h1>'.__("Great!", "advanced-booking-calendar").'</h1>
		            	<p>'.__("We would really appreciate a review:", "advanced-booking-calendar").' <br/>
		            		<a href="https://wordpress.org/support/view/plugin-reviews/advanced-booking-calendar" target="_blank">
		            		https://wordpress.org/support/view/plugin-reviews/advanced-booking-calendar</a>
		            	</p>
		            	<p>'.__('You can also help us by activating a tiny "Powered by Advanced Booking Calendar"-notice below the calendar overview and booking form. It would help us a lot!', 'advanced-booking-calendar').'</p>
		            	<div class="uk-form-controls" style="text-align:center;">	
							<input id="activatePoweredby" class="button button-primary" value="'.__("Activate Powered-By-Link", "advanced-booking-calendar").'" type="submit">
						</div>
		        	</div>
		        </div>	
	            
	        </div>
		 </div>';
	} 
	wp_enqueue_script('abc-bookings', $abcUrl.'backend/js/abc-bookings.js', array('jquery'));
	wp_localize_script( 'abc-bookings', 'ajax_abc_bookings', array( 
			'ajaxurl' => admin_url( 'admin-ajax.php' ), 
			'abc_bookings_nonce' => wp_create_nonce('abc-bookings-nonce'), 
			'nlAsked' => $nlAsked,
			'nlEmail' => $nlEmail,
			'nlText' =>  $nlText
			)
	);
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('uikit-js', $abcUrl.'backend/js/uikit.min.js', array('jquery'));
	wp_enqueue_script('uikit-accordion-js', $abcUrl.'backend/js/accordion.min.js', array('jquery'));
	wp_enqueue_script('uikit-tooltip-js', $abcUrl.'backend/js/tooltip.min.js', array('jquery'));
	wp_enqueue_script('uikit-pagination-js', $abcUrl.'backend/js/pagination.min.js', array('jquery'));
	wp_enqueue_script('jquery-validate', $abcUrl.'frontend/js/jquery.validate.min.js', array('jquery'));
	wp_enqueue_script('abc-functions', $abcUrl.'backend/js/abc-functions.js', array('jquery'));
	wp_localize_script( 'abc-functions', 'abc_functions_vars', array( 'dateformat' => $dateformat, 'firstday' => getAbcSetting("firstdayofweek")));
	wp_enqueue_style('abc-datepicker', $abcUrl.'/frontend/css/jquery-ui.min.css');
	wp_enqueue_style('uk-accordion', $abcUrl.'backend/css/accordion.gradient.min.css');
	wp_enqueue_style('uk-tooltip', $abcUrl.'backend/css/tooltip.gradient.min.css');
	wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');
	wp_enqueue_style('abc-adminstyle', $abcUrl.'/backend/css/admin.css');
	wp_enqueue_style( 'font-awesome', $abcUrl.'frontend/css/font-awesome.min.css' );
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
	$settingsMessage = '';
	if ( isset($_GET["setting"]) ) {
		switch ($_GET["setting"]) {
			case 'booked-open':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking has been saved. Has yet to be confirmed.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'booked-confirmed':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking has been saved and state set to confirmed.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'booking-changed':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking has been changed.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'booking-changed-error':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking has been changed, but dates could not be changed! Calendar was occupied.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'confirmed':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking confirmed.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'canceled':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking canceled.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'rejected':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking rejected.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'deleted':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Booking deleted.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'error':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Something went wrong, change didn\'t work.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
			case 'room-changed':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Room has been changed.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;			
			case 'customMessageSend':
					$settingsMessage .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
									<p><strong>'.__('Message has been send.', 'advanced-booking-calendar').'</strong></p><button type="button" class="notice-dismiss">
									<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
					break;
						
		}
	}
	$setupMessage = '';
	$er = $wpdb->get_row('SELECT COUNT(id) as bookings FROM '.$wpdb->prefix.'abc_bookings', ARRAY_A);
	if($er["bookings"] == 0){ // Check if there are bookings
		$setupUrl = '<b><a href="https://booking-calendar-plugin.com/setup-guide" target="_blank">'.__('setup guide', 'advanced-booking-calendar').'</a></b>';
		$calendarLink = '<a href="admin.php?page=advanced-booking-calendar-show-seasons-calendars">'.__('Seasons & Calendars', 'advanced-booking-calendar').'</a>';
		$createBooking = __('Create a Booking', 'advanced-booking-calendar');
		$setupCalendars = '<div class="uk-grid uk-margin-large-bottom">
				<div class="uk-width-2-10 abcOnboarding abcOnboardingDone"> 
					<i class="uk-icon-check-square-o"></i>
				</div>
				<div class="uk-width-8-10 ">
					<h2 class="abcOnboardingDone">'.__('Create a Calendar', 'advanced-booking-calendar').'</h2>	
					</div>
			</div>';
		$er = $wpdb->get_row('SELECT COUNT(id) as calendars FROM '.$wpdb->prefix.'abc_calendars', ARRAY_A);
		if($er["calendars"] == 0){
			$setupCalendars = '
			<div class="uk-grid uk-margin-large-bottom">
					<div class="uk-width-2-10 abcOnboarding"> 
						<i class="uk-icon-square-o"></i>
					</div>
					<div class="uk-width-8-10">
						<h2>'.__('Create a Calendar', 'advanced-booking-calendar').'</h2>
						<p>'.sprintf(__('A calendar is one room or even a group of rooms of the same room type. Start by adding a calendar here: "%s"', 'advanced-booking-calendar'), $calendarLink).'</p>
					</div>
				</div>';
		}
		$setupAssignments = '<div class="uk-grid uk-margin-large-bottom">
				<div class="uk-width-2-10 abcOnboarding abcOnboardingDone"> 
					<i class="uk-icon-check-square-o"></i>
				</div>
				<div class="uk-width-8-10 ">
					<h2 class="abcOnboardingDone">'.__('Create a Season & assign it to a Calendar', 'advanced-booking-calendar').'</h2>	
					</div>
			</div>';	
		$er = $wpdb->get_row('SELECT COUNT(id) as assignments FROM '.$wpdb->prefix.'abc_seasons_assignment', ARRAY_A);
		if($er["assignments"] == 0){
			$setupAssignments = '
			<div class="uk-grid uk-margin-large-bottom">
					<div class="uk-width-2-10 abcOnboarding"> 
						<i class="uk-icon-square-o"></i>
					</div>
					<div class="uk-width-8-10">
						<h2>'.__('Create a Season & assign it to a Calendar', 'advanced-booking-calendar').'</h2>
						<p>'.sprintf(__('With seasons you can create differnt rates for different time periods. Go to "%s" and add a season and assign it to a calendar.', 'advanced-booking-calendar'), $calendarLink).'</p>
					</div>
				</div>';
		}	
		$setupMessage = '
		<div class="uk-width-large-1-2 uk-width-1-1 uk-container-center uk-panel-box">
		<div class="uk-alert">
		    <p>'.sprintf(__('There are currently no bookings. Check out our %s to get started.', 'advanced-booking-calendar'), $setupUrl).'</p>
			<p>'.sprintf(__('If you have a question, feel free to contact us:', 'advanced-booking-calendar'), $setupUrl).'
				<a href="https://booking-calendar-plugin.com/contact/" target="_blank">'.__('Contact Support', 'advanced-booking-calendar').'</a>
			</p>
		</div>
			'.$setupCalendars.'
			'.$setupAssignments.'
			<div class="uk-grid">
				<div class="uk-width-2-10 abcOnboarding"> 
					<i class="uk-icon-square-o"></i>
				</div>
				<div class="uk-width-8-10">
					<h2>'.__('Create a Booking', 'advanced-booking-calendar').'</h2>	
					<p>'.sprintf(__('When everything is set up, add shortcodes to your WordPress pages and wait for the first guest.', 'advanced-booking-calendar'), $setupUrl).'</p>
					<p>'.sprintf(__('You can also click on "%s" and add your first Booking manually.', 'advanced-booking-calendar'), $createBooking).'</p>
				</div>
			</div>	
		</div>';
	}
	if(isset($_GET["action"])) {
		$getAction = $_GET["action"];
	} else {
		$getAction = "";
	}
	if($getAction == 'editBookingRoom'){
		echo abc_booking_editBookingRoomContent();
	}elseif($getAction == 'editBooking'){
		echo abc_booking_editBookingContent();	
	}elseif($getAction == 'customMessage'){
		echo abc_booking_customMessageContent();
	}else {
		$priceOutput = '<span id="abc_totalPrice">0</span> '.getAbcSetting('currency');
		if(getAbcSetting('currencyPosition') == 0 ){
			$priceOutput = getAbcSetting('currency').' <span id="abc_totalPrice">0</span>';
		} 
		echo '<div class="wrap">
				<h1>'.__('Bookings', 'advanced-booking-calendar').'</h1>
				'.$settingsMessage.'
				'.abc_booking_getAvailabilityTable(date_i18n("Y-m-d")).'
				<div id="abctabs2">
					<ul class="uk-tab" data-uk-tab="{connect:\'#tab-content\'}">
						<li><a href="#">'.__('Open Bookings', 'advanced-booking-calendar').'</a></li>
						<li><a href="#">'.__('Confirmed Bookings', 'advanced-booking-calendar').'</a></li>
						<li><a href="#">'.__('Rejected & canceled Bookings', 'advanced-booking-calendar').'</a></li>
						<li><a href="#">'.__('Create a Booking', 'advanced-booking-calendar').'</a></li>
						<li><a href="#">'.__('Search', 'advanced-booking-calendar').'</a></li>
					</ul>
					<ul id="tab-content" class="uk-switcher uk-margin">
						<li>'.abc_booking_getBookings('open').'</li>
						<li>'.abc_booking_getBookings('confirmed').'</li>
						<li>'.abc_booking_getBookings('canceled", "rejected').'</li>
						<li>
						<form class="uk-form uk-form-horizontal" id="abc-booking-form" action="admin-post.php?action=postAbcBooking" method="post">
							<input id="abc-extralist" type="hidden" name="extras" value="">
							<div class="uk-form-row">
								<label class="uk-form-label" for="calendar_id">'.__('Calendar', 'advanced-booking-calendar').'</label>
								<div class="uk-form-controls">
									<select id="calendar_id" name="calendar_id" required>'.abc_booking_getCalOptList().'</select>
								</div>	
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="persons">'.__('Persons', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<select id="persons" name="persons" required><option disabled value="" selected>'.__('Set calendar', 'advanced-booking-calendar').'...</option></select>
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="start">'.__('Checkin Date', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									 <div class="uk-form-icon">
		                                <i class="uk-icon-calendar"></i>
										<input type="text" id="start" name="start" required>
									</div>	
								 </div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="end">'.__('Checkout Date', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									 <div class="uk-form-icon">
		                                <i class="uk-icon-calendar"></i>
										<input type="text" id="end" name="end" required>
									</div>	
								 </div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('State', 'advanced-booking-calendar').'</span>
								 <div class="uk-form-controls" id="abc_dateStatus">
								</div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('Extras', 'advanced-booking-calendar').'</span>
								<div class="uk-form-controls" id="abc_optionalExtras">
								</div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('Additional costs', 'advanced-booking-calendar').'</span>
								<div class="uk-form-controls" id="abc_mandatoryExtras">
								</div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('Total price', 'advanced-booking-calendar').'</span>
								<div class="uk-form-controls"><b>'.$priceOutput.'</b>
								</div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('Set Booking State to', 'advanced-booking-calendar').'</span>
								 <div class="uk-form-controls uk-form-controls-tex">
									<input id="radio-open" type="radio" name="state" value="open" checked> <label for="radio-open">'.__('Open (has to be confirmed)', 'advanced-booking-calendar').'</label><br/>
									<input id="radio-confirmed" type="radio" name="state" value="confirmed"> <label for="radio-confirmed">'.__('Confirmed', 'advanced-booking-calendar').'</label>
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="first_name">'.__('First Name', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="first_name" name="first_name" placeholder="John">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="last_name">'.__('Last Name', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="last_name" name="last_name" placeholder="Doe">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="email">'.__('Email Address', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="email" name="email" placeholder="your@email.com">
								</div>
							</div>
							<div class="uk-form-row">
								<span class="uk-form-label">'.__('Send confirmation email to guest', 'advanced-booking-calendar').'</span>
								 <div class="uk-form-controls uk-form-controls-text">
									<input id="radio-yes" type="radio" name="emailconfirmation" value="yes"> <label for="radio-yes">'.__('Yes', 'advanced-booking-calendar').'</label><br/>
									<input id="radio-no" type="radio" name="emailconfirmation" value="no" checked> <label for="radio-no">'.__('No', 'advanced-booking-calendar').'</label>
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="phone">'.__('Phone Number', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="phone" name="phone" placeholder="+1 123 456 789">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="address">'.__('Street Address, House no.', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="address" name="address" placeholder="1 Wall St">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="zip">'.__('ZIP Code', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="zip" name="zip" placeholder="NY 10286">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="city">'.__('City', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="city" name="city" placeholder="New York City">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="county">'.__('State / County', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="county" name="county" placeholder="New York">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="country">'.__('Country', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<input type="text" id="country" name="country" placeholder="USA">
								</div>
							</div>
							<div class="uk-form-row">
								<label class="uk-form-label" for="message">'.__('Message, special note for stay', 'advanced-booking-calendar').'</label>
								 <div class="uk-form-controls">
									<textarea id="message" name="message"></textarea>	
								</div>
							</div>
							<div class="uk-form-row">
								<input id="postAbcBooking" class="button button-primary" type="submit" value="'.__('Create Booking', 'advanced-booking-calendar').'"/>
							</div>
						</form>
						</li>
						<li>
						<form class="uk-form uk-form-horizontal" action="">
							<input type="text" id="abcBookingSearchText" placeholder="'.__('search', 'advanced-booking-calendar').'..."/>
							<input id="abcBookingSearchButton" class="button button-primary" type="submit" value="'.__('Search', 'advanced-booking-calendar').'"/>
						</form>
						<div id="abcBookingSearchResult"></div>
						</li>	
					</ul>
					'.$setupMessage.'
				</div>
			</div>'.$feedbackModal;
	}

}//==>advanced_booking_calendar_show_bookings()