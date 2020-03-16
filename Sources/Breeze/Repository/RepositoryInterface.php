<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\ModelInterface;

interface RepositoryInterface
{
	public function getModel(): ModelInterface;
}
