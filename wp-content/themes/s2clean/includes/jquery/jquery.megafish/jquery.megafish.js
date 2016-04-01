/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This function handles the megafish menu system.
Ex: $('ul.megafish').megafish();

Megafish also requires the hoverIntent() extension:
http://cherne.net/brian/resources/jquery.hoverIntent.html
You need to install hoverIntent first before using Megafish.
*/
(function($)
	{
		if (typeof $.fn.megafish !== 'function' && typeof $.fn.hoverIntent === 'function')
			{
				$.fn.megafish = function(options) /* Call this function inline, instead of on $(document).ready() to prevent arrow flickers. */
					{
						var $this = this, o, defaults = {parentClass: 'parent', currentClass: 'current', depressedClass: 'depressed', dividerClass: 'divider', arrowClass: 'arrow', downArrow: '&#9660;', rightArrow: '&#9658;', animShow: 'fadeIn', animHide: 'fadeOut', animSpeed: 'fast', hoverSensitivity: 5, hoverInterval: 200, hoverTimeout: 500, openOnClick: 'arrows-only', switchOnClick: false};
						/**/
						var ie = ($.browser.msie) ? true : false, ielt8 = (ie && $.browser.version < 8) ? true : false, opera = ($.browser.opera) ? true : false;
						/**/
						o = options = $.extend (true, {}, defaults, options); /* Merge options recursively with defaults. */
						/**/
						return $this.each (function() /* This provides built-in support for multiple menus, all on the same page. */
							{
								var $tlItems, liDivider = '<li class="' + o.dividerClass + '"></li>'; /* List item with divider class. */
								/**/
								($tlItems = $('> li', this)).each (function(index) /* Add a divider after each top-level parent. */
									{
										if (index + 1 < $tlItems.length) /* Skip the very last parent; no trailing dividers. */
											$(this).after (liDivider); /* Insert an li divider after each top-level item. */
									});
								/**/
								var insSpace = '<ins style="display:none;">&nbsp;</ins>'; /* Allows line-height to be computed correctly. */
								/**/
								$('> li:has(ul)', this).each (function() /* Append the downArrow and prepend the rightArrow for proper z-indexing. */
									{
										$(this).append ('<ins class="' + o.arrowClass + '">' + insSpace + o.downArrow + '</ins>'); /* Append. */
									});
								/**/
								$('ul li:has(ul)', this).each (function() /* Prepended here, so that z-indexing does not become an issue. */
									{
										$(this).prepend ('<ins class="' + o.arrowClass + '">' + insSpace + o.rightArrow + '</ins>');
									});
								/**/
								if (o.openOnClick) /* If we are using the onClick method ( this method is the default ). */
									{
										$('li:has(ul)', this).addClass (o.parentClass).each (function() /* Add the parent class and attach click events. */
											{
												$(((o.openOnClick === 'arrows-only') ? '> a[href="#"]' : '> a') + ', > ins.' + o.arrowClass, this).click (function()
													{
														var $this = $(this); /* Creates/stores a reference to the jQuery object ( optimizes this routine ). */
														/**/
														if ($('> ul', $this.parent ()).css ('display') === 'none') /* If not visible, we need to show it now. */
															{
																$this.parent ().addClass (o.depressedClass); /* Add the depressed class to the parent item. */
																/**/
																if (ie || opera || !o.animShow) /* If we are in an IE or Opera browser, or if no animation was given. */
																	$('> ul', $this.parent ()).show (); /* Animation causes problems in IE/Opera. Just use show(). */
																else
																	$('> ul', $this.parent ())[o.animShow](o.animSpeed); /* Use animation to display. */
															}
														else /* It is already visible, we need to close. This effectively becomes a toggler for hide/show. */
															{
																$this.parent ().removeClass (o.depressedClass); /* Remove the depressed class from the parent item. */
																/**/
																if (ie || opera || !o.animHide) /* If we are in an IE or Opera browser, or if no animation was given. */
																	$('> ul', $this.parent ()).hide (); /* Animation causes problems in IE/Opera. Just use hide(). */
																else
																	$('> ul', $this.parent ())[o.animHide](o.animSpeed); /* Use animation to hide. */
															}
														/**/
														return false; /* Do not go anywhere. Return false here to prevent a page transition. */
													});
												/*
												The sensitivity/interval is VERY high here, so the children will hide on the out state.
												*/
												$(this).hoverIntent ({sensitivity: 100, interval: 100, timeout: o.hoverTimeout, over: function()
													{
														return; /* Just an empty function here. We are not doing anything on hover. */
													},/**/
												out: function() /* This handles the out state by removing the depressed class and animating. */
													{
														$(this).removeClass (o.depressedClass); /* Remove the depressed class from the parent item. */
														/**/
														if (ie || opera || !o.animHide) /* If we are in an IE or Opera browser, or if no animation was given. */
															$('> ul', this).hide (); /* Here we can just hide the child items using the hide() function. */
														else
															$('> ul', this)[o.animHide](o.animSpeed); /* Use animation to hide. */
													}});
											});
									}
								/**/
								else /* Else we use the hover state instead of interactive clicking. Interactive clicking tends to be more user-friendly though. */
									{
										$('li:has(ul)', this).addClass (o.parentClass). /* Here we add the parent class to items containing a ul tag. */
										/**/
										hoverIntent ({sensitivity: o.hoverSensitivity, interval: o.hoverInterval, timeout: o.hoverTimeout,
										/**/
										over: function() /* This handles the over state by adding the depressed class and animating display. */
											{
												$(this).addClass (o.depressedClass); /* Add the depressed class to the parent list item. */
												/**/
												if (ie || opera || !o.animShow) /* If we are in an IE or Opera browser, or if no animation was given. */
													$('> ul', this).show (); /* Animation causes problems in IE/Opera. Just use show(). */
												else
													$('> ul', this)[o.animShow](o.animSpeed); /* Use animation to display. */
											},/**/
										out: function() /* This handles the out state by removing the depressed class and animating. */
											{
												$(this).removeClass (o.depressedClass); /* Remove the depressed class from the parent item. */
												/**/
												if (ie || opera || !o.animHide) /* If we are in an IE or Opera browser, or if no animation was given. */
													$('> ul', this).hide (); /* Animation causes problems in IE/Opera. Just use hide(). */
												else
													$('> ul', this)[o.animHide](o.animSpeed); /* Use animation to hide. */
											}});
									}
								/**/
								if (o.switchOnClick) /* Instantly switch before the page loads? Useful when using ajax for menu interaction. */
									{
										$('li > a', this).click (function() /* Move the currentClass to the one that is clicked on. Very cool. */
											{
												$('li > a', $this).each (function() /* This will include all menus, no just the current one. */
													{
														$(this).parent ().removeClass (o.currentClass); /* Remove the current class from all items. */
													});
												$(this).parent ().addClass (o.currentClass); /* Add the current class to this clicked item. */
											});
									}
								/**/
								if (ielt8) /* We need to fix a problem in IE < 8. What a suprise. z-indexing is completely broken in IE7. */
									{
										var z = 1000; /* z-index bug. We should force Microsoft to remove IE and pre-install Firefox®. */
										/**/
										$('li', this).each (function() /* http://www.vancelucas.com/blog/fixing-ie7-z-index-issues-with-jquery/ */
											{
												$(this).css ('z-index', z--); /* Give z-index a hierarchy that actually makes sense. */
											});
									}
							});
					};
			}
	})(jQuery);