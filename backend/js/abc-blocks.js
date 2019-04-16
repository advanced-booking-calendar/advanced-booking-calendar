/**
 * Block JS,
 * Script for all Blocks
 */
( function (blocks, editor, components, i18n, element ) {
	// Define common variables
	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var InnerBlocks = wp.editor.InnerBlocks;
	var BlockControls = wp.editor.BlockControls;
	var Dashicon = wp.components.Dashicon;
	var __ = wp.i18n.__;
	//var lang = wp.i18n;

	// Calendar Overview
	registerBlockType( 'abc-shortcodes/calendar-overview', {
		title: abcStrs.overview.title,
		description: '',
		icon: Dashicon.calendar,
		category: 'abc-shortcodes',
		edit: function() {
			return [
				el('div', { className: 'calendar-overview abc-shortcode' },
					el('h3', { className: 'abc-title' }, abcStrs.overview.title ),
					el('p', { className: 'abc-desc' }, abcStrs.overview.desc )
				)
			];
		},
		save: function(props) {
			return(
				el('div', { className: props.className }, '[abc-overview]' )
			);
		}
	} );

	// Single Calendar
	registerBlockType( 'abc-shortcodes/single-calendar', {
		title: abcStrs.single.title,
		description: '',
		icon: Dashicon.calendar,
		category: 'abc-shortcodes',
		attributes: {
			calendar: {
				type: 'string',
			},
			legend: {
				type: 'boolean',
				default: true
			}
		},
		edit: function( props ){
			return [
				el('div', { className: 'single-calendar abc-shortcode' },
					el('h3', { className: 'abc-title' }, abcStrs.single.title ),
					el('p', { className: 'abc-desc' }, abcStrs.single.desc ),

					el('div', { className: 'block-options' },
						el( components.SelectControl, {
							label: abcStrs.single.cal_lbl,
							options: $.merge( [{"label": abcStrs.single.sel_cal,"value":""}], abc_block_calendars ),
							value: props.attributes.calendar,
							onChange: function( calendarID ) {
								props.setAttributes({ calendar: calendarID })
							}
						} ),
						el( components.CheckboxControl, {
							label: abcStrs.single.legend_lbl,
							checked: props.attributes.legend,
							onChange: function( legendVal ) {
								props.setAttributes({ legend: legendVal })
							}
						} ),
					)
				),
			];
		},
		save: function( props ){
			var calendar = props.attributes.calendar;
			var lagend = "0";
			if( props.attributes.legend ) {
				lagend = "1";
			}
			return(
				el('div', { className: props.className }, "[abc-single calendar='"+props.attributes.calendar+"' legend='"+lagend+"']" )
			);
		}
	} );

	// Booking Form
	registerBlockType( 'abc-shortcodes/booking-form', {
		title: abcStrs.bookingform.title,
		description: '',
		icon: Dashicon.calendar,
		category: 'abc-shortcodes',
		attributes: {
			calendar: {
				type: 'string',
			},
			hide_other: {
				type: 'boolean',
				default: false
			},
			hide_tooshort: {
				type: 'boolean',
				default: false
			}
		},
		edit: function( props ){
			return [
				el('div', { className: 'booking-form abc-shortcode' },
					el('h3', { className: 'abc-title' }, abcStrs.bookingform.title ),
					el('p', { className: 'abc-desc' }, abcStrs.bookingform.desc ),

					el('div', { className: 'block-options' },
						el( components.SelectControl, {
							label: abcStrs.bookingform.cal_lbl,
							multiple: true,
							value: props.attributes.calendar,
							options: abc_block_calendars,
							onChange: function( calendarIDs ) {
								props.setAttributes({ calendar: calendarIDs.join() })
							}
						} ),
						el( components.CheckboxControl, {
							className: 'mb-cbcf-0',
							checked: props.attributes.hide_other,
							label: abcStrs.bookingform.hide_other_lbl,
							onChange: function( hide_otherVal ) {
								props.setAttributes({ hide_other: hide_otherVal })
							}
						} ),
						el( components.CheckboxControl, {
							label: abcStrs.bookingform.hide_tooshort_lbl,
							checked: props.attributes.hide_tooshort,
							onChange: function( tooShortVal ) {
								props.setAttributes({ hide_tooshort: tooShortVal })
							}
						} ),
					)
				),
			];
		},
		save: function( props ){
			var calendar = props.attributes.calendar;
			var hide_other = hide_tooshort = "0";
			if( props.attributes.hide_other ) {
				hide_other = "1";
			}
			if( props.attributes.hide_tooshort ) {
				hide_tooshort = "1";
			}

			return(
				el('div', { className: props.className }, "[abc-bookingform calendars='"+calendar+"' hide_other='"+hide_other+"' hide_tooshort='"+hide_tooshort+"']" )
			);
		}
	} );

} ) (
	window.wp.blocks,
	window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element
);