(function(a){if(typeof a.iFlash!=="function"){a.iFlash=function(b){var c,d={id:"iflash-"+Math.round(0+Math.random()*(10000000-0)),src:"",width:"100%",height:"400",bgcolor:"",wmode:"transparent",flashvars:"",quality:"high",scale:"",menu:"",allowfullscreen:"true",swliveconnect:"true",allowscriptaccess:"always",_return:false};b=a.extend(true,{},d,b),c='<embed type="application/x-shockwave-flash" id="'+b.id+'" src="'+b.src+'" width="'+b.width+'" height="'+b.height+'" bgcolor="'+b.bgcolor+'" wmode="'+b.wmode+'" flashvars="'+b.flashvars+'" quality="'+b.quality+'" scale="'+b.scale+'" menu="'+b.menu+'" allowfullscreen="'+b.allowfullscreen+'" swliveconnect="'+b.swliveconnect+'" allowscriptaccess="'+b.allowscriptaccess+'" pluginspage="//www.macromedia.com/go/getflashplayer"></embed>';if(b.src&&b.width&&b.height){if(b._return){return c}document.write(c)}else{if(b._return){return""}}}}})(jQuery);