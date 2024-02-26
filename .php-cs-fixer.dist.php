<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/Sources/Breeze/')
	->in(__DIR__ . '/tests/')
	->exclude(__DIR__ . '/tests/log/');

$config = new PhpCsFixer\Config();

return $config
	->setRules([
		'@PHP74Migration' => true,
		'@PHPUnit60Migration:risky' => true,
		'indentation_type' => true,
		'array_indentation' => true,
		'array_syntax' => ['syntax' => 'short'],
		'blank_line_after_opening_tag' => true,
		'concat_space' => ['spacing' => 'one'],
		'class_attributes_separation' => ['elements' => ['method' => 'one', 'property' => 'one']],
		'declare_strict_types' => true,
		'increment_style' => ['style' => 'post'],
		'is_null' => true,
		'list_syntax' => ['syntax' => 'short'],
		'blank_line_after_namespace' => true,
		'blank_line_before_statement' => true,
		'method_chaining_indentation' => true,
		'modernize_types_casting' => true,
		'multiline_whitespace_before_semicolons' => false,
		'no_superfluous_elseif' => true,
		'no_superfluous_phpdoc_tags' => true,
		'no_useless_else' => true,
		'no_useless_return' => true,
		'no_unused_imports' => true,
		'ordered_imports' => true,
		'phpdoc_align' => false,
		'phpdoc_order' => true,
		'php_unit_construct' => true,
		'php_unit_dedicate_assert' => true,
		'return_assignment' => true,
		'single_blank_line_at_eof' => true,
		'single_line_comment_style' => true,
		'ternary_to_null_coalescing' => true,
		'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
		'void_return' => true,
		'elseif' => true,
		'encoding' => true,
		'line_ending' => true,
		'lowercase_cast' => true,
		'method_argument_space' => ['after_heredoc' => true],
		'constant_case' => true,
		'lowercase_keywords' => true,
		'visibility_required' => true,
		'native_constant_invocation' => true,
		'no_unneeded_braces' => true,
		'function_declaration' => true,
	])
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder)
	->setUsingCache(true)
	->setRiskyAllowed(true);
