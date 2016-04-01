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
if (!class_exists ("c_ws_theme__custom_css_js"))
	{
		class c_ws_theme__custom_css_js
			{
				/*
				Function that outputs custom CSS from options.
				Attach to: add_action("init");
				*/
				public static function custom_css ()
					{
						if ($_GET["ws_theme__custom_css"]) /* Call inner function? */
							{
								return c_ws_theme__custom_css_js_in::custom_css ();
							}
					}
				/*
				Function that outputs custom JS from options.
				Attach to: add_action("init");
				*/
				public static function custom_js ()
					{
						if ($_GET["ws_theme__custom_js"]) /* Call inner function? */
							{
								return c_ws_theme__custom_css_js_in::custom_js ();
							}
					}
			}
	}
?>