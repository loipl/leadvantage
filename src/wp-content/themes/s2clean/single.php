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
Single Template: Column Layout ( w/sidebar )
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
		Hook before single.
		*/
		do_action ("ws_theme__before_single");
		/*
		Open the single wrapper.
		*/
		echo '<div id="single-wrapper" class="single-wrapper">' . "\n";
		/*
		Open the single inner wrapper a.
		*/
		echo '<div id="single-inner-wrapper-a" class="single-inner-wrapper-a">' . "\n";
		/*
		Open the single inner wrapper b.
		*/
		echo '<div id="single-inner-wrapper-b" class="single-inner-wrapper-b">' . "\n";
		/*
		Open the single container.
		*/
		echo '<div id="single-container" class="single-container clearfix">' . "\n";
		/*
		Check if we have this single in the archive.
		*/
		if (have_posts () && the_post () !== "nill" && is_object ($post) && apply_filters ("ws_theme__during_single_display_sections", true))
			{
				do_action ("ws_theme__during_single_before_sections");
				/*
				Build the upper section.
				*/
				if (apply_filters ("ws_theme__during_single_during_sections_display_upper", true))
					{
						do_action ("ws_theme__during_single_during_sections_before_upper");
						/**/
						echo '<div id="single-upper-section" class="single-upper-section clearfix">' . "\n";
						/**/
						echo '<div id="single-share-save" class="single-share-save">' . "\n";
						echo '<a class="share-save"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/social-share-save-small.png") . '" alt="Share/Save" title="Share/Save" /></a>' . "\n";
						echo '</div>' . "\n";
						/**/
						edit_post_link ("Edit", '<div id="single-edit" class="single-edit">[ ', ' ]</div>');
						/**/
						echo '<div id="single-time" class="single-time">' . "\n";
						echo c_ws_theme__utilities::get ("the_time", get_option ("date_format")) . ' ' . c_ws_theme__utilities::get ("the_time", get_option ("time_format")) . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div id="single-title" class="single-title">' . "\n";
						echo '<h1>' . c_ws_theme__utils_strings::cut_string (( ($ws_theme__temp_s = get_post_meta (get_the_ID (), "h1_title", true)) ? $ws_theme__temp_s : c_ws_theme__utilities::get ("the_title")), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["single"]) . '</h1>' . "\n";
						echo ($ws_theme__temp_s = get_post_meta (get_the_ID (), "h1_desc", true)) ? '<div>' . $ws_theme__temp_s . '</div>' . "\n" : '';
						echo '</div>' . "\n";
						/**/
						echo '<div id="single-cats" class="single-cats">' . "\n";
						echo '<strong>Posted In:</strong> ' . implode ($GLOBALS["WS_THEME__"]["c"]["single_category_sep"], array_slice (preg_split ("/\<\|\>/", c_ws_theme__utilities::get ("the_category", "<|>")), 0, $GLOBALS["WS_THEME__"]["c"]["single_category_max"])) . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div id="single-author" class="single-author">' . "\n";
						echo '<strong>By:</strong> ' . c_ws_theme__utilities::get ("the_author_posts_link") . "\n";
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_single_during_sections_after_upper");
					}
				/*
				Build the middle section.
				*/
				if (apply_filters ("ws_theme__during_single_during_sections_display_middle", true))
					{
						do_action ("ws_theme__during_single_during_sections_before_middle");
						/**/
						echo '<div id="single-middle-section" class="single-middle-section clearfix">' . "\n";
						/**/
						echo '<div id="single-content" class="single-content clearfix">' . "\n";
						/**/
						do_action ("ws_theme__during_single_before_content");
						/**/
						echo '<!-- custom-content-before -->' . "\n";
						/**/
						if (apply_filters ("ws_theme__during_single_display_content", true))
							echo c_ws_theme__utilities::get ("the_content") . "\n";
						/**/
						echo '<!-- custom-content -->' . "\n";
						/**/
						echo '<!-- custom-content-after -->' . "\n";
						/**/
						do_action ("ws_theme__during_single_after_content");
						/**/
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_single_during_sections_after_middle");
					}
				/*
				Build the lower section.
				*/
				if (apply_filters ("ws_theme__during_single_during_sections_display_lower", true))
					{
						do_action ("ws_theme__during_single_during_sections_before_lower");
						/**/
						echo '<div id="single-lower-section" class="single-lower-section clearfix">' . "\n";
						/**/
						wp_link_pages ("before=" . urlencode /* These are paginated using: <!--nextpage--> */
						('<div id="single-lower-parts" class="single-lower-parts">') . "&after=" . urlencode ('</div>'));
						/**/
						the_tags ('<div id="single-tags" class="single-tags">' . "\n" ./**/
						'<strong>Tags:</strong> ',/**/
						", ", "\n" . '</div>' . "\n");
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_single_during_sections_after_lower");
					}
				/*
				Hook after sections.
				*/
				do_action ("ws_theme__during_single_after_sections");
				/*
				Possible comments. 
				*/
				comments_template ();
			}
		/*
		Close the single container.
		*/
		echo '</div>' . "\n";
		/*
		Close the single inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the single inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the single wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after single.
		*/
		do_action ("ws_theme__after_single");
		/*
		Get the sidebar.
		*/
		get_sidebar ();
		/*
		Get the footer.
		*/
		get_footer ();
	}
?>