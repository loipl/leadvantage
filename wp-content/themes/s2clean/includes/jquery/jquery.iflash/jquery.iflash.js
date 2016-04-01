/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This function inserts embed code for flash files.
Ex: jQuery.iFlash({src: 'flash.swf', width: '300', height: '300'});
*/
(function($)
	{
		if (typeof $.iFlash !== 'function')
			{
				$.iFlash = function(options)
					{
						var code, defaults = {id: 'iflash-' + Math.round (0 + Math.random () * (10000000 - 0)), src: '', width: '100%', height: '400', bgcolor: '', wmode: 'transparent', flashvars: '', quality: 'high', scale: '', menu: '', allowfullscreen: 'true', swliveconnect: 'true', allowscriptaccess: 'always', _return: false};
						/**/
						options = $.extend (true, {}, defaults, options), code = '<embed type="application/x-shockwave-flash" id="' + options.id + '" src="' + options.src + '" width="' + options.width + '" height="' + options.height + '" bgcolor="' + options.bgcolor + '" wmode="' + options.wmode + '" flashvars="' + options.flashvars + '" quality="' + options.quality + '" scale="' + options.scale + '" menu="' + options.menu + '" allowfullscreen="' + options.allowfullscreen + '" swliveconnect="' + options.swliveconnect + '" allowscriptaccess="' + options.allowscriptaccess + '" pluginspage="//www.macromedia.com/go/getflashplayer"></embed>';
						/**/
						if (options.src && options.width && options.height)
							{
								if (options._return)
									return code;
								/**/
								document.write (code);
							}
						/**/
						else if (options._return)
							return '';
					};
			}
	})(jQuery);