<?php

declare(strict_types=1);


namespace Breeze\Service;

interface FormServiceInterface extends BaseServiceInterface
{
	public function getConfigVarsSettings(): array;

	public function getCoverConfigVarsSettings(): array;
}
