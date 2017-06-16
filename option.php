<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_action( 'admin_init', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$group = KGR_POLLS_KEY;
	$section = KGR_POLLS_KEY;
	add_settings_section( $section, 'polls', function() {
		$option = get_option( KGR_POLLS_KEY, KGR_POLLS_VAL );
		// sanitize
		echo sprintf( '<input type="hidden" name="%s[%s]" value="on" />',
			esc_attr( KGR_POLLS_KEY ),
			esc_attr( 'sanitize' )
		) . "\n";
		// polls
		echo '<table class="wp-list-table widefat fixed striped">' . "\n";
		echo '<thead>' . "\n";
		kgr_polls_option_head();
		echo '</thead>' . "\n";
		echo '<tbody class="kgr-polls-control-items">' . "\n";
		foreach ( $option['polls'] as $poll_id => $poll )
			kgr_polls_option_poll( $poll_id, $poll );
		echo '</tbody>' . "\n";
		echo '<tfoot>' . "\n";
		kgr_polls_option_head();
		echo '</tfoot>' . "\n";
		echo '</table>' . "\n";
		echo '<table class="kgr-polls-control-item0" style="display: none;">' . "\n";
		echo '<tbody>' . "\n";
		kgr_polls_option_poll();
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		// polls_id
		echo sprintf( '<input type="hidden" name="%s[%s]" value="%d" />',
			esc_attr( KGR_POLLS_KEY ),
			esc_attr( 'polls_id' ),
			$option['polls_id']
		) . "\n";
		// answers_id
		echo sprintf( '<input type="hidden" name="%s[%s]" value="%d" />',
			esc_attr( KGR_POLLS_KEY ),
			esc_attr( 'answers_id' ),
			$option['answers_id']
		) . "\n";
	}, $group );
	register_setting( $group, KGR_POLLS_KEY, function( $input ): array {
		if ( !array_key_exists( 'sanitize', $input ) )
			return $input;
		$poll_cnt = 0;
		$answer_cnt = 0;
		$multi_cnt = 0;
		$open_cnt = 0;
		$option = [];
		$option['polls'] = [];
		$option['polls_id'] = intval( $input['polls_id'] );
		$option['answers_id'] = intval( $input['answers_id'] );
		while ( $input['question'][ $poll_cnt ] !== '' ) {
			$poll = [];
			$poll_id = intval( $input['poll_id'][ $poll_cnt ] );
			if ( $poll_id === 0 ) {
				$option['polls_id']++;
				$poll_id = $option['polls_id'];
			}
			$poll['question'] = $input['question'][ $poll_cnt ];
			$poll['answers'] = [];
			while ( $input['answers'][ $answer_cnt ] !== '' ) {
				$answer_id = intval( $input['answer_id'][ $answer_cnt ] );
				if ( $answer_id === 0 ) {
					$option['answers_id']++;
					$answer_id = $option['answers_id'];
				}
				$poll['answers'][ $answer_id ] = $input['answers'][ $answer_cnt ];
				$answer_cnt++;
			}
			$answer_cnt++;
			$poll['multi'] = FALSE;
			$multi_cnt++;
			if ( $input['multi'][ $multi_cnt ] === 'on' ) {
				$poll['multi'] = TRUE;
				$multi_cnt++;
			}
			$poll['open'] = FALSE;
			$open_cnt++;
			if ( $input['open'][ $open_cnt ] === 'on' ) {
				$poll['open'] = TRUE;
				$open_cnt++;
			}
			$option['polls'][ $poll_id ] = $poll;
			$poll_cnt++;
		}
		return $option;
	} );
} );

function kgr_polls_option_head() {
	echo '<tr>' . "\n";
	echo sprintf( '<th class="column-primary">%s</th>', esc_html( 'question' ) ) . "\n";
	echo sprintf( '<th>%s</th>', esc_html( 'answers' ) ) . "\n";
	echo sprintf( '<th style="width: 10%%;">%s</th>', esc_html( 'multi' ) ) . "\n";
	echo sprintf( '<th style="width: 10%%;">%s</th>', esc_html( 'open' ) ) . "\n";
	echo sprintf( '<th>%s</th>', esc_html( 'actions' ) ) . "\n";
	echo '</tr>' . "\n";
}

