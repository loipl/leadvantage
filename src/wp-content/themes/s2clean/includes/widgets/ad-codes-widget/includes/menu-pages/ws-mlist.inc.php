<?php
/**
* Newsletter/Feed Boxes for Menu Pages.
*
* Copyright: © 2009-2011
* {@link http://www.websharks-inc.com/ WebSharks, Inc.}
* ( coded in the USA )
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package s2Member\Menu_Pages
* @since 110524RC
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
/**/
if (!class_exists ("c_ws_plugin__s2member_menu_pages_ws_mlist"))
	{
		/**
		* Newsletter/Feed Boxes for Menu Pages.
		*
		* @package s2Member\Menu_Pages
		* @since 110531
		*/
		class c_ws_plugin__s2member_menu_pages_ws_mlist
			{
				public function __construct ()
					{
						if (is_object ($user = wp_get_current_user ()) && ($user_id = $user->ID))
							{
								echo '<form id="ws-mlist-form" action="http://websharks-inc.us1.list-manage1.com/subscribe/post?u=8f347da54d66b5298d13237d9&amp;id=19e9d213bc" method="post" target="_blank">' . "\n";
								/**/
								if (!is_ssl ()) /* Feed panel. */
									{
#										echo '<div class="ws-menu-page-r-group-header">' . "\n";
#										echo '<ins class="open">-</ins>Latest News<em>!</em>' . "\n";
#										echo '</div>' . "\n";
#										/**/
#										echo '<div class="ws-menu-page-r-group" style="display:block;">' . "\n";
#										echo '<script type="text/javascript" src="http://feeds.feedburner.com/s2member-updates?format=sigpro&amp;nItems=3&amp;openLinks=new&amp;displayTitle=false&amp;displayFeedIcon=false&amp;displayExcerpts=false&amp;displayAuthor=false&amp;displayDate=false&amp;displayEnclosures=false&amp;displayLinkToFeed=false"></script>' . "\n";
#										echo '</div>' . "\n";
									}
								/**/
								echo '<div class="ws-menu-page-r-group-header">' . "\n";
								echo '<ins>+</ins>Email Updates<em>!</em>' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div class="ws-menu-page-r-group">' . "\n";
								/**/
								echo '<div id="ws-mlist-div-fname">' . "\n";
								echo '<label for="ws-mlist-fname">First Name:</label><br />' . "\n";
								echo '<input type="text" name="FNAME" id="ws-mlist-fname" value="' . esc_attr ($user->first_name) . '" />' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div id="ws-mlist-div-lname">' . "\n";
								echo '<label for="ws-mlist-lname">Last Name:</label><br />' . "\n";
								echo '<input type="text" name="LNAME" id="ws-mlist-lname" value="' . esc_attr ($user->last_name) . '" />' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div id="ws-mlist-div-email">' . "\n";
								echo '<label for="ws-mlist-email">Email Address: *</label><br />' . "\n";
								echo '<input type="text" name="EMAIL" id="ws-mlist-email" value="' . format_to_edit ($user->user_email) . '" />' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div id="ws-mlist-div-groups">' . "\n";
								echo '<label>Interest Group(s):</label>' . "\n";
								echo '<ul>' . "\n";
								echo '<li><input type="checkbox" value="1" name="group[1][1]" id="ws-mlist-group-1-1" checked="checked"><label for="ws-mlist-group-1-1">WordPress® Themes</label></li>' . "\n";
								echo '<li><input type="checkbox" value="2" name="group[1][2]" id="ws-mlist-group-1-2" checked="checked"><label for="ws-mlist-group-1-2">All WordPress® Plugins</label></li>' . "\n";
								echo '<li><input type="checkbox" value="4" name="group[1][4]" id="ws-mlist-group-1-4"><label for="ws-mlist-group-1-4">s2Member®/s2Member Pro</label></li>' . "\n";
								echo '</ul>' . "\n";
								echo '</div>' . "\n";
								/**/
								if (!is_ssl ()) /* Only display this whenever we are NOT inside an SSL-enabled administrative panel. */
									{
										echo '<div id="ws-mlist-div-subs">' . "\n";
										echo '<script type="text/javascript" src="http://websharks-inc.us1.list-manage.com/subscriber-count?b=31&u=8c67d547-edf6-41c5-807d-2d2d0e6cffd1&id=19e9d213bc"></script>' . "\n";
										echo '</div>' . "\n";
									}
								/**/
								echo '<div id="ws-mlist-div-priv">' . "\n";
								echo '( <a href="' . esc_attr (c_ws_plugin__s2member_readmes::parse_readme_value ("Privacy URI")) . '" target="_blank">we DO respect your privacy</a> )' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '<div id="ws-mlist-div-submit">' . "\n";
								echo '<input type="submit" value="Subscribe" name="subscribe" />' . "\n";
								echo '</div>' . "\n";
								/**/
								echo '</div>' . "\n";
								/**/
								echo '</form>' . "\n";
							}
					}
			}
	}
/**/
new c_ws_plugin__s2member_menu_pages_ws_mlist ();
?>