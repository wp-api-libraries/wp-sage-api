<?php
/**
 * WP-Sage-API
 *
 * @package WP-Sage-API
 */

/*
* Plugin Name: WP Sage API
* Plugin URI: https://github.com/wp-api-libraries/wp-sage-api
* Description: Perform API requests to Sage 100 in WordPress.
* Author: WP API Libraries
* Version: 1.0.0
* Author URI: https://wp-api-libraries.com
* GitHub Plugin URI: https://github.com/wp-api-libraries/wp-sage-api
* GitHub Branch: master
*/
/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Check if class exists. */
if ( ! class_exists( 'SageAPI' ) ) {

	/**
	 * Sage API Class.
	 */
	class SageAPI {

		/**
		 * Route being called.
		 *
		 * @var string
		 */
		protected $route = '';

		/**
		 * Base API URL.
		 *
		 * @var string
		 */
		private static $base_uri;

		/**
		 * API Username.
		 *
		 * @var string
		 */
		private static $username;

		/**
		 * API Password.
		 *
		 * @var string
		 */
		private static $password;

		/**
		 * Company Code.
		 *
		 * @var string
		 */
		private static $company_code;

		/**
		 * Links for pagination
		 *
		 * @var string
		 */
		public $links;

		/**
		 * Initialize the API object
		 *
		 * @since 0.1
		 * @param string $endpoint the endpoint URL
		 * @param string $username connection username
		 * @param string $password connection password
		 * @param string $company_code identifies the company
		 */
		public function __construct( $base_uri, $username, $password, $company_code ) {

			static::$username     = $username;
			static::$password     = $password;
			static::$company_code = $company_code;

			static::$base_uri = $base_uri . '/' . $company_code . '/';

		}


		 /*
		 * Prepares API request.
		 *
		 * @param  string $route   API route to make the call to.
		 * @param  array  $args    Arguments to pass into the API call.
		 * @param  array  $method  HTTP Method to use for request.
		 * @return self            Returns an instance of itself so it can be chained to the fetch method.
		 */
		protected function build_request( string $route, array $args = array(), string $method = 'GET' ) {

			// Headers get added first.
			$this->set_headers();
			// Add Method and Route.
			$this->args['method']    = $method;
			$this->args['sslverify'] = true;
			$this->route             = $route;

			if ( 'GET' === $method ) {
				$this->route = add_query_arg( array_filter( $args ), $route );
			} else {
				$this->args['body'] = $args;
			}

			return $this;
		}
		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @return array|WP_Error Request results or WP_Error on request failure.
		 */
		protected function fetch( $args = array() ) {

			$format = $args['format'] ?? '';

			// Make the request.
			$response = wp_remote_request( static::$base_uri . $this->route, $this->args ) ?? '';

			// Make sure Response is not Error.
			if ( is_wp_error( $response ) ) {
					return $response->get_error_messages();
			}

			// Retrieve Status code & body.
			$code = wp_remote_retrieve_response_code( $response ) ?? '';

			// Return WP_Error if request is not successful.
			if ( ! $this->is_status_ok( $code ) ) {
				return new WP_Error( 'response-error', sprintf( __( 'Status: %d', 'wp-sage-api' ), $code ) );
			} else {

				// Get Response Body.
				$body = wp_remote_retrieve_body( $response ) ?? '';

				if ( ! is_wp_error( $body ) && ! empty( $body ) ) {

					if ( $format === 'json' || empty( $format ) ) {

						$parsed_xml = $this->xmlstr_to_array( $body ) ?? array();

						$results = array(
							'results'          => $this->get_payload( $parsed_xml ),
							'total_results'    => $this->get_total_results( $parsed_xml ),
							'start_index'      => $this->get_start_index( $parsed_xml ),
							'items_per_page'   => $this->get_items_per_page( $parsed_xml ),
							'has_more'         => $this->get_has_more( $parsed_xml ),
							'next_start_index' => $this->get_next_start_index( $parsed_xml ),
							'next_count'       => $this->get_next_count( $parsed_xml ),
							'_sage_links'      => $this->get_sdata_links( $parsed_xml ),
						);

						$this->clear();

						return $results;

					} else {
						return $body;
					}
				} else {

					return null;
				}
			}

		}

		/**
		 * Set request headers.
		 */
		protected function set_headers() {
			// Set request headers.
			$this->args['headers'] = array(
				'Authorization' => 'Basic ' . base64_encode( static::$username . ':' . static::$password ),
			);
		}
		/**
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
		}

		// Get SData Links.
		public function get_sdata_links( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$links = $parsed_xml['link'] ?? array();

				$sdata_links = array();

				if ( $links ) {
					foreach ( $links as $link ) {

						$results = wp_parse_url( $link['attributes']['href'] ) ?? '';
						parse_str( $results['query'], $query_vars );

						$start_index = intval( $query_vars['startIndex'] ) ?? '';
						$count       = intval( $query_vars['count'] ) ?? '';

						$sdata_links[] = array(
							'rel'        => esc_attr( $link['attributes']['rel'] ),
							'link'       => $link['attributes']['href'],
							'startIndex' => $start_index,
							'count'      => $count,
						);
					}
				}

				return $sdata_links;
			}

		}

		/**
		 * [get_has_more description]
		 *
		 * @param  array $parsed_xml [description]
		 * @return boolean             [description]
		 */
		public function get_has_more( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$links = $parsed_xml['link'] ?? array();

				$has_more = false;

				if ( $links ) {
					foreach ( $links as $link ) {

						$results = wp_parse_url( $link['attributes']['href'] ) ?? '';
						parse_str( $results['query'], $query_vars );

						if ( $link['attributes']['rel'] === 'last' ) {
							$has_more = true;
						}
					}
				}

				return $has_more;
			}

		}

		/**
		 * Get Next Link.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_next_link( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$sdata_links = $this->get_sdata_links( $parsed_xml ) ?? array();

				if ( $sdata_links ) {

					foreach ( $sdata_links as $sdata_link ) {
						if ( 'next' === $sdata_link['rel'] ) {
							$results = wp_parse_url( $sdata_link['link'] ) ?? '';
							parse_str( $results['query'], $query_vars );
							return $query_vars;
						}
					}
				}

				return null;

			}

		}

		/**
		 * Get Next Start Index.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_next_start_index( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$sdata_links = $this->get_sdata_links( $parsed_xml ) ?? array();

				if ( $sdata_links ) {

					foreach ( $sdata_links as $sdata_link ) {
						if ( 'next' === $sdata_link['rel'] ) {
							$results = wp_parse_url( $sdata_link['link'] ) ?? '';
							parse_str( $results['query'], $query_vars );
							return intval( $query_vars['startIndex'] );
						}
					}
				}

				return null;

			}

		}

		/**
		 * Get Next Count.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_next_count( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$sdata_links = $this->get_sdata_links( $parsed_xml ) ?? array();

				if ( $sdata_links ) {

					foreach ( $sdata_links as $sdata_link ) {
						if ( 'next' === $sdata_link['rel'] ) {
							$results = wp_parse_url( $sdata_link['link'] ) ?? '';
							parse_str( $results['query'], $query_vars );
							return intval( $query_vars['count'] );
						}
					}
				}

				return null;

			}

		}

		/**
		 * Get Self Link.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_self_link( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$sdata_links = $this->get_sdata_links( $parsed_xml ) ?? array();

				if ( $sdata_links ) {
					foreach ( $sdata_links as $sdata_link ) {
						if ( 'self' === $sdata_link['rel'] ) {
							$results = wp_parse_url( $sdata_link['link'] ) ?? '';
							parse_str( $results['query'], $query_vars );
							return $query_vars;

						}
					}
				}
			}

		}

		/**
		 * Get Last Link.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_last_link( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$sdata_links = $this->get_sdata_links( $parsed_xml ) ?? array();

				if ( $sdata_links ) {
					foreach ( $sdata_links as $sdata_link ) {
						if ( 'last' === $sdata_link['rel'] ) {
							$results = wp_parse_url( $sdata_link['link'] ) ?? '';
							parse_str( $results['query'], $query_vars );
							return $query_vars;
						}
					}
				}
			}

		}

		/**
		 * Get First Link.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_first_link( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$sdata_links = $this->get_sdata_links( $parsed_xml ) ?? array();

				if ( $sdata_links ) {
					foreach ( $sdata_links as $sdata_link ) {
						if ( 'first' === $sdata_link['rel'] ) {
							$results = wp_parse_url( $sdata_link['link'] ) ?? '';
							parse_str( $results['query'], $query_vars );
							return $query_vars;
						}
					}
				}
			}

		}

		/**
		 * Decamelize String.
		 *
		 * @param  string $string String.
		 * @return [type]         [description]
		 */
		public function decamelize( string $string ) {
			return strtolower( preg_replace( array( '/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/' ), '$1_$2', $string ) );
		}


		/**
		 * Get Previous Link.
		 *
		 * @param  array $parsed_xml [description]
		 * @return [type]             [description]
		 */
		public function get_previous_link( array $parsed_xml ) {

			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {

				$sdata_links = $this->get_sdata_links( $parsed_xml ) ?? array();

				if ( $sdata_links ) {
					foreach ( $sdata_links as $sdata_link ) {
						if ( 'previous' === $sdata_link['rel'] ) {
							$results = wp_parse_url( $sdata_link['link'] ) ?? '';
							parse_str( $results['query'], $query_vars );
							return $query_vars;
						}
					}
				}
			}

		}

		/**
		 * Get Total Results.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_total_results( array $parsed_xml ) {
			$total_results = '';
			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {
				$total = $parsed_xml['opensearch:totalResults'] ?? '';
				if ( $total ) {
					$total_results = intval( $total ) ?? '';
					return $total_results;
				}
			}
		}

		/**
		 * Get Start Index.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_start_index( array $parsed_xml ) {
			$start_index = '';
			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {
				$index = $parsed_xml['opensearch:startIndex'] ?? '';
				if ( $index ) {
					$start_index = intval( $parsed_xml['opensearch:startIndex'] ) ?? '';
					return $start_index;
				}
			}
		}

		/**
		 * Get Items per Page.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_items_per_page( array $parsed_xml ) {
			$items_per_page = '';
			if ( ! empty( $parsed_xml ) && ! is_wp_error( $parsed_xml ) ) {
				$items = $parsed_xml['opensearch:itemsPerPage'] ?? '';
				if ( $items ) {
					$items_per_page = intval( $items ) ?? '';
					return $items_per_page;
				}
			}
		}

		/**
		 * Check if HTTP status code is a success.
		 *
		 * @param  int $code HTTP status code.
		 * @return boolean       True if status is within valid range.
		 */
		protected function is_status_ok( $code ) {
			return ( 200 <= $code && 300 > $code );
		}

		/**
		 * convert xml string to php array - useful to get a serializable value
		 *
		 * @param string $xml
		 * @return mixed[]
		 *
		 * @author Adrien aka Gaarf & contributors
		 * @see http://gaarf.info/2009/08/13/xml-string-to-php-array/
		 */
		public function xmlstr_to_array( string $xml ): array {
			if ( $xml ) {
				assert( \class_exists( '\DOMDocument' ) );
				$doc = new \DOMDocument();
				$doc->loadXML( $xml );
				$root            = $doc->documentElement;
				$output          = (array) $this->domnode_to_array( $root );
				$output['@root'] = $root->tagName;
				return $output ?? array();
			} else {
				return array();
			}
		}
		/**
		 * @param \DOMElement $node
		 * @return array|string
		 */
		public function domnode_to_array( $node ) {
			$output = array();
			switch ( $node->nodeType ) {
				case 4: // XML_CDATA_SECTION_NODE.
				case 3: // XML_TEXT_NODE.
						$output = trim( $node->textContent );
					break;
				case 1: // XML_ELEMENT_NODE.
					for ( $i = 0, $m = $node->childNodes->length; $i < $m; $i++ ) {
						$child = $node->childNodes->item( $i );
						$v     = $this->domnode_to_array( $child );
						if ( isset( $child->tagName ) ) {
							  $t = $child->tagName;
							if ( ! isset( $output[ $t ] ) ) {
								$output[ $t ] = array();
							}
							$output[ $t ][] = $v;
						} elseif ( $v || '0' === $v ) {
							$output = (string) $v;
						}
					}
					if ( $node->attributes->length && ! is_array( $output ) ) { // Has attributes but isn't an array.
						$output = array( 'content' => $output ); // Change output into an array.
					}
					if ( is_array( $output ) ) {
						if ( $node->attributes->length ) {
							$a = array();
							foreach ( $node->attributes as $attr_name => $attr_node ) {
								$a[ $attr_name ] = (string) $attr_node->value;
							}
							$output['attributes'] = $a;
						}
						foreach ( $output as $t => $v ) {
							if ( 'attributes' !== $t && is_array( $v ) && 1 === count( $v ) ) {
								$output[ $t ] = $v[0];
							}
						}
					}
					break;
			}
			return $output;
		}

		// Get Generator.
		public function get_generator( $parsed_xml ) {
			if ( ! empty( $parsed_xml ) ) {
				return $parsed_xml['generator'] ?? '';
			}
		}

		// Get Title.
		public function get_title( $parsed_xml ) {
			if ( ! empty( $parsed_xml ) ) {
				return $parsed_xml['title'] ?? '';
			}
		}

		/**
		 * [get_category description]
		 *
		 * @param  [type] $parsed_xml [description]
		 * @return [type]             [description]
		 */
		public function get_category( $parsed_xml ) {
			if ( ! empty( $parsed_xml ) ) {
				return $parsed_xml['category'] ?? '';
			}
		}

		/**
		 * [get_author description]
		 *
		 * @param  [type] $parsed_xml [description]
		 * @return [type]             [description]
		 */
		public function get_author( $parsed_xml ) {
			if ( ! empty( $parsed_xml ) ) {
				return $parsed_xml['author'] ?? '';
			}
		}

		/**
		 * [get_id description]
		 *
		 * @param  [type] $parsed_xml [description]
		 * @return [type]             [description]
		 */
		public function get_id( $parsed_xml ) {
			if ( ! empty( $parsed_xml ) ) {
				return esc_url( $parsed_xml['id'] ) ?? '';
			}
		}

		/**
		 * [get_content description]
		 *
		 * @param  [type] $parsed_xml [description]
		 * @return [type]             [description]
		 */
		public function get_content( $parsed_xml ) {
			if ( ! empty( $parsed_xml ) ) {
				return $parsed_xml['content'] ?? '';
			}
		}

		/**
		 * [get_http_status description]
		 *
		 * @param  [type] $parsed_xml [description]
		 * @return [type]             [description]
		 */
		public function get_http_status( $parsed_xml ) {
			if ( ! empty( $parsed_xml ) ) {
				return $parsed_xml['http:httpStatus'] ?? '';
			}
		}

		/**
		 * [get_entry_author description]
		 *
		 * @param  [type] $entry [description]
		 * @return [type]        [description]
		 */
		public function get_entry_author( $entry ) {
			if ( ! empty( $entry ) ) {
				return $entry['author']['name'] ?? '';
			}
		}

		/**
		 * [get_entry_content description]
		 *
		 * @param  [type] $entry [description]
		 * @return [type]        [description]
		 */
		public function get_entry_content( $entry ) {
			if ( ! empty( $entry ) ) {
				return $entry['content'] ?? '';
			}
		}

		/**
		 * [get_entry_id description]
		 *
		 * @param  [type] $entry [description]
		 * @return [type]        [description]
		 */
		public function get_entry_id( $entry ) {
			if ( ! empty( $entry ) ) {
				$entry_id = $entry['id'] ?? '';
				return $entry_id ?? '';
			}
		}

		/**
		 * [get_entry_title description]
		 *
		 * @param  [type] $entry [description]
		 * @return [type]        [description]
		 */
		public function get_entry_title( $entry ) {
			if ( ! empty( $entry ) ) {
				return $entry['title'] ?? '';
			}
		}

		/**
		 * [get_entry_updated description]
		 *
		 * @param  [type] $entry [description]
		 * @return [type]        [description]
		 */
		public function get_entry_updated( $entry ) {
			if ( ! empty( $entry ) ) {
				return $entry['updated'] ?? '';
			}
		}

		/**
		 * [get_entry_http_status description]
		 *
		 * @param  [type] $entry [description]
		 * @return [type]        [description]
		 */
		public function get_entry_http_status( $entry ) {
			if ( ! empty( $entry ) ) {
				return $entry['http:httpStatus'] ?? '';
			}
		}

		/**
		 * [get_updated description]
		 *
		 * @param  [type] $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_updated( $parsed_xml ) {
			if ( ! empty( $parsed_xml ) ) {
				return $parsed_xml['updated'] ?? '';
			}
		}

		/**
		 * Get Payload.
		 *
		 * @param  array $parsed_xml Parsed XML.
		 * @return [type]             [description]
		 */
		public function get_payload( array $parsed_xml ) {

			$payloads = array();
			$data     = array();

			if ( ! empty( $parsed_xml ) && $this->get_items_per_page( $parsed_xml ) > 1 ) {

				if ( $parsed_xml['entry'] ) {
					foreach ( $parsed_xml['entry'] as $entry ) {
						$entry_payload = $entry['sdata:payload'] ?? '';

								$payloads[] = array(
									'id'      => $this->get_entry_id( $entry ) ?? '',
									'author'  => $this->get_entry_author( $entry ) ?? '',
									'status'  => $this->get_entry_http_status( $entry ) ?? '',
									'updated' => $this->get_entry_updated( $entry ) ?? '',
									// 'html'    => $this->get_entry_content( $entry) ?? '',
									'data'    => array_values( $entry_payload )[0] ?? '',
								) ?? array();
					}
				}
			} else {

						$entry_payload = $parsed_xml['entry']['sdata:payload'] ?? '';

				if ( $entry_payload ) {

						$payloads[] = array(
							'id'      => $this->get_id( $parsed_xml ) ?? '',
							'author'  => $this->get_entry_author( $parsed_xml ) ?? '',
							'status'  => $this->get_http_status( $parsed_xml ) ?? '',
							'updated' => $this->get_updated( $parsed_xml ) ?? '',
							// 'html'    => $this->get_content( $parsed_xml['entry'] ) ?? '',
							'data'    => array_values( $entry_payload )[0] ?? '',
						) ?? array();

				}
			}

			return $payloads;

		}


		// ############################ ENDPOINTS. #############################

		/**
		 * Get Customers.
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_customers( $args = array(), $format = '' ) {
			$response = $this->build_request( 'AR_Customer', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * Get Customer.
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
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function create_customer( $args = array(), $format = '' ) {

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

			$response = $this->build_request( 'AR_Customer', $args, 'POST' )->fetch( array( 'format' => $format ) );

			return $response;
		}

		/**
		 * Get Customer Contacts.
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_customer_contacts( $args = array(), $format = '' ) {
			$response = $this->build_request( 'AR_CustomerContact', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * Get All Credit Cards.
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_customer_creditcards( $args = array(), $format = '' ) {
			$response = $this->build_request( 'AR_CustomerCreditCard', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * Get Customer Contact.
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
		 * @param  [type] $customer_id         [description]
		 * @param  [type] $customer_contact_id [description]
		 * @param  array  $args                [description]
		 * @return [type]                      [description]
		 */
		public function delete_customer_contact( $customer_id, $customer_contact_id, $args = array(), $format = '' ) {
			$response = $this->build_request( 'AR_CustomerContact(00;' . $customer_id . ';' . $customer_contact_id . ')', $args, 'DELETE' )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * Get Credit Card.
		 * @param  [type] $customer_id [description]
		 * @param  array  $args        [description]
		 * @return [type]              [description]
		 */
		public function get_customer_creditcard( $customer_id, $args = array(), $format = '' ) {
			$response = $this->build_request( 'AR_CustomerCreditCard(00;' . $customer_id . ')', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}


		/**
		 * Get Sales Order History Headers.
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_sales_order_history_headers( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_SalesOrderHistoryHeader', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * Sales Order History Header by ID.
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
		 * @param  [type] $sales_order_id [description]
		 * @param  array  $args           [description]
		 * @return [type]                 [description]
		 */
		public function delete_sales_order_history( $sales_order_id, $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_SalesOrderHistoryHeader(' . $sales_order_id . ')', $args, 'DELETE' )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/* INVOICES. */

		/**
		 * [get_invoice_headers description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_headers( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoiceHeader', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_defaults description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_defaults( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoiceDefaults', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_history_links description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_history_links( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoiceHistoryLink', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_memos description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_memos( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoiceMemo', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_payments description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_payments( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoicePayment', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_trackings description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_trackings( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoiceTracking', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_tier_distributions description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_tier_distributions( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoiceTierDistribution', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_tax_summaries description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_tax_summaries( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoiceTaxSummary', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_tax_details description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_tax_details( $args = array(), $format = '' ) {
			$response = $this->build_request( 'SO_InvoiceTaxDetail', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_invoice_header description]
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
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_history_headers( $args = array(), $format = '' ) {
			$response = $this->build_request( 'AR_InvoiceHistoryHeader', $args )->fetch( array( 'format' => $format ) );
			return $response;

		}

		/**
		 * Get Invoice History Details.
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_invoice_history_details( $args = array(), $format = '' ) {
			$response = $this->build_request( 'AR_InvoiceHistoryDetail', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [get_single_invoice_history_detail description]
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
		 * @param  [type] $invoice_id [description]
		 * @param  array  $args       [description]
		 * @return [type]             [description]
		 */
		public function get_invoice_history_header( $invoice_id, $args = array(), $format = '' ) {
			$response = $this->build_request( 'AR_InvoiceHistoryHeader(' . $invoice_id . ')', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/* ITEMS. */

		/**
		 * [get_items description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		public function get_items( $args = array(), $format = '' ) {
			$response = $this->build_request( 'CI_Item', $args )->fetch( array( 'format' => $format ) );
			return $response;

		}

		/**
		 * [get_item description]
		 * @param  [type] $item_id [description]
		 * @param  array  $args    [description]
		 * @return [type]          [description]
		 */
		public function get_item( $item_id, $args = array(), $format = '' ) {
			$response = $this->build_request( 'CI_Item(' . $item_id . ')', $args )->fetch( array( 'format' => $format ) );
			return $response;
		}

		/**
		 * [create_item description]
		 * @param  array  $args [description]
		 * @return [type]       [description]
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

			$response = $this->build_request( 'AR_Customer', $args, 'POST' )->fetch( array( 'format' => $format ) );

			return $response;
		}


	}
}
