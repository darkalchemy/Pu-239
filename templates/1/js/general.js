if($.browser.mozilla||$.browser.opera ){document.removeEventListener("DOMContentLoaded",jQuery.ready,false);document.addEventListener("DOMContentLoaded",function(){jQuery.ready()},false)}
jQuery.event.remove( window, "load", jQuery.ready );
jQuery.event.add( window, "load", function(){jQuery.ready();} );
jQuery.extend({
	includeStates:{},
	include:function(url,callback,dependency){
		if ( typeof callback!='function'&&!dependency){
			dependency = callback;
			callback = null;
		}
		url = url.replace('\n', '');
		jQuery.includeStates[url] = false;
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.onload = function () {
			jQuery.includeStates[url] = true;
			if ( callback )
				callback.call(script);
		};
		script.onreadystatechange = function () {
			if ( this.readyState != "complete" && this.readyState != "loaded" ) return;
			jQuery.includeStates[url] = true;
			if ( callback )
				callback.call(script);
		};
		script.src = url;
		if ( dependency ) {
			if ( dependency.constructor != Array )
				dependency = [dependency];
			setTimeout(function(){
				var valid = true;
				$.each(dependency, function(k, v){
					if (! v() ) {
						valid = false;
						return false;
					}
				})
				if ( valid )
					document.getElementsByTagName('body')[0].appendChild(script);
				else
					setTimeout(arguments.callee, 10);
			}, 10);
		}
		else
			document.getElementsByTagName('body')[0].appendChild(script);
		return function(){
			return jQuery.includeStates[url];
		}
	},

	readyOld: jQuery.ready,
	ready: function () {
		if (jQuery.isReady) return;
		imReady = true;
		$.each(jQuery.includeStates, function(url, state) {
			if (! state)
				return imReady = false;
		});
		if (imReady) {
			jQuery.readyOld.apply(jQuery, arguments);
		} else {
			setTimeout(arguments.callee, 10);
		}
	}
});

/************** Include Javascript Files ***************/

$.include('templates/1/js/jquery.smoothmenu.js')
$.include('templates/1/js/jflickrfeed.min.js');




/************* Sliders *************/

/* Nivo Slider --> Begin */
	if ($('#imageSlider').length) {
		$.include('sliders/nivo-slider/jquery.nivo.slider.js');
		jQuery(window).load(function(){
			jQuery('#imageSlider').nivoSlider({ 
				effect:'random',
				animSpeed:600,
				startSlide: 0,
				pauseTime:4000,
				captionOpacity: 1, 
				pauseOnHover:true,
				directionNav: false,
				directionNavHide:false
			});
		});
	}
/* Nivo Slider --> End */


/* Load Google Fonts --> Begin */
	WebFontConfig = {
		google: {families: ['Oswald::latin','Open+Sans:300:latin','PT+Sans+Narrow::latin','Nova+Square::latin','Lobster::latin']}
	  };
	  (function() {
		var wf = document.createElement('script');
		wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
			'://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
		wf.type = 'text/javascript';
		wf.async = 'true';
		var s = document.getElementsByTagName('body')[0];
		s.appendChild(wf, s);
	  })();
/* Load Google Fonts --> End */


/* Panel --> Begin */
//$.include('templates/1/themeChanger/js/colorpicker.js')
//$.include('templates/1/themeChanger/js/themeChanger.js')
/* Panel --> End */


/*********************** DOM READY --> Begin ****************************/

