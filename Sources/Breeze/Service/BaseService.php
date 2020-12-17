<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Traits\TextTrait;

abstract class BaseService implements BaseServiceInterface
{
	use TextTrait;

	public function redirect(string $urlName): void
	{
		if (!empty($urlName)) {
			redirectexit($urlName);
		}
	}
}
