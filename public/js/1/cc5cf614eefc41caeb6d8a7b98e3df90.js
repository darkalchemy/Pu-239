(function($) {
    var ColorPicker = function() {
        var ids = {}, inAction, charMin = 65, visible, tpl = '<div class="colorpicker"><div class="colorpicker_color"><div><div></div></div></div><div class="colorpicker_hue"><div></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_submit"></div></div>', defaults = {
            eventName: "click",
            onShow: function() {},
            onBeforeShow: function() {},
            onHide: function() {},
            onChange: function() {},
            onSubmit: function() {},
            color: "ff0000",
            livePreview: true,
            flat: false
        }, fillRGBFields = function(hsb, cal) {
            var rgb = HSBToRGB(hsb);
            $(cal).data("colorpicker").fields.eq(1).val(rgb.r).end().eq(2).val(rgb.g).end().eq(3).val(rgb.b).end();
        }, fillHSBFields = function(hsb, cal) {
            $(cal).data("colorpicker").fields.eq(4).val(hsb.h).end().eq(5).val(hsb.s).end().eq(6).val(hsb.b).end();
        }, fillHexFields = function(hsb, cal) {
            $(cal).data("colorpicker").fields.eq(0).val(HSBToHex(hsb)).end();
        }, setSelector = function(hsb, cal) {
            $(cal).data("colorpicker").selector.css("backgroundColor", "#" + HSBToHex({
                h: hsb.h,
                s: 100,
                b: 100
            }));
            $(cal).data("colorpicker").selectorIndic.css({
                left: parseInt(150 * hsb.s / 100, 10),
                top: parseInt(150 * (100 - hsb.b) / 100, 10)
            });
        }, setHue = function(hsb, cal) {
            $(cal).data("colorpicker").hue.css("top", parseInt(150 - 150 * hsb.h / 360, 10));
        }, setCurrentColor = function(hsb, cal) {
            $(cal).data("colorpicker").currentColor.css("backgroundColor", "#" + HSBToHex(hsb));
        }, setNewColor = function(hsb, cal) {
            $(cal).data("colorpicker").newColor.css("backgroundColor", "#" + HSBToHex(hsb));
        }, keyDown = function(ev) {
            var pressedKey = ev.charCode || ev.keyCode || -1;
            if (pressedKey > charMin && pressedKey <= 90 || pressedKey == 32) {
                return false;
            }
            var cal = $(this).parent().parent();
            if (cal.data("colorpicker").livePreview === true) {
                change.apply(this);
            }
        }, change = function(ev) {
            var cal = $(this).parent().parent(), col;
            if (this.parentNode.className.indexOf("_hex") > 0) {
                cal.data("colorpicker").color = col = HexToHSB(fixHex(this.value));
            } else if (this.parentNode.className.indexOf("_hsb") > 0) {
                cal.data("colorpicker").color = col = fixHSB({
                    h: parseInt(cal.data("colorpicker").fields.eq(4).val(), 10),
                    s: parseInt(cal.data("colorpicker").fields.eq(5).val(), 10),
                    b: parseInt(cal.data("colorpicker").fields.eq(6).val(), 10)
                });
            } else {
                cal.data("colorpicker").color = col = RGBToHSB(fixRGB({
                    r: parseInt(cal.data("colorpicker").fields.eq(1).val(), 10),
                    g: parseInt(cal.data("colorpicker").fields.eq(2).val(), 10),
                    b: parseInt(cal.data("colorpicker").fields.eq(3).val(), 10)
                }));
            }
            if (ev) {
                fillRGBFields(col, cal.get(0));
                fillHexFields(col, cal.get(0));
                fillHSBFields(col, cal.get(0));
            }
            setSelector(col, cal.get(0));
            setHue(col, cal.get(0));
            setNewColor(col, cal.get(0));
            cal.data("colorpicker").onChange.apply(cal, [ col, HSBToHex(col), HSBToRGB(col) ]);
        }, blur = function(ev) {
            var cal = $(this).parent().parent();
            cal.data("colorpicker").fields.parent().removeClass("colorpicker_focus");
        }, focus = function() {
            charMin = this.parentNode.className.indexOf("_hex") > 0 ? 70 : 65;
            $(this).parent().parent().data("colorpicker").fields.parent().removeClass("colorpicker_focus");
            $(this).parent().addClass("colorpicker_focus");
        }, downIncrement = function(ev) {
            var field = $(this).parent().find("input").focus();
            var current = {
                el: $(this).parent().addClass("colorpicker_slider"),
                max: this.parentNode.className.indexOf("_hsb_h") > 0 ? 360 : this.parentNode.className.indexOf("_hsb") > 0 ? 100 : 255,
                y: ev.pageY,
                field: field,
                val: parseInt(field.val(), 10),
                preview: $(this).parent().parent().data("colorpicker").livePreview
            };
            $(document).bind("mouseup", current, upIncrement);
            $(document).bind("mousemove", current, moveIncrement);
        }, moveIncrement = function(ev) {
            ev.data.field.val(Math.max(0, Math.min(ev.data.max, parseInt(ev.data.val + ev.pageY - ev.data.y, 10))));
            if (ev.data.preview) {
                change.apply(ev.data.field.get(0), [ true ]);
            }
            return false;
        }, upIncrement = function(ev) {
            change.apply(ev.data.field.get(0), [ true ]);
            ev.data.el.removeClass("colorpicker_slider").find("input").focus();
            $(document).unbind("mouseup", upIncrement);
            $(document).unbind("mousemove", moveIncrement);
            return false;
        }, downHue = function(ev) {
            var current = {
                cal: $(this).parent(),
                y: $(this).offset().top
            };
            current.preview = current.cal.data("colorpicker").livePreview;
            $(document).bind("mouseup", current, upHue);
            $(document).bind("mousemove", current, moveHue);
        }, moveHue = function(ev) {
            change.apply(ev.data.cal.data("colorpicker").fields.eq(4).val(parseInt(360 * (150 - Math.max(0, Math.min(150, ev.pageY - ev.data.y))) / 150, 10)).get(0), [ ev.data.preview ]);
            return false;
        }, upHue = function(ev) {
            fillRGBFields(ev.data.cal.data("colorpicker").color, ev.data.cal.get(0));
            fillHexFields(ev.data.cal.data("colorpicker").color, ev.data.cal.get(0));
            $(document).unbind("mouseup", upHue);
            $(document).unbind("mousemove", moveHue);
            return false;
        }, downSelector = function(ev) {
            var current = {
                cal: $(this).parent(),
                pos: $(this).offset()
            };
            current.preview = current.cal.data("colorpicker").livePreview;
            $(document).bind("mouseup", current, upSelector);
            $(document).bind("mousemove", current, moveSelector);
        }, moveSelector = function(ev) {
            change.apply(ev.data.cal.data("colorpicker").fields.eq(6).val(parseInt(100 * (150 - Math.max(0, Math.min(150, ev.pageY - ev.data.pos.top))) / 150, 10)).end().eq(5).val(parseInt(100 * Math.max(0, Math.min(150, ev.pageX - ev.data.pos.left)) / 150, 10)).get(0), [ ev.data.preview ]);
            return false;
        }, upSelector = function(ev) {
            fillRGBFields(ev.data.cal.data("colorpicker").color, ev.data.cal.get(0));
            fillHexFields(ev.data.cal.data("colorpicker").color, ev.data.cal.get(0));
            $(document).unbind("mouseup", upSelector);
            $(document).unbind("mousemove", moveSelector);
            return false;
        }, enterSubmit = function(ev) {
            $(this).addClass("colorpicker_focus");
        }, leaveSubmit = function(ev) {
            $(this).removeClass("colorpicker_focus");
        }, clickSubmit = function(ev) {
            var cal = $(this).parent();
            var col = cal.data("colorpicker").color;
            cal.data("colorpicker").origColor = col;
            setCurrentColor(col, cal.get(0));
            cal.data("colorpicker").onSubmit(col, HSBToHex(col), HSBToRGB(col), cal.data("colorpicker").el);
        }, show = function(ev) {
            var cal = $("#" + $(this).data("colorpickerId"));
            cal.data("colorpicker").onBeforeShow.apply(this, [ cal.get(0) ]);
            var pos = $(this).offset();
            var viewPort = getViewport();
            var top = ev.clientY;
            var left = pos.left + 40;
            if (top + 176 > viewPort.h) {
                top -= 176;
            }
            if (left + 356 > viewPort.l + viewPort.w) {
                left -= 356;
            }
            cal.css({
                left: left + "px",
                top: top + "px"
            });
            if (cal.data("colorpicker").onShow.apply(this, [ cal.get(0) ]) != false) {
                cal.show();
            }
            $(document).bind("mousedown", {
                cal: cal
            }, hide);
            return false;
        }, hide = function(ev) {
            if (!isChildOf(ev.data.cal.get(0), ev.target, ev.data.cal.get(0))) {
                if (ev.data.cal.data("colorpicker").onHide.apply(this, [ ev.data.cal.get(0) ]) != false) {
                    ev.data.cal.hide();
                }
                $(document).unbind("mousedown", hide);
            }
        }, isChildOf = function(parentEl, el, container) {
            if (parentEl == el) {
                return true;
            }
            if (parentEl.contains) {
                return parentEl.contains(el);
            }
            if (parentEl.compareDocumentPosition) {
                return !!(parentEl.compareDocumentPosition(el) & 16);
            }
            var prEl = el.parentNode;
            while (prEl && prEl != container) {
                if (prEl == parentEl) return true;
                prEl = prEl.parentNode;
            }
            return false;
        }, getViewport = function() {
            var m = document.compatMode == "CSS1Compat";
            return {
                l: window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
                t: window.pageYOffset || (m ? document.documentElement.scrollTop : document.body.scrollTop),
                w: window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth),
                h: window.innerHeight || (m ? document.documentElement.clientHeight : document.body.clientHeight)
            };
        }, fixHSB = function(hsb) {
            return {
                h: Math.min(360, Math.max(0, hsb.h)),
                s: Math.min(100, Math.max(0, hsb.s)),
                b: Math.min(100, Math.max(0, hsb.b))
            };
        }, fixRGB = function(rgb) {
            return {
                r: Math.min(255, Math.max(0, rgb.r)),
                g: Math.min(255, Math.max(0, rgb.g)),
                b: Math.min(255, Math.max(0, rgb.b))
            };
        }, fixHex = function(hex) {
            var len = 6 - hex.length;
            if (len > 0) {
                var o = [];
                for (var i = 0; i < len; i++) {
                    o.push("0");
                }
                o.push(hex);
                hex = o.join("");
            }
            return hex;
        }, HexToRGB = function(hex) {
            var hex = parseInt(hex.indexOf("#") > -1 ? hex.substring(1) : hex, 16);
            return {
                r: hex >> 16,
                g: (hex & 65280) >> 8,
                b: hex & 255
            };
        }, HexToHSB = function(hex) {
            return RGBToHSB(HexToRGB(hex));
        }, RGBToHSB = function(rgb) {
            var hsb = {
                h: 0,
                s: 0,
                b: 0
            };
            var min = Math.min(rgb.r, rgb.g, rgb.b);
            var max = Math.max(rgb.r, rgb.g, rgb.b);
            var delta = max - min;
            hsb.b = max;
            if (max != 0) {}
            hsb.s = max != 0 ? 255 * delta / max : 0;
            if (hsb.s != 0) {
                if (rgb.r == max) {
                    hsb.h = (rgb.g - rgb.b) / delta;
                } else if (rgb.g == max) {
                    hsb.h = 2 + (rgb.b - rgb.r) / delta;
                } else {
                    hsb.h = 4 + (rgb.r - rgb.g) / delta;
                }
            } else {
                hsb.h = -1;
            }
            hsb.h *= 60;
            if (hsb.h < 0) {
                hsb.h += 360;
            }
            hsb.s *= 100 / 255;
            hsb.b *= 100 / 255;
            return hsb;
        }, HSBToRGB = function(hsb) {
            var rgb = {};
            var h = Math.round(hsb.h);
            var s = Math.round(hsb.s * 255 / 100);
            var v = Math.round(hsb.b * 255 / 100);
            if (s == 0) {
                rgb.r = rgb.g = rgb.b = v;
            } else {
                var t1 = v;
                var t2 = (255 - s) * v / 255;
                var t3 = (t1 - t2) * (h % 60) / 60;
                if (h == 360) h = 0;
                if (h < 60) {
                    rgb.r = t1;
                    rgb.b = t2;
                    rgb.g = t2 + t3;
                } else if (h < 120) {
                    rgb.g = t1;
                    rgb.b = t2;
                    rgb.r = t1 - t3;
                } else if (h < 180) {
                    rgb.g = t1;
                    rgb.r = t2;
                    rgb.b = t2 + t3;
                } else if (h < 240) {
                    rgb.b = t1;
                    rgb.r = t2;
                    rgb.g = t1 - t3;
                } else if (h < 300) {
                    rgb.b = t1;
                    rgb.g = t2;
                    rgb.r = t2 + t3;
                } else if (h < 360) {
                    rgb.r = t1;
                    rgb.g = t2;
                    rgb.b = t1 - t3;
                } else {
                    rgb.r = 0;
                    rgb.g = 0;
                    rgb.b = 0;
                }
            }
            return {
                r: Math.round(rgb.r),
                g: Math.round(rgb.g),
                b: Math.round(rgb.b)
            };
        }, RGBToHex = function(rgb) {
            var hex = [ rgb.r.toString(16), rgb.g.toString(16), rgb.b.toString(16) ];
            $.each(hex, function(nr, val) {
                if (val.length == 1) {
                    hex[nr] = "0" + val;
                }
            });
            return hex.join("");
        }, HSBToHex = function(hsb) {
            return RGBToHex(HSBToRGB(hsb));
        }, restoreOriginal = function() {
            var cal = $(this).parent();
            var col = cal.data("colorpicker").origColor;
            cal.data("colorpicker").color = col;
            fillRGBFields(col, cal.get(0));
            fillHexFields(col, cal.get(0));
            fillHSBFields(col, cal.get(0));
            setSelector(col, cal.get(0));
            setHue(col, cal.get(0));
            setNewColor(col, cal.get(0));
        };
        return {
            init: function(opt) {
                opt = $.extend({}, defaults, opt || {});
                if (typeof opt.color == "string") {
                    opt.color = HexToHSB(opt.color);
                } else if (opt.color.r != undefined && opt.color.g != undefined && opt.color.b != undefined) {
                    opt.color = RGBToHSB(opt.color);
                } else if (opt.color.h != undefined && opt.color.s != undefined && opt.color.b != undefined) {
                    opt.color = fixHSB(opt.color);
                } else {
                    return this;
                }
                return this.each(function() {
                    if (!$(this).data("colorpickerId")) {
                        var options = $.extend({}, opt);
                        options.origColor = opt.color;
                        var id = "collorpicker_" + parseInt(Math.random() * 1e3);
                        $(this).data("colorpickerId", id);
                        var cal = $(tpl).attr("id", id);
                        if (options.flat) {
                            cal.appendTo(this).show();
                        } else {
                            cal.appendTo(document.body);
                        }
                        options.fields = cal.find("input").bind("keyup", keyDown).bind("change", change).bind("blur", blur).bind("focus", focus);
                        cal.find("span").bind("mousedown", downIncrement).end().find(">div.colorpicker_current_color").bind("click", restoreOriginal);
                        options.selector = cal.find("div.colorpicker_color").bind("mousedown", downSelector);
                        options.selectorIndic = options.selector.find("div div");
                        options.el = this;
                        options.hue = cal.find("div.colorpicker_hue div");
                        cal.find("div.colorpicker_hue").bind("mousedown", downHue);
                        options.newColor = cal.find("div.colorpicker_new_color");
                        options.currentColor = cal.find("div.colorpicker_current_color");
                        cal.data("colorpicker", options);
                        cal.find("div.colorpicker_submit").bind("mouseenter", enterSubmit).bind("mouseleave", leaveSubmit).bind("click", clickSubmit);
                        fillRGBFields(options.color, cal.get(0));
                        fillHSBFields(options.color, cal.get(0));
                        fillHexFields(options.color, cal.get(0));
                        setHue(options.color, cal.get(0));
                        setSelector(options.color, cal.get(0));
                        setCurrentColor(options.color, cal.get(0));
                        setNewColor(options.color, cal.get(0));
                        if (options.flat) {
                            cal.css({
                                position: "relative",
                                display: "block"
                            });
                        } else {
                            $(this).bind(options.eventName, show);
                        }
                    }
                });
            },
            showPicker: function() {
                return this.each(function() {
                    if ($(this).data("colorpickerId")) {
                        show.apply(this);
                    }
                });
            },
            hidePicker: function() {
                return this.each(function() {
                    if ($(this).data("colorpickerId")) {
                        $("#" + $(this).data("colorpickerId")).hide();
                    }
                });
            },
            setColor: function(col) {
                if (typeof col == "string") {
                    col = HexToHSB(col);
                } else if (col.r != undefined && col.g != undefined && col.b != undefined) {
                    col = RGBToHSB(col);
                } else if (col.h != undefined && col.s != undefined && col.b != undefined) {
                    col = fixHSB(col);
                } else {
                    return this;
                }
                return this.each(function() {
                    if ($(this).data("colorpickerId")) {
                        var cal = $("#" + $(this).data("colorpickerId"));
                        cal.data("colorpicker").color = col;
                        cal.data("colorpicker").origColor = col;
                        fillRGBFields(col, cal.get(0));
                        fillHSBFields(col, cal.get(0));
                        fillHexFields(col, cal.get(0));
                        setHue(col, cal.get(0));
                        setSelector(col, cal.get(0));
                        setCurrentColor(col, cal.get(0));
                        setNewColor(col, cal.get(0));
                    }
                });
            }
        };
    }();
    $.fn.extend({
        ColorPicker: ColorPicker.init,
        ColorPickerHide: ColorPicker.hidePicker,
        ColorPickerShow: ColorPicker.showPicker,
        ColorPickerSetColor: ColorPicker.setColor
    });
})(jQuery);

