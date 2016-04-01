<?php  

	global $wpdb;
	$table_db_name = $wpdb->prefix . "everleadwp_campaigns";
	
	$ID = $_GET['lead'];
	
	$results = $wpdb->get_results("SELECT * FROM $table_db_name WHERE id = '$ID'", OBJECT);
	
	foreach($results as $results) {}

?>

<div id="editdep">

	<div class="well">
		
			<div id="depTitle">
				
				<h1><?php echo $results->title; ?></h1>
				<p class="subtext2" ><b>created:</b> <?php echo $results->created; ?></p>
				
			</div>
			
			<div class="depStat2">
				
				<h1 style="color:#6d6d6d;"><?php echo $results->total_optins; ?></h1>
				<p class="subtext">total optins</p>
				
			</div>
			
			<div class="depStat1">
				
				<h1 style="color:#6d6d6d;"><?php echo $results->total_views; ?></h1>
				<p class="subtext">total views</p>
				
			</div>
	
			<br clear="all" >
	
	</div>
		
	<table width="840" >
	
	<thead>
	  <tr>
	    <th align="left" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " >Name:</th>
	    <th align="left" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " >E-Mail:</th>
	    <th align="left" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " >Website:</th>
	    <th align="left" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " >Phone:</th>
	    <th align="left" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " >Date Signed Up:</th>
	  </tr>
	</thead>
	
	<tbody>	
	<?php 
	
		$table_db_name = $wpdb->prefix . "everleadwp_users";
		$results = $wpdb->get_results("SELECT * FROM $table_db_name WHERE campaign = '$ID'", OBJECT);
		
		foreach($results as $results) {
		?>
		
			
				<tr style=" border-bottom: 1px solid #333; " >
					<td width="168" class="subtext3" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " ><?php echo $results->name; ?></td>
					<td width="168" class="subtext3" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " width="33%" ><?php echo $results->email; ?></td>
					<td width="168" class="subtext3" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " width="33%" ><?php echo $results->website; ?></td>
					<td width="168" class="subtext3" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " width="33%" ><?php echo $results->phone; ?></td>
					<td width="168" class="subtext3" style=" border-bottom: 1px solid #616161; padding-top: 15px; padding-bottom: 15px; " width="33%"><?php echo $results->created; ?></td>
				</tr>
		
		<?php
		}
	?>
	
	</tbody>
	
	</table>
	
	<a href="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/inc/csv.php?id=<?php echo $_GET['lead']; ?>" class="uibutton large special" target="_blank" style="margin-top: 15px;" >Export To CSV</a>
		

</div>