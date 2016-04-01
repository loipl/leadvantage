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
if (!class_exists ("c_ws_theme__nav_menu"))
	{
		class c_ws_theme__nav_menu
			{
				/*
				Builds navigation menu items based on configuration options.
				This function limits the number of top level items to 3 until a configuration has taken place.
				
					$location indicates where this particular menu is displayed. Used in conjuction with register_nav_menu().
					$nav_layout_model is optional, providing a way to force a specific layout model.
					$custom and $custom_position are optional; for injecting additional items.
				*/
				public static function nav_menu_items ($location = FALSE, $nav_layout_model = FALSE, $custom = FALSE, $custom_position = FALSE)
					{
						$pages = array (); /* Initialize here so that hooks have a chance. */
						$categories = array (); /* Giving hooks a chance. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_nav_menu_items", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if ($nav_layout_model === "custom" || (!$nav_layout_model && $GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "custom"))
							{
								return apply_filters ("ws_theme__nav_menu_items", c_ws_theme__nav_menu_customs::custom_menu_items ($location, $custom, $custom_position), get_defined_vars ());
							}
						else if ($nav_layout_model === "page_cat_combo" || (!$nav_layout_model && $GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "page_cat_combo"))
							{
								$pages = (empty ($pages)) ? get_pages ("sort_column=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_column"]) . "&sort_order=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_order"]) . "&exclude=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["page_nav_exclusions"])) . "&include=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["page_nav_inclusions"])) . ( (!$GLOBALS["WS_THEME__"]["c"]["configured"]) ? "&number=3" : "")) : $pages;
								$categories = (empty ($categories)) ? get_categories ("title_li=&hide_empty=0&orderby=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_column"]) . "&order=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_order"]) . "&exclude=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["cat_nav_exclusions"])) . "&include=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["cat_nav_inclusions"]))) : $categories;
								/**/
								return apply_filters ("ws_theme__nav_menu_items", c_ws_theme__nav_menu_pages_categories::page_category_menu_items ($pages, $categories, $GLOBALS["WS_THEME__"]["o"]["nav_cats_title"], $GLOBALS["WS_THEME__"]["o"]["nav_cats_position"], $custom, $custom_position), get_defined_vars ());
							}
						else if ($nav_layout_model === "cat_page_combo" || (!$nav_layout_model && $GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "cat_page_combo"))
							{
								$categories = (empty ($categories)) ? get_categories ("title_li=&hide_empty=0&orderby=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_column"]) . "&order=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_order"]) . "&exclude=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["cat_nav_exclusions"])) . "&include=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["cat_nav_inclusions"])) . ( (!$GLOBALS["WS_THEME__"]["c"]["configured"]) ? "&number=3" : "")) : $categories;
								$pages = (empty ($pages)) ? get_pages ("sort_column=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_column"]) . "&sort_order=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_order"]) . "&exclude=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["page_nav_exclusions"])) . "&include=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["page_nav_inclusions"]))) : $pages;
								/**/
								return apply_filters ("ws_theme__nav_menu_items", c_ws_theme__nav_menu_categories_pages::category_page_menu_items ($categories, $pages, $GLOBALS["WS_THEME__"]["o"]["nav_pages_title"], $GLOBALS["WS_THEME__"]["o"]["nav_pages_position"], $custom, $custom_position), get_defined_vars ());
							}
						else if ($nav_layout_model === "pages" || (!$nav_layout_model && $GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "pages"))
							{
								$pages = (empty ($pages)) ? get_pages ("sort_column=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_column"]) . "&sort_order=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_order"]) . "&exclude=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["page_nav_exclusions"])) . "&include=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["page_nav_inclusions"])) . ( (!$GLOBALS["WS_THEME__"]["c"]["configured"]) ? "&number=3" : "")) : $pages;
								/**/
								return apply_filters ("ws_theme__nav_menu_items", c_ws_theme__nav_menu_pages::page_menu_items ($pages, $custom, $custom_position), get_defined_vars ());
							}
						else if ($nav_layout_model === "categories" || (!$nav_layout_model && $GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "categories"))
							{
								$categories = (empty ($categories)) ? get_categories ("title_li=&hide_empty=0&orderby=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_column"]) . "&order=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_order"]) . "&exclude=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["cat_nav_exclusions"])) . "&include=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["cat_nav_inclusions"])) . ( (!$GLOBALS["WS_THEME__"]["c"]["configured"]) ? "&number=3" : "")) : $categories;
								/**/
								return apply_filters ("ws_theme__nav_menu_items", c_ws_theme__nav_menu_categories::category_menu_items ($categories, $custom, $custom_position), get_defined_vars ());
							}
						else /* Else default to page/cat menu, because the option value is invalid or incompatible. */
							{
								$pages = (empty ($pages)) ? get_pages ("sort_column=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_column"]) . "&sort_order=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_order"]) . "&exclude=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["page_nav_exclusions"])) . "&include=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["page_nav_inclusions"])) . ( (!$GLOBALS["WS_THEME__"]["c"]["configured"]) ? "&number=3" : "")) : $pages;
								$categories = (empty ($categories)) ? get_categories ("title_li=&hide_empty=0&orderby=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_column"]) . "&order=" . urlencode ($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_order"]) . "&exclude=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["cat_nav_exclusions"])) . "&include=" . urlencode (implode (",", $GLOBALS["WS_THEME__"]["o"]["cat_nav_inclusions"]))) : $categories;
								/**/
								return apply_filters ("ws_theme__nav_menu_items", c_ws_theme__nav_menu_pages_categories::page_category_menu_items ($pages, $categories, $GLOBALS["WS_THEME__"]["o"]["nav_cats_title"], $GLOBALS["WS_THEME__"]["o"]["nav_cats_position"], $custom, $custom_position), get_defined_vars ());
							}
					}
			}
	}
?>