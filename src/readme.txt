=== GetGit ===
Contributors: claycarpenter
Tags: github, embed, code, snippet, repositories
Requires at least: 3.5.1
Tested up to: 3.5.1
Stable tag: 0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Retrieves full files or snippets from a public GitHub repository, displaying the code in your blog with syntax highlighting.


== Description ==

This plugin can retrieve either the full content or a snippet from a file hosted in a public GitHub repository. The code will be displayed with syntax highlighting and line numbers.

Syntax highlighting is provided by the [Sunlight JS](http://sunlightjs.com/ "Sunlight JS") syntax highlighter. Many thanks to those involved in that project!

= Usage = 

This plugin registers a shortcode that allows for embedding GitHub repo content into blog posts. To embed content into your post, use the `github` shortcode like so:

`[github userid="[target user id]" repoid="[target repo id]" path="[path to content]" language="[language of target content]"]`

Attributes used by the `github` shortcode:
* **userid** - The user ID of the repository owner.
* **repoid** - The ID of the repository that contains the target content.
* **path** - The full path to the content. This path should start at the repository root, not inclusive of the leading `/`, and terminate with the name of the target content's filename.
* **language** - The programming language of the code contained in the content. This information will be passed to the syntax highlighter. The value must match one of the recognized values for the Sunlight JS engine. For a list of values, see the [Sunlight documentation](http://sunlightjs.com/docs.html)
* **startloc** - Optional. The starting line of code to display in the code snippet. If this attribute is ommitted, the first line of retrieved file will be the first line of code displayed in the snippet.
* **stoploc** - Optional. The final line of code (inclusive) to display in the code snippet. If this attribute is ommitted, the final line of content shown in the snippet will be the last line of the retrieved file.

= Examples = 



== Installation ==

To install this plugin:

1. Upload the plugin archive ZIP (`getgit.zip`) to the `/wp-content/plugins/` directory.
1. Activate the GetGit plugin through the 'Plugins' menu in WordPress.
1. Add the github shortcode to your blog posts, as outlined in the Description section.


== Frequently Asked Questions ==

= Does this plugin support configurable syntax highlighting? =

Currently, the syntax highlighting style choice is not user changeable. However, such functionality is expected in future releases.


== Screenshots ==


== Changelog ==

= 0.1 =
* Initial public release.
* Supports basic syntax highlighting, line numbers.
* Supports showing only snippets of files, rather than the full file content.
* Uses Sunlight JS syntax highlighter, [v1.17](http://svn.tommymontgomery.com/sunlight/tags/1.17/CHANGELOG).


== Upgrade Notice ==

