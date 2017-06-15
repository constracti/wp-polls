jQuery( document ).on( 'click', '.kgr-polls label', function() {
	var label = jQuery( this );
	var poll = label.parents( '.kgr-polls' );
	if ( poll.data( 'busy' ) === 'on' )
		return false;
	poll.data( 'busy', 'on' );
	var input = label.find( 'input' );
	var inputs = poll.find( 'input' );
	if ( input.prop( 'checked' ) ) {
		input.prop( 'checked', false );
	} else if ( poll.data( 'multi' ) === 'on' ) {
		input.prop( 'checked', true );
	} else {
		inputs.prop( 'checked', false );
		input.prop( 'checked', true );
	}
	label.css( 'font-weight', 'bold' );
	var data = {
		action: 'kgr-polls',
		poll: poll.data( 'poll' ),
		nonce: poll.data( 'nonce' ),
		answers: [],
	};
	inputs.each( function() {
		var input = jQuery( this );
		if ( input.prop( 'checked' ) )
			data.answers.push( input.val() );
	} );
	jQuery.post( poll.data( 'url' ), data ).always( function() {
		label.css( 'font-weight', 'initial' );
		poll.data( 'busy', 'off' );
	} );
	return false;
} );
