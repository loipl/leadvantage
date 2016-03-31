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
if (!class_exists ("c_ws_theme__utils_users"))
	{
		class c_ws_theme__utils_users
			{
				/*
				Function retrieves an array of all admin emails.
				*/
				public static function get_admin_emails ()
					{
						global $wpdb; /* Global DB object. */
						static $emails; /* Optimizes this routine. */
						/**/
						if (!isset ($emails)) /* Only retrieve once. */
							{
								if (is_object ($wpdb)) /* Must have the global DB oject in order to process the query. */
									{
										if (is_array ($emails = $wpdb->get_col ("SELECT `user_email` FROM `" . $wpdb->users . "` WHERE `ID` IN (SELECT DISTINCT `user_id` FROM `" . $wpdb->usermeta . "` WHERE `meta_key` = '" . $wpdb->prefix . "user_level' AND `meta_value` = '10')")))
											{
												return ($emails = array_map ("strtolower", $emails)); /* Force lowercase. */
											}
										else
											return ($emails = false);
									}
								else
									return ($emails = false);
							}
						else
							return $emails;
					}
			}
	}
?>