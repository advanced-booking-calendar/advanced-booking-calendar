jQuery(function() {
	var startdate;
	var enddate;
	var mindate;
	var numberofmonths;
	startdate = "+1d";
	enddate = "+2d"; 
	mindate = 0;
	numberofmonths = 1;

	jQuery( "#start" ).datepicker({
		defaultDate: startdate,
		changeMonth: true,
		changeYear: true,
		showCurrentAtPos: 0,
		numberOfMonths: numberofmonths,
		dateFormat: abc_functions_vars.dateformat,
		firstDay: abc_functions_vars.firstday,
		onClose: function( selectedDate ) {
			jQuery( "#end" ).datepicker( "option", "minDate", selectedDate );
			window.setTimeout(function(){
				jQuery( "#end" ).focus();
			}, 0);
		}
	});
	jQuery( "#end" ).datepicker({
		defaultDate: enddate,
		changeMonth: true,
		changeYear: true,
		minDate: mindate,
		showCurrentAtPos: 0,
		numberOfMonths: numberofmonths,
		dateFormat: abc_functions_vars.dateformat,
		firstDay: abc_functions_vars.firstday,
		onClose: function( selectedDate ) {
			jQuery( "#start" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
});