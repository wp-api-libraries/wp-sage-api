<?php
/**
 * Sage_Items
 *
 * @package WP-API-Libraries\WP-Sage-API
 */

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * Sage Customers.
	 */
class Sage_Customers extends SageAPI {

	/**
	 * [__construct description]
	 */
	public function __construct( $base_uri, $username, $password, $company_code ) {
		parent::__construct( $base_uri, $username, $password, $company_code );
	}

	/**
	 * Get Customers.
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_customers( $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_Customer', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * Get Customer.
	 *
	 * @param  [type] $customer_id [description]
	 * @param  array  $args        [description]
	 * @return [type]              [description]
	 */
	public function get_customer( $customer_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_Customer(00;' . $customer_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 *  Delete Customer.
	 *
	 * @param  [type] $customer_id [description]
	 * @param  array  $args        [description]
	 * @return [type]              [description]
	 */
	public function delete_customer( $customer_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_Customer(00;' . $customer_id . ')', $args, 'DELETE' )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * Create Customer.
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function create_customer( $args = array() ) {

		$customer_no             = '00';
		$salesperson_division_no = '00';
		$customer_name           = $args['customer_name'] ?? '';
		$salesperson_no          = $args['salesperson_no'] ?? 'NG';
		$buyer_group             = $args['buyer_group'] ?? '';

		$data = '
			<entry xmlns:sdata="http://schemas.sage.com/sdata/2008/1"
					 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
					 xmlns="http://schemas.sage.com/sdata/http/2008/1">
			<id/>
			<title/>
			<content/>
			<sdata:payload>
				<AR_Customer sdata:uri="https://' . sage_url() . '/sdata/MasApp/MasContract/' . sage_company_code() . '/AR_Customer" xmlns="">
					<ARDivisionNo>00</ARDivisionNo>
					<CustomerNo>' . $customer_no . '</CustomerNo>
					<CustomerName>' . $customer_name . '</CustomerName>
					<SalespersonDivisionNo>' . $salesperson_division_no . '</SalespersonDivisionNo>
					<SalespersonNo>' . $salesperson_no . '</SalespersonNo>
					<UDF_BUYER_GROUP>' . $buyer_group . '</UDF_BUYER_GROUP>
				</AR_Customer>
				</sdata:payload>
			</entry>';

		$response = $this->build_request( 'AR_Customer', $args, 'POST' )->fetch();

		return $response;
	}

}
