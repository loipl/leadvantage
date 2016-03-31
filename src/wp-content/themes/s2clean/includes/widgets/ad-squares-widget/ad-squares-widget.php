<?php
/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
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
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
/*
Define versions.
*/
@define ("WS_WIDGET__AD_SQUARES_VERSION", "110709");
@define ("WS_WIDGET__AD_SQUARES_MIN_PHP_VERSION", "5.2.3");
@define ("WS_WIDGET__AD_SQUARES_MIN_WP_VERSION", "3.2");
@define ("WS_WIDGET__AD_SQUARES_MIN_PRO_VERSION", "110709");
/*
Compatibility checks.
*/
if (version_compare (PHP_VERSION, WS_WIDGET__AD_SQUARES_MIN_PHP_VERSION, ">=") && version_compare (get_bloginfo ("version"), WS_WIDGET__AD_SQUARES_MIN_WP_VERSION, ">=") && !isset ($GLOBALS["WS_WIDGET__"]["ad_squares"]))
	{
		$GLOBALS["WS_WIDGET__"]["ad_squares"]["l"] = __FILE__;
		/*
		Hook before loaded.
		*/
		do_action("ws_widget__ad_squares_before_loaded");
		/*
		System configuraton.
		*/
		include_once dirname (__FILE__) . "/includes/syscon.inc.php";
		/*
		Hooks and filters.
		*/
		include_once dirname (__FILE__) . "/includes/hooks.inc.php";
		/*
		Hook after system config & hooks are loaded.
		*/
		do_action("ws_widget__ad_squares_config_hooks_loaded");
		/*
		Load a possible Pro module, if/when available.
		*/
		if (apply_filters ("ws_widget__ad_squares_load_pro", true) && file_exists (dirname (__FILE__) . "-pro/pro-module.php"))
			include_once dirname (__FILE__) . "-pro/pro-module.php";
		/*
		Configure options and their defaults now.
		*/
		ws_widget__ad_squares_configure_options_and_their_defaults ();
		/*
		Function includes.
		*/
		include_once dirname (__FILE__) . "/includes/funcs.inc.php";
		/*
		Hook after loaded.
		*/
		do_action("ws_widget__ad_squares_after_loaded");
	}
else if (is_admin ()) /* Admin compatibility errors. */
	{
		if (!version_compare (PHP_VERSION, WS_WIDGET__AD_SQUARES_MIN_PHP_VERSION, ">="))
			{
				add_action ("all_admin_notices", create_function ('', 'echo \'<div class="error fade"><p>You need PHP v\' . WS_WIDGET__AD_SQUARES_MIN_PHP_VERSION . \'+ to use the Ad Squares widget.</p></div>\';'));
			}
		else if (!version_compare (get_bloginfo ("version"), WS_WIDGET__AD_SQUARES_MIN_WP_VERSION, ">="))
			{
				add_action ("all_admin_notices", create_function ('', 'echo \'<div class="error fade"><p>You need WordPress® v\' . WS_WIDGET__AD_SQUARES_MIN_WP_VERSION . \'+ to use the Ad Squares widget.</p></div>\';'));
			}
	}
?>