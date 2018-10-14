<?php

function verify_recaptcha($response, $timeout = 30)
{
    if ($response === '') {
        return false;
    }
    $ip = getip(true);
    $recaptcha = new \ReCaptcha\ReCaptcha($_ENV['RECAPTCHA_SECRET_KEY'], new \ReCaptcha\RequestMethod\CurlPost());
    $resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
        ->setChallengeTimeout($timeout)
        ->setExpectedAction('login')
        ->verify($response, $ip);

    $results = $resp->toArray();

    if (!$results['success'] || $results['action'] !== 'login') {
        $errors = $resp->getErrorCodes();
        write_log('reCAPTCHA Failed: ' . json_encode($errors));

        return $errors[0];
    }

    if ($results['score'] < 0.5 && TIME_NOW - strtotime($results['challenge_ts']) > 15) {
        write_log('reCAPTCHA Failed: Score = ' . json_encode($results['score']));

        return "Failed Score: {$results['score']}";
    }

    write_log('reCAPTCHA Success: ' . json_encode($results));

    return 'valid';
}
