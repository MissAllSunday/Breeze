<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Entity\EntityInterface;
use Breeze\Model\ModelInterface;

class BaseRepository implements RepositoryInterface
{
	/**
	 * @var ModelInterface
	 */
	protected $model;

	/**
	 * @var EntityInterface
	 */
	protected $entity;

	public function __construct(ModelInterface $model, EntityInterface $entity)
	{
		$this->model = $model;
		$this->entity = $entity;
	}

	public function getCount(): int
	{
		return $this->model->getCount();
	}

	public function getChunk(int $start, int $maxIndex): array
	{
		return $this->model->getChunk($start, $maxIndex);
	}
}
