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
if (!class_exists ("c_ws_theme__c_footbars"))
	{
		class c_ws_theme__c_footbars
			{
				/*
				Footbar dynamics.
				Attach to: add_filter("ws_theme__during_footbar_during_markup_display_dynamics");
				*/
				public static function footbar_dynamics ($display = TRUE)
					{
						do_action ("ws_theme__c_before_footbar_dynamics", get_defined_vars ());
						/**/
						if (apply_filters ("ws_theme__c_during_footbar_dynamics_display", true, get_defined_vars ()))
							{
								do_action ("ws_theme__c_during_footbar_dynamics_before", get_defined_vars ());
								/**/
								echo '<ul id="footbar" class="footbar">' . "\n";
								/**/
								if (is_user_logged_in () && is_active_sidebar ("Logged-In Footbar"))
									{
										if (!dynamic_sidebar ("Logged-In Footbar"))
											{
												echo '<li></li>' . "\n";
											}
									}
								/**/
								else if (!dynamic_sidebar ("Default Footbar"))
									{
										echo '<li></li>' . "\n";
									}
								/**/
								echo '</ul>' . "\n";
								/**/
								do_action ("ws_theme__c_during_footbar_dynamics_after", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__c_after_footbar_dynamics", get_defined_vars ());
						/**/
						return false;
					}
			}
	}
?>