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
/*
Add Actions/Filters for various methods.
*/
add_action ("init", "c_ws_theme__custom_css_js::custom_css", 1);
add_action ("init", "c_ws_theme__custom_css_js::custom_js", 1);
/**/
add_action ("init", "c_ws_theme__admin_css_js::menu_pages_css", 1);
add_action ("init", "c_ws_theme__admin_css_js::menu_pages_js", 1);
/**/
add_action ("init", "c_ws_theme__formatting::configure_formatting");
/**/
add_action ("pre_get_posts", "c_ws_theme__home_page::home_page_filter");
/**/
add_filter ("page_template", "c_ws_theme__templates::page_template");
add_filter ("single_template", "c_ws_theme__templates::single_template");
/**/
add_action ("wp_print_scripts", "c_ws_theme__custom_files::add_custom_scripts");
add_action ("wp_print_styles", "c_ws_theme__custom_files::add_custom_styles");
/**/
add_action ("admin_menu", "c_ws_theme__menu_pages::add_admin_options");
/**/
add_action ("admin_print_scripts", "c_ws_theme__menu_pages::add_admin_scripts");
add_action ("admin_print_styles", "c_ws_theme__menu_pages::add_admin_styles");
/**/
add_action ("add_meta_boxes", "c_ws_theme__meta_boxes::add_admin_meta_boxes");
add_action ("save_post", "c_ws_theme__meta_box_saves::save_post_template_options");
/**/
add_action ("admin_notices", "c_ws_theme__admin_notices::admin_notices");
add_action ("user_admin_notices", "c_ws_theme__admin_notices::admin_notices");
add_action ("network_admin_notices", "c_ws_theme__admin_notices::admin_notices");
/**/
add_action ("admin_init", "c_ws_theme__installation::activate");
add_action ("switch_theme", "c_ws_theme__installation::deactivate");
/*
Include child-config hooks.
*/
@include_once TEMPLATEPATH . "/includes/cc-incs/hooks.inc.php";
?>