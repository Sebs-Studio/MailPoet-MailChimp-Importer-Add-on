<?php
/**
 * Add some content to the help tab.
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet MailChimp Importer Add-on/Admin
 * @version 	1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MailPoet_MailChimp_Importer_Addon_Admin_Help' ) ) {

/**
 * MailPoet_MailChimp_Importer_Addon_Admin_Help Class
 */
class MailPoet_MailChimp_Importer_Addon_Admin_Help {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'current_screen', array( &$this, 'add_tabs' ), 50 );
	}

	/**
	 * Add help tabs
	 */
	public function add_tabs() {
		$screen = get_current_screen();

		if ( ! in_array( $screen->id, MailPoet_MailChimp_Importer_Addon_get_screen_ids() ) )
			return;

		$screen->add_help_tab( array(
			'id'	=> 'MailPoet_MailChimp_Importer_Addon_docs_tab',
			'title'	=> __( 'Documentation', 'mailpoet_mailchimp_importer_add_on' ),
			'content'	=>

				'<p>' . __( 'Thank you for using MailPoet MailChimp Importer Add-on :) Should you need help using MailPoet MailChimp Importer Add-on please read the documentation.', 'mailpoet_mailchimp_importer_add_on' ) . '</p>' .

				'<p><a href="' . MailPoet_MailChimp_Importer_Addon()->doc_url . '" class="button button-primary">' . __( 'MailPoet MailChimp Importer Add-on Documentation', 'mailpoet_mailchimp_importer_add_on' ) . '</a></p>'

		) );

		$screen->add_help_tab( array(
			'id'	=> 'MailPoet_MailChimp_Importer_Addon_support_tab',
			'title'	=> __( 'Support', 'mailpoet_mailchimp_importer_add_on' ),
			'content'	=>

				'<p>' . sprintf(__( 'After <a href="%s">reading the documentation</a>, for further assistance you can use the <a href="%s">community forum</a>.', 'mailpoet_mailchimp_importer_add_on' ), MailPoet_MailChimp_Importer_Addon()->doc_url, 'http://wordpress.org/support/plugin/mailpoet-mailchimp-importer-add-on' ) . '</p>' .

				'<p><a href="' . 'http://wordpress.org/support/plugin/mailpoet-mailchimp-importer-add-on' . '" class="button">' . __( 'Community Support', 'mailpoet_mailchimp_importer_add_on' ) . '</a></p>'

		) );

		$screen->add_help_tab( array(
			'id'	=> 'MailPoet_MailChimp_Importer_Addon_bugs_tab',
			'title'	=> __( 'Found a bug?', 'mailpoet_mailchimp_importer_add_on' ),
			'content'	=>

				'<p>' . sprintf(__( 'If you find a bug within MailPoet MailChimp Importer Add-on you can create a ticket via <a href="%s">Github issues</a>. Ensure you read the <a href="%s">contribution guide</a> prior to submitting your report. Be as descriptive as possible.', 'mailpoet_mailchimp_importer_add_on' ), GITHUB_REPO_URL . 'issues?state=open', GITHUB_REPO_URL . 'blob/master/CONTRIBUTING.md' ) . '</p>' .

				'<p><a href="' . GITHUB_REPO_URL . 'issues?state=open" class="button button-primary">' . __( 'Report a bug', 'mailpoet_mailchimp_importer_add_on' ) . '</a></p>'

		) );

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'mailpoet_mailchimp_importer_add_on' ) . '</strong></p>' .
			'<p><a href="http://wordpress.org/plugins/mailpoet-mailchimp-importer-add-on/" target="_blank">' . __( 'Project on WordPress.org', 'mailpoet_mailchimp_importer_add_on' ) . '</a></p>' .
			'<p><a href="' . GITHUB_REPO_URL . '" target="_blank">' . __( 'Project on Github', 'mailpoet_mailchimp_importer_add_on' ) . '</a></p>'
		);
	}

}

} // end if class exists.

return new MailPoet_MailChimp_Importer_Addon_Admin_Help();

?>