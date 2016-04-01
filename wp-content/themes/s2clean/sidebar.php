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
		if (apply_filters ("ws_theme__display_sidebar", true))
			{
				do_action ("ws_theme__before_sidebar");
				/*
				Open the sidebar wrapper.
				*/
				echo '<div id="sidebar-wrapper" class="sidebar-wrapper">' . "\n";
				/*
				Open the sidebar inner wrapper a.
				*/
				echo '<div id="sidebar-inner-wrapper-a" class="sidebar-inner-wrapper-a">' . "\n";
				/*
				Open the sidebar inner wrapper b.
				*/
				echo '<div id="sidebar-inner-wrapper-b" class="sidebar-inner-wrapper-b">' . "\n";
				/*
				Open the sidebar container.
				*/
				echo '<div id="sidebar-container" class="sidebar-container clearfix">' . "\n";
				/*
				Hook before sidebar markup.
				*/
				do_action ("ws_theme__during_sidebar_before_markup");
				/*
				Give filters a chance to exclude the default bars.
				*/
				if (apply_filters ("ws_theme__during_sidebar_during_markup_display_dynamics", true))
					{
						do_action ("ws_theme__during_sidebar_during_markup_before_dynamics");
						/**/
						echo '<ul id="sidebar" class="sidebar">' . "\n";
						/**/
						if (!dynamic_sidebar ("Default Sidebar"))
							{
								echo '<li></li>' . "\n";
							}
						/**/
						echo '</ul>' . "\n";
						/**/
						do_action ("ws_theme__during_sidebar_during_markup_after_dynamics");
					}
				/*
				Hook after sidebar markup.
				*/
				do_action ("ws_theme__during_sidebar_after_markup");
				/*
				Close the sidebar container.
				*/
				echo '</div>' . "\n";
				/*
				Close the sidebar inner wrapper b.
				*/
				echo '</div>' . "\n";
				/*
				Close the sidebar inner wrapper a.
				*/
				echo '</div>' . "\n";
				/*
				Close the sidebar wrapper.
				*/
				echo '</div>' . "\n";
				/*
				Hook after sidebar.
				*/
				do_action ("ws_theme__after_sidebar");
			}
	}
?>