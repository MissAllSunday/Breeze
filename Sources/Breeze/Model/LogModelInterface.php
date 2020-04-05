<?php

declare(strict_types=1);

namespace Breeze\Model;

interface LogModelInterface extends BaseModelInterface
{
	public function getLog(array $userIds, int $maxIndex, int $start): array;
}
