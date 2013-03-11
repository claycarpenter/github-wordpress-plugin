<?php
/**
 * This file contains the content caching support.
 *
 * This effectively acts as a simply proxy to the WordPress Transients API.
 */

/**
 * Require logging support for debug purposes.
 */
require_once ('logger.php');

/**
 * Acts as a simple proxy for the WordPress Transients API.
 *
 * See the get_content method for more details.
 *
 * @author Clay Carpenter
 */
class ContentCache {
	/**
	 * Get the content specified by the given content attributes.
	 *
	 * Pulls the content from either the cache or directly from the GitHub repo. If
	 * pulled from the GitHub repo, the content is cached locally for a short time.
	 *
	 * Cached content is stored under a key that combines the content's identifying
	 * attributes (user ID, repo ID, content path) into a single string, and then
	 * creates a checksum from that string. All keys are prefix with the name of the
	 * plugin in order to avoid (any unlikely) Transient key conflicts.
	 *
	 * All data is stored in base64-encoded format (which currently happens to be the
	 * same format it is delivered in by GitHub's APIs).
	 *
	 * @param 	string	$user_id		The id of the GitHub user who owns the repository.
	 *
	 * @param	string	$repo_id		The id of the GitHub repository.
	 *
	 * @param	string	$content_path	The path (in GitHub) to the content. This is
	 * relative to the GitHub repo root.
	 *
	 * @return string					Raw content targeted by the provided content attributes.
	 */
	public static function get_content( $user_id, $repo_id, $content_path ) {
		$cache_key = self::get_cache_key( $user_id, $repo_id, $content_path );
		Logger::log_debug( "Looking for cached content under key: {$cache_key}" );

		$cache_content = get_transient( $cache_key );

		if ( false === $cache_content ) {
			// Construct the URL to the GitHub content.
			$content_url = "https://api.github.com/repos/{$user_id}/{$repo_id}/contents/{$content_path}";

			Logger::log_debug( "Retrieving URL: {$content_url}" );

			// Pull the content down.
			$content_request_result = wp_remote_get( $content_url );

			// The GitHub APIs will return a JSON-formatted response. Parse the body to find
			// the file content.
			$json_result = json_decode( $content_request_result[ 'body' ] );

			$cache_content = $json_result -> content;

			// The file content is transfered as base64-encoded data.
			// Sanitize the content before being persisted to the cache..
			$cache_content = sanitize_text_field( $cache_content );

			// Push content into the cache.
			// Set the default cache expire lifetime to one hour (60 * 60s).
			set_transient( $cache_key, $cache_content, 60 * 60 );
		}

		// All content in the cache is stored as sanitized base64-encoded data.
		// Decode the data in order to get the raw content for embedding.
		$content = base64_decode( $cache_content );

		return $content;
	}

	/**
	 * Creates a unique key for the given combination of user ID, repo ID, and
	 * content path.
	 *
	 * This key is composed by an MD5 checksum, prefixed with the plugin name.
	 *
	 * @param 	string	$user_id		The id of the GitHub user who owns the repository.
	 *
	 * @param	string	$repo_id		The id of the GitHub repository.
	 *
	 * @param	string	$content_path	The path (in GitHub) to the content. This is
	 * relative to the GitHub repo root.
	 *
	 * @return string					A unique key that can identify this content within the
	 * Transient cache.
	 */
	private static function get_cache_key( $user_id, $repo_id, $content_path ) {
		$separator = "_";
		$content_attributes_id = $user_id . $separator . $repo_id . $separator . $content_path;

		$cache_key = md5( 'getgit' . $separator . $content_attributes_id );

		return $cache_key;
	}

}
?>