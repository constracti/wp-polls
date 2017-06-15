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
# TODO delete options

if ( !defined( 'ABSPATH' ) )
	exit;

define( 'KGR_POLLS_DIR', plugin_dir_path( __FILE__ ) );
define( 'KGR_POLLS_URL', plugin_dir_url( __FILE__ ) );
define( 'KGR_POLLS_KEY', 'kgr-polls' );
define( 'KGR_POLLS_VAL', [
	'auto' => 0,
	'polls' => [],
] );

require_once( KGR_POLLS_DIR . 'settings.php' );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( array $links ): array {
	$links[] = sprintf( '<a href="%s">%s</a>', menu_page_url( KGR_POLLS_KEY, FALSE ), 'Settings' );
	return $links;
} );
