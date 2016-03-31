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
/*
Build custom template.
*/
if (ob_start ()) /* Get parent template. */
	{
		include_once TEMPLATEPATH . "/" . apply_filters ("ws_theme__c_s2_welcome_parent_template", "fullpage.x.php");
		/*
		Store the contents of parent template.
		*/
		$ws_theme__c_temp_s_parent_template = ob_get_contents ();
		/*
		Close the output buffer.
		*/
		ob_end_clean ();
	}
/*
Now lets build the custom page content.
*/
if (! ($ws_theme__c_temp_s_custom = "") && apply_filters ("ws_theme__c_display_s2_welcome", true) && ob_start ())
	{
		do_action ("ws_theme__c_before_s2_welcome");
		/*
		See: `s2Member -> API Scripting -> Constants`
		for more API Constants that are available to use.
		*/
		/*
		Member's profile; and avatar - if they have one.
		*/
		if (apply_filters ("ws_theme__c_during_s2_welcome_display_profile_avatar", true))
			{
				echo '<div id="s2-welcome-profile-avatar" class="s2-welcome-profile-avatar">' . "\n";
				echo '<div class="profile">[ <a rel="winopen;options={width:600,height:400} external" href="' . esc_attr (S2MEMBER_CURRENT_USER_PROFILE_MODIFICATION_PAGE_URL) . '">Modify Profile</a> ]</div>' . "\n";
				echo get_avatar (S2MEMBER_CURRENT_USER_ID, 128) . "\n";
				echo '</div>' . "\n";
			}
		/*
		Member's display name.
		*/
		if (apply_filters ("ws_theme__c_during_s2_welcome_display_name", true))
			{
				echo '<div id="s2-welcome-name" class="s2-welcome-name">' . "\n";
				echo esc_html (S2MEMBER_CURRENT_USER_DISPLAY_NAME) . "\n";
				echo '</div>' . "\n";
			}
		/*
		Member's access level label.
		*/
		if (apply_filters ("ws_theme__c_during_s2_welcome_display_label", true))
			{
				echo '<div id="s2-welcome-label" class="s2-welcome-label">' . "\n";
				echo '<em>' . esc_html (S2MEMBER_CURRENT_USER_ACCESS_LABEL) . '</em>' . "\n";
				echo '</div>' . "\n";
			}
		/*
		Hook after s2 welcome.
		*/
		do_action ("ws_theme__c_after_s2_welcome");
		/*
		See: `s2Member -> API Scripting -> Constants`
		for more API Constants that are available to use.
		*/
		/*
		Store custom content and close buffer.
		*/
		$ws_theme__c_temp_s_custom = ob_get_contents ();
		/*
		Close the output buffer.
		*/
		ob_end_clean ();
	}
/*
Now lets put it all together with custom content.
*/
echo preg_replace ("/\<\!-- custom-content-before --\>/", $ws_theme__c_temp_s_custom, $ws_theme__c_temp_s_parent_template);
/**/
unset ($ws_theme__c_temp_s_custom, $ws_theme__c_temp_s_parent_template); /* Conserve memory; dump these now. */
?>