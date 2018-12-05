grecaptcha.ready(function () {
    grecaptcha.execute(key, {action: 'login'}).then(function (token) {
        var tcheck = document.getElementById('token');
        var el = document.getElementById('login_captcha_check');
        if (el && token) {
            tcheck.value = token;
            el.value = 'Login';
        } else if (el) {
            el.value = 'reCAPTCHA failed to get a token.';
        }

        var el = document.getElementById('signup_captcha_check');
        if (el && token) {
            tcheck.value = token;
            el.value = 'Signup';
        } else if (el) {
            el.value = 'reCAPTCHA failed to get a token.';
        }

        var el = document.getElementById('recover_captcha_check');
        if (el && token) {
            el.disabled = false;
            tcheck.value = token;
            el.value = 'Recover';
        } else if (el) {
            el.value = 'reCAPTCHA failed to get a token.';
        }
    });
});

