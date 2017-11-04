function ShowHideMainCats(tableCount) {
    var MainCats = document.getElementById("cats");
    var MainCatsPic = document.getElementById("pic");
    var DefCats = document.getElementById("defcats");
    if (MainCats.style.display == "none") {
        MainCats.style.display = "block";
        DefCats.style.display = "block";
        MainCatsPic.src = "./images/minus.png";
    } else {
        MainCats.style.display = "none";
        DefCats.style.display = "none";
        MainCatsPic.src = "./images/plus.png";
    }
    for (i = 1; i <= tableCount; i++) {
        tableID = "tabletype" + i;
        tabletype = document.getElementById(tableID);
        picID = "pic" + i;
        picture = document.getElementById(picID);
        tabletype.style.display = "none";
        picture.src = "./images/plus.png";
    }
}

function ShowHideMainSubCats(tableNum, tableCount) {
    if (tableCount > 1) for (i = 1; i <= tableCount; i++) {
        tableID = "tabletype" + i;
        tabletype = document.getElementById(tableID);
        picID = "pic" + i;
        picture = document.getElementById(picID);
        if (i == tableNum) {
            if (tabletype.style.display == "none") {
                tabletype.style.display = "block";
                picture.src = "./images/minus.png";
            } else {
                tabletype.style.display = "none";
                picture.src = "./images/plus.png";
            }
        } else {
            tabletype.style.display = "none";
            picture.src = "./images/plus.png";
        }
    }
}

function checkAllFields(ref, tabletype) {
    checkAllID = "checkAll" + tabletype;
    var chkAll = document.getElementById(checkAllID);
    CatsID = "cats" + tabletype + "[]";
    var checks = document.getElementsByName(CatsID);
    var boxLength = checks.length;
    var allChecked = false;
    var totalChecked = 0;
    if (ref == 1) {
        if (chkAll.checked == true) {
            for (i = 0; i < boxLength; i++) {
                checks[i].checked = true;
            }
        } else {
            for (i = 0; i < boxLength; i++) {
                checks[i].checked = false;
            }
        }
    } else {
        for (i = 0; i < boxLength; i++) {
            if (checks[i].checked == true) {
                allChecked = true;
                continue;
            } else {
                allChecked = false;
                break;
            }
        }
        if (allChecked == true) {
            chkAll.checked = true;
        } else {
            chkAll.checked = false;
        }
    }
    for (j = 0; j < boxLength; j++) {
        if (checks[j].checked == true) {
            totalChecked++;
        }
    }
}

function CheckAll(fmobj) {
    for (var i = 0; i < fmobj.elements.length; i++) {
        var e = fmobj.elements[i];
        if (e.name != "allbox" && e.type == "checkbox" && !e.disabled) {
            e.checked = fmobj.allbox.checked;
        }
    }
}

function CheckCheckAll(fmobj) {
    var TotalBoxes = 0;
    var TotalOn = 0;
    for (var i = 0; i < fmobj.elements.length; i++) {
        var e = fmobj.elements[i];
        if (e.name != "allbox" && e.type == "checkbox") {
            TotalBoxes++;
            if (e.checked) {
                TotalOn++;
            }
        }
    }
    if (TotalBoxes == TotalOn) {
        fmobj.allbox.checked = true;
    } else {
        fmobj.allbox.checked = false;
    }
}

function hide() {
    if (document.layers) {
        document.appgame.visibility = "hidden";
        document.music.visibility = "hidden";
        document.other.visibility = "hidden";
        document.movie.visibility = "hidden";
    }
    if (document.all) {
        document.all.appgame.style.visibility = "hidden";
        document.all.music.style.visibility = "hidden";
        document.all.other.style.visibility = "hidden";
        document.all.movie.style.visibility = "hidden";
    }
    if (document.getElementById) {
        document.getElementById("Apps").style.visibility = "hidden";
        document.getElementById("Games").style.visibility = "hidden";
        document.getElementById("Movies").style.visibility = "hidden";
        document.getElementById("Music").style.visibility = "hidden";
    }
}

function whatbrowser() {
    if (document.layers) {
        thisbrowser = "NN4";
    }
    if (document.all) {
        thisbrowser = "ie";
    }
    if (!document.all && document.getElementById) {
        thisbrowser = "NN6";
    }
}

function show(z) {
    if (document.layers) {
        document[z].visibility = "visible";
    }
    if (document.all) {
        document.all[z].style.visibility = "visible";
    }
    if (document.getElementById) {
        document.getElementById([ z ]).style.visibility = "visible";
    }
}

function whatbrowser() {
    if (document.layers) {
        thisbrowser = "NN4";
    }
    if (document.all) {
        thisbrowser = "ie";
    }
    if (!document.all && document.getElementById) {
        thisbrowser = "NN6";
    }
}

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