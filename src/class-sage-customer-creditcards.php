<?php
/**
 * Sage_Customer_CreditCards
 *
 * @package WP-API-Libraries\WP-Sage-API
 */

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * Sage_Customer_CreditCards.
	 */
class Sage_Customer_CreditCards extends SageAPI {

	/**
	 * [__construct description]
	 */
	public function __construct( $base_uri, $username, $password, $company_code ) {
		parent::__construct( $base_uri, $username, $password, $company_code );
	}

	/**
	 * Get All Credit Cards.
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_customer_creditcards( $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_CustomerCreditCard', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * Get Credit Card.
	 *
	 * @param  [type] $customer_id [description]
	 * @param  array  $args        [description]
	 * @return [type]              [description]
	 */
	public function get_customer_creditcard( $customer_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_CustomerCreditCard(00;' . $customer_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

}
