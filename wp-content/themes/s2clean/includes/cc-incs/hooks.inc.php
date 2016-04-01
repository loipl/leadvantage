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
Add actions/filters for various methods.
*/
add_action ("ws_theme__during_header_after_markup", "c_ws_theme__c_login_box::login_box_and_controls");
add_filter ("ws_theme__during_header_during_markup_display_nav", "c_ws_theme__c_navigation::navigation_dynamics");
add_filter ("ws_theme__during_sidebar_during_markup_display_dynamics", "c_ws_theme__c_sidebars::sidebar_dynamics");
add_filter ("ws_theme__during_footbar_during_markup_display_dynamics", "c_ws_theme__c_footbars::footbar_dynamics");
?>