<?php

declare(strict_types=1);

namespace Breeze\Enums;

use Breeze\Entity\BuddyRequestEntity;

enum BuddyStatus: string
{
	case None = 'none';
	case Pending = 'pending';
	case Confirmed = 'confirmed';

	public static function fromDbStatus(int $dbStatus): self
	{
		return match ($dbStatus) {
			BuddyRequestEntity::PENDING => self::Pending,
			BuddyRequestEntity::ACCEPTED => self::Confirmed,
			default => self::None,
		};
	}
}
