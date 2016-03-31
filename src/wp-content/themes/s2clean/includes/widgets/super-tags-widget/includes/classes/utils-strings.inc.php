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
/**/
if (!class_exists ("c_ws_widget__super_tags_utils_strings"))
	{
		class c_ws_widget__super_tags_utils_strings
			{
				/*
				Function escapes double quotes.
				*/
				public static function esc_dq ($string = FALSE, $times = FALSE)
					{
						$times = (is_numeric ($times) && $times >= 0) ? (int)$times : 1;
						return str_replace ('"', str_repeat ("\\", $times) . '"', (string)$string);
					}
				/*
				Function escapes single quotes.
				*/
				public static function esc_sq ($string = FALSE, $times = FALSE)
					{
						$times = (is_numeric ($times) && $times >= 0) ? (int)$times : 1;
						return str_replace ("'", str_repeat ("\\", $times) . "'", (string)$string);
					}
				/*
				Function escapes JavaScript and single quotes.
				*/
				public static function esc_js_sq ($string = FALSE, $times = FALSE)
					{
						$times = (is_numeric ($times) && $times >= 0) ? (int)$times : 1;
						return str_replace ("'", str_repeat ("\\", $times) . "'", str_replace (array ("\r", "\n"), array ("", '\\n'), str_replace ("\'", "'", (string)$string)));
					}
				/*
				Function escapes single quotes.
				*/
				public static function esc_ds ($string = FALSE, $times = FALSE)
					{
						$times = (is_numeric ($times) && $times >= 0) ? (int)$times : 1;
						return str_replace ('$', str_repeat ("\\", $times) . '$', (string)$string);
					}
				/*
				Function that trims deeply.
				*/
				public static function trim_deep ($value = FALSE)
					{
						return is_array ($value) ? array_map ("c_ws_widget__super_tags_utils_strings::trim_deep", $value) : trim ((string)$value);
					}
			}
	}
?>