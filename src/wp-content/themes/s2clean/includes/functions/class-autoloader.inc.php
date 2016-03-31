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
The __autoload function for all theme classes.
This highly optimizes the theme. Giving it a much smaller footprint.
See: http://www.php.net/manual/en/function.spl-autoload-register.php
*/
if (!function_exists ("ws_theme__classes")) /* Already exists? */
	{
		function ws_theme__classes ($class = FALSE) /* Build dynamic __autoload function. */
			{
				static $c, $cc, $cs; /* Main class directory locations ( locations are optimized with static vars ). */
				static $c_class_dirs, $cc_class_dirs, $cs_class_dirs; /* All possible dir & sub-directory locations. */
				/**/
				if (strpos ($class, "c_ws_theme__") === 0) /* This also handles `c_ws_theme__c_` and `c_ws_theme__cs_` classes. */
					{
						if (strpos ($class, "c_ws_theme__cs_") === 0) /* Handles `c_ws_theme__cs_` classes. */
							{
								$cs = (!isset ($cs)) ? dirname (dirname (__FILE__)) . "/custom/includes/classes" : $cs; /* Custom. */
								$cs_class_dirs = (!isset ($cs_class_dirs)) ? array_merge (array ($cs), _ws_theme__classes_scan_dirs_r ($cs)) : $cs_class_dirs;
								/**/
								$class = str_replace ("_", "-", str_replace ("c_ws_theme__cs_", "", $class));
								/**/
								foreach ($cs_class_dirs as $class_dir) /* Start looking for the class. */
									if ($class_dir === $cs || strpos ($class, basename ($class_dir)) === 0)
										if (file_exists ($class_dir . "/" . $class . ".inc.php"))
											{
												include_once $class_dir . "/" . $class . ".inc.php";
												/**/
												break; /* Now stop looking. */
											}
							}
						else if (strpos ($class, "c_ws_theme__c_") === 0) /* Handles `c_ws_theme__c_` classes. */
							{
								$cc = (!isset ($cc)) ? dirname (dirname (__FILE__)) . "/cc-incs/classes" : $cc; /* Child classes dir. */
								$cc_class_dirs = (!isset ($cc_class_dirs)) ? array_merge (array ($cc), _ws_theme__classes_scan_dirs_r ($cc)) : $cc_class_dirs;
								/**/
								$class = str_replace ("_", "-", str_replace ("c_ws_theme__c_", "", $class));
								/**/
								foreach ($cc_class_dirs as $class_dir) /* Start looking for the class. */
									if ($class_dir === $cc || strpos ($class, basename ($class_dir)) === 0)
										if (file_exists ($class_dir . "/" . $class . ".inc.php"))
											{
												include_once $class_dir . "/" . $class . ".inc.php";
												/**/
												break; /* Now stop looking. */
											}
							}
						else if (strpos ($class, "c_ws_theme__") === 0) /* Handles all other `c_ws_theme__` classes. */
							{
								$c = (!isset ($c)) ? dirname (dirname (__FILE__)) . "/classes" : $c; /* Main/framework classes. */
								$c_class_dirs = (!isset ($c_class_dirs)) ? array_merge (array ($c), _ws_theme__classes_scan_dirs_r ($c)) : $c_class_dirs;
								/**/
								$class = str_replace ("_", "-", str_replace ("c_ws_theme__", "", $class));
								/**/
								foreach ($c_class_dirs as $class_dir) /* Start looking for the class. */
									if ($class_dir === $c || strpos ($class, basename ($class_dir)) === 0)
										if (file_exists ($class_dir . "/" . $class . ".inc.php"))
											{
												include_once $class_dir . "/" . $class . ".inc.php";
												/**/
												break; /* Now stop looking. */
											}
							}
					}
			}
		function _ws_theme__classes_scan_dirs_r ($starting_dir = FALSE)
			{
				$dirs = array (); /* Initialize dirs array. */
				/**/
				foreach (func_get_args () as $starting_dir)
					if (is_dir ($starting_dir)) /* Does this directory exist? */
						foreach (scandir ($starting_dir) as $dir) /* Scan this directory. */
							if ($dir !== "." && $dir !== ".." && is_dir ($dir = $starting_dir . "/" . $dir))
								$dirs = array_merge ($dirs, array ($dir), _ws_theme__classes_scan_dirs_r ($dir));
				/**/
				return $dirs; /* Return array of all directories. */
			}
		/**/
		spl_autoload_register ("ws_theme__classes"); /* Register __autoload. */
	}
?>