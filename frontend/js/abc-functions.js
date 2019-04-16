 jQuery(function() {
	  var startdate;
	  var enddate;
	  var mindate;
	  var numberofmonths;
	  if (abc_functions_vars.startdate != undefined) {
		  startdate = new Date(abc_functions_vars.startdate);
		  enddate = startdate;
		  mindate = startdate;
		  numberofmonths = 1;
	  } else {
		  startdate = "+1d";
		  enddate = "+2d"; 
		  mindate = 0;
		  numberofmonths = 1;
	  }

	  if(jQuery(window).width() <= 600){
		  numberofmonths = 1;
	  } 
	jQuery( "#abc-from" ).datepicker({
      defaultDate: startdate,
      changeMonth: true,
	  changeYear: true,
	  minDate: mindate,
	  showCurrentAtPos: 0,
      numberOfMonths: numberofmonths,
	  dateFormat: abc_functions_vars.dateformat,
	  firstDay: abc_functions_vars.firstday,
      onClose: function( selectedDate ) {
    	  var date2 = jQuery('#abc-from').datepicker('getDate');
    	  if( !date2 ) return;
	      date2.setDate(date2.getDate()+1);
		  jQuery( "#abc-to" ).datepicker( "option", "minDate", date2 );
		  window.setTimeout(function(){
			  jQuery( "#abc-to" ).focus();
		  }, 0);
      }
    });
    jQuery( "#abc-to" ).datepicker({
      defaultDate: enddate,
      changeMonth: true,
	  changeYear: true,
	  minDate: mindate,
	  showCurrentAtPos: 0,
      numberOfMonths: numberofmonths,
	  dateFormat: abc_functions_vars.dateformat,
	  firstDay: abc_functions_vars.firstday,
      onClose: function( selectedDate ) {
        jQuery( "#abc-from" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
  });