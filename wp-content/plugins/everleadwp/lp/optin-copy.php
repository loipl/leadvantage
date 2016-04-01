
<?php

	// OPTIN HEADER COPY

	if($results->a1 == NULL){
		?>

		<div id="optinHeader">
        	<h3>Get Your Free SEO Session Now!</h3>
        </div>

		<?php
	} else {
		?>

		<div id="optinHeader">
        	<h3><?php echo $results->a1; ?></h3>
        </div>

		<?php
	}

?>

<div id="optinContent">

<?php
	// OPTIN INTRO TEXT

	if($results->a4 == NULL){
		?>

		<?php
	} else {
		?>

		<p><?php echo $results->a4; ?></p>

		<?php
	}

?>

<?php
	// FORM - NAME - 

	if($results->a5 == NULL){
		?>

		<input type="text" id="name" class="optform2 opt_name" value="Enter Your Full Name..." >

		<?php
	} else if($results->a5 == "OFF"){
		?>

		<!-- <input type="text" id="name" class="optform2 opt_name" value="Enter Your Full Name..." > -->

		<?php
	} else {
		?>

		<input type="text" id="name" class="optform2 opt_name" value="<?php echo $results->a5; ?>" >

		<?php
	}

?>

<?php
	// FORM - EMAIL - 

	if($results->a6 == NULL){
		?>

		<input type="text" id="email" class="optform2 opt_email" value="Enter Your Best Email..." >

		<?php
	} else if($results->a6 == "OFF"){
		?>

		<!-- <input type="text" id="name" class="optform2 opt_name" value="Enter Your Full Name..." > -->

		<?php
	} else {
		?>

		<input type="text" id="email" class="optform2 opt_email" value="<?php echo $results->a6; ?>" >

		<?php
	}

?>

<?php
	// FORM - PHONE - 

	if($results->a7 == NULL){
		?>

		<input type="text" id="phone" class="optform2 opt_phone" value="Enter Your Phone Number..." >

		<?php
	} else if($results->a7 == "OFF"){
		?>

		<!-- <input type="text" id="name" class="optform2 opt_name" value="Enter Your Full Name..." > -->

		<?php
	} else {
		?>

		<input type="text" id="phone" class="optform2 opt_phone" value="<?php echo $results->a7; ?>" >

		<?php
	}

?>

<?php
	// FORM - WEBSITE - 

	if($results->a8 == NULL){
		?>

		<input type="text" id="website" class="optform2 opt_url" value="Enter Your Website..." >

		<?php
	} else if($results->a8 == "OFF"){
		?>

		<!-- <input type="text" id="name" class="optform2 opt_name" value="Enter Your Full Name..." > -->

		<?php
	} else {
		?>

		<input type="text" id="website" class="optform2 opt_url" value="<?php echo $results->a8; ?>" >

		<?php
	}

?>



<?php

	// See if btn is color or custom

	if($results->a10 == "btn7"){

		?>

		<img src="<?php echo $results->a11; ?>" id="optin" style="cursor:pointer;" alt="">

		<?php

	} else {

		$btnColor = "success";

		// SET BUTTON COLOR:

		if($results->a10 == "btn1"){
			$btnColor = "inverse";
		} else if($results->a10 == "btn2"){
			$btnColor = "info";
		} else if($results->a10 == "btn3"){
			$btnColor = "success";
		} else if($results->a10 == "btn4"){
			$btnColor = "danger";
		} else if($results->a10 == "btn6"){
			$btnColor = "";
		}

		// OPTIN BUTTON COPY

		if($results->a9 == NULL){
			?>

			<p class="btn btn-<?php echo $btnColor; ?> optinBTN" id="optin" >Book Your Session Now</p>

			<?php
		} else {
			?>

			<p class="btn btn-<?php echo $btnColor; ?> optinBTN" id="optin" ><?php echo $results->a9; ?></p>

			<?php
		}

	}

?>

        				</div>