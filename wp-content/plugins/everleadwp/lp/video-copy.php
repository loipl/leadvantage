
<?php

	// Video headline

	if($results->video1 == NULL){
		?>

		<div id="videoHeader">

        	<span>Watch This Amazing Video:</span>
        
        </div>

		<?php
	} else {
		?>

		<div id="videoHeader">

        	<span><?php echo $results->video1; ?></span>
        
        </div>

		<?php
	}

	// Video Code

	if($results->video4 == NULL){
		?>

		<iframe width="560" height="315" src="http://www.youtube.com/embed/GDs2nJf6lro" frameborder="0" allowfullscreen></iframe>

		<?php
	} else {
		?>

		<?php echo $results->video4; ?>

		<?php
	}

	// Video Footer

	if($results->video5 == NULL){
		?>

		<p>Get The Next Video For Free - Fill In The Form Now!</p>

		<?php
	} else {
		?>

		<p><?php echo $results->video5; ?></p>

		<?php
	}

?>