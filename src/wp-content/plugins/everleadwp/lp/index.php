<?php

        // ADD WORDPRESS
        
        define('WP_USE_THEMES', false);
        require('../../../../wp-blog-header.php');

        // Load Up All Key Departments
        
        global $wpdb;
        $table_db_name = $wpdb->prefix . "everleadwp_campaigns";
        
        $id = $_GET['id'];
        
        $data = $wpdb->get_results("SELECT * FROM $table_db_name WHERE id = '$id'", OBJECT);
        
        foreach($data as $results) {}
        
        // +1 For Views
        $views = $results->total_views + 1;
        $wpdb->update($table_db_name, 
        array( 
         'total_views' => $views
        ),
          array( 'id' => $id )
        );
        
    $postID = "";

    if (isset($_GET['p'])) {
        $postID = $_GET['p'];
    } else {
        $postID = "22342342342423423423423423423423423424324235235235252";
    }
   
?>

<html>
<head>
	<title>ConsultPro Squeeze Page Design</title>
	<link href="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/css/main.css" rel="stylesheet" type="text/css" />


        <?php include("bg.php"); ?>

        <?php include("topbar.php"); ?>

        <?php include("logo.php"); ?>

        <?php include("video.php"); ?>

        <?php include("ar.php"); ?>

        <?php include("banner.php"); ?>

    <script type="text/javascript" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/inc/jquery.js"></script>

    <?php echo $results->extra; ?>


	<script type="text/javascript">
	$(document).ready(function() {
	
        // Set video IFRAME
    
        $("iframe").height(315);
        $("iframe").width(560);
        
        // Set video EMBED
        
        $("embed").height(315);
        $("embed").width(560);
        
        // Set video OBJECT
        
        $("object").height(315);
        $("object").width(560);

		//Calculate the height of <header>
        //Use outerHeight() instead of height() if have padding
        var aboveHeight = 423;
 
		//when scroll
        $(window).scroll(function(){
 
	       		//if scrolled down more than the header’s height
                if ($(window).scrollTop() > aboveHeight){
 
	        	// if yes, add “fixed” class to the <nav>
                $('#optinBox').addClass('fixed').css('top','0').next().css('padding-top','0px');
 
                } else {
 
	        	// when scroll up or less than aboveHeight,
                $('#optinBox').removeClass('fixed').next().css('padding-top','0');
                
                }
        });

        // Optin

        $('#optin').click(function() {
                
                // Validate::
                
                $getName = $("#name").val();
                $getEmail = $("#email").val();
                $getPhone = $("#phone").val();
                $getWebsite = $("#website").val();
                
                // CHECK IF INFO IS ENTERED: NAME

                <?php  
                $getName = "";
                if($results->a5 == ""){
                    $getName = "Enter Your Full Name...";
                } else {
                    $getName = $results->a5;
                }
                ?>

                if($getName == "<?php echo $getName; ?>"){
                    // NA
                    $getName = "N/A";
                }
                
                // CHECK IF INFO IS ENTERED: EMAIL

                <?php  
                $getEmail = "";
                if($results->a6 == ""){
                    $getEmail = "Enter Your Best Email...";
                } else {
                    $getEmail = $results->a6;
                }
                ?>

                if($getEmail == "<?php echo $getEmail; ?>"){
                    $getEmail = "N/A";
                }

                 // CHECK IF INFO IS ENTERED: Phone

                <?php  
                $getPhone = "";
                if($results->a7 == ""){
                    $getPhone = "Enter Your Phone Number...";
                } else {
                    $getPhone = $results->a7;
                }
                ?>

                if($getPhone == "<?php echo $getPhone; ?>"){
                    $getPhone = "N/A";
                }

                 // CHECK IF INFO IS ENTERED: Website

                <?php  
                $getWebsite = "";
                if($results->a8 == ""){
                    $getWebsite = "Enter Your Website...";
                } else {
                    $getWebsite = $results->a8;
                }
                ?>

                if($getWebsite == "<?php echo $getWebsite; ?>"){
                    $getWebsite = "N/A";
                }

                var data = {
                    id: "<?php echo $_GET['id']; ?>",
                    name: ""+$getName+"",
                    email: ""+$getEmail+"",
                    phone: ""+$getPhone+"",
                    website: ""+$getWebsite+""
                };
                            
                var savelead = "<?php echo site_url() ?>/wp-content/plugins/everleadwp/inc/save_lead.php";
                    
                $.post( savelead, data,
                    function(results) {
                                            
                });
                
                $(this).html("loading...");

                setTimeout('$("#xCAPTURE").submit();', 2000)        
                        
                
              return false;
            }); 

        // Focus On Edits

        $('#name').click(function() {
            
            $(this).val("");
        
          return false;
        });

        $('#email').click(function() {
            
            $(this).val("");
        
          return false;
        });

        $('#website').click(function() {
            
            $(this).val("");
        
          return false;
        });

        $('#phone').click(function() {
            
            $(this).val("");
        
          return false;
        });
	
	});
	</script>	

