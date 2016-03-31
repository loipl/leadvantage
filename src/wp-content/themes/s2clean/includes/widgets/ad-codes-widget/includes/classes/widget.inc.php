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
if (!class_exists ("c_ws_widget__ad_codes_widget"))
	{
		class c_ws_widget__ad_codes_widget
			{
				/*
				This is the function for registering the widget.
				Attach to: add_action("widgets_init");
				*/
				public static function register ()
					{
						do_action ("ws_widget__ad_codes_before_register", get_defined_vars ());
						/**/
						register_widget ("c_ws_widget__ad_codes_class");
						/**/
						do_action ("ws_widget__ad_codes_after_register", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>