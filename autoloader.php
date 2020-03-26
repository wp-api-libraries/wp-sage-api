<?php
/**
 * Dynamically loads the class attempting to be instantiated elsewhere in the
 * plugin.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'sage_api_autoloader' ) ) {

	/**
	 * Sage API Autoloader.
	 *
	 * @param  [type] $class_name Class Name.
	 */
	function sage_api_autoloader( $class_name ) {
		// Verify its our project.
		if ( 0 !== strpos( $class_name, 'Sage_' ) ) {
			return;
		}

		$file = trailingslashit( dirname( __FILE__ ) ) . 'src/class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

		if ( file_exists( $file ) ) {
			include_once $file;
		}

	}

	spl_autoload_register( 'sage_api_autoloader' );
}
