(function($) {
    $.fn.lightBox = function(settings) {
        settings = jQuery.extend({
            overlayBgColor: "#000",
            overlayOpacity: .8,
            fixedNavigation: false,
            imageLoading: "images/lightbox-ico-loading.gif",
            imageBtnPrev: "images/lightbox-btn-prev.gif",
            imageBtnNext: "images/lightbox-btn-next.gif",
            imageBtnClose: "images/lightbox-btn-close.gif",
            imageBlank: "images/lightbox-blank.gif",
            containerBorderSize: 10,
            containerResizeSpeed: 400,
            txtImage: "Image",
            txtOf: "of",
            keyToClose: "c",
            keyToPrev: "p",
            keyToNext: "n",
            imageArray: [],
            activeImage: 0
        }, settings);
        var jQueryMatchedObj = this;
        function _initialize() {
            _start(this, jQueryMatchedObj);
            return false;
        }
        function _start(objClicked, jQueryMatchedObj) {
            $("embed, object, select").css({
                visibility: "hidden"
            });
            _set_interface();
            settings.imageArray.length = 0;
            settings.activeImage = 0;
            if (jQueryMatchedObj.length == 1) {
                settings.imageArray.push(new Array(objClicked.getAttribute("href"), objClicked.getAttribute("title")));
            } else {
                for (var i = 0; i < jQueryMatchedObj.length; i++) {
                    settings.imageArray.push(new Array(jQueryMatchedObj[i].getAttribute("href"), jQueryMatchedObj[i].getAttribute("title")));
                }
            }
            while (settings.imageArray[settings.activeImage][0] != objClicked.getAttribute("href")) {
                settings.activeImage++;
            }
            _set_image_to_view();
        }
        function _set_interface() {
            $("body").append('<div id="jquery-overlay"></div><div id="jquery-lightbox"><div id="lightbox-container-image-box"><div id="lightbox-container-image"><img id="lightbox-image"><div style="" id="lightbox-nav"><a href="#" id="lightbox-nav-btnPrev"></a><a href="#" id="lightbox-nav-btnNext"></a></div><div id="lightbox-loading"><a href="#" id="lightbox-loading-link"><img src="' + settings.imageLoading + '"></a></div></div></div><div id="lightbox-container-image-data-box"><div id="lightbox-container-image-data"><div id="lightbox-image-details"><span id="lightbox-image-details-caption"></span><span id="lightbox-image-details-currentNumber"></span></div><div id="lightbox-secNav"><a href="#" id="lightbox-secNav-btnClose"><img src="' + settings.imageBtnClose + '"></a></div></div></div></div>');
            var arrPageSizes = ___getPageSize();
            $("#jquery-overlay").css({
                backgroundColor: settings.overlayBgColor,
                opacity: settings.overlayOpacity,
                width: arrPageSizes[0],
                height: arrPageSizes[1]
            }).fadeIn();
            var arrPageScroll = ___getPageScroll();
            $("#jquery-lightbox").css({
                top: arrPageScroll[1] + arrPageSizes[3] / 10,
                left: arrPageScroll[0]
            }).show();
            $("#jquery-overlay,#jquery-lightbox").click(function() {
                _finish();
            });
            $("#lightbox-loading-link,#lightbox-secNav-btnClose").click(function() {
                _finish();
                return false;
            });
            $(window).resize(function() {
                var arrPageSizes = ___getPageSize();
                $("#jquery-overlay").css({
                    width: arrPageSizes[0],
                    height: arrPageSizes[1]
                });
                var arrPageScroll = ___getPageScroll();
                $("#jquery-lightbox").css({
                    top: arrPageScroll[1] + arrPageSizes[3] / 10,
                    left: arrPageScroll[0]
                });
            });
        }
        function _set_image_to_view() {
            $("#lightbox-loading").show();
            if (settings.fixedNavigation) {
                $("#lightbox-image,#lightbox-container-image-data-box,#lightbox-image-details-currentNumber").hide();
            } else {
                $("#lightbox-image,#lightbox-nav,#lightbox-nav-btnPrev,#lightbox-nav-btnNext,#lightbox-container-image-data-box,#lightbox-image-details-currentNumber").hide();
            }
            var objImagePreloader = new Image();
            objImagePreloader.onload = function() {
                $("#lightbox-image").attr("src", settings.imageArray[settings.activeImage][0]);
                _resize_container_image_box(objImagePreloader.width, objImagePreloader.height);
                objImagePreloader.onload = function() {};
            };
            objImagePreloader.src = settings.imageArray[settings.activeImage][0];
        }
        function _resize_container_image_box(intImageWidth, intImageHeight) {
            var intCurrentWidth = $("#lightbox-container-image-box").width();
            var intCurrentHeight = $("#lightbox-container-image-box").height();
            var intWidth = intImageWidth + settings.containerBorderSize * 2;
            var intHeight = intImageHeight + settings.containerBorderSize * 2;
            var intDiffW = intCurrentWidth - intWidth;
            var intDiffH = intCurrentHeight - intHeight;
            $("#lightbox-container-image-box").animate({
                width: intWidth,
                height: intHeight
            }, settings.containerResizeSpeed, function() {
                _show_image();
            });
            if (intDiffW == 0 && intDiffH == 0) {
                var uA = navigator.userAgent.toLowerCase();
                if (uA.indexOf("msie") != -1) {
                    ___pause(250);
                } else {
                    ___pause(100);
                }
            }
            $("#lightbox-container-image-data-box").css({
                width: intImageWidth
            });
            $("#lightbox-nav-btnPrev,#lightbox-nav-btnNext").css({
                height: intImageHeight + settings.containerBorderSize * 2
            });
        }
        function _show_image() {
            $("#lightbox-loading").hide();
            $("#lightbox-image").fadeIn(function() {
                _show_image_data();
                _set_navigation();
            });
            _preload_neighbor_images();
        }
        function _show_image_data() {
            $("#lightbox-container-image-data-box").slideDown("fast");
            $("#lightbox-image-details-caption").hide();
            if (settings.imageArray[settings.activeImage][1]) {
                $("#lightbox-image-details-caption").html(settings.imageArray[settings.activeImage][1]).show();
            }
            if (settings.imageArray.length > 1) {
                $("#lightbox-image-details-currentNumber").html(settings.txtImage + " " + (settings.activeImage + 1) + " " + settings.txtOf + " " + settings.imageArray.length).show();
            }
        }
        function _set_navigation() {
            $("#lightbox-nav").show();
            $("#lightbox-nav-btnPrev,#lightbox-nav-btnNext").css({
                background: "transparent url(" + settings.imageBlank + ") no-repeat"
            });
            if (settings.activeImage != 0) {
                if (settings.fixedNavigation) {
                    $("#lightbox-nav-btnPrev").css({
                        background: "url(" + settings.imageBtnPrev + ") left 15% no-repeat"
                    }).unbind().bind("click", function() {
                        settings.activeImage = settings.activeImage - 1;
                        _set_image_to_view();
                        return false;
                    });
                } else {
                    $("#lightbox-nav-btnPrev").unbind().hover(function() {
                        $(this).css({
                            background: "url(" + settings.imageBtnPrev + ") left 15% no-repeat"
                        });
                    }, function() {
                        $(this).css({
                            background: "transparent url(" + settings.imageBlank + ") no-repeat"
                        });
                    }).show().bind("click", function() {
                        settings.activeImage = settings.activeImage - 1;
                        _set_image_to_view();
                        return false;
                    });
                }
            }
            if (settings.activeImage != settings.imageArray.length - 1) {
                if (settings.fixedNavigation) {
                    $("#lightbox-nav-btnNext").css({
                        background: "url(" + settings.imageBtnNext + ") right 15% no-repeat"
                    }).unbind().bind("click", function() {
                        settings.activeImage = settings.activeImage + 1;
                        _set_image_to_view();
                        return false;
                    });
                } else {
                    $("#lightbox-nav-btnNext").unbind().hover(function() {
                        $(this).css({
                            background: "url(" + settings.imageBtnNext + ") right 15% no-repeat"
                        });
                    }, function() {
                        $(this).css({
                            background: "transparent url(" + settings.imageBlank + ") no-repeat"
                        });
                    }).show().bind("click", function() {
                        settings.activeImage = settings.activeImage + 1;
                        _set_image_to_view();
                        return false;
                    });
                }
            }
            _enable_keyboard_navigation();
        }
        function _enable_keyboard_navigation() {
            $(document).keydown(function(objEvent) {
                _keyboard_action(objEvent);
            });
        }
        function _disable_keyboard_navigation() {
            $(document).unbind();
        }
        function _keyboard_action(objEvent) {
            if (objEvent == null) {
                keycode = event.keyCode;
                escapeKey = 27;
            } else {
                keycode = objEvent.keyCode;
                escapeKey = objEvent.DOM_VK_ESCAPE;
            }
            key = String.fromCharCode(keycode).toLowerCase();
            if (key == settings.keyToClose || key == "x" || keycode == escapeKey) {
                _finish();
            }
            if (key == settings.keyToPrev || keycode == 37) {
                if (settings.activeImage != 0) {
                    settings.activeImage = settings.activeImage - 1;
                    _set_image_to_view();
                    _disable_keyboard_navigation();
                }
            }
            if (key == settings.keyToNext || keycode == 39) {
                if (settings.activeImage != settings.imageArray.length - 1) {
                    settings.activeImage = settings.activeImage + 1;
                    _set_image_to_view();
                    _disable_keyboard_navigation();
                }
            }
        }
        function _preload_neighbor_images() {
            if (settings.imageArray.length - 1 > settings.activeImage) {
                objNext = new Image();
                objNext.src = settings.imageArray[settings.activeImage + 1][0];
            }
            if (settings.activeImage > 0) {
                objPrev = new Image();
                objPrev.src = settings.imageArray[settings.activeImage - 1][0];
            }
        }
        function _finish() {
            $("#jquery-lightbox").remove();
            $("#jquery-overlay").fadeOut(function() {
                $("#jquery-overlay").remove();
            });
            $("embed, object, select").css({
                visibility: "visible"
            });
        }
        function ___getPageSize() {
            var xScroll, yScroll;
            if (window.innerHeight && window.scrollMaxY) {
                xScroll = window.innerWidth + window.scrollMaxX;
                yScroll = window.innerHeight + window.scrollMaxY;
            } else if (document.body.scrollHeight > document.body.offsetHeight) {
                xScroll = document.body.scrollWidth;
                yScroll = document.body.scrollHeight;
            } else {
                xScroll = document.body.offsetWidth;
                yScroll = document.body.offsetHeight;
            }
            var windowWidth, windowHeight;
            if (self.innerHeight) {
                if (document.documentElement.clientWidth) {
                    windowWidth = document.documentElement.clientWidth;
                } else {
                    windowWidth = self.innerWidth;
                }
                windowHeight = self.innerHeight;
            } else if (document.documentElement && document.documentElement.clientHeight) {
                windowWidth = document.documentElement.clientWidth;
                windowHeight = document.documentElement.clientHeight;
            } else if (document.body) {
                windowWidth = document.body.clientWidth;
                windowHeight = document.body.clientHeight;
            }
            if (yScroll < windowHeight) {
                pageHeight = windowHeight;
            } else {
                pageHeight = yScroll;
            }
            if (xScroll < windowWidth) {
                pageWidth = xScroll;
            } else {
                pageWidth = windowWidth;
            }
            arrayPageSize = new Array(pageWidth, pageHeight, windowWidth, windowHeight);
            return arrayPageSize;
        }
        function ___getPageScroll() {
            var xScroll, yScroll;
            if (self.pageYOffset) {
                yScroll = self.pageYOffset;
                xScroll = self.pageXOffset;
            } else if (document.documentElement && document.documentElement.scrollTop) {
                yScroll = document.documentElement.scrollTop;
                xScroll = document.documentElement.scrollLeft;
            } else if (document.body) {
                yScroll = document.body.scrollTop;
                xScroll = document.body.scrollLeft;
            }
            arrayPageScroll = new Array(xScroll, yScroll);
            return arrayPageScroll;
        }
        function ___pause(ms) {
            var date = new Date();
            curDate = null;
            do {
                var curDate = new Date();
            } while (curDate - date < ms);
        }
        return this.unbind("click").click(_initialize);
    };
})(jQuery);

