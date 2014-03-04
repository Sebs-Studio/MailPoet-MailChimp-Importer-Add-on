<?php
/**
 * MailPoet MailChimp Importer Add-on Conditional Functions
 *
 * Functions for determining the current query/page.
 *
 * @author 		Sebs Studio
 * @category 	Core
 * @package 	MilPoet MailChimp Importer Add-on/Functions
 * @version 	1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'is_ajax' ) ) {

	/**
	 * is_ajax - Returns true when the page is loaded via ajax.
	 *
	 * @access public
	 * @return bool
	 */
	function is_ajax() {
		if ( defined('DOING_AJAX') ) {
			return true;
		}

		return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) ? true : false;
	}
}

?>