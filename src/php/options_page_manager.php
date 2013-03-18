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

		// Add the options page's settings registration to the admin_init WordPress
		// action.
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

	/**
	 * Registers the settings sections and fields for the plugin's settings page.
	 */
	public function register_settings( ) {
		// Register the options data.
		register_setting( OptionsPageConstants::$OPTIONS_DATA, OptionsPageConstants::$OPTIONS_DATA, array( $this, 'field_submit_callback' ) );

		/* Shortcode section */

		// Register the Shortcode section.
		add_settings_section( OptionsPageConstants::$SECTION_SHORTCODE, 'Shortcode', array( $this, 'print_shortcode_section_info' ), OptionsPageConstants::$OPTIONS_PAGE_ID );

		// Register the shortcode setting field.
		add_settings_field( OptionsPageConstants::$SETTING_SHORTCODE, 'GetGit Shortcode', array( $this, 'print_input_field_setting_shortcode' ), OptionsPageConstants::$OPTIONS_PAGE_ID, OptionsPageConstants::$SECTION_SHORTCODE );

		/* Cache section */

		// Register the Cache section.
		add_settings_section( OptionsPageConstants::$SECTION_CACHE, 'Cache', array( $this, 'print_cache_section_info' ), OptionsPageConstants::$OPTIONS_PAGE_ID );

		// Register the cache status setting field.
		add_settings_field( OptionsPageConstants::$SETTING_CACHE_STATUS, 'Cache Status', array( $this, 'print_input_field_setting_cache_status' ), OptionsPageConstants::$OPTIONS_PAGE_ID, OptionsPageConstants::$SECTION_CACHE );

		// Register the cache TTL setting field.
		add_settings_field( OptionsPageConstants::$SETTING_CACHE_TTL, 'Cacheed Content TTL', array( $this, 'print_input_field_setting_cache_ttl' ), OptionsPageConstants::$OPTIONS_PAGE_ID, OptionsPageConstants::$SECTION_CACHE );
	}

	/**
	 * Generates the settings page for the plugin.
	 *
	 * This currently simply includes/inlines the file options_page.php.
	 */
	public function generate_options_view( ) {
		include ('options_page.php');
	}

	/**
	 * Generates the description for the Shortcode section.
	 */
	public function print_shortcode_section_info( ) {
		echo 'Configure the shortcode that launches this plugin and embeds GitHub repo content.';
	}

	/**
	 * Generates the description for the Cache section.
	 */
	public function print_cache_section_info( ) {
		echo 'Configure the cache settings. Content caching allows for fewer requests to be made to the GitHub repo servers, and results in faster page load times and lower bandwidth usage.';
	}

	// TODO: This input field generation code is highly redundant.
	/**
	 * Generates the input field for the shortcode setting.
	 */
	public function print_input_field_setting_shortcode( ) {
		$options_data_name = OptionsPageConstants::$OPTIONS_DATA;
		$setting_name = OptionsPageConstants::$SETTING_SHORTCODE;
		$option_value = OptionsManager::get_option_value( OptionsPageConstants::$SETTING_SHORTCODE );

		echo "<input type='text' id='{$setting_name}' name='{$options_data_name}[{$setting_name}]' value='{$option_value}'/><br/><i>The shortcode may consist of only alphabetic characters (upper and lower case).</i>";
	}

	/**
	 * Generates the input field for the cache status setting.
	 */
	public function print_input_field_setting_cache_status( ) {
		$options_data_name = OptionsPageConstants::$OPTIONS_DATA;
		$setting_name = OptionsPageConstants::$SETTING_CACHE_STATUS;
		$option_value = OptionsManager::get_option_value( OptionsPageConstants::$SETTING_CACHE_STATUS );
		$enabled_checked = $option_value === OptionsPageConstants::$SETTING_CACHE_STATUS_VALUE_ENABLED ? " checked " : "";
		$disabled_checked = $option_value === OptionsPageConstants::$SETTING_CACHE_STATUS_VALUE_DISABLED ? " checked " : "";

		echo "<input type='radio' id='{$setting_name}_enabled' name='{$options_data_name}[{$setting_name}]' value='enabled' {$enabled_checked} /><label for='{$setting_name}_enabled'>Enabled</label><br/><input type='radio' id='{$setting_name}_disabled' name='{$options_data_name}[{$setting_name}]' value='disabled' {$disabled_checked}/><label for='{$setting_name}_disabled'>Disabled</label>";
	}

	/**
	 * Generates the input field for the cache TTL setting.
	 */
	public function print_input_field_setting_cache_ttl( ) {
		$options_data_name = OptionsPageConstants::$OPTIONS_DATA;
		$setting_name = OptionsPageConstants::$SETTING_CACHE_TTL;
		$option_value = OptionsManager::get_option_value( OptionsPageConstants::$SETTING_CACHE_TTL );

		echo "<input type='text' id='{$setting_name}' name='{$options_data_name}[{$setting_name}]' value='{$option_value}'/><br/><i>The cache content TTL (Time To Live) is specified in seconds.</i>";
	}

	/**
	 * Validates the settings input data.
	 *
	 * If the settings do not pass the validation, the options values currently held
	 * in the WordPress options table are used instead.
	 *
	 * @param		array 	$input	Associative array of input from the settings fields.
	 *
	 * @return		array 			Associative array of sanitized setting values.
	 */
	public function field_submit_callback( $input ) {
		$valid = OptionsManager::get_options_data( );

		$alpha_only_pattern = '/[^a-zA-Z]/';
		if ( !preg_match( $alpha_only_pattern, $input[ OptionsPageConstants::$SETTING_SHORTCODE ] ) ) {
			// Input consists of only alphabetic characters. Proceed to persist new option.
			$valid[ OptionsPageConstants::$SETTING_SHORTCODE ] = $input[ OptionsPageConstants::$SETTING_SHORTCODE ];
		}

		if ( $input[ OptionsPageConstants::$SETTING_CACHE_STATUS ] === OptionsPageConstants::$SETTING_CACHE_STATUS_VALUE_ENABLED || $input[ OptionsPageConstants::$SETTING_CACHE_STATUS ] === OptionsPageConstants::$SETTING_CACHE_STATUS_VALUE_DISABLED ) {
			// Input consists of a valid cache status flag. Proceed to persist new option.
			$valid[ OptionsPageConstants::$SETTING_CACHE_STATUS ] = $input[ OptionsPageConstants::$SETTING_CACHE_STATUS ];
		}

		$digits_only_pattern = '/\D/';
		if ( !preg_match( $digits_only_pattern, $input[ OptionsPageConstants::$SETTING_CACHE_TTL ] ) ) {
			// Input consists of only digits. Check to ensure it's a valid positive number
			// above zero.
			$int_value = intval( $input[ OptionsPageConstants::$SETTING_CACHE_TTL ] );
			if ( $int_value > 0 ) {
				// New TTL value is valid. Proceed to persist new option.
				$valid[ OptionsPageConstants::$SETTING_CACHE_TTL ] = $int_value;
			}
		}

		return $valid;
	}

}

