<?php
/**
 * Sage_Items
 *
 * @package WP-API-Libraries\WP-Sage-API
 */

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * Sage_Items.
	 */
class Sage_Items extends SageAPI {

	/**
	 * [__construct description]
	 */
	public function __construct() {
		parent::__construct('', '', '', '');
	}

	/**
	 * Get Items.
	 *
	 * @param  array $args Arguments.
	 * @return array API Response.
	 */
	public function get_items( $args = array(), $format = '' ) {
		$response = $this->build_request( 'CI_Item', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * Get Item.
	 *
	 * @param  string $item_id Item Id.
	 * @param  array  $args Arguments.
	 * @return array API Response.
	 */
	public function get_item( $item_id, $args = array(), $format = '' ) {
		$response = $this->build_request( 'CI_Item(' . $item_id . ')', $args )->fetch( array( 'format' => $format ) );
		return $response;
	}

	/**
	 * Create Item.
	 *
	 * @param  array $args Arguments.
	 * @return array API Response.
	 */
	public function create_item( $args = array(), $format = '' ) {

		$item_code          = $args['item_code'] ?? '';
		$standard_unit_cost = $args['v'] ?? '';
		$salesperson_no     = $args['salesperson_no'] ?? 'NG';
		$buyer_group        = $args['buyer_group'] ?? '';

		$data = '
			<entry xmlns:sdata="http://schemas.sage.com/sdata/2008/1"
					 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
					 xmlns="http://schemas.sage.com/sdata/http/2008/1">
			<id/>
			<title/>
			<content/>
			<sdata:payload>
					<CI_Item sdata:uri="https://' . sage_url() . '/sdata/MasApp/MasContract/' . sage_company_code() . '/CI_Item" xmlns="">
					<ItemCode>' . $item_code . '</ItemCode>
								<ItemType>1</ItemType>
								<ProductLine>ACCE</ProductLine>
								<StandardUnitCost>' . $standard_unit_cost . '</StandardUnitCost>
								<ImageFile>TriFold_web_800x800.jpg</ImageFile>
								<SuggestedRetailPrice>10.0000</SuggestedRetailPrice>
				</CI_Item>
				</sdata:payload>
			</entry>';

		$response = $this->build_request( 'CI_Item', $args, 'POST' )->fetch( array( 'format' => $format ) );

		return $response;
	}

}
