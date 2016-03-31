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
if (!class_exists ("c_ws_theme__nav_menu_categories_pages"))
	{
		class c_ws_theme__nav_menu_categories_pages
			{
				/*
				Builds a custom list of categories with nested pages too.
				$pages_parent_position: before-categories|before-category-[ID]|after-categories|after-category-[ID]
				$custom_position: before-categories|before-pages|before-category-[ID]|before-page-[ID]|after-categories|after-pages|after-category-[ID]|after-page-[ID]
				*/
				public static function category_page_menu_items ($categories = array (), $pages = array (), $pages_parent_title = "Home", $pages_parent_position = "before-categories", $custom = FALSE, $custom_position = "before-categories", $parent_category = 0)
					{
						static $keys_reindexed; /* Optimizes this routine. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_category_page_menu_items", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if (!$keys_re_indexed) /* Re-index all of the array keys. */
							{
								$categories = array_merge ((array)$categories);
								$pages = array_merge ((array)$pages);
								$keys_re_indexed = true;
							}
						/**/
						if ((int)$parent_category === 0) /* If in the top level; no parent === 0. */
							{
								if ($custom && $custom_position === "before-categories") /* Custom takes precedence here. */
									{
										$items .= $custom; /* Custom list items take precedence. */
									}
								/**/
								if ($pages_parent_position === "before-categories")
									{
										$items .= '<li class="pages' . ( (is_front_page ()) ? ' current' : '') . '">';
										$items .= '<a href="' . esc_attr (home_url ("/")) . '">' . $pages_parent_title . '</a>';
										$items .= '<ul>' . c_ws_theme__nav_menu_pages::page_menu_items ($pages, $custom, $custom_position, false) . '</ul>';
										$items .= '</li>';
									}
							}
						/**/
						foreach ($categories as $key => $category) /* Search through the array. */
							{
								if ((int)$category->category_parent === (int)$parent_category) /* Specific children. */
									{
										if ($custom && $custom_position === "before-category-" . $category->cat_ID)
											{
												$items .= $custom; /* Custom list items take precedence. */
											}
										/**/
										if ($pages_parent_position === "before-category-" . $category->cat_ID)
											{
												$items .= '<li class="pages' . ( (is_front_page ()) ? ' current' : '') . '">';
												$items .= '<a href="' . esc_attr (home_url ("/")) . '">' . $pages_parent_title . '</a>';
												$items .= '<ul>' . c_ws_theme__nav_menu_pages::page_menu_items ($pages, $custom, $custom_position, false) . '</ul>';
												$items .= '</li>';
											}
										/**/
										if ($children = c_ws_theme__nav_menu_categories_pages::category_page_menu_items ($categories, $pages, $pages_parent_title, $pages_parent_position, $custom, $custom_position, $category->cat_ID))
											{
												$items .= '<li' . ( (is_category ($category->cat_ID)) ? ' class="current"' : '') . '>';
												$items .= '<a href="' . esc_attr (get_category_link ($category->cat_ID)) . '">' . esc_html ($category->cat_name) . '</a>';
												$items .= '<ul>' . $children . '</ul>';
												$items .= '</li>';
											}
										else /* Here we just add the item, there are no children to consider. */
											{
												$items .= '<li' . ( (is_category ($category->cat_ID)) ? ' class="current"' : '') . '>';
												$items .= '<a href="' . esc_attr (get_category_link ($category->cat_ID)) . '">' . esc_html ($category->cat_name) . '</a>';
												$items .= '</li>';
											}
										/**/
										if ($custom && $custom_position === "after-category-" . $category->cat_ID)
											{
												$items .= $custom; /* Custom list items take precedence. */
											}
										/**/
										if ($pages_parent_position === "after-category-" . $category->cat_ID)
											{
												$items .= '<li class="pages' . ( (is_front_page ()) ? ' current' : '') . '">';
												$items .= '<a href="' . esc_attr (home_url ("/")) . '">' . $pages_parent_title . '</a>';
												$items .= '<ul>' . c_ws_theme__nav_menu_pages::page_menu_items ($pages, $custom, $custom_position, false) . '</ul>';
												$items .= '</li>';
											}
									}
							}
						/**/
						if ((int)$parent_category === 0) /* If in the top level; no parent === 0. */
							{
								if ($custom && $custom_position === "after-categories") /* Custom takes precedence here. */
									{
										$items .= $custom; /* Custom list items take precedence. */
									}
								/**/
								if ($pages_parent_position === "after-categories")
									{
										$items .= '<li class="pages' . ( (is_front_page ()) ? ' current' : '') . '">';
										$items .= '<a href="' . esc_attr (home_url ("/")) . '">' . $pages_parent_title . '</a>';
										$items .= '<ul>' . c_ws_theme__nav_menu_pages::page_menu_items ($pages, $custom, $custom_position, false) . '</ul>';
										$items .= '</li>';
									}
							}
						/**/
						return apply_filters ("ws_theme__category_page_menu_items", $items, get_defined_vars ());
					}
			}
	}
?>