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
		do_action ("ws_theme__before_comments");
		/*
		If this is singular and it's no longer Password protected; and if comments are open or it already has comments.
		Note: If it already has comments, we need to display those whenever possible. Otherwise, if comments are
		closed and there have never been any comments, there is no reason to show this at all. This provides
		the ability to disable comments on a Post or Page without triggering the 'comments are closed' msg.
		*/
		if (is_singular () && is_object ($post) && !post_password_required () && (comments_open () || have_comments ()) && apply_filters ("ws_theme__comments_display", true))
			{
				echo '<div class="clear"></div>' . "\n";
				/*
				Open the comments wrapper.
				*/
				echo '<div id="comments-wrapper" class="comments-wrapper">' . "\n";
				/*
				Open the comments inner wrapper a.
				*/
				echo '<div id="comments-inner-wrapper-a" class="comments-wrapper-a">' . "\n";
				/*
				Open the comments inner wrapper b.
				*/
				echo '<div id="comments-inner-wrapper-b" class="comments-wrapper-b">' . "\n";
				/*
				Open the comments container.
				*/
				echo '<div id="comments-container" class="comments-container clearfix">' . "\n";
				/*
				If we have comments.
				*/
				if (have_comments () && apply_filters ("ws_theme__during_comments_display_comments_spool", true))
					{
						do_action ("ws_theme__during_comments_before_comments_spool");
						/*
						Open the comments.
						*/
						echo '<div id="comments" class="comments">' . "\n";
						/*
						Hook during comments spool, before title.
						*/
						do_action ("ws_theme__during_comments_during_comments_spool_before_title");
						/*
						Build the comments title.
						*/
						echo '<div id="comments-title" class="comments-title">' . "\n";
						echo c_ws_theme__utilities::get ("comments_number") . ' on "' . c_ws_theme__utilities::get ("the_title") . '"' . "\n";
						echo '</div>' . "\n";
						/*
						Hook during comments spool, after title.
						*/
						do_action ("ws_theme__during_comments_during_comments_spool_after_title");
						/*
						Build in the upper navigation.
						*/
						if (get_comments_number () > get_option ("comments_per_page") && apply_filters ("ws_theme__during_comments_during_comments_spool_display_upper_nav", true))
							{
								echo '<div id="comments-upper-nav" class="comments-upper-nav clearfix">' . "\n";
								/**/
								echo '<div id="comments-upper-nav-next" class="comments-upper-nav-next">' . "\n";
								echo c_ws_theme__utilities::get ("next_comments_link") . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div id="comments-upper-nav-prev" class="comments-upper-nav-prev">' . "\n";
								echo c_ws_theme__utilities::get ("previous_comments_link") . "\n";
								echo '</div>' . "\n";
								/**/
								echo '</div>' . "\n";
							}
						/*
						Hook during comments spool, before list.
						*/
						do_action ("ws_theme__during_comments_during_comments_spool_before_list");
						/*
						Build in the list of comments.
						*/
						echo '<div id="comments-list" class="comments-list">' . "\n";
						echo '<ol>' . c_ws_theme__utilities::get ("wp_list_comments", "avatar_size=48&reply_text=Reply&login_text=Reply&callback=c_ws_theme__comments::comments") . '</ol>' . "\n";
						echo '</div>' . "\n";
						/*
						Hook during comments spool, after list.
						*/
						do_action ("ws_theme__during_comments_during_comments_spool_after_list");
						/*
						Build in the lower navigation.
						*/
						if (get_comments_number () > get_option ("comments_per_page") && apply_filters ("ws_theme__during_comments_during_comments_spool_display_lower_nav", true))
							{
								echo '<div id="comments-lower-nav" class="comments-lower-nav clearfix">' . "\n";
								/**/
								echo '<div id="comments-lower-nav-next" class="comments-lower-nav-next">' . "\n";
								echo c_ws_theme__utilities::get ("next_comments_link") . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div id="comments-lower-nav-prev" class="comments-lower-nav-prev">' . "\n";
								echo c_ws_theme__utilities::get ("previous_comments_link") . "\n";
								echo '</div>' . "\n";
								/**/
								echo '</div>' . "\n";
							}
						/*
						Build the comments closed message.
						*/
						if (!comments_open () && apply_filters ("ws_theme__during_comments_display_closed", true))
							{
								do_action ("ws_theme__during_comments_during_comments_spool_before_closed");
								/**/
								echo '<div id="comments-closed" class="comments-closed">' . "\n";
								echo 'Comments have been disabled here. This discussion has ended.' . "\n";
								echo '</div>' . "\n";
								/**/
								do_action ("ws_theme__during_comments_during_comments_spool_after_closed");
							}
						/*
						Close the comments.
						*/
						echo '</div>' . "\n";
						/*
						Hook after comments spool.
						*/
						do_action ("ws_theme__during_comments_after_comments_spool");
					}
				/*
				Else if comments have been closed.
				*/
				else if (!comments_open () && apply_filters ("ws_theme__during_comments_display_closed", true))
					{
						do_action ("ws_theme__during_comments_before_comments_spool");
						/*
						Open the comments.
						*/
						echo '<div id="comments" class="comments">' . "\n";
						/*
						Hook during comments spool, before closed message.
						*/
						do_action ("ws_theme__during_comments_during_comments_spool_before_closed");
						/*
						Build the comments closed message.
						*/
						echo '<div id="comments-closed" class="comments-closed">' . "\n";
						echo 'Sorry, comments have been disabled here.' . "\n";
						echo '</div>' . "\n";
						/*
						Hook during comments spool, after closed message.
						*/
						do_action ("ws_theme__during_comments_during_comments_spool_after_closed");
						/*
						Close the comments.
						*/
						echo '</div>' . "\n";
						/*
						Hook after comments spool.
						*/
						do_action ("ws_theme__during_comments_after_comments_spool");
					}
				/*
				If commenting has been opened.
				*/
				if (comments_open () && apply_filters ("ws_theme__comments_display_respond", true))
					{
						do_action ("ws_theme__during_comments_before_respond");
						/*
						Open the response.
						*/
						echo '<div id="respond" class="respond">' . "\n";
						/*
						Build the cancel comment reply link. 
						*/
						echo '<div id="respond-cancel" class="respond-cancel">' . "\n";
						echo c_ws_theme__utilities::get ("cancel_comment_reply_link", "Cancel Comment Reply") . "\n";
						echo '</div>' . "\n";
						/*
						Hook during reponse, before title.
						*/
						do_action ("ws_theme__during_comments_during_respond_before_title");
						/*
						Build the response title. 
						*/
						echo '<div id="respond-title" class="respond-title">' . "\n";
						echo c_ws_theme__utilities::get ("comment_form_title", "Leave A Comment", "Leave A Reply To %s") . "\n";
						echo '</div>' . "\n";
						/*
						Hook during response, after title.
						*/
						do_action ("ws_theme__during_comments_during_respond_after_title");
						/*
						If registration is required and they are not logged in. 
						*/
						if (get_option ("comment_registration") && !$GLOBALS["WS_THEME__"]["c"]["current_user"] && apply_filters ("ws_theme__during_comments_during_respond_display_requires_login", true))
							{
								do_action ("ws_theme__during_comments_during_respond_before_requires_login");
								/*
								Commenting requires them to be registered and logged in. 
								*/
								echo '<div id="respond-requires-login" class="respond-requires-login">' . "\n";
								echo 'You must be <a href="' . esc_attr (c_ws_theme__utilities::get ("wp_login_url", get_permalink ())) . '">logged in</a> to post a comment.' . "\n";
								echo '</div>' . "\n";
								/*
								Hook during response, after requires login.
								*/
								do_action ("ws_theme__during_comments_during_respond_after_requires_login");
							}
						/*
						Else, registration is not required; or there is a user that is already logged in. 
						*/
						else if (apply_filters ("ws_theme__during_comments_during_respond_display_form", true))
							{
								do_action ("ws_theme__during_comments_during_respond_before_form");
								/*
								Open the response form. 
								*/
								echo '<div id="respond-form" class="respond-form">' . "\n";
								/*
								Open the response form tag. 
								*/
								echo '<form action="' . site_url ("/wp-comments-post.php") . '" method="post">' . "\n";
								/*
								Hook during response form, before form fields.
								*/
								do_action ("ws_theme__during_comments_during_respond_during_form_before_fields");
								/*
								If the user is already logged in, no need to display author-email-url. 
								*/
								if ($GLOBALS["WS_THEME__"]["c"]["current_user"])
									{
										do_action ("ws_theme__during_comments_during_respond_during_form_before_current_user_fields");
										/*
										Build in the gravatar image for the form. 
										*/
										echo '<div id="respond-form-gravatar" class="respond-form-gravatar">' . "\n";
										echo get_avatar ($GLOBALS["WS_THEME__"]["c"]["current_user"]->ID, 96);
										echo '</div>' . "\n";
										/*
										The user is already logged in. 
										*/
										echo '<div id="respond-form-logged-in" class="respond-form-logged-in">' . "\n";
										echo 'Logged in as <a href="' . esc_attr (admin_url ("/profile.php")) . '">' . esc_html ($GLOBALS["WS_THEME__"]["c"]["current_user"]->display_name) . '</a>. <a href="' . esc_attr (c_ws_theme__utilities::get ("wp_logout_url", get_permalink ())) . '" title="Log out of this account">Log out &raquo;</a>' . "\n";
										echo '</div>' . "\n";
										/*
										Hook during fields for logged-in users; before comment/message field.
										*/
										do_action ("ws_theme__during_comments_during_respond_during_form_during_current_user_fields_before_comment");
										/*
										Build the input field for the comment itself. 
										*/
										echo '<div id="respond-form-comment" class="respond-form-comment">' . "\n";
										echo '<div>XHTML: feel free to use any of these tags.</div>' . "\n"; /* Toggler not used in this case. */
										echo '<div id="respond-form-comment-allowed-tags"><code>' . c_ws_theme__utilities::get ("allowed_tags") . '</code></div>' . "\n";
										echo '<textarea name="comment" cols="100%" rows="10" title="Type your comment here..." tabindex="40"></textarea>' . "\n";
										echo '</div>' . "\n";
										/*
										Hook after fields for logged-in users.
										*/
										do_action ("ws_theme__during_comments_during_respond_during_form_after_current_user_fields");
									}
								/*
								Else they are not logged in. 
								*/
								else /* This is the standard comment form. Most users are NOT logged-in. */
									{
										do_action ("ws_theme__during_comments_during_respond_during_form_before_guest_fields");
										/*
										Get the current commenter.
										*/
										$ws_theme__temp_a = wp_get_current_commenter ();
										/*
										Build in the gravatar image for the form. 
										*/
										echo '<div id="respond-form-gravatar" class="respond-form-gravatar">' . "\n";
										echo get_avatar ($ws_theme__temp_a["comment_author_email"], 96);
										echo '</div>' . "\n";
										/*
										Open the response form author-email-url. 
										*/
										echo '<div id="respond-form-author-email-url" class="respond-form-author-email-url">' . "\n";
										/*
										Hook during fields for guests; before author-email-url fields.
										*/
										do_action ("ws_theme__during_comments_during_respond_during_form_during_guest_fields_before_aeu");
										/*
										Build the input field for the response author. 
										*/
										echo '<div id="respond-form-author" class="respond-form-author">' . "\n"; /* Name field. */
										echo '<input type="text" name="author" value="' . format_to_edit ($ws_theme__temp_a["comment_author"]) . '" title="Name' . (get_option ("require_name_email") ? ' (required)' : '') . '" tabindex="10" />' . "\n";
										echo '</div>' . "\n";
										/*
										Build the input field for the response author email. 
										*/
										echo '<div id="respond-form-email" class="respond-form-email">' . "\n"; /* Email is private. */
										echo '<input type="text" name="email" value="' . format_to_edit ($ws_theme__temp_a["comment_author_email"]) . '" title="EMail' . (get_option ("require_name_email") ? ' (required, not shown)' : '') . '" tabindex="20" />' . "\n";
										echo '</div>' . "\n";
										/*
										Build the input field for the response author website. 
										*/
										echo '<div id="respond-form-url" class="respond-form-url">' . "\n";
										echo '<input type="text" name="url" value="' . format_to_edit ($ws_theme__temp_a["comment_author_url"]) . '" title="Website Url (completely optional)" tabindex="30" />' . "\n";
										echo '</div>' . "\n";
										/*
										Hook during fields for guests; after author-email-url fields.
										*/
										do_action ("ws_theme__during_comments_during_respond_during_form_during_guest_fields_after_aeu");
										/*
										Close the response form author-email-url.
										*/
										echo '</div>' . "\n";
										/*
										Hook during fields for guests; before comment/message field.
										*/
										do_action ("ws_theme__during_comments_during_respond_during_form_during_guest_fields_before_comment");
										/*
										Build the input field for the comment itself. 
										*/
										echo '<div id="respond-form-comment" class="respond-form-comment">' . "\n"; /* Toggler. */
										echo '<div>XHTML: feel free to use any of <a id="respond-form-comment-allowed-tags-link" href="#">these tags</a>.</div>' . "\n";
										echo '<div id="respond-form-comment-allowed-tags" style="display:none;"><code>' . c_ws_theme__utilities::get ("allowed_tags") . '</code></div>' . "\n";
										echo '<textarea name="comment" cols="100%" rows="10" title="Type your comment here..." tabindex="40"></textarea>' . "\n";
										echo '</div>' . "\n";
										/*
										Hook after fields for guests.
										*/
										do_action ("ws_theme__during_comments_during_respond_during_form_after_guest_fields");
									}
								/*
								Hook after response form fields.
								*/
								do_action ("ws_theme__during_comments_during_respond_during_form_after_fields");
								/*
								Build the input field for the submit button. 
								*/
								echo '<div id="respond-form-submit" class="respond-form-submit">' . "\n";
								echo '<input type="submit" tabindex="50" value="Submit Comment" />' . "\n";
								echo '</div>' . "\n";
								/*
								Build any hidden input fields needed. 
								*/
								echo '<div>' . c_ws_theme__utilities::get ("comment_id_fields") . '</div>' . "\n";
								/*
								Handle any comment form hooks. 
								*/
								do_action ("comment_form", get_the_ID ());
								/*
								Close the response form tag.
								*/
								echo '</form>' . "\n";
								/*
								Close the response form.
								*/
								echo '</div>' . "\n";
								/*
								Hook after response form.
								*/
								do_action ("ws_theme__during_comments_during_respond_after_form");
							}
						/*
						Close the response.
						*/
						echo '</div>' . "\n";
						/*
						Hook after response.
						*/
						do_action ("ws_theme__during_comments_after_respond");
					}
				/*
				Close the comments container.
				*/
				echo '</div>' . "\n";
				/*
				Close the comments inner wrapper b.
				*/
				echo '</div>' . "\n";
				/*
				Close the comments inner wrapper a.
				*/
				echo '</div>' . "\n";
				/*
				Close the comments wrapper.
				*/
				echo '</div>' . "\n";
			}
		/*
		Hook after comments.
		*/
		do_action ("ws_theme__after_comments");
	}
?>