<?php
/**
 * MailPoet MailChimp Importer Add-on Admin.
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet MailChimp Importer Add-on/Admin
 * @version 	1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MailPoet_MailChimp_Importer_Addon_Admin' ) ) {

	class MailPoet_MailChimp_Importer_Addon_Admin {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Actions
			add_action( 'init', array( &$this, 'includes' ) );
		}

		/**
		 * Include any classes we need within admin.
		 */
		public function includes() {
			// Functions
			include( 'mailpoet-mailchimp-importer-addon-admin-functions.php' );

			// Classes we only need if the ajax is not-ajax
			if ( ! is_ajax() ) {
				// Help
				if ( apply_filters( 'mailpoet_mailchimp_importer_addon_enable_admin_help_tab', true ) ) {
					include( 'class-mailpoet-mailchimp-importer-addon-admin-help.php' );
				}
			}

			// Importers
			if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
				include( 'class-mailpoet-mailchimp-importer-addon-admin-importers.php' );
			}
		}

	} // end class.

} // end if class exists.

return new MailPoet_MailChimp_Importer_Addon_Admin();

?>