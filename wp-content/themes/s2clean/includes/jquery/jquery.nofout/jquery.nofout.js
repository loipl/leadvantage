/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
Associated with using @font-face in your CSS.
This extension tries to minimize FOUT; mostly in Firefox.
See: http://paulirish.com/2009/fighting-the-font-face-fout/

This extension, used in conjunction with a data URI in your CSS,
can almost completely get rid of FOUT ( Flash Of Unstyled Text ).
See: http://robert.accettura.com/blog/2009/07/03/optimizing-font-face-for-performance/

FOUT occurs in Gecko 1.9.1 / Firefox 3.5 when a font is NOT embedded in the CSS.
It also occurs if a page is viewed inside a frame, even if the font WAS embedded.
This extension cures the latter. It prevents FOUT inside of a frame. To get
rid of Firefox FOUT completely, you should also use the data URI method.

IE8 also has a bug when viewed inside a frame. The font contained within
the CSS file is not re-painted if the CSS file has been cached. This extension
also corrects this problem. However, in order for it to work, you must use
full URLs in your @font-face declarations. Do NOT use relative paths.

This extension assumes that your @font-face rules are in their own
dedicated CSS file, and that you've included that CSS file in the <head></head>.
You must name your CSS file ( font-faces.css ) for this to work. You can store
it anywhere you like on your server, so long as it's named: font-faces.css.
Also, IE8+ needs the full URL to an eot file in your @font-face declaration.

Example:

	<head>
		<link href="path/to/font-faces.css" type="text/css" rel="stylesheet" />
		<script type="text/javascript" src="path/to/jquery.nofout.js"></script>
	</head>
	
* And just like in the example above, you should then place your <script> tag,
	somewhere below your font-faces.css file, pointing to this extension.
	
* If you have no clue what to put in your font-faces.css file:
	Generate a kit here: http://www.fontsquirrel.com/fontface/generator
	- Make sure IE8+ gets the full URL to the location of your .eot file.

* Example font-faces.css file:

	@font-face
		{
			font-family: 'Primodo';
			src: url('http://www.example.com/fonts/Primodo.eot');
			src: local('Kingthings Gothique Regular'), local('KingthingsGothique'),
			url('http://www.example.com/fonts/Primodo.ttf') format('truetype');
		}

*/
(function($)
	{
		if (typeof $.noFOUT !== 'function')
			{
				($.noFOUT = function() /* Helps to prevent FOUT ( Flash Of Unstyled Text ). */
					{
						if (window != top) /* FOUT occurs in Gecko when the font is not embedded; & both IE/Gecko when inside a frame. */
							{
								if (typeof document.documentElement.style.MozTransform === 'string') /* Gecko 1.9.1+ / Firefox 3.5+ */
									{
										document.write ('<st' + 'yle type="text/css">body { visibility: hidden; }<\/st' + 'yle>');
										/**/
										$(document).ready (function() /* Return visibility when the doc is ready. */
											{
												$('body').css ('visibility', 'visible');
											});
									}
								else if (document.documentMode && typeof document.styleSheets === 'object') /* IE8+. */
									{
										for (var i = 0, link = null, href = ''; i < document.styleSheets.length; i++)
											{
												if ((link = document.styleSheets[i]) && typeof link.href === 'string' && (href = link.href))
													{
														if (href.match (/font-faces\.css/) && typeof link.cssText === 'string' && link.cssText)
															{
																document.write ('<st' + 'yle type="text/css">' + link.cssText + '<\/st' + 'yle>');
															}
													}
											}
									}
							}
					}) ();
			}
	/**/
	})(jQuery);