<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Service\UserService;
use Breeze\Util\Editor;
use Breeze\Util\Folder;
use Breeze\Util\Form\Formatters\CheckFormatter;
use Breeze\Util\Form\Formatters\IntFormatter;
use Breeze\Util\Form\Formatters\SelectFormatter;
use Breeze\Util\Form\Formatters\TextFormatter;
use Breeze\Util\Validate\ValidateGateway;

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
	'util.validate.gateway' => [
		'class' => ValidateGateway::class,
		'arguments'=> [UserService::class]
	],
	'util.editor' => [
		'class' => Editor::class
	],
];
