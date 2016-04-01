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
if (!class_exists ("c_ws_widget__super_tags_installation"))
	{
		class c_ws_widget__super_tags_installation
			{
				/*
				Handles activation routines.
				*/
				public static function activate ()
					{
						do_action ("ws_widget__super_tags_before_activation", get_defined_vars ());
						/**/
						if (!is_numeric (get_option ("ws_widget__super_tags_configured")))
							update_option ("ws_widget__super_tags_configured", "0");
						/**/
						if (!is_array (get_option ("ws_widget__super_tags_notices")))
							update_option ("ws_widget__super_tags_notices", array ());
						/**/
						if (!is_array (get_option ("ws_widget__super_tags_options")))
							update_option ("ws_widget__super_tags_options", array ());
						/**/
						do_action ("ws_widget__super_tags_after_activation", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Handles de-activation / cleanup routines.
				*/
				public static function deactivate ()
					{
						do_action ("ws_widget__super_tags_before_deactivation", get_defined_vars ());
						/**/
						if ($GLOBALS["WS_WIDGET__"]["super_tags"]["o"]["run_deactivation_routines"])
							{
								delete_option ("ws_widget__super_tags_configured");
								delete_option ("ws_widget__super_tags_notices");
								delete_option ("ws_widget__super_tags_options");
								delete_option ("widget_ws_widget__super_tags");
								delete_option ("ws_widget__super_tags");
							}
						/**/
						do_action ("ws_widget__super_tags_after_deactivation", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>