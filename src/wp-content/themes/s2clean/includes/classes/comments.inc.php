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
if (!class_exists ("c_ws_theme__comments"))
	{
		class c_ws_theme__comments
			{
				/*
				Function for handling comment formatting.
				Called upon repeatedly by: wp_list_comments();
				*/
				public static function comments ($c = FALSE, $args = FALSE, $depth = FALSE)
					{
						global $wpdb; /* Global db object. */
						static $admins; /* Holds admin emails. */
						static $class = "even"; /* odd|even */
						/**/
						$GLOBALS["comment"] = $c; /* The comment obj. */
						/**/
						$type = get_comment_type (); /* Comment type. */
						/**/
						$url = c_ws_theme__utilities::get ("comment_author_url");
						/**/
						$email = c_ws_theme__utilities::get ("comment_author_email");
						/**/
						$class = ($class === "even") ? "odd" : "even";
						/**/
						if (!isset ($admins)) /* Only get once. */
							$admins = (array)c_ws_theme__utils_users::get_admin_emails ();
						/**/
						if ($email && in_array (strtolower ($email), $admins))
							$utype = "administrator"; /* Authored by admin. */
						else /* Otherwise, we'll consider them a public user. */
							$utype = "public"; /* Else public visitor. */
						/**/
						$admin_label = "Site Administrator"; /* Label for comments by admin. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_comments_walker", get_defined_vars ()); /* Hook. */
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if (($walker = apply_filters ("ws_theme__comments_walker", "", get_defined_vars ())))
							return call_user_func ($walker, get_defined_vars ()); /* Give plugins a chance. */
						/**/
						echo '<li class="' . esc_attr ($class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						/**/
						echo '<div class="' . esc_attr ("comment-wrapper " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo '<div class="' . esc_attr ("comment-inner-wrapper-a " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo '<div class="' . esc_attr ("comment-inner-wrapper-b " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo '<div class="' . esc_attr ("comment-container " . $class . " " . $type . "-type " . $utype . "-user clearfix") . '">' . "\n";
						echo '<div id="' . esc_attr ("comment-" . get_comment_ID ()) . '" class="' . esc_attr ("comment " . $class . " " . $type . "-type " . $utype . "-user clearfix") . '">' . "\n";
						/**/
						echo '<div class="' . esc_attr ("comment-avatar " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						if (preg_match ("/pingback/i", $type)) /* For pingbacks, we can show a special avatar to help users understand what they are looking at. */
							echo '<img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/avatar-ping.png") . '" class="avatar" alt=".">' . "\n";
						else if (preg_match ("/trackback/i", $type)) /* For trackbacks, we can show a special avatar to help users understand what they are looking at. */
							echo '<img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/avatar-trck.png") . '" class="avatar" alt=".">' . "\n";
						else /* Else we can just show the standard avatar using built-in defaults configured through wp. */
							echo get_avatar ($c, $args["avatar_size"]) . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div class="' . esc_attr ("comment-reply " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo c_ws_theme__utilities::get ("comment_reply_link", array_merge ($args, array ("depth" => $depth))) . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div class="' . esc_attr ("comment-link " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo '<a href="' . esc_attr (c_ws_theme__utilities::get ("comment_link", get_comment_ID ())) . '" rel="nofollow" title="Permalink">#</a>' . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div class="' . esc_attr ("comment-edit " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo c_ws_theme__utilities::get ("edit_comment_link", "Edit", "[ ", " ]") . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div class="' . esc_attr ("comment-details " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						if ($utype === "administrator") /* Label for site admins. */
							echo '<strong>' . $admin_label . '</strong><br />' . "\n";
						else if (!preg_match ("/pingback|trackback/", $type))
							echo 'Comment left on:<br />' . "\n"; /* Followed by date. */
						echo c_ws_theme__utilities::get ("comment_date", get_option ("date_format")) . ' at ' . c_ws_theme__utilities::get ("comment_date", get_option ("time_format")) . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div class="' . esc_attr ("comment-author " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						if ($url) /* The commenters website. Set as nofollow. These are links to other sites. */
							echo '<a href="' . esc_attr ($url) . '" rel="nofollow">' . c_ws_theme__utilities::get ("comment_author") . '</a> <span class="says">says:</span>' . "\n";
						else /* Else they did not submit a url with their comment. No a tag here. */
							echo c_ws_theme__utilities::get ("comment_author") . ' <span class="says">says:</span>' . "\n";
						echo '</div>' . "\n";
						/**/
						echo '<div class="' . esc_attr ("comment-body-wrapper " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo '<div class="' . esc_attr ("comment-body-inner-wrapper-a " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo '<div class="' . esc_attr ("comment-body-inner-wrapper-b " . $class . " " . $type . "-type " . $utype . "-user") . '">' . "\n";
						echo '<div class="' . esc_attr ("comment-body-container " . $class . " " . $type . "-type " . $utype . "-user clearfix") . '">' . "\n";
						echo '<div class="' . esc_attr ("comment-body " . $class . " " . $type . "-type " . $utype . "-user clearfix") . '">' . "\n";
						echo (!$c->comment_approved) ? /* Unapproved? */
						'Your comment is awaiting moderation ~' . "\n" : '';
						echo c_ws_theme__utilities::get ("comment_text") . "\n";
						echo '</div>' . "\n"; /* Close body. */
						echo '</div>' . "\n";
						echo '</div>' . "\n";
						echo '</div>' . "\n";
						echo '</div>' . "\n";
						/**/
						echo '</div>' . "\n";
						echo '</div>' . "\n";
						echo '</div>' . "\n";
						echo '</div>' . "\n";
						echo '</div>' . "\n";
						/**/
						return;
					}
			}
	}
?>