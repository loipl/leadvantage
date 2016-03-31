<!-- CSS FOR TOP BAR -->
<style>

    <?php 

        // TOP BAR STYLE CHECKER

        if($results->design3 == NULL){
                // Show Default Top Bar - it's null
        ?>
        
            #bodyWrapper{
                background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/topbar-black.png);
            }

        <?php
        } else {
        
        if($results->design3 == "top8"){
                // Show Custom Top Bar
        ?>
        
            #bodyWrapper{
                background-image: url(<?php echo $results->design4; ?>);
            }

        <?php

        } else{
                // Show topbar
        ?>
        
            #bodyWrapper{
                background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/topbar/<?php echo $results->design3; ?>.png);
            }

        <?php
        }

        }

    ?>

</style>