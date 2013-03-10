<?php
/*
 Plugin Name: GetGit
 Plugin URI: http://flightlessflyer.pinguinotech.com/github-wordpress-plugin
 Description: Embeds content stored in a public GitHub repository.
 Version: 0.1
 Author: Clay Carpenter
 */

/*
 * Include a debug logger.
 *
 * To enable logging, set the Logger's is_enabled property to TRUE.
 */
require_once ('php/logger.php');
Logger::$is_enabled = FALSE;

/*
 * This file contains the core plugin code, especially the
 * GitHubRepoContentRetriever class.
 */
require_once ('php/github_repo_content_retriever.php');

/*
 * This file contains the options page manangement code (registration,
 * definition, creation).
 */
require_once ('php/options_page_manager.php');

/*
 * Instantiate the core plugin class. The sole argument will configure the
 * plugin with the correct base URL for this plugin. That knowledge allows the
 * plugin to accurately point to (JS, CSS) resource files.
 */
new GitHubRepoContentRetriever( plugin_dir_url( __FILE__ ) );

if ( is_admin( ) ) {
	new OptionsPageManager( );
}
?>
