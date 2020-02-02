<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Repository\User\Cover as CoverRepository;
use Breeze\Service\Request;

abstract class Base
{
	const CREATE = 'create';
	const DELETE = 'delete';

	public function getActions(): array {
		return [
		    self::CREATE,
		    self::DELETE
		];
	}
}
