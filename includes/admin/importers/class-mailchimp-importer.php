<?php
/**
 * MailChimp Importer - Import campaign subscribers from MailChimp into MailPoet subscribers.
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet MailChimp Importer Add-on/Admin/Importers
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'WP_Importer' ) ) {
	class MailPoet_MailChimp_Importer extends WP_Importer {

		var $id;
		var $file_url;
		var $import_page;
		var $delimiter;
		var $posts = array();
		var $imported;
		var $skipped;

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->import_page = 'mailpoet_mailchimp_import_csv';
		}

		/**
		 * Registered callback function for the WordPress Importer
		 *
		 * Manages the two separate stages of the CSV import process
		 */
		function dispatch() {
			$this->header();

			if ( ! empty( $_POST['delimiter'] ) )
				$this->delimiter = stripslashes( trim( $_POST['delimiter'] ) );

			if ( ! $this->delimiter )
				$this->delimiter = ',';

			$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
			switch ( $step ) {
				case 0:
					$this->greet();
					break;
				case 1:
					check_admin_referer( 'import-upload' );
					if ( $this->handle_upload() ) {

						if ( $this->id ) {
							$file = get_attached_file( $this->id );
						}
						else {
							$file = ABSPATH . $this->file_url;
						}

						add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );

						if ( function_exists( 'gc_enable' ) ) {
							gc_enable();
						}

						@set_time_limit(0);
						@ob_flush();
						@flush();

						$this->import( $file );
					}
					break;
			}
			$this->footer();
		}

		/**
		 * format_data_from_csv function.
		 *
		 * @access public
		 * @param mixed $data
		 * @param string $enc
		 * @return string
		 */
		function format_data_from_csv( $data, $enc ) {
			return ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
		}

		/**
		 * import function.
		 *
		 * @access public
		 * @param mixed $file
		 * @return void
		 */
		function import( $file ) {
			global $wpdb;

			$this->imported = $this->skipped = 0;

			if ( ! is_file($file) ) {
				echo '<p><strong>' . __( 'Sorry, there has been an error.', 'mailpoet_mailchimp_importer_addon' ) . '</strong><br />';
				echo __( 'The file does not exist, please try again.', 'mailpoet_mailchimp_importer_addon' ) . '</p>';
				$this->footer();
				die();
			}

			$new_rates = array();

			ini_set( 'auto_detect_line_endings', '1' );

			$domain = get_bloginfo('url');
			if( !empty( $domain ) ) {
				if( is_ssl() ) { 
					$domain = str_replace( 'https://www.', '', $domain );
				}
				else{
					$domain = str_replace( 'http://www.', '', $domain );
				}
			}

			if ( ( $handle = fopen( $file, "r" ) ) !== FALSE ) {

				$header = fgetcsv( $handle, 0, $this->delimiter );

				if ( sizeof( $header ) == 20 ) {

					$loop = 0;

					while ( ( $row = fgetcsv( $handle, 0, $this->delimiter ) ) !== FALSE ) {

						list( $email, $fname, $lname, $website, $format, $member_rating, $optin_time, $optin_ip, $confirm_time, $confirm_ip, $latitude, $longitude, $gmtoff, $dstoff, $timezone, $cc, $region, $last_changed, $leid, $euid ) = $row;

						// Make sure the email does not already exist!
						$exists_in_db = $wpdb->get_var( $wpdb->prepare( "SELECT email FROM " . $wpdb->prefix . "wysija_user WHERE email = %s;", $email ) );

						// If email address is not already registered then insert the subscriber.
						if ( ! $exists_in_db ) {
							// First check if that same email address is a WordPress User.
							$user_exists_in_db = $wpdb->get_var( $wpdb->prepare( "SELECT email FROM " . $wpdb->prefix . "users WHERE email = %s;", $email ) );

							// If the user does not exist already in WordPress then just add the subscriber.
							if ( ! $user_exists_in_db) {
								// Insert subscriber to MailPoet.
								$wpdb->insert( 
									$wpdb->prefix . "wysija_user", 
									array( 
										'email' 		=> $email, 
										'firstname' 	=> $fname, 
										'lastname' 		=> $lname,
										'ip' 			=> $optin_ip,
										'created_at' 	=> strtotime($optin_time),
										'status' 		=> 1,
										'domain' 		=> $domain,
										'confirmed_at' 	=> strtotime($confirm_time),
										'confirmed_ip' 	=> $confirm_ip
									)
								);
							}
							// If a registered WordPress User does exist with the same email address then fetch the user ID.
							else{
								$user = get_user_by( 'email', $email ); // Get user_id by email address.
								// Insert subscriber to MailPoet.
								$wpdb->insert( 
									$wpdb->prefix . "wysija_user", 
									array( 
										'wp_user_id' 	=> $user->id,
										'email' 		=> $email, 
										'firstname' 	=> $fname, 
										'lastname' 		=> $lname,
										'ip' 			=> $optin_ip,
										'created_at' 	=> strtotime($optin_time),
										'status' 		=> 1,
										'domain' 		=> $domain,
										'confirmed_at' 	=> strtotime($confirm_time),
										'confirmed_ip' 	=> $confirm_ip
									)
								);
							} // end if user is a WordPress User.
						}

						$loop ++;
						$this->imported++;
				    }

				} else {

					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'mailpoet_mailchimp_importer_addon' ) . '</strong><br />';
					echo __( 'The CSV is invalid.', 'mailpoet_mailchimp_importer_addon' ) . '</p>';
					$this->footer();
					die();

				}

			    fclose( $handle );
			}

			// Show Result
			echo '<div class="updated settings-error below-h2"><p>
				'.sprintf( __( 'Import complete - imported <strong>%s</strong> subscribers and skipped <strong>%s</strong>.', 'mailpoet_mailchimp_importer_addon' ), $this->imported, $this->skipped ).'
			</p></div>';

			$this->import_end();
		}

		/**
		 * Performs post-import cleanup of files and the cache
		 */
		function import_end() {
			echo '<p>' . __( 'All done!', 'mailpoet_mailchimp_importer_addon' ) . ' <a href="' . admin_url('admin.php?page=wysija_subscribers') . '">' . __( 'View Subscribers', 'mailpoet_mailchimp_importer_addon' ) . '</a>' . '</p>';

			do_action( 'import_end' );
		}

		/**
		 * Handles the CSV upload and initial parsing of the file to prepare for
		 * displaying author import options
		 *
		 * @return bool False if error uploading or invalid file, true otherwise
		 */
		function handle_upload() {

			if ( empty( $_POST['file_url'] ) ) {

				$file = wp_import_handle_upload();

				if ( isset( $file['error'] ) ) {
					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'mailpoet_mailchimp_importer_addon' ) . '</strong><br />';
					echo esc_html( $file['error'] ) . '</p>';
					return false;
				}

				$this->id = (int) $file['id'];

			} else {

				if ( file_exists( ABSPATH . $_POST['file_url'] ) ) {

					$this->file_url = esc_attr( $_POST['file_url'] );

				} else {

					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'mailpoet_mailchimp_importer_addon' ) . '</strong></p>';
					return false;

				}

			}

			return true;
		}

		/**
		 * header function.
		 *
		 * @access public
		 * @return void
		 */
		function header() {
			echo '<div class="wrap"><div class="icon32 icon32-mailpoet-mailchimp-importer-addon" id="icon-mailpoet-mailchimp-importer-addon"><br></div>';
			echo '<h2>' . __( 'Import MailChimp to MailPoet', 'mailpoet_mailchimp_importer_addon' ) . '</h2>';
		}

		/**
		 * footer function.
		 *
		 * @access public
		 * @return void
		 */
		function footer() {
			echo '</div>';
		}

		/**
		 * greet function.
		 *
		 * @access public
		 * @return void
		 */
		function greet() {
	
			echo '<div class="narrow">';
			echo '<p>' . __( 'Hi there! Upload a CSV file containing your campaign subscribers from your MailChimp list of your choosing to import into MailPoet. Choose a .csv file to upload, then click "Upload file and import".', 'mailpoet_mailchimp_importer_addon' ).'</p>';

			$action = 'admin.php?import=mailpoet_mailchimp_import_csv&step=1';

			$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
			$size = size_format( $bytes );
			$upload_dir = wp_upload_dir();
			if ( ! empty( $upload_dir['error'] ) ) :
				?><div class="error"><p><?php _e( 'Before you can upload your import file, you will need to fix the following error:', 'mailpoet_mailchimp_importer_addon' ); ?></p>
				<p><strong><?php echo $upload_dir['error']; ?></strong></p></div><?php
			else :
				?>
				<form enctype="multipart/form-data" id="import-upload-form" method="post" action="<?php echo esc_attr(wp_nonce_url($action, 'import-upload')); ?>">
					<table class="form-table">
						<tbody>
							<tr>
								<th>
									<label for="upload"><?php _e( 'Choose a file from your computer:', 'mailpoet_mailchimp_importer_addon' ); ?></label>
								</th>
								<td>
									<input type="file" id="upload" name="import" size="25" />
									<input type="hidden" name="action" value="save" />
									<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
									<small><?php printf( __('Maximum size: %s', 'mailpoet_mailchimp_importer_addon' ), $size ); ?></small>
								</td>
							</tr>
							<tr>
								<th>
									<label for="file_url"><?php _e( 'OR enter path to file:', 'mailpoet_mailchimp_importer_addon' ); ?></label>
								</th>
								<td>
									<?php echo ' ' . ABSPATH . ' '; ?><input type="text" id="file_url" name="file_url" size="25" />
								</td>
							</tr>
							<tr>
								<th><label><?php _e( 'Delimiter', 'mailpoet_mailchimp_importer_addon' ); ?></label><br/></th>
								<td><input type="text" name="delimiter" placeholder="," size="2" /></td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Upload file and import', 'mailpoet_mailchimp_importer_addon' ); ?>" />
					</p>
				</form>
				<?php
			endif;

			echo '</div>';
		}

		/**
		 * Added to http_request_timeout filter to force timeout at 60 seconds during import
		 * @param  int $val
		 * @return int 60
		 */
		function bump_request_timeout( $val ) {
			return 60;
		}
	}
}
?>