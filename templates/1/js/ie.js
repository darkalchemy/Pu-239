
// Fixs for IE
/************** DOM READY --> Begin ***********************/

$(function() {
	var $collection = $('.categories ul li:nth-child(2n)');
	$collection.css('margin-right','0');
	
	var $last = $('ul.image-grid li:nth-child(3n)');
	$last.addClass('last');
	
	$('.tabs-1 .gamelist li:nth-child(1), .reviews .gamelist li:nth-child(1), .release .gamelist li:nth-child(1), .reviews-content .gamelist li:nth-child(1), .top-games .gamelist li:nth-child(1)').addClass('child1');
	$('.tabs-1 .gamelist li:nth-child(2), .release .gamelist li:nth-child(2), .reviews-content .gamelist li:nth-child(2), .top-games .gamelist li:nth-child(2)').addClass('child2');
	$('.release .gamelist li:nth-child(3), .reviews-content .gamelist li:nth-child(3)').addClass('child3');
	$('.release .gamelist li:nth-child(4)').addClass('child4');
	$('.release .gamelist li:nth-child(5)').addClass('child5');
	$('.release .gamelist li:nth-child(6)').addClass('child6');
	
	$('.categories ul li:nth-child(2)').css('border-top','none');
	
});

/************** DOM READY --> END *************************/