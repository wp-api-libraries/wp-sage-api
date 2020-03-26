<?php
/**
 * Sage_Customer_Contacts
 *
 * @package WP-API-Libraries\WP-Sage-API
 */

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * Sage_Customer_Contacts.
	 */
class Sage_Customer_Contacts extends SageAPI {

	/**
	 * [__construct description]
	 */
	public function __construct( $base_uri, $username, $password, $company_code ) {
		parent::__construct( $base_uri, $username, $password, $company_code );
	}

	/**
	 * Get Customer Contacts.
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_customer_contacts( $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_CustomerContact', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * Get Customer Contact.
	 *
	 * @param  [type] $customer_id         [description]
	 * @param  [type] $customer_contact_id [description]
	 * @param  array  $args                [description]
	 * @return [type]                      [description]
	 */
	public function get_customer_contact( $customer_id, $customer_contact_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_CustomerContact(00;' . $customer_id . ';' . $customer_contact_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * Delete Customer Contact.
	 *
	 * @param  [type] $customer_id         [description]
	 * @param  [type] $customer_contact_id [description]
	 * @param  array  $args                [description]
	 * @return [type]                      [description]
	 */
	public function delete_customer_contact( $customer_id, $customer_contact_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_CustomerContact(00;' . $customer_id . ';' . $customer_contact_id . ')', $args, 'DELETE' )->fetch( array( 'format' => $format ) );
		return $response;
	}


}
