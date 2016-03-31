<?php
/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
The wp_footer() function is called in wpf.x.php instead of footer.php.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/*
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		do_action ("ws_theme__during_body_content_container_after_markup");
		/*
		Close the body content container.
		*/
		echo '</div>' . "\n";
		/*
		Close the body content inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the body content inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the body content wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after body content wrapper.
		*/
		do_action ("ws_theme__after_body_content_wrapper");
		/*
		Hook before footer wrapper.
		*/
		do_action ("ws_theme__before_footer");
		/*
		Open the body footer wrapper.
		*/
		echo '<div id="body-footer-wrapper" class="body-footer-wrapper">' . "\n";
		/*
		Open the body footer inner wrapper a.
		*/
		echo '<div id="body-footer-inner-wrapper-a" class="body-footer-inner-wrapper-a">' . "\n";
		/*
		Open the body footer inner wrapper b.
		*/
		echo '<div id="body-footer-inner-wrapper-b" class="body-footer-inner-wrapper-b">' . "\n";
		/*
		Open the body footer container.
		*/
		echo '<div id="body-footer-container" class="body-footer-container clearfix">' . "\n";
		/*
		Hook during footer.
		*/
		do_action ("ws_theme__during_footer");
		/*
		Close the body footer container.
		*/
		echo '</div>' . "\n";
		/*
		Close the body footer inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the body footer inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the body footer wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after footer wrapper.
		*/
		do_action ("ws_theme__after_footer");
		/*
		Close the body container.
		*/
		echo '</div>' . "\n";
		/*
		Close the body inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the body inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the body wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after body wrapper.
		*/
		do_action ("ws_theme__after_body_wrapper");
		/*
		Get the wp footer.
		*/
		include_once TEMPLATEPATH . "/wpf.x.php";
		/*
		Get the footbar.
		*/
		include_once TEMPLATEPATH . "/footbar.x.php";
		/*
		Get the lower footbar companion.
		*/
		include_once TEMPLATEPATH . "/lfbcomp.x.php";
		/*
		Get the foot.
		*/
		include_once TEMPLATEPATH . "/foot.x.php";
	}
?>