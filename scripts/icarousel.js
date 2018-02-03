/**
 * jQuery iCarousel v1.2.2
 *
 * @version: 1.2.2 - November 06, 2012
 * @version: 1.2.1 - August 29, 2012
 * @version: 1.2 - July 04, 2012
 * @version: 1.1 - June 15, 2012
 * @version: 1.0 - May 25, 2012
 *
 * @author: Hemn Chawroka
 *          hemn@iprodev.com
 *          http://iprodev.com/
 *
 */
(function ($) {

    var iCarousel = function (el, slides, options) {
        var ic = this;
        ic.el = el, ic.slides = slides, ic.options = options;

        //Necessary variables
        ic.defs = {
            degree: 0,
            total: slides.length,
            images: [],
            interval: null,
            timer: options.timer.toLowerCase(),
            dir: options.direction.toLowerCase(),
            pause: (options.autoPlay) ? false : true,
            slide: 0,
            currentSlide: null,
            width: el.width(),
            height: el.height(),
            space: options.slidesSpace,
            topSpace: options.slidesTopSpace,
            lock: false,
            easing: 'ease-in-out',
            time: options.pauseTime
        };

        //Disable text selection
        ic.disableSelection(el[0]);

        slides.each(function (i) {
            var slide = $(this);
            if (slide.height() == 300) {
                slide.attr({
                    'data-outerwidth': slide.outerWidth(),
                    'data-outerheight': slide.outerHeight(),
                    'data-width': slide.width(),
                    'data-height': slide.height(),
                    'index': i
                }).css({
                    visibility: 'hidden'
                });
            } else {
                delete slide;
            }
        });

        //Find images
        var images = $('img', el);
        images.each(function (i) {
            var image = $(this);
            ic.defs.images.push(image.attr("src"));
        });

        //If randomStart
        options.startSlide = (options.randomStart) ? Math.floor(Math.random() * ic.defs.total) : options.startSlide;

        //Set startSlide
        options.startSlide = (options.startSlide < 0 || options.startSlide > ic.defs.total) ? 0 : options.startSlide;
        ic.defs.slide = options.startSlide;

        //Set initial currentSlide
        ic.defs.currentSlide = slides.eq(ic.defs.slide);

        //Set initial pauseTime
        ic.defs.time = (ic.defs.currentSlide.data('pausetime')) ? ic.defs.currentSlide.data('pausetime') : options.pauseTime;

        //Fix slides number
        options.slides = (options.slides > ic.defs.total) ? ic.defs.total : options.slides;
        options.slides = (options.slides % 2) ? options.slides : options.slides - 1;

        //Set Preloader Element
        el.append('<div id="iCarousel-preloader"><div></div></div>');
        var iCarouselPreloader = $('#iCarousel-preloader', el);
        var preloaderBar = $('div', iCarouselPreloader);
        iCarouselPreloader.css({
            top: ((ic.defs.height / 2) - (iCarouselPreloader.height() / 2)) + 'px',
            left: ((ic.defs.width / 2) - (iCarouselPreloader.width() / 2)) + 'px'
        });

        //Set Timer Element
        el.append('<div id="iCarousel-timer"><div></div></div>');
        ic.iCarouselTimer = $('#iCarousel-timer', el);
        ic.iCarouselTimer.hide();
        ic.barTimer = $('div', ic.iCarouselTimer);

        var padding = options.timerPadding,
            diameter = options.timerDiameter,
            stroke = options.timerStroke;

        if (ic.defs.total > 1 && ic.defs.timer != "bar") {
            //Start the Raphael
            stroke = (ic.defs.timer == "360bar") ? options.timerStroke : 0;
            var width = (diameter + (padding * 2) + (stroke * 2)),
                height = width,
                r = Raphael(ic.iCarouselTimer[0], width, height),
                R = (diameter / 2),
                param = {
                    stroke: options.timerBg,
                    "stroke-width": (stroke + (padding * 2))
                },
                param2 = {
                    stroke: options.timerColor,
                    "stroke-width": stroke,
                    "stroke-linecap": "round"
                },
                param3 = {
                    fill: options.timerColor,
                    stroke: 'none',
                    "stroke-width": 0
                },
                bgParam = {
                    fill: options.timerBg,
                    stroke: 'none',
                    "stroke-width": 0
                };
            ic.R = R;

            // Custom Arc Attribute, repreference Raphael arc function
            r.customAttributes.arc = function (value, R) {
                var total = 360,
                    alpha = 360 / total * value,
                    a = (90 - alpha) * Math.PI / 180,
                    cx = ((diameter / 2) + padding + stroke),
                    cy = ((diameter / 2) + padding + stroke),
                    x = cx + R * Math.cos(a),
                    y = cy - R * Math.sin(a),
                    path;
                if (total == value) {
                    path = [["M", cx, cy - R], ["A", R, R, 0, 1, 1, 299.99, cy - R]];
                } else {
                    path = [["M", cx, cy - R], ["A", R, R, 0, +(alpha > 180), 1, x, y]];
                }
                return {
                    path: path
                };
            };

            // Custom Segment Attribute, repreference Raphael segment function
            r.customAttributes.segment = function (angle, R) {
                var a1 = -90;
                R = R - 1;
                angle = (a1 + angle);
                var flag = (angle - a1) > 180,
                    x = ((diameter / 2) + padding),
                    y = ((diameter / 2) + padding);
                a1 = (a1 % 360) * Math.PI / 180;
                angle = (angle % 360) * Math.PI / 180;
                return {
                    path: [["M", x, y], ["l", R * Math.cos(a1), R * Math.sin(a1)], ["A", R, R, 0, +flag, 1, x + R * Math.cos(angle), y + R * Math.sin(angle)], ["z"]]
                };
            };

            if (ic.defs.total > 1 && ic.defs.timer == "pie") {
                r.circle(R + padding, R + padding, R + padding - 1).attr(bgParam);
            }
            ic.timerBgPath = r.path().attr(param), ic.timerPath = r.path().attr(param2), ic.pieTimer = r.path().attr(param3);
        }

        if (ic.defs.total > 1 && ic.defs.timer == "360bar") {
            ic.timerBgPath.attr({
                arc: [359.9, R]
            });
        }

        //Set Timer Styles
        if (ic.defs.timer == "bar") {
            ic.iCarouselTimer.css({
                opacity: options.timerOpacity,
                width: diameter,
                height: stroke,
                border: options.timerBarStroke + 'px ' + options.timerBarStrokeColor + ' ' + options.timerBarStrokeStyle,
                padding: padding,
                background: options.timerBg
            });
            ic.barTimer.css({
                width: 0,
                height: stroke,
                background: options.timerColor,
                'float': 'left'
            });
        } else {
            ic.iCarouselTimer.css({
                opacity: options.timerOpacity,
                width: width,
                height: height
            });
        }

        //Set Timer Position
        var position = options.timerPosition.toLowerCase().split('-');
        for (var i = 0; i < position.length; i++) {
            if (position[i] == 'top') {
                ic.iCarouselTimer.css({
                    top: options.timerY + 'px',
                    bottom: ''
                });
            } else if (position[i] == 'middle') {
                ic.iCarouselTimer.css({
                    top: (options.timerY + (ic.defs.height / 2) - (options.timerDiameter / 2)) + 'px',
                    bottom: ''
                });
            } else if (position[i] == 'bottom') {
                ic.iCarouselTimer.css({
                    bottom: options.timerY + 'px',
                    top: ''
                });
            } else if (position[i] == 'left') {
                ic.iCarouselTimer.css({
                    left: options.timerX + 'px',
                    right: ''
                });
            } else if (position[i] == 'center') {
                ic.iCarouselTimer.css({
                    left: (options.timerX + (ic.defs.width / 2) - (options.timerDiameter / 2)) + 'px',
                    right: ''
                });
            } else if (position[i] == 'right') {
                ic.iCarouselTimer.css({
                    right: options.timerX + 'px',
                    left: ''
                });
            }
        }

        //Set initial easing
        ic.defs.easing = ic.setEasing(options.easing);


        // Run Preloader
        if (ic.defs.images.length > 0) new ImagePreload(ic.defs.images, function (i) {
            var percent = (i * 10);
            preloaderBar.stop().animate({
                width: percent + '%'
            });
        }, function () {
            preloaderBar.stop().animate({
                width: '100%'
            }, function () {
                iCarouselPreloader.remove();
                ic.init();

                //Trigger the onAfterLoad callback
                options.onAfterLoad.call(this);
            });
        }); else {
            iCarouselPreloader.remove();
            ic.init();

            //Trigger the onAfterLoad callback
            options.onAfterLoad.call(this);
        }
    };


    //Start the iCarousel
    iCarousel.prototype = {
        rightItems: new Array(),
        leftItems: new Array(),
        rightOutItem: null,
        leftOutItem: null,

        //Browser capabilities checker functions
        support: {

            //CSS3 Transform3D support
            transform3d: function () {
                var props = ['perspectiveProperty', 'WebkitPerspective', 'MozPerspective', 'OPerspective', 'msPerspective'],
                    i = 0,
                    support = false,
                    form = document.createElement('form');

                while (props[i]) {
                    if (props[i] in form.style) {
                        support = true;
                        break;
                    }
                    i++;
                }
                return support;
            },

            //CSS3 Transform2D support
            transform2d: function () {
                var props = ['transformProperty', 'WebkitTransform', 'MozTransform', 'OTransform', 'msTransform'],
                    i = 0,
                    support = false,
                    form = document.createElement('form');

                while (props[i]) {
                    if (props[i] in form.style) {
                        support = true;
                        break;
                    }
                    i++;
                }
                return support;
            },

            //CSS3 Transistion support
            transition: function () {
                var props = ['transitionProperty', 'WebkitTransition', 'MozTransition', 'OTransition', 'msTransition'],
                    i = 0,
                    support = false,
                    form = document.createElement('form');

                while (props[i]) {
                    if (props[i] in form.style) {
                        support = true;
                        break;
                    }
                    i++;
                }
                return support;
            },

            //touch
            touch: function () {
                return !!('ontouchstart' in window);
            }
        },

        //Initial function
        init: function () {
            var ic = this;

            if (ic.options.directionNav) ic.setButtons();
            ic.layout();
            ic.events();

            ic.iCarouselTimer.attr('title', ic.options.playLabel).addClass('paused').show();

            //Start the timer
            if (ic.options.autoPlay && ic.defs.total > 1) {
                ic.setTimer();
                ic.iCarouselTimer.attr('title', ic.options.pauseLabel).removeClass('paused');
            }
        },

        //Switch slide function
        goSlide: function (index, motionless, fastchange) {
            var ic = this;

            //Trigger the onLastSlide callback
            if (ic.defs && (ic.defs.slide == ic.defs.total - 1)) {
                ic.options.onLastSlide.call(this);
            }

            ic.clearTimer();

            //Trigger the onBeforeChange callback
            ic.options.onBeforeChange.call(this);

            //Set slide
            ic.defs.slide = (index < 0 || index > ic.defs.total - 1) ? 0 : index;

            //Trigger the onSlideShowEnd callback
            if (ic.defs.slide == ic.defs.total - 1) {
                ic.options.onSlideShowEnd.call(this);
            }

            ic.defs.currentSlide = ic.slides.eq(ic.defs.slide);

            //Custom easing as defined by "data-easing" attribute
            ic.defs.easing = (ic.defs.currentSlide.data('easing')) ? ic.setEasing($.trim(ic.defs.currentSlide.data('easing'))) : ic.setEasing(ic.options.easing);

            //Set the currentSlide pausetime
            ic.defs.time = (ic.defs.currentSlide.data('pausetime')) ? ic.defs.currentSlide.data('pausetime') : ic.options.pauseTime;
            var animSpeed = (fastchange) ? (ic.options.animationSpeed / (fastchange)) : false;

            ic.slides.removeClass('current');

            //Start Transition
            ic.defs.lock = true;

            ic.layout(true, animSpeed);

            if (fastchange) return false;

            ic.resetTimer();

            //Triger when animations finished
            setTimeout(function () {
                ic.animationEnd(ic);
            }, ic.options.animationSpeed);
        },

        //goFar function
        goFar: function (index) {
            var ic = this,
                diff = (index == ic.defs.total - 1 && ic.defs.slide == 0) ? -1 : (index - ic.defs.slide);
            if (ic.defs.slide == ic.defs.total - 1 && index == 0) diff = 1;
            var diff2 = (diff < 0) ? -diff : diff,
                timeBuff = 0;

            for (var i = 0; i < diff2; i++) {
                var timeout = (diff2 == 1) ? 0 : (timeBuff);
                setTimeout(function () {
                    (diff < 0) ? ic.goPrev(diff2) : ic.goNext(diff2);
                }, timeout);
                timeBuff += (ic.options.animationSpeed / (diff2));
            }
            setTimeout(function () {
                ic.animationEnd(ic);
            }, ic.options.animationSpeed);

            ic.resetTimer();

        },

        //Triger when animation finished
        animationEnd: function (ic) {
            ic.defs.lock = false;
            ic.defs.degree = 0;

            //Restart the interval
            if (ic.defs.interval == null && !ic.defs.pause) ic.setTimer();

            //Trigger the onAfterChange callback
            ic.options.onAfterChange.call(this);
        },

        //Timer processor
        processTimer: function () {
            var ic = this;
            if (ic.defs.timer == "360bar") {
                var degree = (ic.defs.degree == 0) ? 0 : ic.defs.degree - .9;
                ic.timerPath.attr({
                    arc: [degree, ic.R]
                });
            } else if (ic.defs.timer == "pie") {
                var degree = (ic.defs.degree == 0) ? 0 : ic.defs.degree - .9;
                ic.pieTimer.attr({
                    segment: [degree, ic.R]
                });
            } else {
                ic.barTimer.css({
                    width: ((ic.defs.degree / 360) * 100) + '%'
                });
            }
            ic.defs.degree += 4;
        },

        //Reset Timer
        resetTimer: function () {
            var ic = this;
            if (ic.defs.total > 1) {
                if (ic.defs.timer == "360bar") {
                    ic.timerPath.animate({
                        arc: [0, ic.R]
                    }, ic.options.animationSpeed);
                } else if (ic.defs.timer == "pie") {
                    ic.pieTimer.animate({
                        segment: [0, ic.R]
                    }, ic.options.animationSpeed);
                } else {
                    ic.barTimer.animate({
                        width: 0
                    }, ic.options.animationSpeed);
                }
            }
        },

        //Interval timer call function
        timerCall: function (ic) {
            ic.processTimer();
            if (ic.defs.degree > 360) {
                ic.goNext();
            }
        },

        //Set the timer function
        setTimer: function () {
            var ic = this;
            ic.defs.interval = setInterval(function () {
                ic.timerCall(ic);
            }, (ic.defs.time / 90));
        },

        //Clean the timer function
        clearTimer: function () {
            var ic = this;
            clearInterval(ic.defs.interval);
            ic.defs.interval = null;
            ic.defs.degree = 0;
        },

        //Items layout shower function
        layout: function (animate, speedTime) {
            var ic = this;

            //Set sides items
            ic.setItems();

            //Set initial slides styles
            var slideTop = (ic.defs.topSpace == "auto") ? ((ic.defs.height / 2) - (ic.defs.currentSlide.data('outerheight') / 2)) : 0,
                slideLeft = ((ic.defs.width / 2) - (ic.defs.currentSlide.data('outerwidth') / 2)),
                center = (ic.defs.width / 4),
                zIndex = 999,
                css = {},
                anim = {},
                speed = (speedTime) ? (speedTime / 1000) : (ic.options.animationSpeed / 1000);

            if (animate && ic.support.transition()) ic.slides.css({
                '-webkit-transition': "all " + speed + "s " + ic.defs.easing,
                '-moz-transition': "all " + speed + "s " + ic.defs.easing,
                '-o-transition': "all " + speed + "s " + ic.defs.easing,
                '-ms-transition': "all " + speed + "s " + ic.defs.easing,
                'transition': "all " + speed + "s " + ic.defs.easing
            });

            ic.slides.css({
                position: 'absolute',
                opacity: 0,
                visibility: 'hidden',
                overflow: 'hidden'
            });

            if (ic.support.transition()) ic.slides.css({
                top: slideTop + 'px'
            });

            ic.defs.currentSlide.addClass('current').css({
                zIndex: zIndex,
                opacity: 1,
                visibility: 'visible'
            });

            if (ic.support.transition()) ic.defs.currentSlide.css({
                '-webkit-transform': 'none',
                '-moz-transform': 'none',
                '-o-transform': 'none',
                '-ms-transform': 'none',
                'transform': 'none',
//				left: slideLeft + 'px',
                top: slideTop + 'px',
                width: ic.defs.currentSlide.data('width') + "px",
                height: ic.defs.currentSlide.data('height') + "px"
            }); else {
                if (animate) ic.defs.currentSlide.stop().animate({
//					left: slideLeft + 'px',
                    top: slideTop + 'px',
                    width: ic.defs.currentSlide.data('width') + "px",
                    height: ic.defs.currentSlide.data('height') + "px"
                }, ic.options.animationSpeed, ic.defs.easing); else ic.defs.currentSlide.css({
//					left: slideLeft + 'px',
                    top: slideTop + 'px',
                    width: ic.defs.currentSlide.data('width') + "px",
                    height: ic.defs.currentSlide.data('height') + "px"
                });
            }


            for (var i = 0; i < ic.rightItems.length; i++) {
                var slide = ic.rightItems[i];
                zIndex -= i + 1, css = this.CSS(slide, i, zIndex, true);
                var opacity = (8 - i) / 10;
                if (ic.support.transition()) {
                    slide.css(css).css({
                        opacity: opacity,
                        visibility: 'visible',
                        zIndex: zIndex
                    });
                } else {
                    if (i == ic.rightItems.length - 1) css.opacity = opacity;
                    if (animate) slide.stop().animate(css, ic.options.animationSpeed, ic.defs.easing);
                    else slide.css(css);

                    if (i != ic.rightItems.length - 1) slide.css({
                        opacity: opacity
                    });

                    slide.css({
                        visibility: 'visible',
                        zIndex: zIndex
                    });
                }
            }

            for (var i = 0; i < ic.leftItems.length; i++) {
                var slide = ic.leftItems[i];
                zIndex -= i + 1, css = ic.CSS(slide, i, zIndex);
                var opacity = (8 - i) / 10;
                if (ic.support.transition()) slide.css(css);
                else {
                    if (animate) slide.stop().animate(css, ic.options.animationSpeed, ic.defs.easing);
                    else slide.css(css);
                }
                slide.css({
                    opacity: opacity,
                    visibility: 'visible',
                    zIndex: zIndex
                });
            }

            if (ic.defs.total > ic.options.slides) {
                var rCSS = ic.CSS(ic.rightOutItem, ic.leftItems.length - 0.5, ic.leftItems.length - 1, true),
                    lCSS = ic.CSS(ic.leftOutItem, ic.leftItems.length - 0.5, ic.leftItems.length - 1);
                if (ic.support.transition()) {
                    ic.rightOutItem.css(rCSS);
                    ic.leftOutItem.css(lCSS);
                } else {
                    if (animate) {
                        ic.leftOutItem.css({
                            opacity: 1,
                            visibility: 'visible'
                        });
                        ic.rightOutItem.css(rCSS);
                        lCSS.opacity = 0;
                        ic.leftOutItem.stop().animate(lCSS, ic.options.animationSpeed, ic.defs.easing);
                    } else {
                        ic.rightOutItem.css(rCSS);
                        ic.leftOutItem.css(lCSS);
                    }
                }
            }

        },

        //Set iCarousel items
        setItems: function () {
            var ic = this,
                num = Math.floor(ic.options.slides / 2) + 1;
            ic.leftItems = new Array();
            ic.rightItems = new Array();

            for (var i = 1; i < num; i++) {
                var eq = (ic.defs.dir == "ltr") ? (ic.defs.slide + i) % (ic.defs.total) : (ic.defs.slide - i) % (ic.defs.total);
                ic.leftItems.push(ic.slides.eq(eq));
            }

            for (var i = 1; i < num; i++) {
                var eq = (ic.defs.dir == "ltr") ? (ic.defs.slide - i) % (ic.defs.total) : (ic.defs.slide + i) % (ic.defs.total);
                ic.rightItems.push(ic.slides.eq(eq));
            }

            ic.leftOutItem = ic.slides.eq(ic.defs.slide - num);
            ic.rightOutItem = ((ic.defs.total - ic.defs.slide - num) <= 0) ? ic.slides.eq(-parseInt(ic.defs.total - ic.defs.slide - num)) : ic.slides.eq(ic.defs.slide + num);
            var leftOut = ic.leftOutItem,
                rightOut = ic.rightOutItem;
            if (ic.defs.dir == "ltr") {
                ic.leftOutItem = rightOut;
                ic.rightOutItem = leftOut;
            }
        },

        newDimenstions: function (width, height, width_old, height_old) {
            if (width == 0) factor = height / height_old;
            else if (height == 0) factor = width / width_old;
            else factor = Math.min(width / width_old, height / height_old);

            var final_width = Math.round(width_old * factor),
                final_height = Math.round(height_old * factor);

            return (factor > 1) ? {width: width_old, height: height_old, ratio: 1} : {
                width: final_width,
                height: final_height,
                ratio: factor
            };
        },

        //CSS style generator function
        CSS: function (slide, i, zIndex, positive) {
            var ic = this,
                leftRemain = (ic.defs.space == "auto") ? parseInt((i + 1) * (slide.data('width') / 1.5)) : parseInt((i + 1) * (ic.defs.space));
            if (ic.support.transform3d() && ic.options.make3D) {
                var transform = (positive) ? 'translateX(' + (leftRemain) + 'px) translateZ(-' + (250 + ((i + 1) * 110)) + 'px) rotateY(-' + ic.options.perspective + 'deg)' : 'translateX(-' + (leftRemain) + 'px) translateZ(-' + (250 + ((i + 1) * 110)) + 'px) rotateY(' + ic.options.perspective + 'deg)',
                    left = "0%",
                    top = (ic.defs.topSpace == "auto") ? "none" : parseInt((i + 1) * (ic.defs.space)),
                    width = "none",
                    height = "none",
                    overflow = "visible";
            } else if (ic.support.transition() && ic.support.transform2d()) {
                var transform = (positive) ? 'translateX(' + (leftRemain / 1.5) + 'px) scale(' + (1 - (i / 10) - 0.1) + ')' : 'translateX(-' + (leftRemain / 1.5) + 'px) scale(' + (1 - (i / 10) - 0.1) + ')',
                    left = "0%",
                    top = (ic.defs.topSpace == "auto") ? "none" : parseInt((i + 1) * (ic.defs.topSpace)),
                    width = "none",
                    height = "none",
                    overflow = "visible";
            } else {
                var wDiff = (slide.data('outerwidth') - slide.data('width')),
                    hDiff = (slide.data('outerheight') - slide.data('height')),
                    dims = ic.newDimenstions((slide.data('width') * (1 - (i / 10) - 0.1)), (slide.data('height') * (1 - (i / 10) - 0.1)), slide.data('width'), (slide.data('height'))),
                    leftRemain = (ic.defs.space == "auto") ? parseInt((i + 1) * (dims.width / 1.5)) : parseInt((i + 1) * (ic.defs.space)),
                    transform = '',
                    left = (positive) ? (((ic.defs.width / 2) - ((dims.width + hDiff) / 2)) - (leftRemain / 1.5)) + "px" : (((ic.defs.width / 2) - ((dims.width + hDiff) / 2)) + (leftRemain / 1.5)) + "px",
                    width = dims.width,
                    height = dims.height,
                    center = ((ic.defs.height / 2) - ((dims.height + hDiff) / 2)),
                    top = (ic.defs.topSpace == "auto") ? center : parseInt(center + (i * (ic.defs.topSpace))),
                    overflow = "hidden";
            }
            if (ic.support.transition()) css = {
                '-webkit-transform': transform,
                '-moz-transform': transform,
                '-o-transform': transform,
                '-ms-transform': transform,
                'transform': transform,
                left: left,
                top: top,
                width: width,
                height: height,
                zIndex: zIndex,
                overflow: overflow
            };
            else css = {
                left: left,
                top: top,
                width: width,
                height: height,
                zIndex: zIndex
            };
            return css;

        },

        //Set easing timing function
        setEasing: function (easing) {
            var ic = this;
            easing = $.trim(easing),
                ease = easing;

            switch (ease) {
                case 'linear':
                    ease = 'cubic-bezier(0.250, 0.250, 0.750, 0.750)';
                    break;
                case 'ease':
                    ease = 'cubic-bezier(0.250, 0.100, 0.250, 1.000)';
                    break;
                case 'ease-in':
                    ease = 'cubic-bezier(0.420, 0.000, 1.000, 1.000)';
                    break;
                case 'ease-out':
                    ease = 'cubic-bezier(0.000, 0.000, 0.580, 1.000)';
                    break;
                case 'ease-in-out':
                    ease = 'cubic-bezier(0.420, 0.000, 0.580, 1.000)';
                    break;
                case 'ease-out-in':
                    ease = 'cubic-bezier(0.000, 0.420, 1.000, 0.580)';
                    break;
                case 'easeInQuad':
                    ease = 'cubic-bezier(0.550, 0.085, 0.680, 0.530)';
                    break;
                case 'easeInCubic':
                    ease = 'cubic-bezier(0.550, 0.055, 0.675, 0.190)';
                    break;
                case 'easeInQuart':
                    ease = 'cubic-bezier(0.895, 0.030, 0.685, 0.220)';
                    break;
                case 'easeInQuint':
                    ease = 'cubic-bezier(0.755, 0.050, 0.855, 0.060)';
                    break;
                case 'easeInSine':
                    ease = 'cubic-bezier(0.470, 0.000, 0.745, 0.715)';
                    break;
                case 'easeInExpo':
                    ease = 'cubic-bezier(0.950, 0.050, 0.795, 0.035)';
                    break;
                case 'easeInCirc':
                    ease = 'cubic-bezier(0.600, 0.040, 0.980, 0.335)';
                    break;
                case 'easeInBack':
                    ease = 'cubic-bezier(0.600, -0.280, 0.735, 0.045)';
                    break;
                case 'easeOutQuad':
                    ease = 'cubic-bezier(0.250, 0.460, 0.450, 0.940)';
                    break;
                case 'easeOutCubic':
                    ease = 'cubic-bezier(0.215, 0.610, 0.355, 1.000)';
                    break;
                case 'easeOutQuart':
                    ease = 'cubic-bezier(0.165, 0.840, 0.440, 1.000)';
                    break;
                case 'easeOutQuint':
                    ease = 'cubic-bezier(0.230, 1.000, 0.320, 1.000)';
                    break;
                case 'easeOutSine':
                    ease = 'cubic-bezier(0.390, 0.575, 0.565, 1.000)';
                    break;
                case 'easeOutExpo':
                    ease = 'cubic-bezier(0.190, 1.000, 0.220, 1.000)';
                    break;
                case 'easeOutCirc':
                    ease = 'cubic-bezier(0.075, 0.820, 0.165, 1.000)';
                    break;
                case 'easeOutBack':
                    ease = 'cubic-bezier(0.175, 0.885, 0.320, 1.275)';
                    break;
                case 'easeInOutQuad':
                    ease = 'cubic-bezier(0.455, 0.030, 0.515, 0.955)';
                    break;
                case 'easeInOutCubic':
                    ease = 'cubic-bezier(0.645, 0.045, 0.355, 1.000)';
                    break;
                case 'easeInOutQuart':
                    ease = 'cubic-bezier(0.770, 0.000, 0.175, 1.000)';
                    break;
                case 'easeInOutQuint':
                    ease = 'cubic-bezier(0.860, 0.000, 0.070, 1.000)';
                    break;
                case 'easeInOutSine':
                    ease = 'cubic-bezier(0.445, 0.050, 0.550, 0.950)';
                    break;
                case 'easeInOutExpo':
                    ease = 'cubic-bezier(1.000, 0.000, 0.000, 1.000)';
                    break;
                case 'easeInOutCirc':
                    ease = 'cubic-bezier(0.785, 0.135, 0.150, 0.860)';
                    break;
                case 'easeInOutBack':
                    ease = 'cubic-bezier(0.680, 0, 0.265, 1)';
                    break;
            }

            if (ic.support.transition()) return ease;
            else {
                if (easing == "ease" || easing == "ease-in" || easing == "ease-out" || easing == "ease-in-out" || easing == "ease-out-in") easing = "";
                return easing;
            }
        },

        //goNext function for go to next slide
        goNext: function (fastchange) {
            var ic = this;
            fastchange = (fastchange) ? fastchange : false;
            if (!fastchange && ic.defs.lock) return false;
            (ic.defs.slide == ic.defs.total) ? ic.goSlide(0, false, fastchange) : ic.goSlide(ic.defs.slide + 1, false, fastchange);
        },

        //goPrev function for go to previous slide
        goPrev: function (fastchange) {
            var ic = this;
            fastchange = (fastchange) ? fastchange : false;
            if (!fastchange && ic.defs.lock) return false;
            (ic.defs.slide == 0) ? ic.goSlide(ic.defs.total - 1, false, fastchange) : ic.goSlide(ic.defs.slide - 1, false, fastchange);
        },

        //Events
        events: function () {
            var ic = this;

            // keyboard navigation handler
            if (ic.options.keyboardNav) $(document).bind('keyup.iCarousel', function (event) {
                switch (event.keyCode) {
                    case 33:
                        ; // pg up
                    case 37:
                        ; // left
                    case 38:
                        // up
                        ic.goPrev();
                        break;
                    case 34:
                        ; // pg down
                    case 39:
                        ; // right
                    case 40:
                        // down
                        ic.goNext();
                        break;
                }
            });

            // Navigation buttons
            $('a#iCarouselPrev', ic.el).click(function () {
                ic.goPrev();
            });
            $('a#iCarouselNext', ic.el).click(function () {
                ic.goNext();
            });

            //Play/Pause action
            ic.iCarouselTimer.click(function () {

                if (ic.iCarouselTimer.hasClass('paused')) {
                    ic.iCarouselTimer.removeClass('paused').attr('title', ic.options.pauseLabel);
                    ic.defs.pause = false;

                    //Restart the timer
                    if (ic.defs.interval == null) {
                        ic.setTimer();

                        //Trigger the onPlay callback
                        ic.options.onPlay.call(this);
                    }
                } else {
                    ic.iCarouselTimer.addClass('paused').attr('title', ic.options.playLabel);
                    ic.defs.pause = true;
                    clearInterval(ic.defs.interval);
                    ic.defs.interval = null;

                    //Trigger the onPause callback
                    ic.options.onPause.call(this);
                }
            });

            //For pauseOnHover setting
            if (ic.options.pauseOnHover) {
                ic.el.hover(function () {
                    if (!ic.defs.pause) {
                        clearInterval(ic.defs.interval);
                        ic.defs.interval = null;
                    }
                }, function () {
                    //Restart the timer
                    if (!ic.defs.lock && !ic.defs.pause && ic.defs.interval == null && ic.defs.degree <= 359) {
                        ic.setTimer();
                    }
                });
            }

            //Touch navigation
            if (ic.options.touchNav && (ic.support.touch())) {
                ic.el.bind({
                    swipeleft: function () {
                        (ic.defs.dir == "ltr") ? ic.goPrev() : ic.goNext();
                    },
                    swiperight: function () {
                        (ic.defs.dir == "ltr") ? ic.goNext() : ic.goPrev();
                    }
                });
            }

            //Bind the pause action
            ic.el.bind('iCarousel:pause', function () {
                ic.iCarouselTimer.addClass('paused').attr('title', ic.options.playLabel);
                ic.defs.pause = true;
                clearInterval(ic.defs.interval);
                ic.defs.interval = null;

                //Trigger the onPause callback
                ic.options.onPause.call(this);
            });

            //Bind the play action
            ic.el.bind('iCarousel:play', function () {
                ic.iCarouselTimer.removeClass('paused').attr('title', ic.options.pauseLabel);
                ic.defs.pause = false;

                //Restart the timer
                if (ic.defs.interval == null) {
                    ic.setTimer();

                    //Trigger the onPlay callback
                    ic.options.onPlay.call(this);
                }
            });

            //Bind the goSlide action
            ic.el.bind('iCarousel:goSlide', function (event, slide) {
                if (ic.defs.slide != slide) ic.goFar(slide);
            });

            //Bind the next action
            ic.el.bind('iCarousel:next', function () {
                ic.goNext();
            });

            //Bind the previous action
            ic.el.bind('iCarousel:previous', function () {
                ic.goPrev();
            });

            //Bind the mousewheel on the slides
            if (ic.el.mousewheel && ic.options.mouseWheel) ic.el.mousewheel(function (event, delta) {
                event.preventDefault();
                if (delta < 0) ic.goNext();
                else ic.goPrev();
            });

            //Bind the click on the slides
            ic.slides.click(function () {
                var slide = $(this),
                    index = slide.attr('index');
                if (ic.defs.slide != index) ic.goFar(index);
            });
        },

        //Direction navigation buttons
        setButtons: function () {
            this.el.append('<a class="iCarouselNav" id="iCarouselPrev" title="' + this.options.previousLabel + '">' + this.options.previousLabel + '</a><a class="iCarouselNav" id="iCarouselNext" title="' + this.options.nextLabel + '">' + this.options.nextLabel + '</a>');
        },

        disableSelection: function (target) {
            if (typeof target.onselectstart != "undefined") target.onselectstart = function () {
                return false;
            };
            else if (typeof target.style.MozUserSelect != "undefined") target.style.MozUserSelect = "none";
            else if (typeof target.style.webkitUserSelect != "undefined") target.style.webkitUserSelect = "none";
            else if (typeof target.style.userSelect != "undefined") target.style.userSelect = "none";
            else target.onmousedown = function () {
                    return false;
                };
            target.unselectable = "on";
        }
    };

    //Image Preloader Function
    var ImagePreload = function (p_aImages, p_pfnPercent, p_pfnFinished) {
        this.m_pfnPercent = p_pfnPercent;
        this.m_pfnFinished = p_pfnFinished;
        this.m_nLoaded = 0;
        this.m_nProcessed = 0;
        this.m_aImages = new Array;
        this.m_nICount = p_aImages.length;
        for (var i = 0; i < p_aImages.length; i++) this.Preload(p_aImages[i])
    };

    ImagePreload.prototype = {
        Preload: function (p_oImage) {
            var oImage = new Image;
            this.m_aImages.push(oImage);
            oImage.onload = ImagePreload.prototype.OnLoad;
            oImage.onerror = ImagePreload.prototype.OnError;
            oImage.onabort = ImagePreload.prototype.OnAbort;
            oImage.oImagePreload = this;
            oImage.bLoaded = false;
            oImage.source = p_oImage;
            oImage.src = p_oImage
        },
        OnComplete: function () {
            this.m_nProcessed++;
            if (this.m_nProcessed == this.m_nICount) this.m_pfnFinished();
            else this.m_pfnPercent(Math.round((this.m_nProcessed / this.m_nICount) * 10))
        },
        OnLoad: function () {
            this.bLoaded = true;
            this.oImagePreload.m_nLoaded++;
            this.oImagePreload.OnComplete()
        },
        OnError: function () {
            this.bError = true;
            this.oImagePreload.OnComplete()
        },
        OnAbort: function () {
            this.bAbort = true;
            this.oImagePreload.OnComplete()
        }
    }

    // Begin the iCarousel plugin
    $.fn.iCarousel = function (options) {

        // Default options. Play carefully.
        options = jQuery.extend({
            easing: 'easeInOutBack',
            slides: 13,
            make3D: false,
            perspective: 15,
            animationSpeed: 500,
            pauseTime: 2000,
            startSlide: 2,
            directionNav: true,
            autoPlay: true,
            keyboardNav: true,
            touchNav: true,
            mouseWheel: true,
            pauseOnHover: true,
            nextLabel: "Next",
            previousLabel: "Previous",
            playLabel: "Play",
            pauseLabel: "Pause",
            randomStart: false,
            slidesSpace: '200',
            slidesTopSpace: 'auto',
            direction: 'rtl',
            timer: 'Pie',
            timerBg: '#000',
            timerColor: '#00FF00',
            timerOpacity: 0.4,
            timerDiameter: 35,
            timerPadding: 4,
            timerStroke: 3,
            timerBarStroke: 1,
            timerBarStrokeColor: '#00FF00',
            timerBarStrokeStyle: 'solid',
            timerBarStrokeRadius: 4,
            timerPosition: 'top-right',
            timerX: 10,
            timerY: 10,
            onBeforeChange: function () {
            },
            onAfterChange: function () {
            },
            onAfterLoad: function () {
            },
            onLastSlide: function () {
            },
            onSlideShowEnd: function () {
            },
            onPause: function () {
            },
            onPlay: function () {
            }
        }, options);

        $(this).each(function () {
            var el = $(this),
                slides = el.children();

            new iCarousel(el, slides, options);
        });

    };

    var supportTouch = !!('ontouchstart' in window),
        touchStartEvent = supportTouch ? "touchstart" : "mousedown",
        touchStopEvent = supportTouch ? "touchend" : "mouseup",
        touchMoveEvent = supportTouch ? "touchmove" : "mousemove";
    // also handles swipeleft, swiperight
    $.event.special.swipe = {
        scrollSupressionThreshold: 10, // More than this horizontal displacement, and we will suppress scrolling.

        durationThreshold: 1000, // More time than this, and it isn't a swipe.

        horizontalDistanceThreshold: 30, // Swipe horizontal displacement must be more than this.

        verticalDistanceThreshold: 75, // Swipe vertical displacement must be less than this.

        setup: function () {
            var thisObject = this,
                $this = $(thisObject);

            $this.bind(touchStartEvent, function (event) {
                var data = event.originalEvent.touches ? event.originalEvent.touches[0] : event,
                    start = {
                        time: (new Date()).getTime(),
                        coords: [data.pageX, data.pageY],
                        origin: $(event.target)
                    },
                    stop;

                function moveHandler(event) {

                    if (!start) {
                        return;
                    }

                    var data = event.originalEvent.touches ? event.originalEvent.touches[0] : event;

                    stop = {
                        time: (new Date()).getTime(),
                        coords: [data.pageX, data.pageY]
                    };

                    // prevent scrolling
                    if (Math.abs(start.coords[0] - stop.coords[0]) > $.event.special.swipe.scrollSupressionThreshold) {
                        event.preventDefault();
                    }
                }

                $this.bind(touchMoveEvent, moveHandler).one(touchStopEvent, function (event) {
                    $this.unbind(touchMoveEvent, moveHandler);

                    if (start && stop) {
                        if (stop.time - start.time < $.event.special.swipe.durationThreshold && Math.abs(start.coords[0] - stop.coords[0]) > $.event.special.swipe.horizontalDistanceThreshold && Math.abs(start.coords[1] - stop.coords[1]) < $.event.special.swipe.verticalDistanceThreshold) {

                            start.origin.trigger("swipe").trigger(start.coords[0] > stop.coords[0] ? "swipeleft" : "swiperight");
                        }
                    }
                    start = stop = undefined;
                });
            });
        }
    };


    $.each({
        swipeleft: "swipe",
        swiperight: "swipe"
    }, function (event, sourceEvent) {

        $.event.special[event] = {
            setup: function () {
                $(this).bind(sourceEvent, $.noop);
            }
        };
    });

})(jQuery);
