<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Controller\ControllerInterface;

interface ApiBaseInterface extends  ControllerInterface
{
	public function print(array $data): void;
}
