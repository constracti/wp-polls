<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_shortcode( KGR_POLLS_KEY, function( array $atts ): string {
	# TODO privileges
	$user = get_current_user_id();
	if ( $user === 0 )
		return '';
	$option = get_option( KGR_POLLS_KEY, KGR_POLLS_VAL );
	if ( !array_key_exists( 'id', $atts ) )
		return '';
	$id = intval( $atts['id'] );
	if ( !array_key_exists( $id, $option['polls'] ) )
		return '';
	$key = KGR_POLLS_KEY . '-' . $id;
	$poll = $option['polls'][ $id ];
	$metas = get_user_meta( $user, $key, FALSE );
	$html = '';
	$html .= sprintf( '<div class="kgr-polls" data-poll="%d" data-multi="%s" data-open="%s" data-nonce="%s" data-url="%s">',
		$id,
		$poll['multi'] ? 'on' : 'off',
		$poll['open'] ? 'on' : 'off',
		wp_create_nonce( $key ),
		admin_url( 'admin-ajax.php' )
	) . "\n";
	$html .= sprintf( '<h4>%s</h4>', esc_html( $poll['question'] ) ) . "\n";
	foreach ( $poll['answers'] as $answer => $text ) {
		$html .= sprintf( '<p data-answer="%d">', $answer ) . "\n";
		$html .= '<label style="cursor: pointer;">' . "\n";
		$html .= sprintf( '<input type="%s" value="%d"%s />',
			$poll['multi'] ? 'checkbox' : 'radio',
			$answer,
			checked( in_array( $answer, $metas ), TRUE, FALSE )
		) . "\n";
		$html .= sprintf( '<span>%s</span>', esc_html( $text ) ) . "\n";
		$html .= '</label>' . "\n";
		$html .= '</p>' . "\n";
	}
	$html .= '</div>' . "\n";
	return $html;
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'kgr-polls-shortcode', KGR_POLLS_URL . 'shortcode.js', [ 'jquery' ], NULL );
} );

add_action( 'wp_ajax_' . KGR_POLLS_KEY, function() {
	$user = get_current_user_id();
	if ( $user === 0 )
		exit( 'user' );
	$option = get_option( KGR_POLLS_KEY, KGR_POLLS_VAL );
	$poll = intval( $_POST['poll'] );
	if ( !array_key_exists( $poll, $option['polls'] ) )
		exit( 'poll' );
	$key = KGR_POLLS_KEY . '-' . $poll;
	$poll = $option['polls'][ $poll ];
	if ( !wp_verify_nonce( $_POST['nonce'], $key ) )
		exit( 'nonce' );
	if ( array_key_exists( 'answers', $_POST ) )
		$answers = array_map( 'intval', $_POST['answers'] );
	else
		$answers = [];
	$metas = get_user_meta( $user, $key, FALSE );
	foreach ( $metas as $meta )
		if ( !in_array( $meta, $answers ) )
			delete_user_meta( $user, $key, $meta );
	foreach ( $answers as $answer )
		if ( !in_array( $answer, $metas ) )
			add_user_meta( $user, $key, $answer, FALSE );
	exit;
} );
