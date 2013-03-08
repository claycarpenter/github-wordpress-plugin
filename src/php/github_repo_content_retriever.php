<?php
/**
 * This file contains the core plugin code. This is primarily in the form of the
 * class GitHubRepoContentRetriever.
 */

/**
 * Require logging support for debug purposes.
 */
require_once ('logger.php');

/*
 * Require the options management for support accessing user-configured plugin
 * options.
 */
require_once ('options_page_manager.php');

/**
 * This class contains the meat of the plugin, including the code for processing
 * the shortcode as well as retrieving and formatting the targeted GitHub repo
 * content.
 *
 * @author Clay Carpenter
 */
class GitHubRepoContentRetriever {
	/**
	 * The fully-qualified URL of this plugin's installation.
	 *
	 * This is used to determine the proper URLs when referencing any needed external
	 * resource (JS, CSS) files.
	 */
	private $plugin_dir_url;

	/**
	 * Create the and register the plugin.
	 *
	 * This constructor also registers the necessary plugin hooks (for handling the
	 * plugin shortcode, and for defining the required resource files).
	 *
	 * @param	string	$plugin_dir_url		The fully-qualified URL of the plugin. This
	 * should include this plugin's named directory, not simply the general plugins
	 * installation location.
	 */
	function __construct( $plugin_dir_url ) {
		// Register the page initialization hooks that will in turn register
		// the plugin's shortcode handler and JS/CSS resource dependencies.
		Logger::log_debug( "Registering plugin hooks." );

		// Store the reference to the plugin's root URL. This will be used
		// later when generating URLs for the (JS, CSS) resource files.
		$this -> plugin_dir_url = $plugin_dir_url;

		// Execute the plugin shortcode handler registration on page
		// initialization.
		add_action( 'init', array( $this, 'register_shortcode' ) );

		// Execute the file (JS, CSS) dependencies registration on init.
		add_action( 'init', array( $this, 'register_dependencies' ) );
	}

	/**
	 * Initialize the plugin by registering the shortcode handler.
	 */
	public function register_shortcode( ) {
		$plugin_shortcode = OptionsManager::get_option_value( OptionsPageConstants::$SETTING_SHORTCODE );
		add_shortcode( $plugin_shortcode, array( $this, 'shortcode_handler' ) );
	}

	public function register_dependencies( ) {
		// Register syntax highlighter JS.
		$highlighter_js_path = $this -> get_resource_url( 'sunlight/sunlight-all-min.js' );
		wp_enqueue_script( 'syntax-highlight', $highlighter_js_path );

		// Register syntax highlighter CSS styles.
		$highlighter_style_path = $this -> get_resource_url( 'sunlight/themes/sunlight.default.css' );
		wp_register_style( 'syntax-highlight-style-default', $highlighter_style_path );
		wp_enqueue_style( 'syntax-highlight-style-default' );
	}

	/**
	 * Combines the base plugin URL with the resource path.
	 *
	 * @param	string	$resource_path	The path to the resource, relative to this
	 * plugin's base directory.
	 *
	 * @return	string					The full URL to the specified resource.
	 */
	private function get_resource_url( $resource_path ) {
		return $this -> plugin_dir_url . $resource_path;
	}

	/**
	 * This method handles the shortcodes found in WordPress content that this plugin
	 * is applied to.
	 *
	 * The handler will parse the shortcode attributes for the values needed by the
	 * plugin's content retrieval operation, and then begin that same operation. When
	 * the content has been retrieved and formatted, this handler will pass the
	 * modified content back to WordPress.
	 *
	 * @param	array 		$atts		The attributes contained within this shortcode
	 * declaration.
	 *
	 * @param	string		$content	The content wrapped by the shortcode. For this plugin,
	 * all content wrapped by the shortcode will be replace with the file content
	 * pulled from the GitHub repo.
	 *
	 * @return	string					The content identified by the given shortcode attributes
	 * (userid, repoid, path).
	 */
	public function shortcode_handler( $atts, $content = null ) {
		// Define an array of default values for the shortcodes.
		$shortcode_atts_defaults = array( 'userid' => null, 'repoid' => null, 'path' => null, 'language' => null, 'startloc' => 1, 'stoploc' => null, );

		// Combine the default values with those values pulled from the WordPress
		// shortcode.
		$atts = shortcode_atts( $shortcode_atts_defaults, $atts );

		$content = $this -> pull_content( $atts[ 'userid' ], $atts[ 'repoid' ], $atts[ 'path' ], $atts[ 'language' ], $atts[ 'startloc' ], $atts[ 'stoploc' ] );

		return $content;
	}

