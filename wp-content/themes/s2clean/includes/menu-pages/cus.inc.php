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
Info page.
*/
echo '<div class="wrap ws-menu-page">' . "\n";
/**/
echo '<h2>Advanced Customization</h2>' . "\n";
/**/
echo '<table class="ws-menu-page-table">' . "\n";
echo '<tbody class="ws-menu-page-table-tbody">' . "\n";
echo '<tr class="ws-menu-page-table-tr">' . "\n";
echo '<td class="ws-menu-page-table-l">' . "\n";
/**/
do_action ("ws_theme__during_customization_page_before_left_sections", get_defined_vars ());
/**/
if (apply_filters ("ws_theme__during_customization_page_during_left_sections_display_tweaking", true, get_defined_vars ()))
	{
		if (!c_ws_theme__utils_conds::is_multisite_farm ()) /* NOT on Multisite Farms. */
			{
				do_action ("ws_theme__during_customization_page_during_left_sections_before_tweaking", get_defined_vars ());
				/**/
				echo '<div class="ws-menu-page-group" title="CSS / Tweaking Your Theme">' . "\n";
				/**/
				echo '<div class="ws-menu-page-section ws-theme--tweaking-section">' . "\n";
				echo '<h3>How-To Tweak This Theme\'s Stylesheets</h3>' . "\n";
				echo '<p>This theme can be tweaked further by qualified web developers. If you\'d like some intuitive assistance from the original creators, <a href="http://www.primothemes.com/support/" target="_blank" rel="external">contact PriMoThemes.com</a> for custom development quotes. Generally speaking, you\'re looking at somewhere between $200 - $500 USD for advanced customization, depending on the specifics of your request. If you\'re an aspiring web developer and would just like to modify the CSS files used by this theme, please check your WordPress® theme directory under: <code>../' . esc_html (get_template ()) . '/colors</code>.</p>' . "\n";
				echo '<p>There is an <a href="http://www.primothemes.com/category/faqs/" target="_blank" rel="external">FAQ ( Frequently Asked Questions )</a> and <a href="http://www.primothemes.com/about/framework/" target="_blank" rel="external">Framework Documentation</a> section on our website. The <a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Forum URI")) . '" target="_blank" rel="external">Support Forums</a> are a great place to ask questions and troubleshoot problems with PriMoThemes. All of these are great tools that can really help you out. You will also find a wealth of community driven information available in the <a href="http://codex.wordpress.org/" target="_blank" rel="external">WordPress® Codex</a>.</p>' . "\n";
				echo '<a href="http://www.elance.com/p/how_it_works.html" target="_blank"><img src="http://img21.imageshack.us/img21/3962/elancesmalllogo.png" style="width:75px; height:18px; border:0px; vertical-align:middle; float:right; margin-left:25px;" alt="Elance" /></a>' . "\n";
				echo '<p>Alternatively, you can try services such as <a href="http://www.elance.com/p/how_it_works.html" target="_blank" rel="external">Elance.com</a>. On Elance, companies gain access to 100k rated and tested professionals who offer technical, marketing and business expertise. Elance delivers an immediate, cost-effective and flexible way to hire, manage and pay independent professionals and contractors. There are many developers on Elance that are familiar with PriMoThemes. <a href="http://www.elance.com/p/how_it_works.html" target="_blank" rel="external">Learn more about Elance</a>.</p>' . "\n";
				do_action ("ws_theme__during_customization_page_during_left_sections_during_tweaking", get_defined_vars ());
				echo '</div>' . "\n";
				/**/
				echo '<div class="ws-menu-page-hr ws-theme--theme-options-section-hr"></div>' . "\n";
				/**/
				echo '<div class="ws-menu-page-section ws-theme--theme-options-section">' . "\n";
				echo '<h3>Using Your Built-In Theme Options Panel</h3>' . "\n";
				echo '<p>Before you fire up your FTP application and start editing theme files directly, be sure to check your built-in Theme Options panel first. We try to include as many options as possible, so that digging through CSS and PHP files is not required at all, or at least very little. If you find there is something you cannot modify from the Theme Options Panel, then it is time to make more serious adjustments. If you\'re not comfortable editing theme files directly, you could also try installing one of <em>our</em> <a href="http://www.primothemes.com/category/wordpress-plugins/" target="_blank" rel="external">plugins</a> <em>( developed by PriMoThemes )</em>; or browse the official <a href="http://wordpress.org/extend/plugins/" target="_blank" rel="external">WordPress® Plugins Directory</a> in hopes of finding a solution that satisfies your particular requirements.</p>' . "\n";
				echo '</div>' . "\n";
				/**/
				echo '</div>' . "\n";
				/**/
				do_action ("ws_theme__during_customization_page_during_left_sections_after_tweaking", get_defined_vars ());
			}
	}
