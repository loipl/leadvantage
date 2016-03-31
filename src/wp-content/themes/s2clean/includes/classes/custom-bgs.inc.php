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
if (!class_exists ("c_ws_theme__custom_bgs"))
	{
		class c_ws_theme__custom_bgs
			{
				/*
				Works in conjection with add_custom_background().
				*/
				public static function custom_background_css ()
					{
						do_action ("ws_theme__before_custom_background_css", get_defined_vars ());
						/**/
						$image = get_background_image ();
						$color = get_background_color ();
						/**/
						if ($image || strlen ($color)) /* Image or a color? */
							{
								$color = (strlen ($color)) ? "#" . $color : "";
								$image = "url('" . c_ws_theme__utils_strings::esc_js_sq ($image) . "')";
								$repeat = get_theme_mod ("background_repeat", "repeat");
								$position = "top " . get_theme_mod ("background_position_x", "left");
								$attachment = get_theme_mod ("background_attachment", "scroll");
								/**/
								$background = trim (preg_replace ("/ +/", " ", $color . ' ' . $image . ' ' . $repeat . ' ' . $position . ' ' . $attachment));
								/**/
								eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
								do_action ("ws_theme__during_custom_background_css", get_defined_vars ());
								unset ($__refs, $__v); /* Unset defined __refs, __v. */
								/**/
								echo "\n" . '<style type="text/css">' . "\n";
								echo 'html { background: ' . $background . '; }' . "\n";
								echo '</style>' . "\n";
							}
						/**/
						do_action ("ws_theme__after_custom_background_css", get_defined_vars ());
					}
			}
	}
?>