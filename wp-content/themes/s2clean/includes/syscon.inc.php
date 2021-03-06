<?php
/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
WARNING: This is a system configuration file, please DO NOT EDIT this file directly!
... Instead, use the theme options panel in WordPress® to override these settings.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
/*
Configure the variable name, and also the active color for this theme.
*/
$GLOBALS["WS_THEME__"]["c"]["var_name"] = strtolower (preg_replace ("/[^a-z_0-9]/i", "_", basename (TEMPLATEPATH)));
$GLOBALS["WS_THEME__"]["c"]["color"] = get_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_color", "default");
$GLOBALS["WS_THEME__"]["c"]["color"] = (defined ("CVAR") && CVAR && $_SERVER["HTTP_HOST"] === "dev.primothemes.com" && is_dir (TEMPLATEPATH . "/colors/" . CVAR)) ? CVAR : $GLOBALS["WS_THEME__"]["c"]["color"];
/*
Build a cross-protocol compatible Template URL. This will be needed in a few special circumstances.
*/
$GLOBALS["WS_THEME__"]["c"]["xpc_template_url"] = preg_replace ("/^http(s?)\:/i", "", get_bloginfo ("template_url"));
/*
External plugin dependencies. The name, function or (v_var,var_v) or (v_constant,constant_v), along with a location for each.
*/
$GLOBALS["WS_THEME__"]["c"]["external_plugin_dependencies"] = array (/*array ("name" => "Some Plugin", "function" => "some_plugin_function", "location" => "http://www.example.com/loc")*/);
/*
External widget dependencies. The name, function or (v_var,var_v) or (v_constant,constant_v), along with a location for each.
*/
$GLOBALS["WS_THEME__"]["c"]["external_widget_dependencies"] = array (/*array ("name" => "Some Widget", "function" => "some_widget_function", "location" => "http://www.example.com/loc")*/);
/*
Check if the theme has been configured *should be set after the first config via options panel*.
*/
$GLOBALS["WS_THEME__"]["c"]["configured"] = get_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_configured");
/*
Configure the file location of the logo source file for developers.
*/
$GLOBALS["WS_THEME__"]["c"]["logo_src"] = get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/body-header-logo.zip";
/*
Configure the right menu options panel for this software.
*/
$GLOBALS["WS_THEME__"]["c"]["menu_pages"] = array ("installation" => false, "tools" => true, "videos" => false, "support" => true, "donations" => false);
/*
Configure multibyte detection order when charset is unknown ( used by calls to `mb_convert_encoding()` ).
*/
$GLOBALS["WS_THEME__"]["c"]["mb_detection_order"] = "UTF-8, ISO-8859-1, WINDOWS-1252, ASCII, JIS, EUC-JP, SJIS";
/*
Get the current user information object.
*/
$GLOBALS["WS_THEME__"]["c"]["current_user"] = (is_user_logged_in ()) ? wp_get_current_user () : false;
/*
Configure the default Single Template ( single.php|fullsingle.x.php ).
*/
$GLOBALS["WS_THEME__"]["c"]["default_single_template"] = "single.php";
/*
Configure the default Page Template ( page.php|fullpage.x.php ).
*/
$GLOBALS["WS_THEME__"]["c"]["default_page_template"] = "page.php";
/*
Configure checksum time for the syscon.inc.php file.
*/
$GLOBALS["WS_THEME__"]["c"]["checksum"] = filemtime (__FILE__);
/*
Add support for Custom Menus, and Custom Backgrounds.
*/
add_theme_support("nav-menus") . register_nav_menu ("primary", "Primary Menu");
add_custom_background("c_ws_theme__custom_bgs::custom_background_css");
add_theme_support("automatic-feed-links");
/*
Configure the size of of the logo image being used in this theme.
If you change the dimensions here, also change them in the CSS file.
*/
$GLOBALS["WS_THEME__"]["c"]["logo_width"] = 400;
$GLOBALS["WS_THEME__"]["c"]["logo_height"] = 100;
$GLOBALS["WS_THEME__"]["c"]["logo_width_x_height"] = "400 x 100";
/*
Configure the size of thumbnail images that should be used in this theme.
If you change the dimensions here, also change them in the CSS file.
*/
$GLOBALS["WS_THEME__"]["c"]["thumb_width"] = 128;
$GLOBALS["WS_THEME__"]["c"]["thumb_height"] = 128;
$GLOBALS["WS_THEME__"]["c"]["thumb_width_x_height"] = "128 x 128";
add_theme_support("post-thumbnails") . set_post_thumbnail_size (128, 128, true);
/*
Configure maximum string lengths for titles.
*/
$GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index"] = false;
$GLOBALS["WS_THEME__"]["c"]["max_title_length"]["index_post"] = 25;
$GLOBALS["WS_THEME__"]["c"]["max_title_length"]["page"] = false;
$GLOBALS["WS_THEME__"]["c"]["max_title_length"]["fullpage"] = false;
$GLOBALS["WS_THEME__"]["c"]["max_title_length"]["single"] = false;
$GLOBALS["WS_THEME__"]["c"]["max_title_length"]["fullsingle"] = false;
/*
The index Category type ( single or parental ).
*/
$GLOBALS["WS_THEME__"]["c"]["index_category_type"] = "parental";
/*
Single Category max ( max Categories to display ).
- and also the separator to use.
*/
$GLOBALS["WS_THEME__"]["c"]["single_category_max"] = 2;
$GLOBALS["WS_THEME__"]["c"]["single_category_sep"] = ", ";
/*
Register the default Sidebars used by this theme.
*/
register_sidebar(array ("id" => "default-sidebar", "name" => "Default Sidebar", "description" => ""));
register_sidebar(array ("id" => "default-footbar", "name" => "Default Footbar", "description" => ""));
/*
Configure custom field instructions; these appear on Post/Page forms.
*/
$GLOBALS["WS_THEME__"]["c"]["custom_field_instructions"] = array/**/
(/**/
"h1_title" => array /* The key is the field name. */
( /* Configure this instruction through an array of information. */
"on_posts" => true, /* Show instruction on post editing panel? */
"on_pages" => true, /* Show instruction on page editing panel? */
"ins_label" => "h1_title ( optional custom title for H1 tag )",/**/
"instruction" => "If you don't want a custom title in the H1 tag, feel free to exclude this."),
/**/
"h1_desc" => array /* The key is the field name. */
( /* Configure this instruction through an array of information. */
"on_posts" => true, /* Show instruction on post editing panel? */
"on_pages" => true, /* Show instruction on page editing panel? */
"ins_label" => "h1_desc ( optional description under the H1 tag )",/**/
"instruction" => "If you don't want a custom description, feel free to exclude this."),
/**/
"thumbnail" => array /* The key is the field name. */
( /* Configure this instruction through an array of information. */
"on_posts" => true, /* Show instruction on post editing panel? */
"on_pages" => true, /* Show instruction on page editing panel? */
"ins_label" => "thumbnail ( optional thumbnail image )",/**/
"instruction" => "Upload a " . esc_html ($GLOBALS["WS_THEME__"]["c"]["thumb_width_x_height"]) . " image and paste in its full URL.<br />If you've already set a Featured Image Thumbnail for this Post/Page, feel free to exclude this."),
/**/
"contact_form_to" => array /* The key is the field name. */
( /* Configure this instruction through an array of information. */
"on_posts" => false, /* Show instruction on post editing panel? */
"on_pages" => true, /* Show instruction on page editing panel? */
"ins_label" => "contact_form_to ( email recipient, for contact form )",/**/
"instruction" => "This is only applicable on Pages using a Contact Form Template."));
/*
Include child configuration options.
*/
@include_once TEMPLATEPATH . "/includes/cc-incs/syscon.inc.php";
/*
Sync each option value with its color. This auto-corrects conflicts with color based locations.
*/
if (!function_exists ("ws_theme__sync_option_with_color"))
	{
		function ws_theme__sync_option_with_color ($value = FALSE)
			{
				return preg_replace ("/" . preg_quote ($GLOBALS["WS_THEME__"]["c"]["xpc_template_url"], "/") . "\/colors\/[a-z_0-9\-]+\//", $GLOBALS["WS_THEME__"]["c"]["xpc_template_url"] . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/", (string)$value);
			}
	}
/*
Configure & validate all of the theme options; and set their defaults.
*/
if (!function_exists ("ws_theme__configure_options_and_their_defaults"))
	{
		function ws_theme__configure_options_and_their_defaults ($options = FALSE)
			{
				$default_options = apply_filters ("ws_theme__default_options", array ( /* Give Filters a chance. */
				/**/
				"options_checksum" => "", /* Used internally to maintain the integrity of all options in the array. */
				/**/
				"options_version" => "1.0", /* Used internally to maintain integrity of all options in the array. */
				/**/
				"run_deactivation_routines" => "1", /* Should deactivation routines be processed? Always by default. */
				/**/
				"custom_css" => "", /* This holds a custom CSS file that can be created in the options panel for this theme. */
				"custom_js" => "", /* This holds a custom JavaScript file that can be created in the options panel also. */
				/**/
				"favicon_url" => "//" . $_SERVER["HTTP_HOST"] . "/favicon.ico", /* Defaults to root domain `/favicon.ico`. */
				/**/
				"logo_url" => $GLOBALS["WS_THEME__"]["c"]["xpc_template_url"] . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/body-header-logo.png",/**/
				/**/
				"disable_formatting" => "no", /* This can be one of three options: no|yes|raw. */
				/**/
				"nav_layout_model" => "custom", /* Default is a custom WordPress® menu. */
				"nav_cats_title" => "Our Blog", /* The default value is an Our Blog dummy li. */
				"nav_cats_position" => "before-pages", /* The default value is before-pages. */
				"nav_pages_title" => get_bloginfo ("name"), /* The default value is the site name. */
				"nav_pages_position" => "before-categories", /* Default value is before-categories. */
				/**/
				"page_nav_sort_column" => "menu_order", /* Default sorting is by menu order. */
				"page_nav_sort_order" => "asc", /* Just sort in ascending order by default. */
				"page_nav_exclusions" => array (), /* Page IDs that should be excluded from the nav. */
				"page_nav_inclusions" => array (), /* Page IDs that should be included in the nav. */
				/**/
				"cat_nav_sort_column" => "ID", /* Just sort on the category id by default. */
				"cat_nav_sort_order" => "asc", /* Just sort in ascending order by default. */
				"cat_nav_exclusions" => array (), /* Cat IDs that should be excluded from the nav. */
				"cat_nav_inclusions" => array (), /* Cat IDs that should be included in the nav. */
				/**/
				"home_h1_title" => get_bloginfo ("name"), /* Defaults to the site name. */
				"home_h1_desc" => get_bloginfo ("description"), /* Defaults to site desc. */
				"home_only_sticky" => "0", /* Set to 1 for sticky only on the index/home page. */
				"home_cat_exclusions" => array (), /* Cat IDs that should be excluded from the home page. */
				"home_cat_inclusions" => array (), /* Cat IDs that should be included in the home page. */
				/**/
				"excerpt_words" => "75", /* This defaults to 75 words. It can be increased or decreased. */
				"more_tag_label" => "[ continue reading ]", /* Can also be customized by doing this <!--more[label]-->. */
				"display_excerpts" => "always", /* This can be search|always|never. Default is always. */
				/**/
				"global_tracking_code" => "", /* This is displayed globally by the wp footer. */
				/**/
				"footbar_appendage_code" => "", /* This is displayed inside the footbar as an appendage. */
				/**/
				"lfbcomp_left_code" => '<a href="http://validator.w3.org/check/referer" rel="nofollow"><img src="' . esc_attr ($GLOBALS["WS_THEME__"]["c"]["xpc_template_url"] . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/xhtml-blue.png") . '" style="width:88px; height:31px; border:0px; vertical-align:middle;" alt="Valid XHTML 1.0 Transitional" title="Valid XHTML 1.0 Transitional" /></a> &nbsp; &nbsp; &copy; ' . esc_html (date ("Y")) . ' <a href="' . esc_attr (home_url ("/")) . '">' . esc_html (get_bloginfo ("name")) . '</a> — All Rights Reserved.',/**/
				"lfbcomp_right_code" => 'Designed by <a href="http://www.primothemes.com/" title="Premium WordPress® Themes"><img src="' . esc_attr ($GLOBALS["WS_THEME__"]["c"]["xpc_template_url"] . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-light.png") . '" style="width:132px; height:35px; border:0px; vertical-align:middle;" alt="Premium WordPress® Themes" title="Premium WordPress® Themes" /></a> &nbsp; &nbsp; Powered by <a href="http://wordpress.org/" rel="nofollow"><img src="' . esc_attr ($GLOBALS["WS_THEME__"]["c"]["xpc_template_url"] . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/wordpress-light.png") . '" style="width:97px; height:22px; border:0px; vertical-align:middle;" alt="WordPress®" title="WordPress®" /></a>'));
				/*
				Here they are merged. User options will overwrite some or all default values. 
				*/
				$GLOBALS["WS_THEME__"]["o"] = array_merge ($default_options, (($options !== false) ? (array)$options : (array)get_option ("ws_theme__" . $GLOBALS["WS_THEME__"]["c"]["var_name"] . "_options")));
				/*
				This builds an MD5 checksum for the full array of options. This also includes the config checksum, the current color configuration, and the current set of default options. 
				*/
				$checksum = md5 (($checksum_prefix = $GLOBALS["WS_THEME__"]["c"]["checksum"] . $GLOBALS["WS_THEME__"]["c"]["color"] . serialize ($default_options)) . serialize (array_merge ($GLOBALS["WS_THEME__"]["o"], array ("options_checksum" => 0))));
				/*
				Validate each option, possibly reverting back to the default value in some cases.
				*/
				if ($options !== false || ($GLOBALS["WS_THEME__"]["o"]["options_checksum"] !== $checksum && $GLOBALS["WS_THEME__"]["o"] !== $default_options))
					{
						foreach ($GLOBALS["WS_THEME__"]["o"] as $key => &$value)
							{
								if (!is_array ($value))
									$value = ws_theme__sync_option_with_color ($value);
								else /* A string, or an array of strings. */
									foreach ($value as &$v)
										$v = ws_theme__sync_option_with_color ($v);
								/**/
								if (!isset ($default_options[$key])) /* Disallow any unknown foreign keys. */
									unset($GLOBALS["WS_THEME__"]["o"][$key]); /* Remove from the array. */
								/**/
								else if ($key === "options_checksum" && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if ($key === "options_version" && (!is_string ($value) || !is_numeric ($value)))
									$value = $default_options[$key];
								/**/
								else if ($key === "run_deactivation_routines" && (!is_string ($value) || !is_numeric ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^custom_(css|js)$/", $key) && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^(favicon|logo)_url$/", $key) && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if ($key === "disable_formatting" && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^nav_(layout_model|cats_title|cats_position|pages_title|pages_position)$/", $key) && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^(page|cat)_nav_sort_(column|order)$/", $key) && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^(page|cat)_nav_(exclusions|inclusions)$/", $key) && (!is_array ($value) || empty ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^home_h1_(title|desc)$/", $key) && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if ($key === "home_only_sticky" && (!is_string ($value) || !is_numeric ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^home_cat_(exclusions|inclusions)$/", $key) && (!is_array ($value) || empty ($value)))
									$value = $default_options[$key];
								/**/
								else if ($key === "excerpt_words" && (!is_string ($value) || !is_numeric ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^(more_tag_label|display_excerpts)$/", $key) && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if ($key === "global_tracking_code" && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if ($key === "footbar_appendage_code" && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
								/**/
								else if (preg_match ("/^lfbcomp_(left|right)_code$/", $key) && (!is_string ($value) || !strlen ($value)))
									$value = $default_options[$key];
							}
						/**/
						$GLOBALS["WS_THEME__"]["o"] = apply_filters_ref_array ("ws_theme__options_before_checksum", array (&$GLOBALS["WS_THEME__"]["o"]));
						/**/
						$GLOBALS["WS_THEME__"]["o"]["options_checksum"] = md5 ($checksum_prefix . serialize (array_merge ($GLOBALS["WS_THEME__"]["o"], array ("options_checksum" => 0))));
					}
				/**/
				return apply_filters_ref_array ("ws_theme__options", array (&$GLOBALS["WS_THEME__"]["o"]));
			}
	}
?>