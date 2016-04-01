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
if (!class_exists ("c_ws_theme__home_page"))
	{
		class c_ws_theme__home_page
			{
				/*
				Filters Posts on the Home Page.
				Attach to: add_action("pre_get_posts");
				*/
				public static function home_page_filter (&$wp_query = FALSE)
					{
						static $initial_query = true; /* Tracks initial query filtering. */
						/**/
						do_action ("ws_theme__before_home_page_filter", get_defined_vars ());
						/**/
						if ($initial_query && !is_admin () && is_home () && is_object ($wp_query))
							{
								if ($GLOBALS["WS_THEME__"]["o"]["home_only_sticky"])
									{
										$stickies = get_option ("sticky_posts");
										$stickies = (empty ($stickies)) ? array (0): $stickies;
										$wp_query->set ("post__in", $stickies);
									}
								else /* Else we allow all Posts, but we can filter them by Category. */
									{
										if (!empty ($GLOBALS["WS_THEME__"]["o"]["home_cat_exclusions"]))
											{
												$wp_query->set ("category__not_in", $GLOBALS["WS_THEME__"]["o"]["home_cat_exclusions"]);
											}
										if (!empty ($GLOBALS["WS_THEME__"]["o"]["home_cat_inclusions"]))
											{
												$wp_query->set ("category__in", $GLOBALS["WS_THEME__"]["o"]["home_cat_inclusions"]);
											}
									}
								/**/
								remove_action ("pre_get_posts", "c_ws_theme__home_page::home_page_filter");
								/**/
								eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
								do_action ("ws_theme__during_home_page_filter", get_defined_vars ());
								unset ($__refs, $__v); /* Unset defined __refs, __v. */
							}
						/**/
						do_action ("ws_theme__after_home_page_filter", get_defined_vars ());
						/**/
						$initial_query = false; /* No longer. */
						/**/
						return; /* For uniformity. */
					}
			}
	}
?>