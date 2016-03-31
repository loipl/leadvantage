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
if (!class_exists ("c_ws_theme__custom_files"))
	{
		class c_ws_theme__custom_files
			{
				/*
				Add the custom style sheet to the theme.
				Attach to: add_action("wp_print_styles");
				*/
				public static function add_custom_styles ()
					{
						do_action ("ws_theme__before_add_custom_styles", get_defined_vars ());
						/**/
						if ($GLOBALS["WS_THEME__"]["o"]["custom_css"] && !is_admin ())
							{
								wp_enqueue_style ("ws-theme--custom", site_url ("/?ws_theme__custom_css=1"), array (), c_ws_theme__utilities::ver_checksum (), "all");
								/**/
								do_action ("ws_theme__during_add_custom_styles", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__after_add_custom_styles", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Add the custom JavaScript file to the theme.
				Attach to: add_action("wp_print_scripts");
				*/
				public static function add_custom_scripts ()
					{
						do_action ("ws_theme__before_add_custom_scripts", get_defined_vars ());
						/**/
						if ($GLOBALS["WS_THEME__"]["o"]["custom_js"] && !is_admin ())
							{
								wp_enqueue_script ("jquery");
								wp_enqueue_script ("ws-theme--custom", site_url ("/?ws_theme__custom_js=1"), array ("jquery"), c_ws_theme__utilities::ver_checksum ());
								/**/
								do_action ("ws_theme__during_add_custom_scripts", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__after_add_custom_scripts", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>