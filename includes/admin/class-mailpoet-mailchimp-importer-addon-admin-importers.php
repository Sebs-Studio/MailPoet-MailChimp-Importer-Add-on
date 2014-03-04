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
		add_action( 'admin_init', array( $this, 'register_importers' ) );
		add_action( 'import_start', array( $this, 'post_importer_compatibility' ) );
	}

	/**
	 * Add menu items
	 */
	public function register_importers() {
		register_importer( 'mailpoet_mailchimp_import_csv', __( 'MailPoet MailChimp Importer', mailpoet_mailchimp_importer_addon ), __( 'Import your subscribers from your MailChimp campaigns into MailPoet via a csv file.', mailpoet_mailchimp_importer_addon), array( $this, 'mailchimp_importer' ) );
	}

	/**
	 * Add menu item
	 */
	public function mailchimp_importer() {
		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) )
				require $class_wp_importer;
		}

		// includes
		require 'importers/class-mailchimp-importer.php';

		// Dispatch
		$importer = new MailPoet_MailChimp_Importer();
		$importer->dispatch();
	}

	/**
	 * When running the WP importer, ensure the Email and First Name is not empty.
	 *
	 * This code grabs the file before it is imported and goes through a series of checks.
	 *
	 * @access public
	 * @return void
	 */
	public function post_importer_compatibility() {
		global $wpdb;

		if ( empty( $_POST['import_id'] ) || ! class_exists( 'WXR_Parser' ) )
			return;

		$id          = (int) $_POST['import_id'];
		$file        = get_attached_file( $id );
		$parser      = new WXR_Parser();
		$import_data = $parser->parse( $file );

		if ( isset( $import_data['posts'] ) ) {
			$posts = $import_data['posts'];

			if ( $posts && sizeof( $posts ) > 0 ) foreach ( $posts as $post ) {

				if ( !empty( $post['First Name'] ) ) {

					if ( $post['First Name'] && sizeof( $post['First Name'] ) > 0 ) {

						foreach ( $post['Email Address'] as $subscriber ) {

							// Make sure the email does not already exist!
							$exists_in_db = $wpdb->get_var( $wpdb->prepare( "SELECT email FROM " . $wpdb->prefix . "wysija_user WHERE email = %s;", $subscriber ) );

							// If email address is not already registered then insert the subscriber.
							if ( ! $exists_in_db ) {
								// First check if that same email address is a WordPress User.
								
								$user_exists_in_db = $wpdb->get_var( $wpdb->prepare( "SELECT email FROM " . $wpdb->prefix . "users WHERE email = %s;", $subscriber ) );
								if ( ! $user_exists_in_db) {
									$wpdb->insert( $wpdb->prefix . "wysija_user", array( 'email' => $subscriber, 'firstname' => $post['firstname'], 'lastname' => $post['lastname'] ), array( '%s', '%s', '%s' ) );
								}
								// If a registered WordPress User does exist with the same email address then fetch the user ID.
								else{
								}
								$wpdb->insert( $wpdb->prefix . "wysija_user", array( 'email' => $subscriber, 'firstname' => $post['firstname'], 'lastname' => $post['lastname'] ), array( '%s', '%s', '%s' ) );
							}

						}
					}
				}
			}
		}
	}
}

} // end if class exists

return new MailPoet_MailChimp_Importer_Add_on_Admin_Importers();

?>