/**/
if (apply_filters ("ws_theme__during_customization_page_during_left_sections_display_custom_fields", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_customization_page_during_left_sections_before_custom_fields", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Post/Page Custom Fields">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--custom-fields-section">' . "\n";
		echo '<h3>Custom Fields Used By This Theme</h3>' . "\n";
		echo '<p>WordPress® allows you to assign <a href="http://codex.wordpress.org/Custom_Fields" target="_blank" rel="external">Custom Fields</a> to Posts &amp; Pages. This arbitrary extra information is known as meta-data. This theme created a new panel on both your Post and Page creation forms, titled: "Custom Fields Used By This Theme". Please refer to that panel for detailed descriptions about how these Custom Fields will be used when they are provided. Most notably, all of our themes understand the <code>h1_title</code> field, and also the <code>h1_desc</code> field. The details about how these fields are used, will be made available to you whenever you add or edit content inside WordPress®.</p>' . "\n";
		do_action ("ws_theme__during_customization_page_during_left_sections_during_custom_fields", get_defined_vars ());
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_customization_page_during_left_sections_after_custom_fields", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_customization_page_during_left_sections_display_pre_installed_widgets", true, get_defined_vars ()))
	{
		do_action ("ws_theme__during_customization_page_during_left_sections_before_pre_installed_widgets", get_defined_vars ());
		/**/
		echo '<div class="ws-menu-page-group" title="Pre-Installed Widgets">' . "\n";
		/**/
		echo '<div class="ws-menu-page-section ws-theme--pre-installed-widgets-section">' . "\n";
		echo '<h3>Pre-Installed Widgets For This Theme</h3>' . "\n";
		echo '<p>This theme comes with a few pre-installed widgets. At a minimum, all of our themes include the Super Tags widget, Ad Squares &amp; Ad Codes. You can utilize these widgets ( if you want to ) by adding them to a widget-ready panel from your WordPress® Dashboard. All of our themes include at least one widget-ready panel. Go to <code>Appearance -> Widgets</code>. Please note, the pre-installed widgets that came with this theme, are tied directly to the use of this theme. Therefore, if you switch themes, you will lose all of the functionality offered by these built-in widgets.' . ((c_ws_theme__utils_conds::is_multisite_farm ()) ? '' : ' If you feel the need to use these widgets apart from this theme, please visit <a href="http://www.primothemes.com/" target="_blank" rel="external">PriMoThemes.com</a> and download them separately.') . '</p>' . "\n";
		do_action ("ws_theme__during_customization_page_during_left_sections_during_pre_installed_widgets", get_defined_vars ());
		echo '</div>' . "\n";
		/**/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__during_customization_page_during_left_sections_after_pre_installed_widgets", get_defined_vars ());
	}
/**/
if (apply_filters ("ws_theme__during_customization_page_during_left_sections_display_page_templates", true, get_defined_vars ()))
	{
		if (is_array ($ws_theme__temp_a = get_page_templates ()) && !empty ($ws_theme__temp_a))
			{
				do_action ("ws_theme__during_customization_page_during_left_sections_before_page_templates", get_defined_vars ());
				/**/
				echo '<div class="ws-menu-page-group" title="Custom Page Templates">' . "\n";
				/**/
				echo '<div class="ws-menu-page-section ws-theme--page-templates-section">' . "\n";
				echo '<h3>Custom Page Templates For This Theme</h3>' . "\n";
				echo '<p>This theme came with some additional custom Page Templates:<br />' . "\n";
				echo '<em>' . esc_html (implode (", ", array_keys ($ws_theme__temp_a))) . '</em>. If you want to utilize these templates, you\'ll need to create a new Page with each of them. You can do this by going to Pages &raquo; Add New. Be sure to select a Page Template from the drop down menu there. Some of these Page Templates may display content dynamically, so all you really have to do is create an empty Page and give it a title. You can also add your own content to Pages that use special display templates, but that\'s optional. For more information about custom Page Templates, please read <a href="http://codex.wordpress.org/Pages#Page_Templates" target="_blank" rel="external">this article</a>.</p>' . "\n";
				do_action ("ws_theme__during_customization_page_during_left_sections_during_page_templates", get_defined_vars ());
				echo '</div>' . "\n";
				/**/
				echo '</div>' . "\n";
				/**/
				do_action ("ws_theme__during_customization_page_during_left_sections_after_page_templates", get_defined_vars ());
			}
	}
