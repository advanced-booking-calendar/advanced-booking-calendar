<?php

// Add a Extra to DB
function abc_booking_addExtra() {
	global $wpdb;

	if( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	
	abc_booking_setPersonCount();
	$extraLimit = '&setting=extraAdded';

	if( isset($_POST["name"]) && isset($_POST["mandatory"]) && isset($_POST["calculation"]) 
		&& isset($_POST["price"]) && intval($_POST["persons"]) > 0 ) {
			
		$er = $wpdb->get_row( 'SELECT COUNT(id) as extras FROM '.$wpdb->prefix.'abc_extras', ARRAY_A );
		$extras = $er["extras"];
		if( $extras < 2 ) {
			
			$name			= sanitize_text_field($_POST["name"]);
			$explanation	= sanitize_text_field($_POST["explanation"]);
			$calculation	= sanitize_text_field($_POST["calculation"]);
			$mandatory		= sanitize_text_field($_POST["mandatory"]);
			$price			= sanitize_text_field($_POST["price"]);
			$persons		= intval($_POST["persons"]);
			$order			= intval( $_POST["order"] );
			
			$wpdb->insert( $wpdb->prefix.'abc_extras', array(
				'name'			=> $name,
				'explanation'	=> $explanation,
				'calculation'	=> $calculation,
				'mandatory'		=> $mandatory,
				'price'			=> $price,
				'persons'		=> $persons,
				'order'			=> $order
			));

			if($extras == 0 && strtotime(getAbcSetting('installdate')) < strtotime('2016-07-10')){
				$extraLimit = '&setting=extraNew';
			}
		} else {
			$extraLimit = "&setting=extraLimit";
		}	
	}

	wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-extras".$extraLimit ) );
	exit;

} //==>addExtra()
add_action( 'admin_post_abc_booking_addExtra', 'abc_booking_addExtra' );

// Edit season 
function abc_booking_editExtra() {
	global $wpdb;

	if( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}

	if( isset($_POST["id"]) && isset($_POST["name"]) && isset($_POST["mandatory"]) 
		&& isset($_POST["calculation"]) && isset($_POST["price"])
		&& intval($_POST["persons"]) > 0 ) {
		
		$name			= sanitize_text_field($_POST["name"]);
		$explanation	= sanitize_text_field($_POST["explanation"]);
		$calculation	= sanitize_text_field($_POST["calculation"]);
		$mandatory		= sanitize_text_field($_POST["mandatory"]);
		$price			= sanitize_text_field($_POST["price"]);	
		$persons		= intval($_POST["persons"]);
		$order			= intval($_POST["order"]);
		
		$wpdb->update($wpdb->prefix.'abc_extras', array(
				'name'			=> $name,
				'explanation'	=> $explanation,
				'calculation'	=> $calculation,
				'mandatory'		=> $mandatory,
				'price'			=> $price,
				'persons'		=> $persons,
				'order'			=> $order),
			array('id' => intval($_POST["id"])));
	}

	wp_redirect(  admin_url("admin.php?page=advanced-booking-calendar-show-extras&setting=changeSaved") );
	exit;
} //==>editExtra()
add_action( 'admin_post_abc_booking_editExtra', 'abc_booking_editExtra' );

// Delete Extra
function abc_booking_delExtra() {
	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}
	if( isset($_POST["id"]) ) {
		$wpdb->delete($wpdb->prefix.'abc_extras', array('id' => intval($_POST["id"])));
		$wpdb->delete($wpdb->prefix.'abc_booking_extras', array('extra_id' => intval($_POST["id"])));
		wp_redirect(  admin_url( "admin.php?page=advanced-booking-calendar-show-extras&setting=extraDeleted" ) );
	}
	exit;
} //==>delExtra()
add_action( 'admin_post_abc_booking_delExtra', 'abc_booking_delExtra' );

