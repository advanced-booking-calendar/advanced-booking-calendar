<?php

class abcAvailabilityFormWidget extends WP_Widget {
	
	// constructor
	function __construct() {
		$widget_ops = array( 
			'classname' => 'abcAvailabilityFormWidget',
			'description' => __('Advanced Booking Calendar Widget.', 'advanced-booking-calendar'),
		);
		parent::__construct( false, __('Availability Form Widget', 'advanced-booking-calendar'), $widget_ops );
	}
	
	// widget form creation
	function form($instance) {
		
	// Check title
	if( $instance) {
		$title = esc_attr($instance['title']);
	
	} else {
		// Initial title
		$title = __('Availability', 'advanced-booking-calendar');
	}	
		echo "<p>
				<label for=\"".$this->get_field_id('title')."\">".__('Title:', 'advanced-booking-calendar')."</label>
				<input class=\"widefat\" id=\"".$this->get_field_id('title')."\" name=\"".$this->get_field_name('title')."\" type=\"text\" value=\"".$title."\" />
			</p>";
		// Frontend output
		if(getAbcSetting("bookingpage") > 0){
			echo "<p>".__('This widgets loads a small booking form. After a user selected the dates and clicked on "Check availabilites", the booking form is loaded.', 'advanced-booking-calendar')."</p>";
		} else {
			echo "<p style=\"color:red\">".__('There is no booking page configured. Check the settings and select a page with the booking form.', 'advanced-booking-calendar')."</p>";
		}
	
	}
	
	// Update function for changes
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	
	// Display widget
	function widget($args, $instance) {
		global $abcUrl;
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		echo '<div class="widget-text">';
		// Check if title is set
		if ( $title ) {
			echo $before_title . $title . $after_title ;
		}
		echo '
			<div class="widget-textarea">';
		echo abc_booking_showBookingWidget($args);
		echo '</div></div>';
		echo $after_widget;
	}
}

// Register widget
add_action('widgets_init', create_function('', 'return register_widget("abcAvailabilityFormWidget");'));

?>
