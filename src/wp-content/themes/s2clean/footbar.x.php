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
		if (apply_filters ("ws_theme__display_footbar", true))
			{
				do_action ("ws_theme__before_footbar");
				/*
				Open the footbar wrapper.
				*/
				echo '<div id="footbar-wrapper" class="footbar-wrapper">' . "\n";
				/*
				Open the footbar inner wrapper a.
				*/
				echo '<div id="footbar-inner-wrapper-a" class="footbar-inner-wrapper-a">' . "\n";
				/*
				Open the footbar inner wrapper b.
				*/
				echo '<div id="footbar-inner-wrapper-b" class="footbar-inner-wrapper-b">' . "\n";
				/*
				Open the footbar container.
				*/
				echo '<div id="footbar-container" class="footbar-container clearfix">' . "\n";
				/*
				Hook before footbar markup.
				*/
				do_action ("ws_theme__during_footbar_before_markup");
				/*
				Give filters a chance to exclude the default bars.
				*/
				if (apply_filters ("ws_theme__during_footbar_during_markup_display_dynamics", true))
					{
						do_action ("ws_theme__during_footbar_during_markup_before_dynamics");
						/**/
						echo '<ul id="footbar" class="footbar">' . "\n";
						/**/
						if (!dynamic_sidebar ("Default Footbar"))
							{
								echo '<li></li>' . "\n";
							}
						/**/
						echo '</ul>' . "\n";
						/**/
						do_action ("ws_theme__during_footbar_during_markup_after_dynamics");
					}
				/*
				Hook after footbar markup.
				*/
				do_action ("ws_theme__during_footbar_after_markup");
				/*
				Separate/clear the footbar appendage.
				*/
				echo '<div class="clear-margins"></div>' . "\n";
				/*
				Hook before footbar appendage.
				*/
				do_action ("ws_theme__during_footbar_before_appendage");
				/*
				Build and evaluate the footbar appendage code.
				*/
				echo '<div id="footbar-appendage-code" class="footbar-appendage-code clearfix">' . "\n";
				if (c_ws_theme__utils_conds::is_multisite_farm ()) /* Multisite Farms. */
					echo $GLOBALS["WS_THEME__"]["o"]["footbar_appendage_code"] . "\n";
				else /* ^ Prevent PHP code eval() on Multisite Farm installs. */
					eval ("?>" . $GLOBALS["WS_THEME__"]["o"]["footbar_appendage_code"] . "\n");
				echo '</div>' . "\n";
				/*
				Hook after footbar appendage.
				*/
				do_action ("ws_theme__during_footbar_after_appendage");
				/*
				Close the footbar container.
				*/
				echo '</div>' . "\n";
				/*
				Close the footbar inner wrapper b.
				*/
				echo '</div>' . "\n";
				/*
				Close the footbar inner wrapper a.
				*/
				echo '</div>' . "\n";
				/*
				Close the footbar wrapper.
				*/
				echo '</div>' . "\n";
				/*
				Hook after footbar.
				*/
				do_action ("ws_theme__after_footbar");
			}
	}
?>