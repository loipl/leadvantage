/*
With this file, you can include your own custom JavaScript routines.

We encourage you to follow the instructions here instead of modifying the core framework:
	
	* Whenever possible, use a `custom/` file so that when updates or security patches
		are released for this software, you'll be able to upgrade to the latest version
		without losing any of the customizations that you've added in.

How to include custom JavaScript routines globally for all color variations:

	1. Rename this file to something that does NOT contain the word `sample` in it.
	2. Insert your own custom JavaScript functions, routines, or whatever you need to.
	3. Duplicate this process for as many custom files as you'd like to include.

		* Any file ending with `.js` in the `/includes/custom/` directory will be included with PHP using: `include_once`.
			- One exception: file names with the word `sample` in them are ignored.

		* The JS files in the `custom/` directory will be loaded last, after all other jQuery extensions and JavaScript files for your theme.

		* There are three special PHP variables available to you here.
			- Variable: $u = The full URL to your theme directory. Via: get_bloginfo("template_url").
			- Variable: $c = The name of the currently selected /colors/[sub-folder]. Ex: ( /colors/$c/ ).
			- Variable: $i = The full URL to the $u/colors/[sub-folder]/images directory. Ex: ( $u/colors/$c/images ).
				* The $i variable is really just a combined usage of $u and $c with the images/ directory tacked on.
				* An example of how you might use these variables is provided below.

Need to include custom JS files ONLY for a specific color variation?

	1. Open the specific /colors/sub-folder/ you want to address.
	2. Look for its `custom` folder and follow the sample files.
*/
var template_url = '<?php echo $u; ?>';
var template_url_to_color = '<?php echo $u; ?>/colors/<?php echo $c; ?>';
var bgPng = '<?php echo $i; ?>/bg.png'; /* << Using $i is easier. */