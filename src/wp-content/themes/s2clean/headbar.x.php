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
		if (apply_filters ("ws_theme__display_headbar", true))
			{
				do_action ("ws_theme__before_headbar");
				/*
				Open the headbar wrapper.
				*/
				echo '<div id="headbar-wrapper" class="headbar-wrapper">' . "\n";
				/*
				Open the headbar inner wrapper a.
				*/
				echo '<div id="headbar-inner-wrapper-a" class="headbar-inner-wrapper-a">' . "\n";
				/*
				Open the headbar inner wrapper b.
				*/
				echo '<div id="headbar-inner-wrapper-b" class="headbar-inner-wrapper-b">' . "\n";
				/*
				Open the headbar container.
				*/
				echo '<div id="headbar-container" class="headbar-container clearfix">' . "\n";
				/*
				Hook during headbar.
				*/
				do_action ("ws_theme__during_headbar");
				/*
				Close the headbar container.
				*/
				echo '</div>' . "\n";
				/*
				Close the headbar inner wrapper b.
				*/
				echo '</div>' . "\n";
				/*
				Close the headbar inner wrapper a.
				*/
				echo '</div>' . "\n";
				/*
				Close the headbar wrapper.
				*/
				echo '</div>' . "\n";
				/*
				Hook after headbar.
				*/
				do_action ("ws_theme__after_headbar");
			}
	}
?>