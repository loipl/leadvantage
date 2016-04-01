<style>

#cd_mainWrapper{
	width: 892px;
}

#cd_mainContent{
	background: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/repeater.png) repeat-y;
	padding-top: 20px;
	padding-bottom: 6px;
	padding-left: 25px;
	padding-right: 25px;
	color: #FFF;
}

#cd_headerWrapper{
	width: 892px;
	height: 110px;
	margin-top: 20px;
	background: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/header.png) no-repeat;
}

#cd_keypad{
	padding-top: 114px;
	padding-left: 40px;
}

.cd_keypad{
	float: left;
	width: 62px;
	height: 60px;
	cursor: pointer;
	margin-left: 10px;
}

.cd_keypad img{
	margin-left: 20px;
	margin-top: 12px;
}


#twilioAPI{
	padding-top: 20px;
	padding-bottom: 20px;
	border-bottom: 1px dotted #323232;
	border-top: 1px dotted #323232;
}

input{
	padding: 10px;
	font-size: 12px;
	width: 800px;
	color: #bbbbbb !important;
	background-color: #282828 !important;
	border: 1px solid #343434 !important;
	outline: none;
}

select {
	padding: 10px;
	font-weight: bold;
	font-size: 16px;
	width: 800px;
	outline: none;
	color: #bbbbbb !important;
	background-color: #282828 !important;
	border: 1px solid #343434 !important;
}

textarea {
	color: #bbbbbb;
	background-color: #282828;
	border: 1px solid #343434 !important;
	padding: 10px;
	/*font-weight: bold;*/
	font-size: 12px;
	width: 800px;
	height: 150px;
	outline: none;
}

.well {
	min-height: 20px;
	padding: 19px;
	margin-bottom: 20px;
	background-color: #1f1f1f;
	border: 1px solid #EEE;
	border: 1px solid rgba(0, 0, 0, 0.05);
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
	-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
	-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
	box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
}

.wellX {
	min-height: 20px;
	margin-bottom: 20px;
	background-color: #1f1f1f;
	border: 1px solid #EEE;
	border: 1px solid rgba(0, 0, 0, 0.05);
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
	-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
	-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
	box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
}
.sepper{
	border-top: 1px dotted #303030;
	height: 1px;
	margin-top: 20px;
	margin-bottom: 20px;
}

#depTitle{
	float: left;
	width: 450px;
}

#depStat{
	float: right;
	width: 100px;
	text-align: center;
}

.bTitle{
	color: #FFF;
}

.bContext{
	color: #FFF;
}

.depStat1{
	float: right;
	width: 100px;
	text-align: center;
	background-color: #171717;
	-webkit-border-radius: 3px 0px 0px 3px;
	border-radius: 3px 0px 0px 3px;
	border-top: 1px solid #232323;
	border-left: 1px solid #232323;
	border-right: 1px dotted #232323;
}

.depStat2{
	float: right;
	width: 100px;
	text-align: center;
	background-color: #171717;
	-webkit-border-radius: 0px 3px 3px 0px;
	border-radius: 0px 3px 3px 0px;
	border-top: 1px solid #232323;
	border-right: 1px solid #232323;
}

.depStat3{
	float: right;
	width: 100px;
	text-align: center;
	background-color: #eaeaea;
	border-top: 1px solid #e2e2e2;
	border-right: 1px dotted #dbdbdb;
}


