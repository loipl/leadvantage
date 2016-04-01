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
if (!class_exists ("c_ws_theme__nav_menu_pages_categories"))
	{
		class c_ws_theme__nav_menu_pages_categories
			{
				/*
				Builds a custom list of pages with nested categories too.
				$categories_parent_position: before-pages|before-page-[ID]|after-pages|after-page-[ID]
				$custom_position: before-pages|before-categories|before-page-[ID]|before-category-[ID]|after-pages|after-categories|after-page-[ID]|after-category-[ID]
				*/
				public static function page_category_menu_items ($pages = array (), $categories = array (), $categories_parent_title = "Our Blog", $categories_parent_position = "before-pages", $custom = FALSE, $custom_position = "before-pages", $handle_home_injection = TRUE, $home_injection_title = "Home", $parent_post = 0)
					{
						static $keys_reindexed; /* Optimizes this routine. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_page_category_menu_items", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if (!$keys_re_indexed) /* Re-index all of the array keys. */
							{
								$pages = array_merge ((array)$pages);
								$categories = array_merge ((array)$categories);
								$keys_re_indexed = true;
							}
						/**/
						if ((int)$parent_post === 0) /* If in the top level; no parent === 0. */
							{
								if ($handle_home_injection && get_option ("show_on_front") === "posts")
									{
										$items .= '<li' . ( (is_home ()) ? ' class="current"' : '') . '>';
										$items .= '<a href="' . esc_attr (home_url ("/")) . '">' . esc_html ($home_injection_title) . '</a>';
										$items .= '</li>';
									}
								/**/
								if ($custom && $custom_position === "before-pages") /* Custom takes precedence here. */
									{
										$items .= $custom; /* Custom list items take precedence. */
									}
								/**/
								if ($categories_parent_position === "before-pages")
									{
										$items .= '<li class="cats">';
										$items .= '<a href="#">' . $categories_parent_title . '</a>';
										$items .= '<ul>' . c_ws_theme__nav_menu_categories::category_menu_items ($categories, $custom, $custom_position) . '</ul>';
										$items .= '</li>';
									}
							}
						/**/
						foreach ($pages as $key => $page) /* Search through the array. */
							{
								if ((int)$page->post_parent === (int)$parent_post) /* Specific children. */
									{
										if ($custom && $custom_position === "before-page-" . $page->ID)
											{
												$items .= $custom; /* Custom list items take precedence. */
											}
										/**/
										if ($categories_parent_position === "before-page-" . $page->ID)
											{
												$items .= '<li class="cats">';
												$items .= '<a href="#">' . $categories_parent_title . '</a>';
												$items .= '<ul>' . c_ws_theme__nav_menu_categories::category_menu_items ($categories, $custom, $custom_position) . '</ul>';
												$items .= '</li>';
											}
										/**/
										if ($children = c_ws_theme__nav_menu_pages_categories::page_category_menu_items ($pages, $categories, $categories_parent_title, $categories_parent_position, $custom, $custom_position, $handle_home_injection, $home_injection_title, $page->ID))
											{
												$items .= '<li' . ( (is_page ($page->ID)) ? ' class="current"' : '') . '>';
												$items .= '<a href="' . esc_attr (get_page_link ($page->ID)) . '">' . esc_html ($page->post_title) . '</a>';
												$items .= '<ul>' . $children . '</ul>';
												$items .= '</li>';
											}
										else /* Here we just add the item, there are no children to consider. */
											{
												$items .= '<li' . ( (is_page ($page->ID)) ? ' class="current"' : '') . '>';
												$items .= '<a href="' . esc_attr (get_page_link ($page->ID)) . '">' . esc_html ($page->post_title) . '</a>';
												$items .= '</li>';
											}
										/**/
										if ($custom && $custom_position === "after-page-" . $page->ID)
											{
												$items .= $custom; /* Custom list items take precedence. */
											}
										/**/
										if ($categories_parent_position === "after-page-" . $page->ID)
											{
												$items .= '<li class="cats">';
												$items .= '<a href="#">' . $categories_parent_title . '</a>';
												$items .= '<ul>' . c_ws_theme__nav_menu_categories::category_menu_items ($categories, $custom, $custom_position) . '</ul>';
												$items .= '</li>';
											}
									}
							}
						/**/
						if ((int)$parent_post === 0) /* If in the top level; no parent === 0. */
							{
								if ($custom && $custom_position === "after-pages") /* Custom takes precedence here. */
									{
										$items .= $custom; /* Custom list items take precedence. */
									}
								/**/
								if ($categories_parent_position === "after-pages")
									{
										$items .= '<li class="cats">';
										$items .= '<a href="#">' . $categories_parent_title . '</a>';
										$items .= '<ul>' . c_ws_theme__nav_menu_categories::category_menu_items ($categories, $custom, $custom_position) . '</ul>';
										$items .= '</li>';
									}
							}
						/**/
						return apply_filters ("ws_theme__page_category_menu_items", $items, get_defined_vars ());
					}
			}
	}
?>