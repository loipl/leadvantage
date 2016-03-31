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
if (!class_exists ("c_ws_theme__formatting_filters"))
	{
		class c_ws_theme__formatting_filters
			{
				/*
				Handles `shortcode_unautop()` / `wpautop()` / `convert_chars()` / `convert_smilies()` / `wptexturize()`.
				If the content starts with <!--RAW-->, it will be returned raw.
				
				Attach to: add_filter("the_content") . add_filter("the_excerpt");
				*/
				public static function conditional_formatting ($data = FALSE)
					{
						if (preg_match ("/^(\<|&lt;)\!--RAW--(&gt;|\>)/i", trim ($data)))
							{
								return $data; /* Return RAW. No Filters applied. */
							}
						else /* Otherwise, formatting is to remain on. Smilies if enabled in: `convert_smilies()`. */
							{
								return shortcode_unautop (wpautop (convert_chars (convert_smilies (wptexturize ($data)))));
							}
					}
				/*
				Handles conditional tag balancing with `balanceTags()`.
				If content data starts with <!--RAW-->, it will be returned raw.
				
				Attach to: add_filter("content_save_pre"); when configuration allows.
				Attach to: add_filter("excerpt_save_pre"); when configuration allows.
				Attach to: add_filter("comment_save_pre"); when configuration allows.
				Attach to: add_filter("pre_comment_content"); when configuration allows.
				
				* Also supports <!--COMMENT-SEMI-RAW-->.
				*/
				public static function conditional_balancing ($data = FALSE)
					{
						if (preg_match ("/^(\<|&lt;)\!--(RAW|COMMENT-SEMI-RAW)--(&gt;|\>)/i", trim ($data)))
							{
								return $data; /* Return RAW. Balancing not applied. */
							}
						else if (get_option ("use_balanceTags")) /* Otherwise check option. */
							{
								return balanceTags ($data); /* Setting require balancing. */
							}
						else /* Otherwise, no balancing. */
							{
								return $data;
							}
					}
				/*
				Handles conditional nofollow attrs w/ `wp_rel_nofollow()`.
				If the content starts with <!--RAW-->, it will be returned raw.
				
				Attach to: add_filter("pre_comment_content"); when configuration allows.
				
				* Also supports <!--COMMENT-SEMI-RAW-->.
				*/
				public static function conditional_nofollow ($data = FALSE)
					{
						if (preg_match ("/^(\<|&lt;)\!--(RAW|COMMENT-SEMI-RAW)--(&gt;|\>)/i", trim ($data)))
							{
								return $data; /* Return RAW without nofollow. */
							}
						else /* Otherwise, return with wp_rel_nofollow(). */
							{
								return wp_rel_nofollow ($data);
							}
					}
				/*
				Disables the Visual Editor, and also disables related options dynamically.
				
				Attach to: add_filter("user_can_richedit");
				Attach to: add_filter("option_use_smilies") . add_filter("pre_option_use_smilies");
				Attach to: add_filter("option_use_balanceTags") . add_filter("pre_option_use_balanceTags");
				*/
				public static function disable_editor_ops ($value = FALSE)
					{
						global $wp_rich_edit; /* Must set this also. */
						/**/
						$wp_rich_edit = false; /* Flag this false. */
						/**/
						if (is_string ($value) && !is_numeric ($value))
							{
								return "false";
							}
						else if (is_string ($value) && is_numeric ($value))
							{
								return "0";
							}
						else if (is_bool ($value))
							{
								return false;
							}
						else if (is_integer ($value))
							{
								return 0;
							}
						else /* Default. */
							{
								return null;
							}
					}
				/*
				Removes <!--RAW--> and/or <!--COMMENT-SEMI-RAW-->.
				This Filter should be applied, even if/when the raw option is disabled, to keep things clean.
				Since we're providing a way to use the <!--(RAW|COMMENT-SEMI-RAW)--> tags, we need to clean them up.
				
				Attach to: add_filter("the_content") . add_filter("the_excerpt") . add_filter("comment_text");
				
				* This Filter should be applied with a low priority, so it runs after other Filters.
				*/
				public static function strip_raw_tags ($data = FALSE)
					{
						return preg_replace ("/^(\<|&lt;)\!--(RAW|COMMENT-SEMI-RAW)--(&gt;|\>)/i", "", trim ($data), 1);
					}
				/*
				Builds a `more-link` for excerpts.
				Attach to: add_filter("excerpt_more");
				*/
				public static function excerpt_more ($more = FALSE)
					{
						return ' <a href="' . esc_attr (get_permalink ()) . '" class="more-link" rel="bookmark">' . $GLOBALS["WS_THEME__"]["o"]["more_tag_label"] . '</a>';
					}
				/*
				Adjusts the length of excerpts.
				Attach to: add_filter("excerpt_length");
				*/
				public static function excerpt_length ($length = FALSE)
					{
						return (int)$GLOBALS["WS_THEME__"]["o"]["excerpt_words"];
					}
			}
	}
?>