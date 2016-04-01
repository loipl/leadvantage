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
	exit("Do not access this file directly.");
/*
Include all of the plugins that are included with this theme.
*/
if (is_dir ($ws_theme__temp_dir = TEMPLATEPATH . "/includes/plugins"))
	foreach (scandir ($ws_theme__temp_dir) as $ws_theme__temp_s) /* Scan all files. */
		if (preg_match ("/^([a-z_0-9\-]+)$/", $ws_theme__temp_s) && file_exists ($ws_theme__temp_dir . "/" . $ws_theme__temp_s . "/" . $ws_theme__temp_s . ".php"))
			include_once $ws_theme__temp_dir . "/" . $ws_theme__temp_s . "/" . $ws_theme__temp_s . ".php";
/**/
unset ($ws_theme__temp_dir, $ws_theme__temp_s);
/*
Include all of the child-config plugins that are included with this theme.
*/
if (is_dir ($ws_theme__temp_dir = TEMPLATEPATH . "/includes/cc-incs/plugins"))
	foreach (scandir ($ws_theme__temp_dir) as $ws_theme__temp_s) /* Scan all files. */
		if (preg_match ("/^([a-z_0-9\-]+)$/", $ws_theme__temp_s) && file_exists ($ws_theme__temp_dir . "/" . $ws_theme__temp_s . "/" . $ws_theme__temp_s . ".php"))
			include_once $ws_theme__temp_dir . "/" . $ws_theme__temp_s . "/" . $ws_theme__temp_s . ".php";
/**/
unset ($ws_theme__temp_dir, $ws_theme__temp_s);
/*
Check for the existence of external plugin dependencies and alert the site owner if needed.
*/
foreach ($GLOBALS["WS_THEME__"]["c"]["external_plugin_dependencies"] as $ws_theme__temp_a)
	/**/
	if (($ws_theme__temp_a["function"] && !is_callable ($ws_theme__temp_a["function"]) && !class_exists ($ws_theme__temp_a["function"])) || ($ws_theme__temp_a["v_var"] && $ws_theme__temp_a["var_v"] && (!$$ws_theme__temp_a["v_var"] || !version_compare ($$ws_theme__temp_a["v_var"], $ws_theme__temp_a["var_v"], ">="))) || ($ws_theme__temp_a["v_constant"] && $ws_theme__temp_a["constant_v"] && (!defined ($ws_theme__temp_a["v_constant"]) || !version_compare (constant ($ws_theme__temp_a["v_constant"]), $ws_theme__temp_a["constant_v"], ">="))))
		{
			$GLOBALS["WS_THEME__"]["dependency-error"] = 'You need [ <a onclick="window.open (\'' . c_ws_theme__utils_strings::esc_js_sq (esc_attr ($ws_theme__temp_a["location"])) . '\', \'_window\');" style="cursor:pointer;">' . esc_html ($ws_theme__temp_a["name"]) . '' . (($ws_theme__temp_a["v_var"] && $ws_theme__temp_a["var_v"]) ? ' v' . esc_html ($ws_theme__temp_a["var_v"]) . '+' : (($ws_theme__temp_a["v_constant"] && $ws_theme__temp_a["constant_v"]) ? ' v' . esc_html ($ws_theme__temp_a["constant_v"]) . '+' : '')) . '</a> ] to use this theme. Please <a onclick="window.open (\'' . c_ws_theme__utils_strings::esc_js_sq (esc_attr ($ws_theme__temp_a["location"])) . '\', \'_window\');" style="cursor:pointer;">install the plugin</a> to satisfy system requirements.';
			/**/
			add_action ("all_admin_notices", create_function ('', 'echo \'<div class="error fade"><p>\' . $GLOBALS["WS_THEME__"]["dependency-error"] . \'</p></div>\';'));
			(!is_admin () && !preg_match ("/\/wp-login\.php/", $_SERVER["REQUEST_URI"]) && !defined ("WP_INSTALLING")) ? add_action ("init", create_function ('', 'wp_die ($GLOBALS["WS_THEME__"]["dependency-error"]);')) : null;
			/**/
			$GLOBALS["WS_THEME__"]["compatible"] = false;
			/**/
			break; /* Break on dependency-error. */
		}
?>