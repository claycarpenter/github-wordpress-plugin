<?php
/**
 * This file contains the GetGit options registration, page view, and control
 * code.
 */

require_once ('logger.php');

/**
 * Creates the options page for the GetGit plugin.
 *
 * @author Clay Carpenter
 */
class OptionsPageManager {
	/**
	 * Creates the new OptionsPageManager object.
	 *
	 * During creation, this function will add a hook into the WordPress admin_menu
	 * action to register the options page.
	 */
	function __construct( ) {
		// Add the options page registration to the admin_menu WordPress action.
		add_action( 'admin_menu', array( $this, 'register_options_page' ) );

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Registers the options page with WordPress.
	 *
	 * This allows the options page to show up under the Settings menu visible to
	 * those users with the role of Administrator.
	 */
	public function register_options_page( ) {
		add_options_page( 'GetGit Settings', 'GetGit', 'manage_options', OptionsPageConstants::$OPTIONS_PAGE_ID, array( $this, 'generate_options_view' ) );
	}

	public function register_settings( ) {
		add_settings_section( OptionsPageConstants::$SECTION_SHORTCODE_SETTINGS, 'Shortcode', array( $this, 'print_shortcode_section_info' ), OptionsPageConstants::$SECTION_SHORTCODE );

		register_setting( OptionsPageConstants::$SECTION_SHORTCODE_SETTINGS, 'test_input_id');
		add_settings_field( 'test_input_id', 'Test title', array( $this, 'print_test_input_field' ), OptionsPageConstants::$SECTION_SHORTCODE, OptionsPageConstants::$SECTION_SHORTCODE_SETTINGS );
	}

	public function generate_options_view( ) {
		include ('options_page.php');
	}

	public function print_shortcode_section_info( ) {
		echo 'Section description...';
	}

	public function print_test_input_field( ) {
		echo '<input type="text" id="test_input_id" name="test_input_id" value="fafke value"/>';
	}

	public function field_submit_callback( ) {
		Logger::log_debug( "field_submit_callback executing..." );
	}

}

class OptionsPageConstants {
	public static $OPTIONS_PAGE_ID = 'getgit';
	public static $SECTION_SHORTCODE = 'getgit_section_shortcode_settings';
	public static $SECTION_SHORTCODE_SETTINGS = 'getgit_section_shortcode_settings';
}
?>
