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
    $("#staff_tools_open").click(function() {
        $("#staff_tools").slideToggle("slow", function() {});
    });
    $("#toggle_voters").click(function() {
        $("#voters").slideToggle("slow", function() {});
    });
    $("#pm_open").click(function() {
        $("#pm").slideToggle("slow", function() {});
    });
});