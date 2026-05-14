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
			BuddyRequestEntity::CONFIRMED => self::Confirmed,
			default => self::None,
		};
	}

	public function toDbStatus(): int
	{
		return match ($this) {
			self::Pending => BuddyRequestEntity::PENDING,
			self::Confirmed => BuddyRequestEntity::CONFIRMED,
			default => throw new \InvalidArgumentException('None has no DB status'),
		};
	}
}
