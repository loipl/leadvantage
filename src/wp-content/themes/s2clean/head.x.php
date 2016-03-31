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
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		include_once TEMPLATEPATH . "/includes/w3c-dtd.inc.php";
		/*
		Add the opening html tag.
		*/
		echo '<html xmlns="http://www.w3.org/1999/xhtml" ' . c_ws_theme__utilities::get ("language_attributes") . ' class="html' . (is_home () ? " is-home" : "") . (is_front_page () ? " is-front-page" : "") . '">' . "\n";
		/*
		Open the head tag.
		*/
		echo '<head profile="http://gmpg.org/xfn/11">' . "\n";
		/*
		The document title.
		*/
		echo '<title>' . c_ws_theme__utilities::get ("wp_title", "&laquo;", true, "right") . ' ' . esc_html (get_bloginfo ("name")) . '</title>' . "\n";
		/*
		The content types.
		*/
		echo '<meta http-equiv="Content-Style-Type" content="text/css" />' . "\n";
		echo '<meta http-equiv="Content-Script-Type" content="text/javascript" />' . "\n";
		echo '<meta http-equiv="Content-Type" content="' . esc_attr (get_bloginfo ("html_type")) . '; charset=' . esc_attr (get_bloginfo ("charset")) . '" />' . "\n";
		/*
		The css styles.
		*/
		echo '<link href="' . esc_attr (get_bloginfo ("template_url") . '/css.x.php?l=separates/font-faces.css&amp;c=' . urlencode ($GLOBALS["WS_THEME__"]["c"]["color"]) . '&amp;u=' . base64_encode (get_bloginfo ("template_url")) . '&amp;ver=' . urlencode (c_ws_theme__utilities::ver_checksum ())) . '" type="text/css" rel="stylesheet" media="all" />' . "\n";
		echo '<link href="' . esc_attr (get_bloginfo ("template_url") . '/css.x.php?c=' . urlencode ($GLOBALS["WS_THEME__"]["c"]["color"]) . '&amp;u=' . base64_encode (get_bloginfo ("template_url")) . '&amp;ver=' . urlencode (c_ws_theme__utilities::ver_checksum ())) . '" type="text/css" rel="stylesheet" media="all" />' . "\n";
		/*
		The favicon.
		*/
		echo '<link rel="shortcut icon" href="' . esc_attr ($GLOBALS["WS_THEME__"]["o"]["favicon_url"]) . '" type="image/x-icon" />' . "\n";
		/*
		The pingback url.
		*/
		echo '<link rel="pingback" href="' . esc_attr (get_bloginfo ("pingback_url")) . '" />' . "\n";
		/*
		Enqueue javascript libraries.
		*/
		wp_enqueue_script ("jquery"); /* Wordpress will already have jquery pre-installed; no need to link this up. */
		wp_enqueue_script ("ws-theme--js", get_bloginfo ("template_url") . "/js.x.php?c=" . urlencode ($GLOBALS["WS_THEME__"]["c"]["color"]) . "&amp;u=" . base64_encode (get_bloginfo ("template_url")), array ("jquery"), c_ws_theme__utilities::ver_checksum ());
		(is_singular ()) ? wp_enqueue_script ("comment-reply") : null; /* For the wordpress nested reply system. */
		/*
		Hooks for the head section.
		*/
		do_action ("ws_theme__head");
		wp_head (); /* For hooks. */
		/*
		Close the head tag.
		*/
		echo '</head>' . "\n";
		/*
		Open the body tag. Add WordPress® body classes.
		*/
		echo '<body class="' . esc_attr (implode (" ", (get_body_class ()))) . '">' . "\n";
		/*
		Hook after body open.
		*/
		do_action ("ws_theme__after_body_open");
	}
?>