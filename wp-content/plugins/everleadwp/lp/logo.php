<!-- CSS FOR LOGO -->
<style>

    <?php 

        // LOGO

        if($results->copy1 == NULL){
                // Show Default LOGO - it's null
        ?>

            #logoArea{
                background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/logo-placeholder.png);
            }

        <?php
        } else{
                // Show Pre-BG
        ?>
        
            #logoArea{
                background-image: url(<?php echo $results->copy1; ?>);
            }

        <?php
        }

    ?>

</style>