.btn-successx {
	background-color: #5BB75B;
	background-image: -ms-linear-gradient(top, #62C462, #51A351);
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#62C462), to(#51A351));
	background-image: -webkit-linear-gradient(top, #62C462, #51A351);
	background-image: -o-linear-gradient(top, #62C462, #51A351);
	background-image: -moz-linear-gradient(top, #62C462, #51A351);
	background-image: linear-gradient(top, #62C462, #51A351);
	background-repeat: repeat-x;
	border-color: #51A351 #51A351 #387038;
	border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	filter: progid:dximagetransform.microsoft.gradient(startColorstr='#62c462', endColorstr='#51a351', GradientType=0);
	filter: progid:dximagetransform.microsoft.gradient(enabled=false);
}
.btnx {
	border-color: #CCC;
	border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
}

.btnx {
	display: inline-block;
	padding: 4px 10px 4px;
	margin-bottom: 0;
	font-size: 13px;
	line-height: 18px;
	color: #333;
	text-align: center;
	text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
	vertical-align: middle;
	cursor: pointer;
	background-color: whiteSmoke;
	background-image: -ms-linear-gradient(top, white, #E6E6E6);
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(white), to(#E6E6E6));
	background-image: -webkit-linear-gradient(top, white, #E6E6E6);
	background-image: -o-linear-gradient(top, white, #E6E6E6);
	background-image: linear-gradient(top, white, #E6E6E6);
	background-image: -moz-linear-gradient(top, white, #E6E6E6);
	background-repeat: repeat-x;
	border: 1px solid #CCC;
	border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	border-color: #E6E6E6 #E6E6E6 #BFBFBF;
	border-bottom-color: #B3B3B3;
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
	filter: progid:dximagetransform.microsoft.gradient(startColorstr='#ffffff', endColorstr='#e6e6e6', GradientType=0);
	filter: progid:dximagetransform.microsoft.gradient(enabled=false);
	-webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
	-moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
	box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
}

.uibutton {
position: relative;
z-index: 1;
overflow: visible;
display: inline-block;
padding: 0.3em 0.6em 0.375em;
border: 1px solid #3a3a3a;
border-bottom-color: #3a3a3a;
margin: 0;
text-decoration: none;
text-align: center;
font: bold 11px/normal 'lucida grande', tahoma, verdana, arial, sans-serif;
white-space: nowrap;
cursor: pointer;
color: #717171;
background-color: #3a3a3a;
/*background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#F5F6F6), to(#E4E4E3));
background-image: -moz-linear-gradient(#F5F6F6, #E4E4E3);
background-image: -o-linear-gradient(#F5F6F6, #E4E4E3);
background-image: linear-gradient(#F5F6F6, #E4E4E3);
filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f6f6', EndColorStr='#e4e4e3');*/
-webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #353535;
-moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #353535;
box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #353535;
zoom: 1;
-webkit-border-radius: 3px;
border-radius: 3px;
}

.uibutton.large {
font-size: 13px;
}

.uibutton.special {
border-color: #3B6E22 #3B6E22 #2C5115;
color: white;
background-color: #69A74E;
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#75AE5C), to(#67A54B));
background-image: -moz-linear-gradient(#75AE5C, #67A54B);
background-image: -o-linear-gradient(#75AE5C, #67A54B);
background-image: linear-gradient(#75AE5C, #67A54B);
filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#75ae5c', EndColorStr='#67a54b');
-webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #98C286;
-moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #98c286;
box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #98C286;
}

.uibutton:hover{
	border-color: #3B6E22 #3B6E22 #2C5115;
	color: white !important;
background-color: #69A74E;
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#75AE5C), to(#67A54B));
background-image: -moz-linear-gradient(#75AE5C, #67A54B);
background-image: -o-linear-gradient(#75AE5C, #67A54B);
background-image: linear-gradient(#75AE5C, #67A54B);
filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#75ae5c', EndColorStr='#67a54b');
-webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #98C286;
-moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #98c286;
box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #98C286;
}

.uibutton.confirm {
border-color: #29447E #29447E #1A356E;
color: white;
background-color: #5B74A8;
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#637BAD), to(#5872A7));
background-image: -moz-linear-gradient(#637BAD, #5872A7);
background-image: -o-linear-gradient(#637BAD, #5872A7);
background-image: linear-gradient(#637BAD, #5872A7);
filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#637bad', EndColorStr='#5872a7');
-webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #8A9CC2;
-moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #8a9cc2;
box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1), inset 0 1px 0 #8A9CC2;
}

#editor-toolbar #edButtonHTML, #quicktags {display: none;}

#editQbtns{
	margin-bottom: 25px;
	border-bottom: 1px solid #dfdfdf;
	padding-bottom: 25px;
	margin-top: 10px;
}

.reportResults{
	border: 1px dotted #DDD;
	padding: 25px;
}

.subtext{
	color: #50564f;
}

.subtext2{
	color: #787878;
}

.subtext3{
	color: #bababa;
	font-size: 10px;
}


.startArea{
	-webkit-border-radius: 3px 0px 0px 3px;
	border-radius: 3px 0px 0px 3px;
}

.editArea{
	text-align: center;
	width: 80px;
	border: 3px solid #161616;
	padding: 19px;
	float: left;
	cursor: pointer;
	min-height: 105px;
}

.editArea:hover{
	background-color: #6cb055;
	border: 3px solid #376028;
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/selectedbg.png);
	background-repeat: no-repeat;
	background-position: center center;
}

.editCopy{
	padding-top: 33px;
	padding-bottom: 5px !important;
}

.editAR{
	padding-top: 24px;
	padding-bottom: 14px !important;
}

.editVideo{
	padding-top: 21px;
	padding-bottom: 17px !important;
}

.editExtra{
	padding-top: 23px;
	padding-bottom: 15px !important;
}

.actvieArea{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/selectedbg.png);
	background-repeat: no-repeat;
	background-position: center center;
	background-color: #55923f;
	border: 3px solid #376028;
}

#bgTest{
	margin-top: 20px;
	min-height: 40px;
	border: 2px solid #393939;
	-webkit-border-radius: 3px;
	border-radius: 3px;
}

.bg1{
	background-color: #FFF;
}

.bg2{
	background-color: #DDD;
}

.bg3{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/bg/bg3.png);
}

.bg4{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/bg/bg4.png);
}

.bg5{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/bg/bg5.png);
}

.bg6{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/bg/bg6.png);
}

.bg7{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/bg/bg7.png);
}

.bg8{
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/lp/images/bg/bg8.png);
}

.topbar1{
	-webkit-border-radius: 3px 3px 0px 0px;
	border-radius: 3px 3px 0px 0px;
	height: 25px;
	margin-bottom: 4px;
	margin-top: -5px;
	margin-left: -9px;
	margin-right: -9px;
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/topbarBG.png);
	background-repeat: no-repeat;
	background-position: right top;
}

.videoHeader1{
	-webkit-border-radius: 3px 3px 0px 0px;
	border-radius: 3px 3px 0px 0px;
	height: 25px;
	margin-bottom: 4px;
	margin-top: -5px;
	margin-left: -9px;
	margin-right: -9px;
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/topbarBG.png);
	background-repeat: no-repeat;
	background-position: right top;
}

.optinHeader1{
	-webkit-border-radius: 3px 3px 0px 0px;
	border-radius: 3px 3px 0px 0px;
	height: 25px;
	margin-bottom: 4px;
	margin-top: -5px;
	margin-left: -9px;
	margin-right: -9px;
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/topbarBG.png);
	background-repeat: no-repeat;
	background-position: right top;
}

.btnHeader1{
	-webkit-border-radius: 3px 3px 0px 0px;
	border-radius: 3px 3px 0px 0px;
	height: 25px;
	margin-bottom: 4px;
	margin-top: -5px;
	margin-left: -9px;
	margin-right: -9px;
	background-image: url(<?php echo site_url(); ?>/wp-content/plugins/everleadwp/images/topbarBG.png);
	background-repeat: no-repeat;
	background-position: right top;
}

.banner{
	-webkit-border-radius: 3px 3px 3px 3px;
	border-radius: 3px 3px 3px 3px;
	/*height: 25px;*/
}

.top1{
	background-color: #2c2c2c;
}

.top2{
	background-color: #34637b;
}

.top3{
	background-color: #5e753b;
}

.top4{
	background-color: #88403d;
}

.top5{
	background-color: #7f5587;
}

.top6{
	background-color: #62504f;
}

.top7{
	background-color: #939393;
}

.helpImg{
	cursor: pointer;
}

.helpIMGr{
	border: 2px solid #2d2c2c;
	margin-top: 15px;
	text-align: right;
	margin-bottom: 15px;
	display: none;
}

.banImg{
	display: block;
	margin: 5px;
	-webkit-border-radius: 3px 3px 3px 3px;
	border-radius: 3px 3px 3px 3px;
	border: 2px solid #2d2c2c;
}

</style>