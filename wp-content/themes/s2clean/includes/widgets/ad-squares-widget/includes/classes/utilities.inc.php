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
if (!class_exists ("c_ws_widget__ad_squares_utilities"))
	{
		class c_ws_widget__ad_squares_utilities
			{
				/*
				Function evaluates PHP code, and returns the output afterward.
				*/
				public static function evl ($code = FALSE)
					{
						ob_start (); /* Output buffer. */
						/**/
						eval ("?>" . trim ($code));
						/**/
						return ob_get_clean ();
					}
				/*
				Function that buffers ( gets ) function output.
				*/
				public static function get ($function = FALSE)
					{
						$args = func_get_args ();
						$function = array_shift ($args);
						/**/
						if (is_string ($function) && $function)
							{
								ob_start ();
								/**/
								if (is_array ($args) && !empty ($args))
									{
										$return = call_user_func_array ($function, $args);
									}
								else /* There are no additional arguments to pass. */
									{
										$return = call_user_func ($function);
									}
								/**/
								$echo = ob_get_clean ();
								/**/
								return (!strlen ($echo) && strlen ($return)) ? $return : $echo;
							}
						else /* Else return null. */
							return;
					}
				/*
				Function builds a version checksum for this installation.
				*/
				public static function ver_checksum ()
					{
						$checksum = WS_WIDGET__AD_SQUARES_VERSION; /* Software version string. */
						$checksum .= (defined ("WS_WIDGET__AD_SQUARES_PRO_VERSION")) ? "-" . WS_WIDGET__AD_SQUARES_PRO_VERSION : ""; /* Pro version string? */
						$checksum .= "-" . abs (crc32 ($GLOBALS["WS_WIDGET__"]["ad_squares"]["c"]["checksum"] . $GLOBALS["WS_WIDGET__"]["ad_squares"]["o"]["options_checksum"] . $GLOBALS["WS_WIDGET__"]["ad_squares"]["o"]["options_version"]));
						/**/
						return $checksum; /* ( i.e. version-pro version-checksum ) */
					}
			}
	}
?>