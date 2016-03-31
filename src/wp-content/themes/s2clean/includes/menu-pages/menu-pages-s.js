/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
These routines are all specific to this software.
*/
jQuery(document).ready (function($)
	{
		var homeStickyHandler; /* This function will be used more than once. */
		$('select#ws-theme--home-only-sticky').each (homeStickyHandler = function()
			{
				if (parseInt($(this).val ()))
					{
						$('tr.ws-theme--home-cat-filter-row').css ({'display': 'none'});
					}
				else
					{
						$('tr.ws-theme--home-cat-filter-row').css ({'display': ''});
					}
				/**/
				return;
			/**/
			}).change (homeStickyHandler);
		/**/
		var navLayoutHandler; /* This function will be used more than once. */
		$('select#ws-theme--nav-layout-model').each (navLayoutHandler = function()
			{
				var navLayoutSecDiv = 'div.ws-theme--nav-layout-section', pageNavLayoutTrs = 'tr.ws-theme--nav-page-layout-row', catNavLayoutTrs = 'tr.ws-theme--nav-cat-layout-row', customNavLayoutTrs = 'tr.ws-theme--nav-custom-layout-row', pageSectionDivs = 'div.ws-theme--page-nav-section-hr, div.ws-theme--page-nav-section', catSectionDivs = 'div.ws-theme--cat-nav-section-hr, div.ws-theme--cat-nav-section';
				/**/
				var pageSectionClone = $(pageSectionDivs).clone (true), catSectionClone = $(catSectionDivs).clone (true), r = $(pageSectionDivs).remove (), r = $(catSectionDivs).remove ();
				/**/
				if ($(this).val () === 'pages')
					{
						catSectionClone.insertAfter (navLayoutSecDiv), pageSectionClone.insertAfter (navLayoutSecDiv), $(catNavLayoutTrs).css ({'display': 'none'}), $(pageNavLayoutTrs).css ({'display': 'none'}), $(customNavLayoutTrs).css ({'display': 'none'}), $(catSectionDivs).css ({'display': 'none'}), $(pageSectionDivs).css ({'display': ''});
					}
				else if ($(this).val () === 'page_cat_combo')
					{
						catSectionClone.insertAfter (navLayoutSecDiv), pageSectionClone.insertAfter (navLayoutSecDiv), $(catNavLayoutTrs).css ({'display': ''}), $(pageNavLayoutTrs).css ({'display': 'none'}), $(customNavLayoutTrs).css ({'display': 'none'}), $(catSectionDivs).css ({'display': ''}), $(pageSectionDivs).css ({'display': ''});
					}
				else if ($(this).val () === 'categories')
					{
						pageSectionClone.insertAfter (navLayoutSecDiv), catSectionClone.insertAfter (navLayoutSecDiv), $(pageNavLayoutTrs).css ({'display': 'none'}), $(catNavLayoutTrs).css ({'display': 'none'}), $(customNavLayoutTrs).css ({'display': 'none'}), $(pageSectionDivs).css ({'display': 'none'}), $(catSectionDivs).css ({'display': ''});
					}
				else if ($(this).val () === 'cat_page_combo')
					{
						pageSectionClone.insertAfter (navLayoutSecDiv), catSectionClone.insertAfter (navLayoutSecDiv), $(pageNavLayoutTrs).css ({'display': ''}), $(catNavLayoutTrs).css ({'display': 'none'}), $(customNavLayoutTrs).css ({'display': 'none'}), $(pageSectionDivs).css ({'display': ''}), $(catSectionDivs).css ({'display': ''});
					}
				else if ($(this).val () === 'custom')
					{
						catSectionClone.insertAfter (navLayoutSecDiv), pageSectionClone.insertAfter (navLayoutSecDiv), $(catNavLayoutTrs).css ({'display': 'none'}), $(pageNavLayoutTrs).css ({'display': 'none'}), $(customNavLayoutTrs).css ({'display': ''}), $(catSectionDivs).css ({'display': 'none'}), $(pageSectionDivs).css ({'display': 'none'});
					}
				/**/
				return;
			/**/
			}).change (navLayoutHandler);
		/**/
		$('select#ws-theme--color').change (function()
			{
				var regexEscape = function(str, xtra)
					{
						str = String(str).replace (/([\\\/\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, '\\$1');
						/**/
						return (String(xtra)) ? str.replace (new RegExp('(' + String(xtra) + ')', 'g'), '\\$1') : str;
					};
				/**/
				var color = $.trim ($(this).val ()), xpcTemplateUrl = '<?php echo c_ws_theme__utils_strings::esc_js_sq ($GLOBALS["WS_THEME__"]["c"]["xpc_template_url"]); ?>';
				/**/
				$('form#ws-theme--options-form :input').each (function(i) /* Updates all input values to the proper color variation. */
					{
						$(this).val ($(this).val ().replace (new RegExp(regexEscape(xpcTemplateUrl) + '\\/colors\\/[a-z_0-9\\-]+\\/', 'g'), xpcTemplateUrl + '/colors/' + color + '/'));
					});
				/**/
				return;
			});
	});