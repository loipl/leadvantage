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
	exit ("Do not access this file directly.");
/*
External plugin dependencies.
*/
$GLOBALS["WS_THEME__"]["c"]["external_plugin_dependencies"] = array (array ("name" => "s2Member", "v_constant" => "WS_PLUGIN__S2MEMBER_VERSION", "constant_v" => "3.5.2", "location" => "http://www.s2member.com/"));
/*
Configure checksum time for both of the syscon.inc.php files.
*/
$GLOBALS["WS_THEME__"]["c"]["checksum"] = $GLOBALS["WS_THEME__"]["c"]["checksum"] + filemtime (__FILE__);
/*
Register an additional menu used by this theme.
*/
register_nav_menu ("logged-in-primary", "Logged-In Primary Menu");
/*
Register additional sidebars used by this theme.
*/
register_sidebar (array ("id" => "logged-in-sidebar", "name" => "Logged-In Sidebar", "description" => "This special Sidebar is displayed only when a User/Member is logged in."));
register_sidebar (array ("id" => "logged-in-footbar", "name" => "Logged-In Footbar", "description" => "This special Footbar is displayed only when a User/Member is logged in."));
?>