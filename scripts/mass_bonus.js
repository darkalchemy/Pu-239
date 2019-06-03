$(document).ready(function () {
    $(".select_me").hide();
    $("#bonus_options_1").change(function () {
        $(".select_me").hide();
        $("#div_" + $(this).val()).show();
        let text = $(this).val();
        $("#action_2").val(text);
    });

    $("#all_or_selected_classes").click(function () {
        $("#classes_open").slideToggle("slow", function () {
        });
    });
});