jQuery(document).ready(function(){
	
/* Smoothmenu --> Begin */
	ddsmoothmenu.init({
		mainmenuid: "navigation",
		orientation: "h", 
		classname: "navigation", 
		contentsource: "markup" 
	});
	
	ddsmoothmenu.init({
		mainmenuid: "platform-menu", 
		orientation: "h", 
		classname: "platform-menu", 
		contentsource: "markup" 
	});
/* Smoothmenu --> End */


/* awShowcase --> Begin */
if($('#showcase').length) {
$("#showcase").awShowcase(
	{
		content_width:			770,
		content_height:			500,
		fit_to_parent:			false,
		auto:					false,
		interval:				6000,
		continuous:				false,
		loading:				true,
		tooltip_width:			250,
		tooltip_icon_width:		32,
		tooltip_icon_height:	32,
		tooltip_offsetx:		18,
		tooltip_offsety:		0,
		arrows:					false,
		buttons:				false,
		btn_numbers:			true,
		keybord_keys:			true,
		mousetrace:				false, /* Trace x and y coordinates for the mouse */
		pauseonover:			true,
		stoponclick:			true,
		transition:				'fade', /* hslide/vslide/fade */
		transition_delay:		300,
		transition_speed:		500,
		show_caption:			'show', /* onload/onhover/show */
		thumbnails:				true,
		thumbnails_position:	'outside-last', /* outside-last/outside-first/inside-last/inside-first */
		thumbnails_direction:	'vertical', /* vertical/horizontal */
		thumbnails_slidex:		0, /* 0 = auto / 1 = slide one thumbnail / 2 = slide two thumbnails / etc. */
		dynamic_height:			false, /* For dynamic height to work in webkit you need to set the width and height of images in the source. Usually works to only set the dimension of the first slide in the showcase. */
		speed_change:			true, /* Set to true to prevent users from swithing more then one slide at once. */
		viewline:				false /* If set to true content_width, thumbnails, transition and dynamic_height will be disabled. As for dynamic height you need to set the width and height of images in the source. */
	});	
}

/* awShowcase --> End */	


/* Query data-rel to rel --> Begin */
	if ($("a[data-rel]").length) {
		$('a[data-rel]').each(function() {$(this).attr('rel', $(this).data('rel'));});
	}	
/* Query data-rel to rel --> End */	


/* Rating --> Begin */
	if($('.star').length) {$('.star').raty({half:  true,start: 3});}
/* Rating --> End */


/* jPlayer --> Begin */
	if($('#jplayer').length) {
		$("#jplayer").jPlayer({
			ready: function () {
				$(this).jPlayer("setMedia", {
					m4v: "http://xhtml.webtemplatemasters.com/games/video/cod.m4v",
					ogv: "http://xhtml.webtemplatemasters.com/games/video/cod.ogv",
					poster: "http://xhtml.webtemplatemasters.com/games/video/poster.png"
				});
			},
			play: function() { // To avoid both jPlayers playing together.
				$(this).jPlayer("pauseOthers");
			},
			repeat: function(event) { // Override the default jPlayer repeat event handler
				if(event.jPlayer.options.loop) {
					$(this).unbind(".jPlayerRepeat").unbind(".jPlayerNext");
					$(this).bind($.jPlayer.event.ended + ".jPlayer.jPlayerRepeat", function() {
						$(this).jPlayer("play");
					});
				} else {
					$(this).unbind(".jPlayerRepeat").unbind(".jPlayerNext");
					$(this).bind($.jPlayer.event.ended + ".jPlayer.jPlayerNext", function() {
						$("#jquery_jplayer_2").jPlayer("play", 0);
					});
				}
			},
			swfPath: "js",
			solution: "html, flash",
			supplied: "ogv, m4v"
		});
	}
/* jPlayer --> End */


/* Twitter--> Begin */
	if($('#jstwitter').length) {JQTWEET.loadTweets();}
/* Twitter--> End */


/* Flickr Photos --> Begin */	
	jQuery('ul#flickr-badge').jflickrfeed({
		limit: 6,
		qstrings: {
		id: '64078429@N06'
	},
	itemTemplate: '<li><a href="http://www.flickr.com/photos/64078429@N06"><img src="{{image_s}}" alt="{{title}}" /></a></li>'
	}, function() {$('#flickr-badge li:nth-child(3n)').addClass('last');});
/* Flickr Photos --> End */

/* Scrollpane --> Begin */
	if($('#scroll-pane').length) {$('#scroll-pane').jScrollPane({showArrows: true});}
/* Scrollpane --> End */	


/* Ui --> Begin */
	$(function() {
		$(".scroller_wrap").each(function() {
			slider_init(this)
		})
	});

	function slider_init(obj) {
		//scrollpane parts
		var scrollPane = $(obj),
			scrollContent = $(".scroller_block ul", obj);

		//build slider
		var scrollbar = $(".scroller_slider_bar", obj).slider({
			slide: function( event, ui ) {
				slide(ui.value, scrollPane, scrollContent)
			}
		});
		
		var handle = $('.ui-slider-handle', obj);
		
		var handleLeft = parseInt(handle.css('left'));
		
		var prev = $('.scroller_slider_prev', obj);
		var next = $('.scroller_slider_next', obj);
		
		if(handleLeft == 0) {
			prev.addClass('disabled');
		}

		var handleSize = $(".ui-slider-handle", obj).width();
		
		//append icon to handle
		var handleHelper = scrollbar.find(".ui-slider-handle", obj)
		
		.css({
			width: handleSize,
			"margin-left": '-30px'
		})
		.wrap( "<div class='ui-handle-helper-parent'></div>" ).parent()
		.width('').width( scrollbar.width() - handleSize);
		

		//change overflow to hidden now that slider handles the scrolling
		scrollPane.css( "overflow", "hidden" );

		$(".scroller_slider_prev, .scroller_slider_next", obj).mousedown(function() {
			var delta = $(this).hasClass('scroller_slider_prev') ? -10 : 10
			scrollbar.slider("value", scrollbar.slider("value") + delta);
			slide(scrollbar.slider("value"), scrollPane, scrollContent);
		})
		
		$(".scroller_slider_prev, .scroller_slider_next").click(function() {
			return false;
		})

		scrollPane.mousewheel(function(e, delta) {
			e.preventDefault()

			var delta = delta > 0 ? -10 : 10

			scrollbar.slider("value", scrollbar.slider("value") + delta);

			slide(scrollbar.slider("value"), scrollPane, scrollContent)
		});
	}

	function slide(val, scrollPane, scrollContent) {
		if ( scrollContent.width() > scrollPane.width() ) {
			scrollContent.css( "margin-left", Math.round(
				val / 100 * ( scrollPane.width() - scrollContent.width() )
			) + "px" );
		} else {
			scrollContent.css( "margin-left", 0 );
		}
	}

/* Ui --> End */


/* Pics --> Begin */	
	if (jQuery(".pics").length) {
		jQuery('.pics').cycle({ 	
			fx:     'scrollHorz', 
			timeout: 0, 
			next:   '.next',
			prev:   '.prev',
			easing: 'easeOutQuint'
		});
	}
/* Pics --> End */


/* Tables --> Begin */
	var $table = $('table.feature-table', this);
	$('table.feature-table thead tr th:first-child').addClass('leftR');
	$('table.feature-table thead tr th:last-child').addClass('rightR');
		$table.find('tbody tr:odd').addClass('odd');
/* Tables --> End */


/* Scroll to Top --> Begin */
	$('a[href=#top]').click(function(){
		$('html, body').animate({scrollTop:0}, 'slow');
		return false;
	});
/* Scroll to Top --> End */


/* Notifications --> Begin */
	function handler(event) {
		var $target = $(event.target);
		if($target.is('.close-box')) {
			var $box = $target.parent();
			$box.animate({opacity: '0'}, 500, function() {
				$(this).slideUp(500, function() {
					$(this).remove();
				});
			});	
		}
	}
		
	$('.custom-box-wrap').append('<span class="close-box">&times;</span>').click(handler);
/* Notifications --> End */


/* To top --> Begin */
    $BackTop = $('#back-top');
    var animating = false;
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100 && !animating) {
            $BackTop.fadeIn(1000);
        } else {
            $BackTop.fadeOut(1000);
        }
    });

    $('#back-top a').click(function () {
        $BackTop.fadeOut(400);
        animating = true;
        $('body,html').animate({
            scrollTop: 0
        }, 800, function() {
            animating = false;
        });
        return false;
    });
