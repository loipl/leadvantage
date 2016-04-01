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
Template Name: Archives Page ( displays archives )
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
				include_once TEMPLATEPATH . "/" . apply_filters ("ws_theme__archives_parent_template", "page.php");
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
		if (! ($ws_theme__temp_s_custom = "") && apply_filters ("ws_theme__display_archives", true) && ob_start ())
			{
				do_action ("ws_theme__before_archives");
				/*
				Build the archives by month.
				*/
				if (apply_filters ("ws_theme__during_archives_display_by_month", true))
					{
						do_action ("ws_theme__during_archives_before_by_month");
						/**/
						echo '<div id="archives-by-month" class="archives-by-month">' . "\n";
						echo '<h2>Archives By Month:</h2>' . "\n";
						echo '<ul>' . "\n";
						echo c_ws_theme__utilities::get ("wp_get_archives") . "\n";
						echo '</ul>' . "\n";
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_archives_after_by_month");
					}
				/*
				Build the archives by subject.
				*/
				if (apply_filters ("ws_theme__during_archives_display_by_subject", true))
					{
						do_action ("ws_theme__during_archives_before_by_subject");
						/**/
						echo '<div id="archives-by-subject" class="archives-by-subject">' . "\n";
						echo '<h2>Archives By Subject:</h2>' . "\n";
						echo '<ul>' . "\n";
						echo c_ws_theme__utilities::get ("wp_list_categories", "title_li=&hide_empty=0") . "\n";
						echo '</ul>' . "\n";
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_archives_after_by_subject");
					}
				/*
				Display last 30 posts.
				*/
				if (apply_filters ("ws_theme__during_archives_display_last_30_posts", true))
					{
						do_action ("ws_theme__during_archives_before_last_30_posts");
						/**/
						echo '<div id="last-30-posts" class="last-30-posts">' . "\n";
						echo '<h2>The Last 30 Posts:</h2>' . "\n";
						echo '<ul>' . "\n";
						$ws_theme__temp_o = new WP_Query (array ("post_type" => "post", "post_status" => "publish", "posts_per_page" => 30, "orderby" => "date", "order" => "DESC"));
						while ($ws_theme__temp_o->have_posts () && $ws_theme__temp_o->the_post () !== "nill" && is_object ($post))
							echo '<li><a href="' . esc_attr (get_permalink ()) . '" rel="bookmark">' . c_ws_theme__utilities::get ("the_title") . '</a> - ' . c_ws_theme__utilities::get ("the_time", get_option ("date_format")) . ' - ' . c_ws_theme__utilities::get ("comments_number") . '</li>' . "\n";
						echo '</ul>' . "\n";
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_archives_after_last_30_posts");
					}
				/*
				Hook after archives.
				*/
				do_action ("ws_theme__after_archives");
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