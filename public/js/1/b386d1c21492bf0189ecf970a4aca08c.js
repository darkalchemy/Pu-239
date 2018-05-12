(function($) {
    $.fn.simpleCaptcha = function(o) {
        var n = this;
        if (n.length < 1) {
            return n;
        }
        o = o ? o : {};
        o = auditOptions($.extend({}, $.fn.simpleCaptcha.defaults, o));
        var inputId = "simpleCaptcha_" + $.fn.simpleCaptcha.uid++;
        n.addClass("simpleCaptcha").html("").append("<div class='" + o.introClass + "'>" + o.introText + "</div>" + "<div class='" + o.imageBoxClass + " " + o.imageBoxClassExtra + "'></div>" + "<input class='simpleCaptchaInput' id='" + inputId + "' name='" + o.inputName + "' type='hidden' value='' />");
        $.ajax({
            url: o.scriptPath,
            data: {
                numImages: o.numImages
            },
            method: "post",
            dataType: "json",
            success: function(data, status) {
                if (typeof data.error == "string") {
                    handleError(n, data.error);
                    return;
                } else {
                    n.find("." + o.textClass).html(data.text);
                    var imgBox = n.find("." + o.imageBoxClass);
                    $.each(data.images, function() {
                        imgBox.append("<img class='" + o.imageClass + "' src='" + this.file + "' alt='' data-title='" + this.hash + "' />");
                    });
                    imgBox.find("img." + o.imageClass).click(function(e) {
                        n.find("img." + o.imageClass).removeClass("simpleCaptchaSelected");
                        var hash = $(this).addClass("simpleCaptchaSelected").attr("data-title");
                        $("#" + inputId).val(hash);
                        n.trigger("select.simpleCaptcha", [ hash ]);
                        return false;
                    }).keyup(function(e) {
                        if (e.keyCode == 13 || e.which == 13) {
                            $(this).click();
                        }
                    });
                    n.trigger("loaded.simpleCaptcha", [ data ]);
                }
            },
            error: function(xhr, status) {
                handleError(n, "There was a serious problem: " + xhr.status);
            }
        });
        return n;
    };
    var handleError = function(n, msg) {
        n.trigger("error.simpleCaptcha", [ msg ]);
    };
    var auditOptions = function(o) {
        if (typeof o.numImages != "number" || o.numImages < 1) {
            o.numImages = $.fn.simpleCaptcha.defaults.numImages;
        }
        if (typeof o.introText != "string" || o.introText.length < 1) {
            o.introText = $.fn.simpleCaptcha.defaults.introText;
        }
        if (typeof o.inputName != "string") {
            o.inputName = $.fn.simpleCaptcha.defaults.inputName;
        }
        if (typeof o.scriptPath != "string") {
            o.scriptPath = $.fn.simpleCaptcha.defaults.scriptPath;
        }
        if (typeof o.introClass != "string") {
            o.introClass = $.fn.simpleCaptcha.defaults.introClass;
        }
        if (typeof o.textClass != "string") {
            o.textClass = $.fn.simpleCaptcha.defaults.textClass;
        }
        if (typeof o.imageBoxClass != "string") {
            o.imageBoxClass = $.fn.simpleCaptcha.defaults.imageBoxClass;
        }
        if (typeof o.imageClass != "string") {
            o.imageClass = $.fn.simpleCaptcha.defaults.imageClass;
        }
        return o;
    };
    $.fn.simpleCaptcha.uid = 0;
    $.fn.simpleCaptcha.defaults = {
        numImages: 6,
        introText: "<p align='center'>To make sure you are a human, we need you to click on the <span class='captchaText'></span>.</p>",
        inputName: "captchaSelection",
        scriptPath: "simpleCaptcha.php",
        introClass: "captchaIntro bottom10",
        textClass: "captchaText",
        imageBoxClass: "tabs",
        imageBoxClassExtra: "is-marginless",
        imageClass: "captchaImage"
    };
})(jQuery);

$(document).ready(function() {
    if ($("#captcha_show").length) {
        $("#captcha_show").simpleCaptcha();
    }
});

function checkit() {
    wantusername = document.getElementById("wantusername").value;
    var url = "../ajax/namecheck.php?wantusername=" + encodeURI(wantusername);
    try {
        request = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            request = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e2) {
            request = false;
        }
    }
    if (!request && typeof XMLHttpRequest != "undefined") {
        request = new XMLHttpRequest();
    }
    request.open("GET", url, true);
    global_content = wantusername;
    request.onreadystatechange = check;
    request.send(null);
}

function check() {
    if (request.readyState == 4) {
        if (request.status == 200) {
            var response = request.responseText;
            document.getElementById("namecheck").innerHTML = response;
            if (response.substring(0, 20) == "<font color='#cc0000'>") {
                document.reform.submitt.disabled = true;
            } else if (response.substring(0, 20) == "<font color='#33cc33'>") {
                document.reform.submitt.disabled = false;
            }
        }
    }
}

