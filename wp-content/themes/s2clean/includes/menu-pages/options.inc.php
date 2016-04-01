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
Options page.
*/
echo '<div class="wrap ws-menu-page">' . "\n";
/**/
echo '<div id="icon-themes" class="icon32"><br /></div>' . "\n";
echo '<h2>Theme Specific Options</h2>' . "\n";
/**/
echo '<table class="ws-menu-page-table">' . "\n";
echo '<tbody class="ws-menu-page-table-tbody">' . "\n";
echo '<tr class="ws-menu-page-table-tr">' . "\n";
echo '<td class="ws-menu-page-table-l">' . "\n";
/**/
echo '<form method="post" name="ws_theme__options_form" id="ws-theme--options-form">' . "\n";
echo '<input type="hidden" name="ws_theme__options_save" id="ws-theme--options-save" value="' . esc_attr (wp_create_nonce ("ws-theme--options-save")) . '" />' . "\n";
echo '<input type="hidden" name="ws_theme__configured" id="ws-theme--configured" value="1" />' . "\n";
/**/
do_action ("ws_theme__during_options_page_before_left_sections", get_defined_vars ());
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_color", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_color", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Main Color Selection">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--color-section">' . "\n";
		echo '<h3>Theme Color ( required, please customize this )</h3>' . "\n";
		echo '<p>This theme came with <strong>multiple color variatons</strong>. Each color variation has its own <code>/colors/sub-folder</code> on the server. The sub-folder for each color contains a complete set of style sheets and images that are specific to that color. Here you get to choose which color variation ( e.g. style sheets and images ) that you would like to use.</p>' . "\n";
		echo '<p><em>* Whenever you change the color option here, using the drop-down menu below, that change is automatically propagated down through the remaining option fields. In other words, all of your option values are scanned for URLs that point to images inside a particular color/ directory, and those URLs are updated, as they should be, based on the color you choose.</em></p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_color", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--color">' . "\n";
		echo 'Theme Color ( Please Choose One ):' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__color" id="ws-theme--color">' . "\n";
		if (is_dir ($ws_theme__temp_dir = TEMPLATEPATH . "/colors")) /* Have colors? */
			foreach (scandir ($ws_theme__temp_dir) as $ws_theme__temp_s) /* Scan all colors. */
				if (preg_match ("/^([a-z_0-9\-]+)$/i", $ws_theme__temp_s)) /* Is this a valid color? */
					echo '<option value="' . esc_attr ($ws_theme__temp_s) . '"' . (($GLOBALS["WS_THEME__"]["c"]["color"] === $ws_theme__temp_s) ? ' selected="selected"' : '') . '>' . ucwords (preg_replace ("/[^a-z]/i", " ", $ws_theme__temp_s)) . '</option>' . "\n";
		echo '</select>' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_color", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_custom_css", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_custom_css", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Custom CSS / Style Sheet">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--custom-css-section">' . "\n";
		echo '<h3>Custom CSS / Style Sheet ( optional, for further customization )</h3>' . "\n";
		echo '<p>If you\'d like to include a few custom styles for this theme, please feel free to write your own custom CSS into the box below.</p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_custom_css", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--custom-css">' . "\n";
		echo 'Custom CSS ( do NOT include <code>&lt;style&gt;&lt;/style&gt;</code> tags ):' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<textarea name="ws_theme__custom_css" id="ws-theme--custom-css" rows="8" spellcheck="false">' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["custom_css"]) . '</textarea><br />' . "\n";
		echo 'If you don\'t know what Style Sheets are, just leave this empty. If you\'d like to learn more, there is a <a href="http://www.w3schools.com/css/" target="_blank" rel="external">tutorial here</a>.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_custom_css", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_custom_js", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_custom_js", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Custom JavaScript Routines">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--custom-js-section">' . "\n";
		echo '<h3>Custom JavaScript ( optional, for further customization )</h3>' . "\n";
		echo '<p>If you\'d like to, you can write your own custom JavaScript routines into the box below. They will be loaded into the <code>&lt;HEAD&gt;</code> of your site. <a href="http://jquery.com/" target="_blank" rel="external">jQuery</a> will already be loaded and readily available; so if you know how to use jQuery, feel free to take advantage of it. Otherwise, don\'t worry, none of this is required. * <em>If you need to include external JavaScript libraries, you can do that using <code>document.write</code>. Example: <code>document.write(\'&lt;script type="text/javascript" src="/path/to/external/script.js"&gt;&lt;/script&gt;\');</code></em></p>' . "\n";
		echo '<p><em>* Do NOT paste Google® Analytics code here. There is a separate section down below for Tracking/Analytics.</em></p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_custom_js", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--custom-js">' . "\n";
		echo 'Custom JavaScript ( do NOT include <code>&lt;script&gt;&lt;/script&gt;</code> tags ):' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<textarea name="ws_theme__custom_js" id="ws-theme--custom-js" rows="8" spellcheck="false">' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["custom_js"]) . '</textarea><br />' . "\n";
		echo 'If you don\'t know what JavaScript is, just leave this empty. If you\'d like to learn more, there is a <a href="http://www.w3schools.com/js/" target="_blank" rel="external">tutorial here</a>.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_custom_js", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_formatting", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_formatting", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Content Formatting">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--formatting-section">' . "\n";
		echo '<h3>Formatting Configuration ( optional, for content management )</h3>' . "\n";
		echo '<p>By default, WordPress® comes with several Formatting Filters built into its publishing system. Advanced users may find them annoying, particularly if you already know XHTML and you\'re not using the Visual Editor for WordPress®. These Formatting Filters include: <code>wpautop, balanceTags, wptexturize, convert_chars &amp; convert_smilies</code>. The most important Filter is <code>wpautop</code>. It converts double line-breaks in your content into paragraphs (<code>&lt;p&gt;...&lt;/p&gt;</code>). The other Filters, well, they do a few different things, but for the most part, they\'re responsible for keeping your code clean, handling XHTML entity conversions for special characters like ampersands, and balancing the overall structure of your code. All of that being said, if you plan to write your own XHTML, without the assistance of the Visual Editor, you can safely disable these Filters to prevent your raw code from being modified inadvertently.</p>' . "\n";
		echo '<p><em>* If you\'ve already created Posts/Pages using the Visual Editor for WordPress®, disabling these Filters will cause your existing content to appear broken. For example, the <code>wpautop</code> filter converts double line-breaks in your content into paragraphs (<code>&lt;p&gt;...&lt;/p&gt;</code>). If you\'ve already published a lot of content that depends on automatic paragraphs, and then you disable <code>wpautop</code>, your content will become jumbled. Just keep this in mind if you disable these Filters, and then find your content in a mess. It\'s easy to think your theme is to blame, when actually it\'s just these Filters at work, or not at work. In either case, the problem will be temporary, not permanent. To correct the issue, adjust your configuration.</em></p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_formatting", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--disable-formatting">' . "\n";
		echo 'Disable Formatting Filters &amp; The Visual Editor?' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__disable_formatting" id="ws-theme--disable-formatting">' . "\n";
		echo '<option value="no"' . (($GLOBALS["WS_THEME__"]["o"]["disable_formatting"] === "no") ? ' selected="selected"' : '') . '>No ( do NOT remove any Formatting Filters or disable the Visual Editor )</option>' . "\n";
		echo '<option value="yes"' . (($GLOBALS["WS_THEME__"]["o"]["disable_formatting"] === "yes") ? ' selected="selected"' : '') . '>Yes ( disable these Filters &amp; prevent the Visual Editor from ever being used )</option>' . "\n";
		echo '<option value="raw"' . (($GLOBALS["WS_THEME__"]["o"]["disable_formatting"] === "raw") ? ' selected="selected"' : '') . '>Yes ( disable these Filters, but only when a Post/Page starts with: &lt;!--RAW--&gt; )</option>' . "\n";
		echo '</select><br />' . "\n";
		echo 'If you choose the &lt;!--RAW--&gt; option, the Formatting Filters &amp; Visual Editor will only be disabled when a Post/Page starts with &lt;!--RAW--&gt;. This allows you to safeguard specific content from the Visual Editor.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_formatting", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_logo_favicon", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_logo_favicon", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Logo &amp; Favicon Images">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--logo-section">' . "\n";
		echo '<h3>Logo Image Settings ( required, please customize this )</h3>' . "\n";
		echo '<p>This theme uses a 24-bit PNG logo image with transparency. It should be ' . esc_html ($GLOBALS["WS_THEME__"]["c"]["logo_width_x_height"]) . ' px. If you need to, you can download the original logo source file <a href="' . esc_attr ($GLOBALS["WS_THEME__"]["c"]["logo_src"]) . '" rel="external">here</a>.</p>' . "\n";
		echo '<p><em>* If you cannot create a 24-bit PNG with transparency, just use a GIF or JPG image instead :-)</em></p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_logo", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--logo-url">' . "\n";
		echo 'Logo Image ( Full URL Location ):' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="text" name="ws_theme__logo_url" id="ws-theme--logo-url" value="' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["logo_url"]) . '" /><br />' . "\n";
		echo '<input type="button" id="ws-theme--logo-url-media-btn" value="Open Media Library" class="ws-menu-page-media-btn" rel="ws-theme--logo-url" />' . "\n";
		echo 'You can upload your logo using the Media Library. It should be a 24-bit PNG with transparency, ' . esc_html ($GLOBALS["WS_THEME__"]["c"]["logo_width_x_height"]) . ' px.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-hr ws-theme--favicon-section-hr"></div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--favicon-section">' . "\n";
		echo '<h3>Favicon Location ( optional, for further customization )</h3>' . "\n";
		echo '<p>A Favicon (short for favorites icon), is a 16 × 16 pixel square icon associated with a particular website or webpage. A web designer can create such an icon and install it into a website (or webpage) by several means, and most graphical web browsers will then make use of it. Browsers that provide Favicon support, typically display a page\'s Favicon in the browser\'s address bar and next to the page\'s name in a list of bookmarks. Browsers that support a tabbed document interface typically show a page\'s Favicon next to the page\'s title on the tab.</p>' . "\n";
		echo '<p><em>* There is an <a href="http://www.favicon.cc/" target="_blank" rel="external">online tool for generating Favicons</a> that may help you. The folks over at HTML-Kit.com also provide a <a href="http://www.html-kit.com/favicon/" target="_blank" rel="external">similar tool</a>.</em></p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_favicon", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--favicon-url">' . "\n";
		echo 'Favicon ICO File ( Full URL Location ):';
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="text" name="ws_theme__favicon_url" id="ws-theme--favicon-url" value="' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["favicon_url"]) . '" /><br />' . "\n";
		echo '<input type="button" id="ws-theme--favicon-url-media-btn" value="Open Media Library" class="ws-menu-page-media-btn" rel="ws-theme--favicon-url" />' . "\n";
		echo 'You can upload your Favicon using the Media Library.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_logo_favicon", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_nav_layouts", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_nav_layouts", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Navigation Menu Options">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--nav-layout-section">' . "\n";
		echo '<h3>Navigation Layout Model ( required, please customize this )</h3>' . "\n";
		echo '<p>This setting determines which type of Navigation Menu you would like to use. As of WordPress® 3.0+, we recommend a `Custom WordPress® Menu`. This option provides you with the greatest flexibility. If you decide to go with a Custom WordPress® Menu, you\'ll need to configure your Menu under <code>WordPress® -> Appearance -> Menus</code>. If you don\'t have WordPress® 3.0+ yet, you can choose to include Pages, Categories, or you can use a combination of both Pages &amp; Categories. Based on your selection, there may be some additional settings below, which you can use to fine-tune things further.</p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_nav_layouts", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--nav-layout-model">' . "\n";
		echo 'Navigation Layout Model:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__nav_layout_model" id="ws-theme--nav-layout-model">' . "\n";
		echo '<option value="pages"' . (($GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "pages") ? ' selected="selected"' : '') . '>Pages Only' . "\n";
		echo '<option value="categories"' . (($GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "categories") ? ' selected="selected"' : '') . '>Categories Only</option>' . "\n";
		echo '<option value="page_cat_combo"' . (($GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "page_cat_combo") ? ' selected="selected"' : '') . '>Pages w/nested Categories' . "\n";
		echo '<option value="cat_page_combo"' . (($GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "cat_page_combo") ? ' selected="selected"' : '') . '>Categories w/nested Pages</option>' . "\n";
		echo '<option value="custom"' . (($GLOBALS["WS_THEME__"]["o"]["nav_layout_model"] === "custom") ? ' selected="selected"' : '') . '>Custom WordPress® Menu ( Appearance -> Menus )</option>' . "\n";
		echo '</select>' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-cat-layout-row">' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--nav-cats-title">' . "\n";
		echo 'Title For Nested Categories:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-cat-layout-row">' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="text" name="ws_theme__nav_cats_title" id="ws-theme--nav-cats-title" value="' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["nav_cats_title"]) . '" /><br />' . "\n";
		echo 'Nested Categories will be listed under this main un-clickable Menu item.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-cat-layout-row">' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--nav-cats-position">' . "\n";
		echo 'Menu Position For Nested Categories:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-cat-layout-row">' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__nav_cats_position" id="ws-theme--nav-cats-position">' . "\n";
		echo '<option value="before-pages"' . (($GLOBALS["WS_THEME__"]["o"]["nav_cats_position"] === "before-pages") ? ' selected="selected"' : '') . '>Before All Pages</option>' . "\n";
		echo '<option value="after-pages"' . (($GLOBALS["WS_THEME__"]["o"]["nav_cats_position"] === "after-pages") ? ' selected="selected"' : '') . '>» After All Pages</option>' . "\n";
		for ($ws_theme__temp_a = array_merge ((array)get_pages ()), $ws_theme__temp_a_c = count ($ws_theme__temp_a), $ws_theme__temp_i = 0; $ws_theme__temp_i < $ws_theme__temp_a_c; $ws_theme__temp_i++)
			echo '<option value="before-page-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->ID) . '"' . (($GLOBALS["WS_THEME__"]["o"]["nav_cats_position"] === "before-page-" . $ws_theme__temp_a[$ws_theme__temp_i]->ID) ? ' selected="selected"' : '') . '>Before Page: ' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->post_title) . '</option>' ./**/
			'<option value="after-page-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->ID) . '"' . (($GLOBALS["WS_THEME__"]["o"]["nav_cats_position"] === "after-page-" . $ws_theme__temp_a[$ws_theme__temp_i]->ID) ? ' selected="selected"' : '') . '>» After Page: ' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->post_title) . '</option>' . "\n";
		echo '</select><br />' . "\n";
		echo 'This is how you position nested Categories exactly where you want them.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-page-layout-row">' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--nav-pages-title">' . "\n";
		echo 'Title For Nested Pages:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-page-layout-row">' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="text" name="ws_theme__nav_pages_title" id="ws-theme--nav-pages-title" value="' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["nav_pages_title"]) . '" /><br />' . "\n";
		echo 'Nested Pages will be listed under this main Menu item that will link to your Home Page.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-page-layout-row">' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--nav-pages-position">' . "\n";
		echo 'Menu Position For Nested Pages:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-page-layout-row">' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__nav_pages_position" id="ws-theme--nav-pages-position">' . "\n";
		echo '<option value="before-categories"' . (($GLOBALS["WS_THEME__"]["o"]["nav_pages_position"] === "before-categories") ? ' selected="selected"' : '') . '>Before All Categories</option>' . "\n";
		echo '<option value="after-categories"' . (($GLOBALS["WS_THEME__"]["o"]["nav_pages_position"] === "after-categories") ? ' selected="selected"' : '') . '>» After All Categories</option>' . "\n";
		for ($ws_theme__temp_a = array_merge ((array)get_categories ("hide_empty=0&orderby=name")), $ws_theme__temp_a_c = count ($ws_theme__temp_a), $ws_theme__temp_i = 0; $ws_theme__temp_i < $ws_theme__temp_a_c; $ws_theme__temp_i++)
			echo '<option value="before-category-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '"' . (($GLOBALS["WS_THEME__"]["o"]["nav_pages_position"] === "before-category-" . $ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) ? ' selected="selected"' : '') . '>Before Category: ' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->cat_name) . '</option>' ./**/
			'<option value="after-category-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '"' . (($GLOBALS["WS_THEME__"]["o"]["nav_pages_position"] === "after-category-" . $ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) ? ' selected="selected"' : '') . '>» After Category: ' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->cat_name) . '</option>' . "\n";
		echo '</select><br />' . "\n";
		echo 'This is how you position nested Pages exactly where you want them.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--nav-custom-layout-row">' . "\n";
		/**/
		echo '<td colspan="2">' . "\n";
		echo 'A Custom Menu can be created using WordPress® 3.0+. See: <code>WordPress® -> Appearance -> Menus</code> for full details and configuration.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-hr ws-theme--page-nav-section-hr"></div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--page-nav-section">' . "\n";
		echo '<h3>Page Navigation Settings ( optional configuration )</h3>' . "\n";
		echo '<p>You have selected a Navigation Layout Model that includes Pages. Here you can specify the overall Sort Order and content of your Pages Menu.</p>' . "\n";
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--page-nav-sort-column">' . "\n";
		echo 'Page Navigation Sort Column/Order:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__page_nav_sort_column" id="ws-theme--page-nav-sort-column" style="width:auto;">' . "\n";
		echo '<option value="post_title"' . (($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_column"] === "post_title") ? ' selected="selected"' : '') . '>Page Title</option>' . "\n";
		echo '<option value="post_name"' . (($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_column"] === "post_name") ? ' selected="selected"' : '') . '>Page Slug</option>' . "\n";
		echo '<option value="menu_order"' . (($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_column"] === "menu_order") ? ' selected="selected"' : '') . '>Page Order</option>' . "\n";
		echo '</select>&nbsp;' . "\n";
		echo '<select name="ws_theme__page_nav_sort_order" id="ws-theme--page-nav-sort-order" style="width:auto;">' . "\n";
		echo '<option value="asc"' . (($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_order"] === "asc") ? ' selected="selected"' : '') . '>Ascending</option>' . "\n";
		echo '<option value="desc"' . (($GLOBALS["WS_THEME__"]["o"]["page_nav_sort_order"] === "desc") ? ' selected="selected"' : '') . '>Descending</option>' . "\n";
		echo '</select><br />' . "\n";
		echo 'This determines the order in which Pages will be displayed.<br />' . "\n";
		echo 'If you select <code>Page Order</code>, it will go by the numerical Order Number you supply in the Attributes section for each Page.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--page-nav-exclusions">' . "\n";
		echo 'Page Navigation Exclusions:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<div class="ws-menu-page-scrollbox">' . "\n";
		echo '<input type="hidden" name="ws_theme__page_nav_exclusions[]" value="update-signal" />' . "\n";
		for ($ws_theme__temp_a = array_merge ((array)get_pages ()), $ws_theme__temp_a_c = count ($ws_theme__temp_a), $ws_theme__temp_i = 0; $ws_theme__temp_i < $ws_theme__temp_a_c; $ws_theme__temp_i++)
			echo '<input type="checkbox" name="ws_theme__page_nav_exclusions[]" id="ws-theme--page-nav-exclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->ID) . '" value="' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->ID) . '"' . ((in_array ($ws_theme__temp_a[$ws_theme__temp_i]->ID, $GLOBALS["WS_THEME__"]["o"]["page_nav_exclusions"])) ? ' checked="checked"' : '') . ' /> <label for="ws-theme--page-nav-exclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->ID) . '">' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->post_title) . '</label>' . (($ws_theme__temp_i + 1 < $ws_theme__temp_a_c) ? '<br />' : '') . "\n";
		echo '</div>' . "\n";
		echo 'This is how you prevent certain Pages from appearing in the menu.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--page-nav-inclusions">' . "\n";
		echo 'Page Navigation Inclusions:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<div class="ws-menu-page-scrollbox">' . "\n";
		echo '<input type="hidden" name="ws_theme__page_nav_inclusions[]" value="update-signal" />' . "\n";
		for ($ws_theme__temp_a = array_merge ((array)get_pages ()), $ws_theme__temp_a_c = count ($ws_theme__temp_a), $ws_theme__temp_i = 0; $ws_theme__temp_i < $ws_theme__temp_a_c; $ws_theme__temp_i++)
			echo '<input type="checkbox" name="ws_theme__page_nav_inclusions[]" id="ws-theme--page-nav-inclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->ID) . '" value="' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->ID) . '"' . ((in_array ($ws_theme__temp_a[$ws_theme__temp_i]->ID, $GLOBALS["WS_THEME__"]["o"]["page_nav_inclusions"])) ? ' checked="checked"' : '') . ' /> <label for="ws-theme--page-nav-inclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->ID) . '">' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->post_title) . '</label>' . (($ws_theme__temp_i + 1 < $ws_theme__temp_a_c) ? '<br />' : '') . "\n";
		echo '</div>' . "\n";
		echo 'This is how you allow only certain Pages to appear in the menu.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-hr ws-theme--cat-nav-section-hr"></div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--cat-nav-section">' . "\n";
		echo '<h3>Category Navigation Settings ( optional configuration )</h3>' . "\n";
		echo '<p>You\'ve selected a Navigation Layout Model that includes Categories. Here you can specify the overall Sort Order and content of your Categories Menu.</p>' . "\n";
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--cat-nav-sort-column">' . "\n";
		echo 'Category Navigation Sort Column/Order:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__cat_nav_sort_column" id="ws-theme--cat-nav-sort-column" style="width:auto;">' . "\n";
		echo '<option value="ID"' . (($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_column"] === "ID") ? ' selected="selected"' : '') . '>Category ID</option>' . "\n";
		echo '<option value="name"' . (($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_column"] === "name") ? ' selected="selected"' : '') . '>Category Name</option>' . "\n";
		echo '</select>&nbsp;' . "\n";
		echo '<select name="ws_theme__cat_nav_sort_order" id="ws-theme--cat-nav-sort-order" style="width:auto;">' . "\n";
		echo '<option value="asc"' . (($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_order"] === "asc") ? ' selected="selected"' : '') . '>Ascending</option>' . "\n";
		echo '<option value="desc"' . (($GLOBALS["WS_THEME__"]["o"]["cat_nav_sort_order"] === "desc") ? ' selected="selected"' : '') . '>Descending</option>' . "\n";
		echo '</select><br />' . "\n";
		echo 'This determines the order in which Categories will be displayed.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--cat-nav-exclusions">' . "\n";
		echo 'Category Navigation Exclusions:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<div class="ws-menu-page-scrollbox">' . "\n";
		echo '<input type="hidden" name="ws_theme__cat_nav_exclusions[]" value="update-signal" />' . "\n";
		for ($ws_theme__temp_a = array_merge ((array)get_categories ("hide_empty=0&orderby=name")), $ws_theme__temp_a_c = count ($ws_theme__temp_a), $ws_theme__temp_i = 0; $ws_theme__temp_i < $ws_theme__temp_a_c; $ws_theme__temp_i++)
			echo '<input type="checkbox" name="ws_theme__cat_nav_exclusions[]" id="ws-theme--cat-nav-exclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '" value="' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '"' . ((in_array ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID, $GLOBALS["WS_THEME__"]["o"]["cat_nav_exclusions"])) ? ' checked="checked"' : '') . ' /> <label for="ws-theme--cat-nav-exclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '">' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->cat_name) . '</label>' . (($ws_theme__temp_i + 1 < $ws_theme__temp_a_c) ? '<br />' : '') . "\n";
		echo '</div>' . "\n";
		echo 'This is how you prevent certain Categories from appearing in the menu.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--cat-nav-inclusions">' . "\n";
		echo 'Category Navigation Inclusions:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<div class="ws-menu-page-scrollbox">' . "\n";
		echo '<input type="hidden" name="ws_theme__cat_nav_inclusions[]" value="update-signal" />' . "\n";
		for ($ws_theme__temp_a = array_merge ((array)get_categories ("hide_empty=0&orderby=name")), $ws_theme__temp_a_c = count ($ws_theme__temp_a), $ws_theme__temp_i = 0; $ws_theme__temp_i < $ws_theme__temp_a_c; $ws_theme__temp_i++)
			echo '<input type="checkbox" name="ws_theme__cat_nav_inclusions[]" id="ws-theme--cat-nav-inclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '" value="' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '"' . ((in_array ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID, $GLOBALS["WS_THEME__"]["o"]["cat_nav_inclusions"])) ? ' checked="checked"' : '') . ' /> <label for="ws-theme--cat-nav-inclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '">' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->cat_name) . '</label>' . (($ws_theme__temp_i + 1 < $ws_theme__temp_a_c) ? '<br />' : '') . "\n";
		echo '</div>' . "\n";
		echo 'This is how you allow only certain Categories to appear in the menu.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_nav_layouts", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_home_config", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_home_config", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Home Page Configuration">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--home-title-section">' . "\n";
		echo '<h3>Home Page H1 Title/Description ( optional configuration )</h3>' . "\n";
		echo '<p>This determines the H1 Title and Description for your Home Page.</p>' . "\n";
		echo '<p><em>* If you configure WordPress® to use <a href="http://codex.wordpress.org/Creating_a_Static_Front_Page" target="_blank" rel="external">a static Page as your Home Page</a>, then this setting will simply be ignored. In other words, this H1 Title &amp; Description apply ONLY if you use the default WordPress® configuration, where a list of your most recent Posts are displayed on the Home Page.</em></p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_home_config", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--home-h1-title">' . "\n";
		echo 'Home Page ( H1 Title ):' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="text" name="ws_theme__home_h1_title" id="ws-theme--home-h1-title" value="' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["home_h1_title"]) . '" /><br />' . "\n";
		echo 'Defaults to the name of your site: <code>' . get_bloginfo ("name") . '</code>' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--home-h1-desc">' . "\n";
		echo 'Home Page ( Description / Tag Line ):' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="text" name="ws_theme__home_h1_desc" id="ws-theme--home-h1-desc" value="' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["home_h1_desc"]) . '" /><br />' . "\n";
		echo 'Defaults to the tag line for your site: <code>' . get_bloginfo ("description") . '</code>' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-hr ws-theme--home-filters-section-hr"></div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--home-filters-section">' . "\n";
		echo '<h3>Home Page Filters ( optional configuration )</h3>' . "\n";
		echo '<p>Sticky Only? ... if you set this to <code>Yes</code>, only Posts that you\'ve made Sticky will be shown on the Home Page, and all other non-Sticky Posts will be excluded. The `Sticky` feature was first introduced in WP 2.7. It allows you to send important Posts, to the top of your Home Page; despite its Category or date of publication. This theme offers you the added ability to restrict Posts on the Home Page to only those that you\'ve marked as being Sticky, thereby excluding all other Posts ( Posts that are NOT Sticky ) from the Home Page completely.</p>' . "\n";
		echo '<p><em>* If you configure WordPress® to use <a href="http://codex.wordpress.org/Creating_a_Static_Front_Page" target="_blank" rel="external">a static Page as your Home Page</a>, then the settings below will be ignored. In other words, these settings only apply, if you use the default WordPress® configuration, where a list of your most recent Posts are displayed on the Home Page.</em></p>' . "\n";
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--home-only-sticky">' . "\n";
		echo 'Home Page Sticky Only?' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__home_only_sticky" id="ws-theme--home-only-sticky">' . "\n";
		echo '<option value="0"' . ((!$GLOBALS["WS_THEME__"]["o"]["home_only_sticky"]) ? ' selected="selected"' : '') . '>No ( allow all Posts, and just push Sticky Posts to the top )</option>' . "\n";
		echo '<option value="1"' . (($GLOBALS["WS_THEME__"]["o"]["home_only_sticky"]) ? ' selected="selected"' : '') . '>Yes ( only show Sticky Posts on the Home Page )</option>' . "\n";
		echo '</select><br />' . "\n";
		echo 'This will simply be ignored if you\'re using a static Page as your Home Page.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--home-cat-filter-row">' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--home-cat-exclusions">' . "\n";
		echo 'Home Page Category Exclusions:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--home-cat-filter-row">' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<div class="ws-menu-page-scrollbox">' . "\n";
		echo '<input type="hidden" name="ws_theme__home_cat_exclusions[]" value="update-signal" />' . "\n";
		for ($ws_theme__temp_a = array_merge ((array)get_categories ("hide_empty=0&orderby=name")), $ws_theme__temp_a_c = count ($ws_theme__temp_a), $ws_theme__temp_i = 0; $ws_theme__temp_i < $ws_theme__temp_a_c; $ws_theme__temp_i++)
			echo '<input type="checkbox" name="ws_theme__home_cat_exclusions[]" id="ws-theme--home-cat-exclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '" value="' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '"' . ((in_array ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID, $GLOBALS["WS_THEME__"]["o"]["home_cat_exclusions"])) ? ' checked="checked"' : '') . ' /> <label for="ws-theme--home-cat-exclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '">' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->cat_name) . '</label>' . (($ws_theme__temp_i + 1 < $ws_theme__temp_a_c) ? '<br />' : '') . "\n";
		echo '</div>' . "\n";
		echo 'This is how you prevent Posts from certain Categories, from appearing on the Home Page. However, it should be noted that Sticky Posts will ALWAYS appear on the Home Page, no matter how you configure these options. When you mark a Post as being `Sticky`, it will always be pushed to the top of your Home Page, no matter what Category it resides in.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--home-cat-filter-row">' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--home-cat-inclusions">' . "\n";
		echo 'Home Page Category Inclusions:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr class="ws-theme--home-cat-filter-row">' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<div class="ws-menu-page-scrollbox">' . "\n";
		echo '<input type="hidden" name="ws_theme__home_cat_inclusions[]" value="update-signal" />' . "\n";
		for ($ws_theme__temp_a = array_merge ((array)get_categories ("hide_empty=0&orderby=name")), $ws_theme__temp_a_c = count ($ws_theme__temp_a), $ws_theme__temp_i = 0; $ws_theme__temp_i < $ws_theme__temp_a_c; $ws_theme__temp_i++)
			echo '<input type="checkbox" name="ws_theme__home_cat_inclusions[]" id="ws-theme--home-cat-inclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '" value="' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '"' . ((in_array ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID, $GLOBALS["WS_THEME__"]["o"]["home_cat_inclusions"])) ? ' checked="checked"' : '') . ' /> <label for="ws-theme--home-cat-inclusions-' . esc_attr ($ws_theme__temp_a[$ws_theme__temp_i]->cat_ID) . '">' . esc_html ($ws_theme__temp_a[$ws_theme__temp_i]->cat_name) . '</label>' . (($ws_theme__temp_i + 1 < $ws_theme__temp_a_c) ? '<br />' : '') . "\n";
		echo '</div>' . "\n";
		echo 'This is how you allow Posts, only in certain Categories, to appear on the Home Page. However, it should be noted that Sticky Posts will ALWAYS appear on the Home Page, no matter how you configure these options. When you mark a Post as being `Sticky`, it will always be pushed to the top of your Home Page, no matter what Category it resides in.' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_home_config", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_excerpts_thumbnails", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_excerpts_thumbnails", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Excerpts &amp; Thumbnails">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--excerpts-thumbnails-section">' . "\n";
		echo '<h3>Excerpt/Thumbnail Display ( optional, for further customization )</h3>' . "\n";
		echo '<p>The WordPress® Excerpt is a brief description of a Page or Post. The Excerpt can be used as a Teaser, when a list of Posts/Pages are being displayed together on the same page. Excerpts can be generated automatically by the WordPress® software, in plain text format. These are referred to as Textual Excerpts, or Automatic Excerpts. They are a snippet of the first few words, taken from the beginning of the full Content body. In WordPress®, you can also set a Featured Image Thumbnail for each Post/Page that will accompany this Textual Excerpt. The options below, give you the ability to control when, where &amp; how Excerpts will be displayed.</p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_excerpts_thumbnails", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--display-excerpts">' . "\n";
		echo 'Excerpt/Thumbnail Display Options:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="radio" value="always" name="ws_theme__display_excerpts" id="ws-theme--display-excerpts-always"' . (($GLOBALS["WS_THEME__"]["o"]["display_excerpts"] === "always") ? ' checked="checked"' : '') . '> <label for="ws-theme--display-excerpts-always"><strong>Always Display Textual Excerpts With Thumbnails</strong></label><br />' . "\n";
		echo 'This is the default setting. This forces <a href="http://codex.wordpress.org/Excerpt" target="_blank" rel="external">Textual Excerpts</a> to be used ( instead of Rich Content ) anytime a list of Posts/Pages are being displayed together. With this option selected, you won\'t need to worry about using the <code>&lt;!--more--&gt;</code> tag. You also won\'t need to worry about providing a special Excerpt for each Page or Post, because WordPress® can generate one from your Content automatically. If you do provide a special Excerpt, it will be used; but you won\'t have to. Also, with this option selected, you can set a Featured Image Thumbnail ( optional ) for each Post/Page, to be displayed along with the Textual Excerpt.<br /><br />' . "\n";
		echo '<input type="radio" value="search" name="ws_theme__display_excerpts" id="ws-theme--display-excerpts-search"' . (($GLOBALS["WS_THEME__"]["o"]["display_excerpts"] === "search") ? ' checked="checked"' : '') . '> <label for="ws-theme--display-excerpts-search"><strong>Use Rich Content / Everywhere But Search Results</strong></label><br />' . "\n";
		echo 'With this option, Textual Excerpts w/Thumbnails will only be displayed on Search Result pages. Everywhere else, your Rich Content will be used, and you will want to make use of the <code>&lt;!--more--&gt;</code> tag. This creates a little more work for you, but it does give you more control over everything. You can use the <a href="http://codex.wordpress.org/Template_Tags/the_content" target="_blank" rel="external"><code>&lt;!--more--&gt;</code></a> tag to customize which portion of Rich Content you want used for the Excerpt, and that portion of your Content can contain whatever images or other media you prefer. <em>* You should still set a Thumbnail for each Post/Page, because Featured Image Thumbnails may be displayed out of necessity, in specific areas of your theme that require them.</em><br /><br />' . "\n";
		echo '<input type="radio" value="never" name="ws_theme__display_excerpts" id="ws-theme--display-excerpts-never"' . (($GLOBALS["WS_THEME__"]["o"]["display_excerpts"] === "never") ? ' checked="checked"' : '') . '> <label for="ws-theme--display-excerpts-never"><strong>Always Rich Content / Never Use Textual Excerpts</strong></label><br />' . "\n";
		echo 'With this option, Textual Excerpts w/Thumbnails will never be displayed. Not even in search results. Your Rich Content will always be used, and you will want to make use of the <code>&lt;!--more--&gt;</code> tag. <em>* You should still set a Thumbnail for each Post/Page, because Featured Image Thumbnails may be displayed out of necessity, in specific areas of your theme that require them.</em>' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--excerpt-words">' . "\n";
		echo 'Auto-Generated Excerpt Length ( number of words ):' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="text" name="ws_theme__excerpt_words" id="ws-theme--excerpt-words" value="' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["excerpt_words"]) . '" /><br />' . "\n";
		echo 'When WordPress® automatically generates a Textual Excerpt, how many words should it pull from the full Content?' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--more-tag-label">' . "\n";
		echo 'The Link Text Following An Excerpt:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<input type="text" name="ws_theme__more_tag_label" id="ws-theme--more-tag-label" value="' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["more_tag_label"]) . '" /><br />' . "\n";
		echo 'After the Excerpt, there will be a link to continue reading the rest of the Post/Page. What should this link say?' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_excerpts_thumbnails", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_global_xhtml", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_global_xhtml", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Tracking &amp; Analytics">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--global-xhtml-section">' . "\n";
		echo '<h3>Global XHTML Code ( optional, for further customization )</h3>' . "\n";
		echo '<p>You can use any XHTML or JavaScript here. This is a good place to put code snippets that you want to have loaded on ALL pages of the site. You might paste in your Google® Analytics tracking code, some JavaScript functions, or maybe a tracking code for affiliate sales. Generally speaking, the code snippets that you insert here should not introduce anything visual to the site. This section is best suited for tracking codes and other non-visual elements.</p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_global_xhtml", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--global-tracking-code">' . "\n";
		echo 'Global XHTML Code:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<textarea name="ws_theme__global_tracking_code" id="ws-theme--global-tracking-code" rows="8" spellcheck="false">' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["global_tracking_code"]) . '</textarea><br />' . "\n";
		echo 'Any valid XHTML code should work fine.' . ((c_ws_theme__utils_conds::is_multisite_farm ()) ? '' : ' This field also supports PHP code if you like.') . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_global_xhtml", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_footbar", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_footbar", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Footbar Configuration">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--footbar-appendage-section">' . "\n";
		echo '<h3>Footbar Appendage ( optional, for further customization )</h3>' . "\n";
		echo '<p>This is an XHTML block of code that will appear just underneath the Footbar, as an appendage. You can put whatever you like here. This is a good place to add links to your privacy policy, terms and conditions, affiliate program, contact page, etc. This is also a good place to work on search engine optimization. You might want to include several key phrases within the content of this section.</p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_footbar", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--footbar-appendage-code">' . "\n";
		echo 'Footbar XHTML Appendage:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<textarea name="ws_theme__footbar_appendage_code" id="ws-theme--footbar-appendage-code" rows="8" spellcheck="false">' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["footbar_appendage_code"]) . '</textarea><br />' . "\n";
		echo 'Any valid XHTML code should work fine.' . ((c_ws_theme__utils_conds::is_multisite_farm ()) ? '' : ' This field also supports PHP code if you like.') . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-hr ws-theme--footbar-companion-section-hr"></div>' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--footbar-companion-section">' . "\n";
		echo '<h3>Footbar Companion Codes ( required, but defaults work fine )</h3>' . "\n";
		echo '<p>These are the two small sections that sit just underneath the Footbar ( as a Companion ) with your copyright info and theme credits. There\'s two separate sections to this: the left side &amp; the right. This theme came with default values for both sides that will work just fine for you. However, if you decide to customize these, and then later want to revert back to the defaults, just empty out both fields, it\'s that simple.</p>' . "\n";
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--lfbcomp-left-code">' . "\n";
		echo 'Left Side XHTML Code:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<textarea name="ws_theme__lfbcomp_left_code" id="ws-theme--lfbcomp-left-code" rows="5" spellcheck="false">' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["lfbcomp_left_code"]) . '</textarea><br />' . "\n";
		echo 'By default, this will display the name of your site along with a copyright notice. Feel free to change this if you like. Any valid XHTML code should work fine.' . ((c_ws_theme__utils_conds::is_multisite_farm ()) ? '' : ' This field also supports PHP code.') . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--lfbcomp-right-code">' . "\n";
		echo 'Right Side XHTML Code:' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<textarea name="ws_theme__lfbcomp_right_code" id="ws-theme--lfbcomp-right-code" rows="5" spellcheck="false">' . format_to_edit ($GLOBALS["WS_THEME__"]["o"]["lfbcomp_right_code"]) . '</textarea><br />' . "\n";
		echo 'By default, this will display proper credits to the designer of this theme. Feel free to change this if you like. Any valid XHTML code should work fine.' . ((c_ws_theme__utils_conds::is_multisite_farm ()) ? '' : ' This field also supports PHP code.') . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_footbar", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_options_page_during_left_sections_display_deactivation", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_options_page_during_left_sections_before_deactivation", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Deactivation Safeguards">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--deactivation-section">' . "\n";
		echo '<h3>Deactivation Safeguards ( optional, recommended )</h3>' . "\n";
		echo '<p>By default, this theme will cleanup ( erase ) all of your Configuration Options when/if you deactivate it from the Themes Menu in WordPress®. If you would like to Safeguard all of this information ( recommended ), in case it is deactivated inadvertently, please choose Yes ( safeguard all theme data/options ).</p>' . "\n";
		do_action ("ws_theme__during_options_page_during_left_sections_during_deactivation", get_defined_vars ());
		/**/
		echo '<table class="form-table">' . "\n";
		echo '<tbody>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<th>' . "\n";
		echo '<label for="ws-theme--run-deactivation-routines">' . "\n";
		echo 'Safeguard Theme Data/Options?' . "\n";
		echo '</label>' . "\n";
		echo '</th>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '<tr>' . "\n";
		/**/
		echo '<td>' . "\n";
		echo '<select name="ws_theme__run_deactivation_routines" id="ws-theme--run-deactivation-routines">' . "\n";
		echo '<option value="1"' . (($GLOBALS["WS_THEME__"]["o"]["run_deactivation_routines"]) ? ' selected="selected"' : '') . '></option>' . "\n";
		echo '<option value="0"' . ((!$GLOBALS["WS_THEME__"]["o"]["run_deactivation_routines"]) ? ' selected="selected"' : '') . '>Yes ( safeguard all data/options )</option>' . "\n";
		echo '</select><br />' . "\n";
		echo 'Recommended setting: ( <code>Yes, safeguard all data/options</code> )' . "\n";
		echo '</td>' . "\n";
		/**/
		echo '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_options_page_during_left_sections_after_deactivation", get_defined_vars ());
	}
/**/
do_action ("ws_theme__during_options_page_after_left_sections", get_defined_vars ());
/**/
echo '<div class="ws-menu-page-hr"></div>' . "\n";
/**/
echo '<p class="submit"><input type="submit" class="button-primary" value="Save All Changes" /></p>' . "\n";
/**/
echo '</form>' . "\n";
/**/
echo '</td>' . "\n";
/**/
echo '<td class="ws-menu-page-table-r">' . "\n";
/**/
do_action ("ws_theme__during_options_page_before_right_sections", get_defined_vars ());
do_action ("ws_theme__during_menu_pages_before_right_sections", get_defined_vars ());
/**/
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["installation"]) ? '<div class="ws-menu-page-installation"><a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Professional Installation URI")) . '" target="_blank"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-installation.png") . '" alt="." /></a></div>' . "\n" : '';
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["tools"]) ? '<div class="ws-menu-page-tools"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-tools.png") . '" alt="." /></div>' . "\n" : '';
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["videos"]) ? '<div class="ws-menu-page-videos"><a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Video Tutorials")) . '" target="_blank"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-videos.png") . '" alt="." /></a></div>' . "\n" : '';
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["support"]) ? '<div class="ws-menu-page-support"><a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Forum URI")) . '" target="_blank"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-support.png") . '" alt="." /></a></div>' . "\n" : '';
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["donations"]) ? '<div class="ws-menu-page-donations"><a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Donate link")) . '" target="_blank"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-donations.png") . '" alt="." /></a></div>' . "\n" : '';
/**/
do_action ("ws_theme__during_menu_pages_after_right_sections", get_defined_vars ());
do_action ("ws_theme__during_options_page_after_right_sections", get_defined_vars ());
/**/
echo '</td>' . "\n";
/**/
echo '</tr>' . "\n";
echo '</tbody>' . "\n";
echo '</table>' . "\n";
/**/
echo '</div>' . "\n";
?>