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
Include all of the functions that came with this theme.
*/
if (is_dir ($ws_theme__temp_dir = TEMPLATEPATH . "/includes/functions"))
	foreach (scandir ($ws_theme__temp_dir) as $ws_theme__temp_s) /* Scan all files. */
		if (preg_match ("/\.php$/", $ws_theme__temp_s) && $ws_theme__temp_s !== "index.php")
			include_once $ws_theme__temp_dir . "/" . $ws_theme__temp_s;
/**/
unset ($ws_theme__temp_dir, $ws_theme__temp_s);
/*
Include all of the child-config functions that came with this theme.
*/
if (is_dir ($ws_theme__temp_dir = TEMPLATEPATH . "/includes/cc-incs/functions"))
	foreach (scandir ($ws_theme__temp_dir) as $ws_theme__temp_s) /* Scan all files. */
		if (preg_match ("/\.php$/", $ws_theme__temp_s) && $ws_theme__temp_s !== "index.php")
			include_once $ws_theme__temp_dir . "/" . $ws_theme__temp_s;
/**/
unset ($ws_theme__temp_dir, $ws_theme__temp_s);
?>