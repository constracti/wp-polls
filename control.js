function kgr_polls_control_parent( object, selector ) {
	object = object.parent();
	while ( object.length > 0 && !object.is( selector ) )
		object = object.parent();
	return object;
}

function kgr_polls_control_children( object, selector ) {
	children = jQuery();
	while ( object.length > 0 ) {
		object = object.children().not( '.kgr-polls-control-container' );
		children = children.add( object.filter( selector ) );
		object = object.not( selector );
	}
	return children;
}

jQuery( document ).on( 'click', '.kgr-polls-control-add', function() {
	var container = kgr_polls_control_parent( jQuery( this ), '.kgr-polls-control-container' );
	var items = kgr_polls_control_children( container, '.kgr-polls-control-items' );
	var item0 = kgr_polls_control_children( container, '.kgr-polls-control-item0' );
	var item = kgr_polls_control_children( item0, '.kgr-polls-control-item' );
	item.clone().appendTo( items );
} );

jQuery( document ).on( 'click', '.kgr-polls-control-up', function() {
	var item = kgr_polls_control_parent( jQuery( this ), '.kgr-polls-control-item' );
	var target = item.prev();
	if ( target.length === 0 )
		return;
	item.detach().insertBefore( target );
} );

jQuery( document ).on( 'click', '.kgr-polls-control-down', function() {
	var item = kgr_polls_control_parent( jQuery( this ), '.kgr-polls-control-item' );
	var target = item.next();
	if ( target.length === 0 )
		return;
	item.detach().insertAfter( target );
} );

jQuery( document ).on( 'click', '.kgr-polls-control-delete', function() {
	var item = kgr_polls_control_parent( jQuery( this ), '.kgr-polls-control-item' );
	item.remove();
} );
