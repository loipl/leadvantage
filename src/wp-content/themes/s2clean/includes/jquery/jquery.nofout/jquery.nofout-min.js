(function(a){if(typeof a.noFOUT!=="function"){(a.noFOUT=function(){if(window!=top){if(typeof document.documentElement.style.MozTransform==="string"){document.write('<style type="text/css">body { visibility: hidden; }</style>');a(document).ready(function(){a("body").css("visibility","visible")})}else{if(document.documentMode&&typeof document.styleSheets==="object"){for(var c=0,d=null,b="";c<document.styleSheets.length;c++){if((d=document.styleSheets[c])&&typeof d.href==="string"&&(b=d.href)){if(b.match(/font-faces\.css/)&&typeof d.cssText==="string"&&d.cssText){document.write('<style type="text/css">'+d.cssText+"</style>")}}}}}}})()}})(jQuery);