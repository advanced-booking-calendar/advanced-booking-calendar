jQuery( document ).ready( function($) {
	$( "table.abc-sortable tbody" ).sortable({
		handle: ".abc-dragit",
		placeholder: "ui-state-highlight"
	});
} );