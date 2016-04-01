/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
(function($)
	{
		/*
		Handle gravatar checking.
		*/
		gravatarCheck = function()
			{
				var email = $.trim ($('div#respond-form-email > input').val ());
				/**/
				if (email && email.match (/@/) && email.match (/\./))
					{
						$.get ('<?php echo $u; ?>/md5.x.php?str=' + encodeURIComponent(email), function(md5)
							{
								var img = new Image (), div = 'div#respond-form-gravatar';
								var get = '<?php echo $i; ?>/respond-form-gravatar-get.png';
								var windowFocusedOnError = $(div).attr ('data-window-focused-on-error') ? true : false;
								var gravatar = 'http://www.gravatar.com/avatar/%%email%%?size=96&default=404&no-cache=' + new Date ().getTime ();
								/**/
								$(img) /* Creates a new script-based image and tries to load it. */
								/**/
								.load (function() /* If successful, use this gravatar image on-site. */
									{
										$(div).html ('<img src="' + gravatar.replace (/%%email%%/, md5) + '" />');
									})/**/
								.error (function() /* Error, they need to get a gravatar. */
									{
										$(div).html ('<img src="' + get + '" />'); /* No Gravatar? */
										(!windowFocusedOnError) ? $(window).focus (gravatarCheck) : null;
										$(div).attr ('data-window-focused-on-error', 'true');
									})/**/
								.attr ('src', gravatar.replace (/%%email%%/, md5));
							});
					}
			};
	/**/
	})(jQuery);