/* To top --> End */


/* Image wrapper --> Begin */
    function handle_image(img) {
        var $curtain = $('<span class="curtain">&nbsp</span>');
        img.after($curtain);        
    }
	
    $img_collection = $('.zoomer img, .zoom img');
    $img_collection.each(function() {
        handle_image($(this));
    });
/* Image wrapper --> End */


/* Video loading Fancybox --> Begin */
$(".video-icon .zoomer").click(function() {
		$.fancybox({
			'padding'		: 0,
			'autoScale'		: false,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'title'			: this.title,
			'width'			: 640,
			'height'		: 385,
			'href'			: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
			'type'			: 'swf',
			'swf'			: {
			'wmode'				: 'transparent',
			'allowfullscreen'	: 'true'
			}
		});
		return false;
	});
/* Video loading fancybox --> End */
	
	
/* Prepare loading fancybox --> Begin */
	if($('.zoomer').length) {
		jQuery('.zoomer').fancybox({
			'overlayShow'	: false,
			'transitionIn'	: 'elastic',
			'transitionOut'	: 'elastic'
		});
	}	
/* Prepare loading fancybox --> End */


/* Tabs --> Begin */
	var $tabs1 = $('.tabs1');
	var $tabs2 = $('.tabs2');
	var $tabs3 = $('.tabs3');
	var tabs1 = $('.tabs-1');
	var tabs2 = $('.tabs-2');
	var $tabsfoot = $('#tabs-footer');
	
	$.fn.tabs = function(link) {
		$(link).find("ul.tabs-nav li:first").addClass("active").show(); //Activate first tab
		$(link).find(".tabs-container .tab-content:first").show(); //Show first tab content
		//On Click Event
		$(link).find('ul.tabs-nav li').click(function() {

			$(link).find('ul.tabs-nav li').removeClass("active"); //Remove any "active" class
			$(this).addClass("active"); //Add "active" class to selected tab
			link.find('.tab-content').hide(); //Hide all tab content

			var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
			$(activeTab).fadeIn('normal'); //Fade in the active ID content
			return false;
		});
	};
	$tabs1.tabs($tabs1);
	$tabs2.tabs($tabs2);
	$tabs3.tabs($tabs3);
	tabs1.tabs(tabs1);
	tabs2.tabs(tabs2);
	$tabsfoot.tabs($tabsfoot);
