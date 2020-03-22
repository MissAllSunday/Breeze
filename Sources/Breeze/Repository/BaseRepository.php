<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\EntityInterface;
use Breeze\Model\ModelInterface;
use Breeze\Traits\CacheTrait;

abstract class BaseRepository implements RepositoryInterface
{
	use CacheTrait;

	public const LIKE_TYPE_STATUS = 'breSta';
	public const LIKE_TYPE_COMMENT = 'breCom';

	/**
	 * @var ModelInterface
	 */
	protected $model;

	/**
	 * @var EntityInterface
	 */
	protected $entity;

	public function __construct(ModelInterface $model)
	{
		$this->model = $model;
	}

	public function handleLikes($type, $content): array
	{
	}

	public static function getAllTypes(): array
	{
		return [
			self::LIKE_TYPE_STATUS,
			self::LIKE_TYPE_COMMENT
		];
	}

	public function getModel(): ModelInterface
	{
		return $this->model;
	}
}
