<?php

// SETTING UP THE PLUGIN MENU...

add_action('admin_menu', 'everleadwp_admin_menu');


function everleadwp_admin_menu() 
{
	
	add_menu_page('everleadwp', 'EverLead', 'manage_options', 'everlead-dashboard', 'everlead_dashboard', '../wp-content/plugins/everleadwp/images/icon.png');	
}

?>