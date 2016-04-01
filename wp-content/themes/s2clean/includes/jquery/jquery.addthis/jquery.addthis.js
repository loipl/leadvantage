/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This function attaches the AddThis service to anchor tags
that have the share-save class. This is dependent upon:
//s7.addthis.com/js/250/addthis_widget.js

Automatic positioning is performed and this supports
multiple share-save buttons on a single page.

If you want to enable AddThis statistics, you'll need to
signup for an account at <http://www.addthis.com>, then change
the default anonymous username, to your AddThis.com username.
That being said, if your site uses Google Analytics ( recommended ),
you really won't need their statisitics, because Google Analytics
has been pre-integrated with the AddThis service, and it is enabled
automatically when it is present in your page.

Read this article for more info on Google Analytics:
http://www.addthis.com/help/google-analytics-integration
( this extension auto-enables data_ga_tracker )
*/
(function($)
	{
		if (typeof $.addThis !== 'function')
			{
				var username = 'xa-4b537663748d4b79'; /* This default anonymous username ( xa-4b537663748d4b79 ) can be used on any website. */
				/**/
				document.write ('<scr' + 'ipt type="text/javascript" src="//s7.addthis.com/js/250/addthis_widget.js#username=' + username + '"><\/scr' + 'ipt>');
				document.write ('<st' + 'yle type="text/css">div#atffc { width: 0; height: 0; overflow: hidden; }<\/st' + 'yle>'); /* Hide flash tracking pixel. */
				/**/
				$.addThis = function(options)
					{
						var o, defaults = {hover: false, offsetY: true, regex2Domain: {match: null, title: '', description: ''}};
						/**/
						var metaDesc = $.trim ($ ('meta[name="description"]').attr ('content'));
						/**/
						o = options = $.extend (true, {}, defaults, options);
						/*
						Attach AddThis to anchor tags.
						*/
						if (typeof addthis === 'object' && typeof addthis.button === 'function')
							{
								$ ('a.share-save').each (function() /* Find all anchors with the share-save class. */
									{
										$ (this).css ('cursor', 'pointer'); /* Force anchors to appear clickable. */
										/**/
										var widgetWidth = 242, widgetHeight = 208; /* Hard-coded in because there is no way to detect these. */
										var imageWidth = parseInt ($ ('img', this).css ('width')), imageHeight = parseInt ($ ('img', this).css ('height'));
										/**/
										var offsetTop = (!isNaN (imageHeight) && imageHeight && o.offsetY) ? imageHeight + ( (widgetHeight - imageHeight) / 2) : 0;
										var offsetLeft = (!isNaN (imageWidth) && imageWidth) ? (widgetWidth - imageWidth) / 2 : 0;
										offsetLeft = (offsetLeft > 0) ? -offsetLeft : Math.abs (offsetLeft);
										offsetTop = (offsetTop > 0) ? -offsetTop : Math.abs (offsetTop);
										/*
										The config option: data_use_flash, is turned off because it causes layout problems when a flash pixel is displayed. */
										if (o.regex2Domain.match && o.regex2Domain.title && document.URL.match (o.regex2Domain.match)) /* If the current URL matches regex2Domain.match, we force the page being shared to the main domain. */
											addthis.button (this, {data_ga_tracker: ( ( typeof window.pageTracker === 'object') ? pageTracker : null), ui_click: (o.hover ? false : true), ui_offset_top: offsetTop, ui_offset_left: offsetLeft}, {url: 'http://' + document.domain + '/', title: o.regex2Domain.title, description: o.regex2Domain.description});
										else /* The default settings are designed to use the document.URL, document.title, and to pull the summary content from the meta description tag used in the current page; if there is one. */
											addthis.button (this, {data_ga_tracker: ( ( typeof window.pageTracker === 'object') ? pageTracker : null), ui_click: (o.hover ? false : true), ui_offset_top: offsetTop, ui_offset_left: offsetLeft}, {url: document.URL, title: document.title, description: metaDesc});
									});
							}
					};
			}
	})(jQuery);