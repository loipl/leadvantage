<!-- CSS FOR BACKGROUND -->
<style>

    <?php 

        // BG STYLE CHECKER

        if($results->design1 == NULL){
                // Show Default BG - it's null
        } else {
        
        if($results->design1 == "bg1"){
                // WHITE BACKGROUND
        } else if($results->design1 == "bg2"){
                // GREY BACKGROUND
        ?>

            body{
                background-color: #DDD;
            }

        <?php
        
        } else if($results->design1 == "bg9"){
                // Show Custom BG
        ?>
        
            body{
                background-image: url(<?php echo $results->design2; ?>);
            }

        <?php

        } else{
                // Show Pre-BG
        ?>
        
            body{
                background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/bg/<?php echo $results->design1; ?>.png);
            }

        <?php
        }

        }

    ?>

</style>