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
	 * When running the WP importer, ensure attributes exist.
	 *
	 * WordPress import should work - however, it fails to import custom product attribute taxonomies.
	 * This code grabs the file before it is imported and ensures the taxonomies are created.
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

				if ( $post['post_type'] == 'product' ) {

					if ( $post['terms'] && sizeof( $post['terms'] ) > 0 ) {

						foreach ( $post['terms'] as $term ) {

							$domain = $term['domain'];

							if ( strstr( $domain, 'pa_' ) ) {

								// Make sure it exists!
								if ( ! taxonomy_exists( $domain ) ) {

									$nicename = strtolower( sanitize_title( str_replace( 'pa_', '', $domain ) ) );

									$exists_in_db = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_id FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = %s;", $nicename ) );

									// Create the taxonomy
									if ( ! $exists_in_db )
										$wpdb->insert( $wpdb->prefix . "woocommerce_attribute_taxonomies", array( 'attribute_name' => $nicename, 'attribute_type' => 'select', 'attribute_orderby' => 'menu_order' ), array( '%s', '%s', '%s' ) );

									// Register the taxonomy now so that the import works!
									register_taxonomy( $domain,
								        apply_filters( 'woocommerce_taxonomy_objects_' . $domain, array('product') ),
								        apply_filters( 'woocommerce_taxonomy_args_' . $domain, array(
								            'hierarchical' => true,
								            'show_ui' => false,
								            'query_var' => true,
								            'rewrite' => false,
								        ) )
								    );
								}
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