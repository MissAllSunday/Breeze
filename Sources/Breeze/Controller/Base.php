<?php

declare(strict_types=1);

namespace Breeze\Controller;

use League\Container\Container as Container;
use League\Container\ReflectionContainer as ReflectionContainer;

abstract class Base
{
	const CREATE = 'create';
	const DELETE = 'delete';

	/**
	 * @var Container
	 */
	protected $container;

	public function __construct()
	{
		$this->container = new Container;

		$this->container->delegate(
		    new ReflectionContainer
		);
	}

	public function getActions(): array {
		return [
		    self::CREATE,
		    self::DELETE
		];
	}
}
