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
if (!class_exists ("c_ws_theme__shortcodes_jwplayer_in"))
	{
		class c_ws_theme__shortcodes_jwplayer_in
			{
				/*
				Handles the Shortcode for [WS-T-JWPlayer /].
				Attach to: add_shortcode("WS-T-JWPlayer");
				
				Example: [WS-T-JWPlayer src="[mp4|flv]" width="100%" height="400" autostart="false" /]
					Other attributes are supported as well. As indicated in the $attr array below.
				
				By default, JWPlayer is NOT bundled into the distribution of this WordPress® theme.
				You will need to have a look at the instructions, inside: /includes/players/jwplayer/readme.txt.
				*/
				public static function jwplayer_shortcode ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_jwplayer_shortcode", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$attr = c_ws_theme__utils_strings::trim_qts_deep ((array)$attr); /* Force array; trim quote entities. */
						/**/
						$player = shortcode_atts (array ("src" => "", "autostart" => "false", "player" => get_bloginfo ("template_url") . "/includes/players/jwplayer", "skin" => "ws", "plugins" => "", "_vars" => ""), $attr);
						/**/
						$player["skin"] = (!file_exists (TEMPLATEPATH . "/includes/players/jwplayer/skins/" . $player["skin"] . ".zip")) ? "" : $player["skin"]; /* Verify skin. */
						/**/
						$attr = shortcode_atts (array ("id" => "iflash-" . mt_rand (), "src" => $player["player"] . "/player.swf", "width" => "100%", "height" => "400", "bgcolor" => "#000000", "wmode" => "opaque", "flashvars" => "file=" . urlencode ($player["src"]) . (($player["autostart"] && $player["autostart"] !== "false") ? "&autostart=" . urlencode ($player["autostart"]) : "") . (($player["skin"]) ? "&skin=" . urlencode ($player["player"] . "/skins/" . $player["skin"] . ".zip") : "") . (($player["plugins"]) ? "&plugins=" . urlencode ($player["plugins"]) : "") . (($player["_vars"]) ? "&" . trim ($player["_vars"], "&") : ""), "quality" => "high", "scale" => "", "menu" => "", "allowfullscreen" => "true", "swliveconnect" => "true", "allowscriptaccess" => "always"), $attr);
						$attr["src"] = $player["player"] . "/player.swf"; /* This is forced to $player["player"] . "/player.swf". */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_jwplayer_shortcode_after_shortcode_atts", get_defined_vars ());
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
						return apply_filters ("ws_theme__jwplayer_shortcode", $code, get_defined_vars ());
					}
			}
	}
?>