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
if (!class_exists ("c_ws_theme__c_login_box"))
	{
		class c_ws_theme__c_login_box
			{
				/*
				Login box and controls.
				Attach to: add_action("ws_theme__during_header_after_markup");
				*/
				public static function login_box_and_controls ()
					{
						do_action ("ws_theme__c_before_login_box_and_controls", get_defined_vars ());
						/**/
						if (apply_filters ("ws_theme__c_display_login_box", true, get_defined_vars ()))
							{
								do_action ("ws_theme__c_before_login_box", get_defined_vars ());
								/**/
								echo '<div id="body-header-login-box" class="body-header-login-box">' . "\n";
								echo '<form method="post" action="' . esc_attr (wp_login_url ()) . '">' . "\n";
								/**/
								echo '<div class="log-pwd-label">' . "\n";
								echo '<div>*</div>' . "\n"; /* Float it! */
								echo 'Username / Password' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div class="log">' . "\n";
								echo '<input type="text" name="log" tabindex="1" title="Username" />' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div class="pwd">' . "\n";
								echo '<input type="password" name="pwd" tabindex="2" title="Password" />' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div class="lostpassword">' . "\n";
								echo '<a href="' . esc_attr (wp_lostpassword_url ()) . '">forgot password?</a>' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div class="rememberme">' . "\n";
								echo '<label><input type="checkbox" name="rememberme" tabindex="3" value="forever" />Remember Me</label>' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div class="wp-submit">' . "\n";
								echo '<input type="submit" name="wp-submit" tabindex="4" value="Log Me In" />' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '</form>' . "\n";
								echo '</div>' . "\n";
								/**/
								do_action ("ws_theme__c_after_login_box", get_defined_vars ());
							}
						/**/
						if (apply_filters ("ws_theme__c_display_login_box_controls", true, get_defined_vars ()))
							{
								do_action ("ws_theme__c_before_login_box_controls", get_defined_vars ());
								/**/
								echo '<div id="body-header-login-box-controls" class="body-header-login-box-controls">' . "\n";
								if (is_user_logged_in ()) /* User is logged in. Provide a link to their account; and also to logout. */
									echo '<a href="' . esc_attr (S2MEMBER_LOGIN_WELCOME_PAGE_URL) . '"' . ( (is_page (S2MEMBER_LOGIN_WELCOME_PAGE_ID)) ? ' class="current"' : '') . 'rel="nofollow">My Account</a> | <a href="' . esc_attr (wp_logout_url (home_url ("/"))) . '" rel="nofollow">Logout</a>' . "\n";
								else /* Otherwise, they are not logged in yet. Invite them to login or signup now. The signup page is the Membership Options Page. */
									echo '<a href="' . esc_attr (wp_login_url ()) . '" id="login-box-opener" rel="nofollow">Login</a> <a href="' . esc_attr (S2MEMBER_MEMBERSHIP_OPTIONS_PAGE_URL) . '"' . ( (is_page (S2MEMBER_MEMBERSHIP_OPTIONS_PAGE_ID)) ? ' class="current"' : '') . '>Signup</a>' . "\n";
								echo '</div>' . "\n";
								/**/
								do_action ("ws_theme__c_after_login_box_controls", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__c_after_login_box_and_controls", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>