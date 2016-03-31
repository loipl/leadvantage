<?php
function everlead_dashboard(){
?>


<!--<link href="<?php echo site_url(); ?>/wpcalldirpro/css/style.css" rel="stylesheet" type="text/css" />-->

<?php include("css.php"); ?>


<script type="text/javascript" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/inc/jquery.js"></script>

	

<script type="text/javascript">
$(document).ready(function() {

	// Toggle Helpers

	$('.helpImg').click(function() {
		
		$ID = $(this).attr("help");

		$("#help_"+$ID+"").toggle();
	
	  return false;
	});

	// Change Editing Areas

	$('.editArea').click(function() {
		
		$ID = $(this).attr("editID");

		$(".editSections").hide();

		$("#"+$ID+"").show();

		$('.editArea').removeClass("actvieArea");

		$(this).addClass("actvieArea");

		// $("#"+$ID+"_img").attr("src", ""+$ID+"");

	  return false;
	});

	// preview BG

	$('.bgDesign').click(function() {
		
		$BG = $(this).attr("bg");

		$('#bgTest').removeClass();

		$("#bgTest").addClass($BG);

		$('.bgDesign').removeClass("special");

		$(this).addClass("special");

		if($BG == "bg9"){
			$("#bgCustom").show();
			$("#bgTest").hide();
		} else {
			$("#bgCustom").hide();
			$("#bgTest").show();
		}

		$("#design1").val($BG);
	
	  return false;
	});

	// Choose Top Bar

	$('.topBar').click(function() {
		
		$('.topBar').removeClass("special");
		$(this).addClass("special");

		$ID = $(this).attr("bg");

		if($ID == "top8"){
			$("#topCustom").show();
		} else {
			$("#topCustom").hide();
		}

		$("#design3").val($ID);
	
	  return false;
	});
	
	// Choose Video Header

	$('.videoHeader').click(function() {
		
		$('.videoHeader').removeClass("special");
		$(this).addClass("special");

		$ID = $(this).attr("id");

		if($ID == "video9"){
			$("#videoCustom").show();
		} else {
			$("#videoCustom").hide();
		}

		$("#v2").val($ID);
	
	  return false;
	});

	// Choose Optin BTN

	$('.btnHeader').click(function() {
		
		$('.btnHeader').removeClass("special");
		$(this).addClass("special");

		$ID = $(this).attr("id");

		if($ID == "btn7"){
			$("#btnCustom").show();
		} else {
			$("#btnCustom").hide();
		}

		$("#a10").val($ID);
	
	  return false;
	});

	// Choose Optin Header

	$('.banner').click(function() {
		
		$('.banner').removeClass("special");
		$(this).addClass("special");

		$ID = $(this).attr("id");

		if($ID == "ban9"){
			$("#bannerCustom").show();
		} else {
			$("#bannerCustom").hide();
		}

		$("#banner").val($ID);
	
	  return false;
	});

	// Choose Banner

	$('.optinHeader').click(function() {
		
		$('.optinHeader').removeClass("special");
		$(this).addClass("special");

		$ID = $(this).attr("id");

		if($ID == "ar8"){
			$("#optinCustom").show();
		} else {
			$("#optinCustom").hide();
		}

		$("#a2").val($ID);
	
	  return false;
	});

	$('#createNEW').click(function() {
		
		$getTitle = $("#title_campaign").val();
		
		if($getTitle != ""){
		
		var data = {
					action: 'everleadwp_create',
					title: ""+$getTitle+""
		};
		
		$.post( ajaxurl, data,
				   function(data) {
					window.location = "<?php echo $_SERVER["REQUEST_URI"]; ?>&settings&id="+ data;
		});
		
		} else {
		
		 	alert("Campaign must have a title!");
		
		}
	
	  return false;
	});
	
	$('#editCAMPAIGN').click(function() {
		
		// General Stuff
		$fangate_status = $("#fangate_status").val();
		
		var data = {
			
			action: 'everleadwp_edit',
			id: '<?php echo $_GET['id']; ?>',
			design1: $("#design1").val(),
			design2: $("#design2").val(),
			design3: $("#design3").val(),
			design4: $("#design4").val(),

			copy1: $("#copy1").val(),
			copy2: $("#copy2").val(),
			copy3: $("#copy3").val(),
			copy4: $("#copy4").val(),
			copy5: $("#copy5").val(),

			video1: $("#v1").val(),
			video2: $("#v2").val(),
			video3: $("#v3").val(),
			video4: $("#v4").val(),
			video5: $("#v5").val(),

			a1: $("#a1").val(),
			a2: $("#a2").val(),
			a3: $("#a3").val(),
			a4: $("#a4").val(),
			a5: $("#a5").val(),
			a6: $("#a6").val(),
			a7: $("#a7").val(),
			a8: $("#a8").val(),
			a9: $("#a9").val(),
			a10: $("#a10").val(),
			a11: $("#a11").val(),

			extra: $("#extrax").val(),

			banner: $("#banner").val(),
			banner_url: $("#banner_url").val(),

			ar_code: $("#ar_code").val(),
			ar_name: $("#ar_name2").val(),
			ar_email: $("#ar_email").val(),
			ar_url: $("#ar_url").val(),
			ar_hidden: $("#ar_hidden").val()
	
		}		
		
		$.post( ajaxurl, data,
				   function(data) {
					alert("Campaign Settings Saved!");
		});
		
	
	  return false;
	});
	
	
	
	// POP OPEN IMAGE UPLOADER
	$photoURLSelected = "";
	
	$('.launch_media_lib').click(function() {
		
			$photoURLSelected = $(this).attr("photoBox"); 
			// gets the ID from the photoBox
			
			tb_show('', 'media-upload.php?type=image&TB_iframe=true'); 
			// launches Media Library
		 
		  return false;
	});
	
	window.send_to_editor = function(html) {
	        
	   	    imgurl = $('img',html).attr('src'); 
	    	// gets the selected image URL path
		 	
		 	$("#"+$photoURLSelected+"").val(imgurl); 
		 	// sets the field with the photoBox ID with the image path
		 
		 tb_remove();
	}
	
	// Split AR Code
	
	$('#ar_code').keyup(function() {
				change_selects();
				//alert("?");
		  return false;
		});
		
		function change_selects(){
				var tags = ['a','iframe','frame','frameset','script'], reg, val = $('#ar_code').val(),
					hdn = $('#arcode_hdn_div2'), formurl = $('#ar_url'), hiddenfields = $('#ar_hidden');
			    formurl.val('');
				if(jQuery.trim(val) == '')
					return false;
				$('#arcode_hdn_div').html('');
				$('#arcode_hdn_div2').html('');
				for(var i=0;i<5;i++){
					reg = new RegExp('<'+tags[i]+'([^<>+]*[^\/])>.*?</'+tags[i]+'>', "gi");
					val = val.replace(reg,'');
					
					reg = new RegExp('<'+tags[i]+'([^<>+]*)>', "gi");
					val = val.replace(reg,'');
				}
				var tmpval;
				try {
					tmpval = decodeURIComponent(val);
				} catch(err){
					tmpval = val;
				}
				hdn.append(tmpval);
				var num = 0;
				var name_selected = '';
				var email_selected = '';
				$(':text',hdn).each(function(){
					var name = $(this).attr('name'),
						name_selected = num == '0' ? name : (num != '0' ? name_selected : ''), 
						email_selected = num == '1' ? name : email_selected;
						if(num=='0') jQuery('#ar_name2').val(name_selected);
						if(num=='1') jQuery('#ar_email').val(email_selected);
				num++;
				});
				jQuery(':input[type=hidden]',hdn).each(function(){
					jQuery('#arcode_hdn_div').append(jQuery('<input type="hidden" name="'+jQuery(this).attr('name')+'" />').val(jQuery(this).val()));
				});		
				var hidden_f = jQuery('#arcode_hdn_div').html();
				formurl.val(jQuery('form',hdn).attr('action'));
				hiddenfields.val(hidden_f);
				//alert(tmpval);
				hdn.html('');
				
			};
			
	
	// Delete Campaign
	
	$('#deleteCampaign').click(function() {
	
		confirmation();
	
	  return false;
	});
	
	function confirmation() {
		var answer = confirm("Are You Sure You Want To Delete This Campaign?")
		if (answer){
			
			var data = {
				id: "<?php echo $_GET['id']; ?>"
			};
				
			var savelead = "<?php echo site_url() ?>/wp-content/plugins/everleadwp/inc/delete_campaign.php";
			
			$.post( savelead, data,
			   function(results) {
					window.location = "<?php echo site_url(); ?>/wp-admin/admin.php?page=everlead-dashboard";						     
			});
			
			
			
		}
		else{
			
		}
	}


	$('#showadvar').click(function() {
		
		$("#adv_ar").toggle();
		
	  return false;
	});


	
});
</script>

	<div id="cd_mainWrapper">
	
		<div id="cd_headerWrapper">
				
		</div>
		
		<div id="cd_mainContent">
		
			<?php 
			
				if (isset($_GET['id']) && isset($_GET['settings'])) {
					include("editdep.php");
				} else if (isset($_GET['id']) && isset($_GET['questions'])) {
					include("editquestions.php");
				} else if (isset($_GET['create'])) {
					include("create.php");
				} else if (isset($_GET['report'])) {
					include("report.php");
				} else if (isset($_GET['leads'])) {
					include("lead.php");
				} else {
					include("dash.php");
				}
			
			?>
			
		</div>
		
		<div id="cd_footer">
			<img src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/footer.png" />
		</div>
	
	</div>

<?php
}
?>