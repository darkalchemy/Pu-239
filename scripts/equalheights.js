/*--------------------------------------------------------------------
 * The following source code is a modified version of the original plugin "EqualHeights" for jQuery.
 *
 * JQuery Plugin: "EqualHeights"
 * by:	Scott Jehl, Todd Parker, Maggie Costello Wachs (http://www.filamentgroup.com)
 *
 * Copyright (c) 2009 Filament Group
 * Licensed under GPL (http://www.opensource.org/licenses/gpl-license.php)

 * JQuery Plugin : "EqualHeights-Light"
 * Modified by : Michael (http://www.webdevcodex.com)
 * Description : Does not use px-em dependencies based from the original version. Also fixes a small bug which does not allow divs to be of equal heights if there are more than 2 divs.

------------------------------------------------------------------------*/

jQuery.fn.equalheight = function() {
	jQuery(this).each(function(){
		var currentTallest = 0; //create currentTallest var
		
		//go through every child of the mother div
		jQuery(this).children().each(function(i){
			//keep checking every child's height and get the height of the tallest div											  
			if (jQuery(this).height() > currentTallest) { currentTallest = jQuery(this).height(); }
			
		});
		
		//set currentTallest as pixels
		currentTallest = currentTallest+"px";
		
		//If browser is Microsoft Internet explorer, then use css "height: yypx"
		if (jQuery.browser.msie && jQuery.browser.version == 6.0) { jQuery(this).children().css({'height': currentTallest}); }
		
		//use css "min-height: yypx"
		jQuery(this).children().css({'min-height': currentTallest}); 
	});
	return this;
};
