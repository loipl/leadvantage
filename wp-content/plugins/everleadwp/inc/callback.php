<?php

// Update Home

add_action('wp_ajax_everleadwp_create', 'everleadwp_create_callback');


function everleadwp_create_callback() {
		
	global $wpdb;
	$table_db_name = $wpdb->prefix . "everleadwp_campaigns";
	

	$wpdb->insert($table_db_name, 
	array( 
	 'title' => stripcslashes($_POST['title']),
	 'total_views' => '0',
	 'total_optins' => '0',
	 'created' => date('F j, Y')
	));
	
	$getID = $wpdb->insert_id;
	
	echo $getID;
	die();
	
}

// Edit Campaign

add_action('wp_ajax_everleadwp_edit', 'everleadwp_edit_callback');


function everleadwp_edit_callback() {
		
	global $wpdb;
	$table_db_name = $wpdb->prefix . "everleadwp_campaigns";
	
	$id = $_POST['id'];
	
	$wpdb->update($table_db_name, 
	array( 
	 'design1' => stripcslashes($_POST['design1']),
	 'design2' => stripcslashes($_POST['design2']),
	 'design3' => stripcslashes($_POST['design3']),
	 'design4' => stripcslashes($_POST['design4']),
	 'copy1' => stripcslashes($_POST['copy1']),
	 'copy2' => stripcslashes($_POST['copy2']),
	 'copy3' => stripcslashes($_POST['copy3']),
	 'copy4' => stripcslashes($_POST['copy4']),
	 'copy5' => stripcslashes($_POST['copy5']),
	 'video1' => stripcslashes($_POST['video1']),
	 'video2' => stripcslashes($_POST['video2']),
	 'video3' => stripcslashes($_POST['video3']),
	 'video4' => stripcslashes($_POST['video4']),
	 'video5' => stripcslashes($_POST['video5']),
	 'a1' => stripcslashes($_POST['a1']),
	 'a2' => stripcslashes($_POST['a2']),
	 'a3' => stripcslashes($_POST['a3']),
	 'a4' => stripcslashes($_POST['a4']),
	 'a5' => stripcslashes($_POST['a5']),
	 'a6' => stripcslashes($_POST['a6']),
	 'a7' => stripcslashes($_POST['a7']),
	 'a8' => stripcslashes($_POST['a8']),
	 'a9' => stripcslashes($_POST['a9']),
	 'a10' => stripcslashes($_POST['a10']),
	 'a11' => stripcslashes($_POST['a11']),
	 'extra' => stripcslashes($_POST['extra']),
	 'banner' => stripcslashes($_POST['banner']),
	 'banner_url' => stripcslashes($_POST['banner_url']),
	 'ar_code' => stripcslashes($_POST['ar_code']),
	 'ar_name' => stripcslashes($_POST['ar_name']),
	 'ar_email' => stripcslashes($_POST['ar_email']),
	 'ar_url' => stripcslashes($_POST['ar_url']),
	 'ar_hidden' => stripcslashes($_POST['ar_hidden'])
	),
	  array( 'id' => $id )
	);
	
}



?>