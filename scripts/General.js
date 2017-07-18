jQuery.preloadImages = function()
{
	for(var i = 0; i<arguments.length; i++)
	jQuery("<img>").attr("src", arguments[i]);
}
jQuery.preloadImages("pic/home.png", "pic/homeo.png", "pic/browse.png", "pic/browseo.png", "pic/search.png", "pic/searcho.png", "pic/upload.png", "pic/uploado.png", "pic/chat.png", "pic/chato.png", "pic/forum.png", "pic/forumo.png", "pic/top.png", "pic/topo.png", "pic/rules.png", "pic/ruleso.png", "pic/faq.png", "pic/faqo.png", "pic/links.png", "pic/linkso.png", "pic/staff.png", "pic/staffo.png");

jQuery(document).ready(function(){
	
	$("#iconbar li a").hover(
		function(){
			var iconName = $(this).children("img").attr("src");
			var origen = iconName.split(".png")[0];
			$(this).children("img").attr({src: "" + origen + "o.png"});
			$(this).css("cursor", "pointer");
			$(this).animate({ width: "100px" }, {queue:false, duration:"normal"} );
			$(this).children("span").animate({opacity: "show"}, "fast");
		}, 
		function(){
			var iconName = $(this).children("img").attr("src");
			var origen = iconName.split("o.")[0];
			$(this).children("img").attr({src: "" + origen + ".png"});			
			$(this).animate({ width: "57px" }, {queue:false, duration:"normal"} );
			$(this).children("span").animate({opacity: "hide"}, "fast");
		});
});