	/**
	 * Performs the retrieval of the target content from a GitHub repo.
	 *
	 * This method retrieves the targeted content from a GitHub repo. After
	 * retrieval, it optionally trims the content down to the targeted lines of code.
	 * The remaining code is then wrapped in a syntax highlighting structure and
	 * returned to the WordPress engine.
	 *
	 * @param 	string	$user_id		The id of the GitHub user who owns the repository.
	 *
	 * @param	string	$repo_id		The id of the GitHub repository.
	 *
	 * @param	string	$content_path	The path (in GitHub) to the content. This is
	 * relative to the GitHub repo root.
	 *
	 * @param	string	$language		The programming language of the targeted content.
	 * This should be in lower case, and match one of the values recognized by the
	 * included version of the Sunlight highlighting engine
	 * (http://http://sunlightjs.com/).
	 *
	 * @param	int		$start_loc		Optional. The first line-of-code of the content to
	 * display. If this is not provided, the default is 1.
	 *
	 * @param	int		$stop_loc		Optional. The last line-of-code of the content to
	 * display. If this is not provided, the default is -1 and interpreted as the
	 * equivalent to the last line of content.
	 */
	public static function pull_content( $user_id, $repo_id, $content_path, $language, $start_loc = 1, $stop_loc = -1 ) {
		// Construct the URL to the GitHub content.
		$content_url = "https://api.github.com/repos/{$user_id}/{$repo_id}/contents/{$content_path}";

		Logger::log_debug( "Retrieving URL: {$content_url}" );

		// Pull the content down.
		$content_request_result = wp_remote_get( $content_url );

		// The GitHub APIs will return a JSON-formatted response. Parse the body to find
		// the file content.
		$json_result = json_decode( $content_request_result[ 'body' ] );

		// The file content is transfered as base64-encoded data. Decode the data to get
		// the raw file content.
		$content = base64_decode( $json_result -> content );

		// If a start or stop location has been specified, divide the content into lines
		// and then extract the targeted lines-of-code into a new content snippet.
		if ( $start_loc > 1 || $stop_loc > 0 ) {
			// Count the lines in the content.
			$content_lines = explode( "\n", $content );
			$line_count = count( $content_lines );

			// If there is no stop l-o-c defined, continue to the end of the file.
			if ( $stop_loc < 0 ) {
				$stop_loc = count( $content_lines );
			}

			// Combine the targeted lines into a single content snippet.
			$content_snippet = "";
			for ( $i = $start_loc; $i < $stop_loc + 1; $i++ ) {
				$content_snippet .= "{$content_lines[$i - 1]}\n";
			}

			// Push the snippet back into the main content reference for inclusion in the
			// final syntax highlighted block.
			$content = $content_snippet;
		}

		// Create a random ID for the <pre> element. This ID will be used to tell the
		// syntax highlighter which specific node to target for syntax highlighting.
		// Targeting a single node allows the syntax highlighter to avoid re-processing
		// code blocks that have already been highlighted.
		$element_id = 'github-repo-content-' . rand( ) . '-id';

		// Create the <pre> element will wrap the content, inject the content into that
		// element, and then follow the <pre> with a JS snippet that will execute the
		// syntax highlighting on the content code block.
		$content = "<pre id='{$element_id}' class='sunlight-highlight-{$language}'>{$content}</pre>" . "<script type='text/javascript'>new Sunlight.Highlighter({lineNumbers: true, lineNumberStart: {$start_loc}}).highlightNode(document.getElementById('${element_id}'));</script>";

		return $content;
	}

}
?>
