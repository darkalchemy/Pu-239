<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['vendor', 'node_modules', 'imdb', '.github'])
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2'                              => true,
        'no_whitespace_in_blank_line'        => true,
        'phpdoc_align'                       => true,
        'phpdoc_indent'                      => true,
        'phpdoc_scalar'                      => true,
        'phpdoc_separation'                  => true,
        'short_scalar_cast'                  => true,
        'single_blank_line_before_namespace' => true,
        'standardize_not_equals'             => true,
        'ternary_operator_spaces'            => true,
        'whitespace_after_comma_in_array'    => true
    ])
    ->setFinder($finder)
;
