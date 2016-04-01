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
Template Name: Full Page Layout ( no sidebar )
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/*
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		get_header ();
		/*
		Hook before fullpage.
		*/
		do_action ("ws_theme__before_fullpage");
		/*
		Open the fullpage wrapper.
		*/
		echo '<div id="fullpage-wrapper" class="fullpage-wrapper">' . "\n";
		/*
		Open the fullpage inner wrapper a.
		*/
		echo '<div id="fullpage-inner-wrapper-a" class="fullpage-inner-wrapper-a">' . "\n";
		/*
		Open the fullpage inner wrapper b.
		*/
		echo '<div id="fullpage-inner-wrapper-b" class="fullpage-inner-wrapper-b">' . "\n";
		/*
		Open the fullpage container.
		*/
		echo '<div id="fullpage-container" class="fullpage-container clearfix">' . "\n";
		/*
		Check if we have this fullpage in the archive.
		*/
		if (have_posts () && the_post () !== "nill" && is_object ($post) && apply_filters ("ws_theme__during_fullpage_display_sections", true))
			{
				do_action ("ws_theme__during_fullpage_before_sections");
				/*
				Build the upper section.
				*/
				if (apply_filters ("ws_theme__during_fullpage_during_sections_display_upper", true))
					{
						do_action ("ws_theme__during_fullpage_during_sections_before_upper");
						/**/
						echo '<div id="fullpage-upper-section" class="fullpage-upper-section clearfix">' . "\n";
						/**/
						echo '<div id="fullpage-share-save" class="fullpage-share-save">' . "\n";
						echo '<a class="share-save"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/social-share-save.png") . '" alt="Share/Save" title="Share/Save" /></a>' . "\n";
						echo '</div>' . "\n";
						/**/
						edit_post_link ("Edit", '<div id="fullpage-edit" class="fullpage-edit">[ ', ' ]</div>');
						/**/
						echo '<div id="fullpage-title" class="fullpage-title">' . "\n";
						echo '<h1>' . c_ws_theme__utils_strings::cut_string (( ($ws_theme__temp_s = get_post_meta (get_the_ID (), "h1_title", true)) ? $ws_theme__temp_s : c_ws_theme__utilities::get ("the_title")), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["fullpage"]) . '</h1>' . "\n";
						echo ($ws_theme__temp_s = get_post_meta (get_the_ID (), "h1_desc", true)) ? '<div>' . $ws_theme__temp_s . '</div>' . "\n" : '';
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_fullpage_during_sections_after_upper");
					}
				/*
				Build the middle section.
				*/
				if (apply_filters ("ws_theme__during_fullpage_during_sections_display_middle", true))
					{
						do_action ("ws_theme__during_fullpage_during_sections_before_middle");
						/**/
						echo '<div id="fullpage-middle-section" class="fullpage-middle-section clearfix">' . "\n";
						/**/
						echo '<div id="fullpage-content" class="fullpage-content clearfix">' . "\n";
						/**/
						do_action ("ws_theme__during_fullpage_before_content");
						/**/
						echo '<!-- custom-content-before -->' . "\n";
						/**/
						if (apply_filters ("ws_theme__during_fullpage_display_content", true))
							echo c_ws_theme__utilities::get ("the_content") . "\n";
						/**/
						echo '<!-- custom-content -->' . "\n";
						/**/
						echo '<!-- custom-content-after -->' . "\n";
						/**/
						do_action ("ws_theme__during_fullpage_after_content");
						/**/
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_fullpage_during_sections_after_middle");
					}
				/*
				Build the lower section.
				*/
				if (apply_filters ("ws_theme__during_fullpage_during_sections_display_lower", true))
					{
						do_action ("ws_theme__during_fullpage_during_sections_before_lower");
						/**/
						echo '<div id="fullpage-lower-section" class="fullpage-lower-section clearfix">' . "\n";
						/**/
						wp_link_pages ("before=" . urlencode /* These are paginated using: <!--nextpage--> */
						('<div id="fullpage-lower-parts" class="fullpage-lower-parts">') . "&after=" . urlencode ('</div>'));
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_fullpage_during_sections_after_lower");
					}
				/*
				Hook after fullpage content.
				*/
				do_action ("ws_theme__during_fullpage_after_sections");
				/*
				Possible comments. 
				*/
				comments_template ();
			}
		/*
		Close the fullpage container.
		*/
		echo '</div>' . "\n";
		/*
		Close the fullpage inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the fullpage inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the fullpage wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after fullpage.
		*/
		do_action ("ws_theme__after_fullpage");
		/*
		Get the footer.
		*/
		get_footer ();
	}
?>