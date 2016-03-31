/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This extension adds a rendering engine class
to the HTML tag for the document.

They are as follows:
<html class="msie|mozilla|webkit|opera">

In addition, these conditionals will also be available when they are true:
<html class="msie msie-lt-8 msie-lt-9 msie-lt-10">
<html class="mozilla mozilla-lt-1-9-1 mozilla-lt-1-9-2 mozilla-lt-1-9-3">
<html class="webkit webkit-lt-530 webkit-lt-540 webkit-lt-550">
<html class="opera opera-lt-10 opera-lt-10-5 opera-lt-11">
*/
(function($)
	{
		if (typeof $.userAgs !== 'function')
			{
				($.userAgs = function()
					{
						/*
						Prior to version 1.4, jQuery did not check browsers reliably.
						More specificially, there were some issues with IE version detection.
						So, here we check to see which version of jQuery is running, and if
						we are running < 1.4, this routine will update the $.browser object.
						*/
						if ($.fn.jquery < 1.4) /* Less than 1.4, otherwise no. */
							{
								$.browser = {}; /* Reset $.browser. */
								/**/
								var $1_4_browser = (function() /* v1.4 update. */
									{
										var ua = navigator.userAgent.toLowerCase ();
										var match = /(webkit)[ \/]([\w.]+)/.exec (ua) || /(opera)(?:.*version)?[ \/]([\w.]+)/.exec (ua) || /(msie) ([\w.]+)/.exec (ua) || !/compatible/.test (ua) && /(mozilla)(?:.*? rv:([\w.]+))?/.exec (ua) || [];
										return {browser: match[1] || '', version: match[2] || '0'};
									}) ();
								/**/
								if ($1_4_browser.browser)
									$.browser[$1_4_browser.browser] = true;
								/**/
								$.browser.version = $1_4_browser.version;
							}
						/*
						Version comparison like: version_compare(),
						only there is no support for dev, a, alpha, etc.
						
						If either string contains non-numerics, this
						function will fallback safely to a false return.
						Allowed chars include: [0-9] and decimals.
						
						Strings are right-trimmed for bad chars, just in case a version ends with letters.
						Strings are also trimmed with $.trim() to remove leading/trailing whitespace.
						*/
						var versionLessThan = function(v, l) /* v = version, l = less-than comparison. */
							{
								if (typeof v === 'string' && typeof l === 'string')
									{
										v = $.trim (v.replace (/[^0-9\.]+$/g, '').replace (/\.+$/g, ''));
										l = $.trim (l.replace (/[^0-9\.]+$/g, '').replace (/\.+$/g, ''));
										/**/
										if (v.match (/[0-9]+/) && v.match (/^[0-9\.]+$/))
											{
												if (l.match (/[0-9]+/) && l.match (/^[0-9\.]+$/))
													{
														var version = v.split ('.'), lt = l.split ('.');
														/**/
														for (var i = 0; i < version.length; i++)
															{
																if (isNaN(version[i] = Number(version[i])))
																	{
																		return false;
																	}
																else if (lt.length < i + 1)
																	{
																		return true;
																	}
																else if (isNaN(lt[i] = Number(lt[i])))
																	{
																		return false;
																	}
																else if (version[i] < lt[i])
																	{
																		return true;
																	}
																else if (lt.length < i + 2)
																	{
																		return false;
																	}
															}
													}
											}
									}
								/**/
								return false;
							};
						/*
						Append classes to the html tag.
						For browser detection through CSS.
						*/
						var $html = $('html'), v = $.browser.version;
						/**/
						if ($.browser.msie) /* IE® = Idiots Evolving. */
							{
								$html.addClass ('msie');
								/**/
								if (versionLessThan(v, '8'))
									$html.addClass ('msie-lt-8');
								/**/
								if (versionLessThan(v, '9'))
									$html.addClass ('msie-lt-9');
								/**/
								if (versionLessThan(v, '10'))
									$html.addClass ('msie-lt-10');
							}
						else if ($.browser.webkit) /* Includes Chrome® / Safari®. */
							{
								$html.addClass ('webkit');
								/**/
								if (versionLessThan(v, '530'))
									$html.addClass ('webkit-lt-530');
								/**/
								if (versionLessThan(v, '540'))
									$html.addClass ('webkit-lt-540');
								/**/
								if (versionLessThan(v, '550'))
									$html.addClass ('webkit-lt-550');
							}
						else if ($.browser.mozilla) /* Includes Firefox®. */
							{
								$html.addClass ('mozilla');
								/**/
								if (versionLessThan(v, '1.9.1'))
									$html.addClass ('mozilla-lt-1-9-1');
								/**/
								if (versionLessThan(v, '1.9.2'))
									$html.addClass ('mozilla-lt-1-9-2');
								/**/
								if (versionLessThan(v, '1.9.3'))
									$html.addClass ('mozilla-lt-1-9-3');
							}
						else if ($.browser.opera) /* The Opera browser. */
							{
								$html.addClass ('opera');
								/**/
								if (versionLessThan(v, '10'))
									$html.addClass ('opera-lt-10');
								/**/
								if (versionLessThan(v, '10.5'))
									$html.addClass ('opera-lt-10-5');
								/**/
								if (versionLessThan(v, '11'))
									$html.addClass ('opera-lt-11');
							}
					}) ();
			}
	/**/
	})(jQuery);