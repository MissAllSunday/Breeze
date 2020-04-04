<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Util\Form\Formatters\CheckFormatter;
use Breeze\Util\Form\Formatters\IntFormatter;
use Breeze\Util\Form\Formatters\SelectFormatter;
use Breeze\Util\Form\Formatters\TextFormatter;

return [
	'formatter.check' => [
		'class' => CheckFormatter::class
	],
	'formatter.int' => [
		'class' => IntFormatter::class
	],
	'formatter.select' => [
		'class' => SelectFormatter::class
	],
	'formatter.text' => [
		'class' => TextFormatter::class
	],
];
