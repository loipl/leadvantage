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
	exit ("Do not access this file directly.");
/**/
if (!class_exists ("c_ws_theme__menu_pages"))
	{
		class c_ws_theme__menu_pages
			{
				/*
				Function for saving all theme options from any page.
				*/
				public static function update_all_options ($new_options = FALSE, $verified = FALSE, $update_other = TRUE, $display_notices = TRUE, $enqueue_notices = FALSE, $request_refresh = FALSE)
					{
						do_action ("ws_theme__before_update_all_options", get_defined_vars ()); /* If you use this Hook, be sure to use `wp_verify_nonce()`. */
						/**/
						if ($verified || (($nonce = $_POST["ws_theme__options_save"]) && wp_verify_nonce ($nonce, "ws-theme--options-save")))
							{
								$options = $GLOBALS["WS_THEME__"]["o"]; /* Here we get all of the existing options. */
								$var_name = $GLOBALS["WS_THEME__"]["c"]["var_name"]; /* Get the variable name. */
								$new_options = (is_array ($new_options)) ? $new_options : ((!empty ($_POST)) ? stripslashes_deep ($_POST) : array ());
								$new_options = c_ws_theme__utils_strings::trim_deep ($new_options);
								/**/
								foreach ((array)$new_options as $key => $value) /* Find relevant keys. */
									if (preg_match ("/^" . preg_quote ("ws_theme__", "/") . "/", $key))
										/**/
										if ($key === "ws_theme__color") /* Special theme color key. */
											{
												update_option ("ws_theme__" . $var_name . "_color", $value);
												$GLOBALS["WS_THEME__"]["c"]["color"] = $value;
											}
										else if ($key === "ws_theme__configured") /* Configured? */
											{
												update_option ("ws_theme__" . $var_name . "_configured", $value);
												$GLOBALS["WS_THEME__"]["c"]["configured"] = $value;
											}
										else /* Place this option into the array. Remove ws_theme__. */
											{
												(is_array ($value)) ? array_shift ($value) : null;
												$key = preg_replace ("/^" . preg_quote ("ws_theme__", "/") . "/", "", $key);
												$options[$key] = $value; /* Overrides existing. */
											}
								/**/
								$options["options_version"] = (string)($options["options_version"] + 0.001);
								$options = ws_theme__configure_options_and_their_defaults ($options);
								/**/
								eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
								do_action ("ws_theme__during_update_all_options", get_defined_vars ());
								unset ($__refs, $__v); /* Unset defined __refs, __v. */
								/**/
								update_option ("ws_theme__" . $var_name . "_options", $options);
								/**/
								if (($display_notices === true || in_array ("success", (array)$display_notices)) && ($notice = '<strong>Options saved.' . (($request_refresh) ? ' Please <a href="' . esc_attr ($_SERVER["REQUEST_URI"]) . '">refresh</a>.' : '') . '</strong>'))
									($enqueue_notices === true || in_array ("success", (array)$enqueue_notices)) ? c_ws_theme__admin_notices::enqueue_admin_notice ($notice, "*:*") : c_ws_theme__admin_notices::display_admin_notice ($notice);
								/**/
								$updated_all_options = true; /* Flag indicating this routine was indeed processed. */
							}
						/**/
						do_action ("ws_theme__after_update_all_options", get_defined_vars ());
						/**/
						return $updated_all_options; /* Return status update. */
					}
				/*
				Add the options / this is where the theme will be customized.
				Attach to: add_action("admin_menu");
				*/
				public static function add_admin_options ()
					{
						do_action ("ws_theme__before_add_admin_options", get_defined_vars ());
						/**/
						if (apply_filters ("ws_theme__during_add_admin_options_create_menu_items", true, get_defined_vars ()))
							{
								if (apply_filters ("ws_theme__during_add_admin_options_add_theme_page", true, get_defined_vars ()))
									add_theme_page ("Theme Options", "Theme Options", "edit_themes", "theme-ws-theme--options", "c_ws_theme__menu_pages::options_page");
								/**/
								add_menu_page ("PriMo Theme", "PriMo Theme", "edit_themes", "ws-theme--options", "c_ws_theme__menu_pages::options_page", get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-favicon.png");
								add_submenu_page ("ws-theme--options", "Theme Options", "Theme Options", "edit_themes", "ws-theme--options", "c_ws_theme__menu_pages::options_page");
								/**/
								if (apply_filters ("ws_theme__during_add_admin_options_add_info_page", true, get_defined_vars ()))
									if (!c_ws_theme__utils_conds::is_multisite_farm ()) /* Do NOT provide access to this menu page when running on a Multisite Farm. */
										add_submenu_page ("ws-theme--options", "Theme Info", "Theme Info", "edit_themes", "ws-theme--options-info", "c_ws_theme__menu_pages::info_page");
								/**/
								if (apply_filters ("ws_theme__during_add_admin_options_add_customization_page", true, get_defined_vars ()))
									add_submenu_page ("ws-theme--options", "Customization", "Customization", "edit_themes", "ws-theme--options-customization", "c_ws_theme__menu_pages::customization_page");
							}
						/**/
						do_action ("ws_theme__after_add_admin_options", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Add scripts to admin panels.
				Attach to: add_action("admin_print_scripts");
				*/
				public static function add_admin_scripts ()
					{
						do_action ("ws_theme__before_add_admin_scripts", get_defined_vars ());
						/**/
						if ($_GET["page"] && preg_match ("/ws-theme--/", $_GET["page"]))
							{
								wp_enqueue_script ("jquery");
								wp_enqueue_script ("thickbox");
								wp_enqueue_script ("media-upload");
								wp_enqueue_script ("jquery-ui-core");
								wp_enqueue_script ("jquery-sprintf", get_bloginfo ("template_url") . "/includes/jquery/jquery.sprintf/jquery.sprintf-min.js", array ("jquery"), c_ws_theme__utilities::ver_checksum ());
								wp_enqueue_script ("jquery-json-ps", get_bloginfo ("template_url") . "/includes/jquery/jquery.json-ps/jquery.json-ps-min.js", array ("jquery"), c_ws_theme__utilities::ver_checksum ());
								wp_enqueue_script ("jquery-ui-effects", get_bloginfo ("template_url") . "/includes/jquery/jquery.ui-effects/jquery.ui-effects-min.js", array ("jquery", "jquery-ui-core"), c_ws_theme__utilities::ver_checksum ());
								wp_enqueue_script ("ws-theme--menu-pages", site_url ("/?ws_theme__menu_pages_js=" . urlencode (mt_rand ())), array ("jquery", "thickbox", "media-upload", "jquery-sprintf", "jquery-json-ps", "jquery-ui-core", "jquery-ui-effects"), c_ws_theme__utilities::ver_checksum ());
								/**/
								do_action ("ws_theme__during_add_admin_scripts", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__after_add_admin_scripts", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Add styles to admin panels.
				Attach to: add_action("admin_print_styles");
				*/
				public static function add_admin_styles ()
					{
						do_action ("ws_theme__before_add_admin_styles", get_defined_vars ());
						/**/
						if ($_GET["page"] && preg_match ("/ws-theme--/", $_GET["page"]))
							{
								wp_enqueue_style ("thickbox");
								wp_enqueue_style ("ws-theme--menu-pages", site_url ("/?ws_theme__menu_pages_css=" . urlencode (mt_rand ())), array ("thickbox"), c_ws_theme__utilities::ver_checksum (), "all");
								/**/
								do_action ("ws_theme__during_add_admin_styles", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__after_add_admin_styles", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Function for building and handling the theme options page.
				*/
				public static function options_page ()
					{
						do_action ("ws_theme__before_options_page", get_defined_vars ());
						/**/
						c_ws_theme__menu_pages::update_all_options ();
						/**/
						include_once TEMPLATEPATH . "/includes/menu-pages/options.inc.php";
						/**/
						do_action ("ws_theme__after_options_page", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Function for building and handling the theme info page.
				*/
				public static function info_page ()
					{
						do_action ("ws_theme__before_info_page", get_defined_vars ());
						/**/
						include_once TEMPLATEPATH . "/includes/menu-pages/info.inc.php";
						/**/
						do_action ("ws_theme__after_info_page", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Function for building and handling the theme customization page.
				*/
				public static function customization_page ()
					{
						do_action ("ws_theme__before_customization_page", get_defined_vars ());
						/**/
						include_once TEMPLATEPATH . "/includes/menu-pages/cus.inc.php";
						/**/
						do_action ("ws_theme__after_customization_page", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>