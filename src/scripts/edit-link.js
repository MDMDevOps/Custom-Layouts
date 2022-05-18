jQuery( function( $ ) {
	'use strict';
	$('a.custom-layout-edit').on( 'click', ( event ) => {
		window.open(event.currentTarget.href);
	} );
});