<?php


namespace Breeze\Config\Mapper;

use Breeze\Util\Form\Formatter\CheckFormatter;
use Breeze\Util\Form\Formatter\IntFormatter;
use Breeze\Util\Form\Formatter\SelectFormatter;
use Breeze\Util\Form\Formatter\TextFormatter;

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