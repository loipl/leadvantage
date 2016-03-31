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
if (!class_exists ("c_ws_theme__shortcodes_flash_in"))
	{
		class c_ws_theme__shortcodes_flash_in
			{
				/*
				Function that handles the Shortcode for [WS-T-Flash /].
				Attach to: add_shortcode("WS-T-Flash");
				
				Example: [WS-T-Flash src="[swf file]" width="100%" height="400" bgcolor="" wmode="transparent" flashvars="" /]
					Other attributes are supported as well. As indicated in the $attr array below.
				*/
				public static function flash_shortcode ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_flash_shortcode", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$attr = c_ws_theme__utils_strings::trim_qts_deep ((array)$attr); /* Force array; trim quote entities. */
						/**/
						$attr = shortcode_atts (array ("id" => "iflash-" . mt_rand (), "src" => "", "width" => "100%", "height" => "400", "bgcolor" => "", "wmode" => "transparent", "flashvars" => "", "quality" => "high", "scale" => "", "menu" => "", "allowfullscreen" => "true", "swliveconnect" => "true", "allowscriptaccess" => "always"), $attr);
						/**/
						eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_flash_shortcode_after_shortcode_atts", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						foreach ($attr as $key => $value)
							$params .= $key . ":'" . c_ws_theme__utils_strings::esc_js_sq ($value) . "',";
						$params = "{" . rtrim ($params, ",") . "}";
						/**/
						$code = '<script type="text/javascript">' . "\n";
						$code .= 'jQuery.iFlash(' . $params . ');' . "\n";
						$code .= '</script>';
						/**/
						return apply_filters ("ws_theme__flash_shortcode", $code, get_defined_vars ());
					}
			}
	}
?>