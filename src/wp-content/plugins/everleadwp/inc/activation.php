<?php

// Run when plugin is activated
function everleadwp_installer(){

   global $wpdb;

   // Set Database For Campaigns

   $table_name = $wpdb->prefix . "everleadwp_campaigns";
   
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
	   
	   ID INTEGER(100) UNSIGNED AUTO_INCREMENT,
	   
	   title varchar(250),
	   
	   created varchar(30),
	   
	   total_optins varchar(20),
	   
	   total_views varchar(20),
	   
	   design1 varchar(50),
	   design2 varchar(250),
	   design3 varchar(50),
	   design4 varchar(250),
	   design5 varchar(50),
	   design6 varchar(250),

	   copy1 varchar(550),
	   copy2 varchar(550),
	   copy3 varchar(550),
	   copy4 varchar(550),
	   copy5 varchar(550),

	   video1 varchar(250),
	   video2 varchar(250),
	   video3 varchar(250),
	   video4 LONGTEXT,
	   video5 varchar(250),

	   a1 varchar(250),
	   a2 varchar(250),
	   a3 varchar(250),
	   a4 LONGTEXT,
	   a5 varchar(250),
	   a6 varchar(250),
	   a7 varchar(250),
	   a8 varchar(250),
	   a9 varchar(250),
	   a10 varchar(250),
	   a11 varchar(250),

	   extra LONGTEXT,

	   banner varchar(50),
	   banner_url varchar(250),
	   	   
	   ar_code LONGTEXT,
	   
	   ar_email varchar(50),
	   
	   ar_name varchar(50),
	   
	   ar_url varchar(250),
	   
	   ar_hidden LONGTEXT,
	   	   	
	   UNIQUE KEY id (id)
	   
	 )ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      
      dbDelta($sql);
      
    }
    
       // Set Database For Leads
    
       $table_name = $wpdb->prefix . "everleadwp_users";
       
       if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
          
          $sql = "CREATE TABLE " . $table_name . " (
    	   
    	   ID INTEGER(100) UNSIGNED AUTO_INCREMENT,
    	   
    	   name varchar(250),
    	   
    	   email varchar(250),

    	   phone varchar(250),

    	   website varchar(250),
    	   
    	   created varchar(40),
    	   
    	   campaign varchar(100),
    	   
    	   UNIQUE KEY id (id)
    	   
    	 )ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;";
    
          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
          
          dbDelta($sql);
          
        }

    
} 

?>