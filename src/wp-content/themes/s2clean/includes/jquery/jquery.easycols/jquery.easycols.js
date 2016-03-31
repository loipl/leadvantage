/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
This function sets pixel-perfect widths for all easy columns.

For columns with 3rds:
	- margin3rds must always be a multiple of 3 ( defaults to 15 ).
	- The parent container width must always be divisible by 2,3,4.
		Examples: 960, 924, 912, 888, 612, 588.

For columns with 4ths:
	- margin4ths must always be a multiple of 4 ( defaults to 16 ).
	- The parent container width must always be divisible by 2,3,4.
		Examples: 960, 924, 912, 888, 612, 588.
*/
(function($)
	{
		if (typeof $.easyCols !== 'function')
			{
				$.easyCols = function(options)
					{
						var o, defaults = {margin3rds: 15, margin4ths: 16};
						/**/
						o = options = $.extend (true, {}, defaults, options);
						/*
						Parse and configure all 1-3rd, 2-3rds column widths.
						*/
						$('div.with-1-3rd-column-width, div.with-2-3rds-column-width').each (function()
							{
								var $this = $(this), padding = parseInt($this.css ('padding-left')) + parseInt($this.css ('padding-right')), containerWidth = $this.parent ().width ();
								/**/
								var widthEach = Math.floor (containerWidth - (o.margin3rds * 2) - ((padding) ? (padding * 3) : 0)) / 3;
								/**/
								if ($this.hasClass ('with-2-3rds-column-width'))
									widthEach = (widthEach * 2) + (o.margin3rds * 1) + (padding * 1);
								/**/
								$this.css ('width', widthEach + 'px');
							});
						/*
						Parse and configure all 1-4th, 2-4ths, 3-4ths column widths.
						*/
						$('div.with-1-4th-column-width, div.with-2-4ths-column-width, div.with-3-4ths-column-width').each (function()
							{
								var $this = $(this), padding = parseInt($this.css ('padding-left')) + parseInt($this.css ('padding-right')), containerWidth = $this.parent ().width ();
								/**/
								var widthEach = Math.floor (containerWidth - (o.margin4ths * 3) - ((padding) ? (padding * 4) : 0)) / 4;
								/**/
								if ($this.hasClass ('with-2-4ths-column-width'))
									widthEach = (widthEach * 2) + (o.margin4ths * 1) + (padding * 1);
								/**/
								else if ($this.hasClass ('with-3-4ths-column-width'))
									widthEach = (widthEach * 3) + (o.margin4ths * 2) + (padding * 2);
								/**/
								$this.css ('width', widthEach + 'px');
							});
					};
			}
	/**/
	})(jQuery);