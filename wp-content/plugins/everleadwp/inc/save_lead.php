<?php
	
	// ADD WORDPRESS
	
	define('WP_USE_THEMES', false);
	require('../../../../wp-blog-header.php');

	// Campaign ID
	$ID = $_POST['id'];
	
	// +1 For Total Leads
		
	global $wpdb;
	
	$table_db_name = $wpdb->prefix . "everleadwp_campaigns";
	$findstat = $wpdb->get_results("SELECT * FROM $table_db_name WHERE id = '$ID'", OBJECT);
	
	foreach($findstat as $findstat){}

	// Clean Info:

	$table_db_name = $wpdb->prefix . "everleadwp_users";

	$wpdb->insert($table_db_name, 
	array( 
	 'name' => $_POST['name'],
	 'email' => $_POST['email'],
	 'phone' => $_POST['phone'],
	 'website' => $_POST['website'],
	 'campaign' => $_POST['id'],
	 'created' => date('F j, Y')
	));
	
	$table_db_name = $wpdb->prefix . "everleadwp_campaigns";
	$wpdb->update($table_db_name, 
	array( 
	 'total_optins' => $findstat->total_optins + 1
	),
	  array( 'id' => $ID )
	);
		
?>