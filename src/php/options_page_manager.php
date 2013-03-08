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
		// Verify presence of plugin options.
		$this -> verify_plugin_options( );

		// Add the options page registration to the admin_menu WordPress action.
		add_action( 'admin_menu', array( $this, 'register_options_page' ) );

		// Add the options page's settings registration to the admin_init WordPress
		// action.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	private function verify_plugin_options( ) {
		// Attempt to retrieve the options data for this plugin.
		$options_data = get_option( OptionsPageConstants::$OPTIONS_DATA );

		// If the retrieved value is false, the options have no yet been created.
		if ( $option_data === FALSE ) {
			// Create a set of default values for the options.
			$options_data = array( OptionsPageConstants::$SETTING_SHORTCODE => 'getgit' );

			// Persist the default option values.
			update_option( OptionsPageConstants::$OPTIONS_DATA, $options_data );
		} 
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
		register_setting( OptionsPageConstants::$OPTIONS_DATA, OptionsPageConstants::$OPTIONS_DATA, array( $this, 'field_submit_callback' ) );

		add_settings_section( OptionsPageConstants::$SECTION_SHORTCODE, 'Shortcode', array( $this, 'print_shortcode_section_info' ), OptionsPageConstants::$OPTIONS_PAGE_ID );

		add_settings_field( 'test_input_id', 'GetGit Shortcode', array( $this, 'print_test_input_field' ), OptionsPageConstants::$OPTIONS_PAGE_ID, OptionsPageConstants::$SECTION_SHORTCODE );
	}

	public function generate_options_view( ) {
		include ('options_page.php');
	}

	public function print_shortcode_section_info( ) {
		echo 'Configure the shortcode that launches this plugin and embeds GitHub repo content.';
	}

	public function print_test_input_field( ) {
		$options_data_name = OptionsPageConstants::$OPTIONS_DATA;
		$setting_name = OptionsPageConstants::$SETTING_SHORTCODE;
		$options_data = get_option( OptionsPageConstants::$OPTIONS_DATA );
		$option_value = $options_data[ OptionsPageConstants::$SETTING_SHORTCODE ];

		echo "<input type='text' id='{$setting_name}' name='{$options_data_name}[{$setting_name}]' value='{$option_value}'/><br/><i>The shortcode may consist of only alphabetic characters (upper and lower case).</i>";
	}

	public function field_submit_callback( $input ) {
		$valid = get_option( OptionsPageConstants::$OPTIONS_DATA );

		$alpha_only_pattern = '/[^a-zA-Z]/';
		if ( !preg_match( $alpha_only_pattern, $input[ OptionsPageConstants::$SETTING_SHORTCODE ] ) ) {
			// Input consists of only alphabetic characters. Proceed to persist new option.
			$valid[ OptionsPageConstants::$SETTING_SHORTCODE ] = $input[ OptionsPageConstants::$SETTING_SHORTCODE ];
		}

		return $valid;
	}

}

class OptionsPageConstants {
	public static $OPTIONS_PAGE_ID = 'getgit';
	public static $OPTIONS_DATA = 'getgit_options';

	public static $SECTION_SHORTCODE = 'section_shortcode';

	public static $SETTING_SHORTCODE = 'setting_shortcode';
}
?>