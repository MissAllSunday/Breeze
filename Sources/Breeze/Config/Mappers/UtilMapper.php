<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Util\Folder;
use Breeze\Util\Form\Formatters\CheckFormatter;
use Breeze\Util\Form\Formatters\IntFormatter;
use Breeze\Util\Form\Formatters\SelectFormatter;
use Breeze\Util\Form\Formatters\TextFormatter;

return [
	'util.folder' => [
		'class' => Folder::class
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