(function($) {
    var numbers_array = new Array(), upper_letters_array = new Array(), lower_letters_array = new Array(), special_chars_array = new Array(), pStrengthElementsDefaultStyle = new Array(), settings, methods = {
        init: function(options, callbacks) {
            settings = $.extend({
                bind: "keyup change",
                changeBackground: true,
                backgrounds: [ [ "#cc0000", "#000" ], [ "#cc3333", "#000" ], [ "#cc6666", "#000" ], [ "#ff9999", "#000" ], [ "#e0941c", "#000" ], [ "#e8a53a", "#000" ], [ "#eab259", "#000" ], [ "#efd09e", "#000" ], [ "#ccffcc", "#000" ], [ "#66cc66", "#000" ], [ "#339933", "#000" ], [ "#006600", "#000" ], [ "#105610", "#000" ] ],
                passwordValidFrom: 60,
                onValidatePassword: function(percentage) {},
                onPasswordStrengthChanged: function(passwordStrength, percentage) {}
            }, options);
            for (var i = 48; i < 58; i++) numbers_array.push(i);
            for (i = 65; i < 91; i++) upper_letters_array.push(i);
            for (i = 97; i < 123; i++) lower_letters_array.push(i);
            for (i = 32; i < 48; i++) special_chars_array.push(i);
            for (i = 58; i < 65; i++) special_chars_array.push(i);
            for (i = 91; i < 97; i++) special_chars_array.push(i);
            for (i = 123; i < 127; i++) special_chars_array.push(i);
            return this.each($.proxy(function(idx, pStrengthElement) {
                pStrengthElementsDefaultStyle[$(pStrengthElement)] = {
                    background: $(pStrengthElement).css("background"),
                    color: $(pStrengthElement).css("color")
                };
                calculatePasswordStrength.call(pStrengthElement);
                $(pStrengthElement).bind(settings.bind, function() {
                    calculatePasswordStrength.call(pStrengthElement);
                });
            }, this));
        },
        changeBackground: function(pStrengthElement, passwordStrength) {
            if (passwordStrength === undefined) {
                passwordStrength = pStrengthElement;
                pStrengthElement = $(this);
            }
            passwordStrength = passwordStrength > 12 ? 12 : passwordStrength;
            $(pStrengthElement).css({
                "background-color": settings.backgrounds[passwordStrength][0],
                color: settings.backgrounds[passwordStrength][1]
            });
        },
        resetStyle: function(pStrengthElement) {
            $(pStrengthElement).css(pStrengthElementsDefaultStyle[$(pStrengthElement)]);
        }
    };
    var ord = function(string) {
        var str = string + "", code = str.charCodeAt(0);
        if (55296 <= code && code <= 56319) {
            var hi = code;
            if (str.length === 1) {
                return code;
            }
            var low = str.charCodeAt(1);
            return (hi - 55296) * 1024 + (low - 56320) + 65536;
        }
        if (56320 <= code && code <= 57343) {
            return code;
        }
        return code;
    };
    var calculatePasswordStrength = function() {
        var passwordStrength = 0, numbers_found = 0, upper_letters_found = 0, lower_letters_found = 0, special_chars_found = 0, text = $(this).val().trim();
        passwordStrength += 2 * Math.floor(text.length / 8);
        for (var i = 0; i < text.length; i++) {
            if ($.inArray(ord(text.charAt(i)), numbers_array) != -1 && numbers_found < 2) {
                passwordStrength++;
                numbers_found++;
                continue;
            }
            if ($.inArray(ord(text.charAt(i)), upper_letters_array) != -1 && upper_letters_found < 2) {
                passwordStrength++;
                upper_letters_found++;
                continue;
            }
            if ($.inArray(ord(text.charAt(i)), lower_letters_array) != -1 && lower_letters_found < 2) {
                passwordStrength++;
                lower_letters_found++;
                continue;
            }
            if ($.inArray(ord(text.charAt(i)), special_chars_array) != -1 && special_chars_found < 2) {
                passwordStrength++;
                special_chars_found++;
                continue;
            }
        }
        behaviour.call($(this), passwordStrength);
        return passwordStrength;
    };
    var behaviour = function(passwordStrength) {
        var strengthPercentage = Math.ceil(passwordStrength * 100 / 12);
        strengthPercentage = strengthPercentage > 100 ? 100 : strengthPercentage;
        settings.onPasswordStrengthChanged.call($(this), passwordStrength, strengthPercentage);
        if (strengthPercentage >= settings.passwordValidFrom) {
            settings.onValidatePassword.call($(this), strengthPercentage);
        }
        if (settings.changeBackground) {
            methods.changeBackground.call($(this), passwordStrength);
        }
    };
    $.fn.pStrength = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === "object" || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error("Method " + method + " does not exists on jQuery.pStrength");
        }
    };
})(jQuery);

$(function() {
    $("#myElement1, #myElement2").pStrength({
        changeBackground: true,
        onPasswordStrengthChanged: function(passwordStrength, strengthPercentage) {
            if ($(this).val()) {
                $.fn.pStrength("changeBackground", $(this), passwordStrength);
            } else {
                $.fn.pStrength("resetStyle", $(this));
            }
            $("#" + $(this).data("display")).html("Your password strength is " + strengthPercentage + "%");
        },
        onValidatePassword: function(strengthPercentage) {
            if ($(this).data("display") == "myDisplayElement2") {
                var password = document.getElementById("myElement1").value;
                var confirmPassword = document.getElementById("myElement2").value;
                if (password != confirmPassword) {
                    $("#" + $(this).data("display")).html($("#" + $(this).data("display")).html() + "<br>Passwords do not match!");
                } else {
                    $("#" + $(this).data("display")).html($("#" + $(this).data("display")).html() + "<br>Great, now you can continue to register!");
                }
            }
        }
    });
});