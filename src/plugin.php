<?php
/*
 Plugin Name: GitHub Repo Content Embedder
 Plugin URI: http://flightlessflyer.pinguinotech.com/github-wordpress-plugin
 Description: Embeds content held in a GitHub repo.
 Version: 0.0.1a
 Author: Clay Carpenter
 */

require_once ('php/github_repo_content_retriever.php');

// Instantiate the core plugin class.
new GitHubRepoContentRetriever(plugin_dir_url(__FILE__));
?>
