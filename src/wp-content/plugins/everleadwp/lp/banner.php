<!-- CSS FOR BANNER -->
<style>

    <?php 

        // Banner STYLE CHECKER

        if($results->banner == NULL){
        // Show Default Banner - it's null
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner-mobileseo.png);
        }

        <?php
        } else if($results->banner == "ban9"){
        // Custom Banner
        ?>

        #mainBanner{
            background-image: url(<?php echo $results->banner_url; ?>);
        }

        #bannerCopy h3{
            color: #e8e8e8 !important;
        }

        <?php    

        } else if($results->banner == "ban1"){
        // Mobile SEO
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner/ban1.png);
        }

        <?php    

        } else if($results->banner == "ban2"){
        //  SEO
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner/ban2.png);
        }

        #bannerCopy h1{
            color: #59a034 !important;
        }

        #bannerCopy h3{
            color: #708977 !important;
        }

        <?php    

        } else if($results->banner == "ban3"){
        //  REP
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner/ban3.png);
        }

        #bannerCopy h1{
            /*color: #59a034 !important;*/
        }

        #bannerCopy h3{
            color: #feeae9 !important;
        }

        <?php    

        } else if($results->banner == "ban4"){
        //  Facebook
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner/ban4.png);
        }

        #bannerCopy h1{
            color: #3b3f47 !important;
        }

        #bannerCopy h3{
            color: #5e687f !important;
        }

        <?php    

        } else if($results->banner == "ban5"){
        //  Mobile
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner/ban5.png);
        }

        #bannerCopy h1{
            /*color: #3b3f47 !important;*/
        }

        #bannerCopy h3{
            color: #a1a8c4 !important;
        }

        <?php    

        } else if($results->banner == "ban6"){
        //  Mobile
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner/ban6.png);
        }

        #bannerCopy h1{
            /*color: #3b3f47 !important;*/
        }

        #bannerCopy h3{
            color: #bccce7 !important;
        }

        <?php    

        } else if($results->banner == "ban7"){
        //  Social
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner/ban7.png);
        }

        #bannerCopy h1{
            /*color: #3b3f47 !important;*/
        }

        #bannerCopy h3{
            color: #e2fcdf !important;
        }

        <?php       

        } else if($results->banner == "ban8"){
        //  Lead
        ?>

        #mainBanner{
            background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/banner/ban8.png);
        }

        #bannerCopy h1{
            /*color: #3b3f47 !important;*/
        }

        #bannerCopy h3{
            color: #c3e8ff !important;
        }

        <?php    

        }

    ?>

</style>