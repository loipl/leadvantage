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
Include any custom functions that have been added to this theme.
*/
if (is_dir ($ws_theme__temp_dir = TEMPLATEPATH . "/includes/custom"))
	foreach (scandir ($ws_theme__temp_dir) as $ws_theme__temp_s) /* Scan all files. */
		if (preg_match ("/\.php$/", $ws_theme__temp_s) && stripos ($ws_theme__temp_s, "sample") === false && $ws_theme__temp_s !== "index.php")
			include_once $ws_theme__temp_dir . "/" . $ws_theme__temp_s;
/**/
unset ($ws_theme__temp_dir, $ws_theme__temp_s);
/*
Include any custom functions that have been added for this specific color variation.
*/
if (is_dir ($ws_theme__temp_dir = TEMPLATEPATH . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/custom"))
	foreach (scandir ($ws_theme__temp_dir) as $ws_theme__temp_s) /* Scan all files. */
		if (preg_match ("/\.php$/", $ws_theme__temp_s) && stripos ($ws_theme__temp_s, "sample") === false && $ws_theme__temp_s !== "index.php")
			include_once $ws_theme__temp_dir . "/" . $ws_theme__temp_s;
/**/
unset ($ws_theme__temp_dir, $ws_theme__temp_s);
?>