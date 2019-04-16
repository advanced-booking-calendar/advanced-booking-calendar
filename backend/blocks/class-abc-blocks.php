<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Blocks Class
 * Manage Gutenberg blocks
 */
class ABC_Booking_Blocks {
	public function __construct() {
		// Register block category
		add_filter( 'block_categories', array($this, 'abc_block_category'), 10, 2 );

		// Register Gutenberg blocks
		add_action( 'init', array($this, 'abc_register_gutenberg_blocks') );

		// Manage editor scripts
		add_action( 'enqueue_block_editor_assets', array($this, 'abc_custom_blocks_scripts') );
	}

	/**
	 * Check if gutenberg is active or not
	 */
	public function is_blocks_active() {
		if( ! function_exists('register_block_type') ) return false;
		return true;
	}

	/**
	 * Booking block category
	 */
	public function abc_block_category( $categories, $post ) {
		$categories[] = array(
			'slug' => 'abc-shortcodes',
			'title' => esc_html__( 'Advanced Booking Calendar', 'advanced-booking-calendar' ),
		);

		return $categories;
	}

	/**
	 * Manage blocks scripts
	 */
	public function abc_custom_blocks_scripts() {
		if( ! $this->is_blocks_active() ) return;

		global $abcUrl, $abcDir;

		// Block Styles
		wp_register_style(
			'abc-blocks-style', $abcUrl . 'backend/css/abc-blocks.css',
			array( 'wp-edit-blocks' ), filemtime( $abcDir . '/backend/css/abc-blocks.css' )
		);

		// Blocks script
		wp_register_script(
			'abc-blocks-script', $abcUrl . 'backend/js/abc-blocks.js',
			array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor' ),
			filemtime( $abcDir . '/backend/js/abc-blocks.js' )
		);

		//Strings array
		$strings = array(
			'overview' => array(
				'title' => esc_html__( 'Calendar Overview', 'advanced-booking-calendar' ),
				'desc' => esc_html__( 'It shows all calendars and their availabilities by month.', 'advanced-booking-calendar' )
			),
			'single' => array(
				'title' => esc_html__( 'Single Calendar', 'advanced-booking-calendar' ),
				'desc' => esc_html__( 'It shows one single calendar. Please select a calender below.', 'advanced-booking-calendar' ),
				'cal_lbl' => esc_html__( 'Please select a calendar:', 'advanced-booking-calendar' ),
				'sel_cal' => esc_html__( 'Select a calendar', 'advanced-booking-calendar' ),
				'legend_lbl' => esc_html__( 'Display legend?', 'advanced-booking-calendar' )
			),
			'bookingform' => array(
				'title' => esc_html__( 'Booking Form', 'advanced-booking-calendar' ),
				'desc' => esc_html__( 'The booking form fulfils two tasks: finding the right room for users and generating booking requests. Every user action happens onpage via AJAX, so the page does not reload during interactions.', 'advanced-booking-calendar' ),
				'cal_lbl' => esc_html__( 'Please select calendars:', 'advanced-booking-calendar' ),
				'hide_other_lbl' => esc_html__( 'Hide other Rooms when coming from a Single Calendars?', 'advanced-booking-calendar' ),
				'hide_tooshort_lbl' => esc_html__( 'Hide Rooms when minimum number of nights is too short?', 'advanced-booking-calendar' )
			)
		);

		// Localize scripts for strings
		wp_localize_script( 'abc-blocks-script', 'abcStrs', $strings );

		// Script contains translations
		if( function_exists('wp_set_script_translations') ) {
			wp_set_script_translations( 'abc-blocks-script', 'advanced-booking-calendar' );
		}
	}

	/**
	 * Register Blocks
	 */
	public function abc_register_gutenberg_blocks() {
		// Gutenberg is not active.
		if( ! $this->is_blocks_active() ) return;

		// Register Blocks
		// Calendar overview block
		register_block_type( 'abc-shortcodes/calendar-overview', array(
			'editor_script' => 'abc-blocks-script',
			'editor_style'  => 'abc-blocks-style'
		) );

		// Single calendar block
		register_block_type( 'abc-shortcodes/single-calendar', array(
			'editor_script' => 'abc-blocks-script',
			'editor_style'  => 'abc-blocks-style'
		) );

		// Booking form block
		register_block_type( 'abc-shortcodes/booking-form', array(
			'editor_script' => 'abc-blocks-script',
			'editor_style'  => 'abc-blocks-style'
		) );
	}
}

return new ABC_Booking_Blocks();