// Order Extra
function abc_booking_orderExtra() {

	global $wpdb;

	if ( !current_user_can( abc_booking_admin_capabilities() ) ) {
		wp_die("You don't have access to this page.");
	}

	if( !empty($_POST['extraid']) && is_array($_POST['extraid']) ) {

		foreach( $_POST['extraid'] as $order => $extraid ) {

			$wpdb->update($wpdb->prefix.'abc_extras', array(
				'order'	=> intval( $order + 1 )
			), array( 'id' => intval($extraid)) );
		}
	}

	$redirect = add_query_arg( array(
		'page' => 'advanced-booking-calendar-show-extras',
		'setting' => 'extraOrdered',
	), admin_url('admin.php') );

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_abc_booking_orderExtra', 'abc_booking_orderExtra' );

// Output to backend
function advanced_booking_calendar_show_extras() {
	
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
	}
	
	global $wpdb, $abcUrl;

	wp_enqueue_script('uikit-js', $abcUrl.'backend/js/uikit.min.js', array('jquery'));
	wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');

	$notices = '';	
	if(isset($_GET["setting"]) ){
		switch($_GET["setting"]){
			case 'extraLimit':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
							<p><strong>'.__('Extras are limited to 2 in the free version! If you need more extras, please download our <a href="https://booking-calendar-plugin.com/pro-download/?cmp=ExtraLimit" target="_blank">Pro-Version</a>.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
								<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'extraAdded':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
							<p><strong>'.__('Extra has been added.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
							<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'extraNew':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
							<p><strong>'.__('Extra has been added.', 'advanced-booking-calendar' ).'<br/>'
								.__('Make sure to add the placeholdes for extras in your email settings', 'advanced-booking-calendar' ).': 
								<a href="'.admin_url('admin.php?page=advanced-booking-calendar-show-settings').'">'.__('Email Settings', 'advanced-booking-calendar').'</a></strong></p><button type="button" class="notice-dismiss">
								<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'changeSaved':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
							<p><strong>'.__('Change has been saved.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
							<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
			case 'extraOrdered':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
							<p><strong>'.__('Extras have been ordered.', 'advanced-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
							<span class="screen-reader-text">'.__('Dismiss this notice.', 'advanced-booking-calendar').'</span></button></div>';
				break;
		}
	}
	$output = '<div class="wrap">
				<h1>'.__('Extras', 'advanced-booking-calendar').'</h1>'.$notices.'';
	
	if( isset($_GET["action"]) && $_GET["action"] == "abc_booking_editExtra" &&
		intval($_GET["id"]) > 0 ) {
		
		// Does the ID exist?
		$row = $wpdb->get_row( "SELECT COUNT(*) as co FROM ".$wpdb->prefix."abc_extras WHERE id = '".intval($_GET["id"])."'", ARRAY_A );

		if($row["co"] == 0) {
			
			// ID doesn't exist
			wp_die( "Error! Unknown id<br />Please go <a href='admin.php?page=advanced-booking-calendar-show-extras'>back</a>" );

		} else {
			
			//ID exists
			$row = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."abc_extras WHERE id = '".intval($_GET["id"])."'", ARRAY_A );

			$mandatoryYes = '';
			$mandatoryNo = '';
			switch($row[0]["mandatory"]){
				case 'yes':
					$mandatoryYes = 'checked';
					break;
				case 'no':
					$mandatoryNo = 'checked';
					break;
			}

			$calculationNight = '';
			$calculationDay = '';
			$calculationOnce = '';
			$calculationPerson = '';
			$calculationPersonNight = '';
			$calculationPersonDay = '';

			switch( $row[0]["calculation"] ) {
				case 'night':
					$calculationNight = 'selected';
					break;
				case 'day':
					$calculationDay = 'selected';
					break;
				case 'once':
					$calculationOnce = 'selected';
					break;
				case 'person':
					$calculationPerson = 'selected';
					break;
				case 'personNight':
					$calculationPersonNight = 'selected';
					break;
				case 'personDay':
					$calculationPersonDay = 'selected';
					break;
			}

			$output .= '<h3>'.__('Edit an Extra', 'advanced-booking-calendar').'</h3>
				<div class="wrap">
					<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="abc_booking_editExtra" />
						<input type="hidden" name="id" value="'.intval($_GET["id"]).'" />

						<table class="form-table">
							<tr>
								<td><label for="name">'
									.__('Name', 'advanced-booking-calendar').
								'</label></td>
								<td align="left">
									<input name="name" id="name" type="text" class="regular-text code" value="'.$row[0]["name"].'" required />
									<p class="description">'.__('The name will be shown in the booking form.', 'advanced-booking-calendar').'</p>
								</td>
							</tr>

							<tr>
								<td><label for="explanation">'
									.__('Explanation', 'advanced-booking-calendar').'</br><em>'.__('(optional)', 'advanced-booking-calendar').'</em>
								</label></td>
								<td align="left">
									<input value="'.$row[0]["explanation"].'" name="explanation" id="explanation" type="text" class="regular-text code" />
									<p class="description">'.__('Explain what the extra is for.', 'advanced-booking-calendar').'</p></td>
							</tr>

							<tr>
								<td><label for="calculation">'
									.__('Type of calculation', 'advanced-booking-calendar').
								'</label></td>
								<td align="left">
									<select name="calculation" id="calculation">
										<option value="night" '.$calculationNight.'>'.__('per night', 'advanced-booking-calendar').'</option>
										<option value="day" '.$calculationDay.'>'.__('per day', 'advanced-booking-calendar').'</option>
										<option value="once" '.$calculationOnce.'>'.__('once (no matter how many persons)', 'advanced-booking-calendar').'</option>
										<option value="person" '.$calculationPerson.'>'.__('per person (once)', 'advanced-booking-calendar').'</option>
										<option value="personNight" '.$calculationPersonNight.'>'.__('per person per night', 'advanced-booking-calendar').'</option>
										<option value="personDay" '.$calculationPersonDay.'>'.__('per person per day', 'advanced-booking-calendar').'</option>
									</select>
									<p class="description">'.__('Define how the price of the extra is getting charged.', 'advanced-booking-calendar').'</p>
								</td>
							</tr>

							<tr>
								<td>'.__('Mandatory extra', 'advanced-booking-calendar').'</td>
								<td align="left">
									<label><input name="mandatory" id="mandatoryYes" type="radio" value="yes" '.$mandatoryYes.' />'.__('Yes', 'advanced-booking-calendar').'&nbsp;&nbsp;</label>
									<label><input name="mandatory" id="mandatoryNo" type="radio" value="no" '.$mandatoryNo.' />'.__('No', 'advanced-booking-calendar').'</label>
									<p class="description">'.__('You can make an extra mandatory, so every guest has to pay for it (eg. final cleaning).', 'advanced-booking-calendar').'</p>
								</td>
							</tr>

							<tr>
								<td><label for="price">'
									.__('Price', 'advanced-booking-calendar').
								'</label></td>
								<td align="left">
									<input value="'.$row[0]["price"].'" name="price" id="price" type="number" step="0.01" class="regular-text code" min="0.01" required />
									<p class="description">'.__('Enter the price you want to charge for the extra.', 'advanced-booking-calendar').'</p>
								</td>
							</tr>

							<tr>
								<td><label for="persons">'
									.__('Persons', 'advanced-booking-calendar').
								'</label></td>
								<td align="left">
									<input value="'.$row[0]["persons"].'" name="persons" id="persons" type="number" step="1" class="regular-text code" min="1" required />
									<p class="description">'.__('Enter the number of persons in a booking request to activate this extra. For example, if you enter &quot;4&quot;, the extra will be shown for <b>4 or more</b> persons.', 'advanced-booking-calendar').'</p>
								</td>
							</tr>

							<tr>
								<td><label for="order">'
									.__('Order', 'advanced-booking-calendar').
								'</label></td>
								<td align="left">
									<input value="'.$row[0]["order"].'" name="order" id="order" type="number" step="1" class="short-text code" min="0" required />
									<p class="description">'.__('Enter the position of the extra it should appear in. For example 1 for the first position.', 'advanced-booking-calendar').'</p>
								</td>
							</tr>
						</table>
						<br />

						<input class="button button-primary" type="submit" value="'.__('Save', 'advanced-booking-calendar').'" />

						<a href="admin.php?page=advanced-booking-calendar-show-extras">
							<input class="button button-secondary" type="button" value="'._x('Cancel', 'a change', 'advanced-booking-calendar').'" />
						</a>
					</form>
				</div>'
			;
		}

	} elseif( isset($_GET["action"]) && $_GET["action"] == "abc_sort_extras" ) {

		$extras = '';
		$query = "SELECT * FROM ".$wpdb->prefix."abc_extras ORDER BY `order` ASC, `id` DESC";
		$er = $wpdb->get_results( $query, ARRAY_A );

		wp_enqueue_style('abc-adminstyle', $abcUrl.'/backend/css/admin.css');

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'abc-admin', $abcUrl.'backend/js/abc-admin.js', array('jquery') );

		$foreachcount = 1;
		foreach( $er as $row ) {
			
			if( $foreachcount%2 == 1 ) {
				$class = 'class="alternate"';
			} else {
				$class = '';
			}

			$mandatory = '';
			switch( $row["mandatory"] ){
				case 'yes':
					$mandatory = __( 'Yes', 'advanced-booking-calendar' );
					break;
				case 'no':
					$mandatory = __( 'No', 'advanced-booking-calendar' );
					break;
			}

			$calculation = '';
			switch($row["calculation"]){
				case 'night':
					$calculation = __('per night', 'advanced-booking-calendar');
					break;
				case 'day':
					$calculation = __('per day', 'advanced-booking-calendar');
					break;
				case 'once':
					$calculation = __('once (no matter how many persons)', 'advanced-booking-calendar');
					break;
				case 'person':
					$calculation = __('per person (once)', 'advanced-booking-calendar');
					break;
				case 'personNight':
					$calculation = __('per person per night', 'advanced-booking-calendar');
					break;
				case 'personDay':
					$calculation = __('per person per day', 'advanced-booking-calendar');
					break;
			}

			$extras .= '<tr '.$class.'>
				<td><span class="abc-dragit"></span>'
					.esc_html( $row["order"] ).
					'<input type="hidden" name="extraid[]" value="'.$row["id"].'" />
					<input type="hidden" name="order['.$row["id"].'][]" value="'.$row["order"].'" />
				</td>
				<td>'.esc_html( $row["name"] ).'</td>
				<td>'.esc_html( $row["explanation"] ).'</td>
				<td>'.$calculation.'</td>
				<td>'.$mandatory.'</td>
				<td>'.abc_booking_formatPrice(esc_html($row["price"])).'</td>
				<td>'.intval($row["persons"]).'</td>
			</tr>';

			$foreachcount++;
		}

		$output .= '<div class="wrap">
			<h3>'.__('Order Extras', 'advanced-booking-calendar').'</h3>

			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="abc_booking_orderExtra" />
				<table class="wp-list-table widefat abc-sortable">
					<thead>
						<tr>
							<th>'.__('Order', 'advanced-booking-calendar').'</th>
							<th>'.__('Name', 'advanced-booking-calendar').'</th>
							<th>'.__('Explanation', 'advanced-booking-calendar').'</th>
							<th>'.__('Type of calculation', 'advanced-booking-calendar').'</th>
							<th>'.__('Mandatory', 'advanced-booking-calendar').'</th>
							<th>'.__('Price', 'advanced-booking-calendar').'</th>
							<th>'.__('Persons', 'advanced-booking-calendar').'</th>
						</tr>
					</thead>

					<tbody>'.$extras.'</tody>
				</table>
				<br />
				<input class="button button-primary" type="submit" value="'.__('Save Order', 'advanced-booking-calendar').'" />
				<a href="admin.php?page=advanced-booking-calendar-show-extras">
					<input class="button button-secondary" type="button" value="'._x('Cancel', 'a change', 'advanced-booking-calendar').'" />
				</a>
			</form>
			<hr/>	
			<p>
				'.__('Do you want to promote your business using discount codes?', 'advanced-booking-calendar').' 
				'.__('Or do you want to limit an extra to a calendar?', 'advanced-booking-calendar').'<br/>
				'.__('Take a look at our <a target="_blank" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=DiscountCodes">Pro Version</a>!', 'advanced-booking-calendar').'<br/>
				'.__('Use discount code <b>BASICUPGRADE</b> to save 10€.', 'advanced-booking-calendar').'
			</p>
		</div>';

	} else {

		$extras = '';
		$query = "SELECT * FROM ".$wpdb->prefix."abc_extras ORDER BY `order` ASC, `id` DESC";
		$er = $wpdb->get_results( $query, ARRAY_A );
		$maxOrder = 1;

		$foreachcount = 1;
		foreach( $er as $row ) {

			if( $maxOrder <= $row["order"] ) $maxOrder = $row["order"]+1;
			
			if( $foreachcount%2 == 1 ) {
				$class = 'class="alternate"';
			} else {
				$class = '';
			}

			$mandatory = '';
			switch( $row["mandatory"] ){
				case 'yes':
					$mandatory = __( 'Yes', 'advanced-booking-calendar' );
					break;
				case 'no':
					$mandatory = __( 'No', 'advanced-booking-calendar' );
					break;
			}

			$calculation = '';
			switch($row["calculation"]){
				case 'night':
					$calculation = __('per night', 'advanced-booking-calendar');
					break;
				case 'day':
					$calculation = __('per day', 'advanced-booking-calendar');
					break;
				case 'once':
					$calculation = __('once (no matter how many persons)', 'advanced-booking-calendar');
					break;
				case 'person':
					$calculation = __('per person (once)', 'advanced-booking-calendar');
					break;
				case 'personNight':
					$calculation = __('per person per night', 'advanced-booking-calendar');
					break;
				case 'personDay':
					$calculation = __('per person per day', 'advanced-booking-calendar');
					break;
			}

			$extras .= '<tr '.$class.'>
				 <td>'.esc_html($row["order"]).'</td>
				 <td>'.esc_html($row["name"]).'</td>
				 <td>'.esc_html($row["explanation"]).'</td>
				 <td>'.$calculation.'</td>
				 <td>'.$mandatory.'</td>
				 <td>'.abc_booking_formatPrice(esc_html($row["price"])).'</td>
				 <td>'.intval($row["persons"]).'</td>
				 <td align="left">
				 	<form style="display: inline;" action="admin.php" method="get">
						<input type="hidden" name="page" value="advanced-booking-calendar-show-extras" />
						<input type="hidden" name="action" value="abc_booking_editExtra" />
						<input type="hidden" name="id" value="'.$row["id"].'" />
						<input class="button button-primary" type="submit" value="'.__('Edit', 'advanced-booking-calendar').'" />
					</form>
					
					<form style="display: inline;" action="admin-post.php?action=abc_booking_delExtra" method ="post">
						
						<input type="hidden" name="id" value="'.$row["id"].'" />
						<input class="button button-primary" type="submit" value="'.__('Delete', 'advanced-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this extra?', 'advanced-booking-calendar').'\')" />
					</form>
				</td>
			</tr>';

			$foreachcount++;
		}

		$sortURL = add_query_arg( array(
				'page'		=> 'advanced-booking-calendar-show-extras',
				'action'	=> 'abc_sort_extras',
			), admin_url('admin.php') );

		$output .= '<h3>'
				.__('Existing Extras', 'advanced-booking-calendar').

				'&nbsp;&nbsp;
				<a href="'.$sortURL.'" class="button button-secondary">'.__( 'Sort Extras', 'advanced-booking-calendar' ).'</a>
			</h3>

			<table class="wp-list-table widefat">
				<thead>
					<tr>
						<th>'.__('Order', 'advanced-booking-calendar').'</th>
						<th>'.__('Name', 'advanced-booking-calendar').'</th>
						<th>'.__('Explanation', 'advanced-booking-calendar').'</th>
						<th>'.__('Type of calculation', 'advanced-booking-calendar').'</th>
						<th>'.__('Mandatory', 'advanced-booking-calendar').'</th>
						<th>'.__('Price', 'advanced-booking-calendar').'</th>
						<th>'.__('Persons', 'advanced-booking-calendar').'</th>
						<th align="left"></th>
					</tr>
				</thead>
				<tbody>'.$extras.'</tbody>
			</table>
			<hr/>

			<h3>'.__('Add new Extra', 'advanced-booking-calendar').'</h3>
			<div class="wrap">
				<form method="post" action="admin-post.php">
					<input type="hidden" name="action" value="abc_booking_addExtra" />
					<table class="form-table">
						<tr>
							<td><label for="name">'
								.__('Name', 'advanced-booking-calendar').
							'</label></td>
							<td align="left">
								<input name="name" placeholder="'.__('Wifi', 'advanced-booking-calendar').'" id="name" type="text" class="regular-text code" required />
								<p class="description">'.__('The name will be shown in the booking form.', 'advanced-booking-calendar').'</p>
							</td>
						</tr>

						<tr>
							<td><label for="explanation">'
								.__('Explanation', 'advanced-booking-calendar').'</br><em>'.__('(optional)', 'advanced-booking-calendar').'</em>
							</label></td>
							<td align="left">
								<input placeholder="'.__('Get Wifi-access in your room.', 'advanced-booking-calendar').'" name="explanation" id="explanation" type="text" class="regular-text code"/>
								<p class="description">'.__('Explain what the extra is for.', 'advanced-booking-calendar').'</p>
							</td>
						</tr>

						<tr>
							<td><label for="calculation">'
								.__('Type of calculation', 'advanced-booking-calendar').
							'</label></td>
							<td align="left">
								<select name="calculation" id="calculation">
									<option value="night">'.__('per night', 'advanced-booking-calendar').'</option>
									<option value="day">'.__('per day', 'advanced-booking-calendar').'</option>
									<option value="once">'.__('once (no matter how many persons)', 'advanced-booking-calendar').'</option>
									<option value="person">'.__('per person (once)', 'advanced-booking-calendar').'</option>
									<option value="personNight">'.__('per person per night', 'advanced-booking-calendar').'</option>
									<option value="personDay">'.__('per person per day', 'advanced-booking-calendar').'</option>
								</select>
								<p class="description">'.__('Define how the price of the extra is getting charged.', 'advanced-booking-calendar').'</p>
							</td>
						</tr>

						<tr>
							<td>'.__('Mandatory extra', 'advanced-booking-calendar').'</td>
							<td align="left">
								<label><input name="mandatory" id="mandatoryYes" type="radio" value="yes" /> '.__('Yes', 'advanced-booking-calendar').'&nbsp;&nbsp;</label>
								<label><input name="mandatory" id="mandatoryNo" type="radio" value="no" checked /> '.__('No', 'advanced-booking-calendar').'</label>
								<p class="description">'.__('You can make an extra mandatory, so every guest has to pay for it (eg. final cleaning).', 'advanced-booking-calendar').'</p>
							</td>
						</tr>

						<tr>
							<td><label for="price">'
								.__('Price', 'advanced-booking-calendar').
							'</label></td>
							<td align="left">
								<input placeholder="5,00" name="price" id="price" type="number" step="0.01" class="regular-text code" min="0.01" required />
								<p class="description">'.__('Enter the price you want to charge for the extra.', 'advanced-booking-calendar').'</p>
							</td>
						</tr>
					  
						<tr>
							<td><label for="price">'
								.__('Persons', 'advanced-booking-calendar').
							'</label></td>
							<td align="left">
								<input value="1" name="persons" id="persons" type="number" step="1" class="regular-text code" min="1" required />
								<p class="description">'.__('Enter the number of persons in a booking request to activate this extra. For example, if you enter &quot;4&quot;, the extra will be shown for <b>4 or more</b> persons.', 'advanced-booking-calendar').'</p>
							</td>
						</tr>

						<tr>
							<td><label for="order">'
								.__('Order', 'advanced-booking-calendar').
							'</label></td>
							<td align="left">
								<input name="order" id="order" type="number" step="1" class="short-text code" min="0" value="'.$maxOrder.'" required />
								<p class="description">'.__('Enter the position of the extra it should appear in. For example 1 for the first position.', 'advanced-booking-calendar').'</p>
							</td>
						</tr>
					</table>
					<br />

					<input class="button button-primary" type="submit" value="'.__('Add Extra', 'advanced-booking-calendar').'" />
				</form>
				
				<hr />
				<p>
					'.__('Do you want to promote your business using discount codes?', 'advanced-booking-calendar').' 
					'.__('Or do you want to limit an extra to a calendar?', 'advanced-booking-calendar').'<br/>
					'.__('Take a look at our <a target="_blank" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=DiscountCodes">Pro Version</a>!', 'advanced-booking-calendar').'<br/>
					'.__('Use discount code <b>BASICUPGRADE</b> to save 10€.', 'advanced-booking-calendar').'
				</p>
			</div>';
	}				  
	
	$output .= '</div>';
	echo $output;

	// id
	// name 
	// explanation
	// calculation: night, day, person, once, per night per person
	// price
}