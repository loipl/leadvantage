/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
jQuery(document).ready (function($)
	{
		/*
		This scripting is for: header.php.
		*/
		$('div#body-header-login-box-controls > a#login-box-opener').click (function()
			{
				var $this = $ (this), $loginBox = $ ('div#body-header-login-box');
				/**/
				if ($loginBox.css ('display') === 'none')
					{
						$this.addClass ('current');
						$loginBox.slideDown ('fast');
					}
				else /* Otherwise hide & remove class. */
					{
						$this.removeClass ('current');
						$loginBox.slideUp ('fast');
					}
				/**/
				return false;
			});
		/*
		This scripting is for: table rows.
		*/
		$('table tr:odd').addClass ('odd'), $ ('table tr:even').addClass ('even');
		/*
		This scripting is for: index.php.
		*/
		$('div#index-posts > div.index-post:last-child').css ({'margin-bottom': '0'});
		/*
		This scripting is for: fullpage.x.php.
		*/
		$('form#contact-form input, form#contact-form textarea').watermark ();
		/*
		This scripting is for: sidebar.php.
		*/
		$('ul#sidebar > li.widget:last-child').css ({'margin-bottom': '0'});
		$('ul#sidebar > li.widget.widget_calendar > div > table > tfoot > tr > td:last-child').css ({'text-align': 'right'});
		$('ul#sidebar > li.widget.widget_search > form > div > input[type="text"]').watermark ({source: function()
			{
				return 'Enter search terms...';
			}});
		/*
		This scripting is for: footbar.x.php.
		*/
		$('ul#footbar > li.widget:nth-child(3n)').css ({'margin-right': '0'});
		$('ul#footbar > li.widget:nth-child(3n)').after ('<div class="clear"></div>');
		$('ul#footbar > li.widget:nth-child(1n+4)').css ({'margin-top': '24px'});
		$('ul#footbar > li.widget.widget_search > form > div > input[type="text"]').watermark ({source: function()
			{
				return 'Enter search terms...';
			}});
		/*
		This scripting is for: comments.php ( gravatar specific ).
		*/
		$('div#respond-form-gravatar').click (function()
			{
				$.winOpen ({href: 'http://www.gravatar.com/'});
			});
		$('div#respond-form-email > input').blur (gravatarCheck);
		$('div#respond-form-comment > textarea').focus (gravatarCheck).blur (gravatarCheck);
		/*
		This scripting is for: comments.php ( other ).
		*/
		$('div#respond-form input, div#respond-form textarea').watermark ();
		$('a#respond-form-comment-allowed-tags-link').click (function()
			{
				$('div#respond-form-comment-allowed-tags').slideToggle ('fast');
				/**/
				return false;
			});
		/*
		Handle easy column layouts, and assign margins.
		*/
		$.easyCols ({margin3rds: 15, margin4ths: 16});
		/*
		Share/Save via AddThis.com.
		*/
		$.addThis ();
	});