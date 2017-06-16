<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_shortcode( KGR_POLLS_KEY, function( array $atts ): string {
	$option = get_option( KGR_POLLS_KEY, KGR_POLLS_VAL );
	if ( !array_key_exists( 'id', $atts ) )
		return '';
	$id = intval( $atts['id'] );
	if ( !array_key_exists( $id, $option['polls'] ) )
		return '';
	$key = KGR_POLLS_KEY . '-' . $id;
	$poll = $option['polls'][ $id ];
	$user = get_current_user_id();
	if ( $user !== 0 )
		$metas = get_user_meta( $user, $key, FALSE );
	else
		$metas = [];
	$html = '';
	if ( $poll['open'] && $user !== 0 )
		$html .= sprintf( '<div class="kgr-polls" data-poll="%d" data-multi="%s" data-open="%s" data-nonce="%s" data-url="%s">',
			$id,
			$poll['multi'] ? 'on' : 'off',
			$poll['open'] ? 'on' : 'off',
			wp_create_nonce( $key ),
			admin_url( 'admin-ajax.php' )
		) . "\n";
	else
		$html .= sprintf( '<div class="kgr-polls" data-poll="%d">', $id ) . "\n";
	$html .= sprintf( '<h4>%s</h4>', esc_html( $poll['question'] ) ) . "\n";
	if ( !$poll['open'] ) {
		$results = kgr_polls_results( $id, $poll );
		$sum = array_sum( $results );
	}
	foreach ( $poll['answers'] as $answer => $text ) {
		$html .= sprintf( '<p data-answer="%d">', $answer ) . "\n";
		$html .= sprintf( '<label style="cursor: %s;">', $poll['open'] && $user !== 0 ? 'pointer' : 'not-allowed' ) . "\n";
		$html .= sprintf( '<input type="%s" value="%d"%s%s />',
			$poll['multi'] ? 'checkbox' : 'radio',
			$answer,
			checked( in_array( $answer, $metas ), TRUE, FALSE ),
			disabled( !$poll['open'] || $user === 0, TRUE, FALSE )
		) . "\n";
		$html .= sprintf( '<span>%s</span>', esc_html( $text ) ) . "\n";
		$html .= '</label>' . "\n";
		if ( !$poll['open'] && $sum > 0 )
			$html .= sprintf( '<progress class="kgr-polls-progress" value="%d" max="%d"></progress>', $results[ $answer ], $sum ) . "\n";
		$html .= '</p>' . "\n";
	}
	if ( $poll['open'] && $user === 0 )
		$html .= sprintf( '<p><a href="%s">%s</a></p>', wp_login_url(), esc_html__( 'Log in' ) ) . "\n";
	$html .= '</div>' . "\n";
	return $html;
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'kgr-polls-progress', KGR_POLLS_URL . 'progress.css', [], NULL );
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
	if ( !$poll['open'] )
		exit( 'open' );
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
