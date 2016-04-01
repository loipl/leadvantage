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
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		get_header ();
		/*
		Hook before index.
		*/
		do_action ("ws_theme__before_index");
		/*
		Open the index wrapper.
		*/
		echo '<div id="index-wrapper" class="index-wrapper">' . "\n";
		/*
		Open the index inner wrapper a.
		*/
		echo '<div id="index-inner-wrapper-a" class="index-inner-wrapper-a">' . "\n";
		/*
		Open the index inner wrapper b.
		*/
		echo '<div id="index-inner-wrapper-b" class="index-inner-wrapper-b">' . "\n";
		/*
		Open the index container.
		*/
		echo '<div id="index-container" class="index-container clearfix">' . "\n";
		/*
		Check if we have any posts in the index.
		*/
		if (have_posts () && the_post () !== "nill" && rewind_posts () !== "nill" && is_object ($post) && apply_filters ("ws_theme__during_index_display_title", true))
			{
				do_action ("ws_theme__during_index_before_title");
				/*
				Establish index title.
				*/
				if (is_404 ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('404 Error: Not Found', $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '</h1>' . "\n";
						echo '<div>Sorry, the page you requested could not be found.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_home ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ($GLOBALS["WS_THEME__"]["o"]["home_h1_title"], $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '</h1>' . "\n";
						echo '<div>' . $GLOBALS["WS_THEME__"]["o"]["home_h1_desc"] . '</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_category () && ($ws_theme__temp_s = get_query_var ("cat")))
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string (( ($GLOBALS["WS_THEME__"]["c"]["index_category_type"] === "single") ? c_ws_theme__utilities::get ("single_cat_title") : preg_replace ("/ &raquo; $/", "", get_category_parents ($ws_theme__temp_s, true, " &raquo; "))), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '</h1>' . "\n";
						echo ( ($ws_theme__temp_s = c_ws_theme__utilities::get ("category_description", $ws_theme__temp_s)) && trim ($ws_theme__temp_s) !== "<br />") ? '<div>' . $ws_theme__temp_s . '</div>' . "\n" : '';
						echo '</div>' . "\n";
					}
				else if (is_search ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Search Results For "' . c_ws_theme__utilities::get ("the_search_query"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_tag ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Tagged "' . c_ws_theme__utilities::get ("single_tag_title"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_date () && is_time ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive For "' . c_ws_theme__utilities::get ("the_time", "F jS, Y g:i a"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_date () && is_day ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive For "' . c_ws_theme__utilities::get ("the_time", "F jS, Y"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_date () && is_month ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive For "' . c_ws_theme__utilities::get ("the_time", "F, Y"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_date () && is_year ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive For "' . c_ws_theme__utilities::get ("the_time", "Y"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_author ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Posts By "' . c_ws_theme__utilities::get ("the_author"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '</div>' . "\n";
					}
				else if (!is_home ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive Index', $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '</h1>' . "\n";
						echo '</div>' . "\n";
					}
				/*
				Hook during index, after title.
				*/
				do_action ("ws_theme__during_index_after_title");
				/*
				Build in the upper navigation.
				*/
				if ((is_paged () || $wp_query->found_posts > get_option ("posts_per_page")) && ($ws_theme__temp_s = get_query_var ("paged")) !== "nill" && apply_filters ("ws_theme__during_index_display_upper_nav", true))
					{
						echo '<div id="index-upper-nav" class="index-upper-nav clearfix">' . "\n";
						/**/
						echo '<div id="index-upper-nav-page" class="index-upper-nav-page">' . "\n";
						echo 'Page ' . (($ws_theme__temp_s) ? $ws_theme__temp_s : 1) . ' of ' . $wp_query->max_num_pages . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div id="index-upper-nav-next" class="index-upper-nav-next">' . "\n";
						echo c_ws_theme__utilities::get ("next_posts_link", '<span></span>') . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div id="index-upper-nav-prev" class="index-upper-nav-prev">' . "\n";
						echo c_ws_theme__utilities::get ("previous_posts_link", '<span></span>') . "\n";
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
					}
				/*
				Build the archive-view; the list of posts.
				*/
				if (apply_filters ("ws_theme__during_index_display_posts", true))
					{
						do_action ("ws_theme__during_index_before_posts");
						/*
						Open the index posts.
						*/
						echo '<div id="index-posts" class="index-posts">' . "\n";
						/*
						Handle the loop here.
						*/
						while (have_posts () && the_post () !== "nill" && is_object ($post))
							{
								if (apply_filters ("ws_theme__during_index_during_posts_display_post", true))
									{
										do_action ("ws_theme__during_index_during_posts_before_post");
										/*
										Open the post.
										*/
										echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '" class="index-post' . ( (is_sticky ()) ? ' index-post-sticky' : '') . '">' . "\n";
										/*
										Open the post wrapper.
										*/
										echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-wrapper" class="index-post-wrapper">' . "\n";
										/*
										Open the post inner wrapper a.
										*/
										echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-inner-wrapper-a" class="index-post-inner-wrapper-a">' . "\n";
										/*
										Open the post inner wrapper b.
										*/
										echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-inner-wrapper-b" class="index-post-inner-wrapper-b">' . "\n";
										/*
										Open the post container.
										*/
										echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-container" class="index-post-container clearfix">' . "\n";
										/*
										Build the post sections.
										*/
										if (apply_filters ("ws_theme__during_index_during_posts_during_post_display_sections", true))
											{
												do_action ("ws_theme__during_index_during_posts_during_post_before_sections");
												/*
												Build the upper section.
												*/
												if (apply_filters ("ws_theme__during_index_during_posts_during_post_during_sections_display_upper", true))
													{
														do_action ("ws_theme__during_index_during_posts_during_post_during_sections_before_upper");
														/**/
														echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-upper-section" class="index-post-upper-section clearfix">' . "\n";
														/**/
														echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-date" class="index-post-date">' . "\n";
														echo c_ws_theme__utilities::get ("the_time", get_option ("date_format")) . "\n";
														echo '</div>' . "\n";
														/**/
														edit_post_link ("Edit", '<div id="index-post-' . esc_attr (get_the_ID ()) . '-edit" class="index-post-edit">[ ', ' ]</div>');
														/**/
														echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-title" class="index-post-title">' . "\n";
														echo '<a href="' . esc_attr (get_permalink ()) . '" rel="bookmark">' . c_ws_theme__utils_strings::cut_string (c_ws_theme__utilities::get ("the_title"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index_post"]) . '</a>' . "\n";
														echo '</div>' . "\n";
														/**/
														echo '</div>' . "\n";
														/**/
														do_action ("ws_theme__during_index_during_posts_during_post_during_sections_after_upper");
													}
												/*
												Build the middle section.
												*/
												if (apply_filters ("ws_theme__during_index_during_posts_during_post_during_sections_display_middle", true))
													{
														do_action ("ws_theme__during_index_during_posts_during_post_during_sections_before_middle");
														/**/
														echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-middle-section" class="index-post-middle-section clearfix">' . "\n";
														/**/
														if ($GLOBALS["WS_THEME__"]["o"]["display_excerpts"] === "always" || ($GLOBALS["WS_THEME__"]["o"]["display_excerpts"] === "search" && is_search ()))
															{
																echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-excerpt" class="index-post-excerpt clearfix">' . "\n";
																/**/
																if ($ws_theme__temp_s = get_post_meta (get_the_ID (), "thumbnail", true))
																	echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-excerpt-thumb" class="index-post-excerpt-thumb">' . "\n" ./**/
																	'<a href="' . esc_attr (get_permalink ()) . '" rel="bookmark"><img src="' . esc_attr ($ws_theme__temp_s) . '" alt="Thumbnail" title="' . esc_attr (c_ws_theme__utilities::get ("the_title_attribute")) . '" /></a>' . "\n" ./**/
																	'</div>' . "\n";
																else if (has_post_thumbnail ())
																	echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-excerpt-thumb" class="index-post-excerpt-thumb">' . "\n" ./**/
																	'<a href="' . esc_attr (get_permalink ()) . '" rel="bookmark">' . c_ws_theme__utilities::get ("the_post_thumbnail", "post-thumbnail", array ("alt" => "Thumbnail", "title" => c_ws_theme__utilities::get ("the_title_attribute"))) . '</a>' . "\n" ./**/
																	'</div>' . "\n";
																/**/
																echo c_ws_theme__utilities::get ("the_excerpt") . "..." . "\n"; /* If an excerpt does not exist, one will be generated. */
																/**/
																echo '</div>' . "\n";
															}
														else /* Otherwise we show the content, and allow the site owner to use the <!--more--> tag as needed. */
															{
																echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-excerpt" class="index-post-excerpt clearfix">' . "\n";
																echo c_ws_theme__utilities::get ("the_content", $GLOBALS["WS_THEME__"]["o"]["more_tag_label"]) . "\n";
																echo '</div>' . "\n";
															}
														/**/
														echo '</div>' . "\n";
														/**/
														do_action ("ws_theme__during_index_during_posts_during_post_during_sections_after_middle");
													}
												/*
												Build the lower section.
												*/
												if (apply_filters ("ws_theme__during_index_during_posts_during_post_during_sections_display_lower", true))
													{
														do_action ("ws_theme__during_index_during_posts_during_post_during_sections_before_lower");
														/**/
														echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-lower-section" class="index-post-lower-section clearfix">' . "\n";
														/**/
														echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-comments" class="index-post-comments">' . "\n";
														echo c_ws_theme__utilities::get ("comments_popup_link") . "\n";
														echo '</div>' . "\n";
														/**/
														the_tags ('<div id="index-post-' . esc_attr (get_the_ID ()) . '-tags" class="index-post-tags">' . "\n", ", ", "\n" . '</div>' . "\n");
														/**/
														echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-author" class="index-post-author">' . "\n";
														echo c_ws_theme__utilities::get ("the_author_posts_link") . "\n";
														echo '</div>' . "\n";
														/**/
														echo '<div id="index-post-' . esc_attr (get_the_ID ()) . '-cats" class="index-post-cats">' . "\n";
														echo c_ws_theme__utilities::get ("the_category", ", ") . "\n";
														echo '</div>' . "\n";
														/**/
														echo '</div>' . "\n";
														/**/
														do_action ("ws_theme__during_index_during_posts_during_post_during_sections_after_lower");
													}
												/*
												Hook during post; after sections.
												*/
												do_action ("ws_theme__during_index_during_posts_during_post_after_sections");
											}
										/*
										Close the post container.
										*/
										echo '</div>' . "\n";
										/*
										Close the post inner wrapper b.
										*/
										echo '</div>' . "\n";
										/*
										Close the post inner wrapper a.
										*/
										echo '</div>' . "\n";
										/*
										Close the post wrapper.
										*/
										echo '</div>' . "\n";
										/*
										Close the post.
										*/
										echo '</div>' . "\n";
										/*
										Hook after post.
										*/
										do_action ("ws_theme__during_index_during_posts_after_post");
									}
							}
						/*
						Close the index posts.
						*/
						echo '</div>' . "\n";
						/*
						Hook during index, after posts.
						*/
						do_action ("ws_theme__during_index_after_posts");
					}
				/*
				Build in the lower navigation.
				*/
				if ((is_paged () || $wp_query->found_posts > get_option ("posts_per_page")) && ($ws_theme__temp_s = get_query_var ("paged")) !== "nill" && apply_filters ("ws_theme__during_index_display_lower_nav", true))
					{
						echo '<div id="index-lower-nav" class="index-lower-nav clearfix">' . "\n";
						/**/
						echo '<div id="index-lower-nav-page" class="index-lower-nav-page">' . "\n";
						echo 'Page ' . esc_html (($ws_theme__temp_s) ? $ws_theme__temp_s : 1) . ' of ' . esc_html ($wp_query->max_num_pages) . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div id="index-lower-nav-next" class="index-lower-nav-next">' . "\n";
						echo c_ws_theme__utilities::get ("next_posts_link", '<span></span>') . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div id="index-lower-nav-prev" class="index-lower-nav-prev">' . "\n";
						echo c_ws_theme__utilities::get ("previous_posts_link", '<span></span>') . "\n";
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
					}
			}
		/*
		Else there are no posts in the index.
		*/
		else if (apply_filters ("ws_theme__during_index_display_title", true)) /* Establish title. */
			{
				do_action ("ws_theme__during_index_before_title");
				/*
				Establish an empty index title.
				*/
				if (is_404 ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('404 Error: Not Found', $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '</h1>' . "\n";
						echo '<div>Sorry, the page you requested could not be found.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_home ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ($GLOBALS["WS_THEME__"]["o"]["home_h1_title"], $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '</h1>' . "\n";
						echo '<div>' . $GLOBALS["WS_THEME__"]["o"]["home_h1_desc"] . '</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_category () && ($ws_theme__temp_s = get_query_var ("cat")))
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string (( ($GLOBALS["WS_THEME__"]["c"]["index_category_type"] === "single") ? c_ws_theme__utilities::get ("single_cat_title") : preg_replace ("/ &raquo; $/", "", get_category_parents ($ws_theme__temp_s, true, " &raquo; "))), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '</h1>' . "\n";
						echo '<div>Sorry, there are no posts in this category yet.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_search ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Search Results For "' . c_ws_theme__utilities::get ("the_search_query"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '<div>Sorry, your search returned 0 results.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_tag ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Tagged "' . c_ws_theme__utilities::get ("single_tag_title"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '<div>Sorry, there are no posts with this tag yet.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_date () && is_time ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive For "' . c_ws_theme__utilities::get ("the_time", "F jS, Y g:i a"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '<div>Sorry, there are no posts with this date yet.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_date () && is_day ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive For "' . c_ws_theme__utilities::get ("the_time", "F jS, Y"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '<div>Sorry, there are no posts with this date yet.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_date () && is_month ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive For "' . c_ws_theme__utilities::get ("the_time", "F, Y"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '<div>Sorry, there are no posts with this date yet.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_date () && is_year ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive For "' . c_ws_theme__utilities::get ("the_time", "Y"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '<div>Sorry, there are no posts with this date yet.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (is_author ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Posts By "' . c_ws_theme__utilities::get ("the_author"), $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '"</h1>' . "\n";
						echo '<div>Sorry, there are no posts by this author yet.</div>' . "\n";
						echo '</div>' . "\n";
					}
				else if (!is_home ())
					{
						echo '<div id="index-title" class="index-title">' . "\n";
						do_action ("ws_theme__during_index_during_title_before");
						echo '<h1>' . c_ws_theme__utils_strings::cut_string ('Archive Index', $GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"]) . '</h1>' . "\n";
						echo '<div>Sorry, no posts were found.</div>' . "\n";
						echo '</div>' . "\n";
					}
				/*
				Hook during index, after title.
				*/
				do_action ("ws_theme__during_index_after_title");
			}
		/*
		Close the index container.
		*/
		echo '</div>' . "\n";
		/*
		Close the index inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the index inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the index wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after index.
		*/
		do_action ("ws_theme__after_index");
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