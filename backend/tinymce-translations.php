<?php
if ( ! defined( 'ABSPATH' ) ) exit;
 
if ( ! class_exists( '_WP_Editors' ) ) require( ABSPATH . WPINC . '/class-wp-editor.php' );
 
function abc_booking_tinymce_translation() {
    
    $strings = array(
        'bookingform' => __('Booking Form', 'advanced-booking-calendar'),
        'hideother' => __('Hide other Rooms when coming from a Single Calendar link.', 'advanced-booking-calendar-pro'),
        'hidetooshort' => __('Hide Rooms when minimum number of nights is too short.', 'advanced-booking-calendar-pro'),
        'calendaroverview' => __('Calendar Overview', 'advanced-booking-calendar'),
        'addsingle' => __('Add Single Calendar', 'advanced-booking-calendar'),
        'calendar' => __('Calendar', 'advanced-booking-calendar'),
        'calendars' => __('Calendars', 'advanced-booking-calendar'),
        'listofcalendars' => __('Comma separated list of calendar ids, if you want to limit the form to a calendar. If in doubt, leave empty.', 'advanced-booking-calendar'),
        'legend' => __('Show legend', 'advanced-booking-calendar'),
        'singlecalendar' => __('Single Calendar', 'advanced-booking-calendar')
    );
 
    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.advanced-booking-calendar", ' . json_encode( $strings ) . ");\n";
 
    return $translated;
}
 
$strings = abc_booking_tinymce_translation();