/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This function opens a link in a new window according to your options.
Here is an example winOpen usage: jQuery('a#linkid').winOpen({width:300,height:300});

Options can be passed as shown in the example above, or they could also be set using
the rel attribute of your links, like this: rel="winopen;options={width:300,height:300}".

In addition, this extension will automatically attach click events for winOpen() to any
link: a[rel^="winopen"], saving you the trouble of setting it up yourself.
*/
(function($)
	{
		if (typeof $.winOpen !== 'function')
			{
				$.winOpen = $.fn.winOpen = function(options)
					{
						var $this, o, defaults = {top: 0, left: 0, name: '_winopen', href: '', width: ((screen.width >= 1280) ? 1280 : screen.width), height: ((screen.height >= 768) ? 768 : screen.height), resizable: 1, toolbar: 0, scrollbars: 1, status: 0, center: 1, location: 0, menubar: 0};
						/**/
						o = options = ( typeof winOpen === 'object') ? $.extend (true, {}, defaults, winOpen, options) : $.extend (true, {}, defaults, options);
						/**/
						$this = (this instanceof jQuery && this[0].nodeType === 1) ? $ (this[0]) : null;
						/**/
						var relOptions = function(rel) /* Parses options from the rel attribute. */
							{
								rel = ( typeof rel === 'string') ? rel : '';
								/**/
								var relRegex = /^winopen/i; /* Looking for this relationship. */
								var relOptions = {}, prop, prms, p, pl, m, opts, op, ol; /* Misc vars. */
								var paramsSeparator = ';'; /* Separates the parameters, ex: relRegex;options={} */
								var optionsRegex = /(options)( ?)(\=)( ?)(\{)(.+)(\})/i; /* Regex for parsing out the options. */
								var optionsSeparator = ','; /* Separates the options, ex: option:value,option:value,option:value */
								var optionRegex = /([a-z_]+)( ?)(\:)( ?)('?)([^'$]*)('?)/i; /* Regex for parsing an option. */
								/**/
								if (rel.match (relRegex) && ((prms = rel.split (paramsSeparator)).length))
									for (p = 0, pl = prms.length; p < pl; p++) /* Go through params, look for options={}. */
										if ((m = prms[p].match (optionsRegex)) && ((opts = m[6].split (optionsSeparator)).length))
											for (op = 0, ol = opts.length; op < ol; op++) /* Go through options and get values. */
												if (m = opts[op].match (optionRegex)) /* Be sure to trim these up. */
													relOptions[$.trim (m[1])] = $.trim (m[6]);
								/**/
								for (prop in relOptions) /* Typecasting. */
									if (typeof relOptions[prop] === 'string')
										if (relOptions[prop].match (/^(true|false)$/))
											relOptions[prop] = (relOptions[prop] === 'true') ? true : false;
										else if (relOptions[prop].match (/^([0-9\.]+)$/))
											relOptions[prop] = Number (relOptions[prop]);
								/**/
								return relOptions; /* Object with option values. */
							};
						/**/
						var rel = ($this) ? $.trim ($this.attr ('rel')) : ''; /* Get rel attribute. */
						/**/
						o = options = $.extend (true, {}, options, relOptions (rel)); /* rel options. */
						/**/
						if (o.center == 1) /* Adjust top and left if we are centering the window. */
							o.top = (screen.height - o.height) / 2, o.left = (screen.width - o.width) / 2;
						/**/
						var winopen, parameters = 'location=' + o.location + ',menubar=' + o.menubar + ',height=' + o.height + ',width=' + o.width + ',toolbar=' + o.toolbar + ',scrollbars=' + o.scrollbars + ',status=' + o.status + ',resizable=' + o.resizable + ',left=' + o.left + ',screenX=' + o.left + ',top=' + o.top + ',screenY=' + o.top;
						/**/
						if (!(winopen = window.open (((o.href) ? o.href : (($this && $this.attr ('href')) ? $this.attr ('href') : 'about:blank')), o.name, parameters)))
							alert('Ot oh! It looks like you have a popup blocker preventing the window from opening. Please turn off all popup blockers and try again.');
						else /* Get focus. */
							winopen.focus ();
						/**/
						return winopen; /* Return the result of the winopen. */
					};
				/*
				This extension will automatically attach click events for winOpen() to any
				link: a[rel^="winopen"], saving you the trouble of setting it up yourself.
				*/
				$(document).ready (function($)
					{
						$('a[rel^="winopen"]').bind ('click', function(e)
							{
								$(this).winOpen ();
								return false;
							});
					});
			}
	})(jQuery);