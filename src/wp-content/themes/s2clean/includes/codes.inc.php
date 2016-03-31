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
/*
Add WordPress® Editor Shortcodes.
http://codex.wordpress.org/Shortcode_API
*/
add_shortcode ("WS-T-Flash", "c_ws_theme__shortcodes::flash_shortcode");
add_shortcode ("WS-T-Video", "c_ws_theme__shortcodes::video_shortcode");
add_shortcode ("WS-T-FlowPlayer", "c_ws_theme__shortcodes::flowplayer_shortcode");
add_shortcode ("WS-T-JWPlayer", "c_ws_theme__shortcodes::jwplayer_shortcode");
/*
Include child-config codes.
*/
@include_once TEMPLATEPATH . "/includes/cc-incs/codes.inc.php";
?>