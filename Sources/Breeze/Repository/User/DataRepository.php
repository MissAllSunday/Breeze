<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Entity\MemberEntity;
use Breeze\Repository\BaseRepository;

class DataRepository extends BaseRepository
{
	public function getTableName(): string
	{
		return MemberEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return MemberEntity::ID;
	}

	public function getColumns(): array
	{
		return MemberEntity::getUserDataColumns();
	}

	public function getColumnPosterId(): string
	{
		return MemberEntity::ID;
	}
}
