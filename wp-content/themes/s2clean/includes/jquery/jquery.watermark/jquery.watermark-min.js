(function(a){if(typeof a.fn.watermark!=="function"){document.write('<style type="text/css">.watermarked { color: #999999; font-style: italic; }</style>');a.fn.watermark=function(d){var f=this,g,c,b,e={source:"title",watermarkedClass:"watermarked"};g=d=a.extend(true,{},e,d);c={title:function(h){return a.trim(a(h).attr("title"))},label:function(h){return a('label[for="'+h.id+'"]').text()}};b=function(){f.each(function(){var i=a(this),h=a.trim(i.val());if(h===""||h===i.data("watermark")){i.val(""),i.removeClass(g.watermarkedClass)}})};f.parents("form").submit(b),a(window).unload(b);return f.each(function(){var k=a(this),j=a.trim(k.val()),h,i;if(typeof(i=g.source)==="function"||typeof(i=c[g.source])==="function"){if(a.trim(h=i(this))!==""){k.data("watermark",h.replace(/[\r\n\t]+/g,""));if(j===""){k.val(k.data("watermark")),k.addClass(g.watermarkedClass)}k.focus(function(){var m=a(this),l=a.trim(m.val());if(l===""||l===m.data("watermark")){m.val(""),m.removeClass(g.watermarkedClass)}});k.blur(function(){var m=a(this),l=a.trim(m.val());if(l===""||l===m.data("watermark")){m.val(m.data("watermark")),m.addClass(g.watermarkedClass)}})}}else{return false}})}}})(jQuery);