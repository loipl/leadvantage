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
if (!class_exists ("c_ws_theme__shortcodes_flowplayer_in"))
	{
		class c_ws_theme__shortcodes_flowplayer_in
			{
				/*
				Function that handles the Shortcode for [WS-T-FlowPlayer /].
				Attach to: add_shortcode("WS-T-FlowPlayer");
				
				Example: [WS-T-FlowPlayer src="[mp4|flv]" width="100%" height="400" autostart="false" /]
					Other attributes are supported as well. As indicated in the $attr array below.
				*/
				public static function flowplayer_shortcode ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_flowplayer_shortcode", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$attr = c_ws_theme__utils_strings::trim_qts_deep ((array)$attr); /* Force array; trim quote entities. */
						/**/
						$player = shortcode_atts (array ("src" => "", "autostart" => "false", "scaling" => "orig", "player" => get_bloginfo ("template_url") . "/includes/players/flowplayer", "viral" => ((file_exists (rtrim ($_SERVER["DOCUMENT_ROOT"], DIRECTORY_SEPARATOR . "/") . "/crossdomain.xml") || file_exists (ABSPATH . "crossdomain.xml")) ? "true" : "false")), $attr);
						/**/
						$attr = shortcode_atts (array ("id" => "iflash-" . mt_rand (), "src" => $player["player"] . "/player.swf", "width" => "100%", "height" => "400", "bgcolor" => "#000000", "wmode" => "opaque", "flashvars" => "config=" . urlencode ("{'clip':{'url':'" . $player["src"] . "','autoPlay':" . $player["autostart"] . ",'scaling':'" . $player["scaling"] . "'}" . (($player["viral"] && $player["viral"] !== "false") ? ",'plugins':{'viral':{'url':'" . $player["player"] . "/plugins/viral.swf'}}" : "") . "}"), "quality" => "high", "scale" => "", "menu" => "", "allowfullscreen" => "true", "swliveconnect" => "true", "allowscriptaccess" => "always"), $attr);
						/**/
						$attr["src"] = $player["player"] . "/player.swf"; /* This is forced to $player["player"] . "/player.swf". */
						/**/
						eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_flowplayer_shortcode_after_shortcode_atts", get_defined_vars ());
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
						return apply_filters ("ws_theme__flowplayer_shortcode", $code, get_defined_vars ());
					}
			}
	}
?>