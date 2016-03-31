=== Ad Squares Widget ===

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

Plugin Name: Ad Squares Widget
Forum URI: http://www.primothemes.com/forums/viewforum.php?f=9
Privacy URI: http://www.primothemes.com/about/privacy-policy/
Plugin URI: http://www.primothemes.com/post/product/ad-squares-widget/
Description: The Ad Squares Widget makes it possible for you to display 125x125 ad squares ( or some other configurable size ad, advertisement, banner ) into a widget-ready bar for WordPress®. It supports AdSense®, Javascript, XHTML and more.
Tags: widget, widgets, ad codes, ad squares, square, squares, ads, adsense, google, sponsors, advertise, advertisements, banners, ad networks, banner rotation, options panel included, websharks framework, w3c validated code, multi widget support, includes extensive documentation, highly extensible

Supports AdSense®, Javascript, XHTML and PHP code. This widget makes it possible for you to display 125x125 ad squares ( or some other configurable size ad, advertisement, banner ) into a widget-ready bar for WordPress®. PHP code can be disabled for security when its installed on a Multisite Blog Farm.

== Installation ==

1. Upload the `/ad-squares-widget` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress®.
3. Navigate to `Appearance->Widgets` and add the widget.

***Special instructions for Multisite Blog Farms:** If you're installing this plugin on WordPress® with Multisite/Networking enabled, and you run a Blog Farm ( i.e. you give away free blogs to the public ); please `define("MULTISITE_FARM", true);` in your /wp-config.php file. When this plugin is running on a Multisite Blog Farm, it will mutate itself ( including its menus ) for safe compatiblity with Blog Farms. You don't need to do this unless you run a Blog Farm. If you're running the standard version of WordPress®, or you run WordPress® Multisite to host your own sites, you can ( and should ) skip this step.*

== Description ==

This widget makes it possible for you to display 125x125 ad squares ( or some other configurable size ) into a widget-ready bar for WordPress®. You can have just 2 squares, or as many as 8 running together. It also supports multi-widget options. So technically you could have even more than 8 if you add it to a bar more than once. Each ad square accepts any HTML/JavaScript code, so it will work with standard affiliate ads, or even with ad network codes. You can also configure the widget to shuffle the positioning of your squares if you like.

The Ad Squares widget supports embedded PHP code too. If you know a little PHP scripting, you could add conditionals to the code that you place into this widget. For example, if you wanted to show different ads based on the category that is currently being displayed on your blog, you could do something like this:<!--more-->

	<?php if(is_category("green-lizards")): ?>

	  insert green lizard ad campaign here

	<?php elseif(is_category("purple-socks")): ?>

	  insert purple socks ad here

	<?php else: ?>

	  insert default ad code here

	<?php endif; ?>

== Screenshots ==

1. Ad Squares Widget / Screenshot #1

== Frequently Asked Questions ==

= What is the best size ( width x height ) to use? =
The recommended size is 125x125, but it does not have to be that exact size. Just be sure to stick with the same size in each square so things remain symmetrical.

= What type of code can I put into the ad squares? =
Feel free to paste any type of ad code into the squares. AdSense®, XHTML, PHP, IFrame, JavaScript, whatever.

== Changelog ==

= 110709 =
* Routine maintenance. No signifigant changes.

= 110708 =
* Routine maintenance. No signifigant changes.
* Compatibility with WordPress v3.2.

= 110523 =
* **Versioning.** Starting with this release, versions will follow this format: `yymmdd`. The version for this release is: `110523`.
* Routine maintenance. No signifigant changes.

= 2.0.2 =
* Routine maintenance. No signifigant changes.

= 2.0.1 =
* Routine maintenance. No signifigant changes.

= 2.0 =
* Framework updated; general cleanup.
* Updated with static class methods. This plugin now uses PHP's SPL autoload functionality to further optimize all of its routines.
* Optimizations. Further internal optimizations applied through configuration checksums that allow this plugin to load with even less overhead now.

= 1.9.8 =
* Framework updated; general cleanup.
* Updated for compatibility with WordPress® 3.1.

= 1.9.7 =
* Framework updated; general cleanup.

= 1.9.6 =
* Framework updated; general cleanup.

= 1.9.5 =
* Framework updated; general cleanup.
* Updated minimum requirements to WordPress® 3.0.

= 1.9.4 =
* Framework updated to WS-W-3.0.

= 1.9.3 =
* Framework updated to WS-W-2.3.

= 1.9.2 =
* Updated minimum requirements to WordPress® 2.9.2.
* Framework updated to WS-W-2.2.

= 1.9.1 =
* Stable tag updated in support of tagged releases within the repository at WordPress.org.

= 1.9 =
* WebSharks Framework for Widgets has been updated to W-2.1.

= 1.8 =
* Re-organized core framework. Updated to: W-2.0.
* Updated to support WP 2.9+.

= 1.7 =
* Added some additional documentation.
* Added support for embedded PHP code.
* Changed the rotation tag format to: `<!--rotate-->`.
* Replaced deprecated `split()` function with `preg_split()`.

= 1.6 =
* Updated to provide better instruction on the options panel.

= 1.5 =
* Added default placeholders when no ad codes are present.

= 1.4 =
* Code re-organized / optimized for better performance.

= 1.3 =
* Added a feature that allows you to select 2 to 8 squares per widget.

= 1.2 =
* Updated to support ad shuffling / rotations.

= 1.1 =
* Updated to support WordPress® 2.8.4.

= 1.0 =
* Initial release.