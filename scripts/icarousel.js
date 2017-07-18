/**
 * jQuery iCarousel v1.1
 * 
 * @version: 1.1 - June 15, 2012
 * @version: 1.0 - May 25, 2012
 * 
 * @author: Hemn Chawroka
 *          chavroka@yahoo.com
 *          http://hemn.soloset.net/
 * 
 */

(function (g) {
	var o = function (a, d, c) {
		var b = this;
		b.el = a;
		b.slides = d;
		b.options = c;
		b.defs = {
			degree: 0,
			total: d.length,
			images: [],
			interval: null,
			timer: c.timer.toLowerCase(),
			dir: c.direction.toLowerCase(),
			pause: !1,
			slide: 0,
			currentSlide: null,
			width: a.width(),
			height: a.height(),
			space: c.slidesSpace,
			topSpace: c.slidesTopSpace,
			lock: !1,
			easing: "ease-in-out",
			time: c.pauseTime
		};
		b.disableSelection(a[0]);
		d.each(function (a) {
			var b = g(this);
			b.attr({
				"data-outerwidth": b.outerWidth(),
				"data-outerheight": b.outerHeight(),
				"data-width": b.width(),
				"data-height": b.height(),
				index: a
			}).css({
				visibility: "hidden"
			})
		});
		g("img", a).each(function () {
			var a = g(this);
			b.defs.images.push(a.attr("src"))
		});
		c.startSlide = c.randomStart ? Math.floor(Math.random() * b.defs.total) : c.startSlide;
		c.startSlide = 0 > c.startSlide || c.startSlide > b.defs.total ? 0 : c.startSlide;
		b.defs.slide = c.startSlide;
		b.defs.currentSlide = d.eq(b.defs.slide);
		b.defs.time = b.defs.currentSlide.data("pausetime") ? b.defs.currentSlide.data("pausetime") : c.pauseTime;
		c.slides = c.slides > b.defs.total ? defs.total: c.slides;
		c.slides = c.slides % 2 ? c.slides: c.slides - 1;
		a.append('<div id="iCarousel-preloader"><div></div></div>');
		var e = g("#iCarousel-preloader", a),
		f = g("div", e);
		e.css({
			top: b.defs.height / 2 - e.height() / 2 + "px",
			left: b.defs.width / 2 - e.width() / 2 + "px"
		});
		a.append('<div id="iCarousel-timer"><div></div></div>');
		b.iCarouselTimer = g("#iCarousel-timer", a);
		b.iCarouselTimer.hide();
		b.barTimer = g("div", b.iCarouselTimer);
		var h = c.timerPadding,
		l = c.timerDiameter,
		k = c.timerStroke;
		if (c.autoPlay && 1 < b.defs.total && "bar" != b.defs.timer) {
			var k = "360bar" == b.defs.timer ? c.timerStroke: 0,
			i = l + 2 * h + 2 * k,
			j = i,
			a = Raphael(b.iCarouselTimer[0], i, j),
			m = l / 2,
			d = {
				stroke: c.timerBg,
				"stroke-width": k + 2 * h
			},
			o = {
				stroke: c.timerColor,
				"stroke-width": k,
				"stroke-linecap": "round"
			},
			p = {
				fill: c.timerColor,
				stroke: "none",
				"stroke-width": 0
			},
			q = {
				fill: c.timerBg,
				stroke: "none",
				"stroke-width": 0
			};
			b.R = m;
			a.customAttributes.arc = function (a, b) {
				var c = 1 * a,
				d = (90 - c) * Math.PI / 180,
				e = l / 2 + h + k,
				f = l / 2 + h + k,
				g = e + b * Math.cos(d),
				d = f - b * Math.sin(d);
				return {
					path: 360 == a ? [["M", e, f - b], ["A", b, b, 0, 1, 1, 299.99, f - b]] : [["M", e, f - b], ["A", b, b, 0, +(180 < c), 1, g, d]]
				}
			};
			a.customAttributes.segment = function (a, b) {
				var c = -90,
				b = b - 1,
				a = c + a,
				d = 180 < a - c,
				e = l / 2 + h,
				f = l / 2 + h,
				c = c % 360 * Math.PI / 180,
				a = a % 360 * Math.PI / 180;
				return {
					path: [["M", e, f], ["l", b * Math.cos(c), b * Math.sin(c)], ["A", b, b, 0, +d, 1, e + b * Math.cos(a), f + b * Math.sin(a)], ["z"]]
				}
			};
			c.autoPlay && (1 < b.defs.total && "pie" == b.defs.timer) && a.circle(m + h, m + h, m + h - 1).attr(q);
			b.timerBgPath = a.path().attr(d);
			b.timerPath = a.path().attr(o);
			b.pieTimer = a.path().attr(p)
		}
		c.autoPlay && (1 < b.defs.total && "360bar" == b.defs.timer) && b.timerBgPath.attr({
			arc: [359.9, m]
		});
		"bar" == b.defs.timer ? (b.iCarouselTimer.css({
			opacity: c.timerOpacity,
			width: l,
			height: k,
			border: c.timerBarStroke + "px " + c.timerBarStrokeColor + " " + c.timerBarStrokeStyle,
			padding: h,
			background: c.timerBg
		}), b.barTimer.css({
			width: 0,
			height: k,
			background: c.timerColor,
			"float": "left"
		})) : b.iCarouselTimer.css({
			opacity: c.timerOpacity,
			width: i,
			height: j
		});
		i = c.timerPosition.toLowerCase().split("-");
		for (j = 0; j < i.length; j++)"top" == i[j] ? b.iCarouselTimer.css({
			top: c.timerY + "px",
			bottom: ""
		}) : "middle" == i[j] ? b.iCarouselTimer.css({
			top: c.timerY + b.defs.height / 2 - c.timerDiameter / 2 + "px",
			bottom: ""
		}) : "bottom" == i[j] ? b.iCarouselTimer.css({
			bottom: c.timerY + "px",
			top: ""
		}) : "left" == i[j] ? b.iCarouselTimer.css({
			left: c.timerX + "px",
			right: ""
		}) : "center" == i[j] ? b.iCarouselTimer.css({
			left: c.timerX + b.defs.width / 2 - c.timerDiameter / 2 + "px",
			right: ""
		}) : "right" == i[j] && b.iCarouselTimer.css({
			right: c.timerX + "px",
			left: ""
		});
		b.defs.easing = b.setEasing(c.easing);
		new n(b.defs.images, function (a) {
			a = a * 10;
			f.stop().animate({
				width: a + "%"
			})
		},
		function () {
			f.stop().animate({
				width: "100%"
			},
			function () {
				e.remove();
				b.init();
				c.onAfterLoad.call(this)
			})
		})
	};
	o.prototype = {
		rightItems: [],
		leftItems: [],
		rightOutItem: null,
		leftOutItem: null,
		support: {
			transform3d: function () {
				for (var a = ["perspectiveProperty", "WebkitPerspective", "MozPerspective", "OPerspective", "msPerspective"], d = 0, c = !1, b = document.createElement("form"); a[d];) {
					if (a[d] in b.style) {
						c = !0;
						break
					}
					d++
				}
				return c
			},
			transform2d: function () {
				for (var a = ["transformProperty", "WebkitTransform", "MozTransform", "OTransform", "msTransform"], d = 0, c = !1, b = document.createElement("form"); a[d];) {
					if (a[d] in b.style) {
						c = !0;
						break
					}
					d++
				}
				return c
			},
			transition: function () {
				for (var a = ["transitionProperty", "WebkitTransition", "MozTransition", "OTransition", "msTransition"], d = 0, c = !1, b = document.createElement("form"); a[d];) {
					if (a[d] in b.style) {
						c = !0;
						break
					}
					d++
				}
				return c
			}
		},
		init: function () {
			this.options.directionNav && this.setButtons();
			this.layout();
			this.events();
			this.options.autoPlay && 1 < this.defs.total && (this.setTimer(), this.iCarouselTimer.attr("title", this.options.pauseLabel).show())
		},
		goSlide: function (a, d, c) {
			var b = this;
			b.defs && b.defs.slide == b.defs.total - 1 && b.options.onLastSlide.call(this);
			b.clearTimer();
			b.options.onBeforeChange.call(this);
			b.defs.slide = 0 > a || a > b.defs.total - 1 ? 0 : a;
			b.defs.slide == b.defs.total - 1 && b.options.onSlideShowEnd.call(this);
			b.defs.currentSlide = b.slides.eq(b.defs.slide);
			b.defs.easing = b.defs.currentSlide.data("easing") ? b.setEasing(g.trim(b.defs.currentSlide.data("easing"))) : b.setEasing(b.options.easing);
			b.defs.time = b.defs.currentSlide.data("pausetime") ? b.defs.currentSlide.data("pausetime") : b.options.pauseTime;
			a = c ? b.options.animationSpeed / c: !1;
			b.slides.removeClass("current");
			b.defs.lock = !0;
			b.layout(!0, a);
			if (c) return ! 1;
			b.resetTimer();
			setTimeout(function () {
				b.animationEnd(b)
			},
			b.options.animationSpeed)
		},
		goFar: function (a) {
			var d = this,
			c = a == d.defs.total - 1 && 0 == d.defs.slide ? -1 : a - d.defs.slide;
			d.defs.slide == d.defs.total - 1 && 0 == a && (c = 1);
			for (var b = 0 > c ? -c: c, e = a = 0; e < b; e++) setTimeout(function () {
				0 > c ? d.goPrev(b) : d.goNext(b)
			},
			1 == b ? 0 : a),
			a += d.options.animationSpeed / b;
			setTimeout(function () {
				d.animationEnd(d)
			},
			d.options.animationSpeed);
			d.resetTimer()
		},
		animationEnd: function (a) {
			a.options.onAfterChange.call(this);
			a.defs.lock = !1;
			a.defs.degree = 0;
			null == a.defs.interval && (!a.defs.pause && a.options.autoPlay) && a.setTimer()
		},
		processTimer: function () {
			if ("360bar" == this.defs.timer) {
				var a = 0 == this.defs.degree ? 0 : this.defs.degree - 0.9;
				this.timerPath.attr({
					arc: [a, this.R]
				})
			} else "pie" == this.defs.timer ? (a = 0 == this.defs.degree ? 0 : this.defs.degree - 0.9, this.pieTimer.attr({
				segment: [a, this.R]
			})) : this.barTimer.css({
				width: 100 * (this.defs.degree / 360) + "%"
			});
			this.defs.degree += 4
		},
		resetTimer: function () {
			"360bar" == this.defs.timer ? this.timerPath.animate({
				arc: [0, this.R]
			},
			this.options.animationSpeed) : "pie" == this.defs.timer ? this.pieTimer.animate({
				segment: [0, this.R]
			},
			this.options.animationSpeed) : this.barTimer.animate({
				width: 0
			},
			this.options.animationSpeed)
		},
		timerCall: function (a) {
			a.processTimer();
			360 < a.defs.degree && a.goNext()
		},
		setTimer: function () {
			var a = this;
			a.defs.interval = setInterval(function () {
				a.timerCall(a)
			},
			a.defs.time / 90)
		},
		clearTimer: function () {
			clearInterval(this.defs.interval);
			this.defs.interval = null;
			this.defs.degree = 0
		},
		layout: function (a, d) {
			this.setItems();
			var c = "auto" == this.defs.topSpace ? this.defs.height / 2 - this.defs.currentSlide.data("outerheight") / 2 : 0,
			b = this.defs.width / 2 - this.defs.currentSlide.data("outerwidth") / 2,
			e = 999,
			f = {},
			f = d ? d / 1E3: this.options.animationSpeed / 1E3;
			a && this.support.transition() && this.slides.css({
				"-webkit-transition": "all " + f + "s " + this.defs.easing,
				"-moz-transition": "all " + f + "s " + this.defs.easing,
				"-o-transition": "all " + f + "s " + this.defs.easing,
				"-ms-transition": "all " + f + "s " + this.defs.easing,
				transition: "all " + f + "s " + this.defs.easing
			});
			this.slides.css({
				top: c + "px",
				position: "absolute",
				opacity: 0,
				visibility: "hidden"
			});
			this.defs.currentSlide.addClass("current").css({
				"-webkit-transform": "none",
				"-moz-transform": "none",
				"-o-transform": "none",
				"-ms-transform": "none",
				transform: "none",
				left: b + "px",
				top: c + "px",
				width: this.defs.currentSlide.data("width") + "px",
				height: this.defs.currentSlide.data("height") + "px",
				zIndex: e,
				opacity: 1,
				visibility: "visible"
			});
			for (c = 0; c < this.rightItems.length; c++) b = this.rightItems[c],
			e -= c + 1,
			f = this.CSS(b, c, e, !0),
			cssA = this.CSS(b, c, e, !0, !0),
			b.css(f).css({
				opacity: 1,
				visibility: "visible"
			});
			for (c = 0; c < this.leftItems.length; c++) b = this.leftItems[c],
			e -= c + 1,
			f = this.CSS(b, c, e),
			b.css(f).css({
				opacity: 1,
				visibility: "visible"
			});
			this.defs.total > this.options.slides && (this.rightOutItem.css(this.CSS(this.rightOutItem, this.leftItems.length - 0.5, this.leftItems.length - 1, !0)), this.leftOutItem.css(this.CSS(this.leftOutItem, this.leftItems.length - 0.5, this.leftItems.length - 1)))
		},
		setItems: function () {
			var a = Math.floor(this.options.slides / 2) + 1;
			this.leftItems = [];
			this.rightItems = [];
			for (var d = 1; d < a; d++) {
				var c = "ltr" == this.defs.dir ? (this.defs.slide + d) % this.defs.total: (this.defs.slide - d) % this.defs.total;
				this.leftItems.push(this.slides.eq(c))
			}
			for (d = 1; d < a; d++) c = "ltr" == this.defs.dir ? (this.defs.slide - d) % this.defs.total: (this.defs.slide + d) % this.defs.total,
			this.rightItems.push(this.slides.eq(c));
			this.leftOutItem = this.slides.eq(this.defs.slide - a);
			this.rightOutItem = 0 >= this.defs.total - this.defs.slide - a ? this.slides.eq( - parseInt(this.defs.total - this.defs.slide - a)) : this.slides.eq(this.defs.slide + a);
			a = this.leftOutItem;
			d = this.rightOutItem;
			"ltr" == this.defs.dir && (this.leftOutItem = d, this.rightOutItem = a)
		},
		CSS: function (a, d, c, b) {
			var e = "auto" == this.defs.space ? parseInt((d + 1) * (a.data("width") / 1.5)) : parseInt((d + 1) * this.defs.space);
			if (this.support.transform3d() && this.options.make3D) var f = b ? "translateX(" + e + "px) translateZ(-" + (250 + 110 * (d + 1)) + "px) rotateY(-" + this.options.perspective + "deg)": "translateX(-" + e + "px) translateZ(-" + (250 + 110 * (d + 1)) + "px) rotateY(" + this.options.perspective + "deg)",
			b = "0%",
			d = "auto" == this.defs.topSpace ? "none": parseInt((d + 1) * this.defs.space),
			a = e = "none",
			h = "visible";
			else this.support.transform2d() ? (f = b ? "translateX(" + e / 1.5 + "px) scale(" + (1 - d / 10 - 0.1) + ")": "translateX(-" + e / 1.5 + "px) scale(" + (1 - d / 10 - 0.1) + ")", b = "0%", d = "auto" == this.defs.topSpace ? "none": parseInt((d + 1) * this.defs.topSpace), a = e = "none", h = "visible") : (f = "", b = b ? e / 1.5 + 50 * (d + 2) + "px": "-" + e / 1.5 + "px", e = a.data("width") - 50 * (d + 2), a = a.data("height") - 50 * (d + 2), d = "auto" == this.defs.topSpace ? this.defs.height / 2 - a / 2 : parseInt((d + 1) * this.defs.topSpace), h = "hidden");
			return css = {
				"-webkit-transform": f,
				"-moz-transform": f,
				"-o-transform": f,
				"-ms-transform": f,
				transform: f,
				left: b,
				top: d,
				width: e,
				height: a,
				zIndex: c,
				overflow: h
			}
		},
		setEasing: function (a) {
			a = g.trim(a);
			switch (a) {
			case "linear":
				a = "cubic-bezier(0.250, 0.250, 0.750, 0.750)";
				break;
			case "ease":
				a = "cubic-bezier(0.250, 0.100, 0.250, 1.000)";
				break;
			case "ease-in":
				a = "cubic-bezier(0.420, 0.000, 1.000, 1.000)";
				break;
			case "ease-out":
				a = "cubic-bezier(0.000, 0.000, 0.580, 1.000)";
				break;
			case "ease-in-out":
				a = "cubic-bezier(0.420, 0.000, 0.580, 1.000)";
				break;
			case "ease-out-in":
				a = "cubic-bezier(0.000, 0.420, 1.000, 0.580)";
				break;
			case "easeInQuad":
				a = "cubic-bezier(0.550, 0.085, 0.680, 0.530)";
				break;
			case "easeInCubic":
				a = "cubic-bezier(0.550, 0.055, 0.675, 0.190)";
				break;
			case "easeInQuart":
				a = "cubic-bezier(0.895, 0.030, 0.685, 0.220)";
				break;
			case "easeInQuint":
				a = "cubic-bezier(0.755, 0.050, 0.855, 0.060)";
				break;
			case "easeInSine":
				a = "cubic-bezier(0.470, 0.000, 0.745, 0.715)";
				break;
			case "easeInExpo":
				a = "cubic-bezier(0.950, 0.050, 0.795, 0.035)";
				break;
			case "easeInCirc":
				a = "cubic-bezier(0.600, 0.040, 0.980, 0.335)";
				break;
			case "easeInBack":
				a = "cubic-bezier(0.600, -0.280, 0.735, 0.045)";
				break;
			case "easeOutQuad":
				a = "cubic-bezier(0.250, 0.460, 0.450, 0.940)";
				break;
			case "easeOutCubic":
				a = "cubic-bezier(0.215, 0.610, 0.355, 1.000)";
				break;
			case "easeOutQuart":
				a = "cubic-bezier(0.165, 0.840, 0.440, 1.000)";
				break;
			case "easeOutQuint":
				a = "cubic-bezier(0.230, 1.000, 0.320, 1.000)";
				break;
			case "easeOutSine":
				a = "cubic-bezier(0.390, 0.575, 0.565, 1.000)";
				break;
			case "easeOutExpo":
				a = "cubic-bezier(0.190, 1.000, 0.220, 1.000)";
				break;
			case "easeOutCirc":
				a = "cubic-bezier(0.075, 0.820, 0.165, 1.000)";
				break;
			case "easeOutBack":
				a = "cubic-bezier(0.175, 0.885, 0.320, 1.275)";
				break;
			case "easeInOutQuad":
				a = "cubic-bezier(0.455, 0.030, 0.515, 0.955)";
				break;
			case "easeInOutCubic":
				a = "cubic-bezier(0.645, 0.045, 0.355, 1.000)";
				break;
			case "easeInOutQuart":
				a = "cubic-bezier(0.770, 0.000, 0.175, 1.000)";
				break;
			case "easeInOutQuint":
				a = "cubic-bezier(0.860, 0.000, 0.070, 1.000)";
				break;
			case "easeInOutSine":
				a = "cubic-bezier(0.445, 0.050, 0.550, 0.950)";
				break;
			case "easeInOutExpo":
				a = "cubic-bezier(1.000, 0.000, 0.000, 1.000)";
				break;
			case "easeInOutCirc":
				a = "cubic-bezier(0.785, 0.135, 0.150, 0.860)";
				break;
			case "easeInOutBack":
				a = "cubic-bezier(0.680, 0, 0.265, 1)"
			}
			return a
		},
		goNext: function (a) {
			a = a ? a: !1;
			if (!a && this.defs.lock) return ! 1;
			this.defs.slide == this.defs.total ? this.goSlide(0, !1, a) : this.goSlide(this.defs.slide + 1, !1, a)
		},
		goPrev: function (a) {
			a = a ? a: !1;
			if (!a && this.defs.lock) return ! 1;
			0 == this.defs.slide ? this.goSlide(this.defs.total - 1, !1, a) : this.goSlide(this.defs.slide - 1, !1, a)
		},
		events: function () {
			var a = this;
			a.options.keyboardNav && g(document).bind("keyup.iCarousel", function (d) {
				switch (d.keyCode) {
				case 33:
				case 37:
				case 38:
					a.goPrev();
					break;
				case 34:
				case 39:
				case 40:
					a.goNext()
				}
			});
			g("a#iCarouselPrev", a.el).click(function () {
				a.goPrev()
			});
			g("a#iCarouselNext", a.el).click(function () {
				a.goNext()
			});
			a.iCarouselTimer.click(function () {
				a.iCarouselTimer.hasClass("paused") ? (a.iCarouselTimer.removeClass("paused").attr("title", a.options.pauseLabel), a.defs.pause = !1, null == a.defs.interval && (a.setTimer(), a.options.onPlay.call(this))) : (a.iCarouselTimer.addClass("paused").attr("title", a.options.playLabel), a.defs.pause = !0, clearInterval(a.defs.interval), a.defs.interval = null, a.options.onPause.call(this))
			});
			a.options.pauseOnHover && a.el.hover(function () {
				a.defs.pause || (clearInterval(a.defs.interval), a.defs.interval = null)
			},
			function () { ! a.defs.lock && (!a.defs.pause && null == a.defs.interval && 359 >= a.defs.degree && a.options.autoPlay) && a.setTimer()
			});
			a.options.touchNav && navigator.userAgent.match(/ipad|iphone|ipod|android/i) && a.el.swipe({
				swipeLeft: function () {
					"ltr" == a.defs.dir ? a.goPrev() : a.goNext()
				},
				swipeRight: function () {
					"ltr" == a.defs.dir ? a.goNext() : a.goPrev()
				}
			});
			a.el.bind("iCarousel:pause", function () {
				a.iCarouselTimer.addClass("paused").attr("title", a.options.playLabel);
				a.defs.pause = !0;
				clearInterval(a.defs.interval);
				a.defs.interval = null;
				a.options.onPause.call(this)
			});
			a.el.bind("iCarousel:play", function () {
				a.iCarouselTimer.removeClass("paused").attr("title", a.options.pauseLabel);
				a.defs.pause = !1;
				null == a.defs.interval && (a.setTimer(), a.options.onPlay.call(this))
			});
			a.el.bind("iCarousel:goSlide", function (d, c) {
				a.defs.slide != c && a.goFar(c)
			});
			a.el.bind("iCarousel:next", function () {
				a.goNext()
			});
			a.el.bind("iCarousel:previous", function () {
				a.goPrev()
			});
			a.el.mousewheel && a.options.mouseWheel && a.el.mousewheel(function (d, c) {
				0 > c ? a.goNext() : a.goPrev()
			});
			a.slides.click(function () {
				var d = g(this).attr("index");
				a.defs.slide != d && a.goFar(d)
			})
		},
		setButtons: function () {
			this.el.append('<a class="iCarouselNav" id="iCarouselPrev" title="' + this.options.previousLabel + '">' + this.options.previousLabel + '</a><a class="iCarouselNav" id="iCarouselNext" title="' + this.options.nextLabel + '">' + this.options.nextLabel + "</a>")
		},
		disableSelection: function (a) {
			"undefined" != typeof a.onselectstart ? a.onselectstart = function () {
				return ! 1
			}: "undefined" != typeof a.style.MozUserSelect ? a.style.MozUserSelect = "none": "undefined" != typeof a.style.webkitUserSelect ? a.style.webkitUserSelect = "none": "undefined" != typeof a.style.userSelect ? a.style.userSelect = "none": a.onmousedown = function () {
				return ! 1
			};
			a.unselectable = "on"
		}
	};
	var n = function (a, d, c) {
		this.m_pfnPercent = d;
		this.m_pfnFinished = c;
		this.m_nProcessed = this.m_nLoaded = 0;
		this.m_aImages = [];
		this.m_nICount = a.length;
		for (d = 0; d < a.length; d++) this.Preload(a[d])
	};
	n.prototype = {
		Preload: function (a) {
			var d = new Image;
			this.m_aImages.push(d);
			d.onload = n.prototype.OnLoad;
			d.onerror = n.prototype.OnError;
			d.onabort = n.prototype.OnAbort;
			d.oImagePreload = this;
			d.bLoaded = !1;
			d.source = a;
			d.src = a
		},
		OnComplete: function () {
			this.m_nProcessed++;
			this.m_nProcessed == this.m_nICount ? this.m_pfnFinished() : this.m_pfnPercent(Math.round(10 * (this.m_nProcessed / this.m_nICount)))
		},
		OnLoad: function () {
			this.bLoaded = !0;
			this.oImagePreload.m_nLoaded++;
			this.oImagePreload.OnComplete()
		},
		OnError: function () {
			this.bError = !0;
			this.oImagePreload.OnComplete()
		},
		OnAbort: function () {
			this.bAbort = !0;
			this.oImagePreload.OnComplete()
		}
	};
	g.fn.iCarousel = function (a) {
		a = jQuery.extend({
			easing: "ease-in-out",
			slides: 3,
			make3D: !0,
			perspective: 35,
			animationSpeed: 500,
			pauseTime: 5E3,
			startSlide: 0,
			directionNav: !0,
			autoPlay: !0,
			keyboardNav: !0,
			touchNav: !0,
			mouseWheel: !0,
			pauseOnHover: !1,
			nextLabel: "Next",
			previousLabel: "Previous",
			playLabel: "Play",
			pauseLabel: "Pause",
			randomStart: !1,
			slidesSpace: "auto",
			slidesTopSpace: "auto",
			direction: "rtl",
			timer: "Pie",
			timerBg: "#000",
			timerColor: "#FFF",
			timerOpacity: 0.4,
			timerDiameter: 35,
			timerPadding: 4,
			timerStroke: 3,
			timerBarStroke: 1,
			timerBarStrokeColor: "#FFF",
			timerBarStrokeStyle: "solid",
			timerBarStrokeRadius: 4,
			timerPosition: "top-right",
			timerX: 10,
			timerY: 10,
			onBeforeChange: function () {},
			onAfterChange: function () {},
			onAfterLoad: function () {},
			onLastSlide: function () {},
			onSlideShowEnd: function () {},
			onPause: function () {},
			onPlay: function () {}
		},
		a);
		g(this).each(function () {
			var d = g(this),
			c = d.children();
			new o(d, c, a)
		})
	};
	g.fn.swipe = function (a) {
		a = jQuery.extend({
			threshold: {
				x: 30,
				y: 100
			},
			swipeLeft: function () {
				alert("swiped left")
			},
			swipeRight: function () {
				alert("swiped right")
			}
		},
		a);
		g(this).each(function () {
			var d = g(this),
			c = 0,
			b = 0,
			e = 0,
			f = 0;
			d.bind("touchstart MozTouchDown", function (a) {
				c = a.originalEvent.targetTouches[0].pageX;
				b = a.originalEvent.targetTouches[0].pageY;
				e = c;
				f = b
			});
			d.bind("touchmove MozTouchMove", function (a) {
				a.preventDefault();
				e = a.originalEvent.touches[0].pageX;
				f = a.originalEvent.touches[0].pageY
			});
			d.bind("touchend MozTouchRelease", function () {
				var d = b - f;
				d < a.threshold.y && d > -1 * a.threshold.y && (changeX = c - e, changeX > a.threshold.x && a.swipeLeft.call(this), changeX < -1 * a.threshold.x && a.swipeRight.call(this))
			})
		})
	}
})(jQuery);