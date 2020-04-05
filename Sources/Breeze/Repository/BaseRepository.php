<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\EntityInterface;
use Breeze\Model\BaseModelInterface;
use Breeze\Traits\CacheTrait;

abstract class BaseRepository
{
	use CacheTrait;

	public const LIKE_TYPE_STATUS = 'breSta';
	public const LIKE_TYPE_COMMENT = 'breCom';

	public function handleLikes($type, $content): array
	{
		return [];
	}

	public static function getAllTypes(): array
	{
		return [
			self::LIKE_TYPE_STATUS,
			self::LIKE_TYPE_COMMENT
		];
	}

	public abstract function getModel();
}
