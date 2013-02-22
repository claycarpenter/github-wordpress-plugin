<?php
/*
Plugin Name: GitHub Repo Content Embedder
Plugin URI: http://flightlessflyer.pinguinotech.com/github-wordpress-plugin
Description: Embeds content held in a GitHub repo.
Version: 0.0.1a
Author: Clay Carpenter
*/

/*
 * Simple error/debug logging utility. 
 */
class Logger {
	private static $log_file_location = 'debug.log';
	
	public static function log_debug($message) {
		error_log(self::get_timestamp() . " - " . $message . "\n", 3, self::$log_file_location);
	}
	
	private static function get_timestamp() {
		return date(DateTime::RFC822);
	}
}


// TODO: Check for WP_http availability
// https://github.com/claycarpenter/github-wordpress-plugin.git
class GitHubRepoContentRetriever {
	public static function pull_content($userid, $repoid, $content_path, $language, $start_loc, $stop_loc) {
		$content_url = "https://api.github.com/repos/{$userid}/{$repoid}/contents/{$content_path}";
		
		Logger::log_debug("Retrieving URL: {$content_url}");
		
		$content_request_result = wp_remote_get($content_url);
			
		$json_result = json_decode($content_request_result['body']);
		Logger::log_debug(print_r($json_result, true));
		$content_encoded = $json_result->content;
		Logger::log_debug(print_r($content_encoded, true));
		$content_raw = base64_decode($json_result->content);
		$content = $content_raw;
		
		$content_lines = explode("\n", $content_raw);
		$lines_count = count($content_lines);		
		$content_loc = "";
		if ($stop_loc == null) {
			$stop_loc = count($content_lines);
		}
		Logger::log_debug("Counted {$lines_count} lines in content. Printing {$start_loc} - {$stop_loc}");
		for ($i = $start_loc; $i < $stop_loc + 1; $i++) {
			$content_loc .= "{$content_lines[$i - 1]}\n";
		}
		$element_id = 'github-repo-content-'.rand().'-id';
		$content = "<pre id='{$element_id}' class='sunlight-highlight-{$language}'>{$content_loc}</pre><script type='text/javascript'>new Sunlight.Highlighter({lineNumbers: true, lineNumberStart: {$start_loc}}).highlightNode(document.getElementById('${element_id}'));</script>";
		
		return $content;
	}
}

function github_repo_content_shortcode_handler($atts, $content = null) {
	$shortcode_atts_defaults = array(
		'userid' => null,
		'repoid' => null,
		'path' => null,
		'language' => null,
		'startloc' => 1,
		'stoploc' => null,
	);
	
	$atts = shortcode_atts($shortcode_atts_defaults, $atts);

	$content = GitHubRepoContentRetriever::pull_content(
		$atts['userid'], 
		$atts['repoid'], 
		$atts['path'],
		$atts['language'],
		$atts['startloc'],
		$atts['stoploc']);
	
	return $content;
}

/**
 * Initialize the plugin by registering the shortcode handler.
 */
function github_repo_content_register_shortcode() {
	add_shortcode('github', 'github_repo_content_shortcode_handler');
}

function github_repo_content_register_dependencies() {
	// Register syntax highlighter JS.
	$highlighter_js_path = plugins_url(
		'sunlight/sunlight-all-min.js', 
		__FILE__
	);
	wp_enqueue_script(
		'syntax-highlight',
		$highlighter_js_path
	);
	
	// Register syntax highlighter CSS styles.
	wp_register_style(
		'syntax-highlight-style-default', 
		plugins_url('sunlight/themes/sunlight.default.css', __FILE__)
	);
	wp_enqueue_style('syntax-highlight-style-default');
}

// Execute the plugin shortcode handler registration on page initialization.
add_action('init', 'github_repo_content_register_shortcode');

// Execute the file (JS, CSS) dependencies registration on init.
add_action('init', 'github_repo_content_register_dependencies');
?>