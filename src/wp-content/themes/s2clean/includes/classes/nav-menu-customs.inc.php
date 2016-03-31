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
if (!class_exists ("c_ws_theme__nav_menu_customs"))
	{
		class c_ws_theme__nav_menu_customs
			{
				/*
				Builds a custom menu items list.
				$custom_position: before-items|before-item-[ID]|after-items
				$custom_position: after-item-[ID] is not supported here.
				*/
				public static function custom_menu_items ($location = FALSE, $custom = FALSE, $custom_position = "before-items")
					{
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_custom_menu_items", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$menu = $items = trim (wp_nav_menu (array ("theme_location" => $location, "fallback_cb" => false, "container" => false, "echo" => false)));
						/**/
						$items = preg_replace ("/^\<div([^\>]*)\>/i", "", preg_replace ("/\<\/div\>$/i", "", $items));
						$items = preg_replace ("/^\<ul([^\>]*)\>/i", "", preg_replace ("/\<\/ul\>$/i", "", $items));
						/**/
						$items = preg_replace ("/current-menu-item/", "current current-menu-item", $items);
						/**/
						if ($custom && $custom_position) /* Handle injection of custom items. */
							{
								if ($custom_position === "before-items")
									$items = $custom . $items;
								/**/
								else if (preg_match ("/^before-item-([0-9]+)$/", $custom_location, $m))
									$items = preg_replace ("/\<li id\=\"menu-item-" . preg_quote ($m[1], "/") . "\"([^\>]+)\>/", $custom . "$0", $items);
								/**/
								else if ($custom_position === "after-items")
									$items = $items . $custom;
							}
						/**/
						return apply_filters ("ws_theme__custom_menu_items", $items, get_defined_vars ());
					}
			}
	}
?>