var page_config = {
    nav: {
        0: {
            name: "Light",
            className: "skin-1"
        },
        1: {
            name: "Dark",
            className: "skin-2"
        }
    },
    backgrounds: {
        0: {
            name: "Background 1",
            className: "background-1"
        },
        1: {
            name: "Background 2",
            className: "background-2"
        },
        2: {
            name: "Background 3",
            className: "background-3"
        },
        3: {
            name: "Background 4",
            className: "background-4"
        },
        4: {
            name: "Background 5",
            className: "background-5"
        },
        5: {
            name: "Background 6",
            className: "background-6"
        },
        6: {
            name: "Background 7",
            className: "background-7"
        },
        7: {
            name: "Background 8",
            className: "background-8"
        },
        8: {
            name: "Background 9",
            className: "background-9"
        },
        9: {
            name: "Background 10",
            className: "background-10"
        },
        10: {
            name: "Background 11",
            className: "background-11"
        },
        11: {
            name: "Background 12",
            className: "background-12"
        },
        12: {
            name: "Background 13",
            className: "background-13"
        },
        13: {
            name: "Background 14",
            className: "background-14"
        },
        14: {
            name: "Background 15",
            className: "background-15"
        },
        15: {
            name: "Default",
            className: "background-16"
        }
    },
    styles: {
        headerStyle: {
            name: "Heading Font",
            id: "heading_style",
            list: {
                0: {
                    name: "Oswald",
                    className: "h-style-1",
                    class: "text-1"
                },
                1: {
                    name: "PT Sans Narrow",
                    className: "h-style-2",
                    class: "text-2"
                },
                2: {
                    name: "Nova Square",
                    className: "h-style-3",
                    class: "text-3"
                },
                3: {
                    name: "Lobster",
                    className: "h-style-4",
                    class: "text-4"
                },
                4: {
                    name: "Open Sans",
                    className: "h-style-5",
                    class: "text-5"
                },
                5: {
                    name: "Encode Sans Condensed",
                    className: "h-style-6",
                    class: "text-6"
                },
                6: {
                    name: "Baloo Bhaijaan",
                    className: "h-style-7",
                    class: "text-7"
                },
                7: {
                    name: "Acme",
                    className: "h-style-8",
                    class: "text-8"
                },
                8: {
                    name: "Default",
                    className: "h-style-9",
                    class: "text-9"
                }
            }
        },
        textStyle: {
            name: "Content Font",
            id: "text_style",
            list: {
                0: {
                    name: "Oswald",
                    className: "text-1",
                    class: "text-1"
                },
                1: {
                    name: "PT Sans Narrow",
                    className: "text-2",
                    class: "text-2"
                },
                2: {
                    name: "Nova Square",
                    className: "text-3",
                    class: "text-3"
                },
                3: {
                    name: "Lobster",
                    className: "text-4",
                    class: "text-4"
                },
                4: {
                    name: "Open Sans",
                    className: "text-5",
                    class: "text-5"
                },
                5: {
                    name: "Encode Sans Condensed",
                    className: "text-6",
                    class: "text-6"
                },
                6: {
                    name: "Baloo Bhaijaan",
                    className: "text-7",
                    class: "text-7"
                },
                7: {
                    name: "Acme",
                    className: "text-8",
                    class: "text-8"
                },
                8: {
                    name: "Default",
                    className: "text-9",
                    class: "text-9"
                }
            }
        }
    }
};

$(function() {
    var $body = $("body");
    var $nav = $(".navigation li a");
    var $theme_control_panel = $("#control_panel");
    var a_color = localStorage.getItem("a_color");
    if (a_color != null) {
        $("body").get(0).style.setProperty("--main-color", "#" + a_color);
        $("iframe").contents().find("body").css("--main-color", "#" + a_color);
    }
    function changeBodyClass(className, classesArray) {
        $.each(classesArray, function(idx, val) {
            $body.removeClass(val);
        });
        $body.addClass(className);
        var body_class = localStorage.getItem("theme");
        if (body_class != null) {
            new_class_pattern = className.replace(/\d{1,2}$/, "");
            body_class = body_class.replace(new RegExp(new_class_pattern + "\\d{1,2}"), className);
            localStorage.setItem("theme", body_class);
        } else {
            body_class = $body.attr("class");
            localStorage.setItem("theme", body_class);
        }
    }
    if (typeof page_config != "undefined" && $theme_control_panel) {
        var pattern_classes = new Array();
        var nav = new Array();
        var defaultSettings = {};
        if (page_config.nav) {
            var $bg_block = $("<div/>").attr("id", "nav").addClass("style_block clearfix");
            var $header = $("#header");
            var bg_change_html = "<span>Menu Skin:</span>";
            bg_change_html += "<ul>";
            $.each(page_config.nav, function(idx, val) {
                bg_change_html += '<li><a href="' + val.className + '" title="' + val.name + '" class="tooltipper ' + val.className + '"></a></li>';
                nav.push(val.className);
            });
            bg_change_html += "</ul>";
            $bg_block.html(bg_change_html);
            $theme_control_panel.append($bg_block);
            $bg_block.find("a").click(function() {
                var nextClassName = $(this).attr("href");
                if (!$body.hasClass(nextClassName)) {
                    changeBodyClass(nextClassName, nav);
                    $bg_block.find(".active").removeClass("active");
                    $(this).parent().addClass("active");
                }
                return false;
            });
        }
        if (page_config.backgrounds) {
            var $bg_block = $("<div/>").attr("id", "backgrounds").addClass("style_block");
            var bg_change_html = "<span>Backgrounds:</span>";
            bg_change_html += '<ul class="limited">';
            $.each(page_config.backgrounds, function(idx, val) {
                bg_change_html += '<li><a href="' + val.className + '" title="' + val.name + '" class="tooltipper ' + val.className + '"></a></li>';
                pattern_classes.push(val.className);
            });
            bg_change_html += "</ul>";
            $bg_block.html(bg_change_html);
            $theme_control_panel.append($bg_block);
            $bg_block.find("a").click(function() {
                var nextClassName = $(this).attr("href");
                if (!$body.hasClass(nextClassName)) {
                    changeBodyClass(nextClassName, pattern_classes);
                    $bg_block.find(".active").removeClass("active");
                    $(this).parent().addClass("active");
                }
                return false;
            });
        }
        if (page_config.styles) {
            var $style_block;
            var $block_label;
            var $select_element;
            var $links_color;
            var $links_color_wrapper;
            var select_html;
            var header_style_classes = [];
            var text_style_classes = [];
            defaultSettings.style = {};
            $.each(page_config.styles, function(idx, val) {
                $style_block = $("<div/>").addClass("style_block");
                $block_label = $("<span>" + val.name + ":</span>");
                $select_element = $("<select/>").attr({
                    id: val.id
                });
                select_html = "";
                $.each(val.list, function(list_idx, list_val) {
                    if (list_val.class) {
                        var classes = ' class="' + list_val.class + '"';
                    }
                    if ($body.hasClass(list_val.className)) {
                        select_html += '<option value="' + list_val.className + '" ' + (classes || "") + ' selected="selected">' + list_val.name + "</option>";
                    } else {
                        select_html += '<option value="' + list_val.className + '"' + (classes || "") + ">" + list_val.name + "</option>";
                    }
                });
                $select_element.html(select_html);
                $style_block.append($block_label, $select_element);
                $theme_control_panel.append($style_block);
            });
            $.each(page_config.styles.headerStyle.list, function(idx, val) {
                header_style_classes.push(val.className);
            });
            $("#heading_style").change(function() {
                if (!$body.hasClass($(this).val())) {
                    changeBodyClass($(this).val(), header_style_classes);
                }
            });
            $.each(page_config.styles.textStyle.list, function(idx, val) {
                text_style_classes.push(val.className);
            });
            $("#text_style").change(function() {
                if (!$body.hasClass($(this).val())) {
                    changeBodyClass($(this).val(), text_style_classes);
                    $("iframe").contents().find("body").removeClass("text-1 text-2 text-3 text-4 text-5 text-6 text-7 text-8").addClass($(this).val());
                }
            });
            $links_color = $("<div/>").attr({
                id: "linkspicker"
            }).addClass("colorPicker");
            $links_color_wrapper = $("<div/>").addClass("links_color_wrapper clearfix");
            $links_color_wrapper.append("<span>Links Color:</span>", $links_color);
            $theme_control_panel.append($links_color_wrapper);
            var links_picker = $("#linkspicker");
            links_picker.css("background-color", "#0a0b35").ColorPicker({
                color: "#0a0b35",
                onChange: function(hsb, hex, rgb) {
                    links_picker.css("backgroundColor", "#" + hex);
                    $("body").get(0).style.setProperty("--main-color", "#" + hex);
                    $("iframe").contents().find("body").css("--main-color", "#" + hex);
                    localStorage.setItem("a_color", hex);
                }
            });
            var setDefaultsSettings = function() {
                changeBodyClass(page_config.nav[1].className, nav);
                changeBodyClass(page_config.backgrounds[15].className, pattern_classes);
                $theme_control_panel.find("select").val(1);
                changeBodyClass(page_config.styles.headerStyle.list[8].className, header_style_classes);
                changeBodyClass(page_config.styles.textStyle.list[8].className, text_style_classes);
                $("iframe").contents().find("body").removeClass("text-1 text-2 text-3 text-4 text-5 text-6 text-7 text-8").addClass("text-9");
                links_picker.css({
                    "background-color": "#008a05"
                }).ColorPickerSetColor("#008a05");
                $("body").get(0).style.setProperty("--main-color", "#9193de");
                $("iframe").contents().find("body").css("--main-color", "#9193de");
                $theme_control_panel.find(".active").removeClass();
                localStorage.removeItem("a_color");
                return false;
            };
            var $restore_button_wrapper = $("<div/>").addClass("restore_button_wrapper");
            var $restore_button = $("<a/>").text("Reset").attr("id", "restore_button").addClass("button small dark").click(setDefaultsSettings);
            $restore_button_wrapper.append($restore_button);
            $theme_control_panel.append($restore_button_wrapper);
        }
        var $theme_control_panel_label = $("#control_label");
        $theme_control_panel_label.click(function() {
            if ($theme_control_panel.hasClass("visible")) {
                $theme_control_panel.animate({
                    left: -210
                }, 400, function() {
                    $theme_control_panel.removeClass("visible");
                });
            } else {
                $theme_control_panel.animate({
                    left: 0
                }, 400, function() {
                    $theme_control_panel.addClass("visible");
                });
            }
            return false;
        });
    }
});

