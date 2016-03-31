<?php

// ********* META DATA BOX ********************///

add_action( 'add_meta_boxes', 'everleadwp_meta_box_add' );

function everleadwp_meta_box_add(){
	add_meta_box( 'everleadwp-id', 'Link EverLead Page', 'everleadwp_meta_box_cb', 'page', 'side', 'high' );
}

function everleadwp_meta_box_cb(){ 
	
	   global $post;  
	   $values = get_post_custom( $post->ID );  
	   $selected = isset( $values['everleadwp_meta_box_select'] ) ?
	   esc_attr( $values['everleadwp_meta_box_select'] ) : '';
	    
	   wp_nonce_field( 'everleadwp_meta_box_nonce', 'everleadwp_box_nonce' );
	   
	   $everleadwpSelected = $values['everleadwp_meta_box_select'];
	      
	   $everleadwpCurrentSelected = $everleadwpSelected[0];
	      
	?>
	<h4 style=" margin-bottom: 0px; margin-top: 15px;">Select A Landing Page</h4>
	<span style="font-size: 11px;" >This page will be replaced with this landing page...</span>
	<br>   
	<select name="everleadwp_meta_box_select" id="everleadwp_meta_box_select" style="margin-top: 10px; width: 250px;">
	
	<option <?php if($everleadwpCurrentSelected == "0"){ echo "selected='selected'"; } ?> value="0">NONE</option>
	
	
		  	<?php 
		   	
		   	global $wpdb;
		   	$table_db_name = $wpdb->prefix . "everleadwp_campaigns";
		   	$templates = $wpdb->get_results("SELECT * FROM $table_db_name",ARRAY_A);
		   	$templates = array_reverse($templates );
		   	
		   	foreach($templates as $template){
		   	
		   		$name = stripslashes($template['title']);
		   		$id = stripslashes($template['ID']);
		   		$selectedBox = "";
		   		if($everleadwpCurrentSelected == $id){ $selectedBox = "selected='selected'"; }
		   		
		   		echo "<option $selectedBox value='$id'>$name</option>";
		   	
		   	}
		   	
		   	?>
	
	</select>
	
	<?php
}

// Save Settings

add_action( 'save_post', 'everleadwp_meta_box_save' );

function everleadwp_meta_box_save( $post_id ){
	// Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	// if our nonce isn't there, or we can't verify it, bail
	if( !isset( $_POST['everleadwp_box_nonce'] ) || !wp_verify_nonce( $_POST['everleadwp_box_nonce'], 'everleadwp_meta_box_nonce' ) ) return;

	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) ) return;
	
	// now we can actually save the data 
	  
	// Make sure your data is set before trying to save it  

	if( isset( $_POST['everleadwp_meta_box_select'] ) )  
	        update_post_meta( $post_id, 'everleadwp_meta_box_select', esc_attr( $_POST['everleadwp_meta_box_select'] ) );  
	                         
}

// Get post settings ::

add_action('template_redirect', 'everleadwp_checkpost');

function everleadwp_checkpost(){
	
	// get POST ID:
	global $post;  
	$values = get_post_custom( $post->ID );  
	$selected = isset( $values['everleadwp_meta_box_select'] );	
	$everleadwpSelected = $values['everleadwp_meta_box_select'];
	
	$everleadwpCurrentSelected = $everleadwpSelected[0];
	
	
	if($everleadwpCurrentSelected == "0" || $everleadwpCurrentSelected == ""){
		// do nothing...
	} else {
				
			$full_path = WP_PLUGIN_URL.'/'.str_replace(basename( FILE),"",plugin_basename(FILE));
			
			$client = urlencode($everleadwpCurrentSelected);
			
			$CheckTY = "ty";
			$postID = $post->ID;
	
			
					
			$url = $full_path. "everleadwp/lp/index.php?id=$client&p=$postID";
			
			//$url_feed = file_get_contents("$url", true);
			$url_feed = get_url_content($url);
			
			if ( $url_feed === false )
			{
			   
			   // Try cURL to open the file ::
			   
			   function url_get_contents ($Url) {
			       if (!function_exists('curl_init')){ 
			           die('CURL is not installed!');
			       }
			       $ch = curl_init();
			       curl_setopt($ch, CURLOPT_URL, $Url);
			       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			       $output = curl_exec($ch);
			       curl_close($ch);
			       return $output;
			   }

			    echo "<title>";
				echo get_the_title($post->ID);
				echo "</title>";
			   
			   echo url_get_contents($url);
			   
			} else {

				echo "<title>";
				echo get_the_title($post->ID);
				echo "</title>";
				
				//echo file_get_contents("$url", true);
				echo get_url_content($url);
			
			}
			
			die();
			
		}
		
	

}

function get_url_content($url){
	
	$response = '';
	
	if ( function_exists('curl_init') ) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);						
		curl_close($ch);	
		
	}else{
					
		$response = @file_get_contents($url);			
		if (!$response) {
			$fp = fsockopen($url, 80);
			
			if($fp) {
				fwrite($fp, "POST /checking HTTP/1.1\r\n");
				fwrite($fp, "Host: exitsplash.com\r\n");
				fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
				fwrite($fp, "Connection: close\r\n");
				fwrite($fp, "\r\n");
				
				while (!feof($fp)) {
					$reply .= fgets($fp,1024);
				}
				
				fclose($fp);
				$response = substr($reply, (strpos($reply, "\r\n\r\n")+4));
										
			}
		}									
	}
	
	return $response;
}

?>