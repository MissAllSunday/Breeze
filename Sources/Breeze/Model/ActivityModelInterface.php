<?php

declare(strict_types=1);


namespace Breeze\Model;

interface ActivityModelInterface
{
	public function getByIds(array $activityIds = []): array;
}
