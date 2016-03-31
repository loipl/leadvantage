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
if (!class_exists ("c_ws_widget__ad_codes_menu_pages"))
	{
		class c_ws_widget__ad_codes_menu_pages
			{
				/*
				Function that saves all options from any page.
				Options can also be passed in directly.
					Can also be self-verified.
				*/
				public static function update_all_options ($new_options = FALSE, $verified = FALSE, $update_other = TRUE, $display_notices = TRUE, $enqueue_notices = FALSE, $request_refresh = FALSE)
					{
						do_action ("ws_widget__ad_codes_before_update_all_options", get_defined_vars ()); /* If you use this Hook, be sure to use `wp_verify_nonce()`. */
						/**/
						if ($verified || (($nonce = $_POST["ws_widget__ad_codes_options_save"]) && wp_verify_nonce ($nonce, "ws-widget--ad-codes-options-save")))
							{
								$options = $GLOBALS["WS_WIDGET__"]["ad_codes"]["o"]; /* Here we get all of the existing options. */
								$new_options = (is_array ($new_options)) ? $new_options : ((!empty ($_POST)) ? stripslashes_deep ($_POST) : array ());
								$new_options = c_ws_widget__ad_codes_utils_strings::trim_deep ($new_options);
								/**/
								foreach ((array)$new_options as $key => $value) /* Looking for relevant keys. */
									if (preg_match ("/^" . preg_quote ("ws_widget__ad_codes_", "/") . "/", $key))
										/**/
										if ($key === "ws_widget__ad_codes_configured") /* Configured. */
											{
												update_option ("ws_widget__ad_codes_configured", $value);
												$GLOBALS["WS_WIDGET__"]["ad_codes"]["c"]["configured"] = $value;
											}
										else /* Place this option into the array. Remove ws_widget__ad_codes_. */
											{
												(is_array ($value)) ? array_shift ($value) : null; /* Arrays should be padded. */
												$key = preg_replace ("/^" . preg_quote ("ws_widget__ad_codes_", "/") . "/", "", $key);
												$options[$key] = $value; /* Overriding a possible existing option. */
											}
								/**/
								$options["options_version"] = (string)($options["options_version"] + 0.001);
								$options = ws_widget__ad_codes_configure_options_and_their_defaults ($options);
								/**/
								eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
								do_action ("ws_widget__ad_codes_during_update_all_options", get_defined_vars ());
								unset ($__refs, $__v); /* Unset defined __refs, __v. */
								/**/
								update_option ("ws_widget__ad_codes_options", $options);
								/**/
								if (($display_notices === true || in_array ("success", (array)$display_notices)) && ($notice = '<strong>Options saved.' . (($request_refresh) ? ' Please <a href="' . esc_attr ($_SERVER["REQUEST_URI"]) . '">refresh</a>.' : '') . '</strong>'))
									($enqueue_notices === true || in_array ("success", (array)$enqueue_notices)) ? c_ws_widget__ad_codes_admin_notices::enqueue_admin_notice ($notice, "*:*") : c_ws_widget__ad_codes_admin_notices::display_admin_notice ($notice);
								/**/
								$updated_all_options = true; /* Flag indicating this routine was indeed processed. */
							}
						/**/
						do_action ("ws_widget__ad_codes_after_update_all_options", get_defined_vars ());
						/**/
						return $updated_all_options; /* Return status update. */
					}
				/*
				Add options, details, info, etc.
				Attach to: add_action("admin_menu");
				*/
				public static function admin_options ()
					{
						do_action ("ws_widget__ad_codes_before_add_admin_options", get_defined_vars ());
						/**/
						if (!c_ws_widget__ad_codes_utils_conds::is_multisite_farm ()) /* NOT on Multisite Farms. */
							{
								add_filter ("plugin_action_links", "c_ws_widget__ad_codes_menu_pages::_add_details_link", 10, 2);
								/**/
								if (apply_filters ("ws_widget__ad_codes_during_add_admin_options_create_menu_items", true, get_defined_vars ()))
									{
										if (apply_filters ("ws_widget__ad_codes_during_add_admin_options_add_info_page", true, get_defined_vars ()))
											add_theme_page ("Ad Codes / Widget Information", "Ad Codes Widget", "edit_plugins", "ws-widget--ad-codes-info", "c_ws_widget__ad_codes_menu_pages::info_page");
									}
							}
						/**/
						do_action ("ws_widget__ad_codes_after_add_admin_options", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				A sort of callback function to add the details link.
				*/
				public static function _add_details_link ($links = array (), $file = "")
					{
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("_ws_widget__ad_codes_before_add_details_link", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if (preg_match ("/" . preg_quote ($file, "/") . "$/", $GLOBALS["WS_WIDGET__"]["ad_codes"]["l"]) && is_array ($links))
							{
								$details = '<a href="' . esc_attr (admin_url ("/admin.php?page=ws-widget--ad-codes-info")) . '">Details</a>';
								array_unshift ($links, $details);
								/**/
								eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
								do_action ("_ws_widget__ad_codes_during_add_details_link", get_defined_vars ());
								unset ($__refs, $__v); /* Unset defined __refs, __v. */
							/**/
							}
						/**/
						return apply_filters ("_ws_widget__ad_codes_add_details_link", $links, get_defined_vars ());
					}
				/*
				Add scripts to admin panels.
				Attach to: add_action("admin_print_scripts");
				*/
				public static function add_admin_scripts ()
					{
						do_action ("ws_widget__ad_codes_before_add_admin_scripts", get_defined_vars ());
						/**/
						if ($_GET["page"] && preg_match ("/ws-widget--ad-codes-/", $_GET["page"]))
							{
								wp_enqueue_script ("jquery");
								wp_enqueue_script ("thickbox");
								wp_enqueue_script ("media-upload");
								wp_enqueue_script ("jquery-ui-core");
								wp_enqueue_script ("jquery-sprintf", $GLOBALS["WS_WIDGET__"]["ad_codes"]["c"]["dir_url"] . "/includes/jquery/jquery.sprintf/jquery.sprintf-min.js", array ("jquery"), c_ws_widget__ad_codes_utilities::ver_checksum ());
								wp_enqueue_script ("jquery-json-ps", $GLOBALS["WS_WIDGET__"]["ad_codes"]["c"]["dir_url"] . "/includes/jquery/jquery.json-ps/jquery.json-ps-min.js", array ("jquery"), c_ws_widget__ad_codes_utilities::ver_checksum ());
								wp_enqueue_script ("jquery-ui-effects", $GLOBALS["WS_WIDGET__"]["ad_codes"]["c"]["dir_url"] . "/includes/jquery/jquery.ui-effects/jquery.ui-effects-min.js", array ("jquery", "jquery-ui-core"), c_ws_widget__ad_codes_utilities::ver_checksum ());
								wp_enqueue_script ("ws-widget--ad-codes-menu-pages", site_url ("/?ws_widget__ad_codes_menu_pages_js=" . urlencode (mt_rand ())), array ("jquery", "thickbox", "media-upload", "jquery-sprintf", "jquery-json-ps", "jquery-ui-core", "jquery-ui-effects"), c_ws_widget__ad_codes_utilities::ver_checksum ());
								/**/
								do_action ("ws_widget__ad_codes_during_add_admin_scripts", get_defined_vars ());
							}
						/**/
						do_action ("ws_widget__ad_codes_after_add_admin_scripts", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Add styles to admin panels.
				Attach to: add_action("admin_print_styles");
				*/
				public static function add_admin_styles ()
					{
						do_action ("ws_widget__ad_codes_before_add_admin_styles", get_defined_vars ());
						/**/
						if ($_GET["page"] && preg_match ("/ws-widget--ad-codes-/", $_GET["page"]))
							{
								wp_enqueue_style ("thickbox");
								wp_enqueue_style ("ws-widget--ad-codes-menu-pages", site_url ("/?ws_widget__ad_codes_menu_pages_css=" . urlencode (mt_rand ())), array ("thickbox"), c_ws_widget__ad_codes_utilities::ver_checksum (), "all");
								/**/
								do_action ("ws_widget__ad_codes_during_add_admin_styles", get_defined_vars ());
							}
						/**/
						do_action ("ws_widget__ad_codes_after_add_admin_styles", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Function for building the info page.
				*/
				public static function info_page ()
					{
						do_action ("ws_widget__ad_codes_before_info_page", get_defined_vars ());
						/**/
						include_once dirname (dirname (__FILE__)) . "/menu-pages/info.inc.php";
						/**/
						do_action ("ws_widget__ad_codes_after_info_page", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>