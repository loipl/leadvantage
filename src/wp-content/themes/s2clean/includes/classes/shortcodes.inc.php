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
if (!class_exists ("c_ws_theme__shortcodes"))
	{
		class c_ws_theme__shortcodes
			{
				/*
				Function that handles the Shortcode for [WS-T-Flash /].
				Attach to: add_shortcode("WS-T-Flash");
				
				Example: [WS-T-Flash src="[swf file]" width="100%" height="400" bgcolor="" wmode="transparent" flashvars="" /]
					Other attributes are supported as well. As indicated in the $attr array below.
				*/
				public static function flash_shortcode ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						return c_ws_theme__shortcodes_flash_in::flash_shortcode ($attr, $content, $shortcode);
					}
				/*
				Function that handles the Shortcode for [WS-T-FlowPlayer /].
				Attach to: add_shortcode("WS-T-FlowPlayer");
				
				Example: [WS-T-FlowPlayer src="[mp4|flv]" width="100%" height="400" autostart="false" /]
					Other attributes are supported as well. As indicated in the $attr array below.
				*/
				public static function flowplayer_shortcode ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						return c_ws_theme__shortcodes_flowplayer_in::flowplayer_shortcode ($attr, $content, $shortcode);
					}
				/*
				Function that handles the Shortcode for [WS-T-JWPlayer /].
				Attach to: add_shortcode("WS-T-JWPlayer");
				
				Example: [WS-T-JWPlayer src="[mp4|flv]" width="100%" height="400" autostart="false" /]
					Other attributes are supported as well. As indicated in the $attr array below.
				
				By default, JWPlayer is NOT bundled into the distribution of this WordPress® theme.
				You will need to have a look at the instructions, inside: /includes/players/jwplayer/readme.txt.
				*/
				public static function jwplayer_shortcode ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						return c_ws_theme__shortcodes_jwplayer_in::jwplayer_shortcode ($attr, $content, $shortcode);
					}
				/*
				Function that handles the Shortcode for [WS-T-Video /].
				This is a wrapper for both FlowPlayer & JWPlayer.
				Attach to: add_shortcode("WS-T-Video");
				
				Example: [WS-T-Video src="[mp4|flv]" width="100%" height="400" autostart="false" /]
					The attributes: src, width, height, and autostart; are the same for both FlowPlayer & JWPlayer.
					Other attributes are supported too. As indicated in the $attr array for both FlowPlayer & JWPlayer.
				
				By default, JWPlayer is NOT bundled into the distribution of this WordPress® theme.
				You will need to have a look at the instructions, inside: /includes/players/jwplayer/readme.txt.
				*/
				public static function video_shortcode ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						return (file_exists (TEMPLATEPATH . "/includes/players/jwplayer/player.swf")) ? c_ws_theme__shortcodes::jwplayer_shortcode ($attr, $content, $shortcode) : c_ws_theme__shortcodes::flowplayer_shortcode ($attr, $content, $shortcode);
					}
			}
	}
?>