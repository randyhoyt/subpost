=== Plugin Name ===
Contributors: randyhoyt
Tags: custom post types
Requires at least: 3.4
Tested up to: 3.4.2
Stable tag: VERSION_NUMBER
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides a number of helpers functions to aid in the creation of subordinate relationships between two post types.

== Description ==

This plugin provides a number of helpers functions to aid in the creation of subordinate relationships between two post types, one-to-many relationships in which posts of one type are children of another. (For example, a Job Listing might have a Company as a parent; a Meeting might have a Group.) With this plugin activated, developers can easily write code in their plugins and themes to register two post types related in this manner.

For an example on how to use the functions this plugin makes available, check out my Groups and Meetings plugin on Github:
<ul><li>https://github.com/randyhoyt/mythsoc-groups</li></ul>

== Changelog ==

= 0.2 =
* 'show_in_menu' argument is now honored for the child post type.

= 0.1.1 =
* 'post' can now be specified as the parent post type.

= 0.1 =
* Initial version.