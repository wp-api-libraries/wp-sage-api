<?php
/**
 * Sage_Invoices
 *
 * @package WP-API-Libraries\WP-Sage-API
 */

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * Sage_Invoices.
	 */
class Sage_Invoices extends SageAPI {

	/**
	 * [__construct description]
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * [get_invoice_headers description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_headers( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceHeader', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_defaults description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_defaults( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceDefaults', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_history_links description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_history_links( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceHistoryLink', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_memos description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_memos( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceMemo', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_payments description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_payments( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoicePayment', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_trackings description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_trackings( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceTracking', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_tier_distributions description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_tier_distributions( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceTierDistribution', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_tax_summaries description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_tax_summaries( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceTaxSummary', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_tax_details description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_tax_details( $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceTaxDetail', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_header description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_header( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceHeader(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;

	}

	/**
	 * [get_invoice_detail description]
	 *
	 * @param  [type] $invoice_id  [description]
	 * @param  [type] $line_number [description]
	 * @param  array  $args        [description]
	 * @return [type]              [description]
	 */
	public function get_invoice_detail( $invoice_id, $line_number, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceDetail(' . $invoice_id . ',' . $line_number . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_details description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_details( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceDetail(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_payment description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_payment( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoicePayment(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_tier_distribution description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_tier_distribution( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceTierDistribution(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_tax_summary description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_tax_summary( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceTaxSummary(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_tax_detail description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_tax_detail( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceTaxDetail(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_tracking description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_tracking( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceTracking(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_history_link description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_history_link( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'SO_InvoiceDefaults', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/* INVOICES HISTORY. */

	/**
	 * [get_invoice_history_headers description]
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_history_headers( $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_InvoiceHistoryHeader', $args )->fetch( array( 'format' => $format ) );
		return $response;

	}

	/**
	 * Get Invoice History Details.
	 *
	 * @param  array $args [description]
	 * @return [type]       [description]
	 */
	public function get_invoice_history_details( $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_InvoiceHistoryDetail', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_single_invoice_history_detail description]
	 *
	 * @param  [type] $invoice_id  [description]
	 * @param  [type] $line_number [description]
	 * @param  array  $args        [description]
	 * @return [type]              [description]
	 */
	public function get_single_invoice_history_detail( $invoice_id, $line_number, $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_InvoiceHistoryDetail(' . $invoice_id . ',' . $line_number . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * [get_invoice_history_header description]
	 *
	 * @param  [type] $invoice_id [description]
	 * @param  array  $args       [description]
	 * @return [type]             [description]
	 */
	public function get_invoice_history_header( $invoice_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'AR_InvoiceHistoryHeader(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}


}
