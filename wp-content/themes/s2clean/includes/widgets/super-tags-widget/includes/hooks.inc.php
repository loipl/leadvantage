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
Add the plugin Actions/Filters here.
*/
add_action ("init", "c_ws_widget__super_tags_admin_css_js::menu_pages_css", 1);
add_action ("init", "c_ws_widget__super_tags_admin_css_js::menu_pages_js", 1);
/**/
add_action ("widgets_init", "c_ws_widget__super_tags_widget::register");
/**/
add_action ("admin_menu", "c_ws_widget__super_tags_menu_pages::add_admin_options");
add_action ("admin_print_scripts", "c_ws_widget__super_tags_menu_pages::add_admin_scripts");
add_action ("admin_print_styles", "c_ws_widget__super_tags_menu_pages::add_admin_styles");
/**/
add_action ("admin_notices", "c_ws_widget__super_tags_admin_notices::admin_notices");
add_action ("user_admin_notices", "c_ws_widget__super_tags_admin_notices::admin_notices");
add_action ("network_admin_notices", "c_ws_widget__super_tags_admin_notices::admin_notices");
/*
Register the activation | de-activation routines.
*/
register_activation_hook ($GLOBALS["WS_WIDGET__"]["super_tags"]["l"], "c_ws_widget__super_tags_installation::activate");
register_deactivation_hook ($GLOBALS["WS_WIDGET__"]["super_tags"]["l"], "c_ws_widget__super_tags_installation::deactivate");
?>