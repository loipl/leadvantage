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
The wp_head() function is called in head.x.php.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/*
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		include_once TEMPLATEPATH . "/head.x.php";
		/*
		Get the headbar.
		*/
		include_once TEMPLATEPATH . "/headbar.x.php";
		/*
		Hook before body wrapper.
		*/
		do_action ("ws_theme__before_body_wrapper");
		/*
		Open the body wrapper.
		*/
		echo '<div id="body-wrapper" class="body-wrapper">' . "\n";
		/*
		Open the body inner wrapper a.
		*/
		echo '<div id="body-inner-wrapper-a" class="body-inner-wrapper-a">' . "\n";
		/*
		Open the body inner wrapper b.
		*/
		echo '<div id="body-inner-wrapper-b" class="body-inner-wrapper-b">' . "\n";
		/*
		Open the body container.
		*/
		echo '<div id="body-container" class="body-container clearfix">' . "\n";
		/*
		Hook before header wrapper.
		*/
		do_action ("ws_theme__before_header"); /* Give hooks a chance. */
		/*
		Open the body header wrapper.
		*/
		echo '<div id="body-header-wrapper" class="body-header-wrapper">' . "\n";
		/*
		Open the body header inner wrapper a.
		*/
		echo '<div id="body-header-inner-wrapper-a" class="body-header-inner-wrapper-a">' . "\n";
		/*
		Open the body header inner wrapper b.
		*/
		echo '<div id="body-header-inner-wrapper-b" class="body-header-inner-wrapper-b">' . "\n";
		/*
		Open the body header container.
		*/
		echo '<div id="body-header-container" class="body-header-container clearfix">' . "\n";
		/*
		Hook before header markup.
		*/
		do_action ("ws_theme__during_header_before_markup");
		/*
		Build the body header logo.
		*/
		if (apply_filters ("ws_theme__during_header_during_markup_display_logo", true))
			{
				do_action ("ws_theme__during_header_during_markup_before_logo");
				/**/
				echo '<div id="body-header-logo" class="body-header-logo">' . "\n";
				echo '<a href="' . esc_attr (home_url ("/")) . '" rel="bookmark" title="' . esc_attr (get_bloginfo ("name")) . '"><img src="' . esc_attr ($GLOBALS["WS_THEME__"]["o"]["logo_url"]) . '" alt="' . esc_attr (get_bloginfo ("name")) . '" /></a>' . "\n";
				echo '</div>' . "\n";
				/**/
				do_action ("ws_theme__during_header_during_markup_after_logo");
			}
		/*
		Build the header navigation menu.
		*/
		if (apply_filters ("ws_theme__during_header_during_markup_display_nav", true))
			{
				do_action ("ws_theme__during_header_during_markup_before_nav");
				/**/
				echo '<div id="body-header-nav" class="body-header-nav clearfix">' . "\n";
				echo '<div id="body-header-nav-menu" class="body-header-nav-menu">' . "\n";
				echo '<ul class="primary-menu megafish">' . "\n";
				echo c_ws_theme__nav_menu::nav_menu_items ("primary") . "\n";
				echo '</ul>' . "\n";
				echo '</div>' . "\n";
				echo '</div>' . "\n";
				/**/
				echo '<script type="text/javascript">' . "\n";
				echo "jQuery('div#body-header-nav-menu > ul').megafish";
				echo "({downArrow: '', rightArrow: '', animShow: '', animHide: ''});" . "\n";
				echo '</script>' . "\n";
				/**/
				do_action ("ws_theme__during_header_during_markup_after_nav");
			}
		/*
		Hook after header markup.
		*/
		do_action ("ws_theme__during_header_after_markup");
		/*
		Close the body header container.
		*/
		echo '</div>' . "\n";
		/*
		Close the body header inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the body header inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the body header wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after header wrapper.
		*/
		do_action ("ws_theme__after_header");
		/*
		Hook before body content wrapper.
		*/
		do_action ("ws_theme__before_body_content_wrapper");
		/*
		Open the body content wrapper.
		*/
		echo '<div id="body-content-wrapper" class="body-content-wrapper">' . "\n";
		/*
		Open the body content inner wrapper a.
		*/
		echo '<div id="body-content-inner-wrapper-a" class="body-content-inner-wrapper-a">' . "\n";
		/*
		Open the body content inner wrapper b.
		*/
		echo '<div id="body-content-inner-wrapper-b" class="body-content-inner-wrapper-b">' . "\n";
		/*
		Open the body content container.
		*/
		echo '<div id="body-content-container" class="body-content-container clearfix">' . "\n";
		/*
		Hook during body content, before the actual content.
		*/
		do_action ("ws_theme__during_body_content_container_before_markup");
	}
?>