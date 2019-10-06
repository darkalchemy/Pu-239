<?php

declare(strict_types = 1);

use Delight\I18n\I18n;
use Pu239\Session;

global $container, $site_config;

$i18n = $container->get(I18n::class);
$lang = get_language();
try {
    $i18n->setLocaleManually($lang);
} catch (Exception $e) {
    $session = $container->get(Session::class);
    $session->set('is-danger', _fe('{0} is not currently a supported locale.', $lang));
    $i18n->setLocaleManually('en_US');
}

/**
 * @param       $text
 * @param mixed ...$replacements
 *
 * @return string
 */
function _f($text, ...$replacements)
{
    global $i18n;

    return $i18n->translateFormatted($text, ...$replacements);
}

/**
 * @param       $text
 * @param mixed ...$replacements
 *
 * @return string
 */
function _fe($text, ...$replacements)
{
    global $i18n;

    return $i18n->translateFormattedExtended($text, ...$replacements);
}

/**
 * @param $text
 * @param $alternative
 * @param $count
 *
 * @return string
 */
function _p($text, $alternative, $count)
{
    global $i18n;

    return $i18n->translatePlural($text, $alternative, $count);
}

/**
 * @param       $text
 * @param       $alternative
 * @param       $count
 * @param mixed ...$replacements
 *
 * @return string
 */
function _pf($text, $alternative, $count, ...$replacements)
{
    global $i18n;

    return $i18n->translatePluralFormatted($text, $alternative, $count, ...$replacements);
}

/**
 * @param       $text
 * @param       $alternative
 * @param       $count
 * @param mixed ...$replacements
 *
 * @return string
 */
function _pfe($text, $alternative, $count, ...$replacements)
{
    global $i18n;

    return $i18n->translatePluralFormattedExtended($text, $alternative, $count, ...$replacements);
}

/**
 * @param $text
 * @param $context
 *
 * @return string
 */
function _c($text, $context)
{
    global $i18n;

    return $i18n->translateWithContext($text, $context);
}

/**
 * @param $text
 *
 * @return string
 */
function _m($text)
{
    global $i18n;

    return $i18n->markForTranslation($text);
}
