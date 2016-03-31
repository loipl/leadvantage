/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This function preloads an array of images, or a single image.
Example: jQuery.preload(['URL to image', 'URL to image']);
Example: jQuery.preload('URL to image');
*/
(function($)
	{
		if (typeof $.preload !== 'function')
			{
				$.preload = function(parameter)
					{
						if (typeof parameter === 'object')
							{
								for (var i = 0, preloads = new Array (); i < parameter.length; i++)
									{
										preloads[i] = new Image (), preloads[i].src = parameter[i];
									}
							}
						else if (typeof parameter === 'string')
							{
								var preload = new Image ();
								preload.src = parameter;
							}
					};
			}
	/**/
	})(jQuery);