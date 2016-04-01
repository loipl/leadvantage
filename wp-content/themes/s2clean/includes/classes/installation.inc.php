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
if (!class_exists ("c_ws_theme__installation"))
	{
		class c_ws_theme__installation
			{
				/*
				Handles activation routines.
				Attach to: add_action("admin_init");
				*/
				public static function activate ()
					{
						global $pagenow; /* Need global reference. */
						/**/
						do_action ("ws_theme__before_activate", get_defined_vars ());
						/**/
						if (is_blog_admin () && $pagenow === "themes.php") /* On the right page? */
							{
								if ($_GET["activated"] && get_current_theme () === c_ws_theme__readmes::parse_readme_value ("Theme Name"))
									{
										(!get_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_color")) ? update_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_color", $GLOBALS["WS_THEME__"]["c"]["color"]) : null;
										(!is_numeric (get_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_configured"))) ? update_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_configured", "0") : null;
										(!is_array (get_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_notices"))) ? update_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_notices", array ()) : null;
										(!is_array (get_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_options"))) ? update_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_options", array ()) : null;
										/**/
										if (is_array ($GLOBALS["WS_PLUGIN__"]))
											foreach ($GLOBALS["WS_PLUGIN__"] as $plugin_key => $plugin_array)
												if (preg_match ("/^" . preg_quote ($GLOBALS["WS_THEME__"]["l"], "/") . "/", $plugin_array["l"]))
													if (is_callable ("c_ws_plugin__" . $plugin_key . "_installation::activate"))
														call_user_func ("c_ws_plugin__" . $plugin_key . "_installation::activate");
										/**/
										if (is_array ($GLOBALS["WS_WIDGET__"]))
											foreach ($GLOBALS["WS_WIDGET__"] as $widget_key => $widget_array)
												if (preg_match ("/^" . preg_quote ($GLOBALS["WS_THEME__"]["l"], "/") . "/", $widget_array["l"]))
													if (is_callable ("c_ws_widget__" . $widget_key . "_installation::activate"))
														call_user_func ("c_ws_widget__" . $widget_key . "_installation::activate");
										/**/
										do_action ("ws_theme__during_activate", get_defined_vars ());
									}
							}
						/**/
						do_action ("ws_theme__after_activate", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Handles de-activation / cleanup routines.
				Attach to: add_action("switch_theme");
				*/
				public static function deactivate ()
					{
						global $pagenow; /* Need global reference. */
						/**/
						do_action ("ws_theme__before_deactivate", get_defined_vars ());
						/**/
						if (is_blog_admin () && $pagenow === "themes.php") /* Right page? */
							{
								if (get_current_theme () !== c_ws_theme__readmes::parse_readme_value ("Theme Name"))
									{
										if ($GLOBALS["WS_THEME__"]["o"]["run_deactivation_routines"])
											{
												delete_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_color");
												delete_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_configured");
												delete_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_notices");
												delete_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_options");
											}
										/**/
										if (is_array ($GLOBALS["WS_PLUGIN__"]))
											foreach ($GLOBALS["WS_PLUGIN__"] as $plugin_key => $plugin_array)
												if (preg_match ("/^" . preg_quote ($GLOBALS["WS_THEME__"]["l"], "/") . "/", $plugin_array["l"]))
													if (is_callable ("c_ws_plugin__" . $plugin_key . "_installation::deactivate"))
														call_user_func ("c_ws_plugin__" . $plugin_key . "_installation::deactivate");
										/**/
										if (is_array ($GLOBALS["WS_WIDGET__"]))
											foreach ($GLOBALS["WS_WIDGET__"] as $widget_key => $widget_array)
												if (preg_match ("/^" . preg_quote ($GLOBALS["WS_THEME__"]["l"], "/") . "/", $widget_array["l"]))
													if (is_callable ("c_ws_widget__" . $widget_key . "_installation::deactivate"))
														call_user_func ("c_ws_widget__" . $widget_key . "_installation::deactivate");
										/**/
										do_action ("ws_theme__during_deactivate", get_defined_vars ());
									}
							}
						/**/
						do_action ("ws_theme__after_deactivate", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>