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
if (!class_exists ("c_ws_theme__meta_box_saves"))
	{
		class c_ws_theme__meta_box_saves
			{
				/*
				Save Post Template options.
				Attach to: add_action("save_post");
				*/
				public static function save_post_template_options ($post_id = FALSE)
					{
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_save_post_template_options", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if ($post_id && ($nonce = $_POST["ws_theme__wp_post_template_save"]) && wp_verify_nonce ($nonce, "ws-theme--wp-post-template-save") && current_user_can ("edit_post", $post_id))
							{
								$_p = ws_plugin__s2member_trim_deep (stripslashes_deep ($_POST)); /* Clean up all _POST vars; making a local working copy of the array. */
								/**/
								if ($_p["ws_theme__wp_post_template"]) /* Was this value posted? */
									update_post_meta ($post_id, "_wp_post_template", array ($_p["ws_theme__wp_post_template"]));
								/**/
								else /* Otherwise, we delete the meta key. */
									delete_post_meta ($post_id, "_wp_post_template");
								/**/
								do_action ("ws_theme__during_save_post_template_options", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__after_save_post_template_options", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
			}
	}
?>