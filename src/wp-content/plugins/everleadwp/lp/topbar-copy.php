
<?php

	if($results->copy2 == NULL){
		?>

		<span class="precall" >Call To Get A Free SEO Session</span>

		<?php
	} else {
		?>

		<span class="precall" ><?php echo $results->copy2; ?></span>

		<?php
	}

	if($results->copy3 == NULL){
		?>

		<span class="call" >1-555-554-5454</span>

		<?php
	} else {
		?>

		<span class="call" ><?php echo $results->copy3; ?></span>

		<?php
	}

?>