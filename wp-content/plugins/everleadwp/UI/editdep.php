<?php  

	global $wpdb;
	$table_db_name = $wpdb->prefix . "everleadwp_campaigns";
	
	$ID = $_GET['id'];
	
	$results = $wpdb->get_results("SELECT * FROM $table_db_name WHERE id = '$ID'", OBJECT);
	
	foreach($results as $results) {}

?>

<script type="text/javascript">
$(document).ready(function() {

	// SET BG Style On Load

	<?php

	if($results->design1 == NULL){
		// Do nothing - NULL
	} else if($results->design1 == "bg9"){
		// Custom BG
		?>

		$('.bgDesign').removeClass("special");

		$('#bgTest').removeClass();

		$("#bgTest").addClass("<?php echo $results->design1; ?>");

		$("#<?php echo $results->design1; ?>").addClass("special");

		$("#bgCustom").show();
		$("#bgTest").hide();

		<?php

	} else {
		// Set BG Style
		?>

		$('.bgDesign').removeClass("special");

		$('#bgTest').removeClass();

		$("#bgTest").addClass("<?php echo $results->design1; ?>");

		$("#<?php echo $results->design1; ?>").addClass("special");

		<?php
	}

	?>

	// SET TOP BAR Style On Load

	<?php

	if($results->design3 == NULL){
		// Do nothing - NULL
	} else if($results->design3 == "top8"){
		// Custom Top bar
		?>

		$('.topBar').removeClass("special");

		$("#<?php echo $results->design3; ?>").addClass("special");

		$("#topCustom").show();

		<?php

	} else {
		// Set Top Bar Style
		?>

		$('.topBar').removeClass("special");

		$("#<?php echo $results->design3; ?>").addClass("special");

		<?php
	}

	?>

	// SET VIDEO HEADER Style On Load

	<?php

	if($results->video2 == NULL){
		// Do nothing - NULL
	} else if($results->video2 == "video9"){
		// Custom Top bar
		?>

		$('.videoHeader').removeClass("special");

		$("#<?php echo $results->video2; ?>").addClass("special");

		$("#videoCustom").show();

		<?php

	} else {
		// Set Top Bar Style
		?>

		$('.videoHeader').removeClass("special");

		$("#<?php echo $results->video2; ?>").addClass("special");

		<?php
	}

	?>

	// SET OPTIN HEADER Style On Load

	<?php

	if($results->a2 == NULL){
		// Do nothing - NULL
	} else if($results->a2 == "ar8"){
		// Custom Top bar
		?>

		$('.optinHeader').removeClass("special");

		$("#<?php echo $results->a2; ?>").addClass("special");

		$("#optinCustom").show();

		<?php

	} else {
		// Set Top Bar Style
		?>

		$('.optinHeader').removeClass("special");

		$("#<?php echo $results->a2; ?>").addClass("special");

		<?php
	}

	?>

	// SET OPTIN BTN Style On Load

	<?php

	if($results->a10 == NULL){
		// Do nothing - NULL
	} else if($results->a10 == "btn7"){
		// Custom Top bar
		?>

		$('.btnHeader').removeClass("special");

		$("#<?php echo $results->a10; ?>").addClass("special");

		$("#btnCustom").show();

		<?php

	} else {
		// Set Top Bar Style
		?>

		$('.btnHeader').removeClass("special");

		$("#<?php echo $results->a10; ?>").addClass("special");

		<?php
	}

	?>

	// SET BANNER Style On Load

	<?php

	if($results->banner == NULL){
		// Do nothing - NULL
	} else if($results->banner == "ban9"){
		// Custom Top bar
		?>

		$('.banner').removeClass("special");

		$("#<?php echo $results->banner; ?>").addClass("special");

		$("#bannerCustom").show();

		<?php

	} else {
		// Set Top Bar Style
		?>

		$('.banner').removeClass("special");

		$("#<?php echo $results->banner; ?>").addClass("special");

		<?php
	}

	?>

});
</script>

<div id="editdep">

	<div class="well">
		
			<div id="depTitle">
				
				<h1><?php echo $results->title; ?></h1>
				<p class="subtext"><b>created:</b> <?php echo $results->created; ?></p>
				
			</div>
			
			<div class="depStat2">
				
				<h1 style="color:#6d6d6d;"  ><?php echo $results->total_optins; ?></h1>
				<p class="subtext">total optins</p>
				
			</div>
	
			<div class="depStat1">
				
				<h1 style="color:#6d6d6d;" ><?php echo $results->total_views; ?></h1>
				<p class="subtext" >total views</p>
				
			</div>
	
			<br clear="all" >
		
		</div>
			
		<div class="wellX">
	
			<div class="editArea startArea actvieArea" editID="design" >
			<img id="design_img" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/design.png" alt="">
			</div>

			<div class="editArea editCopy" editID="copy" >
			<img id="copy_img" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/copy.png" alt="">			
			</div>

			<div class="editArea editVideo" editID="video" >
			<img id="video_img" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/video.png" alt="">			
			</div>

			<div class="editArea editAR" editID="ar" >
			<img id="ar_img" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/ar.png" alt="">
			</div>

			<div class="editArea editExtra" editID="extra" >
			<img id="extra_img" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/extra.png" alt="">			
			</div>

			<br clear="all" >

		</div>
		
		
		<div id="design" class="editSections" >
			<?php include("design.php"); ?>
		</div>

		<div id="copy" class="editSections" style="display:none;" >
			<?php include("copy.php"); ?>
		</div>

		<div id="video" class="editSections" style="display:none;" >
			<?php include("video.php"); ?>
		</div>

		<div id="ar" class="editSections" style="display:none;" >
			<?php include("ar.php"); ?>
		</div>

		<div id="extra" class="editSections" style="display:none;" >
			<?php include("extra.php"); ?>
		</div>
	

	<span id="editCAMPAIGN"  class="uibutton large special">Update Campaign Settings</span>
	
	<span style="margin-left: 583px;"><a href="#" class="uibutton large" id="deleteCampaign"><b>Delete</b></a></span>

</div>