(function(root, factory) {
    if (typeof define === "function" && define.amd) {
        define([ "jquery" ], function(a0) {
            return factory(a0);
        });
    } else if (typeof exports === "object") {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
})(this, function($) {
    var defaults = {
        animation: "fade",
        animationDuration: 350,
        content: null,
        contentAsHTML: false,
        contentCloning: false,
        debug: true,
        delay: 300,
        delayTouch: [ 300, 500 ],
        functionInit: null,
        functionBefore: null,
        functionReady: null,
        functionAfter: null,
        functionFormat: null,
        IEmin: 6,
        interactive: false,
        multiple: false,
        parent: null,
        plugins: [ "sideTip" ],
        repositionOnScroll: false,
        restoration: "none",
        selfDestruction: true,
        theme: [],
        timer: 0,
        trackerInterval: 500,
        trackOrigin: false,
        trackTooltip: false,
        trigger: "hover",
        triggerClose: {
            click: false,
            mouseleave: false,
            originClick: false,
            scroll: false,
            tap: false,
            touchleave: false
        },
        triggerOpen: {
            click: false,
            mouseenter: false,
            tap: false,
            touchstart: false
        },
        updateAnimation: "rotate",
        zIndex: 9999999
    }, win = typeof window != "undefined" ? window : null, env = {
        hasTouchCapability: !!(win && ("ontouchstart" in win || win.DocumentTouch && win.document instanceof win.DocumentTouch || win.navigator.maxTouchPoints)),
        hasTransitions: transitionSupport(),
        IE: false,
        semVer: "4.2.5",
        window: win
    }, core = function() {
        this.__$emitterPrivate = $({});
        this.__$emitterPublic = $({});
        this.__instancesLatestArr = [];
        this.__plugins = {};
        this._env = env;
    };
    core.prototype = {
        __bridge: function(constructor, obj, pluginName) {
            if (!obj[pluginName]) {
                var fn = function() {};
                fn.prototype = constructor;
                var pluginInstance = new fn();
                if (pluginInstance.__init) {
                    pluginInstance.__init(obj);
                }
                $.each(constructor, function(methodName, fn) {
                    if (methodName.indexOf("__") != 0) {
                        if (!obj[methodName]) {
                            obj[methodName] = function() {
                                return pluginInstance[methodName].apply(pluginInstance, Array.prototype.slice.apply(arguments));
                            };
                            obj[methodName].bridged = pluginInstance;
                        } else if (defaults.debug) {
                            console.log("The " + methodName + " method of the " + pluginName + " plugin conflicts with another plugin or native methods");
                        }
                    }
                });
                obj[pluginName] = pluginInstance;
            }
            return this;
        },
        __setWindow: function(window) {
            env.window = window;
            return this;
        },
        _getRuler: function($tooltip) {
            return new Ruler($tooltip);
        },
        _off: function() {
            this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _on: function() {
            this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _one: function() {
            this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _plugin: function(plugin) {
            var self = this;
            if (typeof plugin == "string") {
                var pluginName = plugin, p = null;
                if (pluginName.indexOf(".") > 0) {
                    p = self.__plugins[pluginName];
                } else {
                    $.each(self.__plugins, function(i, plugin) {
                        if (plugin.name.substring(plugin.name.length - pluginName.length - 1) == "." + pluginName) {
                            p = plugin;
                            return false;
                        }
                    });
                }
                return p;
            } else {
                if (plugin.name.indexOf(".") < 0) {
                    throw new Error("Plugins must be namespaced");
                }
                self.__plugins[plugin.name] = plugin;
                if (plugin.core) {
                    self.__bridge(plugin.core, self, plugin.name);
                }
                return this;
            }
        },
        _trigger: function() {
            var args = Array.prototype.slice.apply(arguments);
            if (typeof args[0] == "string") {
                args[0] = {
                    type: args[0]
                };
            }
            this.__$emitterPrivate.trigger.apply(this.__$emitterPrivate, args);
            this.__$emitterPublic.trigger.apply(this.__$emitterPublic, args);
            return this;
        },
        instances: function(selector) {
            var instances = [], sel = selector || ".tooltipstered";
            $(sel).each(function() {
                var $this = $(this), ns = $this.data("tooltipster-ns");
                if (ns) {
                    $.each(ns, function(i, namespace) {
                        instances.push($this.data(namespace));
                    });
                }
            });
            return instances;
        },
        instancesLatest: function() {
            return this.__instancesLatestArr;
        },
        off: function() {
            this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        on: function() {
            this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        one: function() {
            this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        origins: function(selector) {
            var sel = selector ? selector + " " : "";
            return $(sel + ".tooltipstered").toArray();
        },
        setDefaults: function(d) {
            $.extend(defaults, d);
            return this;
        },
        triggerHandler: function() {
            this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        }
    };
    $.tooltipster = new core();
    $.Tooltipster = function(element, options) {
        this.__callbacks = {
            close: [],
            open: []
        };
        this.__closingTime;
        this.__Content;
        this.__contentBcr;
        this.__destroyed = false;
        this.__$emitterPrivate = $({});
        this.__$emitterPublic = $({});
        this.__enabled = true;
        this.__garbageCollector;
        this.__Geometry;
        this.__lastPosition;
        this.__namespace = "tooltipster-" + Math.round(Math.random() * 1e6);
        this.__options;
        this.__$originParents;
        this.__pointerIsOverOrigin = false;
        this.__previousThemes = [];
        this.__state = "closed";
        this.__timeouts = {
            close: [],
            open: null
        };
        this.__touchEvents = [];
        this.__tracker = null;
        this._$origin;
        this._$tooltip;
        this.__init(element, options);
    };
    $.Tooltipster.prototype = {
        __init: function(origin, options) {
            var self = this;
            self._$origin = $(origin);
            self.__options = $.extend(true, {}, defaults, options);
            self.__optionsFormat();
            if (!env.IE || env.IE >= self.__options.IEmin) {
                var initialTitle = null;
                if (self._$origin.data("tooltipster-initialTitle") === undefined) {
                    initialTitle = self._$origin.attr("title");
                    if (initialTitle === undefined) initialTitle = null;
                    self._$origin.data("tooltipster-initialTitle", initialTitle);
                }
                if (self.__options.content !== null) {
                    self.__contentSet(self.__options.content);
                } else {
                    var selector = self._$origin.attr("data-tooltip-content"), $el;
                    if (selector) {
                        $el = $(selector);
                    }
                    if ($el && $el[0]) {
                        self.__contentSet($el.first());
                    } else {
                        self.__contentSet(initialTitle);
                    }
                }
                self._$origin.removeAttr("title").addClass("tooltipstered");
                self.__prepareOrigin();
                self.__prepareGC();
                $.each(self.__options.plugins, function(i, pluginName) {
                    self._plug(pluginName);
                });
                if (env.hasTouchCapability) {
                    $(env.window.document.body).on("touchmove." + self.__namespace + "-triggerOpen", function(event) {
                        self._touchRecordEvent(event);
                    });
                }
                self._on("created", function() {
                    self.__prepareTooltip();
                })._on("repositioned", function(e) {
                    self.__lastPosition = e.position;
                });
            } else {
                self.__options.disabled = true;
            }
        },
        __contentInsert: function() {
            var self = this, $el = self._$tooltip.find(".tooltipster-content"), formattedContent = self.__Content, format = function(content) {
                formattedContent = content;
            };
            self._trigger({
                type: "format",
                content: self.__Content,
                format: format
            });
            if (self.__options.functionFormat) {
                formattedContent = self.__options.functionFormat.call(self, self, {
                    origin: self._$origin[0]
                }, self.__Content);
            }
            if (typeof formattedContent === "string" && !self.__options.contentAsHTML) {
                $el.text(formattedContent);
            } else {
                $el.empty().append(formattedContent);
            }
            return self;
        },
        __contentSet: function(content) {
            if (content instanceof $ && this.__options.contentCloning) {
                content = content.clone(true);
            }
            this.__Content = content;
            this._trigger({
                type: "updated",
                content: content
            });
            return this;
        },
        __destroyError: function() {
            throw new Error("This tooltip has been destroyed and cannot execute your method call.");
        },
        __geometry: function() {
            var self = this, $target = self._$origin, originIsArea = self._$origin.is("area");
            if (originIsArea) {
                var mapName = self._$origin.parent().attr("name");
                $target = $('img[usemap="#' + mapName + '"]');
            }
            var bcr = $target[0].getBoundingClientRect(), $document = $(env.window.document), $window = $(env.window), $parent = $target, geo = {
                available: {
                    document: null,
                    window: null
                },
                document: {
                    size: {
                        height: $document.height(),
                        width: $document.width()
                    }
                },
                window: {
                    scroll: {
                        left: env.window.scrollX || env.window.document.documentElement.scrollLeft,
                        top: env.window.scrollY || env.window.document.documentElement.scrollTop
                    },
                    size: {
                        height: $window.height(),
                        width: $window.width()
                    }
                },
                origin: {
                    fixedLineage: false,
                    offset: {},
                    size: {
                        height: bcr.bottom - bcr.top,
                        width: bcr.right - bcr.left
                    },
                    usemapImage: originIsArea ? $target[0] : null,
                    windowOffset: {
                        bottom: bcr.bottom,
                        left: bcr.left,
                        right: bcr.right,
                        top: bcr.top
                    }
                }
            }, geoFixed = false;
            if (originIsArea) {
                var shape = self._$origin.attr("shape"), coords = self._$origin.attr("coords");
                if (coords) {
                    coords = coords.split(",");
                    $.map(coords, function(val, i) {
                        coords[i] = parseInt(val);
                    });
                }
                if (shape != "default") {
                    switch (shape) {
                      case "circle":
                        var circleCenterLeft = coords[0], circleCenterTop = coords[1], circleRadius = coords[2], areaTopOffset = circleCenterTop - circleRadius, areaLeftOffset = circleCenterLeft - circleRadius;
                        geo.origin.size.height = circleRadius * 2;
                        geo.origin.size.width = geo.origin.size.height;
                        geo.origin.windowOffset.left += areaLeftOffset;
                        geo.origin.windowOffset.top += areaTopOffset;
                        break;

                      case "rect":
                        var areaLeft = coords[0], areaTop = coords[1], areaRight = coords[2], areaBottom = coords[3];
                        geo.origin.size.height = areaBottom - areaTop;
                        geo.origin.size.width = areaRight - areaLeft;
                        geo.origin.windowOffset.left += areaLeft;
                        geo.origin.windowOffset.top += areaTop;
                        break;

                      case "poly":
                        var areaSmallestX = 0, areaSmallestY = 0, areaGreatestX = 0, areaGreatestY = 0, arrayAlternate = "even";
                        for (var i = 0; i < coords.length; i++) {
                            var areaNumber = coords[i];
                            if (arrayAlternate == "even") {
                                if (areaNumber > areaGreatestX) {
                                    areaGreatestX = areaNumber;
                                    if (i === 0) {
                                        areaSmallestX = areaGreatestX;
                                    }
                                }
                                if (areaNumber < areaSmallestX) {
                                    areaSmallestX = areaNumber;
                                }
                                arrayAlternate = "odd";
                            } else {
                                if (areaNumber > areaGreatestY) {
                                    areaGreatestY = areaNumber;
                                    if (i == 1) {
                                        areaSmallestY = areaGreatestY;
                                    }
                                }
                                if (areaNumber < areaSmallestY) {
                                    areaSmallestY = areaNumber;
                                }
                                arrayAlternate = "even";
                            }
                        }
                        geo.origin.size.height = areaGreatestY - areaSmallestY;
                        geo.origin.size.width = areaGreatestX - areaSmallestX;
                        geo.origin.windowOffset.left += areaSmallestX;
                        geo.origin.windowOffset.top += areaSmallestY;
                        break;
                    }
                }
            }
            var edit = function(r) {
                geo.origin.size.height = r.height, geo.origin.windowOffset.left = r.left, geo.origin.windowOffset.top = r.top, 
                geo.origin.size.width = r.width;
            };
            self._trigger({
                type: "geometry",
                edit: edit,
                geometry: {
                    height: geo.origin.size.height,
                    left: geo.origin.windowOffset.left,
                    top: geo.origin.windowOffset.top,
                    width: geo.origin.size.width
                }
            });
            geo.origin.windowOffset.right = geo.origin.windowOffset.left + geo.origin.size.width;
            geo.origin.windowOffset.bottom = geo.origin.windowOffset.top + geo.origin.size.height;
            geo.origin.offset.left = geo.origin.windowOffset.left + geo.window.scroll.left;
            geo.origin.offset.top = geo.origin.windowOffset.top + geo.window.scroll.top;
            geo.origin.offset.bottom = geo.origin.offset.top + geo.origin.size.height;
            geo.origin.offset.right = geo.origin.offset.left + geo.origin.size.width;
            geo.available.document = {
                bottom: {
                    height: geo.document.size.height - geo.origin.offset.bottom,
                    width: geo.document.size.width
                },
                left: {
                    height: geo.document.size.height,
                    width: geo.origin.offset.left
                },
                right: {
                    height: geo.document.size.height,
                    width: geo.document.size.width - geo.origin.offset.right
                },
                top: {
                    height: geo.origin.offset.top,
                    width: geo.document.size.width
                }
            };
            geo.available.window = {
                bottom: {
                    height: Math.max(geo.window.size.height - Math.max(geo.origin.windowOffset.bottom, 0), 0),
                    width: geo.window.size.width
                },
                left: {
                    height: geo.window.size.height,
                    width: Math.max(geo.origin.windowOffset.left, 0)
                },
                right: {
                    height: geo.window.size.height,
                    width: Math.max(geo.window.size.width - Math.max(geo.origin.windowOffset.right, 0), 0)
                },
                top: {
                    height: Math.max(geo.origin.windowOffset.top, 0),
                    width: geo.window.size.width
                }
            };
            while ($parent[0].tagName.toLowerCase() != "html") {
                if ($parent.css("position") == "fixed") {
                    geo.origin.fixedLineage = true;
                    break;
                }
                $parent = $parent.parent();
            }
            return geo;
        },
        __optionsFormat: function() {
            if (typeof this.__options.animationDuration == "number") {
                this.__options.animationDuration = [ this.__options.animationDuration, this.__options.animationDuration ];
            }
            if (typeof this.__options.delay == "number") {
                this.__options.delay = [ this.__options.delay, this.__options.delay ];
            }
            if (typeof this.__options.delayTouch == "number") {
                this.__options.delayTouch = [ this.__options.delayTouch, this.__options.delayTouch ];
            }
            if (typeof this.__options.theme == "string") {
                this.__options.theme = [ this.__options.theme ];
            }
            if (this.__options.parent === null) {
                this.__options.parent = $(env.window.document.body);
            } else if (typeof this.__options.parent == "string") {
                this.__options.parent = $(this.__options.parent);
            }
            if (this.__options.trigger == "hover") {
                this.__options.triggerOpen = {
                    mouseenter: true,
                    touchstart: true
                };
                this.__options.triggerClose = {
                    mouseleave: true,
                    originClick: true,
                    touchleave: true
                };
            } else if (this.__options.trigger == "click") {
                this.__options.triggerOpen = {
                    click: true,
                    tap: true
                };
                this.__options.triggerClose = {
                    click: true,
                    tap: true
                };
            }
            this._trigger("options");
            return this;
        },
        __prepareGC: function() {
            var self = this;
            if (self.__options.selfDestruction) {
                self.__garbageCollector = setInterval(function() {
                    var now = new Date().getTime();
                    self.__touchEvents = $.grep(self.__touchEvents, function(event, i) {
                        return now - event.time > 6e4;
                    });
                    if (!bodyContains(self._$origin)) {
                        self.close(function() {
                            self.destroy();
                        });
                    }
                }, 2e4);
            } else {
                clearInterval(self.__garbageCollector);
            }
            return self;
        },
        __prepareOrigin: function() {
            var self = this;
            self._$origin.off("." + self.__namespace + "-triggerOpen");
            if (env.hasTouchCapability) {
                self._$origin.on("touchstart." + self.__namespace + "-triggerOpen " + "touchend." + self.__namespace + "-triggerOpen " + "touchcancel." + self.__namespace + "-triggerOpen", function(event) {
                    self._touchRecordEvent(event);
                });
            }
            if (self.__options.triggerOpen.click || self.__options.triggerOpen.tap && env.hasTouchCapability) {
                var eventNames = "";
                if (self.__options.triggerOpen.click) {
                    eventNames += "click." + self.__namespace + "-triggerOpen ";
                }
                if (self.__options.triggerOpen.tap && env.hasTouchCapability) {
                    eventNames += "touchend." + self.__namespace + "-triggerOpen";
                }
                self._$origin.on(eventNames, function(event) {
                    if (self._touchIsMeaningfulEvent(event)) {
                        self._open(event);
                    }
                });
            }
            if (self.__options.triggerOpen.mouseenter || self.__options.triggerOpen.touchstart && env.hasTouchCapability) {
                var eventNames = "";
                if (self.__options.triggerOpen.mouseenter) {
                    eventNames += "mouseenter." + self.__namespace + "-triggerOpen ";
                }
                if (self.__options.triggerOpen.touchstart && env.hasTouchCapability) {
                    eventNames += "touchstart." + self.__namespace + "-triggerOpen";
                }
                self._$origin.on(eventNames, function(event) {
                    if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                        self.__pointerIsOverOrigin = true;
                        self._openShortly(event);
                    }
                });
            }
            if (self.__options.triggerClose.mouseleave || self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                var eventNames = "";
                if (self.__options.triggerClose.mouseleave) {
                    eventNames += "mouseleave." + self.__namespace + "-triggerOpen ";
                }
                if (self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                    eventNames += "touchend." + self.__namespace + "-triggerOpen touchcancel." + self.__namespace + "-triggerOpen";
                }
                self._$origin.on(eventNames, function(event) {
                    if (self._touchIsMeaningfulEvent(event)) {
                        self.__pointerIsOverOrigin = false;
                    }
                });
            }
            return self;
        },
        __prepareTooltip: function() {
            var self = this, p = self.__options.interactive ? "auto" : "";
            self._$tooltip.attr("id", self.__namespace).css({
                "pointer-events": p,
                zIndex: self.__options.zIndex
            });
            $.each(self.__previousThemes, function(i, theme) {
                self._$tooltip.removeClass(theme);
            });
            $.each(self.__options.theme, function(i, theme) {
                self._$tooltip.addClass(theme);
            });
            self.__previousThemes = $.merge([], self.__options.theme);
            return self;
        },
        __scrollHandler: function(event) {
            var self = this;
            if (self.__options.triggerClose.scroll) {
                self._close(event);
            } else {
                if (bodyContains(self._$origin) && bodyContains(self._$tooltip)) {
                    var geo = null;
                    if (event.target === env.window.document) {
                        if (!self.__Geometry.origin.fixedLineage) {
                            if (self.__options.repositionOnScroll) {
                                self.reposition(event);
                            }
                        }
                    } else {
                        geo = self.__geometry();
                        var overflows = false;
                        if (self._$origin.css("position") != "fixed") {
                            self.__$originParents.each(function(i, el) {
                                var $el = $(el), overflowX = $el.css("overflow-x"), overflowY = $el.css("overflow-y");
                                if (overflowX != "visible" || overflowY != "visible") {
                                    var bcr = el.getBoundingClientRect();
                                    if (overflowX != "visible") {
                                        if (geo.origin.windowOffset.left < bcr.left || geo.origin.windowOffset.right > bcr.right) {
                                            overflows = true;
                                            return false;
                                        }
                                    }
                                    if (overflowY != "visible") {
                                        if (geo.origin.windowOffset.top < bcr.top || geo.origin.windowOffset.bottom > bcr.bottom) {
                                            overflows = true;
                                            return false;
                                        }
                                    }
                                }
                                if ($el.css("position") == "fixed") {
                                    return false;
                                }
                            });
                        }
                        if (overflows) {
                            self._$tooltip.css("visibility", "hidden");
                        } else {
                            self._$tooltip.css("visibility", "visible");
                            if (self.__options.repositionOnScroll) {
                                self.reposition(event);
                            } else {
                                var offsetLeft = geo.origin.offset.left - self.__Geometry.origin.offset.left, offsetTop = geo.origin.offset.top - self.__Geometry.origin.offset.top;
                                self._$tooltip.css({
                                    left: self.__lastPosition.coord.left + offsetLeft,
                                    top: self.__lastPosition.coord.top + offsetTop
                                });
                            }
                        }
                    }
                    self._trigger({
                        type: "scroll",
                        event: event,
                        geo: geo
                    });
                }
            }
            return self;
        },
        __stateSet: function(state) {
            this.__state = state;
            this._trigger({
                type: "state",
                state: state
            });
            return this;
        },
        __timeoutsClear: function() {
            clearTimeout(this.__timeouts.open);
            this.__timeouts.open = null;
            $.each(this.__timeouts.close, function(i, timeout) {
                clearTimeout(timeout);
            });
            this.__timeouts.close = [];
            return this;
        },
        __trackerStart: function() {
            var self = this, $content = self._$tooltip.find(".tooltipster-content");
            if (self.__options.trackTooltip) {
                self.__contentBcr = $content[0].getBoundingClientRect();
            }
            self.__tracker = setInterval(function() {
                if (!bodyContains(self._$origin) || !bodyContains(self._$tooltip)) {
                    self._close();
                } else {
                    if (self.__options.trackOrigin) {
                        var g = self.__geometry(), identical = false;
                        if (areEqual(g.origin.size, self.__Geometry.origin.size)) {
                            if (self.__Geometry.origin.fixedLineage) {
                                if (areEqual(g.origin.windowOffset, self.__Geometry.origin.windowOffset)) {
                                    identical = true;
                                }
                            } else {
                                if (areEqual(g.origin.offset, self.__Geometry.origin.offset)) {
                                    identical = true;
                                }
                            }
                        }
                        if (!identical) {
                            if (self.__options.triggerClose.mouseleave) {
                                self._close();
                            } else {
                                self.reposition();
                            }
                        }
                    }
                    if (self.__options.trackTooltip) {
                        var currentBcr = $content[0].getBoundingClientRect();
                        if (currentBcr.height !== self.__contentBcr.height || currentBcr.width !== self.__contentBcr.width) {
                            self.reposition();
                            self.__contentBcr = currentBcr;
                        }
                    }
                }
            }, self.__options.trackerInterval);
            return self;
        },
        _close: function(event, callback, force) {
            var self = this, ok = true;
            self._trigger({
                type: "close",
                event: event,
                stop: function() {
                    ok = false;
                }
            });
            if (ok || force) {
                if (callback) self.__callbacks.close.push(callback);
                self.__callbacks.open = [];
                self.__timeoutsClear();
                var finishCallbacks = function() {
                    $.each(self.__callbacks.close, function(i, c) {
                        c.call(self, self, {
                            event: event,
                            origin: self._$origin[0]
                        });
                    });
                    self.__callbacks.close = [];
                };
                if (self.__state != "closed") {
                    var necessary = true, d = new Date(), now = d.getTime(), newClosingTime = now + self.__options.animationDuration[1];
                    if (self.__state == "disappearing") {
                        if (newClosingTime > self.__closingTime && self.__options.animationDuration[1] > 0) {
                            necessary = false;
                        }
                    }
                    if (necessary) {
                        self.__closingTime = newClosingTime;
                        if (self.__state != "disappearing") {
                            self.__stateSet("disappearing");
                        }
                        var finish = function() {
                            clearInterval(self.__tracker);
                            self._trigger({
                                type: "closing",
                                event: event
                            });
                            self._$tooltip.off("." + self.__namespace + "-triggerClose").removeClass("tooltipster-dying");
                            $(env.window).off("." + self.__namespace + "-triggerClose");
                            self.__$originParents.each(function(i, el) {
                                $(el).off("scroll." + self.__namespace + "-triggerClose");
                            });
                            self.__$originParents = null;
                            $(env.window.document.body).off("." + self.__namespace + "-triggerClose");
                            self._$origin.off("." + self.__namespace + "-triggerClose");
                            self._off("dismissable");
                            self.__stateSet("closed");
                            self._trigger({
                                type: "after",
                                event: event
                            });
                            if (self.__options.functionAfter) {
                                self.__options.functionAfter.call(self, self, {
                                    event: event,
                                    origin: self._$origin[0]
                                });
                            }
                            finishCallbacks();
                        };
                        if (env.hasTransitions) {
                            self._$tooltip.css({
                                "-moz-animation-duration": self.__options.animationDuration[1] + "ms",
                                "-ms-animation-duration": self.__options.animationDuration[1] + "ms",
                                "-o-animation-duration": self.__options.animationDuration[1] + "ms",
                                "-webkit-animation-duration": self.__options.animationDuration[1] + "ms",
                                "animation-duration": self.__options.animationDuration[1] + "ms",
                                "transition-duration": self.__options.animationDuration[1] + "ms"
                            });
                            self._$tooltip.clearQueue().removeClass("tooltipster-show").addClass("tooltipster-dying");
                            if (self.__options.animationDuration[1] > 0) {
                                self._$tooltip.delay(self.__options.animationDuration[1]);
                            }
                            self._$tooltip.queue(finish);
                        } else {
                            self._$tooltip.stop().fadeOut(self.__options.animationDuration[1], finish);
                        }
                    }
                } else {
                    finishCallbacks();
                }
            }
            return self;
        },
        _off: function() {
            this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _on: function() {
            this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _one: function() {
            this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _open: function(event, callback) {
            var self = this;
            if (!self.__destroying) {
                if (bodyContains(self._$origin) && self.__enabled) {
                    var ok = true;
                    if (self.__state == "closed") {
                        self._trigger({
                            type: "before",
                            event: event,
                            stop: function() {
                                ok = false;
                            }
                        });
                        if (ok && self.__options.functionBefore) {
                            ok = self.__options.functionBefore.call(self, self, {
                                event: event,
                                origin: self._$origin[0]
                            });
                        }
                    }
                    if (ok !== false) {
                        if (self.__Content !== null) {
                            if (callback) {
                                self.__callbacks.open.push(callback);
                            }
                            self.__callbacks.close = [];
                            self.__timeoutsClear();
                            var extraTime, finish = function() {
                                if (self.__state != "stable") {
                                    self.__stateSet("stable");
                                }
                                $.each(self.__callbacks.open, function(i, c) {
                                    c.call(self, self, {
                                        origin: self._$origin[0],
                                        tooltip: self._$tooltip[0]
                                    });
                                });
                                self.__callbacks.open = [];
                            };
                            if (self.__state !== "closed") {
                                extraTime = 0;
                                if (self.__state === "disappearing") {
                                    self.__stateSet("appearing");
                                    if (env.hasTransitions) {
                                        self._$tooltip.clearQueue().removeClass("tooltipster-dying").addClass("tooltipster-show");
                                        if (self.__options.animationDuration[0] > 0) {
                                            self._$tooltip.delay(self.__options.animationDuration[0]);
                                        }
                                        self._$tooltip.queue(finish);
                                    } else {
                                        self._$tooltip.stop().fadeIn(finish);
                                    }
                                } else if (self.__state == "stable") {
                                    finish();
                                }
                            } else {
                                self.__stateSet("appearing");
                                extraTime = self.__options.animationDuration[0];
                                self.__contentInsert();
                                self.reposition(event, true);
                                if (env.hasTransitions) {
                                    self._$tooltip.addClass("tooltipster-" + self.__options.animation).addClass("tooltipster-initial").css({
                                        "-moz-animation-duration": self.__options.animationDuration[0] + "ms",
                                        "-ms-animation-duration": self.__options.animationDuration[0] + "ms",
                                        "-o-animation-duration": self.__options.animationDuration[0] + "ms",
                                        "-webkit-animation-duration": self.__options.animationDuration[0] + "ms",
                                        "animation-duration": self.__options.animationDuration[0] + "ms",
                                        "transition-duration": self.__options.animationDuration[0] + "ms"
                                    });
                                    setTimeout(function() {
                                        if (self.__state != "closed") {
                                            self._$tooltip.addClass("tooltipster-show").removeClass("tooltipster-initial");
                                            if (self.__options.animationDuration[0] > 0) {
                                                self._$tooltip.delay(self.__options.animationDuration[0]);
                                            }
                                            self._$tooltip.queue(finish);
                                        }
                                    }, 0);
                                } else {
                                    self._$tooltip.css("display", "none").fadeIn(self.__options.animationDuration[0], finish);
                                }
                                self.__trackerStart();
                                $(env.window).on("resize." + self.__namespace + "-triggerClose", function(e) {
                                    var $ae = $(document.activeElement);
                                    if (!$ae.is("input") && !$ae.is("textarea") || !$.contains(self._$tooltip[0], $ae[0])) {
                                        self.reposition(e);
                                    }
                                }).on("scroll." + self.__namespace + "-triggerClose", function(e) {
                                    self.__scrollHandler(e);
                                });
                                self.__$originParents = self._$origin.parents();
                                self.__$originParents.each(function(i, parent) {
                                    $(parent).on("scroll." + self.__namespace + "-triggerClose", function(e) {
                                        self.__scrollHandler(e);
                                    });
                                });
                                if (self.__options.triggerClose.mouseleave || self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                                    self._on("dismissable", function(event) {
                                        if (event.dismissable) {
                                            if (event.delay) {
                                                timeout = setTimeout(function() {
                                                    self._close(event.event);
                                                }, event.delay);
                                                self.__timeouts.close.push(timeout);
                                            } else {
                                                self._close(event);
                                            }
                                        } else {
                                            clearTimeout(timeout);
                                        }
                                    });
                                    var $elements = self._$origin, eventNamesIn = "", eventNamesOut = "", timeout = null;
                                    if (self.__options.interactive) {
                                        $elements = $elements.add(self._$tooltip);
                                    }
                                    if (self.__options.triggerClose.mouseleave) {
                                        eventNamesIn += "mouseenter." + self.__namespace + "-triggerClose ";
                                        eventNamesOut += "mouseleave." + self.__namespace + "-triggerClose ";
                                    }
                                    if (self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                                        eventNamesIn += "touchstart." + self.__namespace + "-triggerClose";
                                        eventNamesOut += "touchend." + self.__namespace + "-triggerClose touchcancel." + self.__namespace + "-triggerClose";
                                    }
                                    $elements.on(eventNamesOut, function(event) {
                                        if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                                            var delay = event.type == "mouseleave" ? self.__options.delay : self.__options.delayTouch;
                                            self._trigger({
                                                delay: delay[1],
                                                dismissable: true,
                                                event: event,
                                                type: "dismissable"
                                            });
                                        }
                                    }).on(eventNamesIn, function(event) {
                                        if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                                            self._trigger({
                                                dismissable: false,
                                                event: event,
                                                type: "dismissable"
                                            });
                                        }
                                    });
                                }
                                if (self.__options.triggerClose.originClick) {
                                    self._$origin.on("click." + self.__namespace + "-triggerClose", function(event) {
                                        if (!self._touchIsTouchEvent(event) && !self._touchIsEmulatedEvent(event)) {
                                            self._close(event);
                                        }
                                    });
                                }
                                if (self.__options.triggerClose.click || self.__options.triggerClose.tap && env.hasTouchCapability) {
                                    setTimeout(function() {
                                        if (self.__state != "closed") {
                                            var eventNames = "", $body = $(env.window.document.body);
                                            if (self.__options.triggerClose.click) {
                                                eventNames += "click." + self.__namespace + "-triggerClose ";
                                            }
                                            if (self.__options.triggerClose.tap && env.hasTouchCapability) {
                                                eventNames += "touchend." + self.__namespace + "-triggerClose";
                                            }
                                            $body.on(eventNames, function(event) {
                                                if (self._touchIsMeaningfulEvent(event)) {
                                                    self._touchRecordEvent(event);
                                                    if (!self.__options.interactive || !$.contains(self._$tooltip[0], event.target)) {
                                                        self._close(event);
                                                    }
                                                }
                                            });
                                            if (self.__options.triggerClose.tap && env.hasTouchCapability) {
                                                $body.on("touchstart." + self.__namespace + "-triggerClose", function(event) {
                                                    self._touchRecordEvent(event);
                                                });
                                            }
                                        }
                                    }, 0);
                                }
                                self._trigger("ready");
                                if (self.__options.functionReady) {
                                    self.__options.functionReady.call(self, self, {
                                        origin: self._$origin[0],
                                        tooltip: self._$tooltip[0]
                                    });
                                }
                            }
                            if (self.__options.timer > 0) {
                                var timeout = setTimeout(function() {
                                    self._close();
                                }, self.__options.timer + extraTime);
                                self.__timeouts.close.push(timeout);
                            }
                        }
                    }
                }
            }
            return self;
        },
        _openShortly: function(event) {
            var self = this, ok = true;
            if (self.__state != "stable" && self.__state != "appearing") {
                if (!self.__timeouts.open) {
                    self._trigger({
                        type: "start",
                        event: event,
                        stop: function() {
                            ok = false;
                        }
                    });
                    if (ok) {
                        var delay = event.type.indexOf("touch") == 0 ? self.__options.delayTouch : self.__options.delay;
                        if (delay[0]) {
                            self.__timeouts.open = setTimeout(function() {
                                self.__timeouts.open = null;
                                if (self.__pointerIsOverOrigin && self._touchIsMeaningfulEvent(event)) {
                                    self._trigger("startend");
                                    self._open(event);
                                } else {
                                    self._trigger("startcancel");
                                }
                            }, delay[0]);
                        } else {
                            self._trigger("startend");
                            self._open(event);
                        }
                    }
                }
            }
            return self;
        },
        _optionsExtract: function(pluginName, defaultOptions) {
            var self = this, options = $.extend(true, {}, defaultOptions);
            var pluginOptions = self.__options[pluginName];
            if (!pluginOptions) {
                pluginOptions = {};
                $.each(defaultOptions, function(optionName, value) {
                    var o = self.__options[optionName];
                    if (o !== undefined) {
                        pluginOptions[optionName] = o;
                    }
                });
            }
            $.each(options, function(optionName, value) {
                if (pluginOptions[optionName] !== undefined) {
                    if (typeof value == "object" && !(value instanceof Array) && value != null && (typeof pluginOptions[optionName] == "object" && !(pluginOptions[optionName] instanceof Array) && pluginOptions[optionName] != null)) {
                        $.extend(options[optionName], pluginOptions[optionName]);
                    } else {
                        options[optionName] = pluginOptions[optionName];
                    }
                }
            });
            return options;
        },
        _plug: function(pluginName) {
            var plugin = $.tooltipster._plugin(pluginName);
            if (plugin) {
                if (plugin.instance) {
                    $.tooltipster.__bridge(plugin.instance, this, plugin.name);
                }
            } else {
                throw new Error('The "' + pluginName + '" plugin is not defined');
            }
            return this;
        },
        _touchIsEmulatedEvent: function(event) {
            var isEmulated = false, now = new Date().getTime();
            for (var i = this.__touchEvents.length - 1; i >= 0; i--) {
                var e = this.__touchEvents[i];
                if (now - e.time < 500) {
                    if (e.target === event.target) {
                        isEmulated = true;
                    }
                } else {
                    break;
                }
            }
            return isEmulated;
        },
        _touchIsMeaningfulEvent: function(event) {
            return this._touchIsTouchEvent(event) && !this._touchSwiped(event.target) || !this._touchIsTouchEvent(event) && !this._touchIsEmulatedEvent(event);
        },
        _touchIsTouchEvent: function(event) {
            return event.type.indexOf("touch") == 0;
        },
        _touchRecordEvent: function(event) {
            if (this._touchIsTouchEvent(event)) {
                event.time = new Date().getTime();
                this.__touchEvents.push(event);
            }
            return this;
        },
        _touchSwiped: function(target) {
            var swiped = false;
            for (var i = this.__touchEvents.length - 1; i >= 0; i--) {
                var e = this.__touchEvents[i];
                if (e.type == "touchmove") {
                    swiped = true;
                    break;
                } else if (e.type == "touchstart" && target === e.target) {
                    break;
                }
            }
            return swiped;
        },
        _trigger: function() {
            var args = Array.prototype.slice.apply(arguments);
            if (typeof args[0] == "string") {
                args[0] = {
                    type: args[0]
                };
            }
            args[0].instance = this;
            args[0].origin = this._$origin ? this._$origin[0] : null;
            args[0].tooltip = this._$tooltip ? this._$tooltip[0] : null;
            this.__$emitterPrivate.trigger.apply(this.__$emitterPrivate, args);
            $.tooltipster._trigger.apply($.tooltipster, args);
            this.__$emitterPublic.trigger.apply(this.__$emitterPublic, args);
            return this;
        },
        _unplug: function(pluginName) {
            var self = this;
            if (self[pluginName]) {
                var plugin = $.tooltipster._plugin(pluginName);
                if (plugin.instance) {
                    $.each(plugin.instance, function(methodName, fn) {
                        if (self[methodName] && self[methodName].bridged === self[pluginName]) {
                            delete self[methodName];
                        }
                    });
                }
                if (self[pluginName].__destroy) {
                    self[pluginName].__destroy();
                }
                delete self[pluginName];
            }
            return self;
        },
        close: function(callback) {
            if (!this.__destroyed) {
                this._close(null, callback);
            } else {
                this.__destroyError();
            }
            return this;
        },
        content: function(content) {
            var self = this;
            if (content === undefined) {
                return self.__Content;
            } else {
                if (!self.__destroyed) {
                    self.__contentSet(content);
                    if (self.__Content !== null) {
                        if (self.__state !== "closed") {
                            self.__contentInsert();
                            self.reposition();
                            if (self.__options.updateAnimation) {
                                if (env.hasTransitions) {
                                    var animation = self.__options.updateAnimation;
                                    self._$tooltip.addClass("tooltipster-update-" + animation);
                                    setTimeout(function() {
                                        if (self.__state != "closed") {
                                            self._$tooltip.removeClass("tooltipster-update-" + animation);
                                        }
                                    }, 1e3);
                                } else {
                                    self._$tooltip.fadeTo(200, .5, function() {
                                        if (self.__state != "closed") {
                                            self._$tooltip.fadeTo(200, 1);
                                        }
                                    });
                                }
                            }
                        }
                    } else {
                        self._close();
                    }
                } else {
                    self.__destroyError();
                }
                return self;
            }
        },
        destroy: function() {
            var self = this;
            if (!self.__destroyed) {
                if (self.__state != "closed") {
                    self.option("animationDuration", 0)._close(null, null, true);
                } else {
                    self.__timeoutsClear();
                }
                self._trigger("destroy");
                self.__destroyed = true;
                self._$origin.removeData(self.__namespace).off("." + self.__namespace + "-triggerOpen");
                $(env.window.document.body).off("." + self.__namespace + "-triggerOpen");
                var ns = self._$origin.data("tooltipster-ns");
                if (ns) {
                    if (ns.length === 1) {
                        var title = null;
                        if (self.__options.restoration == "previous") {
                            title = self._$origin.data("tooltipster-initialTitle");
                        } else if (self.__options.restoration == "current") {
                            title = typeof self.__Content == "string" ? self.__Content : $("<div></div>").append(self.__Content).html();
                        }
                        if (title) {
                            self._$origin.attr("title", title);
                        }
                        self._$origin.removeClass("tooltipstered");
                        self._$origin.removeData("tooltipster-ns").removeData("tooltipster-initialTitle");
                    } else {
                        ns = $.grep(ns, function(el, i) {
                            return el !== self.__namespace;
                        });
                        self._$origin.data("tooltipster-ns", ns);
                    }
                }
                self._trigger("destroyed");
                self._off();
                self.off();
                self.__Content = null;
                self.__$emitterPrivate = null;
                self.__$emitterPublic = null;
                self.__options.parent = null;
                self._$origin = null;
                self._$tooltip = null;
                $.tooltipster.__instancesLatestArr = $.grep($.tooltipster.__instancesLatestArr, function(el, i) {
                    return self !== el;
                });
                clearInterval(self.__garbageCollector);
            } else {
                self.__destroyError();
            }
            return self;
        },
        disable: function() {
            if (!this.__destroyed) {
                this._close();
                this.__enabled = false;
                return this;
            } else {
                this.__destroyError();
            }
            return this;
        },
        elementOrigin: function() {
            if (!this.__destroyed) {
                return this._$origin[0];
            } else {
                this.__destroyError();
            }
        },
        elementTooltip: function() {
            return this._$tooltip ? this._$tooltip[0] : null;
        },
        enable: function() {
            this.__enabled = true;
            return this;
        },
        hide: function(callback) {
            return this.close(callback);
        },
        instance: function() {
            return this;
        },
        off: function() {
            if (!this.__destroyed) {
                this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            }
            return this;
        },
        on: function() {
            if (!this.__destroyed) {
                this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        },
        one: function() {
            if (!this.__destroyed) {
                this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        },
        open: function(callback) {
            if (!this.__destroyed) {
                this._open(null, callback);
            } else {
                this.__destroyError();
            }
            return this;
        },
        option: function(o, val) {
            if (val === undefined) {
                return this.__options[o];
            } else {
                if (!this.__destroyed) {
                    this.__options[o] = val;
                    this.__optionsFormat();
                    if ($.inArray(o, [ "trigger", "triggerClose", "triggerOpen" ]) >= 0) {
                        this.__prepareOrigin();
                    }
                    if (o === "selfDestruction") {
                        this.__prepareGC();
                    }
                } else {
                    this.__destroyError();
                }
                return this;
            }
        },
        reposition: function(event, tooltipIsDetached) {
            var self = this;
            if (!self.__destroyed) {
                if (self.__state != "closed" && bodyContains(self._$origin)) {
                    if (tooltipIsDetached || bodyContains(self._$tooltip)) {
                        if (!tooltipIsDetached) {
                            self._$tooltip.detach();
                        }
                        self.__Geometry = self.__geometry();
                        self._trigger({
                            type: "reposition",
                            event: event,
                            helper: {
                                geo: self.__Geometry
                            }
                        });
                    }
                }
            } else {
                self.__destroyError();
            }
            return self;
        },
        show: function(callback) {
            return this.open(callback);
        },
        status: function() {
            return {
                destroyed: this.__destroyed,
                enabled: this.__enabled,
                open: this.__state !== "closed",
                state: this.__state
            };
        },
        triggerHandler: function() {
            if (!this.__destroyed) {
                this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        }
    };
    $.fn.tooltipster = function() {
        var args = Array.prototype.slice.apply(arguments), contentCloningWarning = "You are using a single HTML element as content for several tooltips. You probably want to set the contentCloning option to TRUE.";
        if (this.length === 0) {
            return this;
        } else {
            if (typeof args[0] === "string") {
                var v = "#*$~&";
                this.each(function() {
                    var ns = $(this).data("tooltipster-ns"), self = ns ? $(this).data(ns[0]) : null;
                    if (self) {
                        if (typeof self[args[0]] === "function") {
                            if (this.length > 1 && args[0] == "content" && (args[1] instanceof $ || typeof args[1] == "object" && args[1] != null && args[1].tagName) && !self.__options.contentCloning && self.__options.debug) {
                                console.log(contentCloningWarning);
                            }
                            var resp = self[args[0]](args[1], args[2]);
                        } else {
                            throw new Error('Unknown method "' + args[0] + '"');
                        }
                        if (resp !== self || args[0] === "instance") {
                            v = resp;
                            return false;
                        }
                    } else {
                        throw new Error("You called Tooltipster's \"" + args[0] + '" method on an uninitialized element');
                    }
                });
                return v !== "#*$~&" ? v : this;
            } else {
                $.tooltipster.__instancesLatestArr = [];
                var multipleIsSet = args[0] && args[0].multiple !== undefined, multiple = multipleIsSet && args[0].multiple || !multipleIsSet && defaults.multiple, contentIsSet = args[0] && args[0].content !== undefined, content = contentIsSet && args[0].content || !contentIsSet && defaults.content, contentCloningIsSet = args[0] && args[0].contentCloning !== undefined, contentCloning = contentCloningIsSet && args[0].contentCloning || !contentCloningIsSet && defaults.contentCloning, debugIsSet = args[0] && args[0].debug !== undefined, debug = debugIsSet && args[0].debug || !debugIsSet && defaults.debug;
                if (this.length > 1 && (content instanceof $ || typeof content == "object" && content != null && content.tagName) && !contentCloning && debug) {
                    console.log(contentCloningWarning);
                }
                this.each(function() {
                    var go = false, $this = $(this), ns = $this.data("tooltipster-ns"), obj = null;
                    if (!ns) {
                        go = true;
                    } else if (multiple) {
                        go = true;
                    } else if (debug) {
                        console.log("Tooltipster: one or more tooltips are already attached to the element below. Ignoring.");
                        console.log(this);
                    }
                    if (go) {
                        obj = new $.Tooltipster(this, args[0]);
                        if (!ns) ns = [];
                        ns.push(obj.__namespace);
                        $this.data("tooltipster-ns", ns);
                        $this.data(obj.__namespace, obj);
                        if (obj.__options.functionInit) {
                            obj.__options.functionInit.call(obj, obj, {
                                origin: this
                            });
                        }
                        obj._trigger("init");
                    }
                    $.tooltipster.__instancesLatestArr.push(obj);
                });
                return this;
            }
        }
    };
    function Ruler($tooltip) {
        this.$container;
        this.constraints = null;
        this.__$tooltip;
        this.__init($tooltip);
    }
    Ruler.prototype = {
        __init: function($tooltip) {
            this.__$tooltip = $tooltip;
            this.__$tooltip.css({
                left: 0,
                overflow: "hidden",
                position: "absolute",
                top: 0
            }).find(".tooltipster-content").css("overflow", "auto");
            this.$container = $('<div class="tooltipster-ruler"></div>').append(this.__$tooltip).appendTo(env.window.document.body);
        },
        __forceRedraw: function() {
            var $p = this.__$tooltip.parent();
            this.__$tooltip.detach();
            this.__$tooltip.appendTo($p);
        },
        constrain: function(width, height) {
            this.constraints = {
                width: width,
                height: height
            };
            this.__$tooltip.css({
                display: "block",
                height: "",
                overflow: "auto",
                width: width
            });
            return this;
        },
        destroy: function() {
            this.__$tooltip.detach().find(".tooltipster-content").css({
                display: "",
                overflow: ""
            });
            this.$container.remove();
        },
        free: function() {
            this.constraints = null;
            this.__$tooltip.css({
                display: "",
                height: "",
                overflow: "visible",
                width: ""
            });
            return this;
        },
        measure: function() {
            this.__forceRedraw();
            var tooltipBcr = this.__$tooltip[0].getBoundingClientRect(), result = {
                size: {
                    height: tooltipBcr.height || tooltipBcr.bottom - tooltipBcr.top,
                    width: tooltipBcr.width || tooltipBcr.right - tooltipBcr.left
                }
            };
            if (this.constraints) {
                var $content = this.__$tooltip.find(".tooltipster-content"), height = this.__$tooltip.outerHeight(), contentBcr = $content[0].getBoundingClientRect(), fits = {
                    height: height <= this.constraints.height,
                    width: tooltipBcr.width <= this.constraints.width && contentBcr.width >= $content[0].scrollWidth - 1
                };
                result.fits = fits.height && fits.width;
            }
            if (env.IE && env.IE <= 11 && result.size.width !== env.window.document.documentElement.clientWidth) {
                result.size.width = Math.ceil(result.size.width) + 1;
            }
            return result;
        }
    };
    function areEqual(a, b) {
        var same = true;
        $.each(a, function(i, _) {
            if (b[i] === undefined || a[i] !== b[i]) {
                same = false;
                return false;
            }
        });
        return same;
    }
    function bodyContains($obj) {
        var id = $obj.attr("id"), el = id ? env.window.document.getElementById(id) : null;
        return el ? el === $obj[0] : $.contains(env.window.document.body, $obj[0]);
    }
    var uA = navigator.userAgent.toLowerCase();
    if (uA.indexOf("msie") != -1) env.IE = parseInt(uA.split("msie")[1]); else if (uA.toLowerCase().indexOf("trident") !== -1 && uA.indexOf(" rv:11") !== -1) env.IE = 11; else if (uA.toLowerCase().indexOf("edge/") != -1) env.IE = parseInt(uA.toLowerCase().split("edge/")[1]);
    function transitionSupport() {
        if (!win) return false;
        var b = win.document.body || win.document.documentElement, s = b.style, p = "transition", v = [ "Moz", "Webkit", "Khtml", "O", "ms" ];
        if (typeof s[p] == "string") {
            return true;
        }
        p = p.charAt(0).toUpperCase() + p.substr(1);
        for (var i = 0; i < v.length; i++) {
            if (typeof s[v[i] + p] == "string") {
                return true;
            }
        }
        return false;
    }
    var pluginName = "tooltipster.sideTip";
    $.tooltipster._plugin({
        name: pluginName,
        instance: {
            __defaults: function() {
                return {
                    arrow: true,
                    distance: 6,
                    functionPosition: null,
                    maxWidth: null,
                    minIntersection: 16,
                    minWidth: 0,
                    position: null,
                    side: "top",
                    viewportAware: true
                };
            },
            __init: function(instance) {
                var self = this;
                self.__instance = instance;
                self.__namespace = "tooltipster-sideTip-" + Math.round(Math.random() * 1e6);
                self.__previousState = "closed";
                self.__options;
                self.__optionsFormat();
                self.__instance._on("state." + self.__namespace, function(event) {
                    if (event.state == "closed") {
                        self.__close();
                    } else if (event.state == "appearing" && self.__previousState == "closed") {
                        self.__create();
                    }
                    self.__previousState = event.state;
                });
                self.__instance._on("options." + self.__namespace, function() {
                    self.__optionsFormat();
                });
                self.__instance._on("reposition." + self.__namespace, function(e) {
                    self.__reposition(e.event, e.helper);
                });
            },
            __close: function() {
                if (this.__instance.content() instanceof $) {
                    this.__instance.content().detach();
                }
                this.__instance._$tooltip.remove();
                this.__instance._$tooltip = null;
            },
            __create: function() {
                var $html = $('<div class="tooltipster-base tooltipster-sidetip">' + '<div class="tooltipster-box">' + '<div class="tooltipster-content"></div>' + "</div>" + '<div class="tooltipster-arrow">' + '<div class="tooltipster-arrow-uncropped">' + '<div class="tooltipster-arrow-border"></div>' + '<div class="tooltipster-arrow-background"></div>' + "</div>" + "</div>" + "</div>");
                if (!this.__options.arrow) {
                    $html.find(".tooltipster-box").css("margin", 0).end().find(".tooltipster-arrow").hide();
                }
                if (this.__options.minWidth) {
                    $html.css("min-width", this.__options.minWidth + "px");
                }
                if (this.__options.maxWidth) {
                    $html.css("max-width", this.__options.maxWidth + "px");
                }
                this.__instance._$tooltip = $html;
                this.__instance._trigger("created");
            },
            __destroy: function() {
                this.__instance._off("." + self.__namespace);
            },
            __optionsFormat: function() {
                var self = this;
                self.__options = self.__instance._optionsExtract(pluginName, self.__defaults());
                if (self.__options.position) {
                    self.__options.side = self.__options.position;
                }
                if (typeof self.__options.distance != "object") {
                    self.__options.distance = [ self.__options.distance ];
                }
                if (self.__options.distance.length < 4) {
                    if (self.__options.distance[1] === undefined) self.__options.distance[1] = self.__options.distance[0];
                    if (self.__options.distance[2] === undefined) self.__options.distance[2] = self.__options.distance[0];
                    if (self.__options.distance[3] === undefined) self.__options.distance[3] = self.__options.distance[1];
                    self.__options.distance = {
                        top: self.__options.distance[0],
                        right: self.__options.distance[1],
                        bottom: self.__options.distance[2],
                        left: self.__options.distance[3]
                    };
                }
                if (typeof self.__options.side == "string") {
                    var opposites = {
                        top: "bottom",
                        right: "left",
                        bottom: "top",
                        left: "right"
                    };
                    self.__options.side = [ self.__options.side, opposites[self.__options.side] ];
                    if (self.__options.side[0] == "left" || self.__options.side[0] == "right") {
                        self.__options.side.push("top", "bottom");
                    } else {
                        self.__options.side.push("right", "left");
                    }
                }
                if ($.tooltipster._env.IE === 6 && self.__options.arrow !== true) {
                    self.__options.arrow = false;
                }
            },
            __reposition: function(event, helper) {
                var self = this, finalResult, targets = self.__targetFind(helper), testResults = [];
                self.__instance._$tooltip.detach();
                var $clone = self.__instance._$tooltip.clone(), ruler = $.tooltipster._getRuler($clone), satisfied = false, animation = self.__instance.option("animation");
                if (animation) {
                    $clone.removeClass("tooltipster-" + animation);
                }
                $.each([ "window", "document" ], function(i, container) {
                    var takeTest = null;
                    self.__instance._trigger({
                        container: container,
                        helper: helper,
                        satisfied: satisfied,
                        takeTest: function(bool) {
                            takeTest = bool;
                        },
                        results: testResults,
                        type: "positionTest"
                    });
                    if (takeTest == true || takeTest != false && satisfied == false && (container != "window" || self.__options.viewportAware)) {
                        for (var i = 0; i < self.__options.side.length; i++) {
                            var distance = {
                                horizontal: 0,
                                vertical: 0
                            }, side = self.__options.side[i];
                            if (side == "top" || side == "bottom") {
                                distance.vertical = self.__options.distance[side];
                            } else {
                                distance.horizontal = self.__options.distance[side];
                            }
                            self.__sideChange($clone, side);
                            $.each([ "natural", "constrained" ], function(i, mode) {
                                takeTest = null;
                                self.__instance._trigger({
                                    container: container,
                                    event: event,
                                    helper: helper,
                                    mode: mode,
                                    results: testResults,
                                    satisfied: satisfied,
                                    side: side,
                                    takeTest: function(bool) {
                                        takeTest = bool;
                                    },
                                    type: "positionTest"
                                });
                                if (takeTest == true || takeTest != false && satisfied == false) {
                                    var testResult = {
                                        container: container,
                                        distance: distance,
                                        fits: null,
                                        mode: mode,
                                        outerSize: null,
                                        side: side,
                                        size: null,
                                        target: targets[side],
                                        whole: null
                                    };
                                    var rulerConfigured = mode == "natural" ? ruler.free() : ruler.constrain(helper.geo.available[container][side].width - distance.horizontal, helper.geo.available[container][side].height - distance.vertical), rulerResults = rulerConfigured.measure();
                                    testResult.size = rulerResults.size;
                                    testResult.outerSize = {
                                        height: rulerResults.size.height + distance.vertical,
                                        width: rulerResults.size.width + distance.horizontal
                                    };
                                    if (mode == "natural") {
                                        if (helper.geo.available[container][side].width >= testResult.outerSize.width && helper.geo.available[container][side].height >= testResult.outerSize.height) {
                                            testResult.fits = true;
                                        } else {
                                            testResult.fits = false;
                                        }
                                    } else {
                                        testResult.fits = rulerResults.fits;
                                    }
                                    if (container == "window") {
                                        if (!testResult.fits) {
                                            testResult.whole = false;
                                        } else {
                                            if (side == "top" || side == "bottom") {
                                                testResult.whole = helper.geo.origin.windowOffset.right >= self.__options.minIntersection && helper.geo.window.size.width - helper.geo.origin.windowOffset.left >= self.__options.minIntersection;
                                            } else {
                                                testResult.whole = helper.geo.origin.windowOffset.bottom >= self.__options.minIntersection && helper.geo.window.size.height - helper.geo.origin.windowOffset.top >= self.__options.minIntersection;
                                            }
                                        }
                                    }
                                    testResults.push(testResult);
                                    if (testResult.whole) {
                                        satisfied = true;
                                    } else {
                                        if (testResult.mode == "natural" && (testResult.fits || testResult.size.width <= helper.geo.available[container][side].width)) {
                                            return false;
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
                self.__instance._trigger({
                    edit: function(r) {
                        testResults = r;
                    },
                    event: event,
                    helper: helper,
                    results: testResults,
                    type: "positionTested"
                });
                testResults.sort(function(a, b) {
                    if (a.whole && !b.whole) {
                        return -1;
                    } else if (!a.whole && b.whole) {
                        return 1;
                    } else if (a.whole && b.whole) {
                        var ai = self.__options.side.indexOf(a.side), bi = self.__options.side.indexOf(b.side);
                        if (ai < bi) {
                            return -1;
                        } else if (ai > bi) {
                            return 1;
                        } else {
                            return a.mode == "natural" ? -1 : 1;
                        }
                    } else {
                        if (a.fits && !b.fits) {
                            return -1;
                        } else if (!a.fits && b.fits) {
                            return 1;
                        } else if (a.fits && b.fits) {
                            var ai = self.__options.side.indexOf(a.side), bi = self.__options.side.indexOf(b.side);
                            if (ai < bi) {
                                return -1;
                            } else if (ai > bi) {
                                return 1;
                            } else {
                                return a.mode == "natural" ? -1 : 1;
                            }
                        } else {
                            if (a.container == "document" && a.side == "bottom" && a.mode == "natural") {
                                return -1;
                            } else {
                                return 1;
                            }
                        }
                    }
                });
                finalResult = testResults[0];
                finalResult.coord = {};
                switch (finalResult.side) {
                  case "left":
                  case "right":
                    finalResult.coord.top = Math.floor(finalResult.target - finalResult.size.height / 2);
                    break;

                  case "bottom":
                  case "top":
                    finalResult.coord.left = Math.floor(finalResult.target - finalResult.size.width / 2);
                    break;
                }
                switch (finalResult.side) {
                  case "left":
                    finalResult.coord.left = helper.geo.origin.windowOffset.left - finalResult.outerSize.width;
                    break;

                  case "right":
                    finalResult.coord.left = helper.geo.origin.windowOffset.right + finalResult.distance.horizontal;
                    break;

                  case "top":
                    finalResult.coord.top = helper.geo.origin.windowOffset.top - finalResult.outerSize.height;
                    break;

                  case "bottom":
                    finalResult.coord.top = helper.geo.origin.windowOffset.bottom + finalResult.distance.vertical;
                    break;
                }
                if (finalResult.container == "window") {
                    if (finalResult.side == "top" || finalResult.side == "bottom") {
                        if (finalResult.coord.left < 0) {
                            if (helper.geo.origin.windowOffset.right - this.__options.minIntersection >= 0) {
                                finalResult.coord.left = 0;
                            } else {
                                finalResult.coord.left = helper.geo.origin.windowOffset.right - this.__options.minIntersection - 1;
                            }
                        } else if (finalResult.coord.left > helper.geo.window.size.width - finalResult.size.width) {
                            if (helper.geo.origin.windowOffset.left + this.__options.minIntersection <= helper.geo.window.size.width) {
                                finalResult.coord.left = helper.geo.window.size.width - finalResult.size.width;
                            } else {
                                finalResult.coord.left = helper.geo.origin.windowOffset.left + this.__options.minIntersection + 1 - finalResult.size.width;
                            }
                        }
                    } else {
                        if (finalResult.coord.top < 0) {
                            if (helper.geo.origin.windowOffset.bottom - this.__options.minIntersection >= 0) {
                                finalResult.coord.top = 0;
                            } else {
                                finalResult.coord.top = helper.geo.origin.windowOffset.bottom - this.__options.minIntersection - 1;
                            }
                        } else if (finalResult.coord.top > helper.geo.window.size.height - finalResult.size.height) {
                            if (helper.geo.origin.windowOffset.top + this.__options.minIntersection <= helper.geo.window.size.height) {
                                finalResult.coord.top = helper.geo.window.size.height - finalResult.size.height;
                            } else {
                                finalResult.coord.top = helper.geo.origin.windowOffset.top + this.__options.minIntersection + 1 - finalResult.size.height;
                            }
                        }
                    }
                } else {
                    if (finalResult.coord.left > helper.geo.window.size.width - finalResult.size.width) {
                        finalResult.coord.left = helper.geo.window.size.width - finalResult.size.width;
                    }
                    if (finalResult.coord.left < 0) {
                        finalResult.coord.left = 0;
                    }
                }
                self.__sideChange($clone, finalResult.side);
                helper.tooltipClone = $clone[0];
                helper.tooltipParent = self.__instance.option("parent").parent[0];
                helper.mode = finalResult.mode;
                helper.whole = finalResult.whole;
                helper.origin = self.__instance._$origin[0];
                helper.tooltip = self.__instance._$tooltip[0];
                delete finalResult.container;
                delete finalResult.fits;
                delete finalResult.mode;
                delete finalResult.outerSize;
                delete finalResult.whole;
                finalResult.distance = finalResult.distance.horizontal || finalResult.distance.vertical;
                var finalResultClone = $.extend(true, {}, finalResult);
                self.__instance._trigger({
                    edit: function(result) {
                        finalResult = result;
                    },
                    event: event,
                    helper: helper,
                    position: finalResultClone,
                    type: "position"
                });
                if (self.__options.functionPosition) {
                    var result = self.__options.functionPosition.call(self, self.__instance, helper, finalResultClone);
                    if (result) finalResult = result;
                }
                ruler.destroy();
                var arrowCoord, maxVal;
                if (finalResult.side == "top" || finalResult.side == "bottom") {
                    arrowCoord = {
                        prop: "left",
                        val: finalResult.target - finalResult.coord.left
                    };
                    maxVal = finalResult.size.width - this.__options.minIntersection;
                } else {
                    arrowCoord = {
                        prop: "top",
                        val: finalResult.target - finalResult.coord.top
                    };
                    maxVal = finalResult.size.height - this.__options.minIntersection;
                }
                if (arrowCoord.val < this.__options.minIntersection) {
                    arrowCoord.val = this.__options.minIntersection;
                } else if (arrowCoord.val > maxVal) {
                    arrowCoord.val = maxVal;
                }
                var originParentOffset;
                if (helper.geo.origin.fixedLineage) {
                    originParentOffset = helper.geo.origin.windowOffset;
                } else {
                    originParentOffset = {
                        left: helper.geo.origin.windowOffset.left + helper.geo.window.scroll.left,
                        top: helper.geo.origin.windowOffset.top + helper.geo.window.scroll.top
                    };
                }
                finalResult.coord = {
                    left: originParentOffset.left + (finalResult.coord.left - helper.geo.origin.windowOffset.left),
                    top: originParentOffset.top + (finalResult.coord.top - helper.geo.origin.windowOffset.top)
                };
                self.__sideChange(self.__instance._$tooltip, finalResult.side);
                if (helper.geo.origin.fixedLineage) {
                    self.__instance._$tooltip.css("position", "fixed");
                } else {
                    self.__instance._$tooltip.css("position", "");
                }
                self.__instance._$tooltip.css({
                    left: finalResult.coord.left,
                    top: finalResult.coord.top,
                    height: finalResult.size.height,
                    width: finalResult.size.width
                }).find(".tooltipster-arrow").css({
                    left: "",
                    top: ""
                }).css(arrowCoord.prop, arrowCoord.val);
                self.__instance._$tooltip.appendTo(self.__instance.option("parent"));
                self.__instance._trigger({
                    type: "repositioned",
                    event: event,
                    position: finalResult
                });
            },
            __sideChange: function($obj, side) {
                $obj.removeClass("tooltipster-bottom").removeClass("tooltipster-left").removeClass("tooltipster-right").removeClass("tooltipster-top").addClass("tooltipster-" + side);
            },
            __targetFind: function(helper) {
                var target = {}, rects = this.__instance._$origin[0].getClientRects();
                if (rects.length > 1) {
                    var opacity = this.__instance._$origin.css("opacity");
                    if (opacity == 1) {
                        this.__instance._$origin.css("opacity", .99);
                        rects = this.__instance._$origin[0].getClientRects();
                        this.__instance._$origin.css("opacity", 1);
                    }
                }
                if (rects.length < 2) {
                    target.top = Math.floor(helper.geo.origin.windowOffset.left + helper.geo.origin.size.width / 2);
                    target.bottom = target.top;
                    target.left = Math.floor(helper.geo.origin.windowOffset.top + helper.geo.origin.size.height / 2);
                    target.right = target.left;
                } else {
                    var targetRect = rects[0];
                    target.top = Math.floor(targetRect.left + (targetRect.right - targetRect.left) / 2);
                    if (rects.length > 2) {
                        targetRect = rects[Math.ceil(rects.length / 2) - 1];
                    } else {
                        targetRect = rects[0];
                    }
                    target.right = Math.floor(targetRect.top + (targetRect.bottom - targetRect.top) / 2);
                    targetRect = rects[rects.length - 1];
                    target.bottom = Math.floor(targetRect.left + (targetRect.right - targetRect.left) / 2);
                    if (rects.length > 2) {
                        targetRect = rects[Math.ceil((rects.length + 1) / 2) - 1];
                    } else {
                        targetRect = rects[rects.length - 1];
                    }
                    target.left = Math.floor(targetRect.top + (targetRect.bottom - targetRect.top) / 2);
                }
                return target;
            }
        }
    });
    return $;
});

var animate_duration = 1250;

var animation = "fade";

$(function() {
    $(".tooltipper").tooltipster({
        theme: "tooltipster-borderless",
        animation: animation,
        animationDuration: animate_duration,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 500
    });
    initAll();
});

function initAll() {
    $(".dt-tooltipper.tooltipstered").tooltipster("destroy");
    $(".dt-tooltipper-large").tooltipster({
        theme: "tooltipster-borderless",
        animation: animation,
        animationDuration: animate_duration,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 500
    });
    $(".dt-tooltipper-small").tooltipster({
        theme: "tooltipster-borderless",
        animation: animation,
        animationDuration: animate_duration,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 250
    });
    $(".tooltipper-ajax.tooltipstered").tooltipster("destroy");
    $(".tooltipper-ajax").tooltipster({
        trigger: "custom",
        triggerOpen: {
            mouseenter: true,
            touchstart: true
        },
        triggerClose: {
            mouseleave: true,
            originClick: true,
            click: true,
            scroll: true,
            tap: true,
            touchLeave: true
        },
        theme: [ "tooltipster-borderless", "tooltipster-custom" ],
        contentAsHTML: true,
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        updateAnimation: animation,
        arrow: true,
        minWidth: 250,
        content: "patience, grasshopper...",
        functionBefore: function(instance, helper) {
            var $origin = $(helper.origin);
            if ($origin.data("loaded") !== true) {
                $.post("../ajax/ajax_tooltips.php", {
                    csrf_token: csrf_token
                }, function(data) {
                    if (instance.content() === "") return false;
                    instance.content(data);
                    $origin.data("loaded", true);
                });
            }
        }
    });
}

function PopUp(url, name, width, height, center, resize, scroll, posleft, postop) {
    showx = "";
    showy = "";
    if (posleft != 0) {
        X = posleft;
    }
    if (postop != 0) {
        Y = postop;
    }
    if (!scroll) {
        scroll = 1;
    }
    if (!resize) {
        resize = 1;
    }
    if (parseInt(navigator.appVersion) >= 4 && center) {
        X = (screen.width - width) / 2;
        Y = (screen.height - height) / 2;
    }
    if (X > 0) {
        showx = ",left=" + X;
    }
    if (Y > 0) {
        showy = ",top=" + Y;
    }
    if (scroll != 0) {
        scroll = 1;
    }
    var Win = window.open(url, name, "width=" + width + ",height=" + height + showx + showy + ",resizable=" + resize + ",scrollbars=" + scroll + ",location=no,directories=no,status=no,menubar=no,toolbar=no");
    event.preventDefault();
}

var offset = 250;

var animate_duration = 1250;

var easing = "linear";

function themes() {
    PopUp("take_theme.php", "My themes", 300, 150, 1, 0);
}

function language_select() {
    PopUp("take_lang.php", "My language", 300, 150, 1, 0);
}

function radio() {
    PopUp("radio_popup.php", "My Radio", 800, 700, 1, 0);
}

$(".delete").on("click", function() {
    $(this).parent().slideUp(animate_duration, function() {
        $(this).remove();
    });
});

function refrClock() {
    var d = new Date();
    var s = d.getSeconds();
    var m = d.getMinutes();
    var h = d.getHours();
    var day = d.getDay();
    var date = d.getDate();
    var month = d.getMonth();
    var year = d.getFullYear();
    var am_pm;
    if (s < 10) {
        s = "0" + s;
    }
    if (m < 10) {
        m = "0" + m;
    }
    if (h > 12) {
        h -= 12;
        am_pm = "pm";
    } else {
        am_pm = "am";
    }
    document.getElementById("clock").innerHTML = h + ":" + m + ":" + s + " " + am_pm;
    setTimeout("refrClock()", 1e3);
}

function togglepic(bu, picid, formid) {
    var pic = document.getElementById(picid);
    var form = document.getElementById(formid);
    if (pic.src == bu + "/images/plus.gif") {
        pic.src = bu + "/images/minus.gif";
        form.value = "minus";
    } else {
        pic.src = bu + "/images/plus.gif";
        form.value = "plus";
    }
}

$(function() {
    if ($("#clock").length) {
        refrClock();
    }
    if ($(".password").length) {
        $(".password").pstrength();
    }
    if ($("#help_open").length) {
        $("#help_open").click(function() {
            $("#help").slideToggle(animate_duration, easing, function() {});
        });
    }
    if (typeof Storage !== "undefined") {
        $(".flipper").click(function(e) {
            $(this).next().slideToggle(animate_duration, easing, function() {
                var id = $(this).parent().attr("id");
                if (!$(this).is(":visible")) {
                    localStorage.setItem(id, "closed");
                    $(this).parent().addClass("no-margin no-padding");
                } else {
                    localStorage.setItem(id, "open");
                    $(this).parent().removeClass("no-margin no-padding");
                }
            });
            $(this).parent().find(".fa").toggleClass("fa-angle-up fa-angle-down");
        });
    }
    $(window).scroll(function() {
        if ($(this).scrollTop() > offset) {
            $(".back-to-top").fadeIn(animate_duration);
        } else {
            $(".back-to-top").fadeOut(animate_duration);
        }
    });
    $(".back-to-top").click(function(event) {
        event.preventDefault();
        $("html, body").animate({
            scrollTop: 0
        }, animate_duration, easing);
        $(".back-to-top").blur();
        return false;
    });
    if ($("#request_form").length) {
        $("#request_form").validate();
    }
    if ($("#offer_form").length) {
        $("#offer_form").validate();
    }
    if ($("#upload_form").length) {
        setupDependencies("upload_form");
    }
    if ($("#edit_form").length) {
        setupDependencies("edit_form");
    }
    if ($("#carousel-container").length) {
        $("#icarousel").iCarousel({
            easing: "ease-in-out",
            slides: 10,
            make3D: 1,
            perspective: 590,
            animationSpeed: animate_duration,
            pauseTime: 5e3,
            startSlide: 1,
            directionNav: 1,
            autoPlay: 1,
            keyboardNav: 1,
            touchNav: 1,
            mouseWheel: true,
            pauseOnHover: 1,
            nextLabel: "Next",
            previousLabel: "Previous",
            playLabel: "Play",
            pauseLabel: "Pause",
            randomStart: 1,
            slidesSpace: "200",
            slidesTopSpace: "auto",
            direction: "rtl",
            timer: "360bar",
            timerBg: "#000",
            timerColor: "#0f0",
            timerOpacity: .4,
            timerDiameter: 35,
            timerPadding: 4,
            timerStroke: 3,
            timerBarStroke: 1,
            timerBarStrokeColor: "#FFF",
            timerBarStrokeStyle: "solid",
            timerBarStrokeRadius: 4,
            timerPosition: "top-right",
            timerX: 10,
            timerY: 10
        });
    }
    if ($("#IE_ALERT").length) {
        if (navigator.userAgent.search("MSIE") >= 0) {
            $("#IE_ALERT").slideToggle(animate_duration, easing, function() {});
        }
    }
    $(window).resize(function() {
        var windowWidth = $(window).width();
        if (windowWidth > 768) {
            $("#menuWrapper").show();
        }
    });
    $("#hamburger").click(function(event) {
        event.preventDefault();
        $("#navbar").addClass("showNav");
        var winHeight = $(window).outerHeight();
        $("#menuWrapper").css("height", winHeight + "px");
        $("#menuWrapper").slideToggle(animate_duration, easing, function() {});
    });
    $("#close").click(function(event) {
        event.preventDefault();
        $("#menuWrapper").slideToggle(animate_duration, easing, function() {
            $("#navbar").removeClass("showNav");
            $("#menuWrapper").css("height", "auto");
        });
    });
    $("#menuWrapper ul li").hover(function() {
        var el = $(this).children("ul");
        if (el.hasClass("hov")) {
            $(el).removeClass("hov");
        } else {
            $(el).addClass("hov");
        }
    });
    if ($(".content").length) {
        $(".h1").click(function() {
            $(".content").slideToggle(animate_duration, easing, function() {});
        });
    }
    if ($("#thanks_holder").length) {
        show_thanks(tid);
    }
    if ($("#tz-checkdst").length) {
        if (!$("#tz-checkdst").is(":checked")) {
            $("#tz-checkmanual").show();
        }
    }
    $("#tz-checkdst").click(function() {
        $("#tz-checkmanual").slideToggle(animate_duration, easing, function() {});
    });
    $('li a[href=".' + this.location.pathname + this.location.search + '"]').addClass("is_active");
    if ($("#checkThemAll").length) {
        $("#checkThemAll").change(function() {
            $("input:checkbox").prop("checked", $(this).prop("checked"));
        });
    }
    if ($("#checkAll").length) {
        $("#checkAll").change(function() {
            $("#checkbox_container :checkbox").prop("checked", $(this).prop("checked"));
        });
        if ($("#checkbox_container :checkbox:checked").length == $("#checkbox_container :checkbox").length) {
            $("#checkAll").prop("checked", true);
        }
        $("#checkbox_container :checkbox").click(function() {
            if ($("#checkbox_container :checkbox:checked").length == $("#checkbox_container :checkbox").length) {
                $("#checkAll").prop("checked", true);
            } else {
                $("#checkAll").prop("checked", false);
            }
        });
    }
    if ($("#accordion").length) {
        $("#accordion").accordion({
            collapsible: true,
            active: false,
            animate: animate_duration,
            heightStyle: "content"
        });
    }
    if ($(".notification").length) {
        setTimeout(function() {
            $(".notification").slideUp(animate_duration, function() {
                $(".notification").remove();
            });
        }, 15e3);
    }
    $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(event) {
        if (location.pathname.replace(/^\//, "") == this.pathname.replace(/^\//, "") && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $("[name=" + this.hash.slice(1) + "]");
            if (target.length) {
                event.preventDefault();
                $("html, body").animate({
                    scrollTop: target.offset().top
                }, animate_duration, function() {
                    var $target = $(target);
                    $target.focus();
                    if ($target.is(":focus")) {
                        return false;
                    } else {
                        $target.attr("tabindex", "-1");
                        $target.focus();
                    }
                });
            }
        }
    });
});