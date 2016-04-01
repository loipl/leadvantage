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
if (!class_exists ("c_ws_theme__c_navigation"))
	{
		class c_ws_theme__c_navigation
			{
				/*
				Navigation dynamics.
				Attach to: add_filter("ws_theme__during_header_during_markup_display_nav");
				*/
				public static function navigation_dynamics ($display = TRUE)
					{
						do_action ("ws_theme__c_before_navigation_dynamics", get_defined_vars ());
						/**/
						if (apply_filters ("ws_theme__c_navigation_dynamics_display", true, get_defined_vars ()))
							{
								do_action ("ws_theme__c_during_navigation_dynamics_before_nav", get_defined_vars ());
								/**/
								echo '<div id="body-header-nav" class="body-header-nav clearfix">' . "\n";
								echo '<div id="body-header-nav-menu" class="body-header-nav-menu">' . "\n";
								echo '<ul class="primary-menu megafish">' . "\n";
								/**/
								if ($GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "custom" && is_user_logged_in ())
									{
										echo c_ws_theme__nav_menu::nav_menu_items ("logged-in-primary") . "\n";
									}
								else /* Else display the normal Primary Menu. */
									{
										echo c_ws_theme__nav_menu::nav_menu_items ("primary") . "\n";
									}
								/**/
								echo '</ul>' . "\n";
								echo '</div>' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<script type="text/javascript">' . "\n";
								echo "jQuery('div#body-header-nav-menu > ul').megafish";
								echo "({downArrow: '', rightArrow: '', animShow: '', animHide: ''});" . "\n";
								echo '</script>' . "\n";
								/**/
								do_action ("ws_theme__c_during_navigation_dynamics_after_nav", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__c_after_navigation_dynamics", get_defined_vars ());
						/**/
						return false;
					}
			}
	}
?>