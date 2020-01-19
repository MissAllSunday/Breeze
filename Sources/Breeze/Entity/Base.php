<?php

declare(strict_types=1);


namespace Breeze\Entity;


abstract class Base
{
	function getName(): string
	{
		return static::TABLE;
	}

	abstract function getColumns(): array;
}