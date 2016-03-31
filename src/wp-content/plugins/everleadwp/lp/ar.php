<!-- CSS FOR Optin HEADER -->
<style>

    <?php 

        // Optin HEADER STYLE CHECKER

        if($results->a2 == NULL){
                // Show Default Option Header - it's null
        ?>

        #optinHeader{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/optin/ar1.png);
        }

        <?php    

        } else if($results->a2 == "ar8"){
        ?>

        #optinHeader{
            background-image: url(<?php echo $results->a3; ?>);
        }

        <?php
        } else {
        ?>

        #optinHeader{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/optin/<?php echo $results->a2; ?>.png);
        }

        <?php 

        }


    ?>

</style>