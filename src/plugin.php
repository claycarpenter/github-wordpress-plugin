<?php
/*
 Plugin Name: GitHub Repo Content Embedder
 Plugin URI: http://flightlessflyer.pinguinotech.com/github-wordpress-plugin
 Description: Embeds content held in a GitHub repo.
 Version: 0.1
 Author: Clay Carpenter
 */

/*
 * This file contains the core plugin code, especially the
 * GitHubRepoContentRetriever class.
 */
require_once ('php/github_repo_content_retriever.php');

/*
 * Instantiate the core plugin class. The sole argument will configure the
 * plugin with the correct base URL for this plugin. That knowledge allows the
 * plugin to accurately point to (JS, CSS) resource files.
 */
new GitHubRepoContentRetriever( plugin_dir_url( __FILE__ ) );
?>
