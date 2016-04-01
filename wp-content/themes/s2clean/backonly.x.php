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
Template Name: Background Only Layout ( clean )
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/*
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		include_once TEMPLATEPATH . "/head.x.php";
		/*
		Hook before backonly.
		*/
		do_action ("ws_theme__before_backonly");
		/*
		Open the backonly wrapper.
		*/
		echo '<div id="backonly-wrapper" class="backonly-wrapper">' . "\n";
		/*
		Open the backonly inner wrapper a.
		*/
		echo '<div id="backonly-inner-wrapper-a" class="backonly-inner-wrapper-a">' . "\n";
		/*
		Open the backonly inner wrapper b.
		*/
		echo '<div id="backonly-inner-wrapper-b" class="backonly-inner-wrapper-b">' . "\n";
		/*
		Open the backonly container.
		*/
		echo '<div id="backonly-container" class="backonly-container clearfix">' . "\n";
		/*
		Check if we have this backonly in the archive.
		*/
		if (have_posts () && the_post () !== "nill" && is_object ($post) && apply_filters ("ws_theme__during_backonly_display_content_section", true))
			{
				echo '<div id="backonly-content" class="backonly-content clearfix">' . "\n";
				/**/
				do_action ("ws_theme__during_backonly_before_content");
				/**/
				echo '<!-- custom-content-before -->' . "\n";
				/**/
				if (apply_filters ("ws_theme__during_backonly_display_content", true))
					echo c_ws_theme__utilities::get ("the_content") . "\n";
				/**/
				echo '<!-- custom-content -->' . "\n";
				/**/
				echo '<!-- custom-content-after -->' . "\n";
				/**/
				do_action ("ws_theme__during_backonly_after_content");
				/**/
				echo '</div>' . "\n";
			}
		/*
		Close the backonly container.
		*/
		echo '</div>' . "\n";
		/*
		Close the backonly inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the backonly inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the backonly wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after backonly.
		*/
		do_action ("ws_theme__after_backonly");
		/*
		Get the wp footer.
		*/
		include_once TEMPLATEPATH . "/wpf.x.php";
		/*
		Get the foot.
		*/
		include_once TEMPLATEPATH . "/foot.x.php";
	}
?>