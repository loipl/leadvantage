<!-- CSS FOR VIDEO HEADER -->
<style>

    <?php 

        // VIDEO HEADER STYLE CHECKER

        if($results->video2 == NULL){
                // Show Default Video Header - it's null
        ?>

        #videoHeader{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/vheader/video1.png);
        }

        <?php    

        } else if($results->video2 == "video8"){
                // Show Custom Video Header
        ?>
        
            #videoHeader{
                display: none;
            }

        <?php
        
        } else if($results->video2 == "video9"){
                // Show Custom Video Header
        ?>
        
            #videoHeader{
                background-image: url(<?php echo $results->video3; ?>);
            }

        <?php

        } else{
                // Show Pre-Video Header
        ?>

            #videoHeader{
                background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/vheader/<?php echo $results->video2; ?>.png);
            }

        <?php
        }


    ?>

</style>