/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
Basic calling syntax: jQuery('input').watermark();
Defaults to taking the title from the field's title attribute.

You can also pass an options object with the following parameters:

	1. The {source} parameter can be one of these:
				
		* Use 'title' to get the in-field title from the field's title attribute.
			Note: This is the default value when it is not passed in.
			Usage: jQuery('input').watermark({source:'title'});
				
		* Use 'label' to get the in-field label from the inner text of the field's label.
			Note: The label must be attached to the field with for="[the field's ID]".
			Usage: jQuery('input').watermark({source:'label'});
				
		* A function which takes one parameter, the input field, and returns whatever source value it likes.
			Usage: function myFunction(obj){ return jQuery(obj).attr('title'); }
					jQuery('input').watermark({source:myFunction});

	2. The {watermarkedClass} parameter:
		
		A class that will be applied to the input field when it contains the watermark, and removed when it contains user input.
			Note: This defaults to the string value: 'watermarked' when it is not passed in. The watermarked class
			contains the following properties by default, which you can override using CSS if you like:
				
				Default CSS styles:
					.watermarked { color: #999999; font-style: italic; }
*/
(function($)
	{
		if (typeof $.fn.watermark !== 'function')
			{
				document.write ('<st' + 'yle type="text/css">.watermarked { color: #999999; font-style: italic; }<\/st' + 'yle>');
				/**/
				$.fn.watermark = function(options) /* The default CSS can be overwritten using the CSS !important statement. */
					{
						var $this = this, o, sources, remove, defaults = {source: 'title', watermarkedClass: 'watermarked'};
						/**/
						o = options = $.extend (true, {}, defaults, options);
						/**/
						sources = { /* Build source functions. */
						/**/
						title: function(input) /* Title attribute. */
							{
								return $.trim ($ (input).attr ('title'));
							},
						/**/
						label: function(input) /* Label for the field. */
							{
								return $ ('label[for="' + input.id + '"]').text ();
							}};
						/**/
						remove = function() /* Removes watermarks. */
							{
								$this.each (function() /* Go through each one. */
									{
										var $this = $ (this), value = $.trim ($this.val ());
										/**/
										if (value === '' || value === $this.data ('watermark'))
											{
												$this.val (''), $this.removeClass (o.watermarkedClass);
											}
									})
							};
						/**/
						$this.parents ('form').submit (remove), $ (window).unload (remove);
						/**/
						return $this.each (function() /* Configure each element for watermarking. */
							{
								var $this = $ (this), value = $.trim ($this.val ()), watermark, sourceFunction; /* Initialize vars. */
								/**/
								if (typeof (sourceFunction = o.source) === 'function' || typeof (sourceFunction = sources[o.source]) === 'function')
									{
										if ($.trim (watermark = sourceFunction (this)) !== '') /* If we have a watermark. */
											{
												$this.data ('watermark', watermark.replace (/[\r\n\t]+/g, ''));
												/**/
												if (value === '') /* Add watermark initially, in some cases. */
													{
														$this.val ($this.data ('watermark')), $this.addClass (o.watermarkedClass);
													}
												/**/
												$this.focus (function() /* Remove watermark on focus. */
													{
														var $this = $ (this), value = $.trim ($this.val ());
														/**/
														if (value === '' || value === $this.data ('watermark'))
															{
																$this.val (''), $this.removeClass (o.watermarkedClass);
															}
													});
												/**/
												$this.blur (function() /* Add watermark back in, on blur, in some cases. */
													{
														var $this = $ (this), value = $.trim ($this.val ());
														/**/
														if (value === '' || value === $this.data ('watermark'))
															{
																$this.val ($this.data ('watermark')), $this.addClass (o.watermarkedClass);
															}
													});
											}
									}
								else
									return false;
							});
					};
			}
	})(jQuery);