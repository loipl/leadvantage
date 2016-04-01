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
if (!class_exists ("c_ws_theme__meta_box_options"))
	{
		class c_ws_theme__meta_box_options
			{
				/*
				Build Post Template options.
				*/
				public static function build_post_template_options ($post = FALSE)
					{
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_build_post_template_options", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if (is_object ($post) && ($post_id = $post->ID) && current_user_can ("edit_post", $post_id))
							{
								$_a = (array)get_post_meta ($post_id, "_wp_post_template", true);
								$_wp_post_template = array_shift ($_a); /* First element. */
								/**/
								echo '<input type="hidden" name="ws_theme__wp_post_template_save" id="ws-theme--wp-post-template-save" value="' . esc_attr (wp_create_nonce ("ws-theme--wp-post-template-save")) . '" />' . "\n";
								/**/
								echo '<select name="ws_theme__wp_post_template" id="ws-theme--wp-post-template" style="width:99%;">' . "\n";
								/**/
								echo '<option value="default">Default Template</option>' . "\n"; /* Always build default option. */
								/**/
								foreach (c_ws_theme__templates::get_single_templates () as $name => $value)
									echo '<option value="' . esc_attr ($value) . '"' . ( ($value === $_wp_post_template) ? ' selected="selected"' : '') . '>' . esc_html ($name) . '</option>' . "\n";
								/**/
								do_action ("ws_theme__during_build_post_template_options", get_defined_vars ());
								/**/
								echo '</select>' . "\n";
							}
						/**/
						do_action ("ws_theme__after_build_post_template_options", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Build custom field instructions.
				*/
				public static function build_custom_field_instructions ($post = FALSE)
					{
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_build_custom_field_instructions", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$counter = 0; /* Keep a running counter on the number of instructions. */
						/**/
						foreach ($GLOBALS["WS_THEME__"]["c"]["custom_field_instructions"] as $key => $array)
							{
								if (is_object ($post) && ($array["on_" . $post->post_type . "s"] || ($array["on_posts"] && $post->post_type !== "page")))
									{
										echo ($counter) ? '<div style="margin:10px 0 10px 0; line-height:1px; height:1px; background:#EEEEEE;"></div>' . "\n" : '';
										echo '<p><label style="font-weight:bold;">' . $array["ins_label"] . ':</label><br />' . "\n";
										echo $array["instruction"] . '<br />' . "\n"; /* Add the detailed instructions. */
										echo '<em>When used, this value should exist as the Custom Field ( ' . $key . ' ).</em></p>' . "\n";
										$counter++; /* Increment the counter. */
									}
								/**/
								do_action ("ws_theme__during_build_custom_field_instructions", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__after_build_custom_field_instructions", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>