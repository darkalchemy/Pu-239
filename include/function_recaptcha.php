<?php

require_once INCL_DIR . 'staff_functions.php';
/**
 * @param     $response
 * @param int $timeout
 *
 * @return bool|string
 */
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
        write_info('reCAPTCHA Failed: ' . json_encode($errors) . ' : ' . $results['hostname'] . ' : ' . $_SERVER['HTTP_HOST']);

        return $errors[0];
    }

    if ($results['score'] < 0.5 && TIME_NOW - strtotime($results['challenge_ts']) > 15) {
        write_info('reCAPTCHA Failed: Score = ' . json_encode($results['score']) . ' : ' . $results['hostname'] . ' : ' . $_SERVER['HTTP_HOST']);

        return "Failed Score: {$results['score']}";
    }

    write_info('reCAPTCHA Success: ' . json_encode($results));

    return 'valid';
}
