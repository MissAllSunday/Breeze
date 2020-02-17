<?php

declare(strict_types=1);


namespace Breeze\Repository\Like;

class Comment extends Base
{
	public function getType(): string
	{
		return self::LIKE_TYPE_COMMENT;
	}
}
