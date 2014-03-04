<?php
/**
 * Setup menu in WP admin.
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet MailChimp Importer Add-on/Admin
 * @version 	1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MailPoet_MailChimp_Importer_Addon_Admin_Menus' ) ) {

/**
 * MailPoet_MailChimp_Importer_Addon_Admin_Menus Class
 */
class MailPoet_MailChimp_Importer_Addon_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus
		add_action( 'admin_menu', array( &$this, 'admin_menu' ), 9 );
	}

	/**
	 * Add menu items
	 */
	public function admin_menu() {
		$settings_page = add_submenu_page( 'pmpro-membershiplevels', __( 'MailPoet MailChimp Importer Settings', 'mailpoet_mailchimp_importer_addon' ),  __( 'MailPoet PMPro', 'mailpoet_mailchimp_importer_addon' ) , 'manage_options', 'mailchimp-importer-settings', array( &$this, 'settings_page' ) );
	}

}

} // end if class exists.

return new MailPoet_MailChimp_Importer_Addon_Admin_Menus();

?>