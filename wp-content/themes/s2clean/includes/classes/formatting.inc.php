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
if (!class_exists ("c_ws_theme__formatting"))
	{
		class c_ws_theme__formatting
			{
				/*
				Configures formatting Filters.
				Attach to: add_action("init");
				*/
				public static function configure_formatting ()
					{
						do_action ("ws_theme__before_configure_formatting", get_defined_vars ());
						/**/
						add_filter ("excerpt_length", "c_ws_theme__formatting_filters::excerpt_length") . add_filter ("excerpt_more", "c_ws_theme__formatting_filters::excerpt_more");
						/**/
						if ($GLOBALS["WS_THEME__"]["o"]["disable_formatting"] === "yes")
							{
								if (($removed = true)) /* Remove these runtime filters. */
									{
										remove_filter ("the_content", "wptexturize") . remove_filter ("the_content", "convert_smilies") . remove_filter ("the_content", "convert_chars") . remove_filter ("the_content", "wpautop") . remove_filter ("the_content", "shortcode_unautop");
										remove_filter ("the_excerpt", "wptexturize") . remove_filter ("the_excerpt", "convert_smilies") . remove_filter ("the_excerpt", "convert_chars") . remove_filter ("the_excerpt", "wpautop") . remove_filter ("the_excerpt", "shortcode_unautop");
									}
								/**/
								if ($removed && current_user_can ("edit_posts")) /* Is this a capable User? */
									{
										remove_filter ("content_save_pre", "balanceTags", 50) . remove_filter ("excerpt_save_pre", "balanceTags", 50);
										remove_filter ("comment_save_pre", "balanceTags", 50) . remove_filter ("pre_comment_content", "balanceTags", 50) . remove_filter ("pre_comment_content", "wp_rel_nofollow", 15);
										/**/
										add_action ("admin_init", "c_ws_theme__formatting_editor::configure_editor");
									}
							}
						else if ($GLOBALS["WS_THEME__"]["o"]["disable_formatting"] === "raw")
							{
								if (($conditioned = true)) /* Condition these runtime Filters. */
									{
										remove_filter ("the_content", "wptexturize") . remove_filter ("the_content", "convert_smilies") . remove_filter ("the_content", "convert_chars") . remove_filter ("the_content", "wpautop") . remove_filter ("the_content", "shortcode_unautop");
										remove_filter ("the_excerpt", "wptexturize") . remove_filter ("the_excerpt", "convert_smilies") . remove_filter ("the_excerpt", "convert_chars") . remove_filter ("the_excerpt", "wpautop") . remove_filter ("the_excerpt", "shortcode_unautop");
										/**/
										add_filter ("the_content", "c_ws_theme__formatting_filters::conditional_formatting") . add_filter ("the_excerpt", "c_ws_theme__formatting_filters::conditional_formatting");
									}
								/**/
								if ($conditioned && current_user_can ("edit_posts")) /* Is this a capable user? */
									{
										remove_filter ("content_save_pre", "balanceTags", 50) . remove_filter ("excerpt_save_pre", "balanceTags", 50);
										remove_filter ("comment_save_pre", "balanceTags", 50) . remove_filter ("pre_comment_content", "balanceTags", 50) . remove_filter ("pre_comment_content", "wp_rel_nofollow", 15);
										/**/
										add_filter ("content_save_pre", "c_ws_theme__formatting_filters::conditional_balancing", 50) . add_filter ("excerpt_save_pre", "c_ws_theme__formatting_filters::conditional_balancing", 50);
										add_filter ("comment_save_pre", "c_ws_theme__formatting_filters::conditional_balancing", 50) . add_filter ("pre_comment_content", "c_ws_theme__formatting_filters::conditional_balancing", 50) . add_filter ("pre_comment_content", "c_ws_theme__formatting_filters::conditional_nofollow", 15);
										/**/
										add_action ("admin_init", "c_ws_theme__formatting_editor::configure_editor");
									}
							}
						/**/
						add_filter ("the_content", "c_ws_theme__formatting_filters::strip_raw_tags") . add_filter ("the_excerpt", "c_ws_theme__formatting_filters::strip_raw_tags") . add_filter ("comment_text", "c_ws_theme__formatting_filters::strip_raw_tags");
						/**/
						do_action ("ws_theme__after_configure_formatting", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>