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

# TODO wrong
function kgr_polls_results( int $poll_id, array $poll ): array {
	$results = array_fill_keys( array_keys( $poll['answers'] ), 0 );
	$key = KGR_POLLS_KEY . '-' . $poll_id;
	$users = get_users( [
		'meta_key' => $key,
		'fields' => 'ids',
	] );
	$users = array_map( 'intval', $users );
	foreach ( $users as $user ) {
		$metas = get_user_meta( $user, $key, FALSE );
		$metas = array_map( 'intval', $metas );
		foreach ( $metas as $meta )
			if ( array_key_exists( $meta, $results ) )
				$results[ $meta ]++;
	}
	return $results;
}
