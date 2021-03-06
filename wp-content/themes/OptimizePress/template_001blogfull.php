<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
<?php
/*
Template Name: Blog Template w/ Full Content & Images
*/
?>
<?php
    $postcustom = get_post_custom($post->ID);
    $templatedir = get_bloginfo('template_directory');
?>
<?php get_header();?>
<?php include ("exit-pop.php"); ?>
<?php $url = get_bloginfo('template_directory'); ?>

<?php // Blog Page Options implement ?>
    <style type="text/css">
<?php 
		$blogbgtiling = ($blogbgimgtiling) ? $blogbgimgtiling : 'repeat';
		$blogbgimageurl = ($blogbgimageurl) ? 'url('.$blogbgimageurl.');background-repeat:'.$blogbgtiling : 'none'; 
?>
		body { <?php echo ($blogbgcolor) ? "background-color:#".$blogbgcolor : ""; ?>;background-image:<?=$blogbgimageurl?>; }
	    #header{<?php echo ($blogheaderheight) ? "height:".$blogheaderheight."px" : ""; ?>;background-image:url(<?php echo $blogheader; ?>);margin:0px auto;}
		.headerlogo{background-position:<?php echo $logo_align; ?> center;background-image:url(<?php echo $bloglogo; ?>);<?php echo ($blogheaderheight) ? "height:".$blogheaderheight."px" : ""; ?>;width:977px;background-repeat:no-repeat;}
		#headertext{<?php echo ($blogheaderheight) ? "line-height:".$blogheaderheight."px" : ""; ?>;text-align:center;width:977px;<?php echo ($blogheaderheight) ? "height:".$blogheaderheight."px" : ""; ?>;letter-spacing:-2px;font-weight:normal;<?php echo ($blogheadertextsize) ? "font-size:".$blogheadertextsize."px;" : ""; echo ($blogheadertextcolor) ? "color:#".$blogheadertextcolor.";" : ""; ?> } 
		#headerfullwidth{<?php echo ($blogheaderbg) ? "background-image:url(".$blogheaderbg.");" : ""; ?>margin:0px auto;}

		a {color:#<?php echo ($bloghyperlinkcolor) ? stripcslashes($bloghyperlinkcolor) : '0088cc'; ?>;text-decoration:none;}
		.page_item a:hover{color:#<?php echo ($bloghyperlinkcolor) ? stripcslashes($bloghyperlinkcolor) : '0088cc'; ?>;}
		#blognavbarbk{
		background: #<?php echo ($blognavbarmainbggradienttopcolor) ? $blognavbarmainbggradienttopcolor : ""; ?>;
			background-image: -moz-linear-gradient(100% 100% 90deg, #<?php echo ($blognavbarmainbggradientbotcolor) ? $blognavbarmainbggradientbotcolor : ""; ?>, #<?php echo ($blognavbarmainbggradienttopcolor) ? $blognavbarmainbggradienttopcolor : ""; ?>);
			background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#<?php echo ($blognavbarmainbggradienttopcolor) ? $blognavbarmainbggradienttopcolor : ""; ?>), to(#<?php echo ($blognavbarmainbggradientbotcolor) ? $blognavbarmainbggradientbotcolor : ""; ?>));
			border-bottom:<?php echo ($blognavbarbottomlinethickness) ? $blognavbarbottomlinethickness : ""; ?>px solid #<?php echo ($blognavbarbottomlinecolor) ? $blognavbarbottomlinecolor : ""; ?>;
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo ($blognavbarmainbggradienttopcolor) ? $blognavbarmainbggradienttopcolor : ""; ?>, endColorstr=#<?php echo ($blognavbarmainbggradientbotcolor) ? $blognavbarmainbggradientbotcolor : ""; ?>);
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo ($blognavbarmainbggradienttopcolor) ? $blognavbarmainbggradienttopcolor : ""; ?>, endColorstr=#<?php echo (blognavbarmainbggradientbotcolor) ? $blognavbarmainbggradientbotcolor : ""; ?>)";
		}
		
		#access a {color:#<?php echo ($blognavbartextcolor) ? $blognavbartextcolor : ""; ?>;text-shadow:0 1px 0 #<?php echo ($blognavbartextshadow) ? $blognavbartextshadow : ""; ?>;}
		
		#access li:hover > a,
		#access ul ul :hover > a {
			background: #<?php echo ($blognavbarmainbggradientbotcolor) ? $blognavbarmainbggradientbotcolor : ""; ?>;
			color: #<?php echo ($blognavbartexthovercolor) ? $blognavbartexthovercolor : ""; ?>;
			background-image: -moz-linear-gradient(100% 100% 90deg, #<?php echo ($blognavbarmainbggradienthoverbotcolor) ? $blognavbarmainbggradienthoverbotcolor : ""; ?>, #<?php echo ($blognavbarmainbggradienthovertopcolor) ? $blognavbarmainbggradienthovertopcolor : ""; ?>);
			background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#<?php echo ($blognavbarmainbggradienthovertopcolor) ? $blognavbarmainbggradienthovertopcolor : ""; ?>), to(#<?php echo ($blognavbarmainbggradienthoverbotcolor) ? $blognavbarmainbggradienthoverbotcolor : ""; ?>));
			text-shadow:0 1px 0 #<?php echo ($blognavbartexthovercolorshadow) ? $blognavbartexthovercolorshadow : ""; ?>;
			font-size:<?php echo ($blognavbarfontsize) ? $blognavbarfontsize : ""; ?>px;
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo ($blognavbarmainbggradienthovertopcolor) ? $blognavbarmainbggradienthovertopcolor : ""; ?>, endColorstr=#<?php echo ($blognavbarmainbggradienthoverbotcolor) ? $blognavbarmainbggradienthoverbotcolor : ""; ?>);
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo ($blognavbarmainbggradienthovertopcolor) ? $blognavbarmainbggradienthovertopcolor : ""; ?>, endColorstr=#<?php echo ($blognavbarmainbggradienthoverbotcolor) ? $blognavbarmainbggradienthoverbotcolor : ""; ?>)";
		}
		
		
		#access ul ul a {
			background:#<?php echo ($blognavbarsubbggradientbotcolor) ? $blognavbarsubbggradientbotcolor : ""; ?>;
			color: #<?php echo ($blognavbarsubtextcolor) ? $blognavbarsubtextcolor : ""; ?>;
			background-image: -moz-linear-gradient(100% 100% 90deg, #<?php echo ($blognavbarsubbggradientbotcolor) ? $blognavbarsubbggradientbotcolor : ""; ?>, #<?php echo ($blognavbarsubbggradienttopcolor) ? $blognavbarsubbggradienttopcolor : ""; ?>);
			background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#<?php echo ($blognavbarsubbggradienttopcolor) ? $blognavbarsubbggradienttopcolor : ""; ?>), to(#<?php echo ($blognavbarsubbggradientbotcolor) ? $blognavbarsubbggradientbotcolor : ""; ?>));
			text-shadow:0 1px 0 #<?php echo ($blognavbarsubtextshadow) ? $blognavbarsubtextshadow : ""; ?>;	
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo ($blognavbarsubbggradienttopcolor) ? $blognavbarsubbggradienttopcolor : ""; ?>, endColorstr=#<?php echo ($blognavbarsubbggradientbotcolor) ? $blognavbarsubbggradientbotcolor : ""; ?>);
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo ($blognavbarsubbggradienttopcolor) ? $blognavbarsubbggradienttopcolor : ""; ?>, endColorstr=#<?php echo ($blognavbarsubbggradientbotcolor) ? $blognavbarsubbggradientbotcolor : ""; ?>)";	
		}
		
		#access .menu-header, div.menu{font-size:<?php echo ($blognavbarfontsize) ? $blognavbarfontsize : ""; ?>px}
		
		#access ul li.current-menu-item > a{
		font-size:<?php echo ($blognavbarfontsize) ? $blognavbarfontsize : ""; ?>px;
		padding:0 15px;
		}
		#access .menu-header li, div.menu li{font-size:<?php echo ($blognavbarfontsize) ? $blognavbarfontsize : ""; ?>px;}
		
		.pagination .current {background:none repeat scroll 0 0 #<?php echo ($bloghyperlinkcolor) ? stripcslashes($bloghyperlinkcolor) : '4581B5'; ?>;}
		
		.pagination a:hover{background: #<?php echo ($bloghyperlinkcolor) ? stripcslashes($bloghyperlinkcolor) : '4581B5'; ?>;}

		.postpic { <?php echo ($bloghidepostthumbsetpages) ? "display:none" : ""; ?>; }
		.postinfo { <?php echo ($bloghidepostthumbsetpages) ? "width:100%" : "width:445px"; ?>; }

		
			    </style>
    
    <?php
if($customcss) {
echo '<style>'; 
echo stripslashes($customcss); 
echo '</style>';}
?>


<?php if($postcustom['_seo_onpagecustomcss']) {
echo '<style>'; 
echo stripslashes($postcustom['_seo_onpagecustomcss'][0]); 
echo '</style>';} 
?>


<div id="wrapper">

<div id="wrapper2">

<div id="innerwrapper">

<div id="header"<?php echo ($blogheaderhyperlink) ? ' onclick="location.href=\''.$blogheaderhyperlink.'\';" style="cursor: pointer;"' : ''; ?>>
	<div class="headerlogo"><?php if($blogactivateheadertext) { echo ($blogheadertext) ? '<div id="headertext">'.$blogheadertext.'</div>' : ''; } ?></div>
</div><!--close header-->

<div id="navbar">
<div id="blognavbarbk">
<div id="blognavbar">
<div id="access">

<?php wp_nav_menu(array('theme_location' => 'blog_menu', 'container_class' => 'menu-header')); ?>

</div>
</div>
</div>
</div>


<div id="blogmain">


<?php
$temp_query = $wp_query; 

/* 12/20/2010 Nick, Added to retrieve current URL and will be used if page template is set as static homepage. Fixed the WP bug of pagination in static homepage */

function curPageName() { 
$url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	$removethis = get_bloginfo('home').'/page/';
	$mypagenum = str_replace( $removethis, "", $url );
	$mypagenum = str_replace( "/", "", $mypagenum );
return $mypagenum;
} 

	$paged = (is_front_page()) ? curPageName() : ((get_query_var('paged')) ? get_query_var('paged') : 1); // checks if page template is used as static homepage
	$args = array(
	'author' => get_the_author_meta( 'ID' ),
	'post_date' => 'DESC',
	'cat' => '-0',
	'paged' => $paged
	);
	query_posts($args);
?>

<div style="width:620px;">
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

<!--Start Blog Post Coding-->
<div class="postcontent">

<div class="postinfo" style="width:615px;">
<div class="postpic">
<?php $post_tumb = get_post_meta( $post->ID, '_blogpost_images', true ); ?>
<img alt="<?php echo get_post_meta( $post->ID, '_blogpost_postimage_alttag', true ); ?>" src="<?php echo $templatedir.'/timthumb.php?src='.$post_tumb; ?>&h=144&w=144&zc=1" style="border:1px solid #c0c0c0;margin:0 20px 10px 0;" align="left">
</div>
<h2 class="postinfoheader"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

                  <div class="post-meta">
                    
                    <p class="post-people" style="font-size:13px !important;">Posted In <?php the_category(', '); ?> 
                    <?php if(!$blogdeactivatecomments) { // if Wordpress Comments is deactivated ?>
                    <span class="divide">|</span> <?php comments_number('No comments','1 comment','% comments'); ?>  </p>
                    <?php } ?>
</div>

<?php the_content(); ?>


<div class="postbar">

<div class="readmore" style="float:left;">

<div class="readmoreicon"></div>
<div class="readmorelink"><a href="<?php the_permalink(); ?>">Read Full Article...</a></div>
</div>

<div class="commentsmore" style="float:right;">

<?php if(!$blogdisablecommentstats) { // if Comment Stats is disabled ?>
<div class="commentsicon"></div>
<div class="commentslink"><a href="<?php the_permalink(); ?>"><?php comments_number('0 comments','1 comment','% comments'); ?></a></div>
<?php } ?>

</div>
</div><!--close postbar-->

</div><!--close postinfo-->
<div class="aclear"></div>

</div><!--close postcontent-->

<!--End Blog Post Coding-->

<?php endwhile; ?>
<?php i_pagination(); // Nick 11/29/2010 .Pagination ?>

<?php $wp_query = $temp_query;  ?>



</div><!--close blogmain-->

</div>

<div id="blogsidebar">
<?php if ( is_active_sidebar( 'sidebar-7' ) ) : dynamic_sidebar( 'sidebar-7' ); endif; // Nick 10/29/2010 .Blog Sidebar ?>
</div><!--close blog sidebar-->

<div class="aclear"></div>

</div><!--close inner wrapper-->


<div class="aclear"></div>

</div><!--close wrapper-->


</div>


<div id="blogfooter"></div><!--close footer-->

<div id="footer">

<div id="footer-inside">

<?php if(!$postcustom['_launchpage_disabledisclaimermsg']) { // On-Page disabled ?>
<?php if($activatefooterdisclaimermsg) { // if Footer Disclaimer Message Activated ?>
<div id="footer-disclaimer">
<?php echo ($footerdisclaimermsg) ? stripslashes($footerdisclaimermsg) : ""; ?>
</div>
<?php } ?>
<?php } // end On-Page disabled ?>

<div class="footer-left">Copyright &copy; <?php echo ($footer_text) ? stripslashes($footer_text) : ""; ?>
	<?php echo ($footer_poweredby) ? ( ($footer_afflink) ? '<a href="'.$footer_afflink.'">Powered by OptimizePress</a>' : 'Powered by OptimizePress' ) : ''; ?>
</div>
<div class="footer-right">
	<?php if($footer_include) { ?>
    <ul>		<?php // Nick 11/12/2010
        function new_nav_menu_items($items) {
            return str_replace('<a', '<a target="_blank"', $items);
        }
        ($footerlinkstarget) ? add_filter( 'wp_list_pages', 'new_nav_menu_items' ) : '';
        ?>
        <?php wp_list_pages("depth=0&sort_column=menu_order&include=".$footer_include."&title_li="); ?>
    </ul>
    <?php } ?>
</div>

</div>
</div><!--close footer-->

<?php include ("exit-redirect.php"); ?>

<?php // custom tracking code
echo ($customtrackingcodefooter) ? eval('?>'.stripcslashes(stripslashes($customtrackingcodefooter))) : ""; ?>
<?php echo ($postcustom['_seo_footertrackingjscode']) ? eval('?>'.stripcslashes($postcustom['_seo_footertrackingjscode'][0])) : ""; ?>

<?php wp_footer(); ?>

<script type="text/javascript" src="<?php echo get_bloginfo('template_directory'); ?>/js/combinebottom.js"></script>
<script type="text/javascript">
Cufon.replace('#headertext', { 
			  fontFamily: '<?php echo $blogheadertextfont; ?>' ,
			  textShadow: '1px 1px #<?php echo stripcslashes($blogheadertextshadowcolor); ?>'
			  });
</script>

</body>

</html>