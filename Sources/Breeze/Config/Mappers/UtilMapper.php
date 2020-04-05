<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Util\Form\Formatters\CheckFormatter;
use Breeze\Util\Form\Formatters\IntFormatter;
use Breeze\Util\Form\Formatters\SelectFormatter;
use Breeze\Util\Form\Formatters\TextFormatter;
use Breeze\Util\Settings;
use Breeze\Util\Text;

return [
	'util.settings' => [
		'class' => Settings::class
	],
	'util.text' => [
		'class' => Text::class
	],
	'util.formatter.check' => [
		'class' => CheckFormatter::class
	],
	'util.formatter.int' => [
		'class' => IntFormatter::class
	],
	'util.formatter.select' => [
		'class' => SelectFormatter::class
	],
	'util.formatter.text' => [
		'class' => TextFormatter::class
	],
];
