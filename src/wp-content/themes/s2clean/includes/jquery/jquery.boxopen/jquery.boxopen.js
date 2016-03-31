/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This extension is dependent on ColorBox for jQuery, by Jack Moore.
This extension passes all of its options over to the ColorBox extension.
Go here for a full list of options: <http://colorpowered.com/colorbox/>

The boxOpen function opens content in a ColorBox window according to your options.
Here's an example of boxOpen usage: jQuery('a#linkid').boxOpen({width:300,height:300});

Options can be passed as shown in the example above, or they could also be set using
the rel attribute of your links, like this: rel="boxopen;options={width:300,height:300}".

This extension will automatically attach click events for boxOpen() to any
link: a[rel^="boxopen"], saving you the trouble of setting it up yourself.

In addition, this extension will automatically attach itself to WordPress®
image galleries that link to their files: [gallery link="file"].
*/
(function($)
	{
		if (typeof $.boxOpen !== 'function' && typeof $.fn.colorbox === 'function')
			{
				$.boxOpen = $.fn.boxOpen = function(options)
					{
						var $this, o, i, rels = [], defaults = {maxWidth: '95%', maxHeight: '95%', transition: 'fade'};
						/**/
						o = options = ( typeof boxOpen === 'object') ? $.extend (true, {}, defaults, boxOpen, options) : $.extend (true, {}, defaults, options);
						/**/
						$this = (this instanceof jQuery) ? this : null; /* Is this a jQuery object, or the function itself? */
						/**/
						if (!$this) /* If we are not dealing with an array of elements, return now. */
							return $.fn.colorbox (o); /* This issues a direct call w/options. */
						/**/
						var relOptions = function(rel) /* Parses options from the rel attribute. */
							{
								rel = ( typeof rel === 'string') ? rel : '';
								/**/
								var relRegex = /^boxopen/i; /* Looking for this relationship. */
								var relOptions = {}, prop, prms, p, pl, m, opts, op, ol; /* Misc vars. */
								var paramsSeparator = ';'; /* Separates the parameters, ex: relRegex;options={} */
								var optionsRegex = /(options)( ?)(\=)( ?)(\{)(.+)(\})/i; /* Regex for parsing out the options. */
								var optionsSeparator = ','; /* Separates the options, ex: option:value,option:value,option:value */
								var optionRegex = /([a-z_]+)( ?)(\:)( ?)('?)([^'$]*)('?)/i; /* Regex for parsing an option. */
								/**/
								if (rel.match (relRegex) && ( (prms = rel.split (paramsSeparator)).length))
									for (p = 0, pl = prms.length; p < pl; p++) /* Go through params, look for options={}. */
										if ((m = prms[p].match (optionsRegex)) && ( (opts = m[6].split (optionsSeparator)).length))
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
						$this.each (function() /* Unique relationship groups. */
							{
								var rel = $.trim ($ (this).attr ('rel'));
								if (rel && $.inArray (rel, rels) === -1)
									rels.push (rel);
							});
						/**/
						for (i = 0; i < rels.length; i++) /* Handle relationship groups. */
							{
								$this.filter (function() /* Filter by rel attribute. */
									{
										return this.rel === rels[i];
									})/**/
								.colorbox ($.extend (true, {}, o, relOptions (rels[i])));
							}
						/**/
						return $this; /* Return the jQuery object for chaining. */
					};
				/*
				This extension will automatically attach itself to WordPress® image
				galleries that link to their files: [gallery link="file"].
				*/
				$ (document).ready (function($) /* Handles Wordpress® galleries. */
					{
						$ ('div.gallery').each (function() /* Wordpress® assigns this class internally. */
							{
								var $this = $ (this), gallery = $.trim ($this.attr ('id')), $anchors = $ ('a', this);
								/**/
								var s = ($this.parent ().is ('div.gallery-slideshow')) ? ',slideshow:true' : '';
								/**/
								if (gallery && $anchors.length && typeof $anchors[0].href === 'string')
									if ($anchors[0].href.match (/\.(svg|png|gif|jpg|jpe|jpeg|bmp)$/i))
										$anchors.attr ('rel', 'boxopen-' + gallery + ';options={photo:true' + s + '}');
							});
					});
				/*
				This extension will automatically attach click events for boxOpen() to any
				link: a[rel^="boxopen"], saving you the trouble of setting it up yourself.
				*/
				$ (document).ready (function($) /* colorbox() attaches click events. */
					{
						$ ('a[rel^="boxopen"]').boxOpen ();
					});
			}
	})(jQuery);