<?php
add_action('admin_head', 'abc_booking_add_tinymce_button');

function abc_booking_add_tinymce_button() {
    global $typenow;
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		return;
    }
    if( ! in_array( $typenow, array( 'post', 'page' ) ) ){
		return;
	}
	
    if ( get_user_option('rich_editing') == 'true') {
    	global $wpdb;
		$calendar = $blockCal = array();
    	$calendarRows = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."abc_calendars", ARRAY_A);
		foreach($calendarRows as $row) {
			$calendar[] = array('text' => $row["name"], 'value' => $row["id"]);
            $blockCal[] = array('label' => $row["name"], 'value' => $row["id"]);;
		}
		echo "<script type='text/javascript'>"; 
        echo "var abc_tinymce_calendars = ".json_encode($calendar).";"; 
	    echo "var abc_block_calendars = ".json_encode($blockCal).";"; 
		echo "</script>"; 
    }
}

add_filter('mce_buttons', 'abc_booking_register_tinymce_button');
add_filter("mce_external_plugins", "abc_booking_add_tinymce_plugin");

function abc_booking_add_tinymce_plugin($plugin_array) {
    $plugin_array['abc_booking_button'] = plugins_url( '/js/abc-tinymce.js', __FILE__ ); // CHANGE THE BUTTON SCRIPT HERE
    return $plugin_array;
}

function abc_booking_register_tinymce_button($buttons) {
   array_push($buttons, "abc_booking_button");
   return $buttons;
}

function abc_booking_tinymce_css() {
    wp_enqueue_style('abc-booking-tinymce', plugins_url('css/tinymce.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'abc_booking_tinymce_css');
	
function abc_booking_tinymce_lang($locales) {
    $locales['advanced-booking-calendar'] = plugin_dir_path ( __FILE__ ) . 'tinymce-translations.php';
    return $locales;
}
 
add_filter( 'mce_external_languages', 'abc_booking_tinymce_lang');
?>