<?php

declare(strict_types=1);


namespace Breeze\Repository\Like;

use Breeze\Repository\BaseRepository;
use Breeze\Repository\RepositoryInterface;

class Comment extends BaseRepository implements RepositoryInterface
{
	public function getType(): string
	{
		return self::LIKE_TYPE_COMMENT;
	}
}
