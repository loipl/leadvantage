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
Template Name: Column Layout ( w/sidebar )
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
		Hook before page.
		*/
		do_action ("ws_theme__before_page");
		/*
		Open the page wrapper.
		*/
		echo '<div id="page-wrapper" class="page-wrapper">' . "\n";
		/*
		Open the page inner wrapper a.
		*/
		echo '<div id="page-inner-wrapper-a" class="page-inner-wrapper-a">' . "\n";
		/*
		Open the page inner wrapper b.
		*/
		echo '<div id="page-inner-wrapper-b" class="page-inner-wrapper-b">' . "\n";
		/*
		Open the page container.
		*/
		echo '<div id="page-container" class="page-container clearfix">' . "\n";
		/*
		Check if we have this page in the archive.
		*/
		if (have_posts () && the_post () !== "nill" && is_object ($post) && apply_filters ("ws_theme__during_page_display_sections", true))
			{
				do_action ("ws_theme__during_page_before_sections");
				/*
				Build the upper section.
				*/
				if (apply_filters ("ws_theme__during_page_during_sections_display_upper", true))
					{
						do_action ("ws_theme__during_page_during_sections_before_upper");
						/**/
						echo '<div id="page-upper-section" class="page-upper-section clearfix">' . "\n";
						/**/
						echo '<div id="page-share-save" class="page-share-save">' . "\n";
						echo '<a class="share-save"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/social-share-save-small.png") . '" alt="Share/Save" title="Share/Save" /></a>' . "\n";
						echo '</div>' . "\n";
						/**/
						edit_post_link ("Edit", '<div id="page-edit" class="page-edit">[ ', ' ]</div>');
						/**/
						echo '<div id="page-title" class="page-title">' . "\n";
						echo '<h1>' . c_ws_theme__utils_strings::cut_string (( ($ws_theme__temp_s = get_post_meta (get_the_ID (), "h1_title", true)) ? $ws_theme__temp_s : c_ws_theme__utilities::get ("the_title")), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["page"]) . '</h1>' . "\n";
						echo ($ws_theme__temp_s = get_post_meta (get_the_ID (), "h1_desc", true)) ? '<div>' . $ws_theme__temp_s . '</div>' . "\n" : '';
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_page_during_sections_after_upper");
					}
				/*
				Build the middle section.
				*/
				if (apply_filters ("ws_theme__during_page_during_sections_display_middle", true))
					{
						do_action ("ws_theme__during_page_during_sections_before_middle");
						/**/
						echo '<div id="page-middle-section" class="page-middle-section clearfix">' . "\n";
						/**/
						echo '<div id="page-content" class="page-content clearfix">' . "\n";
						/**/
						do_action ("ws_theme__during_page_before_content");
						/**/
						echo '<!-- custom-content-before -->' . "\n";
						/**/
						if (apply_filters ("ws_theme__during_page_display_content", true))
							echo c_ws_theme__utilities::get ("the_content") . "\n";
						/**/
						echo '<!-- custom-content -->' . "\n";
						/**/
						echo '<!-- custom-content-after -->' . "\n";
						/**/
						do_action ("ws_theme__during_page_after_content");
						/**/
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_page_during_sections_after_middle");
					}
				/*
				Build the lower section.
				*/
				if (apply_filters ("ws_theme__during_page_during_sections_display_lower", true))
					{
						do_action ("ws_theme__during_page_during_sections_before_lower");
						/**/
						echo '<div id="page-lower-section" class="page-lower-section clearfix">' . "\n";
						/**/
						wp_link_pages ("before=" . urlencode /* These are paginated using: <!--nextpage--> */
						('<div id="page-lower-parts" class="page-lower-parts">') . "&after=" . urlencode ('</div>'));
						/**/
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_page_during_sections_after_lower");
					}
				/*
				Hook after sections.
				*/
				do_action ("ws_theme__during_page_after_sections");
				/*
				Possible comments. 
				*/
				comments_template ();
			}
		/*
		Close the page container.
		*/
		echo '</div>' . "\n";
		/*
		Close the page inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the page inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the page wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after page.
		*/
		do_action ("ws_theme__after_page");
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