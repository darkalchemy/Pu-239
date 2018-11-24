$(function () {
    $('#myElement1, #myElement2').pStrength({
        changeBackground: true,
        onPasswordStrengthChanged: function (passwordStrength, strengthPercentage) {
            if ($(this).val()) {
                $('.button').prop('disabled', true);
                $.fn.pStrength('changeBackground', $(this), passwordStrength);
            } else {
                $.fn.pStrength('resetStyle', $(this));
                $('.button').prop('disabled', false);
            }
            $('#' + $(this).data('display')).html('Your password strength is ' + strengthPercentage + '%.<br>Min strength = 60%');
        },
        onValidatePassword: function (strengthPercentage) {
            if ($(this).data('display') == 'myDisplayElement2') {
                var password = document.getElementById('myElement1').value;
                var confirmPassword = document.getElementById('myElement2').value;
                if (password != confirmPassword) {
                    $('#' + $(this).data('display')).html($('#' + $(this).data('display')).html() + '<br>Passwords do not match!');
                } else {
                    $('#' + $(this).data('display')).html($('#' + $(this).data('display')).html() + '<br>Great, now you can continue to register!');
                    $('.button').prop('disabled', false);
                }
            }
        }
    });
});
