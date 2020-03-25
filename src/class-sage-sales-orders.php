<?php
/**
 * Sage_Sales_Orders
 *
 * @package WP-API-Libraries\WP-Sage-API
 */

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * Sage_Sales_Orders.
	 */
class Sage_Sales_Orders extends SageAPI {

	/**
	 * [__construct description]
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get Sales Order History Headers.
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_sales_order_history_headers( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_SalesOrderHistoryHeader', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * Sales Order History Header by ID.
	 *
	 * @param  [type] $sales_order_id [description]
	 * @param  array  $args           [description]
	 * @return [type]                 [description]
	 */
	public function get_sales_order_history_header( $sales_order_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_SalesOrderHistoryHeader(' . $sales_order_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [delete_sales_order_history description]
	 *
	 * @param  [type] $sales_order_id [description]
	 * @param  array  $args           [description]
	 * @return [type]                 [description]
	 */
	public function delete_sales_order_history( $sales_order_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_SalesOrderHistoryHeader(' . $sales_order_id . ')', $args, 'DELETE' )->fetch( array( 'format' => $format ) );
		return $response;
	}



}