</head>
<body>

<div id="bodyWrapper">        

	<div id="mainWrapper">
		
		<div id="topHeader">

			<div id="logoArea">
				<!-- <img src="images/logo-placeholder.png" alt=""> -->
			</div>

			<div id="callArea">
				<?php include("topbar-copy.php"); ?>
			</div>

			<br clear="all" />
			
		</div>

        <div id="mainBanner">

        	<div id="bannerCopy">

                <?php include("banner-copy.php"); ?>

        	</div>
        	
        </div>

        <div id="content">
        	
        	<div id="leftSide">

        		<div id="videoBox">

                    <?php include("video-copy.php"); ?>

        		</div>

        		<div id="salesCopy">

                    <?php

                    $content = get_post_field('post_content', $postID);    

                    if($content == ""){
                    ?>
        			
        			<h1>We Can Make Your Mobile SEO Really Blow Up To a Million Pieces!</h1>

        			<p class="lead" >Sign up and we can jump on the phone and talk all that jazz about mobile stuff...</p>

        			<p>Duis faucibus adipiscing dui eget pharetra. Curabitur nec nibh scelerisque libero commodo rhoncus. Mauris dignissim aliquam quam ut hendrerit. Cras ac neque nisi, sed placerat lacus.</p>

        			<p> In tincidunt orci vitae nisl vestibulum pharetra. Fusce semper, nunc non pretium iaculis, turpis libero pharetra ante, in rutrum mi tellus non tellus.</p>

        			<p>
					Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nulla facilisi. 
					</p>

					<p>Vestibulum ac mauris tellus. Nam tellus metus, mollis nec convallis non, vulputate id sapien. Nam non velit eget leo tristique gravida. Aenean porttitor ipsum facilisis tortor mollis facilisis.</p>

					<p> In tincidunt orci vitae nisl vestibulum pharetra. Fusce semper, nunc non pretium iaculis, turpis libero pharetra ante, in rutrum mi tellus non tellus.</p>

        			<p>
					Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nulla facilisi. 
					</p>

					<p>Vestibulum ac mauris tellus. Nam tellus metus, mollis nec convallis non, vulputate id sapien. Nam non velit eget leo tristique gravida. Aenean porttitor ipsum facilisis tortor mollis facilisis.</p>


					<p> In tincidunt orci vitae nisl vestibulum pharetra. Fusce semper, nunc non pretium iaculis, turpis libero pharetra ante, in rutrum mi tellus non tellus.</p>

        			<p>
					Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nulla facilisi. 
					</p>

					<p>Vestibulum ac mauris tellus. Nam tellus metus, mollis nec convallis non, vulputate id sapien. Nam non velit eget leo tristique gravida. Aenean porttitor ipsum facilisis tortor mollis facilisis.</p>


					<p> In tincidunt orci vitae nisl vestibulum pharetra. Fusce semper, nunc non pretium iaculis, turpis libero pharetra ante, in rutrum mi tellus non tellus.</p>

        			<p>
					Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nulla facilisi. 
					</p>

					<p>Vestibulum ac mauris tellus. Nam tellus metus, mollis nec convallis non, vulputate id sapien. Nam non velit eget leo tristique gravida. Aenean porttitor ipsum facilisis tortor mollis facilisis.</p>

                    <?php
                } else {

                    echo $content;
                }

                    ?>

        		</div>
        		
        	</div>

        	<div id="rightSide">
        		
        		<div id="optinBox">

                    <?php include("optin-copy.php"); ?>

        			<div id="optinFooter">
        				<img src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/optin-footer.png" alt="">
        			</div>

        		</div>

        	</div>

        	<br clear="left" />

        	<div id="footerArea">
        		<!-- <img src="images/logo-placeholder.png" alt=""> -->
        	</div>

        </div>

	</div>
        
</div>

<div style="display:none;">

    <form action="<?php echo $results->ar_url; ?>" id="xCAPTURE" method="POST" >

    <?php echo $results->ar_hidden; ?>
    
    <input id="arName" type="text" name="<?php echo $results->ar_name; ?>" value="" />
    <input id="arEmail" type="text" name="<?php echo $results->ar_email; ?>" value="" />

</form>


</div>

</body>
</html>