class OptionsManager {
	/**
	 * Retrieves the value of an individual option.
	 *
	 * This function calls through to get_options_data, ensuring that even if no
	 * options are currently stored in the WordPress options table, a default value
	 * will be created and returned.
	 *
	 * @param	string	$option_key		The key for the targeted option value. This is the
	 * same key the option is stored under in the associative array that holds the
	 * option values for this plugin.
	 *
	 * @return	mixed					The value for the option associated with the specified key.
	 */
	public static function get_option_value( $option_key ) {
		$options_data = self::get_options_data( );

		return $options_data[ $option_key ];
	}

	/**
	 * Retrieves the options data for this plugin.
	 *
	 * If the options data is not already present/persisted, a default data set will
	 * be created.
	 *
	 * @return		array 	Options data as an associative array; either read from the
	 * WordPress options table or built from default values.
	 */
	public static function get_options_data( ) {
		// Attempt to retrieve the options data for this plugin.
		$options_data = get_option( OptionsPageConstants::$OPTIONS_DATA );

		$update_options = FALSE;

		// If the retrieved value is false, the options have no yet been created.
		if ( $options_data === FALSE ) {
			// Create a set of default values for the options.
			$options_data = array( );

			$update_options = TRUE;
		}

		if ( !array_key_exists( OptionsPageConstants::$SETTING_SHORTCODE, $options_data ) ) {
			// Default shortcode is 'getgit'.
			$options_data[ OptionsPageConstants::$SETTING_SHORTCODE ] = 'getgit';

			$update_options = TRUE;
		}

		if ( !array_key_exists( OptionsPageConstants::$SETTING_CACHE_STATUS, $options_data ) ) {
			// Default cache status is 'enabled'.
			$options_data[ OptionsPageConstants::$SETTING_CACHE_STATUS ] = OptionsPageConstants::$SETTING_CACHE_STATUS_VALUE_ENABLED;

			$update_options = TRUE;
		}

		if ( !array_key_exists( OptionsPageConstants::$SETTING_CACHE_TTL, $options_data ) ) {
			// Default cache TTL is 1 hour (3600 seconds).
			$options_data[ OptionsPageConstants::$SETTING_CACHE_TTL ] = 3600;

			$update_options = TRUE;
		}

		if ( $update_options ) {
			// Persist the default option values.
			update_option( OptionsPageConstants::$OPTIONS_DATA, $options_data );
		}

		return $options_data;
	}

}

class OptionsPageConstants {
	/**
	 * Settings page slug/ID.
	 */
	public static $OPTIONS_PAGE_ID = 'getgit';

	/**
	 * Options data name (stored in WordPress options table).
	 */
	public static $OPTIONS_DATA = 'getgit_options';

	/**
	 * ID for the Shortcode settings section.
	 */
	public static $SECTION_SHORTCODE = 'section_shortcode';

	/**
	 * ID/name for the shortcode setting. This setting allows the user to configure
	 * the shortcode name that will be used to start the plugin handler.
	 */
	public static $SETTING_SHORTCODE = 'setting_shortcode';

	/**
	 * ID for the Cache settings section.
	 */
	public static $SECTION_CACHE = 'section_cache';

	/**
	 * ID/name for the shortcode setting. This setting allows the user to configure
	 * whether the cache will be used or not.
	 */
	public static $SETTING_CACHE_STATUS = 'setting_cache_status';

	/**
	 * The enabled and disabled flag values for the cache status setting.
	 */
	public static $SETTING_CACHE_STATUS_VALUE_ENABLED = 'enabled';
	public static $SETTING_CACHE_STATUS_VALUE_DISABLED = 'disabled';

	/**
	 * ID/name for the cache TTL setting. This setting allows the user to configure
	 * how long content will stay fresh in the (Transients) cache before expiring.
	 */
	public static $SETTING_CACHE_TTL = 'setting_cache_ttl';
}
?>