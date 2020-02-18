<?php

declare(strict_types=1);


namespace Breeze\Controller;

interface ControllerInterface
{
	public function dispatch();

	public function getSubActions(): array;

	public function general(): void;
}
