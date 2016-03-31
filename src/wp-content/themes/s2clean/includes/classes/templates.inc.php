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
if (!class_exists ("c_ws_theme__templates"))
	{
		class c_ws_theme__templates
			{
				/*
				Handles templates for Singles.
				Attach to: add_filter("single_template");
				*/
				public static function single_template ($template = FALSE)
					{
						global $post; /* Need global Post reference. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_single_template", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$type_template = "single-" . $post->post_type . ".php";
						$type_template_x = "single-" . $post->post_type . ".x.php";
						/**/
						$id_template = "single-" . $post->ID . ".php";
						$id_template_x = "single-" . $post->ID . ".x.php";
						/**/
						$_a = (array)get_post_meta ($post->ID, "_wp_post_template", true);
						$_wp_post_template = array_shift ($_a); /* First array element. */
						$_wp_post_template = ($_wp_post_template === "default") ? "" : $_wp_post_template;
						/**/
						if (file_exists (TEMPLATEPATH . "/" . $type_template))
							$template = TEMPLATEPATH . "/" . $type_template;
						/**/
						else if (file_exists (TEMPLATEPATH . "/" . $type_template_x))
							$template = TEMPLATEPATH . "/" . $type_template_x;
						/**/
						else if (file_exists (TEMPLATEPATH . "/" . $id_template))
							$template = TEMPLATEPATH . "/" . $id_template;
						/**/
						else if (file_exists (TEMPLATEPATH . "/" . $id_template_x))
							$template = TEMPLATEPATH . "/" . $id_template_x;
						/**/
						else if ($_wp_post_template && file_exists (TEMPLATEPATH . "/" . $_wp_post_template))
							$template = TEMPLATEPATH . "/" . $_wp_post_template;
						/**/
						else if ($default_template = $GLOBALS["WS_THEME__"]["c"]["default_single_template"])
							if (file_exists (TEMPLATEPATH . "/" . $default_template))
								$template = TEMPLATEPATH . "/" . $default_template;
						/**/
						return apply_filters ("ws_theme__single_template", $template, get_defined_vars ());
					}
				/*
				Handles templates for Pages.
				Attach to: add_filter("page_template");
				*/
				public static function page_template ($template = FALSE)
					{
						global $post; /* Need global Post reference. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_page_template", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$page_template = "page-" . $post->post_name . ".php";
						$page_template_x = "page-" . $post->post_name . ".x.php";
						/**/
						$id_template = "page-" . $post->ID . ".php";
						$id_template_x = "page-" . $post->ID . ".x.php";
						/**/
						$_wp_page_template = get_post_meta ($post->ID, "_wp_page_template", true);
						$_wp_page_template = ($_wp_page_template === "default") ? "" : $_wp_page_template;
						/**/
						if (file_exists (TEMPLATEPATH . "/" . $page_template))
							$template = TEMPLATEPATH . "/" . $page_template;
						/**/
						else if (file_exists (TEMPLATEPATH . "/" . $page_template_x))
							$template = TEMPLATEPATH . "/" . $page_template_x;
						/**/
						else if (file_exists (TEMPLATEPATH . "/" . $id_template))
							$template = TEMPLATEPATH . "/" . $id_template;
						/**/
						else if (file_exists (TEMPLATEPATH . "/" . $id_template_x))
							$template = TEMPLATEPATH . "/" . $id_template_x;
						/**/
						else if ($_wp_page_template && file_exists (TEMPLATEPATH . "/" . $_wp_page_template))
							$template = TEMPLATEPATH . "/" . $_wp_page_template;
						/**/
						else if ($default_template = $GLOBALS["WS_THEME__"]["c"]["default_page_template"])
							if (file_exists (TEMPLATEPATH . "/" . $default_template))
								$template = TEMPLATEPATH . "/" . $default_template;
						/**/
						return apply_filters ("ws_theme__page_template", $template, get_defined_vars ());
					}
				/*
				Function that retrieves all Single Templates.
				*/
				public static function get_single_templates ()
					{
						do_action ("ws_theme__before_get_single_templates", get_defined_vars ());
						/**/
						if (is_array ($themes = get_themes ()) && ($theme = get_current_theme ()) && is_array ($templates = $themes[$theme]["Template Files"]))
							{
								$base = array (trailingslashit (get_template_directory ()), trailingslashit (get_stylesheet_directory ()));
								/**/
								foreach ($templates as $template) /* Go through each template file. */
									/**/
									if (!preg_match ("/\//", ($basename = str_replace ($base, "", $template))))
										{
											unset ($name); /* Unset before each pass. */
											if (preg_match ("/Single Template:(.*)$/mi", file_get_contents ($template), $name))
												if ($name = trim (_cleanup_header_comment ($name[1])))
													$single_templates[$name] = $basename;
										}
							}
						/**/
						return apply_filters ("ws_theme__get_single_templates", (array)$single_templates, get_defined_vars ());
					}
			}
	}
?>