/**/
if (apply_filters ("ws_theme__during_customization_page_during_left_sections_display_flash", true, get_defined_vars ()))
	{
		if (!c_ws_theme__utils_conds::is_multisite_farm ()) /* NOT on Multisite Farms. */
			{
				do_action ("ws_theme__during_customization_page_during_left_sections_before_flash", get_defined_vars ());
				/**/
				echo '<div class="ws-menu-page-group" title="Embedding Flash® Content">' . "\n";
				/**/
				echo '<div class="ws-menu-page-section ws-theme--embedding-flash-section">' . "\n";
				echo '<h3>Embedding Flash® Content ( <code>wmode="transparent"</code> )</h3>' . "\n";
				echo '<p>By default, browsers place embedded plug-in content, such as a Flash® movie ( e.g. YouTube® videos ) or Java applets, on the topmost layer of a page. Unfortunately, this can cause drop-down menus, lightboxes, and sometimes other elements to be covered up; ( <a href="http://kb2.adobe.com/cps/155/tn_15523.html" target="_blank" rel="external">as reported here</a> ). To prevent this from becoming a problem on your site, you should always add the <code>wmode="transparent"</code> parameter to any <code>OBJECT/EMBED</code> code that you add to your site. If you publish YouTube® videos, or other media driven by Flash®, please read <a href="http://www.primothemes.com/post/embedding-flash-content/" target="_blank" rel="external">this article for a tutorial</a>.</p>' . "\n";
				do_action ("ws_theme__during_customization_page_during_left_sections_during_flash", get_defined_vars ());
				echo '</div>' . "\n";
				/**/
				echo '</div>' . "\n";
				/**/
				do_action ("ws_theme__during_customization_page_during_left_sections_after_flash", get_defined_vars ());
			}
	}
/**/
do_action ("ws_theme__during_customization_page_after_left_sections", get_defined_vars ());
/**/
echo '</td>' . "\n";
/**/
echo '<td class="ws-menu-page-table-r">' . "\n";
/**/
do_action ("ws_theme__during_customization_page_before_right_sections", get_defined_vars ());
do_action ("ws_theme__during_menu_pages_before_right_sections", get_defined_vars ());
/**/
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["installation"]) ? '<div class="ws-menu-page-installation"><a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Professional Installation URI")) . '" target="_blank"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-installation.png") . '" alt="." /></a></div>' . "\n" : '';
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["tools"]) ? '<div class="ws-menu-page-tools"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-tools.png") . '" alt="." /></div>' . "\n" : '';
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["videos"]) ? '<div class="ws-menu-page-videos"><a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Video Tutorials")) . '" target="_blank"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-videos.png") . '" alt="." /></a></div>' . "\n" : '';
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["support"]) ? '<div class="ws-menu-page-support"><a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Forum URI")) . '" target="_blank"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-support.png") . '" alt="." /></a></div>' . "\n" : '';
echo ($GLOBALS["WS_THEME__"]["c"]["menu_pages"]["donations"]) ? '<div class="ws-menu-page-donations"><a href="' . esc_attr (c_ws_theme__readmes::parse_readme_value ("Donate link")) . '" target="_blank"><img src="' . esc_attr (get_bloginfo ("template_url") . "/colors/" . $GLOBALS["WS_THEME__"]["c"]["color"] . "/images/brand-donations.png") . '" alt="." /></a></div>' . "\n" : '';
/**/
do_action ("ws_theme__during_menu_pages_after_right_sections", get_defined_vars ());
do_action ("ws_theme__during_customization_page_after_right_sections", get_defined_vars ());
/**/
echo '</td>' . "\n";
/**/
echo '</tr>' . "\n";
echo '</tbody>' . "\n";
echo '</table>' . "\n";
/**/
echo '</div>' . "\n";
?>