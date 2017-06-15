<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_action( 'admin_init', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$group = KGR_POLLS_KEY;
	$section = KGR_POLLS_KEY;
	add_settings_section( $section, 'polls', '__return_null', $group );
	register_setting( $group, KGR_POLLS_KEY, function( $input ): array {
		if ( !array_key_exists( 'sanitize', $input ) )
			return $input;
		$option = get_option( KGR_POLLS_KEY, KGR_POLLS_VAL );
		$auto = $option['auto'];
		$polls = [];
		$question = 0;
		$answers = 0;
		$multi = 0;
		$open = 0;
		while ( $input['question'][ $question ] !== '' ) {
			$poll = [];
			if ( intval( $input['id'][ $question ] ) === 0 ) {
				$auto++;
				$id = $auto;
			} else {
				$id = intval( $input['id'][ $question ] );
			}
			$poll['question'] = $input['question'][ $question ];
			$question++;
			$poll['answers'] = [];
			while ( $input['answers'][ $answers ] !== '' ) {
				$poll['answers'][] = $input['answers'][ $answers ];
				$answers++;
			}
			$answers++;
			$poll['multi'] = FALSE;
			$multi++;
			if ( $input['multi'][ $multi ] === 'on' ) {
				$poll['multi'] = TRUE;
				$multi++;
			}
			$poll['open'] = FALSE;
			$open++;
			if ( $input['open'][ $open ] === 'on' ) {
				$poll['open'] = TRUE;
				$open++;
			}
			$polls[ $id ] = $poll;
		}
		return [
			'auto' => $auto,
			'polls' => $polls,
		];
	} );
} );

add_action( 'admin_menu', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$page_title = 'KGR Polls';
	$menu_title = 'KGR Polls';
	$menu_slug = KGR_POLLS_KEY;
	$function = 'kgr_polls_settings';
	add_submenu_page( 'options-general.php', $page_title, $menu_title, 'administrator', $menu_slug, $function );
} );

function kgr_polls_settings() {
	if ( !current_user_can( 'administrator' ) )
		return;
	/*
	if ( array_key_exists( 'action', $_GET ) ) {
		switch ( $_GET['action'] ) {
			case KGR_POLLS_KEY . '-delete':
				if ( wp_verify_nonce( $_GET['nonce'], $_GET['action'] ) )
					delete_option( KGR_POLLS_KEY );
				break;
		}
	}
	*/
	$option = get_option( KGR_POLLS_KEY, KGR_POLLS_VAL );
	$polls = $option['polls'];
	echo '<div class="wrap">' . "\n";
	echo sprintf( '<h1>%s</h1>', 'KGR Polls' ) . "\n";
	kgr_polls_settings_notice( 'info', 'info', 'Do not leave empty text fields.' );
	echo '<form method="post" action="options.php" class="kgr-polls-control-container">' . "\n";
	settings_fields( KGR_POLLS_KEY );
	do_settings_sections( KGR_POLLS_KEY );
	echo sprintf( '<input type="hidden" name="%s[%s]" value="on" />', esc_attr( KGR_POLLS_KEY ), esc_attr( 'sanitize' ) ) . "\n";
	echo '<table class="wp-list-table widefat fixed striped">' . "\n";
	echo '<thead>' . "\n";
	kgr_polls_settings_head();
	echo '</thead>' . "\n";
	echo '<tbody class="kgr-polls-control-items">' . "\n";
	foreach ( $polls as $id => $poll )
		kgr_polls_settings_poll( $id, $poll );
	echo '</tbody>' . "\n";
	echo '<tfoot>' . "\n";
	kgr_polls_settings_head();
	echo '</tfoot>' . "\n";
	echo '</table>' . "\n";
	echo '<table class="kgr-polls-control-item0" style="display: none;">' . "\n";
	echo '<tbody>' . "\n";
	kgr_polls_settings_poll();
	echo '</tbody>' . "\n";
	echo '</table>' . "\n";
	echo '<p class="submit">' . "\n";
	submit_button( 'save', 'primary', 'submit', FALSE );
	echo sprintf( '<button type="button" class="button kgr-polls-control-add" style="float: right;">%s</button>', 'add' ) . "\n";
	echo '</p>' . "\n";
	echo '</form>' . "\n";
	/*
	echo sprintf( '<h2>%s</h2>', 'uninstall' ) . "\n";
	$action = KGR_POLLS_KEY . '-delete';
	echo sprintf( '<a href="%s&action=%s&nonce=%s" class="button" onclick="return confirm( this.innerHTML );">%s</a>',
		menu_page_url( KGR_POLLS_KEY, FALSE ),
		$action,
		wp_create_nonce( $action ),
		esc_html( 'delete option' )
	) . "\n";
	*/
	echo '</div>' . "\n";
}

function kgr_polls_settings_notice( string $class, string $dashicon, string $message ) {
	echo sprintf( '<div class="notice notice-%s">', $class ) . "\n";
	echo sprintf( '<p class="dashicons-before dashicons-%s">%s</p>', $dashicon, esc_html( $message ) ) . "\n";
	echo '</div>' . "\n";
}

