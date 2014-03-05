<?php
/**
 * Setup importers for MailChimp to MailPoet.
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet MailChimp Importer Add-on/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MailPoet_MailChimp_Importer_Add_on_Admin_Importers' ) ) {

/**
 * MailPoet_MailChimp_Importer_Add_on_Admin_Importers Class
 */
class MailPoet_MailChimp_Importer_Add_on_Admin_Importers {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'register_importers' ) );
	}

	/**
	 * Add menu items
	 */
	public function register_importers() {
		register_importer( 'mailpoet_mailchimp_import_csv', __( 'MailChimp to MailPoet', 'mailpoet_mailchimp_importer_addon' ), __( 'Import your subscribers from your MailChimp campaigns into MailPoet via a csv file.', 'mailpoet_mailchimp_importer_addon'), array( &$this, 'mailchimp_importer' ) );
	}

	/**
	 * Add menu item
	 */
	public function mailchimp_importer() {
		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) ) {
				require $class_wp_importer;
			}
		}

		// includes
		require 'importers/class-mailchimp-importer.php';

		// Dispatch
		$importer = new MailPoet_MailChimp_Importer();
		$importer->dispatch();
	}

} // end class

} // end if class exists

return new MailPoet_MailChimp_Importer_Add_on_Admin_Importers();

?>