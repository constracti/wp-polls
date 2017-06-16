<?php

/*
 * Plugin Name: KGR Polls
 * Plugin URI: https://github.com/constracti/wp-polls
 * Author: constracti
 * Version: 0.1
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

# TODO clear irrelevant votes

if ( !defined( 'ABSPATH' ) )
	exit;

define( 'KGR_POLLS_DIR', plugin_dir_path( __FILE__ ) );
define( 'KGR_POLLS_URL', plugin_dir_url( __FILE__ ) );
define( 'KGR_POLLS_KEY', 'kgr-polls' );
define( 'KGR_POLLS_VAL', [
	'polls' => [],
	'polls_id' => 0,
	'answers_id' => 0,
] );

require_once( KGR_POLLS_DIR . 'option.php' );
require_once( KGR_POLLS_DIR . 'settings.php' );
require_once( KGR_POLLS_DIR . 'shortcode.php' );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( array $links ): array {
	$links[] = sprintf( '<a href="%s">%s</a>', menu_page_url( KGR_POLLS_KEY, FALSE ), 'Settings' );
	return $links;
} );

function kgr_polls_results( int $poll_id, array $poll ): array {
	$results = [];
	foreach ( array_keys( $poll['answers'] ) as $answer_id )
		$results[ $answer_id ] = count( get_users( [
			'meta_key' => KGR_POLLS_KEY,
			'meta_value' => $answer_id,
			'fields' => 'ids',
		] ) );
	return $results;
}
