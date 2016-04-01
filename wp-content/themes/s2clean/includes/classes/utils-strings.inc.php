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
if (!class_exists ("c_ws_theme__utils_strings"))
	{
		class c_ws_theme__utils_strings
			{
				/*
				Function cuts a string short and applies trailing dots when applicable.
				*/
				public static function cut_string ($string = FALSE, $length = FALSE, $trail = "...")
					{
						if (is_string ($string) && is_integer ($length) && is_string ($trail))
							{
								if (strlen ($str = trim (strip_tags ($string))) > $length)
									$string = substr ($str, 0, $length - strlen ($trail)) . $trail;
							}
						/**/
						return $string;
					}
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
				Sanitizes a string; by removing non-standard characters.
				This allows all characters that appears on a standard computer keyboard.
				*/
				public static function keyboard_chars_only ($string = FALSE)
					{
						return preg_replace ("/[^0-9A-Z\r\n\t\s`\=\[\]\\\;',\.\/~\!@#\$%\^&\*\(\)_\+\|\}\{\:\"\?\>\<\-]/i", "", remove_accents ((string)$string));
					}
				/*
				Function that trims deeply.
				*/
				public static function trim_deep ($value = FALSE)
					{
						return is_array ($value) ? array_map ("c_ws_theme__utils_strings::trim_deep", $value) : trim ((string)$value);
					}
				/*
				Function that trims all single/double quote entities deeply.
				This is useful on Shortcode attributes mangled by a Visual Editor.
				*/
				public static function trim_qts_deep ($value = FALSE)
					{
						$qts = implode ("|", array_keys /* Keys are regex patterns. */ (array ("&apos;" => "&apos;", "&#0*39;" => "&#39;", "&#[xX]0*27;" => "&#x27;"/**/, "&lsquo;" => "&lsquo;", "&#0*8216;" => "&#8216;", "&#[xX]0*2018;" => "&#x2018;"/**/, "&rsquo;" => "&rsquo;", "&#0*8217;" => "&#8217;", "&#[xX]0*2019;" => "&#x2019;"/**/, "&quot;" => "&quot;", "&#0*34;" => "&#34;", "&#[xX]0*22;" => "&#x22;"/**/, "&ldquo;" => "&ldquo;", "&#0*8220;" => "&#8220;", "&#[xX]0*201[cC];" => "&#x201C;"/**/, "&rdquo;" => "&rdquo;", "&#0*8221;" => "&#8221;", "&#[xX]0*201[dD];" => "&#x201D;")));
						return is_array ($value) ? array_map ("c_ws_theme__utils_strings::trim_qts_deep", $value) : preg_replace ("/^(?:" . $qts . ")+|(?:" . $qts . ")+$/", "", (string)$value);
					}
				/*
				Function that trims double quotes deeply ( i.e. " ).
				This is useful on CSV data that is encapsulated by double quotes.
				*/
				public static function trim_dq_deep ($value = FALSE)
					{
						return is_array ($value) ? array_map ("c_ws_theme__utils_strings::trim_dq_deep", $value) : trim ((string)$value, "\" \t\n\r\0\x0B");
					}
				/*
				Function generates a random string with letters/numbers/symbols.
				*/
				public static function random_str_gen ($length = 12, $special_chars = TRUE, $extra_special_chars = FALSE)
					{
						$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
						$chars .= ($extra_special_chars) ? "-_ []{}<>~`+=,.;:/?|" : "";
						$chars .= ($special_chars) ? "!@#$%^&*()" : "";
						/**/
						for ($i = 0, $random_str = ""; $i < $length; $i++)
							$random_str .= substr ($chars, mt_rand (0, strlen ($chars) - 1), 1);
						/**/
						return $random_str;
					}
			}
	}
?>