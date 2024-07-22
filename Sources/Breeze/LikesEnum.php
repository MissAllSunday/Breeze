<?php

declare(strict_types=1);

namespace Breeze;

enum LikesEnum: string
{
	case Status = 'br_sta';
	case Comments = 'br_com';

	public static function isValid(string $type): bool
	{
		return match($type)
		{
			self::Status->value, self::Comments->value => true,
			default => false,
		};
	}
}
