<?php
/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
/*
Define versions.
*/
@define ("WS_THEME__MIN_PHP_VERSION", "5.2.3");
@define ("WS_THEME__MIN_WP_VERSION", "3.2");
/*
Compatibility checks.
*/
if (version_compare (PHP_VERSION, WS_THEME__MIN_PHP_VERSION, ">=") && version_compare (get_bloginfo ("version"), WS_THEME__MIN_WP_VERSION, ">=") && !isset ($GLOBALS["WS_THEME__"]))
	{
		$GLOBALS["WS_THEME__"]["l"] = __FILE__;
		/*
		Hook before theme loaded.
		*/
		do_action("ws_theme__before_loaded");
		/*
		System configuraton.
		*/
		include_once TEMPLATEPATH . "/includes/syscon.inc.php";
		/*
		Hooks and Filters.
		*/
		include_once TEMPLATEPATH . "/includes/hooks.inc.php";
		/*
		Hook after system config & Hooks are loaded.
		*/
		do_action("ws_theme__config_hooks_loaded");
		/*
		Custom PHP includes will be loaded here automatically.
		See: [theme-folder]/includes/custom/custom-php-sample.php
		*/
		include_once TEMPLATEPATH . "/includes/cus.inc.php";
		/*
		Custom PHP code could also be inserted here.
		However, it is better to place your custom PHP files
		in the designated directory: [theme-folder]/includes/custom/
		---------------------------------------------------------------------- */
		# If you must, custom PHP code could go here.
		/*
		Configure options and their defaults now.
		*/
		ws_theme__configure_options_and_their_defaults ();
		/*
		Function includes.
		*/
		include_once TEMPLATEPATH . "/includes/funcs.inc.php";
		/*
		Include Shortcodes.
		*/
		include_once TEMPLATEPATH . "/includes/codes.inc.php";
		/*
		Plugin includes.
		*/
		include_once TEMPLATEPATH . "/includes/plugs.inc.php";
		/*
		Widget includes.
		*/
		include_once TEMPLATEPATH . "/includes/widgs.inc.php";
		/*
		Compatibility.
		*/
		if ($GLOBALS["WS_THEME__"]["compatible"] !== false)
			$GLOBALS["WS_THEME__"]["compatible"] = true;
		/*
		Hook indicating load with compatibility.
		*/
		if ($GLOBALS["WS_THEME__"]["compatible"])
			do_action("ws_theme__loaded");
		/*
		Hook after theme loaded.
		*/
		do_action("ws_theme__after_loaded");
	}
/*
Else handle incompatibilities.
*/
else /* Compatibility errors. Admin notice & wp_die(). */
	{
		if (!version_compare (PHP_VERSION, WS_THEME__MIN_PHP_VERSION, ">="))
			{
				$GLOBALS["WS_THEME__"]["compatibility-error"] = 'You need PHP v' . WS_THEME__MIN_PHP_VERSION . '+ to use this theme.';
				add_action ("all_admin_notices", create_function ('', 'echo \'<div class="error fade"><p>\' . $GLOBALS["WS_THEME__"]["compatibility-error"] . \'</p></div>\';'));
				(!is_admin () && !preg_match ("/\/wp-login\.php/", $_SERVER["REQUEST_URI"]) && !defined ("WP_INSTALLING")) ? add_action ("init", create_function ('', 'wp_die ($GLOBALS["WS_THEME__"]["compatibility-error"]);')) : null;
			}
		else if (!version_compare (get_bloginfo ("version"), WS_THEME__MIN_WP_VERSION, ">="))
			{
				$GLOBALS["WS_THEME__"]["compatibility-error"] = 'You need WordPress® v' . WS_THEME__MIN_WP_VERSION . '+ to use this theme.';
				add_action ("all_admin_notices", create_function ('', 'echo \'<div class="error fade"><p>\' . $GLOBALS["WS_THEME__"]["compatibility-error"] . \'</p></div>\';'));
				(!is_admin () && !preg_match ("/\/wp-login\.php/", $_SERVER["REQUEST_URI"]) && !defined ("WP_INSTALLING")) ? add_action ("init", create_function ('', 'wp_die ($GLOBALS["WS_THEME__"]["compatibility-error"]);')) : null;
			}
	}
?>