$("document").ready(function() {
    $("a[rel='lightbox']").lightBox();
});

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

jQuery.fn.trilemma = function(options) {
    var options = options || {};
    var cbfs = this;
    var cbs = this.find("input:checkbox");
    var maxnum = options.max ? options.max : 2;
    cbs.each(function() {
        $(this).bind("click", function() {
            if ($(this).is(":checked")) {
                if (cbs.filter(":checked").length == maxnum) {
                    cbs.not(":checked").each(function() {
                        $(this).attr("disabled", "true");
                        if (options.disablelabels) {
                            var thisid = $(this).attr("id");
                            $('label[for="' + thisid + '"]').addClass("disabled");
                        }
                    });
                }
            } else {
                cbs.removeAttr("disabled");
                if (options.disablelabels) {
                    cbfs.find("label.disabled").removeClass("disabled");
                }
            }
        });
    });
    return this;
};

$(function() {
    $(".poll_select").trilemma({
        max: " . $multi_options . ",
        disablelabels: true
    });
});

$(document).ready(function() {
    $("#tool_open").click(function() {
        $("#tools").slideToggle("slow", function() {});
    });
    $("#toggle_voters").click(function() {
        $("#voters").slideToggle("slow", function() {});
    });
    $("#toggle_voters").click(function() {
        $("#voters").slideToggle("slow", function() {});
    });
});

$("#pm_open").click(function() {
    $("#pm").slideToggle("slow", function() {});
});

