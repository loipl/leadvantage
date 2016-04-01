<?php
/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/*
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		do_action ("ws_theme__before_wpf");
		/*
		Open the wpf wrapper.
		*/
		echo '<div id="wpf-wrapper" class="wpf-wrapper">' . "\n";
		/*
		Open the wpf inner wrapper a.
		*/
		echo '<div id="wpf-inner-wrapper-a" class="wpf-inner-wrapper-a">' . "\n";
		/*
		Open the wpf inner wrapper b.
		*/
		echo '<div id="wpf-inner-wrapper-b" class="wpf-inner-wrapper-b">' . "\n";
		/*
		Open the wpf container.
		*/
		echo '<div id="wpf-container" class="wpf-container clearfix">' . "\n";
		/*
		Hook before wpf markup.
		*/
		do_action ("ws_theme__during_wpf_before_markup");
		/*
		Call wpf function.
		*/
		wp_footer (); /* For hooks. */
		/*
		Evaluate the global tracking code.
		*/
		if (c_ws_theme__utils_conds::is_multisite_farm ())
			echo $GLOBALS["WS_THEME__"]["o"]["global_tracking_code"] . "\n";
		else /* ^ Prevent PHP code eval() on Multisite Farm installs. */
			eval ("?>" . $GLOBALS["WS_THEME__"]["o"]["global_tracking_code"] . "\n");
		/*
		Hook after wpf markup.
		*/
		do_action ("ws_theme__during_wpf_after_markup");
		/*
		Close the wpf container.
		*/
		echo '</div>' . "\n";
		/*
		Close the wpf inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the wpf inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the wpf wrapper.
		*/
		echo '</div>' . "\n";
		/**/
		do_action ("ws_theme__after_wpf");
	}
?>