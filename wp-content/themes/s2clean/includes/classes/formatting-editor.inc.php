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
if (!class_exists ("c_ws_theme__formatting_editor"))
	{
		class c_ws_theme__formatting_editor
			{
				/*
				Configures the Visual Editor.
				
				Attach to: add_action("load-page.php") . add_action("load-post.php");
				Attach to: add_action("load-post-new.php") . add_action("load-post-new.php");
				Attach to: add_action("admin_head-profile.php") . add_action("admin_head-user-edit.php") . add_action("load-options-writing.php");
				*/
				public static function configure_editor ()
					{
						global $pagenow; /* This holds the current admin page file name. */
						/**/
						do_action ("ws_theme__before_configure_editor", get_defined_vars ());
						/**/
						if (is_blog_admin () && in_array ($pagenow, array ("page.php", "post.php", "page-new.php", "post-new.php", "profile.php", "user-edit.php", "options-writing.php")))
							{
								if ($GLOBALS["WS_THEME__"]["o"]["disable_formatting"] === "yes")
									{
										add_filter ("user_can_richedit", "c_ws_theme__formatting_filters::disable_editor_ops"); /* Turn off rich editing. */
										add_filter ("option_use_smilies", "c_ws_theme__formatting_filters::disable_editor_ops") . add_filter ("pre_option_use_smilies", "c_ws_theme__formatting_filters::disable_editor_ops");
										add_filter ("option_use_balanceTags", "c_ws_theme__formatting_filters::disable_editor_ops") . add_filter ("pre_option_use_balanceTags", "c_ws_theme__formatting_filters::disable_editor_ops");
										/**/
										global $profileuser; /* Used in: user-edit.php / profile.php. */
										/**/
										if (is_object ($profileuser) && in_array ($pagenow, array ("profile.php", "user-edit.php")))
											{
												$profileuser->rich_editing = "false"; /* Disable rich editing on profile page. */
												/**/
												$notice = '<strong>NOTE:</strong> The Visual Editor has been disabled globally - for all Users.';
												c_ws_theme__admin_notices::enqueue_admin_notice ($notice, "blog:" . $pagenow); /* Send notification to User. */
											}
										else if (in_array ($pagenow, array ("options-writing.php"))) /* Else if we are in the writing options. */
											{
												$notice = '<strong>NOTE:</strong> Emoticons and XHTML nesting have been disabled globally - for all Users.';
												c_ws_theme__admin_notices::enqueue_admin_notice ($notice, "blog:" . $pagenow); /* Send notification to User. */
											}
									}
								else if ($GLOBALS["WS_THEME__"]["o"]["disable_formatting"] === "raw")
									{
										add_filter ("user_can_richedit", "c_ws_theme__formatting_editor::conditional_editor_ops"); /* Conditional rich editing. */
										add_filter ("option_use_smilies", "c_ws_theme__formatting_editor::conditional_editor_ops") . add_filter ("pre_option_use_smilies", "c_ws_theme__formatting_editor::conditional_editor_ops");
										add_filter ("option_use_balanceTags", "c_ws_theme__formatting_editor::conditional_editor_ops") . add_filter ("pre_option_use_balanceTags", "c_ws_theme__formatting_editor::conditional_editor_ops");
										/**/
										global $profileuser; /* Used in: user-edit.php / profile.php. */
										/**/
										if (is_object ($profileuser) && in_array ($pagenow, array ("profile.php")))
											{
												if (isset ($GLOBALS['g_execphp_manager']) && current_user_can ("install_themes"))
													$notice = '<strong>TIP:</strong> If you create a Page or Post that starts with <code>&lt;!--RAW--&gt;</code>, the Visual Editor will be disabled automatically, for that particular entry; no matter what your default Profile option has been set to here. Also, it appears that the Exec-PHP plugin has been installed. If you start your content with <code>&lt;!--RAW--&gt;</code>, using the Code Editor ( aka: the HTML Tab ), you can safely ignore WYSIWYG warnings issued by Exec-PHP.';
												else /* Different notices here, depending on whether they have the Exec-PHP plugin installed. */
													$notice = '<strong>TIP:</strong> If you create a Page or Post that starts with <code>&lt;!--RAW--&gt;</code>, the Visual Editor will be disabled automatically, for that particular entry; no matter what your default Profile option has been set to here.';
												c_ws_theme__admin_notices::enqueue_admin_notice ($notice, "blog:" . $pagenow); /* Send notification to User. */
											}
										else if (in_array ($pagenow, array ("options-writing.php"))) /* Else if we are in the writing options. */
											{
												$notice = '<strong>TIP:</strong> If you create a Page or Post that starts with <code>&lt;!--RAW--&gt;</code>, Emoticons and XHTML nesting will be disabled automatically, for that particular entry; no matter what your default options have been set to here.';
												c_ws_theme__admin_notices::enqueue_admin_notice ($notice, "blog:" . $pagenow); /* Send notification to User. */
											}
									}
								/**/
								do_action ("ws_theme__during_configure_editor", get_defined_vars ());
							}
						/**/
						do_action ("ws_theme__after_configure_editor", get_defined_vars ());
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Disables the Visual Editor if content starts with <!--RAW-->, and also disables related options dynamically at runtime.
				
				Attach to: add_filter("user_can_richedit");
				Attach to: add_filter("option_use_smilies") . add_filter("pre_option_use_smilies");
				Attach to: add_filter("option_use_balanceTags") . add_filter("pre_option_use_balanceTags");
				*/
				public static function conditional_editor_ops ($value = FALSE)
					{
						global $pagenow, $wp_rich_edit; /* Must reset this global as well. */
						static $post, $notified = false; /* Only send Post/Page editor notification once. */
						/**/
						if (is_blog_admin () && in_array ($pagenow, array ("page.php", "post.php", "page-new.php", "post-new.php")))
							{
								$post = (!isset ($post) && ($post_ID = (int)$_GET["post"])) ? get_post ($post_ID) : $post;
								/**/
								if (is_object ($post) && preg_match ("/^(\<|&lt;)\!--RAW--(&gt;|\>)/i", trim ($post->post_content)))
									{
										if (!$notified && $wp_rich_edit && in_array ($pagenow, array ("page.php", "post.php")) && ($notified = true))
											{
												$notice = '<strong>RAW:</strong> The Visual Editor has been automatically disabled here, because your content starts with <code>&lt;!--RAW--&gt;</code>.';
												/**/
												c_ws_theme__admin_notices::enqueue_admin_notice ($notice, "blog:" . $pagenow); /* Send this important notification to the User. */
											}
										/**/
										$wp_rich_edit = false; /* Flag this false now. */
										/**/
										if (is_string ($value) && !is_numeric ($value))
											{
												return "false";
											}
										else if (is_string ($value) && is_numeric ($value))
											{
												return "0";
											}
										else if (is_bool ($value))
											{
												return false;
											}
										else if (is_integer ($value))
											{
												return 0;
											}
										else /* Default. */
											{
												return null;
											}
									}
								else if (is_object ($post) && !$notified && $wp_rich_edit && in_array ($pagenow, array ("page.php", "post.php")) && ($notified = true))
									{
										if (isset ($GLOBALS["g_execphp_manager"]) && current_user_can ("install_themes"))
											$notice = '<strong>TIP:</strong> If you start your content with <code>&lt;!--RAW--&gt;</code>, the Visual Editor will be disabled automatically, for this particular entry. This safeguards your RAW content from being mangled by the Visual Editor. Also, it appears that the Exec-PHP plugin has been installed. If you start your content with <code>&lt;!--RAW--&gt;</code>, using the Code Editor ( aka: the HTML Tab ), you can safely ignore WYSIWYG warnings issued by Exec-PHP.';
										else /* Different notices here, depending on whether they have the Exec-PHP plugin installed. */
											$notice = '<strong>TIP:</strong> If you start your content with <code>&lt;!--RAW--&gt;</code>, using the Code Editor ( aka: the HTML Tab ), the Visual Editor will be disabled automatically, for this particular entry. This safeguards your RAW content from being mangled by the Visual Editor.';
										/**/
										c_ws_theme__admin_notices::enqueue_admin_notice ($notice, "blog:" . $pagenow); /* Send notification to User. */
										/**/
										return $value; /* No change. */
									}
								else if (!is_object ($post) && !$notified && $wp_rich_edit && in_array ($pagenow, array ("page-new.php", "post-new.php")) && ($notified = true))
									{
										if (isset ($GLOBALS["g_execphp_manager"]) && current_user_can ("install_themes"))
											$notice = '<strong>TIP:</strong> If you start your content with <code>&lt;!--RAW--&gt;</code>, the Visual Editor will be disabled automatically, for this particular entry. This safeguards your RAW content from being mangled by the Visual Editor. Also, it appears that the Exec-PHP plugin has been installed. If you start your content with <code>&lt;!--RAW--&gt;</code>, using the Code Editor ( aka: the HTML Tab ), you can safely ignore WYSIWYG warnings issued by Exec-PHP.';
										else /* Different notices here, depending on whether they have the Exec-PHP plugin installed. */
											$notice = '<strong>TIP:</strong> If you start your content with <code>&lt;!--RAW--&gt;</code>, using the Code Editor ( aka: the HTML Tab ), the Visual Editor will be disabled automatically, for this particular entry. This safeguards your RAW content from being mangled by the Visual Editor.';
										/**/
										c_ws_theme__admin_notices::enqueue_admin_notice ($notice, "blog:" . $pagenow); /* Send notification to User. */
										/**/
										return $value; /* No change. */
									}
								else /* No change to the current value. */
									{
										return $value; /* No change. */
									}
							}
						else /* No change to the current value. */
							{
								return $value; /* No change. */
							}
					}
			}
	}
?>