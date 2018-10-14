<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
global $site_config;

header('content-type: application/json');

$response = !empty($_GET['token']) ? $_GET['token'] : '';
$action = !empty($_GET['action']) ? $_GET['action'] : '';
if (empty($response) || empty($action)) {
    $status = ['data' => 'Failed'];
    echo json_encode($status);
    die();
}

$ip = getip(true);
$recaptcha = new \ReCaptcha\ReCaptcha($_ENV['RECAPTCHA_SECRET_KEY'], new \ReCaptcha\RequestMethod\CurlPost());
$resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
    ->setChallengeTimeout(15)
    ->setExpectedAction($action)
    ->verify($response, $ip);

$results = $resp->toArray();
if (!$results['success']) {//} || $results['action'] !== $action || $results['score'] < 0.5 || TIME_NOW - 15 > strtotime($resp->getChallengeTs())) {
    $errors = $resp->getErrorCodes();
    write_log('reCAPTCHA Failed: ' . json_encode($errors));
    $status = ['data' => 'Failed'];
} else {
    $status = ['data' => 'Success'];
}

echo json_encode($status);
