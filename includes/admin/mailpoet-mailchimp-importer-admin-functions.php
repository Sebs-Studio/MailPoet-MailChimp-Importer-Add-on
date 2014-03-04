 <?php
/**
 * MailPoet MailChimp Importer Add-on Admin Functions
 *
 * @author 		Sebs Studio
 * @category 	Core
 * @package 	MailPoet MailChimp Importer Add-on/Admin/Functions
 * @version 	1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get all MailPoet MailChimp Importer Add-on screen ids
 *
 * @return array
 */
function mailpoet_mailchimp_importer_addon_get_screen_ids() {
	$mailpoet_mailchimp_importer_addon_screen_id = strtolower( str_replace ( ' ', '-', __( 'MailPoet MailChimp Importer Add-on', 'mailpoet_mailchimp_importer_addon' ) ) );

	return apply_filters( 'mailpoet_mailchimp_importer_addon_screen_ids', array(
		'toplevel_page_' . $mailpoet_mailchimp_importer_addon_screen_id,
	) );
}

?>