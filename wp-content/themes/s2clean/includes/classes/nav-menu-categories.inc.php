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
if (!class_exists ("c_ws_theme__nav_menu_categories"))
	{
		class c_ws_theme__nav_menu_categories
			{
				/*
				Builds a custom list of categories.
				$custom_position: before-categories|before-category-[ID]|after-categories|after-category-[ID]
				*/
				public static function category_menu_items ($categories = array (), $custom = FALSE, $custom_position = "before-categories", $parent_category = 0)
					{
						static $keys_reindexed; /* Optimizes this routine. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_category_menu_items", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if (!$keys_re_indexed) /* Re-index all of the array keys. */
							{
								$categories = array_merge ((array)$categories);
								$keys_re_indexed = true;
							}
						/**/
						if ((int)$parent_category === 0) /* If in the top level; no parent === 0. */
							{
								if ($custom && $custom_position === "before-categories")
									{
										$items .= $custom; /* List items. */
									}
							}
						/**/
						foreach ($categories as $key => $category) /* Search through the array. */
							{
								if ((int)$category->category_parent === (int)$parent_category) /* Specific children. */
									{
										if ($custom && $custom_position === "before-category-" . $category->cat_ID)
											{
												$items .= $custom; /* List items. */
											}
										/**/
										if ($children = c_ws_theme__nav_menu_categories::category_menu_items ($categories, $custom, $custom_position, $category->cat_ID))
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
												$items .= $custom; /* List items. */
											}
									}
							}
						/**/
						if ((int)$parent_category === 0) /* If in the top level; no parent === 0. */
							{
								if ($custom && $custom_position === "after-categories")
									{
										$items .= $custom; /* List items. */
									}
							}
						/**/
						return apply_filters ("ws_theme__category_menu_items", $items, get_defined_vars ());
					}
			}
	}
?>