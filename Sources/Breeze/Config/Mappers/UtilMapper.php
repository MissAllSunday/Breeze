<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Service\UserService;
use Breeze\Util\Components;
use Breeze\Util\Folder;
use Breeze\Util\Form\SettingsBuilder;
use Breeze\Util\Form\Types\CheckType;
use Breeze\Util\Form\Types\IntType;
use Breeze\Util\Form\Types\SelectType;
use Breeze\Util\Form\Types\TextType;
use Breeze\Util\Validate\ValidateGateway;

return [
	'util.folder' => [
		'class' => Folder::class
	],
	'util.components' => [
		'class' => Components::class
	],
	'util.SettingsBuilder' => [
		'class' => SettingsBuilder::class
	],
	'util.formatter.check' => [
		'class' => CheckType::class
	],
	'util.formatter.int' => [
		'class' => IntType::class
	],
	'util.formatter.select' => [
		'class' => SelectType::class
	],
	'util.formatter.text' => [
		'class' => TextType::class
	],
	'util.validate.gateway' => [
		'class' => ValidateGateway::class,
		'arguments'=> [UserService::class]
	],
];
