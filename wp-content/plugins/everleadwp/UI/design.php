<div id="depTitle3" style="margin-top: 35px;">
					
	<h1>Background Style:</h1>
	<p class="subtext2" style="margin-top: -10px;">Choose a background - roll over to preview design pattern - some are very subtle...</p>
					
</div>
				
			
<div class="well">
		
	<div id="edit_dep3" >

		<div id="introMp3x" >

		<img class="helpImg" help="bg" align="right" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/help.png" />	
		
		<a class="bgDesign uibutton large special" id="bg1" bg="bg1" >white</a>
		<a class="bgDesign uibutton large" bg="bg2" id="bg2" style="margin-left: 20px;">grey</a>
		<a class="bgDesign uibutton large" bg="bg3" id="bg3" style="margin-left: 20px;">noise</a>
		<a class="bgDesign uibutton large" bg="bg4" id="bg4" style="margin-left: 20px;">new wave</a>
		<a class="bgDesign uibutton large" bg="bg5" id="bg5" style="margin-left: 20px;">wood</a>
		<a class="bgDesign uibutton large" bg="bg6" id="bg6" style="margin-left: 20px;">grid</a>
		<a class="bgDesign uibutton large" bg="bg7" id="bg7" style="margin-left: 20px;">stripes</a>
		<a class="bgDesign uibutton large" bg="bg8" id="bg8" style="margin-left: 20px;">cubes</a>
		<a class="bgDesign uibutton large" bg="bg9" id="bg9" style="margin-left: 20px;">custom</a>

		<input type="hidden" id="design1" value="<?php echo stripcslashes($results->design1); ?>" >

		<div id="bgTest" class="bg1" >
			
		</div>

		<div id="bgCustom" style="display:none;" >
		
		<div class="sepper"></div>

		<h3 style="margin-top: 15px; color: #afafaf;" >Custom BG Image:</h3>
		<p class="subtext2" style="margin-top: -10px;">This must be the full URL to the image you want to display as the background...</p>
						
		<input type="text" id="design2" value="<?php echo stripcslashes($results->design2); ?>" />
		
		</div>

		<img id="help_bg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/help/bg.png" class="helpIMGr" />

		</div>
				
	</div>
				
</div>	

<div id="depTitle3" style="margin-top: 35px;">
					
	<h1>Top Bar Style:</h1>
	<p class="subtext2" style="margin-top: -10px;">This is the style of the top bar of the page, where the logo is placed...</p>
					
</div>

<div class="well">
		
	<div id="edit_dep3" >
	
		<div>

		<img class="helpImg" help="topbar" align="right" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/help.png" />	
							
		<a class="topBar uibutton large special" id="top1" bg="top1" >
			<div class="topbar1 top1" ></div>
			black
		</a>

		<a class="topBar uibutton large" id="top2" bg="top2" style="margin-left:20px;" >
			<div class="topbar1 top2" ></div>
			blue
		</a>

		<a class="topBar uibutton large" bg="top3" id="top3" style="margin-left:20px;" >
			<div class="topbar1 top3" ></div>
			green
		</a>

		<a class="topBar uibutton large" bg="top4" id="top4" style="margin-left:20px;" >
			<div class="topbar1 top4" ></div>
			red
		</a>

		<a class="topBar uibutton large" bg="top5" id="top5" style="margin-left:20px;" >
			<div class="topbar1 top5" ></div>
			purple
		</a>

		<a class="topBar uibutton large" bg="top6" id="top6" style="margin-left:20px;" >
			<div class="topbar1 top6" ></div>
			brown
		</a>

		<a class="topBar uibutton large" bg="top7" id="top7" style="margin-left:20px;" >
			<div class="topbar1 top7" ></div>
			grey
		</a>

		<a class="topBar uibutton large" bg="top8" id="top8" style="margin-left:20px;" >
			<div class="topbar1 top8" ></div>
			custom
		</a>

		<input type="hidden" id="design3" value="<?php echo stripcslashes($results->design3); ?>" >

		<div id="topCustom" style="display:none;" >
		
		<div class="sepper"></div>

		<h3 style="margin-top: 15px; color: #afafaf;" >Custom Top Bar Image:</h3>
		<p class="subtext2" style="margin-top: -10px;">This must be the full URL to the image you want to display, it will be placed at the top of the page repeating hortizonally...</p>
						
		<input type="text" id="design4" value="<?php echo stripcslashes($results->design4); ?>" />
						
		</div>

		<img id="help_topbar" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/help/topbar.png" class="helpIMGr" />
						
		</div>
				
	</div>
				
</div>

<div id="depTitle3" style="margin-top: 35px;">
					
	<h1>Main Banner Style:</h1>
	<p class="subtext2" style="margin-top: -10px;">This is the biggest area of the landing page, and most important, choose wisely...</p>
					
</div>				
			
<div class="well">
		
	<div id="edit_dep3" >
	

		<div id="introMp3x" >
							
		<h3 style=" margin-top: -5px; color: #afafaf;" >Choose Banner Style:</h3>
		<p class="subtext2" style="margin-top: -10px;">You can choose a pre-designed header, or a use your own header image...</p>
		
		<div>

			<p class="banner uibutton large special" bg="ban1" id="ban1" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/1.png" />
			mobile SEO
			</p>

			<p class="banner uibutton large" bg="ban2" id="ban2" style="margin-left:35px;" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/2.png" />
			SEO
			</p>

			<p class="banner uibutton large" bg="ban3" id="ban3" style="margin-left:35px;" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/3.png" />
		 	Rep Mgt
			</p>

			<p class="banner uibutton large" bg="ban4" id="ban4" style="margin-left:35px;" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/4.png" />
			Facebook
			</p>

			<p class="banner uibutton large" bg="ban5" id="ban5" style="margin-left:35px;" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/5.png" />
			Mobile
			</p>

			<br />

			<p class="banner uibutton large" bg="ban6" id="ban6" style="margin-top:35px;" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/6.png" />
			Web Design
			</p>

			<p class="banner uibutton large" bg="ban7" id="ban7" style="margin-left:35px;" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/7.png" />
			Social
			</p>

			<p class="banner uibutton large" bg="ban8" id="ban8" style="margin-left:35px;" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/8.png" />
			Lead Sales
			</p>

			<p class="banner uibutton large" bg="ban9" id="ban9" style="margin-left:35px;" >
			<img class="banImg" src="<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/banner/9.png" />
			Custom
			</p>

		</div>

		<input type="hidden" id="banner" value="<?php echo stripcslashes($results->banner); ?>" />
		
		<div id="bannerCustom" style="display:none;" >
		
		<div class="sepper"></div>

		<h3 style="margin-top: 15px; color: #afafaf;" >Custom Banner Image:</h3>
		<p class="subtext2" style="margin-top: -10px;">This would be the main banner image, for best results, 948x338...</p>
						
		<input type="text" id="banner_url" value="<?php echo stripcslashes($results->banner_url); ?>" />
						
		</div>

		</div>
				
	</div>
				
</div>	