/* Tabs --> End */


/* Toggle --> Begin */
	if($('.toggle-container').length) {	
		$(".toggle-container").hide(); //Hide (Collapse) the toggle containers on load
		//Switch the "Open" and "Close" state per click then slide up/down (depending on open/close state)
		$("b.trigger").click(function(){
			$(this).toggleClass("active").next().slideToggle("slow");
			return false; //Prevent the browser jump to the link anchor
		});
	}
/* Toggle --> End */


/* Gallery --> Begin */
if($('#gallery').length) {
	var $filterType = $('#filter a');
	var $list = $('#gallery');
	$list.css('height', $(this).height()/2.5);
	var $data = $list.clone();
	$filterType.click(function(event) {
	if ($(this).attr('data-rel') == 'everyone') {
		var $sortedData = $data.find('li');
	} else {
		$sortedData = $data.find('.'+ $(this).attr('data-rel'));
	}
	$('#filter li').removeClass('active');
	$(this).parent('li').addClass('active');

	$list.quicksand($sortedData, {
		attribute: 'id',
		duration: 500,
		easing: 'easeInOutQuad',
		adjustHeight: 'auto',
		useScaling: 'true'
	}, function(){
		$('.image-grid li').removeClass('last'); 
		$('.image-grid li:nth-child(3n)').addClass('last');
		$(".video-icon .zoomer").click(function() {
				$.fancybox({
					'padding'		: 0,
					'autoScale'		: false,
					'transitionIn'	: 'none',
					'transitionOut'	: 'none',
					'title'			: this.title,
					'width'			: 640,
					'height'		: 385,
					'href'			: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
					'type'			: 'swf',
					'swf'			: {
					'wmode'				: 'transparent',
					'allowfullscreen'	: 'true'
					}
				});
				return false;
			});
	});
	return false;
});	
}

/* Gallery --> End */


});/************** DOM READY --> End ***********************/
