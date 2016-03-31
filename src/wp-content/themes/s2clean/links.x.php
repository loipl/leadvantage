<?php
/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
Template Name: Links Page ( displays links )
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/*
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		if (ob_start ()) /* Get parent template. */
			{
				include_once TEMPLATEPATH . "/" . apply_filters ("ws_theme__links_parent_template", "page.php");
				/*
				Store the contents of parent template.
				*/
				$ws_theme__temp_s_parent_template = ob_get_contents ();
				/*
				Close the output buffer. 
				*/
				ob_end_clean ();
			}
		/*
		Now lets build the custom page content.
		*/
		if (! ($ws_theme__temp_s_custom = "") && apply_filters ("ws_theme__display_links", true) && ob_start ())
			{
				do_action ("ws_theme__before_links");
				/*
				Display the list of bookmarks. 
				*/
				if (apply_filters ("ws_theme__during_links_display_bookmarks", true))
					{
						do_action ("ws_theme__during_links_before_bookmarks");
						/**/
						echo '<div id="links" class="links">' . "\n";
						echo '<ul>' . "\n";
						echo c_ws_theme__utilities::get ("wp_list_bookmarks", "title_li=&title_before=" . urlencode ("<h3>") . "&title_after=" . urlencode ("</h3>")) . "\n";
						echo '</ul>' . "\n";
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_links_after_bookmarks");
					}
				/*
				Hook after links.
				*/
				do_action ("ws_theme__after_links");
				/*
				Store custom content and close buffer. 
				*/
				$ws_theme__temp_s_custom = ob_get_contents ();
				/*
				Close the output buffer.
				*/
				ob_end_clean ();
			}
		/*
		Now lets put it all together with custom content.
		*/
		echo preg_replace ("/\<\!-- custom-content-after --\>/", $ws_theme__temp_s_custom, $ws_theme__temp_s_parent_template);
		/**/
		unset ($ws_theme__temp_s_custom, $ws_theme__temp_s_parent_template); /* Conserve memory; dump these now. */
	}
?>