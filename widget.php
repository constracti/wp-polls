<?php

if ( !defined( 'ABSPATH' ) )
	exit;

class KGR_Polls_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = [
			'classname' => 'kgr-polls-widget',
			'description' => 'A poll.',
		];
		parent::__construct( FALSE, 'KGR Polls Widget', $widget_ops );
	}

	function settings(): array {
		$settings = [];
		$settings['title'] = [
			'default' => '',
			'sanitize' => 'strval',
			'label' => 'title',
			'field' => function( string $id, string $name, string $value, string $label ) {
				echo '<p>' . "\n";
				echo sprintf( '<label for="%s">%s</label>', $id, esc_html( $label ) ) . "\n";
				echo sprintf( '<input class="widefat" id="%s" name="%s" type="text" value="%s" />', $id, $name, esc_attr( $value ) ) . "\n";
				echo '</p>' . "\n";
			},
		];
		$settings['poll_id'] = [
			'default' => 0,
			'sanitize' => 'intval',
			'label' => 'poll',
			'field' => function( string $id, string $name, int $value, string $label ) {
				echo '<p>' . "\n";
				echo sprintf( '<label for="%s">%s</label>', $id, esc_html( $label ) ) . "\n";
				echo sprintf( '<select class="widefat" id="%s" name="%s">', $id, $name ) . "\n";
				$option = get_option( KGR_POLLS_KEY, KGR_POLLS_VAL );
				var_dump( $option );
				echo sprintf( '<option value="%d"></option>', 0 ) . "\n";
				foreach ( $option['polls'] as $poll_id => $poll )
					echo sprintf( '<option value="%d"%s>%s</option>',
						$poll_id,
						selected( $poll_id, $value, FALSE ),
						esc_html( $poll['question'] )
					) . "\n";
				echo '</select>' . "\n";
				echo '</p>' . "\n";
			},
		];
		return $settings;
	}

	function instance( $instance = NULL ): array {
		$settings = $this->settings();
		if ( is_null( $instance ) || !is_array( $instance ) )
			$instance = [];
		foreach ( $settings as $key => $value )
			if ( !array_key_exists( $key, $instance ) )
				$instance[ $key ] = $value['default'];
		return $instance;
	}

	function title( array $args, array $instance ) {
		if ( $instance['title'] === '' )
			return;
		echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'] . "\n";
	}

	function content( array $instance ) {
		$poll_id = $instance['poll_id'];
		echo do_shortcode( sprintf( '[%s id="%d"]', KGR_POLLS_KEY, $poll_id ) );
	}

	function form( $instance ) {
		$instance = $this->instance( $instance );
		foreach ( $this->settings() as $key => $value ) {
			$id = $this->get_field_id( $key );
			$name = $this->get_field_name( $key );
			$value['field']( $id, $name, $instance[ $key ], $value['label'] );
		}
	}

	function update( $new_instance, $old_instance ): array {
		$instance = [];
		foreach ( $this->settings() as $key => $value )
			if ( array_key_exists( $key, $new_instance ) )
				$instance[ $key ] = $value['sanitize']( $new_instance[ $key ] );
			else
				$instance[ $key ] = $value['default'];
		return $instance;
	}

	function widget( $args, $instance ) {
		$instance = $this->instance( $instance );
		echo $args['before_widget'];
		$this->title( $args, $instance );
		$this->content( $instance );
		echo $args['after_widget'];
	}

}

add_action( 'widgets_init', function() {
	register_widget( 'KGR_Polls_Widget' );
} );
