<?php

$finder = PhpCsFixer\Finder::create()
	->exclude('Breeze/Pimple/')
	->in(__DIR__ . '/Sources/');

return PhpCsFixer\Config::create()
    ->setRules([
		'@PHP71Migration:risky' => true,
		'@PHPUnit60Migration:risky' => true,
		'array_indentation' => true,
		'array_syntax' => ['syntax' => 'short'],
		'blank_line_after_opening_tag' => true,
		'concat_space' => ['spacing' => 'one'],
		'class_attributes_separation' => ['elements' => ['method', 'property']],
		'declare_strict_types' => true,
		'increment_style' => ['style' => 'post'],
		'is_null' => ['use_yoda_style' => false],
		'list_syntax' => ['syntax' => 'short'],
    	'blank_line_after_namespace' => true,
        'blank_line_before_return' => true,
		'method_chaining_indentation' => true,
		'modernize_types_casting' => true,
		'no_multiline_whitespace_before_semicolons' => true,
		'no_superfluous_elseif' => true,
		'no_superfluous_phpdoc_tags' => true,
		'no_useless_else' => true,
		'no_useless_return' => true,
		'ordered_imports' => true,
		'phpdoc_align' => false,
		'phpdoc_order' => true,
		'php_unit_construct' => true,
		'php_unit_dedicate_assert' => true,
		'return_assignment' => true,
		'single_blank_line_at_eof' => true,
		'single_line_comment_style' => true,
		'ternary_to_null_coalescing' => true,
		'yoda_style' => ['equal' => true, 'identical' => true, 'less_and_greater' => true],
		'void_return' => true,
        'elseif' => true,
        'encoding' => true,
        'line_ending' => true,
        'lowercase_cast' => true,
        'lowercase_constants' => true,
        'native_constant_invocation' => true,
        'method_argument_space' => ['ensure_fully_multiline' => true],
    ])
	->setFinder($finder)
    ->setUsingCache(true)
    ->setRiskyAllowed(true);