<?php
/**
 * Dynamically loads the class attempting to be instantiated elsewhere in the
 * plugin.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

// https://dsgnwrks.pro/how-to/using-class-autoloaders-in-wordpress/
 // Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'sage_api_autoloader' ) ) {

	/**
	 * [sage_api_autoload description]
	 *
	 * @param  [type] $class_name [description]
	 * @return [type]             [description]
	 */
	function sage_api_autoloader( $class_name ) {
		// Verify its our project.
		if ( 0 !== strpos( $class_name, 'Sage_' ) ) {
			return;
		}

		$file = trailingslashit( dirname( __FILE__ ) ) .'src/class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
		
		if ( file_exists( $file ) ) {
			include_once $file;
		}
	
	}

	spl_autoload_register( 'sage_api_autoloader' );
}
