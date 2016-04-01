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
if (!class_exists ("c_ws_theme__custom_css_js_in"))
	{
		class c_ws_theme__custom_css_js_in
			{
				/*
				Function that outputs custom CSS from options.
				Attach to: add_action("init");
				*/
				public static function custom_css ()
					{
						do_action ("ws_theme__before_custom_css", get_defined_vars ());
						/**/
						if ($_GET["ws_theme__custom_css"])
							{
								$u = get_bloginfo ("template_url");
								$c = $GLOBALS["WS_THEME__"]["c"]["color"];
								$i = $u . "/colors/" . $c . "/images";
								/**/
								status_header (200); /* 200 OK status header. */
								/**/
								header ("Content-Type: text/css; charset=utf-8");
								header ("Expires: " . gmdate ("D, d M Y H:i:s", strtotime ("+1 week")) . " GMT");
								header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
								header ("Cache-Control: max-age=604800");
								header ("Pragma: public");
								/**/
								eval ('while (@ob_end_clean ());'); /* Clean buffers. */
								/**/
								if (c_ws_theme__utils_conds::is_multisite_farm ())
									echo $GLOBALS["WS_THEME__"]["o"]["custom_css"] . "\n";
								else /* ^ Prevent PHP code eval() on Multisite Farm installs. */
									eval ("?>" . $GLOBALS["WS_THEME__"]["o"]["custom_css"] . "\n");
								/**/
								do_action ("ws_theme__during_custom_css", get_defined_vars ());
								/**/
								exit (); /* Clean exit. */
							}
						/**/
						do_action ("ws_theme__after_custom_css", get_defined_vars ());
					}
				/*
				Function that outputs custom JS from options.
				Attach to: add_action("init");
				*/
				public static function custom_js ()
					{
						do_action ("ws_theme__before_custom_js", get_defined_vars ());
						/**/
						if ($_GET["ws_theme__custom_js"])
							{
								$u = get_bloginfo ("template_url");
								$c = $GLOBALS["WS_THEME__"]["c"]["color"];
								$i = $u . "/colors/" . $c . "/images";
								/**/
								status_header (200); /* 200 OK status header. */
								/**/
								header ("Content-Type: text/javascript; charset=utf-8");
								header ("Expires: " . gmdate ("D, d M Y H:i:s", strtotime ("+1 week")) . " GMT");
								header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
								header ("Cache-Control: max-age=604800");
								header ("Pragma: public");
								/**/
								eval ('while (@ob_end_clean ());'); /* Clean buffers. */
								/**/
								if (c_ws_theme__utils_conds::is_multisite_farm ())
									echo $GLOBALS["WS_THEME__"]["o"]["custom_js"] . "\n";
								else /* ^ Prevent PHP code eval() on Multisite Farm installs. */
									eval ("?>" . $GLOBALS["WS_THEME__"]["o"]["custom_js"] . "\n");
								/**/
								do_action ("ws_theme__during_custom_js", get_defined_vars ());
								/**/
								exit (); /* Clean exit. */
							}
						/**/
						do_action ("ws_theme__after_custom_js", get_defined_vars ());
					}
			}
	}
?>