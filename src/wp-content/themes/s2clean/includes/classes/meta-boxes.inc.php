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
if (!class_exists ("c_ws_theme__meta_boxes"))
	{
		class c_ws_theme__meta_boxes
			{
				/*
				Add Meta Boxes.
				* Note: WordPress® also calls this Hook with $type set to: `link` and `comment` Possibly others.
						Thus, the need for: `in_array ($type, array_keys (get_post_types ()))`.
				Attach to: add_action("add_meta_boxes");
				*/
				public static function add_admin_meta_boxes ($type = FALSE)
					{
						do_action ("ws_theme__before_add_admin_meta_boxes", get_defined_vars ());
						/**/
						if (in_array ($type, array_keys (get_post_types ())) && !in_array ($type, array ("link", "comment", "revision", "attachment", "nav_menu_item", "page")))
							if (apply_filters ("ws_theme__during_add_admin_meta_boxes_post_template_options_" . $type . "_display", true, get_defined_vars ()))
								add_meta_box ("ws-theme--meta-box-post-template", ucwords ($type) . " Template", "c_ws_theme__meta_box_options::build_post_template_options", $type, "side", "low");
						/**/
						if (in_array ($type, array_keys (get_post_types ())) && !in_array ($type, array ("link", "comment", "revision", "attachment", "nav_menu_item")))
							if (apply_filters ("ws_theme__during_add_admin_meta_boxes_custom_field_instructions_" . $type . "_display", true, get_defined_vars ()))
								add_meta_box ("ws-theme--meta-box-custom-fields", "Custom Fields Used In Theme", "c_ws_theme__meta_box_options::build_custom_field_instructions", $type, "normal");
						/**/
						do_action ("ws_theme__after_add_admin_meta_boxes", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>