=== Super Tags Widget ===

Version: 110709
Stable tag: 110709
Framework: WS-W-110523

SSL Compatible: yes
WordPress Compatible: yes
WP Multisite Compatible: yes
Multisite Blog Farm Compatible: yes

Tested up to: 3.2.1
Requires at least: 3.2
Requires: WordPress® 3.2+, PHP 5.2.3+

Copyright: © 2009 WebSharks, Inc.
License: GNU General Public License
Contributors: WebSharks, PriMoThemes
Author URI: http://www.primothemes.com/
Author: PriMoThemes.com / WebSharks, Inc.
Donate link: http://www.primothemes.com/donate/

Plugin Name: Super Tags Widget
Forum URI: http://www.primothemes.com/forums/viewforum.php?f=7
Privacy URI: http://www.primothemes.com/about/privacy-policy/
Plugin URI: http://www.primothemes.com/post/product/super-tags-widget/
Description: Provides a customizable alternative to the default WordPress® Tag Cloud widget.
Tags: widget, widgets, tag, cloud, tag cloud, super, tags, super tags, categories, taxonomy, posts, options panel included, websharks framework, w3c validated code, multi widget support, includes extensive documentation, highly extensible

This widget makes it possible for you to fully customize a Tag Cloud ( or multiple Tag Clouds ) to meet your specific needs.

== Installation ==

1. Upload the `/super-tags-widget` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress®.
3. Navigate to `Appearance->Widgets` and add the widget.

***Special instructions for Multisite Blog Farms:** If you're installing this plugin on WordPress® with Multisite/Networking enabled, and you run a Blog Farm ( i.e. you give away free blogs to the public ); please `define("MULTISITE_FARM", true);` in your /wp-config.php file. When this plugin is running on a Multisite Blog Farm, it will mutate itself ( including its menus ) for safe compatiblity with Blog Farms. You don't need to do this unless you run a Blog Farm. If you're running the standard version of WordPress®, or you run WordPress® Multisite to host your own sites, you can ( and should ) skip this step.*

== Description ==

The Super Tags plugin, is a customizable alternative to the default WordPress® Tag Cloud widget. This widget makes it possible for you to fully customize a Tag Cloud ( or multiple Tag Clouds ) to meet your specific needs. It takes the same options as the `wp_tag_cloud()` function. You can specify a number of configurable parameters, such as: minimum font size, maximum font size, maximum tags to display, format ( `flat|list` ), separators, order, sort method, specific tags to include, specific tags to exclude, or even a specific taxonomy ( `post_tag|category|link_category` ).

== Screenshots ==

1. Super Tags Widget / Screenshot #1

== Frequently Asked Questions ==

= Where can I learn more about the option parameters? =
This plugin takes the same options as the `wp_tag_cloud()` function. Docs are found [here](http://codex.wordpress.org/Template_Tags/wp_tag_cloud).

== Changelog ==

= 110709 =
* Routine maintenance. No signifigant changes.

= 110708 =
* Routine maintenance. No signifigant changes.
* Compatibility with WordPress v3.2.

= 110523 =
* **Versioning.** Starting with this release, versions will follow this format: `yymmdd`. The version for this release is: `110523`.
* Routine maintenance. No signifigant changes.

= 1.4.2 =
* Routine maintenance. No signifigant changes.

= 1.4.1 =
* Routine maintenance. No signifigant changes.

= 1.4 =
* Framework updated; general cleanup.
* Updated with static class methods. This plugin now uses PHP's SPL autoload functionality to further optimize all of its routines.
* Optimizations. Further internal optimizations applied through configuration checksums that allow this plugin to load with even less overhead now.

= 1.3.7 =
* Framework updated; general cleanup.
* Updated for compatibility with WordPress® 3.1.

= 1.3.6 =
* Framework updated; general cleanup.

= 1.3.5 =
* Framework updated; general cleanup.

= 1.3.4 =
* Framework updated; general cleanup.
* Updated minimum requirements to WordPress® 3.0.

= 1.3.3 =
* Framework updated to WS-W-3.0.

= 1.3.2 =
* Framework updated to WS-W-2.3.

= 1.3.1 =
* Updated minimum requirements to WordPress® 2.9.2.
* Framework updated to WS-W-2.2.

= 1.3 =
* WebSharks Framework for Widgets has been updated to W-2.1.
* Stable tag updated in support of tagged releases within the repository at WordPress.org.

= 1.2 =
* Re-organized core framework. Updated to: W-2.0.
* Updated to support WP 2.9+.

= 1.1 =
* Updated documentation.
* Replaced deprecated `split()` function with `preg_split()`.

= 1.0 =
* Initial release.