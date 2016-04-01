/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This function attaches event listeners for special fading effects.
Attached to div, img, a elements that have one or more of these classes:

oo-hoverfade-i = Element fades into view on hover.
oo-hoverfade-o = Element fades out of view on hover.
oo-clickfade-o = Element fades out when clicked on.
*/
(function($)
	{
		if (typeof $.ooFade !== 'function')
			{
				$.ooFade = function(options)
					{
						if (!$.browser.msie) /* Internet explorer has alpha transparency issues. */
							{
								var o, defaults = {clickFadeOutAmount: 0.3, hoverFadeInOpacity: 1.0, hoverFadeOutOpacity: 0.5};
								/**/
								o = options = $.extend (true, {}, defaults, options);
								/*
								Handle click fade outlines.
								*/
								$('div.oo-clickfade-o, img.oo-clickfade-o, a.oo-clickfade-o').each (function()
									{
										$(this).css ('outline', 0);
										$(this).parent ('a').css ('outline', 0);
										$('a', this).css ('outline', 0);
									});
								/*
								Handle click fades out.
								*/
								$('div.oo-clickfade-o, img.oo-clickfade-o, a.oo-clickfade-o').click (function()
									{
										if (!$(this).attr ('oo-clickfaded-o'))
											{
												$(this).attr ('oo-clickfaded-o', 'true');
												$(this).fadeTo ('fast', o.clickFadeOutAmount);
											}
									});
								/*
								Handle hover fades in.
								*/
								$('div.oo-hoverfade-i, img.oo-hoverfade-i, a.oo-hoverfade-i').hover (function()
									{
										if (!$(this).attr ('oo-clickfaded-o'))
											$(this).fadeTo ('fast', o.hoverFadeInOpacity);
									}, function()
									{
										if (!$(this).attr ('oo-clickfaded-o'))
											$(this).fadeTo ('fast', o.hoverFadeOutOpacity);
									});
								/*
								Handle hover fades out.
								*/
								$('div.oo-hoverfade-o, img.oo-hoverfade-o, a.oo-hoverfade-o').hover (function()
									{
										if (!$(this).attr ('oo-clickfaded-o'))
											$(this).fadeTo ('fast', o.hoverFadeOutOpacity);
									}, function()
									{
										if (!$(this).attr ('oo-clickfaded-o'))
											$(this).fadeTo ('fast', o.hoverFadeInOpacity);
									});
							}
					};
			}
	/**/
	})(jQuery);