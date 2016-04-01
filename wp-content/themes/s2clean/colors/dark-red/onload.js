/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
jQuery(window).load (function()
	{
		/*
		Reference to jQuery.
		*/
		var $ = jQuery;
		/*
		Adjust the height of the sidebar.
		*/
		$('div#sidebar-container').css ('min-height', ($('div#body-content-container').height () - 40) + 'px');
		/*
		Do not allow short-sheet pages to mess up the layout.
		This just moves the wp-footer and it's siblings down to fill the page.
		*/
		if ($('body').outerHeight (true) < $(window).height ())
			$('div#wpf-wrapper').css ({'margin-top': (parseInt($('div#wpf-wrapper').css ('margin-top')) + $(window).height () - $('body').outerHeight (true)) + 'px'});
	});