<?php
/*
With this file, you can include your own custom PHP functions, Hooks or Filters.

We encourage you to follow the instructions here instead of modifying the core framework:

	* Whenever possible, use a `custom/` file so that when updates or security patches
		are released for this software, you'll be able to upgrade to the latest version
		without losing any of the customizations that you've added in.

	* There are MANY WordPress® Hooks/Filters spread throughout our framework,
		so this should be easy for an experienced WordPress® developer.

How to include custom PHP functions for a specific color variation:

	1. Rename this file to something that does NOT contain the word `sample` in it.
	2. Insert your own custom PHP functions, Hooks, Filters, or whatever you need to.
	3. Duplicate this process for as many custom files as you'd like to include.

		* Any file ending with `.php` in the `custom/` directory will be included using: `include_once`.
			- Two exceptions: `index.php` and file names with the word `sample` in them are ignored.

		* The PHP files in the `custom/` directory will be loaded immediately after the system
			configuration file & Hooks are loaded for this theme, but before any functions/classes are defined.
			Since all theme functions/classes are preceded by `function_exists/class_exists`, you can override them.

Need to include custom PHP files globally, for all color variations?

	1. Open /includes/custom/ from your theme directory.
	2. Follow the sample files in that directory.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
?>