function kgr_polls_settings_head() {
	echo '<tr>' . "\n";
	echo sprintf( '<th class="column-primary">%s</th>', esc_html( 'question' ) ) . "\n";
	echo sprintf( '<th>%s</th>', esc_html( 'answers' ) ) . "\n";
	echo sprintf( '<th style="width: 10%%;">%s</th>', esc_html( 'multi' ) ) . "\n";
	echo sprintf( '<th style="width: 10%%;">%s</th>', esc_html( 'open' ) ) . "\n";
	echo sprintf( '<th>%s</th>', esc_html( 'actions' ) ) . "\n";
	echo '</tr>' . "\n";
}

function kgr_polls_settings_poll( int $id = 0, array $poll = [] ) {
	if ( $poll === [] )
		$poll = [
			'question' => '',
			'answers' => [],
			'multi' => FALSE,
			'open' => FALSE,
		];
	echo '<tr class="kgr-polls-control-item">' . "\n";
	echo sprintf( '<td class="column-primary" data-colname="%s">', esc_html( 'question' ) ) . "\n";
	echo sprintf( '<input type="hidden" name="%s[%s][]" value="%d" />', esc_attr( KGR_POLLS_KEY ), esc_attr( 'id' ), $id ) . "\n";
	echo sprintf( '<input type="text" name="%s[%s][]" value="%s" placeholder="%s" autocomplete="off" />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'question' ),
		esc_attr( $poll['question'] ),
		esc_attr( 'question' )
	) . "\n";
	if ( $id !== 0 ) {
		echo '<label style="display: block;">' . "\n";
		echo sprintf( '<div>%s</div>', 'shortcode' ) . "\n";
		$shortcode = sprintf( '[%s id="%d"]', KGR_POLLS_KEY, $id );
		echo sprintf( '<input type="text" onfocus="this.select();" readonly="readonly" value="%s" />', esc_attr( $shortcode ) ) . "\n";
		echo '</label>' . "\n";
	}
	echo '<button type="button" class="toggle-row"></button>' . "\n";
	echo '</td>' . "\n";
	echo sprintf( '<td class="kgr-polls-control-container" data-colname="%s">', esc_html( 'answers' ) ) . "\n";
	echo '<div class="kgr-polls-control-items">' . "\n";
	foreach ( $poll['answers'] as $answer )
		kgr_polls_settings_poll_answer( $answer );
	echo '</div>' . "\n";
	echo '<div class="kgr-polls-control-item0" style="display: none;">' . "\n";
	kgr_polls_settings_poll_answer();
	echo '</div>' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<button type="button" class="button kgr-polls-control-add" style="float: right;">%s</button>', esc_html( 'add' ) ) . "\n";
	echo '</div>' . "\n";
	echo '</td>' . "\n";
	echo sprintf( '<td data-colname="%s" style="width: 10%%;">', esc_html( 'multi' ) ) . "\n";
	echo sprintf( '<input type="hidden" name="%s[%s][]" value="off" />', esc_attr( KGR_POLLS_KEY ), esc_attr( 'multi' ) ) . "\n";
	echo sprintf( '<input type="checkbox" name="%s[%s][]" value="on"%s />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'multi' ),
		checked( $poll['multi'], TRUE, FALSE )
	) . "\n";
	echo '</td>' . "\n";
	echo sprintf( '<td data-colname="%s" style="width: 10%%;">', esc_html( 'open' ) ) . "\n";
	echo sprintf( '<input type="hidden" name="%s[%s][]" value="off" />', esc_attr( KGR_POLLS_KEY ), esc_attr( 'open' ) ) . "\n";
	echo sprintf( '<input type="checkbox" name="%s[%s][]" value="on"%s />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'open' ),
		checked( $poll['open'], TRUE, FALSE )
	) . "\n";
	echo '</td>' . "\n";
	echo sprintf( '<td data-colname="%s">', esc_html( 'actions' ) ) . "\n";
	echo sprintf( '<button type="button" class="button kgr-polls-control-up">%s</button>', esc_html( 'up' ) ) . "\n";
	echo sprintf( '<button type="button" class="button kgr-polls-control-down">%s</button>', esc_html( 'down' ) ) . "\n";
	echo sprintf( '<button type="button" class="button kgr-polls-control-delete">%s</button>', esc_html( 'delete' ) ) . "\n";
	echo '</td>' . "\n";
	echo '</tr>' . "\n";
}

function kgr_polls_settings_poll_answer( string $answer = '' ) {
	echo '<div class="kgr-polls-control-item" style="margin-bottom: 10px;">' . "\n";
	echo sprintf( '<input type="text" name="%s[%s][]" value="%s" placeholder="%s" autocomplete="off" />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'answers' ),
		esc_attr( $answer ),
		esc_attr( 'answer' )
	) . "\n";
	echo '<span style="display: inline-block;">' . "\n";
	echo sprintf( '<button type="button" class="button kgr-polls-control-up">%s</button>', esc_html( 'up' ) ) . "\n";
	echo sprintf( '<button type="button" class="button kgr-polls-control-down">%s</button>', esc_html( 'down' ) ) . "\n";
	echo sprintf( '<button type="button" class="button kgr-polls-control-delete">%s</button>', esc_html( 'delete' ) ) . "\n";
	echo '</span>' . "\n";
	echo '</div>' . "\n";
}

add_action( 'admin_enqueue_scripts', function( string $hook ) {
	if ( $hook !== sprintf( 'settings_page_%s', KGR_POLLS_KEY ) )
		return;
	wp_enqueue_script( 'kgr-polls-control', KGR_POLLS_URL . 'control.js', [ 'jquery' ], NULL );
} );
