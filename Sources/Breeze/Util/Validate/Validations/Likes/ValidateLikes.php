<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateLikes extends ValidateData
{
	public function __construct(protected LikeRepositoryInterface $repository)
	{
	}

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