(function($) {
    $.fn.markItUp = function(settings, extraSettings) {
        var method, params, options, ctrlKey, shiftKey, altKey;
        ctrlKey = shiftKey = altKey = false;
        if (typeof settings == "string") {
            method = settings;
            params = extraSettings;
        }
        options = {
            id: "",
            nameSpace: "",
            root: "",
            previewHandler: false,
            previewInWindow: "",
            previewInElement: "",
            previewAutoRefresh: true,
            previewPosition: "after",
            previewTemplatePath: "~/templates/preview.html",
            previewParser: false,
            previewParserPath: "",
            previewParserVar: "data",
            previewParserAjaxType: "POST",
            resizeHandle: true,
            beforeInsert: "",
            afterInsert: "",
            onEnter: {},
            onShiftEnter: {},
            onCtrlEnter: {},
            onTab: {},
            markupSet: [ {} ]
        };
        $.extend(options, settings, extraSettings);
        if (!options.root) {
            $("script").each(function(a, tag) {
                miuScript = $(tag).get(0).src.match(/(.*)jquery\.markitup(\.pack)?\.js$/);
                if (miuScript !== null) {
                    options.root = miuScript[1];
                }
            });
        }
        var uaMatch = function(ua) {
            ua = ua.toLowerCase();
            var match = /(chrome)[ \/]([\w.]+)/.exec(ua) || /(webkit)[ \/]([\w.]+)/.exec(ua) || /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) || /(msie) ([\w.]+)/.exec(ua) || ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) || [];
            return {
                browser: match[1] || "",
                version: match[2] || "0"
            };
        };
        var matched = uaMatch(navigator.userAgent);
        var browser = {};
        if (matched.browser) {
            browser[matched.browser] = true;
            browser.version = matched.version;
        }
        if (browser.chrome) {
            browser.webkit = true;
        } else if (browser.webkit) {
            browser.safari = true;
        }
        return this.each(function() {
            var $$, textarea, levels, scrollPosition, caretPosition, caretOffset, clicked, hash, header, footer, previewWindow, template, iFrame, abort;
            $$ = $(this);
            textarea = this;
            levels = [];
            abort = false;
            scrollPosition = caretPosition = 0;
            caretOffset = -1;
            options.previewParserPath = localize(options.previewParserPath);
            options.previewTemplatePath = localize(options.previewTemplatePath);
            if (method) {
                switch (method) {
                  case "remove":
                    remove();
                    break;

                  case "insert":
                    markup(params);
                    break;

                  default:
                    $.error("Method " + method + " does not exist on jQuery.markItUp");
                }
                return;
            }
            function localize(data, inText) {
                if (inText) {
                    return data.replace(/("|')~\//g, "$1" + options.root);
                }
                return data.replace(/^~\//, options.root);
            }
            function init() {
                id = "";
                nameSpace = "";
                if (options.id) {
                    id = 'id="' + options.id + '"';
                } else if ($$.attr("id")) {
                    id = 'id="markItUp' + $$.attr("id").substr(0, 1).toUpperCase() + $$.attr("id").substr(1) + '"';
                }
                if (options.nameSpace) {
                    nameSpace = 'id="' + options.nameSpace + '" class="' + options.nameSpace + '"';
                }
                $$.wrap("<div " + nameSpace + "></div>");
                $$.wrap("<div " + id + ' class="markItUp"></div>');
                $$.wrap('<div class="markItUpContainer"></div>');
                $$.addClass("markItUpEditor");
                header = $('<div class="markItUpHeader"></div>').insertBefore($$);
                $(dropMenus(options.markupSet)).appendTo(header);
                footer = $('<div class="markItUpFooter"></div>').insertAfter($$);
                if (options.resizeHandle === true && browser.safari !== true) {
                    resizeHandle = $('<div class="markItUpResizeHandle"></div>').insertAfter($$).bind("mousedown.markItUp", function(e) {
                        var h = $$.height(), y = e.clientY, mouseMove, mouseUp;
                        mouseMove = function(e) {
                            $$.css("height", Math.max(20, e.clientY + h - y) + "px");
                            return false;
                        };
                        mouseUp = function(e) {
                            $("html").unbind("mousemove.markItUp", mouseMove).unbind("mouseup.markItUp", mouseUp);
                            return false;
                        };
                        $("html").bind("mousemove.markItUp", mouseMove).bind("mouseup.markItUp", mouseUp);
                    });
                    footer.append(resizeHandle);
                }
                $$.bind("keydown.markItUp", keyPressed).bind("keyup", keyPressed);
                $$.bind("insertion.markItUp", function(e, settings) {
                    if (settings.target !== false) {
                        get();
                    }
                    if (textarea === $.markItUp.focused) {
                        markup(settings);
                    }
                });
                $$.bind("focus.markItUp", function() {
                    $.markItUp.focused = this;
                });
                if (options.previewInElement) {
                    refreshPreview();
                }
            }
            function dropMenus(markupSet) {
                var ul = $("<ul></ul>"), i = 0;
                $("li:hover > ul", ul).css("display", "block");
                $.each(markupSet, function() {
                    var button = this, t = "", title, li, j;
                    button.title ? title = button.key ? (button.title || "") + " [Ctrl+" + button.key + "]" : button.title || "" : title = button.key ? (button.name || "") + " [Ctrl+" + button.key + "]" : button.name || "";
                    key = button.key ? 'accesskey="' + button.key + '"' : "";
                    if (button.separator) {
                        li = $('<li class="markItUpSeparator">' + (button.separator || "") + "</li>").appendTo(ul);
                    } else {
                        i++;
                        for (j = levels.length - 1; j >= 0; j--) {
                            t += levels[j] + "-";
                        }
                        var setTitle = ' title="' + title + '"';
                        var addClass = "";
                        if (typeof button.className !== "undefined") {
                            var str = button.className;
                            if (str.includes("font_") || str.includes("text-") || str.includes("size") || str.includes("colors")) {
                                setTitle = "";
                            }
                            if (str.includes("text-")) {
                                addClass = " " + str;
                            }
                        }
                        li = $('<li class="tooltipper markItUpButton markItUpButton' + t + i + " " + (button.className || "") + '"' + setTitle + '><a href="#" ' + key + ">" + (button.showName || "") + "</a></li>").bind("contextmenu.markItUp", function() {
                            return false;
                        }).bind("click.markItUp", function(e) {
                            e.preventDefault();
                        }).bind("focusin.markItUp", function() {
                            $$.focus();
                        }).bind("mouseup", function(e) {
                            if (button.call) {
                                eval(button.call)(e);
                            }
                            setTimeout(function() {
                                markup(button);
                            }, 1);
                            return false;
                        }).bind("mouseenter.markItUp", function() {
                            $("> ul", this).show();
                            $(document).one("click", function() {
                                $("ul ul", header).hide();
                            });
                        }).bind("mouseleave.markItUp", function() {
                            $("> ul", this).hide();
                        }).appendTo(ul);
                        if (button.dropMenu) {
                            levels.push(i);
                            $(li).addClass("markItUpDropMenu").append(dropMenus(button.dropMenu));
                        }
                    }
                });
                levels.pop();
                return ul;
            }
            function magicMarkups(string) {
                if (string) {
                    string = string.toString();
                    string = string.replace(/\(\!\(([\s\S]*?)\)\!\)/g, function(x, a) {
                        var b = a.split("|!|");
                        if (altKey === true) {
                            return b[1] !== undefined ? b[1] : b[0];
                        } else {
                            return b[1] === undefined ? "" : b[0];
                        }
                    });
                    string = string.replace(/\[\!\[([\s\S]*?)\]\!\]/g, function(x, a) {
                        var b = a.split(":!:");
                        if (abort === true) {
                            return false;
                        }
                        value = prompt(b[0], b[1] ? b[1] : "");
                        if (value === null) {
                            abort = true;
                        }
                        return value;
                    });
                    return string;
                }
                return "";
            }
            function prepare(action) {
                if ($.isFunction(action)) {
                    action = action(hash);
                }
                return magicMarkups(action);
            }
            function build(string) {
                var openWith = prepare(clicked.openWith);
                var placeHolder = prepare(clicked.placeHolder);
                var replaceWith = prepare(clicked.replaceWith);
                var closeWith = prepare(clicked.closeWith);
                var openBlockWith = prepare(clicked.openBlockWith);
                var closeBlockWith = prepare(clicked.closeBlockWith);
                var multiline = clicked.multiline;
                if (replaceWith !== "") {
                    block = openWith + replaceWith + closeWith;
                } else if (selection === "" && placeHolder !== "") {
                    block = openWith + placeHolder + closeWith;
                } else {
                    string = string || selection;
                    var lines = [ string ], blocks = [];
                    if (multiline === true) {
                        lines = string.split(/\r?\n/);
                    }
                    for (var l = 0; l < lines.length; l++) {
                        line = lines[l];
                        var trailingSpaces;
                        if (trailingSpaces = line.match(/ *$/)) {
                            blocks.push(openWith + line.replace(/ *$/g, "") + closeWith + trailingSpaces);
                        } else {
                            blocks.push(openWith + line + closeWith);
                        }
                    }
                    block = blocks.join("\n");
                }
                block = openBlockWith + block + closeBlockWith;
                return {
                    block: block,
                    openBlockWith: openBlockWith,
                    openWith: openWith,
                    replaceWith: replaceWith,
                    placeHolder: placeHolder,
                    closeWith: closeWith,
                    closeBlockWith: closeBlockWith
                };
            }
            function markup(button) {
                var len, j, n, i;
                hash = clicked = button;
                get();
                $.extend(hash, {
                    line: "",
                    root: options.root,
                    textarea: textarea,
                    selection: selection || "",
                    caretPosition: caretPosition,
                    ctrlKey: ctrlKey,
                    shiftKey: shiftKey,
                    altKey: altKey
                });
                prepare(options.beforeInsert);
                prepare(clicked.beforeInsert);
                if (ctrlKey === true && shiftKey === true || button.multiline === true) {
                    prepare(clicked.beforeMultiInsert);
                }
                $.extend(hash, {
                    line: 1
                });
                if (ctrlKey === true && shiftKey === true) {
                    lines = selection.split(/\r?\n/);
                    for (j = 0, n = lines.length, i = 0; i < n; i++) {
                        if ($.trim(lines[i]) !== "") {
                            $.extend(hash, {
                                line: ++j,
                                selection: lines[i]
                            });
                            lines[i] = build(lines[i]).block;
                        } else {
                            lines[i] = "";
                        }
                    }
                    string = {
                        block: lines.join("\n")
                    };
                    start = caretPosition;
                    len = string.block.length + (browser.opera ? n - 1 : 0);
                } else if (ctrlKey === true) {
                    string = build(selection);
                    start = caretPosition + string.openWith.length;
                    len = string.block.length - string.openWith.length - string.closeWith.length;
                    len = len - (string.block.match(/ $/) ? 1 : 0);
                    len -= fixIeBug(string.block);
                } else if (shiftKey === true) {
                    string = build(selection);
                    start = caretPosition;
                    len = string.block.length;
                    len -= fixIeBug(string.block);
                } else {
                    string = build(selection);
                    start = caretPosition + string.block.length;
                    len = 0;
                    start -= fixIeBug(string.block);
                }
                if (selection === "" && string.replaceWith === "") {
                    caretOffset += fixOperaBug(string.block);
                    start = caretPosition + string.openBlockWith.length + string.openWith.length;
                    len = string.block.length - string.openBlockWith.length - string.openWith.length - string.closeWith.length - string.closeBlockWith.length;
                    caretOffset = $$.val().substring(caretPosition, $$.val().length).length;
                    caretOffset -= fixOperaBug($$.val().substring(0, caretPosition));
                }
                $.extend(hash, {
                    caretPosition: caretPosition,
                    scrollPosition: scrollPosition
                });
                if (string.block !== selection && abort === false) {
                    insert(string.block);
                    set(start, len);
                } else {
                    caretOffset = -1;
                }
                get();
                $.extend(hash, {
                    line: "",
                    selection: selection
                });
                if (ctrlKey === true && shiftKey === true || button.multiline === true) {
                    prepare(clicked.afterMultiInsert);
                }
                prepare(clicked.afterInsert);
                prepare(options.afterInsert);
                if (previewWindow && options.previewAutoRefresh) {
                    refreshPreview();
                }
                shiftKey = altKey = ctrlKey = abort = false;
            }
            function fixOperaBug(string) {
                if (browser.opera) {
                    return string.length - string.replace(/\n*/g, "").length;
                }
                return 0;
            }
            function fixIeBug(string) {
                if (browser.msie) {
                    return string.length - string.replace(/\r*/g, "").length;
                }
                return 0;
            }
            function insert(block) {
                if (document.selection) {
                    var newSelection = document.selection.createRange();
                    newSelection.text = block;
                } else {
                    textarea.value = textarea.value.substring(0, caretPosition) + block + textarea.value.substring(caretPosition + selection.length, textarea.value.length);
                }
            }
            function set(start, len) {
                if (textarea.createTextRange) {
                    if (browser.opera && browser.version >= 9.5 && len == 0) {
                        return false;
                    }
                    range = textarea.createTextRange();
                    range.collapse(true);
                    range.moveStart("character", start);
                    range.moveEnd("character", len);
                    range.select();
                } else if (textarea.setSelectionRange) {
                    textarea.setSelectionRange(start, start + len);
                }
                textarea.scrollTop = scrollPosition;
                textarea.focus();
            }
            function get() {
                textarea.focus();
                scrollPosition = textarea.scrollTop;
                if (document.selection) {
                    selection = document.selection.createRange().text;
                    if (browser.msie) {
                        var range = document.selection.createRange(), rangeCopy = range.duplicate();
                        rangeCopy.moveToElementText(textarea);
                        caretPosition = -1;
                        while (rangeCopy.inRange(range)) {
                            rangeCopy.moveStart("character");
                            caretPosition++;
                        }
                    } else {
                        caretPosition = textarea.selectionStart;
                    }
                } else {
                    caretPosition = textarea.selectionStart;
                    selection = textarea.value.substring(caretPosition, textarea.selectionEnd);
                }
                return selection;
            }
            function preview() {
                if (typeof options.previewHandler === "function") {
                    previewWindow = true;
                } else if (options.previewInElement) {
                    previewWindow = $(options.previewInElement);
                    var parent = $("#" + options.previewInElement).parent().parent().attr("id");
                    $("#" + parent).slideToggle(1250);
                } else if (!previewWindow || previewWindow.closed) {
                    if (options.previewInWindow) {
                        previewWindow = window.open("", "preview", options.previewInWindow);
                        $(window).unload(function() {
                            previewWindow.close();
                        });
                    } else {
                        iFrame = $('<iframe class="markItUpPreviewFrame"></iframe>');
                        if (options.previewPosition == "after") {
                            iFrame.insertAfter(footer);
                        } else {
                            iFrame.insertBefore(header);
                        }
                        previewWindow = iFrame[iFrame.length - 1].contentWindow || frame[iFrame.length - 1];
                    }
                } else if (altKey === true) {
                    if (iFrame) {
                        iFrame.remove();
                    } else {
                        previewWindow.close();
                    }
                    previewWindow = iFrame = false;
                }
                if (!options.previewAutoRefresh) {
                    refreshPreview();
                }
                if (options.previewInWindow) {
                    previewWindow.focus();
                }
            }
            function refreshPreview() {
                renderPreview();
            }
            function renderPreview() {
                var parsedData = $$.val();
                if (options.previewParser && typeof options.previewParser === "function") {
                    parsedData = options.previewParser(parsedData);
                }
                if (parsedData.length > 1) {
                    if (options.previewInElement != "") {
                        var parent = $("#" + options.previewInElement).parent().parent().attr("id");
                        if ($("#" + parent).is(":visible")) {
                            getAjax(parsedData);
                        }
                    } else {
                        getAjax(parsedData);
                    }
                } else {
                    return false;
                }
            }
            function getAjax(parsedData) {
                if (options.previewHandler && typeof options.previewHandler === "function") {
                    options.previewHandler(parsedData);
                } else if (options.previewParserPath !== "") {
                    $.ajax({
                        type: options.previewParserAjaxType,
                        dataType: "text",
                        global: false,
                        url: options.previewParserPath,
                        data: options.previewParserVar + "=" + encodeURIComponent(parsedData),
                        success: function(data) {
                            writeInPreview(localize(data, 1));
                        }
                    });
                } else {
                    if (!template) {
                        $.ajax({
                            url: options.previewTemplatePath,
                            dataType: "text",
                            global: false,
                            success: function(data) {
                                writeInPreview(localize(data, 1).replace(/<!-- content -->/g, parsedData));
                            }
                        });
                    }
                }
                return false;
            }
            function writeInPreview(data) {
                if (options.previewInElement) {
                    $("#" + options.previewInElement).html(data);
                } else if (previewWindow && previewWindow.document) {
                    try {
                        sp = previewWindow.document.documentElement.scrollTop;
                    } catch (e) {
                        sp = 0;
                    }
                    previewWindow.document.open();
                    previewWindow.document.write(data);
                    previewWindow.document.close();
                    previewWindow.document.documentElement.scrollTop = sp;
                }
            }
            function keyPressed(e) {
                shiftKey = e.shiftKey;
                altKey = e.altKey;
                ctrlKey = !(e.altKey && e.ctrlKey) ? e.ctrlKey || e.metaKey : false;
                if (e.type === "keydown") {
                    if (ctrlKey === true) {
                        li = $('a[accesskey="' + (e.keyCode == 13 ? "\\n" : String.fromCharCode(e.keyCode)) + '"]', header).parent("li");
                        if (li.length !== 0) {
                            ctrlKey = false;
                            setTimeout(function() {
                                li.triggerHandler("mouseup");
                            }, 1);
                            return false;
                        }
                    }
                    if (e.keyCode === 13 || e.keyCode === 10) {
                        if (ctrlKey === true) {
                            ctrlKey = false;
                            markup(options.onCtrlEnter);
                            return options.onCtrlEnter.keepDefault;
                        } else if (shiftKey === true) {
                            shiftKey = false;
                            markup(options.onShiftEnter);
                            return options.onShiftEnter.keepDefault;
                        } else {
                            markup(options.onEnter);
                            return options.onEnter.keepDefault;
                        }
                    }
                    if (e.keyCode === 9) {
                        if (shiftKey == true || ctrlKey == true || altKey == true) {
                            return false;
                        }
                        if (caretOffset !== -1) {
                            get();
                            caretOffset = $$.val().length - caretOffset;
                            set(caretOffset, 0);
                            caretOffset = -1;
                            return false;
                        } else {
                            markup(options.onTab);
                            return options.onTab.keepDefault;
                        }
                    }
                }
            }
            function remove() {
                $$.unbind(".markItUp").removeClass("markItUpEditor");
                $$.parent("div").parent("div.markItUp").parent("div").replaceWith($$);
                var relativeRef = $$.parent("div").parent("div.markItUp").parent("div");
                if (relativeRef.length) {
                    relativeRef.replaceWith($$);
                }
                $$.data("markItUp", null);
            }
            init();
        });
    };
    $.fn.markItUpRemove = function() {
        return this.each(function() {
            $(this).markItUp("remove");
        });
    };
    $.markItUp = function(settings) {
        var options = {
            target: false
        };
        $.extend(options, settings);
        if (options.target) {
            return $(options.target).each(function() {
                $(this).focus();
                $(this).trigger("insertion", [ options ]);
            });
        } else {
            $("textarea").trigger("insertion", [ options ]);
        }
    };
})(jQuery);

var myBbcodeSettings = {
    nameSpace: "bbcode",
    previewParserPath: "./ajax/bbcode_parser.php",
    previewInElement: "preview-window",
    markupSet: [ {
        name: "Bold",
        key: "B",
        openWith: "[b]",
        closeWith: "[/b]",
        className: "boldbutton"
    }, {
        name: "Italic",
        key: "I",
        openWith: "[i]",
        closeWith: "[/i]",
        className: "italicbutton"
    }, {
        name: "Underline",
        key: "U",
        openWith: "[u]",
        closeWith: "[/u]",
        className: "underlinebutton"
    }, {
        name: "Strike through",
        key: "S",
        openWith: "[s]",
        closeWith: "[/s]",
        className: "strikebutton"
    }, {
        name: "Subscript",
        openWith: "[sub]",
        closeWith: "[/sub]",
        className: "subscriptbutton"
    }, {
        name: "Superscript",
        openWith: "[sup]",
        closeWith: "[/sup]",
        className: "superscriptbutton"
    }, {
        name: "Horizontal line",
        openWith: "[hr] ",
        className: "Horizontal_line"
    }, {
        separator: " "
    }, {
        name: "Picture",
        key: "P",
        replaceWith: "[img][![Url]!][/img]",
        className: "picture"
    }, {
        name: "Link",
        key: "L",
        openWith: "[url=[![Url]!]]",
        closeWith: "[/url]",
        className: "linkbutton",
        placeHolder: "Your text to link here..."
    }, {
        name: "Youtube / Google Video",
        openWith: "[video=[![Enter URL to Google Or Yahoo Video Here]!]]",
        className: "youtubebutton"
    }, {
        name: "MP3 / Audio",
        openWith: "[audio][![Enter URL to Audio File Here]!]",
        closeWith: "[/audio]",
        className: "audiobutton"
    }, {
        name: "Email",
        openWith: "[email][![Enter Email Address Here]!]",
        closeWith: "[/email]",
        className: "emailbutton"
    }, {
        separator: " "
    }, {
        name: "Fonts",
        className: "fontsbutton",
        dropMenu: [ {
            name: "Oswald",
            showName: "Oswald",
            openWith: "[font01]",
            closeWith: "[/font01]",
            className: "text-1"
        }, {
            name: "PT Sans Narrow",
            showName: "PT Sans Narrow",
            openWith: "[font02]",
            closeWith: "[/font02]",
            className: "text-2"
        }, {
            name: "Nova Square",
            showName: "Nova Square",
            openWith: "[font03]",
            closeWith: "[/font03]",
            className: "text-3"
        }, {
            name: "Lobster",
            showName: "Lobster",
            openWith: "[font04]",
            closeWith: "[/font04]",
            className: "text-4"
        }, {
            name: "Open Sans",
            showName: "Open Sans",
            openWith: "[font05]",
            closeWith: "[/font05]",
            className: "text-5"
        }, {
            name: "Encode Sans Condensed",
            showName: "Encode Sans Condensed",
            openWith: "[font06]",
            closeWith: "[/font06]",
            className: "text-6"
        }, {
            name: "Baloo Bhaijaan",
            showName: "Baloo Bhaijaan",
            openWith: "[font07]",
            closeWith: "[/font07]",
            className: "text-7"
        }, {
            name: "Acme",
            showName: "Acme",
            openWith: "[font08]",
            closeWith: "[/font08]",
            className: "text-8"
        }, {
            name: "Arial",
            showName: "Arial",
            openWith: "[font=Arial]",
            closeWith: "[/font]",
            className: "font_7"
        }, {
            name: "Arial Black",
            showName: "Arial Black",
            openWith: "[font=Arial Black]",
            closeWith: "[/font]",
            className: "font_Arial"
        }, {
            name: "Comic Sans MS",
            showName: "Comic Sans MS",
            openWith: "[font=Comic Sans MS]",
            closeWith: "[/font]",
            className: "font_Comic_Sans_MS"
        }, {
            name: "Courier New",
            showName: "Courier New",
            openWith: "[font=Courier New]",
            closeWith: "[/font]",
            className: "font_Courier_New"
        }, {
            name: "Georgia",
            showName: "Georgia",
            openWith: "[font=Georgia]",
            closeWith: "[/font]",
            className: "font_Georgia"
        }, {
            name: "Impact",
            showName: "Impact",
            openWith: "[font=Impact]",
            closeWith: "[/font]",
            className: "font_Impact"
        }, {
            name: "Times New Roman",
            showName: "Times New Roman",
            openWith: "[font=Times New Roman]",
            closeWith: "[/font]",
            className: "font_Times_New_Roman"
        }, {
            name: "Trebuchet MS",
            showName: "Trebuchet MS",
            openWith: "[font=Trebuchet MS]",
            closeWith: "[/font]",
            className: "font_Trebuchet_MS"
        }, {
            name: "Verdana",
            showName: "Verdana",
            openWith: "[font=Verdana]",
            closeWith: "[/font]",
            className: "font_Verdana"
        }, {
            name: "Courier",
            showName: "Courier",
            openWith: "[font=Courier]",
            closeWith: "[/font]",
            className: "font_Courier"
        }, {
            name: "Helvetica",
            showName: "Helvetica",
            openWith: "[font=Helvetica]",
            closeWith: "[/font]",
            className: "font_Helvetica"
        }, {
            name: "Times",
            showName: "Times",
            openWith: "[font=Times]",
            closeWith: "[/font]",
            className: "font_Times"
        }, {
            name: "Andale Mono",
            showName: "Andale Mono",
            openWith: "[font=Andale Mono]",
            closeWith: "[/font]",
            className: "font_Andale_Mono"
        }, {
            name: "Bitstream Vera Sans",
            showName: "Bitstream Vera Sans",
            openWith: "[font=Bitstream Vera Sans]",
            closeWith: "[/font]",
            className: "font_Bitstream_Vera_Sans"
        }, {
            name: "Mono",
            showName: "Mono",
            openWith: "[font=Mono]",
            closeWith: "[/font]",
            className: "font_Mono"
        } ]
    }, {
        name: "Colors",
        className: "palette",
        openWith: "[color=[![Enter Hex or web-safe color, ie: #FF33FF or purple]!]]",
        closeWith: "[/color]",
        dropMenu: [ {
            name: "#330000",
            openWith: "[color=#330000]",
            closeWith: "[/color]",
            className: "col1-1"
        }, {
            name: "#333300",
            openWith: "[color=#333300]",
            closeWith: "[/color]",
            className: "col1-2"
        }, {
            name: "#336600",
            openWith: "[color=#336600]",
            closeWith: "[/color]",
            className: "col1-3"
        }, {
            name: "#339900",
            openWith: "[color=#339900]",
            closeWith: "[/color]",
            className: "col1-4"
        }, {
            name: "#33CC00",
            openWith: "[color=#33CC00]",
            closeWith: "[/color]",
            className: "col1-5"
        }, {
            name: "#33FF00",
            openWith: "[color=#33FF00]",
            closeWith: "[/color]",
            className: "col1-6"
        }, {
            name: "#66FF00",
            openWith: "[color=#66FF00]",
            closeWith: "[/color]",
            className: "col1-7"
        }, {
            name: "#66CC00",
            openWith: "[color=#66CC00]",
            closeWith: "[/color]",
            className: "col1-8"
        }, {
            name: "#669900",
            openWith: "[color=#669900]",
            closeWith: "[/color]",
            className: "col1-9"
        }, {
            name: "#666600",
            openWith: "[color=#666600]",
            closeWith: "[/color]",
            className: "col1-10"
        }, {
            name: "#663300",
            openWith: "[color=#663300]",
            closeWith: "[/color]",
            className: "col1-11"
        }, {
            name: "#660000",
            openWith: "[color=#660000]",
            closeWith: "[/color]",
            className: "col1-12"
        }, {
            name: "#FF0000",
            openWith: "[color=#FF0000]",
            closeWith: "[/color]",
            className: "col1-13"
        }, {
            name: "#FF3300",
            openWith: "[color=#FF3300]",
            closeWith: "[/color]",
            className: "col1-14"
        }, {
            name: "#FF6600",
            openWith: "[color=#FF6600]",
            closeWith: "[/color]",
            className: "col1-15"
        }, {
            name: "#FF9900",
            openWith: "[color=#FF9900]",
            closeWith: "[/color]",
            className: "col1-16"
        }, {
            name: "#FFCC00",
            openWith: "[color=#FFCC00]",
            closeWith: "[/color]",
            className: "col1-17"
        }, {
            name: "#FFFF00",
            openWith: "[color=#FFFF00]",
            closeWith: "[/color]",
            className: "col1-18"
        }, {
            name: "#330033",
            openWith: "[color=#330033]",
            closeWith: "[/color]",
            className: "col2-1"
        }, {
            name: "#333333",
            openWith: "[color=#333333]",
            closeWith: "[/color]",
            className: "col2-2"
        }, {
            name: "#336633",
            openWith: "[color=#336633]",
            closeWith: "[/color]",
            className: "col2-3"
        }, {
            name: "#339933",
            openWith: "[color=#339933]",
            closeWith: "[/color]",
            className: "col2-4"
        }, {
            name: "#33CC33",
            openWith: "[color=#33CC33]",
            closeWith: "[/color]",
            className: "col2-5"
        }, {
            name: "#33FF33",
            openWith: "[color=#33FF33]",
            closeWith: "[/color]",
            className: "col2-6"
        }, {
            name: "#66FF33",
            openWith: "[color=#66FF33]",
            closeWith: "[/color]",
            className: "col2-7"
        }, {
            name: "#66CC33",
            openWith: "[color=#66CC33]",
            closeWith: "[/color]",
            className: "col2-8"
        }, {
            name: "#669933",
            openWith: "[color=#669933]",
            closeWith: "[/color]",
            className: "col2-9"
        }, {
            name: "#666633",
            openWith: "[color=#666633]",
            closeWith: "[/color]",
            className: "col2-10"
        }, {
            name: "#663333",
            openWith: "[color=#663333]",
            closeWith: "[/color]",
            className: "col2-11"
        }, {
            name: "#660033",
            openWith: "[color=#660033]",
            closeWith: "[/color]",
            className: "col2-12"
        }, {
            name: "#FF0033",
            openWith: "[color=#FF0033]",
            closeWith: "[/color]",
            className: "col2-13"
        }, {
            name: "#FF3333",
            openWith: "[color=#FF3333]",
            closeWith: "[/color]",
            className: "col2-14"
        }, {
            name: "#FF6633",
            openWith: "[color=#FF6633]",
            closeWith: "[/color]",
            className: "col2-15"
        }, {
            name: "#FF9933",
            openWith: "[color=#FF9933]",
            closeWith: "[/color]",
            className: "col2-16"
        }, {
            name: "#FFCC33",
            openWith: "[color=#FFCC33]",
            closeWith: "[/color]",
            className: "col2-17"
        }, {
            name: "#FFFF33",
            openWith: "[color=#FFFF33]",
            closeWith: "[/color]",
            className: "col2-18"
        }, {
            name: "#330066",
            openWith: "[color=#330066]",
            closeWith: "[/color]",
            className: "col3-1"
        }, {
            name: "#333366",
            openWith: "[color=#333366]",
            closeWith: "[/color]",
            className: "col3-2"
        }, {
            name: "#336666",
            openWith: "[color=#336666]",
            closeWith: "[/color]",
            className: "col3-3"
        }, {
            name: "#339966",
            openWith: "[color=#339966]",
            closeWith: "[/color]",
            className: "col3-4"
        }, {
            name: "#33CC66",
            openWith: "[color=#33CC66]",
            closeWith: "[/color]",
            className: "col3-5"
        }, {
            name: "#33FF66",
            openWith: "[color=#33FF66]",
            closeWith: "[/color]",
            className: "col3-6"
        }, {
            name: "#66FF66",
            openWith: "[color=#66FF66]",
            closeWith: "[/color]",
            className: "col3-7"
        }, {
            name: "#66CC66",
            openWith: "[color=#66CC66]",
            closeWith: "[/color]",
            className: "col3-8"
        }, {
            name: "#669966",
            openWith: "[color=#669966]",
            closeWith: "[/color]",
            className: "col3-9"
        }, {
            name: "#666666",
            openWith: "[color=#666666]",
            closeWith: "[/color]",
            className: "col3-10"
        }, {
            name: "#663366",
            openWith: "[color=#663366]",
            closeWith: "[/color]",
            className: "col3-11"
        }, {
            name: "#660066",
            openWith: "[color=#660066]",
            closeWith: "[/color]",
            className: "col3-12"
        }, {
            name: "#FF0066",
            openWith: "[color=#FF0066]",
            closeWith: "[/color]",
            className: "col3-13"
        }, {
            name: "#FF3366",
            openWith: "[color=#FF3366]",
            closeWith: "[/color]",
            className: "col3-14"
        }, {
            name: "#FF6666",
            openWith: "[color=#FF6666]",
            closeWith: "[/color]",
            className: "col3-15"
        }, {
            name: "#FF9966",
            openWith: "[color=#FF9966]",
            closeWith: "[/color]",
            className: "col3-16"
        }, {
            name: "#FFCC66",
            openWith: "[color=#FFCC66]",
            closeWith: "[/color]",
            className: "col3-17"
        }, {
            name: "#FFFF66",
            openWith: "[color=#FFFF66]",
            closeWith: "[/color]",
            className: "col3-18"
        }, {
            name: "#330099",
            openWith: "[color=#330099]",
            closeWith: "[/color]",
            className: "col4-1"
        }, {
            name: "#333399",
            openWith: "[color=#333399]",
            closeWith: "[/color]",
            className: "col4-2"
        }, {
            name: "#336699",
            openWith: "[color=#336699]",
            closeWith: "[/color]",
            className: "col4-3"
        }, {
            name: "#339999",
            openWith: "[color=#339999]",
            closeWith: "[/color]",
            className: "col4-4"
        }, {
            name: "#33CC99",
            openWith: "[color=#33CC99]",
            closeWith: "[/color]",
            className: "col4-5"
        }, {
            name: "#33FF99",
            openWith: "[color=#33FF99]",
            closeWith: "[/color]",
            className: "col4-6"
        }, {
            name: "#66FF99",
            openWith: "[color=#66FF99]",
            closeWith: "[/color]",
            className: "col4-7"
        }, {
            name: "#66CC99",
            openWith: "[color=#66CC99]",
            closeWith: "[/color]",
            className: "col4-8"
        }, {
            name: "#669999",
            openWith: "[color=#669999]",
            closeWith: "[/color]",
            className: "col4-9"
        }, {
            name: "#666699",
            openWith: "[color=#666699]",
            closeWith: "[/color]",
            className: "col4-10"
        }, {
            name: "#663399",
            openWith: "[color=#663399]",
            closeWith: "[/color]",
            className: "col4-11"
        }, {
            name: "#660099",
            openWith: "[color=#660099]",
            closeWith: "[/color]",
            className: "col4-12"
        }, {
            name: "#FF0099",
            openWith: "[color=#FF0099]",
            closeWith: "[/color]",
            className: "col4-13"
        }, {
            name: "#FF3399",
            openWith: "[color=#FF3399]",
            closeWith: "[/color]",
            className: "col4-14"
        }, {
            name: "#FF6699",
            openWith: "[color=#FF6699]",
            closeWith: "[/color]",
            className: "col4-15"
        }, {
            name: "#FF9999",
            openWith: "[color=#FF9999]",
            closeWith: "[/color]",
            className: "col4-16"
        }, {
            name: "#FFCC99",
            openWith: "[color=#FFCC99]",
            closeWith: "[/color]",
            className: "col4-17"
        }, {
            name: "#FFFF99",
            openWith: "[color=#FFFF99]",
            closeWith: "[/color]",
            className: "col4-18"
        }, {
            name: "#3300CC",
            openWith: "[color=#3300CC]",
            closeWith: "[/color]",
            className: "col5-1"
        }, {
            name: "#3333CC",
            openWith: "[color=#3333CC]",
            closeWith: "[/color]",
            className: "col5-2"
        }, {
            name: "#3366CC",
            openWith: "[color=#3366CC]",
            closeWith: "[/color]",
            className: "col5-3"
        }, {
            name: "#3399CC",
            openWith: "[color=#3399CC]",
            closeWith: "[/color]",
            className: "col5-4"
        }, {
            name: "#33CCCC",
            openWith: "[color=#33CCCC]",
            closeWith: "[/color]",
            className: "col5-5"
        }, {
            name: "#33FFCC",
            openWith: "[color=#33FFCC]",
            closeWith: "[/color]",
            className: "col5-6"
        }, {
            name: "#66FFCC",
            openWith: "[color=#66FFCC]",
            closeWith: "[/color]",
            className: "col5-7"
        }, {
            name: "#66CCCC",
            openWith: "[color=#66CCCC]",
            closeWith: "[/color]",
            className: "col5-8"
        }, {
            name: "#6699CC",
            openWith: "[color=#6699CC]",
            closeWith: "[/color]",
            className: "col5-9"
        }, {
            name: "#6666CC",
            openWith: "[color=#6666CC]",
            closeWith: "[/color]",
            className: "col5-10"
        }, {
            name: "#6633CC",
            openWith: "[color=#6633CC]",
            closeWith: "[/color]",
            className: "col5-11"
        }, {
            name: "#6600CC",
            openWith: "[color=#6600CC]",
            closeWith: "[/color]",
            className: "col5-12"
        }, {
            name: "#FF00CC",
            openWith: "[color=#FF00CC]",
            closeWith: "[/color]",
            className: "col5-13"
        }, {
            name: "#FF33CC",
            openWith: "[color=#FF33CC]",
            closeWith: "[/color]",
            className: "col5-14"
        }, {
            name: "#FF66CC",
            openWith: "[color=#FF66CC]",
            closeWith: "[/color]",
            className: "col5-15"
        }, {
            name: "#FF99CC",
            openWith: "[color=#FF99CC]",
            closeWith: "[/color]",
            className: "col5-16"
        }, {
            name: "#FFCCCC",
            openWith: "[color=#FFCCCC]",
            closeWith: "[/color]",
            className: "col5-17"
        }, {
            name: "#FFFFCC",
            openWith: "[color=#FFFFCC]",
            closeWith: "[/color]",
            className: "col5-18"
        }, {
            name: "#3300FF",
            openWith: "[color=#3300FF]",
            closeWith: "[/color]",
            className: "col6-1"
        }, {
            name: "#3333FF",
            openWith: "[color=#3333FF]",
            closeWith: "[/color]",
            className: "col6-2"
        }, {
            name: "#3366FF",
            openWith: "[color=#3366FF]",
            closeWith: "[/color]",
            className: "col6-3"
        }, {
            name: "#3399FF",
            openWith: "[color=#3399FF]",
            closeWith: "[/color]",
            className: "col6-4"
        }, {
            name: "#33CCFF",
            openWith: "[color=#33CCFF]",
            closeWith: "[/color]",
            className: "col6-5"
        }, {
            name: "#33FFFF",
            openWith: "[color=#33FFFF]",
            closeWith: "[/color]",
            className: "col6-6"
        }, {
            name: "#66FFFF",
            openWith: "[color=#66FFFF]",
            closeWith: "[/color]",
            className: "col6-7"
        }, {
            name: "#66CCFF",
            openWith: "[color=#66CCFF]",
            closeWith: "[/color]",
            className: "col6-8"
        }, {
            name: "#6699FF",
            openWith: "[color=#6699FF]",
            closeWith: "[/color]",
            className: "col6-9"
        }, {
            name: "#6666FF",
            openWith: "[color=#6666FF]",
            closeWith: "[/color]",
            className: "col6-10"
        }, {
            name: "#6633FF",
            openWith: "[color=#6633FF]",
            closeWith: "[/color]",
            className: "col6-11"
        }, {
            name: "#6600FF",
            openWith: "[color=#6600FF]",
            closeWith: "[/color]",
            className: "col6-12"
        }, {
            name: "#FF00FF",
            openWith: "[color=#FF00FF]",
            closeWith: "[/color]",
            className: "col6-13"
        }, {
            name: "#FF33FF",
            openWith: "[color=#FF33FF]",
            closeWith: "[/color]",
            className: "col6-14"
        }, {
            name: "#FF66FF",
            openWith: "[color=#FF66FF]",
            closeWith: "[/color]",
            className: "col6-15"
        }, {
            name: "#FF99FF",
            openWith: "[color=#FF99FF]",
            closeWith: "[/color]",
            className: "col6-16"
        }, {
            name: "#FFCCFF",
            openWith: "[color=#FFCCFF]",
            closeWith: "[/color]",
            className: "col6-17"
        }, {
            name: "#FFFFFF",
            openWith: "[color=#FFFFFF]",
            closeWith: "[/color]",
            className: "col6-18"
        }, {
            name: "#0000FF",
            openWith: "[color=#0000FF]",
            closeWith: "[/color]",
            className: "col7-1"
        }, {
            name: "#0033FF",
            openWith: "[color=#0033FF]",
            closeWith: "[/color]",
            className: "col7-2"
        }, {
            name: "#0066FF",
            openWith: "[color=#0066FF]",
            closeWith: "[/color]",
            className: "col7-3"
        }, {
            name: "#0099FF",
            openWith: "[color=#0099FF]",
            closeWith: "[/color]",
            className: "col7-4"
        }, {
            name: "#00CCFF",
            openWith: "[color=#00CCFF]",
            closeWith: "[/color]",
            className: "col7-5"
        }, {
            name: "#00FFFF",
            openWith: "[color=#00FFFF]",
            closeWith: "[/color]",
            className: "col7-6"
        }, {
            name: "#99FFFF",
            openWith: "[color=#99FFFF]",
            closeWith: "[/color]",
            className: "col7-7"
        }, {
            name: "#99CCFF",
            openWith: "[color=#99CCFF]",
            closeWith: "[/color]",
            className: "col7-8"
        }, {
            name: "#9999FF",
            openWith: "[color=#9999FF]",
            closeWith: "[/color]",
            className: "col7-9"
        }, {
            name: "#9966FF",
            openWith: "[color=#9966FF]",
            closeWith: "[/color]",
            className: "col7-10"
        }, {
            name: "#9933FF",
            openWith: "[color=#9933FF]",
            closeWith: "[/color]",
            className: "col7-11"
        }, {
            name: "#9900FF",
            openWith: "[color=#9900FF]",
            closeWith: "[/color]",
            className: "col7-12"
        }, {
            name: "#CC00FF",
            openWith: "[color=#CC00FF]",
            closeWith: "[/color]",
            className: "col7-13"
        }, {
            name: "#CC33FF",
            openWith: "[color=#CC33FF]",
            closeWith: "[/color]",
            className: "col7-14"
        }, {
            name: "#CC66FF",
            openWith: "[color=#CC66FF]",
            closeWith: "[/color]",
            className: "col7-15"
        }, {
            name: "#CC99FF",
            openWith: "[color=#CC99FF]",
            closeWith: "[/color]",
            className: "col7-16"
        }, {
            name: "#CCCCFF",
            openWith: "[color=#CCCCFF]",
            closeWith: "[/color]",
            className: "col7-17"
        }, {
            name: "#CCFFFF",
            openWith: "[color=#CCFFFF]",
            closeWith: "[/color]",
            className: "col7-18"
        }, {
            name: "#0000CC",
            openWith: "[color=#0000CC]",
            closeWith: "[/color]",
            className: "col8-1"
        }, {
            name: "#0033CC",
            openWith: "[color=#0033CC]",
            closeWith: "[/color]",
            className: "col8-2"
        }, {
            name: "#0066CC",
            openWith: "[color=#0066CC]",
            closeWith: "[/color]",
            className: "col8-3"
        }, {
            name: "#0099CC",
            openWith: "[color=#0099CC]",
            closeWith: "[/color]",
            className: "col8-4"
        }, {
            name: "#00CCCC",
            openWith: "[color=#00CCCC]",
            closeWith: "[/color]",
            className: "col8-5"
        }, {
            name: "#00FFCC",
            openWith: "[color=#00FFCC]",
            closeWith: "[/color]",
            className: "col8-6"
        }, {
            name: "#99FFCC",
            openWith: "[color=#99FFCC]",
            closeWith: "[/color]",
            className: "col8-7"
        }, {
            name: "#99CCCC",
            openWith: "[color=#99CCCC]",
            closeWith: "[/color]",
            className: "col8-8"
        }, {
            name: "#9999CC",
            openWith: "[color=#9999CC]",
            closeWith: "[/color]",
            className: "col8-9"
        }, {
            name: "#9966CC",
            openWith: "[color=#9966CC]",
            closeWith: "[/color]",
            className: "col8-10"
        }, {
            name: "#9933CC",
            openWith: "[color=#9933CC]",
            closeWith: "[/color]",
            className: "col8-11"
        }, {
            name: "#9900CC",
            openWith: "[color=#9900CC]",
            closeWith: "[/color]",
            className: "col8-12"
        }, {
            name: "#CC00CC",
            openWith: "[color=#CC00CC]",
            closeWith: "[/color]",
            className: "col8-13"
        }, {
            name: "#CC33CC",
            openWith: "[color=#CC33CC]",
            closeWith: "[/color]",
            className: "col8-14"
        }, {
            name: "#CC66CC",
            openWith: "[color=#CC66CC]",
            closeWith: "[/color]",
            className: "col8-15"
        }, {
            name: "#CC99CC",
            openWith: "[color=#CC99CC]",
            closeWith: "[/color]",
            className: "col8-16"
        }, {
            name: "#CCCCCC",
            openWith: "[color=#CCCCCC]",
            closeWith: "[/color]",
            className: "col8-17"
        }, {
            name: "#CCFFCC",
            openWith: "[color=#CCFFCC]",
            closeWith: "[/color]",
            className: "col8-18"
        }, {
            name: "#000099",
            openWith: "[color=#000099]",
            closeWith: "[/color]",
            className: "col9-1"
        }, {
            name: "#003399",
            openWith: "[color=#003399]",
            closeWith: "[/color]",
            className: "col9-2"
        }, {
            name: "#006699",
            openWith: "[color=#006699]",
            closeWith: "[/color]",
            className: "col9-3"
        }, {
            name: "#009999",
            openWith: "[color=#009999]",
            closeWith: "[/color]",
            className: "col9-4"
        }, {
            name: "#00CC99",
            openWith: "[color=#00CC99]",
            closeWith: "[/color]",
            className: "col9-5"
        }, {
            name: "#00FF99",
            openWith: "[color=#00FF99]",
            closeWith: "[/color]",
            className: "col9-6"
        }, {
            name: "#99FF99",
            openWith: "[color=#99FF99]",
            closeWith: "[/color]",
            className: "col9-7"
        }, {
            name: "#99CC99",
            openWith: "[color=#99CC99]",
            closeWith: "[/color]",
            className: "col9-8"
        }, {
            name: "#999999",
            openWith: "[color=#999999]",
            closeWith: "[/color]",
            className: "col9-9"
        }, {
            name: "#996699",
            openWith: "[color=#996699]",
            closeWith: "[/color]",
            className: "col9-10"
        }, {
            name: "#993399",
            openWith: "[color=#993399]",
            closeWith: "[/color]",
            className: "col9-11"
        }, {
            name: "#990099",
            openWith: "[color=#990099]",
            closeWith: "[/color]",
            className: "col9-12"
        }, {
            name: "#CC0099",
            openWith: "[color=#CC0099]",
            closeWith: "[/color]",
            className: "col9-13"
        }, {
            name: "#CC3399",
            openWith: "[color=#CC3399]",
            closeWith: "[/color]",
            className: "col9-14"
        }, {
            name: "#CC6699",
            openWith: "[color=#CC6699]",
            closeWith: "[/color]",
            className: "col9-15"
        }, {
            name: "#CC9999",
            openWith: "[color=#CC9999]",
            closeWith: "[/color]",
            className: "col9-16"
        }, {
            name: "#CCCC99",
            openWith: "[color=#CCCC99]",
            closeWith: "[/color]",
            className: "col9-17"
        }, {
            name: "#CCFF99",
            openWith: "[color=#CCFF99]",
            closeWith: "[/color]",
            className: "col9-18"
        }, {
            name: "#000066",
            openWith: "[color=#000066]",
            closeWith: "[/color]",
            className: "col10-1"
        }, {
            name: "#003366",
            openWith: "[color=#003366]",
            closeWith: "[/color]",
            className: "col10-2"
        }, {
            name: "#006666",
            openWith: "[color=#006666]",
            closeWith: "[/color]",
            className: "col10-3"
        }, {
            name: "#009966",
            openWith: "[color=#009966]",
            closeWith: "[/color]",
            className: "col10-4"
        }, {
            name: "#00CC66",
            openWith: "[color=#00CC66]",
            closeWith: "[/color]",
            className: "col10-5"
        }, {
            name: "#00FF66",
            openWith: "[color=#00FF66]",
            closeWith: "[/color]",
            className: "col10-6"
        }, {
            name: "#99FF66",
            openWith: "[color=#99FF66]",
            closeWith: "[/color]",
            className: "col10-7"
        }, {
            name: "#99CC66",
            openWith: "[color=#99CC66]",
            closeWith: "[/color]",
            className: "col10-8"
        }, {
            name: "#999966",
            openWith: "[color=#999966]",
            closeWith: "[/color]",
            className: "col10-9"
        }, {
            name: "#996666",
            openWith: "[color=#996666]",
            closeWith: "[/color]",
            className: "col10-10"
        }, {
            name: "#993366",
            openWith: "[color=#993366]",
            closeWith: "[/color]",
            className: "col10-11"
        }, {
            name: "#990066",
            openWith: "[color=#990066]",
            closeWith: "[/color]",
            className: "col10-12"
        }, {
            name: "#CC0066",
            openWith: "[color=#CC0066]",
            closeWith: "[/color]",
            className: "col10-13"
        }, {
            name: "#CC3366",
            openWith: "[color=#CC336]",
            closeWith: "[/color]",
            className: "col10-14"
        }, {
            name: "#CC6666",
            openWith: "[color=#CC6666]",
            closeWith: "[/color]",
            className: "col10-15"
        }, {
            name: "#CC9966",
            openWith: "[color=#CC9966]",
            closeWith: "[/color]",
            className: "col10-16"
        }, {
            name: "#CCCC66",
            openWith: "[color=#CCCC66]",
            closeWith: "[/color]",
            className: "col10-17"
        }, {
            name: "#CCFF66",
            openWith: "[color=#CCFF66]",
            closeWith: "[/color]",
            className: "col10-18"
        }, {
            name: "#000033",
            openWith: "[color=#000033]",
            closeWith: "[/color]",
            className: "col11-1"
        }, {
            name: "#003333",
            openWith: "[color=#003333]",
            closeWith: "[/color]",
            className: "col11-2"
        }, {
            name: "#006633",
            openWith: "[color=#006633]",
            closeWith: "[/color]",
            className: "col11-3"
        }, {
            name: "#009933",
            openWith: "[color=#009933]",
            closeWith: "[/color]",
            className: "col11-4"
        }, {
            name: "#00CC33",
            openWith: "[color=#00CC33]",
            closeWith: "[/color]",
            className: "col11-5"
        }, {
            name: "#00FF33",
            openWith: "[color=#00FF33]",
            closeWith: "[/color]",
            className: "col11-6"
        }, {
            name: "#99FF33",
            openWith: "[color=#99FF33]",
            closeWith: "[/color]",
            className: "col11-7"
        }, {
            name: "#99CC33",
            openWith: "[color=#99CC33]",
            closeWith: "[/color]",
            className: "col11-8"
        }, {
            name: "#999933",
            openWith: "[color=#999933]",
            closeWith: "[/color]",
            className: "col11-9"
        }, {
            name: "#996633",
            openWith: "[color=#996633]",
            closeWith: "[/color]",
            className: "col11-10"
        }, {
            name: "#993333",
            openWith: "[color=#993333]",
            closeWith: "[/color]",
            className: "col11-11"
        }, {
            name: "#990033",
            openWith: "[color=#990033]",
            closeWith: "[/color]",
            className: "col11-12"
        }, {
            name: "#CC0033",
            openWith: "[color=#CC0033]",
            closeWith: "[/color]",
            className: "col11-13"
        }, {
            name: "#CC3333",
            openWith: "[color=#CC3333]",
            closeWith: "[/color]",
            className: "col11-14"
        }, {
            name: "#CC6633",
            openWith: "[color=#CC6633]",
            closeWith: "[/color]",
            className: "col11-15"
        }, {
            name: "#CC9933",
            openWith: "[color=#CC9933]",
            closeWith: "[/color]",
            className: "col11-16"
        }, {
            name: "#CCCC33",
            openWith: "[color=#CCCC33]",
            closeWith: "[/color]",
            className: "col11-17"
        }, {
            name: "#CCFF33",
            openWith: "[color=#CCFF33]",
            closeWith: "[/color]",
            className: "col11-18"
        }, {
            name: "#000000",
            openWith: "[color=#000000]",
            closeWith: "[/color]",
            className: "col12-1"
        }, {
            name: "#003300",
            openWith: "[color=#003300]",
            closeWith: "[/color]",
            className: "col12-2"
        }, {
            name: "#006600",
            openWith: "[color=#006600]",
            closeWith: "[/color]",
            className: "col12-3"
        }, {
            name: "#009900",
            openWith: "[color=#009900]",
            closeWith: "[/color]",
            className: "col12-4"
        }, {
            name: "#00CC00",
            openWith: "[color=#00CC00]",
            closeWith: "[/color]",
            className: "col12-5"
        }, {
            name: "#00FF00",
            openWith: "[color=#00FF00]",
            closeWith: "[/color]",
            className: "col12-6"
        }, {
            name: "#99FF00",
            openWith: "[color=#99FF00]",
            closeWith: "[/color]",
            className: "col12-7"
        }, {
            name: "#99CC00",
            openWith: "[color=#99CC00]",
            closeWith: "[/color]",
            className: "col12-8"
        }, {
            name: "#999900",
            openWith: "[color=#999900]",
            closeWith: "[/color]",
            className: "col12-9"
        }, {
            name: "#996600",
            openWith: "[color=#996600]",
            closeWith: "[/color]",
            className: "col12-10"
        }, {
            name: "#993300",
            openWith: "[color=#993300]",
            closeWith: "[/color]",
            className: "col12-11"
        }, {
            name: "#990000",
            openWith: "[color=#990000]",
            closeWith: "[/color]",
            className: "col12-12"
        }, {
            name: "#CC0000",
            openWith: "[color=#CC0000]",
            closeWith: "[/color]",
            className: "col12-13"
        }, {
            name: "#CC3300",
            openWith: "[color=#CC3300]",
            closeWith: "[/color]",
            className: "col12-14"
        }, {
            name: "#CC6600",
            openWith: "[color=#CC6600]",
            closeWith: "[/color]",
            className: "col12-15"
        }, {
            name: "#CC9900",
            openWith: "[color=#CC9900]",
            closeWith: "[/color]",
            className: "col12-16"
        }, {
            name: "#CCCC00",
            openWith: "[color=#CCCC00]",
            closeWith: "[/color]",
            className: "col12-17"
        }, {
            name: "#CCFF00",
            openWith: "[color=#CCFF00]",
            closeWith: "[/color]",
            className: "col12-18"
        }, {
            name: "#000000",
            openWith: "[color=#000000]",
            closeWith: "[/color]",
            className: "col13-1"
        }, {
            name: "#000000",
            openWith: "[color=#000000]",
            closeWith: "[/color]",
            className: "col13-1"
        }, {
            name: "#000000",
            openWith: "[color=#000000]",
            closeWith: "[/color]",
            className: "col13-1"
        }, {
            name: "#111111",
            openWith: "[color=#111111]",
            closeWith: "[/color]",
            className: "col13-2"
        }, {
            name: "#222222",
            openWith: "[color=#222222]",
            closeWith: "[/color]",
            className: "col13-3"
        }, {
            name: "#333333",
            openWith: "[color=#333333]",
            closeWith: "[/color]",
            className: "col13-4"
        }, {
            name: "#444444",
            openWith: "[color=#444444]",
            closeWith: "[/color]",
            className: "col13-5"
        }, {
            name: "#555555",
            openWith: "[color=#555555]",
            closeWith: "[/color]",
            className: "col13-6"
        }, {
            name: "#666666",
            openWith: "[color=#666666]",
            closeWith: "[/color]",
            className: "col13-7"
        }, {
            name: "#777777",
            openWith: "[color=#777777]",
            closeWith: "[/color]",
            className: "col13-8"
        }, {
            name: "#888888",
            openWith: "[color=#888888]",
            closeWith: "[/color]",
            className: "col13-9"
        }, {
            name: "#999999",
            openWith: "[color=#999999]",
            closeWith: "[/color]",
            className: "col13-10"
        }, {
            name: "#AAAAAA",
            openWith: "[color=#AAAAAA]",
            closeWith: "[/color]",
            className: "col13-11"
        }, {
            name: "#BBBBBB",
            openWith: "[color=#BBBBBB]",
            closeWith: "[/color]",
            className: "col13-12"
        }, {
            name: "#CCCCCC",
            openWith: "[color=#CCCCCC]",
            closeWith: "[/color]",
            className: "col13-13"
        }, {
            name: "#DDDDDD",
            openWith: "[color=#DDDDDD]",
            closeWith: "[/color]",
            className: "col13-14"
        }, {
            name: "#EEEEEE",
            openWith: "[color=#EEEEEE]",
            closeWith: "[/color]",
            className: "col13-15"
        }, {
            name: "#FFFFFF",
            openWith: "[color=#FFFFFF]",
            closeWith: "[/color]",
            className: "col13-16"
        } ]
    }, {
        name: "Size",
        key: "S",
        openWith: "[size=[![Text size]!]]",
        closeWith: "[/size]",
        className: "sizebutton",
        dropMenu: [ {
            name: "xx-large",
            showName: "xx-large",
            openWith: "[size=7]",
            closeWith: "[/size]",
            className: "size_7"
        }, {
            name: "x-large",
            showName: "x-large",
            openWith: "[size=6]",
            closeWith: "[/size]",
            className: "size_6"
        }, {
            name: "large",
            showName: "large",
            openWith: "[size=5]",
            closeWith: "[/size]",
            className: "size_5"
        }, {
            name: "medium",
            showName: "medium",
            openWith: "[size=4]",
            closeWith: "[/size]",
            className: "size_4"
        }, {
            name: "small",
            showName: "small",
            openWith: "[size=3]",
            closeWith: "[/size]",
            className: "size_3"
        }, {
            name: "x-small",
            showName: "x-small",
            openWith: "[size=2]",
            closeWith: "[/size]",
            className: "size_2"
        }, {
            name: "xx-small",
            showName: "xx-small",
            openWith: "[size=1]",
            closeWith: "[/size]",
            className: "size_1"
        } ]
    }, {
        separator: " "
    }, {
        name: "Unordered list",
        openWith: "[list]\n",
        closeWith: "[/list]",
        className: "list_bullet"
    }, {
        name: "Ordered list",
        openWith: "[list=[![Starting number]!]]\n",
        closeWith: "\n[/list]",
        className: "list_numeric"
    }, {
        name: "List item",
        openWith: "[*] ",
        className: "list_item"
    }, {
        separator: " "
    }, {
        name: "Align Left",
        openWith: "[left]",
        closeWith: "[/left]",
        className: "align-left"
    }, {
        name: "Align Center",
        openWith: "[center]",
        closeWith: "[/center]",
        className: "align-center"
    }, {
        name: "Align Right",
        openWith: "[right]",
        closeWith: "[/right]",
        className: "align-right"
    }, {
        name: "Justify",
        openWith: "[justify]",
        closeWith: "[/justify]",
        className: "align-justify"
    }, {
        separator: " "
    }, {
        name: "Blockquote",
        openWith: "[blockquote]",
        closeWith: "[/blockquote]",
        className: "blockquotebutton"
    }, {
        name: "Quotes",
        key: "Q",
        openWith: "[quote]",
        closeWith: "[/quote]",
        className: "quotebutton"
    }, {
        name: "Code",
        key: "K",
        openWith: "[code]",
        closeWith: "[/code]",
        className: "codebutton"
    }, {
        name: "Marquee",
        openWith: "[marquee]",
        closeWith: "[/marquee]",
        className: "marqueebutton"
    }, {
        name: "Spoiler",
        openWith: "[spoiler]",
        closeWith: "[/spoiler]",
        className: "spoilerbutton"
    }, {
        separator: " "
    }, {
        name: "Table generator",
        className: "tablegenerator",
        placeholder: "Your text here...",
        replaceWith: function(h) {
            var cols = prompt("How many cols?"), rows = prompt("How many rows?"), thead = prompt("Is first row a table header? (yes or no)"), html = "[table]\n";
            if (thead == "yes") {
                for (var c = 0; c < cols; c++) {
                    html += "\t[th] [![TH" + (c + 1) + " text:]!][/th]\n";
                }
            }
            for (var r = 0; r < rows; r++) {
                html += "\t[tr]\n";
                for (var c = 0; c < cols; c++) {
                    html += "\t\t[td]" + (h.placeholder || "") + "[/td]\n";
                }
                html += "\t[/tr]\n";
            }
            html += "[/table]";
            return html;
        }
    }, {
        separator: " "
    }, {
        name: "Remove Formatting from Selected Text",
        className: "clean",
        replaceWith: function(h) {
            return h.selection.replace(/\[(.*?)\]/g, "");
        }
    }, {
        name: "Preview",
        key: "!",
        className: "preview",
        call: "preview"
    } ]
};

$(document).ready(function() {
    $("#box_1").hide();
    $("#box_2").hide();
    $("#box_3").hide();
    $("#box_4").hide();
    $("#box_1").fadeIn("slow");
    $("a#smilies").click(function() {
        $("#box_1").show("slow");
        $("#box_2").hide();
        $("#box_3").hide();
        $("#box_4").hide();
    });
    $("a#custom").click(function() {
        $("#box_1").hide();
        $("#box_2").show("slow");
        $("#box_3").hide();
        $("#box_4").hide();
    });
    $("a#staff").click(function() {
        $("#box_1").hide();
        $("#box_2").hide();
        $("#box_3").show("slow");
        $("#box_4").hide();
    });
    if ($("#bbcode_editor").length) {
        $("#bbcode_editor").markItUp(myBbcodeSettings);
    }
    $(".emoticons a").click(function() {
        emoticon = $(this).attr("title");
        $.markItUp({
            openWith: emoticon
        });
        return false;
    });
    $("#tool_open").click(function() {
        $("#tools").slideToggle("slow", function() {});
        $("#tool_open").hide();
        $("#tool_close").show();
    });
    $("#tool_close").click(function() {
        $("#tools").slideToggle("slow", function() {});
        $("#tool_close").hide();
        $("#tool_open").show();
    });
    $("#more").click(function() {
        $("#attach_more").slideToggle("slow", function() {});
    });
});