function kgr_polls_option_poll( int $poll_id = 0, array $poll = [] ) {
	if ( $poll_id === 0 )
		$poll = [
			'question' => '',
			'answers' => [],
			'answers_ai' => 0,
			'multi' => FALSE,
			'open' => FALSE,
		];
	echo '<tr class="kgr-polls-control-item">' . "\n";
	echo sprintf( '<td class="column-primary" data-colname="%s">', esc_html( 'question' ) ) . "\n";
	// poll_id
	echo sprintf( '<input type="hidden" name="%s[%s][]" value="%d" />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'poll_id' ),
		$poll_id
	) . "\n";
	// question
	echo sprintf( '<input type="text" name="%s[%s][]" value="%s" placeholder="%s" autocomplete="off" />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'question' ),
		esc_attr( $poll['question'] ),
		esc_attr( 'question' )
	) . "\n";
	// shortcode
	if ( $poll_id !== 0 ) {
		echo '<label style="display: block;">' . "\n";
		echo sprintf( '<div>%s</div>', 'shortcode' ) . "\n";
		echo sprintf( '<input type="text" onfocus="this.select();" readonly="readonly" value="%s" />',
			esc_attr( sprintf( '[%s id="%d"]', KGR_POLLS_KEY, $poll_id ) )
		) . "\n";
		echo '</label>' . "\n";
	}
	echo '<button type="button" class="toggle-row"></button>' . "\n";
	echo '</td>' . "\n";
	echo sprintf( '<td class="kgr-polls-control-container" data-colname="%s">', esc_html( 'answers' ) ) . "\n";
	// answers
	echo '<div class="kgr-polls-control-items">' . "\n";
	$results = kgr_polls_results( $poll_id, $poll );
	$sum = array_sum( $results );
	foreach ( $poll['answers'] as $answer_id => $answer )
		kgr_polls_option_poll_answer( $answer_id, $answer, $results[ $answer_id ], $sum );
	echo '</div>' . "\n";
	echo '<div class="kgr-polls-control-item0" style="display: none;">' . "\n";
	kgr_polls_option_poll_answer();
	echo '</div>' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<button type="button" class="button kgr-polls-control-add" style="float: right;">%s</button>', esc_html( 'add' ) ) . "\n";
	echo '</div>' . "\n";
	echo '</td>' . "\n";
	echo sprintf( '<td data-colname="%s" style="width: 10%%;">', esc_html( 'multi' ) ) . "\n";
	// multi
	echo sprintf( '<input type="hidden" name="%s[%s][]" value="off" />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'multi' )
	) . "\n";
	echo sprintf( '<input type="checkbox" name="%s[%s][]" value="on"%s />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'multi' ),
		checked( $poll['multi'], TRUE, FALSE )
	) . "\n";
	echo '</td>' . "\n";
	echo sprintf( '<td data-colname="%s" style="width: 10%%;">', esc_html( 'open' ) ) . "\n";
	// open
	echo sprintf( '<input type="hidden" name="%s[%s][]" value="off" />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'open' )
	) . "\n";
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

function kgr_polls_option_poll_answer( int $answer_id = 0, string $answer = '', int $result = 0, int $sum = 0 ) {
	echo '<div class="kgr-polls-control-item" style="margin-bottom: 10px;">' . "\n";
	// answer_id
	echo sprintf( '<input type="hidden" name="%s[%s][]" value="%d" />',
		esc_attr( KGR_POLLS_KEY ),
		esc_attr( 'answer_id' ),
		$answer_id
	) . "\n";
	// answer
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
	if ( $answer_id !== 0 )
		echo sprintf( '<progress class="kgr-polls-progress" value="%d" max="%d"></progress>', $result, $sum ) . "\n";
	echo '</div>' . "\n";
}
