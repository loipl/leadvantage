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
Single Template: Full Page Layout ( no sidebar )
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
		Hook before fullsingle.
		*/
		do_action ("ws_theme__before_fullsingle");
		/*
		Open the fullsingle wrapper.
		*/
		echo '<div id="fullsingle-wrapper" class="fullsingle-wrapper">' . "\n";
		/*
		Open the fullsingle inner wrapper a.
		*/
		echo '<div id="fullsingle-inner-wrapper-a" class="fullsingle-inner-wrapper-a">' . "\n";
		/*
		Open the fullsingle inner wrapper b.
		*/
		echo '<div id="fullsingle-inner-wrapper-b" class="fullsingle-inner-wrapper-b">' . "\n";
		/*
		Open the fullsingle container.
		*/
		echo '<div id="fullsingle-container" class="fullsingle-container clearfix">' . "\n";
		/*
		Check if we have this fullsingle in the archive.
		*/
		if (have_posts () && the_post () !== "nill" && is_object ($post) && apply_filters ("ws_theme__during_fullsingle_display_sections", true))
			{
				do_action ("ws_theme__during_fullsingle_before_sections");
				/*
				Build the upper section.
				*/
				if (apply_filters ("ws_theme__during_fullsingle_during_sections_display_upper", true))
					{
						do_action ("ws_theme__during_fullsingle_during_sections_before_upper");
						/**/
						echo '<div id="fullsingle-upper-section" class="fullsingle-upper-section clearfix">' . "\n";
						/**/
						echo '<div id="fullsingle-share-save" class="fullsingle-share-save">' . "\n";
						echo '<a class="share-save"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/social-share-save.png") . '" alt="Share/Save" title="Share/Save" /></a>' . "\n";
						echo '</div>' . "\n";
						/**/
						edit_post_link ("Edit", '<div id="fullsingle-edit" class="fullsingle-edit">[ ', ' ]</div>');
						/**/
						echo '<div id="fullsingle-time" class="fullsingle-time">' . "\n";
						echo c_ws_theme__utilities::get ("the_time", get_option ("date_format")) . ' ' . c_ws_theme__utilities::get ("the_time", get_option ("time_format")) . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div id="fullsingle-title" class="fullsingle-title">' . "\n";
						echo '<h1>' . c_ws_theme__utils_strings::cut_string (( ($ws_theme__temp_s = get_post_meta (get_the_ID (), "h1_title", true)) ? $ws_theme__temp_s : c_ws_theme__utilities::get ("the_title")), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["fullsingle"]) . '</h1>' . "\n";
						echo ($ws_theme__temp_s = get_post_meta (get_the_ID (), "h1_desc", true)) ? '<div>' . $ws_theme__temp_s . '</div>' . "\n" : '';
						echo '</div>' . "\n";
						/**/
						echo '<div id="fullsingle-cats" class="fullsingle-cats">' . "\n";
						echo '<strong>Posted In:</strong> ' . implode ($GLOBALS["WS_THEME__"]["c"]["single_category_sep"], array_slice (preg_split ("/\<\|\>/", c_ws_theme__utilities::get ("the_category", "<|>")), 0, $GLOBALS["WS_THEME__"]["c"]["single_category_max"])) . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div id="fullsingle-author" class="fullsingle-author">' . "\n";
						echo '<strong>By:</strong> ' . c_ws_theme__utilities::get ("the_author_posts_link") . "\n";
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_fullsingle_during_sections_after_upper");
					}
				/*
				Build the middle section.
				*/
				if (apply_filters ("ws_theme__during_fullsingle_during_sections_display_middle", true))
					{
						do_action ("ws_theme__during_fullsingle_during_sections_before_middle");
						/**/
						echo '<div id="fullsingle-middle-section" class="fullsingle-middle-section clearfix">' . "\n";
						/**/
						echo '<div id="fullsingle-content" class="fullsingle-content clearfix">' . "\n";
						/**/
						do_action ("ws_theme__during_fullsingle_before_content");
						/**/
						echo '<!-- custom-content-before -->' . "\n";
						/**/
						if (apply_filters ("ws_theme__during_fullsingle_display_content", true))
							echo c_ws_theme__utilities::get ("the_content") . "\n";
						/**/
						echo '<!-- custom-content -->' . "\n";
						/**/
						echo '<!-- custom-content-after -->' . "\n";
						/**/
						do_action ("ws_theme__during_fullsingle_after_content");
						/**/
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_fullsingle_during_sections_after_middle");
					}
				/*
				Build the lower section.
				*/
				if (apply_filters ("ws_theme__during_fullsingle_during_sections_display_lower", true))
					{
						do_action ("ws_theme__during_fullsingle_during_sections_before_lower");
						/**/
						echo '<div id="fullsingle-lower-section" class="fullsingle-lower-section clearfix">' . "\n";
						/**/
						wp_link_pages ("before=" . urlencode /* These are paginated using: <!--nextpage--> */
						('<div id="fullsingle-lower-parts" class="fullsingle-lower-parts">') . "&after=" . urlencode ('</div>'));
						/**/
						the_tags ('<div id="fullsingle-tags" class="fullsingle-tags">' . "\n" ./**/
						'<strong>Tags:</strong> ',/**/
						", ", "\n" . '</div>' . "\n");
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_fullsingle_during_sections_after_lower");
					}
				/*
				Hook after sections.
				*/
				do_action ("ws_theme__during_fullsingle_after_sections");
				/*
				Possible comments. 
				*/
				comments_template ();
			}
		/*
		Close the fullsingle container.
		*/
		echo '</div>' . "\n";
		/*
		Close the fullsingle inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the fullsingle inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the fullsingle wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after fullsingle.
		*/
		do_action ("ws_theme__after_fullsingle");
		/*
		Get the footer.
		*/
		get_footer ();
	}
?>