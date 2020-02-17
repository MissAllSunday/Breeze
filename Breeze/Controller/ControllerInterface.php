<?php

declare(strict_types=1);


namespace Breeze\Controller;

interface ControllerInterface
{
	public function doAction();

	public function getSubActions(): array;

}
