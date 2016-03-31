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
		do_action ("ws_theme__before_lfbcomp");
		/*
		Open the lfbcomp wrapper.
		*/
		echo '<div id="lfbcomp-wrapper" class="lfbcomp-wrapper">' . "\n";
		/*
		Open the lfbcomp inner wrapper a.
		*/
		echo '<div id="lfbcomp-inner-wrapper-a" class="lfbcomp-inner-wrapper-a">' . "\n";
		/*
		Open the lfbcomp inner wrapper b.
		*/
		echo '<div id="lfbcomp-inner-wrapper-b" class="lfbcomp-inner-wrapper-b">' . "\n";
		/*
		Open the lfbcomp container.
		*/
		echo '<div id="lfbcomp-container" class="lfbcomp-container clearfix">' . "\n";
		/*
		Hook before lfbcomp markup.
		*/
		do_action ("ws_theme__during_lfbcomp_before_markup");
		/*
		Build and evaluate the lfbcomp left section.
		*/
		echo '<div id="lfbcomp-left" class="lfbcomp-left">' . "\n";
		if (c_ws_theme__utils_conds::is_multisite_farm ())
			echo $GLOBALS["WS_THEME__"]["o"]["lfbcomp_left_code"] . "\n";
		else /* ^ Prevent PHP code eval() on Multisite Farm installs. */
			eval ("?>" . $GLOBALS["WS_THEME__"]["o"]["lfbcomp_left_code"] . "\n");
		echo '</div>' . "\n";
		/*
		Build and evaluate the lfbcomp right section.
		*/
		echo '<div id="lfbcomp-right" class="lfbcomp-right">' . "\n";
		if (c_ws_theme__utils_conds::is_multisite_farm ())
			echo $GLOBALS["WS_THEME__"]["o"]["lfbcomp_right_code"] . "\n";
		else /* ^ Prevent PHP code eval() on Multisite Farm installs. */
			eval ("?>" . $GLOBALS["WS_THEME__"]["o"]["lfbcomp_right_code"] . "\n");
		echo '</div>' . "\n";
		/*
		Hook after lfbcomp markup.
		*/
		do_action ("ws_theme__during_lfbcomp_after_markup");
		/*
		Close the lfbcomp container.
		*/
		echo '</div>' . "\n";
		/*
		Close the lfbcomp inner wrapper b.
		*/
		echo '</div>' . "\n";
		/*
		Close the lfbcomp inner wrapper a.
		*/
		echo '</div>' . "\n";
		/*
		Close the lfbcomp wrapper.
		*/
		echo '</div>' . "\n";
		/*
		Hook after lfbcomp.
		*/
		do_action ("ws_theme__after_lfbcomp");
	}
?>