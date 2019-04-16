(function() {
    tinymce.PluginManager.add('abc_booking_button', function( editor, url ) {
        editor.addButton( 'abc_booking_button', {
            title: 'Advanced Booking Calendar',
            type: 'menubutton',
            icon: 'icon dashicons-calendar-alt',
            menu: [
                {
                    text: editor.getLang('advanced-booking-calendar.bookingform'),
                    value: '[abc-bookingform]',
                    onclick:  function() {
						editor.windowManager.open( {
							title: editor.getLang('advanced-booking-calendar.bookingform'),
							width: 600,
							height: 130,
							body: [
								{
									type: 'checkbox',
									name: 'hideotherBox',
								    checked: false,
								    text: editor.getLang('advanced-booking-calendar.hideother')
								},
								{
									type: 'checkbox',
									name: 'hidetooshortBox',
								    checked: false,
								    text: editor.getLang('advanced-booking-calendar.hidetooshort')
								},
								{
									type: 'textbox',
									name: 'bookingform_calendars',
									tooltip: editor.getLang('advanced-booking-calendar.listofcalendars'),
									label: editor.getLang('advanced-booking-calendar.calendars')
								}
							],
							onsubmit: function( e ) {
								var hideother = "";
								if(e.data.hideotherBox){
									hideother = ' hide_other="1"';
								}
								var hidetooshort = "";
								if(e.data.hidetooshortBox){
									hidetooshort = ' hide_tooshort="1"';
								}
								var calendars = "";
								if(e.data.bookingform_calendars){
									calendars = ' calendars="'+e.data.bookingform_calendars+'"';
								}
								editor.insertContent( '[abc-bookingform'+ hideother + hidetooshort + calendars +']');
							}
						});
					}
				},
                {
                    text: editor.getLang('advanced-booking-calendar.calendaroverview'),
                    value: '[abc-overview]',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                },
                {
                    text: editor.getLang('advanced-booking-calendar.singlecalendar'),
                    value: '[abc-overview]',
                    onclick:  function() {
						editor.windowManager.open( {
							title: editor.getLang('advanced-booking-calendar.addsingle'),
							width: 400,
							height: 100,
							body: [
								{
									type: 'listbox',
									name: 'calendar',
									label: editor.getLang('advanced-booking-calendar.calendar'),
									'values': abc_tinymce_calendars
								},
								{
									type: 'checkbox',
									name: 'legendBox',
								    checked: true,
								    text: editor.getLang('advanced-booking-calendar.legend')	
								}
							],
							onsubmit: function( e ) {
								var legend = "";
								if(e.data.legendBox){
									legend = ' legend="1"';
								}
								editor.insertContent( '[abc-single calendar="' + e.data.calendar + '"'+ legend + ']');
							}
						});
					}
				}	
           ]
        });
    });
})();