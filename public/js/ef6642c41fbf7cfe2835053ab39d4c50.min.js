(function(factory) {
    "use strict";
    if (typeof define === "function" && define.amd) {
        define([ "jquery" ], factory);
    } else if (typeof module !== "undefined" && module.exports) {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
})(function($) {
    "use strict";
    var $scrollTo = $.scrollTo = function(target, duration, settings) {
        return $(window).scrollTo(target, duration, settings);
    };
    $scrollTo.defaults = {
        axis: "xy",
        duration: 0,
        limit: true
    };
    function isWin(elem) {
        return !elem.nodeName || $.inArray(elem.nodeName.toLowerCase(), [ "iframe", "#document", "html", "body" ]) !== -1;
    }
    $.fn.scrollTo = function(target, duration, settings) {
        if (typeof duration === "object") {
            settings = duration;
            duration = 0;
        }
        if (typeof settings === "function") {
            settings = {
                onAfter: settings
            };
        }
        if (target === "max") {
            target = 9e9;
        }
        settings = $.extend({}, $scrollTo.defaults, settings);
        duration = duration || settings.duration;
        var queue = settings.queue && settings.axis.length > 1;
        if (queue) {
            duration /= 2;
        }
        settings.offset = both(settings.offset);
        settings.over = both(settings.over);
        return this.each(function() {
            if (target === null) return;
            var win = isWin(this), elem = win ? this.contentWindow || window : this, $elem = $(elem), targ = target, attr = {}, toff;
            switch (typeof targ) {
              case "number":
              case "string":
                if (/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(targ)) {
                    targ = both(targ);
                    break;
                }
                targ = win ? $(targ) : $(targ, elem);

              case "object":
                if (targ.length === 0) return;
                if (targ.is || targ.style) {
                    toff = (targ = $(targ)).offset();
                }
            }
            var offset = $.isFunction(settings.offset) && settings.offset(elem, targ) || settings.offset;
            $.each(settings.axis.split(""), function(i, axis) {
                var Pos = axis === "x" ? "Left" : "Top", pos = Pos.toLowerCase(), key = "scroll" + Pos, prev = $elem[key](), max = $scrollTo.max(elem, axis);
                if (toff) {
                    attr[key] = toff[pos] + (win ? 0 : prev - $elem.offset()[pos]);
                    if (settings.margin) {
                        attr[key] -= parseInt(targ.css("margin" + Pos), 10) || 0;
                        attr[key] -= parseInt(targ.css("border" + Pos + "Width"), 10) || 0;
                    }
                    attr[key] += offset[pos] || 0;
                    if (settings.over[pos]) {
                        attr[key] += targ[axis === "x" ? "width" : "height"]() * settings.over[pos];
                    }
                } else {
                    var val = targ[pos];
                    attr[key] = val.slice && val.slice(-1) === "%" ? parseFloat(val) / 100 * max : val;
                }
                if (settings.limit && /^\d+$/.test(attr[key])) {
                    attr[key] = attr[key] <= 0 ? 0 : Math.min(attr[key], max);
                }
                if (!i && settings.axis.length > 1) {
                    if (prev === attr[key]) {
                        attr = {};
                    } else if (queue) {
                        animate(settings.onAfterFirst);
                        attr = {};
                    }
                }
            });
            animate(settings.onAfter);
            function animate(callback) {
                var opts = $.extend({}, settings, {
                    queue: true,
                    duration: duration,
                    complete: callback && function() {
                        callback.call(elem, targ, settings);
                    }
                });
                $elem.animate(attr, opts);
            }
        });
    };
    $scrollTo.max = function(elem, axis) {
        var Dim = axis === "x" ? "Width" : "Height", scroll = "scroll" + Dim;
        if (!isWin(elem)) return elem[scroll] - $(elem)[Dim.toLowerCase()]();
        var size = "client" + Dim, doc = elem.ownerDocument || elem.document, html = doc.documentElement, body = doc.body;
        return Math.max(html[scroll], body[scroll]) - Math.min(html[size], body[size]);
    };
    function both(val) {
        return $.isFunction(val) || $.isPlainObject(val) ? val : {
            top: val,
            left: val
        };
    }
    $.Tween.propHooks.scrollLeft = $.Tween.propHooks.scrollTop = {
        get: function(t) {
            return $(t.elem)[t.prop]();
        },
        set: function(t) {
            var curr = this.get(t);
            if (t.options.interrupt && t._last && t._last !== curr) {
                return $(t.elem).stop();
            }
            var next = Math.round(t.now);
            if (curr !== next) {
                $(t.elem)[t.prop](next);
                t._last = this.get(t);
            }
        }
    };
    return $scrollTo;
});

$(document).ready(function() {
    $("#question_1").click(function() {
        $.scrollTo("#answer_1", {
            duration: 1250,
            onAfter: function() {
                $("#answer_1_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
    $("#question_2").click(function() {
        $.scrollTo("#answer_2", {
            duration: 1250,
            onAfter: function() {
                $("#answer_2_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
    $("#question_3").click(function() {
        $.scrollTo("#answer_3", {
            duration: 1250,
            onAfter: function() {
                $("#answer_3_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
    $("#question_4").click(function() {
        $.scrollTo("#answer_4", {
            duration: 1250,
            onAfter: function() {
                $("#answer_4_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
    $("#question_5").click(function() {
        $.scrollTo("#answer_5", {
            duration: 1250,
            onAfter: function() {
                $("#answer_5_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
    $("#question_6").click(function() {
        $.scrollTo("#answer_6", {
            duration: 1250,
            onAfter: function() {
                $("#answer_6_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
    $("#question_7").click(function() {
        $.scrollTo("#answer_7", {
            duration: 1250,
            onAfter: function() {
                $("#answer_7_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
    $("#question_8").click(function() {
        $.scrollTo("#answer_8", {
            duration: 1250,
            onAfter: function() {
                $("#answer_8_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
    $("#question_9").click(function() {
        $.scrollTo("#answer_9", {
            duration: 1250,
            onAfter: function() {
                $("#answer_9_text").highlightFade({
                    color: "rgb(255, 189, 112)",
                    speed: 1250
                });
            }
        });
    });
});

jQuery.fn.highlightFade = function(settings) {
    var o = settings && settings.constructor == String ? {
        start: settings
    } : settings || {};
    var d = jQuery.highlightFade.defaults;
    var i = o["interval"] || d["interval"];
    var a = o["attr"] || d["attr"];
    var ts = {
        linear: function(s, e, t, c) {
            return parseInt(s + c / t * (e - s));
        },
        sinusoidal: function(s, e, t, c) {
            return parseInt(s + Math.sin(c / t * 90 * (Math.PI / 180)) * (e - s));
        },
        exponential: function(s, e, t, c) {
            return parseInt(s + Math.pow(c / t, 2) * (e - s));
        }
    };
    var t = o["iterator"] && o["iterator"].constructor == Function ? o["iterator"] : ts[o["iterator"]] || ts[d["iterator"]] || ts["linear"];
    if (d["iterator"] && d["iterator"].constructor == Function) t = d["iterator"];
    return this.each(function() {
        if (!this.highlighting) this.highlighting = {};
        var e = this.highlighting[a] ? this.highlighting[a].end : jQuery.highlightFade.getBaseValue(this, a) || [ 255, 255, 255 ];
        var c = jQuery.highlightFade.getRGB(o["start"] || o["colour"] || o["color"] || d["start"] || [ 255, 255, 128 ]);
        var s = jQuery.speed(o["speed"] || d["speed"]);
        var r = o["final"] || this.highlighting[a] && this.highlighting[a].orig ? this.highlighting[a].orig : jQuery.css(this, a);
        if (o["end"] || d["end"]) r = jQuery.highlightFade.asRGBString(e = jQuery.highlightFade.getRGB(o["end"] || d["end"]));
        if (typeof o["final"] != "undefined") r = o["final"];
        if (this.highlighting[a] && this.highlighting[a].timer) window.clearInterval(this.highlighting[a].timer);
        this.highlighting[a] = {
            steps: s.duration / i,
            interval: i,
            currentStep: 0,
            start: c,
            end: e,
            orig: r,
            attr: a
        };
        jQuery.highlightFade(this, a, o["complete"], t);
    });
};

jQuery.highlightFade = function(e, a, o, t) {
    e.highlighting[a].timer = window.setInterval(function() {
        var newR = t(e.highlighting[a].start[0], e.highlighting[a].end[0], e.highlighting[a].steps, e.highlighting[a].currentStep);
        var newG = t(e.highlighting[a].start[1], e.highlighting[a].end[1], e.highlighting[a].steps, e.highlighting[a].currentStep);
        var newB = t(e.highlighting[a].start[2], e.highlighting[a].end[2], e.highlighting[a].steps, e.highlighting[a].currentStep);
        jQuery(e).css(a, jQuery.highlightFade.asRGBString([ newR, newG, newB ]));
        if (e.highlighting[a].currentStep++ >= e.highlighting[a].steps) {
            jQuery(e).css(a, e.highlighting[a].orig || "");
            window.clearInterval(e.highlighting[a].timer);
            e.highlighting[a] = null;
            if (o && o.constructor == Function) o.call(e);
        }
    }, e.highlighting[a].interval);
};

jQuery.highlightFade.defaults = {
    start: [ 255, 255, 128 ],
    interval: 50,
    speed: 400,
    attr: "backgroundColor"
};

jQuery.highlightFade.getRGB = function(c, d) {
    var result;
    if (c && c.constructor == Array && c.length == 3) return c;
    if (result = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(c)) return [ parseInt(result[1]), parseInt(result[2]), parseInt(result[3]) ]; else if (result = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(c)) return [ parseFloat(result[1]) * 2.55, parseFloat(result[2]) * 2.55, parseFloat(result[3]) * 2.55 ]; else if (result = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(c)) return [ parseInt("0x" + result[1]), parseInt("0x" + result[2]), parseInt("0x" + result[3]) ]; else if (result = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(c)) return [ parseInt("0x" + result[1] + result[1]), parseInt("0x" + result[2] + result[2]), parseInt("0x" + result[3] + result[3]) ]; else return jQuery.highlightFade.checkColorName(c) || d || null;
};

jQuery.highlightFade.asRGBString = function(a) {
    return "rgb(" + a.join(",") + ")";
};

jQuery.highlightFade.getBaseValue = function(e, a, b) {
    var s, t;
    b = b || false;
    t = a = a || jQuery.highlightFade.defaults["attr"];
    do {
        s = jQuery(e).css(t || "backgroundColor");
        if (s != "" && s != "transparent" || e.tagName.toLowerCase() == "body" || !b && e.highlighting && e.highlighting[a] && e.highlighting[a].end) break;
        t = false;
    } while (e = e.parentNode);
    if (!b && e.highlighting && e.highlighting[a] && e.highlighting[a].end) s = e.highlighting[a].end;
    if (s == undefined || s == "" || s == "transparent") s = [ 255, 255, 255 ];
    return jQuery.highlightFade.getRGB(s);
};

jQuery.highlightFade.checkColorName = function(c) {
    if (!c) return null;
    switch (c.replace(/^\s*|\s*$/g, "").toLowerCase()) {
      case "aqua":
        return [ 0, 255, 255 ];

      case "black":
        return [ 0, 0, 0 ];

      case "blue":
        return [ 0, 0, 255 ];

      case "fuchsia":
        return [ 255, 0, 255 ];

      case "gray":
        return [ 128, 128, 128 ];

      case "green":
        return [ 0, 128, 0 ];

      case "lime":
        return [ 0, 255, 0 ];

      case "maroon":
        return [ 128, 0, 0 ];

      case "navy":
        return [ 0, 0, 128 ];

      case "olive":
        return [ 128, 128, 0 ];

      case "purple":
        return [ 128, 0, 128 ];

      case "red":
        return [ 255, 0, 0 ];

      case "silver":
        return [ 192, 192, 192 ];

      case "teal":
        return [ 0, 128, 128 ];

      case "white":
        return [ 255, 255, 255 ];

      case "yellow":
        return [ 255, 255, 0 ];
    }
};

var form = "checkme";

function SetChecked(val, chkName) {
    dml = document.forms[form];
    len = dml.elements.length;
    var i = 0;
    for (i = 0; i < len; i++) {
        if (dml.elements[i].name == chkName) {
            dml.elements[i].checked = val;
        }
    }
}