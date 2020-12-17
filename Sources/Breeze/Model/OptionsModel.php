<?php

declare(strict_types=1);


namespace Breeze\Model;

use Breeze\Entity\MemberEntity;
use Breeze\Entity\OptionsEntity;
use Breeze\Util\Json;

class OptionsModel extends BaseModel implements OptionsModelInterface
{
	public const SAVED_AS_JSON = ['moodHistory'];

	public function insert(array $data, int $userID = 0): int
	{
		if (empty($data) || empty($userId)) {
			return 0;
		}

		$inserts = [];

		foreach ($data as $settingName => $settingValue) {
			if (in_array($settingName, self::SAVED_AS_JSON)) {
				$settingValue = Json::encode($settingValue);
			}

			$inserts[] = [$userID, $settingName, $settingValue];
		}

		$this->dbClient->replace(
			OptionsEntity::TABLE,
			[
				MemberEntity::COLUMN_ID => 'int',
				OptionsEntity::COLUMN_VARIABLE => 'string',
				OptionsEntity::COLUMN_VALUE => 'string'
			],
			$inserts,
			MemberEntity::COLUMN_ID
		);

		return 1;
	}

	public function update(array $data, int $userID = 0): array
	{
		return [$this->insert($data, $userID)];
	}

	public function getTableName(): string
	{
		return '';
	}

	public function getColumnId(): string
	{
		return '';
	}

	public function getColumns(): array
	{
		return [];
	}
}
